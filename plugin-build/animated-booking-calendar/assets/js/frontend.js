(() => {
  const I18N = window.abcCalendarI18n || {};
  const t = (key, fallback) => {
    const value = I18N[key];
    return typeof value === 'string' && value.trim() !== '' ? value : fallback;
  };
  const locale = typeof I18N.locale === 'string' && I18N.locale.trim() !== '' ? I18N.locale : 'en-US';
  const tWeekdays = Array.isArray(I18N.weekdays) && I18N.weekdays.length === 7
    ? I18N.weekdays
    : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  const STATUS_LABEL = {
    available: t('status_available', 'Available'),
    tentative: t('status_tentative', 'Tentative booking'),
    booked: t('status_booked', 'Booked'),
    none: t('status_none', 'No information'),
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

  const normalizeSlots = (raw) => {
    if (!Array.isArray(raw)) return [];
    return [...new Set(raw
      .map((v) => (typeof v === 'string' ? v.trim() : ''))
      .filter((v) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(v)))].sort();
  };

  const normalizeOverrides = (raw) => {
    if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};
    const out = {};
    Object.entries(raw).forEach(([date, slots]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return;
      out[date] = normalizeSlots(slots);
    });
    return out;
  };

  const normalizeDayModeMap = (raw) => {
    if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};
    const out = {};
    Object.entries(raw).forEach(([date, mode]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return;
      if (mode === 'slots' || mode === 'all_day') {
        out[date] = mode;
      }
    });
    return out;
  };

  const normalizeReservations = (raw) => {
    if (!raw || typeof raw !== 'object' || Array.isArray(raw)) return {};
    const now = Math.floor(Date.now() / 1000);
    const out = {};
    Object.entries(raw).forEach(([date, slots]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return;
      if (!slots || typeof slots !== 'object' || Array.isArray(slots)) return;
      Object.entries(slots).forEach(([time, entry]) => {
        if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(time) && time !== 'ALL_DAY') return;
        if (!entry || typeof entry !== 'object' || Array.isArray(entry)) return;
        const status = String(entry.status || '');
        if (!['hold', 'booked'].includes(status)) return;
        const expiresAt = Number(entry.expires_at || 0);
        if (status === 'hold' && expiresAt > 0 && expiresAt <= now) return;
        if (!out[date]) out[date] = {};
        out[date][time] = { status, expires_at: expiresAt };
      });
    });
    return out;
  };

  const run = (calendar) => {
    const id = (calendar.dataset.abcCalendarId || '').trim();
    const monthsRaw = Number(calendar.dataset.abcMonths || 12);
    const offsetRaw = Number(calendar.dataset.abcOffset || 0);
    const dayModeDefaultRaw = String(calendar.dataset.abcDayModeDefault || 'slots').trim();
    const dayModeDefault = ['slots', 'all_day', 'hybrid'].includes(dayModeDefaultRaw) ? dayModeDefaultRaw : 'slots';

    let statusMap = {};
    let dayModeMap = {};
    let defaultSlots = [];
    let timeOverrides = {};
    let timeReservations = {};
    try {
      statusMap = normalizeMap(JSON.parse(calendar.dataset.abcStatusMap || '{}'));
    } catch (_e) {
      statusMap = {};
    }
    try {
      dayModeMap = normalizeDayModeMap(JSON.parse(calendar.dataset.abcDayModeMap || '{}'));
    } catch (_e) {
      dayModeMap = {};
    }
    try {
      defaultSlots = normalizeSlots(JSON.parse(calendar.dataset.abcTimeDefault || '[]'));
    } catch (_e) {
      defaultSlots = [];
    }
    try {
      timeOverrides = normalizeOverrides(JSON.parse(calendar.dataset.abcTimeOverrides || '{}'));
    } catch (_e) {
      timeOverrides = {};
    }
    try {
      timeReservations = normalizeReservations(JSON.parse(calendar.dataset.abcTimeReservations || '{}'));
    } catch (_e) {
      timeReservations = {};
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
    const timeSelect = calendar.querySelector('[data-abc-time-select]');
    const isAllDayInput = calendar.querySelector('[data-abc-is-all-day]');

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

    const weekdaysLabels = tWeekdays;
    weekdays.innerHTML = weekdaysLabels.map((d) => `<div>${d}</div>`).join('');

    const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });
    const dateFormatter = new Intl.DateTimeFormat(locale, { day: '2-digit', month: '2-digit', year: 'numeric' });
    let index = 0;

    const hideBooking = () => {
      if (bookingPanel) bookingPanel.hidden = true;
      if (form) form.hidden = true;
      if (dateInput) dateInput.value = '';
      if (dateDisplay) dateDisplay.value = '';
      if (timeSelect) {
        timeSelect.innerHTML = `<option value="">${t('choose_hour', 'Choose time')}</option>`;
        timeSelect.value = '';
      }
      if (isAllDayInput) {
        isAllDayInput.value = '0';
      }
    };

    const resolveDayMode = (dayKey) => {
      const fromMap = dayModeMap[dayKey];
      if (fromMap === 'all_day' || fromMap === 'slots') return fromMap;
      if (dayModeDefault === 'all_day') return 'all_day';
      return 'slots';
    };

    const getAvailableSlots = (dayKey) => {
      if (resolveDayMode(dayKey) === 'all_day') {
        const reserved = timeReservations[dayKey] || {};
        return Object.keys(reserved).length > 0 ? [] : ['ALL_DAY'];
      }
      const configured = (timeOverrides[dayKey] && timeOverrides[dayKey].length > 0)
        ? timeOverrides[dayKey]
        : defaultSlots;
      const reserved = timeReservations[dayKey] || {};
      return configured.filter((slot) => !reserved[slot]);
    };

    const setBooking = (dayKey, dayData) => {
      if (!bookingPanel || !dateInput || !dateDisplay || !openButton || !form || !timeSelect) return;
      const daySlots = getAvailableSlots(dayKey);
      if (!dayData || dayData.status !== 'available' || daySlots.length === 0) {
        hideBooking();
        return;
      }
      const dayMode = resolveDayMode(dayKey);
      const d = new Date(dayKey + 'T00:00:00');
      bookingPanel.hidden = false;
      openButton.hidden = false;
      form.hidden = true;
      dateInput.value = dayKey;
      dateDisplay.value = dateFormatter.format(d);
      if (dayMode === 'all_day') {
        timeSelect.innerHTML = `<option value="ALL_DAY">${t('all_day', 'Full day')}</option>`;
        timeSelect.value = 'ALL_DAY';
        timeSelect.required = false;
        timeSelect.disabled = true;
        if (isAllDayInput) {
          isAllDayInput.value = '1';
        }
      } else {
        timeSelect.disabled = false;
        timeSelect.required = true;
        timeSelect.innerHTML = [`<option value="">${t('choose_hour', 'Choose time')}</option>`]
          .concat(daySlots.map((slot) => `<option value="${slot}">${slot}</option>`))
          .join('');
        if (isAllDayInput) {
          isAllDayInput.value = '0';
        }
      }
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
        note.textContent = t('no_data_month', 'No data for selected month.');
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
      const daySlots = day ? getAvailableSlots(day) : [];
      const dayMode = day ? resolveDayMode(day) : 'slots';
      const showSlots = ['available', 'tentative', 'free', 'hold'].includes(String(data.status || ''));
      const slotsText = showSlots
        ? (dayMode === 'all_day'
            ? ` · ${t('all_day', 'Full day')}`
            : (daySlots.length > 0 ? ` · ${t('hours_prefix', 'Hours:')} ${daySlots.join(', ')}` : ''))
        : '';

      note.textContent = data.note
        ? `${dateText}: ${(STATUS_LABEL[data.status] || STATUS_LABEL.none)} · ${data.note}${slotsText}`
        : `${dateText}: ${(STATUS_LABEL[data.status] || STATUS_LABEL.none)}${slotsText}`;

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
