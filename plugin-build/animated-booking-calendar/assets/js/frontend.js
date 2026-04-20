(() => {
  const STATUS_LABEL = {
    available: 'Dostępny',
    tentative: 'Wstępna rezerwacja',
    booked: 'Zajęty',
    none: 'Brak informacji',
  };

  const toDateKey = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  const normalizeMap = (raw) => {
    if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};
    const out = {};
    const now = Math.floor(Date.now() / 1000);

    Object.entries(raw).forEach(([key, value]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(key)) return;
      if (!value || typeof value !== 'object') return;
      const status = String(value.status || 'none');
      const note = typeof value.note === 'string' ? value.note : '';
      const holdExpiresAt = Number(value.hold_expires_at || 0);

      if (status === 'tentative' && holdExpiresAt > 0 && holdExpiresAt <= now) {
        out[key] = { status: 'available', note: '' };
        return;
      }

      out[key] = { status, note };
    });

    return out;
  };

  const run = (calendar) => {
    const id = (calendar.dataset.abcCalendarId || '').trim();
    const monthsRaw = Number(calendar.dataset.abcMonths || 12);
    const offsetRaw = Number(calendar.dataset.abcOffset || 0);

    let statusMap = {};
    try {
      statusMap = normalizeMap(JSON.parse(calendar.dataset.abcStatusMap || '{}'));
    } catch (_e) {
      statusMap = {};
    }

    const monthLabel = calendar.querySelector('[data-abc-month-label]');
    const weekdays = calendar.querySelector('[data-abc-weekdays]');
    const daysGrid = calendar.querySelector('[data-abc-days]');
    const note = calendar.querySelector('[data-abc-note]');
    const prev = calendar.querySelector('[data-abc-prev]');
    const next = calendar.querySelector('[data-abc-next]');

    const bookingPanel = calendar.querySelector('[data-abc-booking-panel]');
    const openButton = calendar.querySelector('[data-abc-open]');
    const form = calendar.querySelector('[data-abc-form]');
    const dateInput = calendar.querySelector('[data-abc-date]');
    const dateDisplay = calendar.querySelector('[data-abc-date-display]');

    if (!monthLabel || !weekdays || !daysGrid || !note || !prev || !next) return;

    if (id) {
      const url = new URL(window.location.href);
      const resultCalendar = (url.searchParams.get('abc_calendar') || '').trim();
      const resultState = (url.searchParams.get('abc_booking') || '').trim();
      if (resultCalendar === id && resultState) {
        url.searchParams.delete('abc_calendar');
        url.searchParams.delete('abc_booking');
        url.searchParams.delete('abc_msg');
        window.history.replaceState({}, document.title, url.toString());
      }
    }

    const monthsToShow = Number.isFinite(monthsRaw) ? Math.max(3, Math.min(24, monthsRaw)) : 12;
    const offset = Number.isFinite(offsetRaw) ? Math.max(-12, Math.min(12, offsetRaw)) : 0;

    const base = new Date();
    base.setDate(1);
    base.setMonth(base.getMonth() + offset);

    const months = [];
    for (let i = 0; i < monthsToShow; i += 1) {
      months.push(new Date(base.getFullYear(), base.getMonth() + i, 1));
    }

    const weekdaysLabels = ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'];
    weekdays.innerHTML = weekdaysLabels.map((d) => `<div>${d}</div>`).join('');

    const monthFormatter = new Intl.DateTimeFormat('pl-PL', { month: 'long', year: 'numeric' });
    const dateFormatter = new Intl.DateTimeFormat('pl-PL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    let index = 0;

    const hideBooking = () => {
      if (bookingPanel) bookingPanel.hidden = true;
      if (form) form.hidden = true;
      if (dateInput) dateInput.value = '';
      if (dateDisplay) dateDisplay.value = '';
    };

    const setBooking = (dayKey, dayData) => {
      if (!bookingPanel || !dateInput || !dateDisplay || !openButton || !form) return;
      if (!dayData || dayData.status !== 'available') {
        hideBooking();
        return;
      }
      const d = new Date(dayKey + 'T00:00:00');
      bookingPanel.hidden = false;
      openButton.hidden = false;
      form.hidden = true;
      dateInput.value = dayKey;
      dateDisplay.value = dateFormatter.format(d);
    };

    openButton?.addEventListener('click', () => {
      if (form) form.hidden = false;
      openButton.blur();
    });

    const renderMonth = () => {
      const month = months[index];
      if (!month) return;

      monthLabel.textContent = monthFormatter.format(month);
      prev.disabled = index === 0;
      next.disabled = index === months.length - 1;

      const y = month.getFullYear();
      const m = month.getMonth();
      const firstWeekday = (new Date(y, m, 1).getDay() + 6) % 7;
      const daysInMonth = new Date(y, m + 1, 0).getDate();
      const prevDays = new Date(y, m, 0).getDate();

      const cells = [];
      for (let i = 0; i < firstWeekday; i += 1) {
        const num = prevDays - firstWeekday + i + 1;
        cells.push(`<button type="button" class="abc-day is-muted" disabled>${num}</button>`);
      }

      for (let d = 1; d <= daysInMonth; d += 1) {
        const date = new Date(y, m, d);
        const key = toDateKey(date);
        const entry = statusMap[key] || { status: 'none', note: '' };
        cells.push(`<button type="button" class="abc-day is-${entry.status}" data-day="${key}" data-status="${entry.status}">${d}</button>`);
      }

      while (cells.length < 42) {
        const num = cells.length - (firstWeekday + daysInMonth) + 1;
        cells.push(`<button type="button" class="abc-day is-muted" disabled>${num}</button>`);
      }

      daysGrid.innerHTML = cells.join('');
      const first = daysGrid.querySelector('.abc-day[data-day]');
      if (first) first.click();
      else {
        note.textContent = 'Brak danych.';
        hideBooking();
      }
    };

    daysGrid.addEventListener('click', (e) => {
      const target = e.target;
      if (!(target instanceof HTMLElement)) return;
      const btn = target.closest('.abc-day[data-day]');
      if (!(btn instanceof HTMLElement)) return;

      daysGrid.querySelectorAll('.abc-day.is-active').forEach((node) => node.classList.remove('is-active'));
      btn.classList.add('is-active');

      const day = btn.dataset.day || '';
      const data = statusMap[day] || { status: 'none', note: '' };
      const d = day ? new Date(day + 'T00:00:00') : null;
      const dateText = d ? dateFormatter.format(d) : '';

      note.textContent = data.note
        ? `${dateText}: ${(STATUS_LABEL[data.status] || STATUS_LABEL.none)} · ${data.note}`
        : `${dateText}: ${(STATUS_LABEL[data.status] || STATUS_LABEL.none)}`;

      setBooking(day, data);
    });

    prev.addEventListener('click', () => {
      if (index > 0) {
        index -= 1;
        renderMonth();
      }
    });

    next.addEventListener('click', () => {
      if (index < months.length - 1) {
        index += 1;
        renderMonth();
      }
    });

    renderMonth();
  };

  const boot = () => {
    document.querySelectorAll('[data-abc-calendar]').forEach(run);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
