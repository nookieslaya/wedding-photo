import {
  STATUS_LABEL,
  normalizeRanges,
  normalizeStatusMap,
  parseDate,
  resolveDayStatus,
  toDateKey,
} from '../shared/availability-status';

export const initAvailabilityCalendarModule = () => {
  const modules = document.querySelectorAll('[data-availability-calendar]');

  modules.forEach((calendar) => {
    const moduleId = (calendar.dataset.availabilityModuleId || '').trim();
    const rangesRaw = calendar.dataset.availabilityRanges;
    const statusMapRaw = calendar.dataset.availabilityMap;
    const defaultSlotsRaw = calendar.dataset.availabilityTimeDefault;
    const overridesRaw = calendar.dataset.availabilityTimeOverrides;
    const reservationsRaw = calendar.dataset.availabilityTimeReservations;
    const monthsRaw = Number(calendar.dataset.availabilityMonths || 12);
    const offsetRaw = Number(calendar.dataset.availabilityOffset || 0);

    let ranges = [];
    let statusMap = {};
    let defaultSlots = [];
    let timeOverrides = {};
    let timeReservations = {};
    try {
      ranges = normalizeRanges(JSON.parse(rangesRaw || '[]'));
    } catch (error) {
      ranges = [];
    }
    try {
      statusMap = normalizeStatusMap(JSON.parse(statusMapRaw || '{}'));
    } catch (error) {
      statusMap = {};
    }
    try {
      const parsed = JSON.parse(defaultSlotsRaw || '[]');
      defaultSlots = Array.isArray(parsed)
        ? [...new Set(parsed
          .map((v) => (typeof v === 'string' ? v.trim() : ''))
          .filter((v) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(v)))].sort()
        : [];
    } catch (error) {
      defaultSlots = [];
    }
    try {
      const parsed = JSON.parse(overridesRaw || '{}');
      timeOverrides = parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (error) {
      timeOverrides = {};
    }
    try {
      const parsed = JSON.parse(reservationsRaw || '{}');
      timeReservations = parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (error) {
      timeReservations = {};
    }

    const monthsToShow = Number.isFinite(monthsRaw) ? Math.max(3, Math.min(24, monthsRaw)) : 12;
    const startOffset = Number.isFinite(offsetRaw) ? Math.max(-12, Math.min(12, offsetRaw)) : 0;

    const monthLabel = calendar.querySelector('[data-availability-month-label]');
    const weekdays = calendar.querySelector('[data-availability-weekdays]');
    const daysGrid = calendar.querySelector('[data-availability-days]');
    const note = calendar.querySelector('[data-availability-note]');
    const prevButton = calendar.querySelector('[data-availability-prev]');
    const nextButton = calendar.querySelector('[data-availability-next]');
    const bookingPanel = calendar.querySelector('[data-availability-booking-panel]');
    const bookingCta = calendar.querySelector('[data-availability-booking-cta]');
    const bookingOpenButton = calendar.querySelector('[data-availability-booking-open]');
    const bookingForm = calendar.querySelector('[data-availability-booking-form]');
    const bookingDateInput = calendar.querySelector('[data-availability-booking-date]');
    const bookingDateDisplay = calendar.querySelector('[data-availability-booking-date-display]');
    const bookingTimeSelect = calendar.querySelector('[data-availability-booking-time]');

    if (!monthLabel || !weekdays || !daysGrid || !note || !prevButton || !nextButton) {
      return;
    }

    if (moduleId !== '') {
      const url = new URL(window.location.href);
      const bookingModule = (url.searchParams.get('booking_module') || '').trim();
      const bookingState = (url.searchParams.get('booking_request') || '').trim();
      if (bookingModule === moduleId && bookingState !== '') {
        url.searchParams.delete('booking_module');
        url.searchParams.delete('booking_request');
        url.searchParams.delete('booking_message');
        window.history.replaceState({}, document.title, url.toString());
      }
    }

    const baseDate = new Date();
    baseDate.setDate(1);
    baseDate.setMonth(baseDate.getMonth() + startOffset);

    const months = [];
    for (let i = 0; i < monthsToShow; i += 1) {
      const monthDate = new Date(baseDate.getFullYear(), baseDate.getMonth() + i, 1);
      months.push(monthDate);
    }

    let monthIndex = 0;

    const weekdayFormatter = ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'];
    weekdays.innerHTML = weekdayFormatter.map((label) => `<div>${label}</div>`).join('');

    const monthNameFormatter = new Intl.DateTimeFormat('pl-PL', {
      month: 'long',
      year: 'numeric',
    });
    const dateFormatter = new Intl.DateTimeFormat('pl-PL', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });

    const getDateSlots = (dateKey) => {
      const override = timeOverrides?.[dateKey];
      if (Array.isArray(override)) {
        return [...new Set(override
          .map((v) => (typeof v === 'string' ? v.trim() : ''))
          .filter((v) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(v)))].sort();
      }
      return [...defaultSlots];
    };

    const getReservedSlots = (dateKey) => {
      const now = Math.floor(Date.now() / 1000);
      const day = timeReservations?.[dateKey];
      const reserved = new Set();
      if (!day || typeof day !== 'object' || Array.isArray(day)) {
        return reserved;
      }
      Object.entries(day).forEach(([slot, entry]) => {
        if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || !entry || typeof entry !== 'object' || Array.isArray(entry)) {
          return;
        }
        const status = String(entry.status || '');
        if (status === 'booked') {
          reserved.add(slot);
          return;
        }
        if (status === 'hold') {
          const expiresAt = Number(entry.expires_at) || 0;
          if (!expiresAt || expiresAt > now) {
            reserved.add(slot);
          }
        }
      });
      return reserved;
    };

    const getAvailableSlots = (dateKey) => {
      const slots = getDateSlots(dateKey);
      if (!slots.length) return [];
      const reserved = getReservedSlots(dateKey);
      return slots.filter((slot) => !reserved.has(slot));
    };

    const updateBookingPanel = (dayData, dayDate) => {
      if (!bookingPanel || !bookingDateInput || !bookingDateDisplay || !bookingTimeSelect) {
        return;
      }

      if (dayData?.status !== 'available' || !dayDate) {
        bookingPanel.hidden = true;
        if (bookingCta) {
          bookingCta.hidden = true;
        }
        if (bookingForm) {
          bookingForm.hidden = true;
        }
        bookingDateInput.value = '';
        bookingDateDisplay.value = '';
        bookingTimeSelect.innerHTML = '<option value="">Wybierz godzinę</option>';
        return;
      }

      const dateKey = toDateKey(dayDate);
      const availableSlots = getAvailableSlots(dateKey);
      if (availableSlots.length === 0) {
        bookingPanel.hidden = true;
        if (bookingCta) {
          bookingCta.hidden = true;
        }
        if (bookingForm) {
          bookingForm.hidden = true;
        }
        bookingDateInput.value = '';
        bookingDateDisplay.value = '';
        bookingTimeSelect.innerHTML = '<option value="">Brak wolnych godzin</option>';
        return;
      }

      bookingPanel.hidden = false;
      if (bookingCta) {
        bookingCta.hidden = false;
      }
      if (bookingForm) {
        bookingForm.hidden = true;
      }
      bookingDateInput.value = dateKey;
      bookingDateDisplay.value = dateFormatter.format(dayDate);
      bookingTimeSelect.innerHTML = ['<option value="">Wybierz godzinę</option>']
        .concat(availableSlots.map((slot) => `<option value="${slot}">${slot}</option>`))
        .join('');
    };

    bookingOpenButton?.addEventListener('click', () => {
      if (!bookingForm) {
        return;
      }
      bookingForm.hidden = false;
      bookingOpenButton.blur();
    });

    const renderMonth = () => {
      const currentMonth = months[monthIndex];
      const year = currentMonth.getFullYear();
      const month = currentMonth.getMonth();

      monthLabel.textContent = monthNameFormatter.format(currentMonth);

      const firstWeekday = (new Date(year, month, 1).getDay() + 6) % 7;
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const prevDays = new Date(year, month, 0).getDate();

      const cells = [];

      for (let i = 0; i < firstWeekday; i += 1) {
        const dayNum = prevDays - firstWeekday + i + 1;
        cells.push(`<button type="button" class="availability-day is-muted" disabled>${dayNum}</button>`);
      }

      for (let day = 1; day <= daysInMonth; day += 1) {
        const date = new Date(year, month, day);
        const dateKey = toDateKey(date);
        const { status } = resolveDayStatus(date, ranges, statusMap);
        cells.push(
          `<button type="button" class="availability-day is-${status}" data-day-date="${dateKey}" data-day-status="${status}">${day}</button>`,
        );
      }

      const remaining = 42 - cells.length;
      for (let i = 1; i <= remaining; i += 1) {
        cells.push(`<button type="button" class="availability-day is-muted" disabled>${i}</button>`);
      }

      daysGrid.innerHTML = cells.join('');

      prevButton.disabled = monthIndex === 0;
      nextButton.disabled = monthIndex === months.length - 1;

      const firstDay = daysGrid.querySelector('.availability-day[data-day-date]');
      if (firstDay) {
        firstDay.click();
      } else {
        note.textContent = 'Brak danych dla wybranego miesiąca.';
        updateBookingPanel(null, null);
      }
    };

    daysGrid.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) {
        return;
      }

      const dayButton = target.closest('.availability-day[data-day-date]');
      if (!(dayButton instanceof HTMLElement)) {
        return;
      }

      daysGrid.querySelectorAll('.availability-day.is-active').forEach((node) => {
        node.classList.remove('is-active');
      });
      dayButton.classList.add('is-active');

      const dayDate = parseDate(dayButton.dataset.dayDate || '');
      const dayStatus = dayButton.dataset.dayStatus || 'none';
      const dayData = dayDate ? resolveDayStatus(dayDate, ranges, statusMap) : { status: 'none', note: '' };
      const dateKey = dayDate ? toDateKey(dayDate) : '';
      const availableSlots = dateKey ? getAvailableSlots(dateKey) : [];
      const slotsLabel = dayData.status === 'available' && availableSlots.length > 0
        ? ` · Godziny: ${availableSlots.join(', ')}`
        : '';

      const humanDate = dayDate
        ? new Intl.DateTimeFormat('pl-PL', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(dayDate)
        : '';

      note.textContent = dayData.note
        ? `${humanDate}: ${STATUS_LABEL[dayStatus] || STATUS_LABEL.none} · ${dayData.note}${slotsLabel}`
        : `${humanDate}: ${STATUS_LABEL[dayStatus] || STATUS_LABEL.none}${slotsLabel}`;

      updateBookingPanel(dayData, dayDate);
    });

    prevButton.addEventListener('click', () => {
      if (monthIndex > 0) {
        monthIndex -= 1;
        renderMonth();
      }
    });

    nextButton.addEventListener('click', () => {
      if (monthIndex < months.length - 1) {
        monthIndex += 1;
        renderMonth();
      }
    });

    renderMonth();
  });
};
