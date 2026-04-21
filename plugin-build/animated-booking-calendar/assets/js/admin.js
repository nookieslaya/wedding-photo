(() => {
  const I18N = window.abcAdminI18n || {};
  const t = (key, fallback) => {
    const value = I18N[key];
    return typeof value === 'string' && value.trim() !== '' ? value : fallback;
  };
  const fmt = (template, vars = {}) => String(template || '').replace(/\{([a-z_]+)\}/gi, (m, key) => {
    if (Object.prototype.hasOwnProperty.call(vars, key)) return String(vars[key]);
    return m;
  });
  const locale = typeof I18N.locale === 'string' && I18N.locale.trim() !== '' ? I18N.locale : 'en-US';
  const tWeekdays = Array.isArray(I18N.weekdays) && I18N.weekdays.length === 7
    ? I18N.weekdays
    : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  const STATUSS = [
    ['available', t('status_available', 'Available')],
    ['tentative', t('status_tentative', 'Tentative')],
    ['booked', t('status_booked', 'Booked')],
  ];

  const parseMap = (raw) => {
    try {
      const parsed = JSON.parse(String(raw || '{}'));
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_e) {
      return {};
    }
  };

  const parseOverrides = (raw) => {
    try {
      const parsed = JSON.parse(String(raw || '{}'));
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_e) {
      return {};
    }
  };

  const parseReservations = (raw) => {
    try {
      const parsed = JSON.parse(String(raw || '{}'));
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_e) {
      return {};
    }
  };

  const parseDayModeMap = (raw) => {
    try {
      const parsed = JSON.parse(String(raw || '{}'));
      if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) return {};
      const out = {};
      Object.entries(parsed).forEach(([date, mode]) => {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) return;
        if (mode === 'slots' || mode === 'all_day') out[date] = mode;
      });
      return out;
    } catch (_e) {
      return {};
    }
  };

  const parseTimeSlots = (raw) => {
    const lines = String(raw || '').split(/\r?\n/);
    return [...new Set(lines
      .map((line) => line.trim())
      .filter((slot) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot)))].sort();
  };

  const toDateKey = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  };

  const normalize = (map) => {
    const out = {};
    Object.entries(map || {}).forEach(([k, v]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(k)) return;
      let status = 'none';
      let note = '';
      let holdExpiresAt;
      let holdRequestId;

      if (typeof v === 'string') {
        status = String(v || 'none');
      } else if (v && typeof v === 'object') {
        status = String(v.status || 'none');
        note = typeof v.note === 'string' ? v.note : '';
        if (v.hold_expires_at !== undefined) holdExpiresAt = Number(v.hold_expires_at) || 0;
        if (v.hold_request_id !== undefined) holdRequestId = Number(v.hold_request_id) || 0;
      } else {
        return;
      }

      if (!['available', 'tentative', 'booked'].includes(status)) return;

      const entry = { status, note };
      if (holdExpiresAt && holdExpiresAt > 0) entry.hold_expires_at = holdExpiresAt;
      if (holdRequestId && holdRequestId > 0) entry.hold_request_id = holdRequestId;
      out[k] = entry;
    });
    return out;
  };

  const init = (host) => {
    if (host.dataset.ready === '1') return;
    host.dataset.ready = '1';

    const storage = document.getElementById('abc_status_map');
    const dayModeStorage = document.getElementById('abc_day_mode_map');
    const overridesStorage = document.getElementById('abc_time_slots_overrides');
    if (!storage) return;

    let state = {
      map: normalize(parseMap(storage.value || host.dataset.abcStatusMap || '{}')),
      timeOverrides: {},
      timeReservations: {},
      dayModeMap: {},
      selectedDayMode: 'slots',
      dayModeDefault: ['slots', 'all_day', 'hybrid'].includes(String(host.dataset.abcDayModeDefault || 'slots'))
        ? String(host.dataset.abcDayModeDefault || 'slots')
        : 'slots',
      monthIndex: 0,
      selectedStatus: 'booked',
      selectedDates: new Set(),
      note: '',
    };

    const parsedOverrides = parseOverrides((overridesStorage && overridesStorage.value) || host.dataset.abcTimeOverrides || '{}');
    state.dayModeMap = parseDayModeMap((dayModeStorage && dayModeStorage.value) || host.dataset.abcDayModeMap || '{}');
    Object.entries(parsedOverrides || {}).forEach(([date, slots]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || !Array.isArray(slots)) return;
      const valid = [...new Set(slots
        .map((v) => (typeof v === 'string' ? v.trim() : ''))
        .filter((v) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(v)))].sort();
      state.timeOverrides[date] = valid;
    });

    const parsedReservations = parseReservations(host.dataset.abcTimeReservations || '{}');
    Object.entries(parsedReservations || {}).forEach(([date, slots]) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(date) || !slots || typeof slots !== 'object' || Array.isArray(slots)) return;
      const perDate = {};
      Object.entries(slots).forEach(([slot, entry]) => {
        if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(String(slot)) && String(slot) !== 'ALL_DAY') return;
        if (!entry || typeof entry !== 'object' || Array.isArray(entry)) return;
        const status = String(entry.status || '');
        if (!['hold', 'booked'].includes(status)) return;
        perDate[slot] = {
          status,
          expires_at: Number(entry.expires_at) || 0,
        };
      });
      if (Object.keys(perDate).length > 0) {
        state.timeReservations[date] = perDate;
      }
    });

    const monthsRaw = Number(host.dataset.abcMonths || 12);
    const offsetRaw = Number(host.dataset.abcOffset || 0);
    const monthsToShow = Number.isFinite(monthsRaw) ? Math.max(3, Math.min(24, monthsRaw)) : 12;
    const offset = Number.isFinite(offsetRaw) ? Math.max(-12, Math.min(12, offsetRaw)) : 0;

    const base = new Date();
    base.setDate(1);
    base.setMonth(base.getMonth() + offset);
    const months = [];
    for (let i = 0; i < monthsToShow; i += 1) {
      months.push(new Date(base.getFullYear(), base.getMonth() + i, 1));
    }

    host.innerHTML = `
      <div class="abc-admin-ui">
        <div class="abc-admin-bar">
          <button type="button" class="button" data-prev>←</button>
          <strong data-month>—</strong>
          <button type="button" class="button" data-next>→</button>
        </div>
        <div class="abc-admin-tools">
          <div class="abc-admin-statuses" data-statuses>
            ${STATUSS.map(([k, l]) => `<button type="button" class="button" data-status="${k}">${l}</button>`).join('')}
          </div>
          <input type="text" data-note placeholder="${t('note_optional', 'Note (optional)')}">
          <div class="abc-admin-actions">
            <button type="button" class="button button-primary" data-apply>${t('apply_selected', 'Apply to selected')}</button>
            <button type="button" class="button" data-clear>${t('clear_selected', 'Clear selected')}</button>
            <button type="button" class="button" data-unselect>${t('unselect_all', 'Unselect all')}</button>
          </div>
          <div class="abc-admin-statuses" data-day-modes>
            <button type="button" class="button" data-day-mode="slots">${t('mode_slots', 'Hours')}</button>
            <button type="button" class="button" data-day-mode="all_day">${t('mode_all_day', 'Full day')}</button>
          </div>
          <div class="abc-admin-time-tools">
            <input type="text" data-time-slot placeholder="${t('time_placeholder', 'Time HH:MM')}">
            <button type="button" class="button" data-time-add>${t('time_add', 'Add time to selected dates')}</button>
            <button type="button" class="button" data-time-remove>${t('time_remove', 'Remove time from selected dates')}</button>
          </div>
          <div class="abc-admin-time-preview" data-time-preview></div>
        </div>
        <div class="abc-admin-weekdays">${tWeekdays.map((d)=>`<div>${d}</div>`).join('')}</div>
        <div class="abc-admin-days" data-days></div>
        <p class="abc-admin-summary" data-summary></p>
      </div>
    `;

    const monthLabel = host.querySelector('[data-month]');
    const daysGrid = host.querySelector('[data-days]');
    const statuses = host.querySelector('[data-statuses]');
    const dayModes = host.querySelector('[data-day-modes]');
    const noteInput = host.querySelector('[data-note]');
    const timeInput = host.querySelector('[data-time-slot]');
    const timePreview = host.querySelector('[data-time-preview]');
    const summary = host.querySelector('[data-summary]');
    const defaultSlotsInput = document.getElementById('abc_booking_default_time_slots');

    const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });

    const save = () => {
      const sorted = Object.keys(state.map).sort().reduce((acc, key) => {
        acc[key] = state.map[key];
        return acc;
      }, {});
      storage.value = JSON.stringify(sorted);
      storage.dispatchEvent(new Event('change', { bubbles: true }));

      if (overridesStorage) {
        const sortedOverrides = Object.keys(state.timeOverrides).sort().reduce((acc, key) => {
          acc[key] = state.timeOverrides[key];
          return acc;
        }, {});
        overridesStorage.value = JSON.stringify(sortedOverrides);
        overridesStorage.dispatchEvent(new Event('change', { bubbles: true }));
      }
      if (dayModeStorage) {
        const sortedDayModes = Object.keys(state.dayModeMap).sort().reduce((acc, key) => {
          acc[key] = state.dayModeMap[key];
          return acc;
        }, {});
        dayModeStorage.value = JSON.stringify(sortedDayModes);
        dayModeStorage.dispatchEvent(new Event('change', { bubbles: true }));
      }
    };

    const getDefaultSlots = () => parseTimeSlots(defaultSlotsInput ? defaultSlotsInput.value : '');

    const getDateSlots = (dateKey) => {
      if ((state.dayModeMap[dateKey] || (state.dayModeDefault === 'all_day' ? 'all_day' : 'slots')) === 'all_day') {
        const hasAnyReservation = state.timeReservations[dateKey] && Object.keys(state.timeReservations[dateKey]).length > 0;
        return hasAnyReservation ? [] : ['ALL_DAY'];
      }
      if (Array.isArray(state.timeOverrides[dateKey])) {
        return [...state.timeOverrides[dateKey]].sort();
      }
      return getDefaultSlots();
    };

    const getReservedSlots = (dateKey) => {
      const now = Math.floor(Date.now() / 1000);
      const perDate = state.timeReservations[dateKey];
      if (!perDate || typeof perDate !== 'object') return new Set();
      const reserved = new Set();
      Object.entries(perDate).forEach(([slot, entry]) => {
        if (!entry || typeof entry !== 'object') return;
        const status = String(entry.status || '');
        if (status === 'booked') {
          reserved.add(slot);
          return;
        }
        if (status === 'hold') {
          const expires = Number(entry.expires_at) || 0;
          if (!expires || expires > now) {
            reserved.add(slot);
          }
        }
      });
      return reserved;
    };
    const isDateLocked = (dateKey) => {
      const configured = getDateSlots(dateKey);
      const reserved = getReservedSlots(dateKey);
      if (reserved.size === 0) return false;
      const free = configured.filter((slot) => !reserved.has(slot));
      return free.length === 0;
    };

    const renderTimePreview = () => {
      if (!(timePreview instanceof HTMLElement)) return;
      if (state.selectedDates.size === 0) {
        timePreview.innerHTML = `<strong>${t('time_preview_title', 'Hours preview:')}</strong> ${t('select_one_date', 'Select one date to preview available hours.')}`;
        return;
      }
      if (state.selectedDates.size > 1) {
        timePreview.innerHTML = `<strong>${t('time_preview_title', 'Hours preview:')}</strong> ${t('select_single_date', 'Multiple dates selected. Preview works for one date at a time.')}`;
        return;
      }

      const [dateKey] = [...state.selectedDates];
      const configured = getDateSlots(dateKey);
      if (configured.length === 0) {
        timePreview.innerHTML = `<strong>${t('time_preview_title', 'Hours preview:')} ${dateKey}</strong> ${t('no_configured_hours', 'No configured hours.')}`;
        return;
      }

      const reserved = getReservedSlots(dateKey);
      const available = configured.filter((slot) => !reserved.has(slot));
      const availableHtml = available.length > 0 ? available.join(', ') : t('no_free_hours', 'No free hours');
      const reservedList = configured.filter((slot) => reserved.has(slot));
      const reservedHtml = reservedList.length > 0 ? reservedList.join(', ') : t('none', 'None');

      timePreview.innerHTML = `
        <strong>${t('time_preview_title', 'Hours preview:')} ${dateKey}</strong><br>
        <span><strong>${t('available', 'Available:')}</strong> ${availableHtml}</span><br>
        <span><strong>${t('busy_hold', 'Booked / hold:')}</strong> ${reservedHtml}</span>
      `;
    };

    const renderSummary = () => {
      const selectedCount = state.selectedDates.size;
      const total = Object.keys(state.map).length;
      const overrides = Object.keys(state.timeOverrides).length;
      summary.textContent = selectedCount > 0
        ? `${t('summary_selected_prefix', 'Selected:')} ${selectedCount} · ${t('summary_status', 'Status:')} ${state.selectedStatus}`
        : `${t('summary_idle', 'Click days and apply status. Saved:')} ${total} · ${t('summary_overrides', 'Time overrides:')} ${overrides}`;
      renderTimePreview();
    };

    const renderStatusButtons = () => {
      statuses.querySelectorAll('button[data-status]').forEach((btn) => {
        btn.classList.toggle('is-selected', btn.dataset.status === state.selectedStatus);
      });
    };

    const renderDayModeButtons = () => {
      dayModes?.querySelectorAll('button[data-day-mode]').forEach((btn) => {
        btn.classList.toggle('is-selected', btn.dataset.dayMode === state.selectedDayMode);
      });
    };

    const renderMonth = () => {
      const month = months[state.monthIndex];
      if (!month) return;

      monthLabel.textContent = monthFormatter.format(month);

      const y = month.getFullYear();
      const m = month.getMonth();
      const firstWeekday = (new Date(y, m, 1).getDay() + 6) % 7;
      const daysInMonth = new Date(y, m + 1, 0).getDate();
      const prevDays = new Date(y, m, 0).getDate();

      const cells = [];
      for (let i = 0; i < firstWeekday; i += 1) {
        const n = prevDays - firstWeekday + i + 1;
        cells.push(`<button type="button" class="abc-admin-day is-muted" disabled>${n}</button>`);
      }
      for (let d = 1; d <= daysInMonth; d += 1) {
        const key = toDateKey(new Date(y, m, d));
        const status = state.map[key]?.status || 'none';
        const locked = isDateLocked(key);
        const lockClass = locked ? ' is-locked' : '';
        const lockAttr = locked ? ' data-locked="1"' : '';
        const picked = state.selectedDates.has(key) ? ' is-picked' : '';
        cells.push(`<button type="button" class="abc-admin-day is-${status}${picked}${lockClass}" data-day="${key}"${lockAttr}>${d}</button>`);
      }
      while (cells.length < 42) {
        cells.push('<button type="button" class="abc-admin-day is-muted" disabled>·</button>');
      }
      daysGrid.innerHTML = cells.join('');
      renderSummary();
    };

    defaultSlotsInput?.addEventListener('input', () => {
      renderTimePreview();
    });

    host.querySelector('[data-prev]')?.addEventListener('click', () => {
      if (state.monthIndex > 0) {
        state.monthIndex -= 1;
        renderMonth();
      }
    });

    host.querySelector('[data-next]')?.addEventListener('click', () => {
      if (state.monthIndex < months.length - 1) {
        state.monthIndex += 1;
        renderMonth();
      }
    });

    statuses.addEventListener('click', (e) => {
      const target = e.target;
      if (!(target instanceof HTMLElement)) return;
      const btn = target.closest('button[data-status]');
      if (!(btn instanceof HTMLElement)) return;
      state.selectedStatus = btn.dataset.status || 'booked';
      renderStatusButtons();
      renderSummary();
    });

    dayModes?.addEventListener('click', (e) => {
      const target = e.target;
      if (!(target instanceof HTMLElement)) return;
      const btn = target.closest('button[data-day-mode]');
      if (!(btn instanceof HTMLElement)) return;
      const mode = String(btn.dataset.dayMode || 'slots');
      state.selectedDayMode = mode === 'all_day' ? 'all_day' : 'slots';
      renderDayModeButtons();
    });

    noteInput?.addEventListener('input', () => {
      state.note = noteInput.value.trim();
    });

    daysGrid.addEventListener('click', (e) => {
      const target = e.target;
      if (!(target instanceof HTMLElement)) return;
      const btn = target.closest('.abc-admin-day[data-day]');
      if (!(btn instanceof HTMLElement)) return;
      const key = btn.dataset.day || '';
      if (!key) return;
      if (btn.dataset.locked === '1') {
        window.alert(t('locked_date_click_info', 'This date is locked by an active reservation. To release it, go to Booking Requests and use "Release date".'));
        return;
      }

      if (state.selectedDates.has(key)) state.selectedDates.delete(key);
      else state.selectedDates.add(key);

      renderMonth();
    });

    host.querySelector('[data-apply]')?.addEventListener('click', () => {
      if (state.selectedDates.size === 0) return;
      let skipped = 0;
      state.selectedDates.forEach((key) => {
        if (isDateLocked(key)) {
          skipped += 1;
          return;
        }
        state.map[key] = { status: state.selectedStatus, note: state.note };
        state.dayModeMap[key] = state.selectedDayMode;
        if (state.selectedDayMode === 'all_day') {
          delete state.timeOverrides[key];
        }
      });
      state.selectedDates.clear();
      save();
      renderMonth();
      if (skipped > 0) {
        window.alert(fmt(t('locked_dates_skipped', 'Some selected dates are locked by active reservations and were skipped ({count}).'), { count: skipped }));
      }
    });

    host.querySelector('[data-clear]')?.addEventListener('click', () => {
      if (state.selectedDates.size === 0) return;
      let skipped = 0;
      state.selectedDates.forEach((key) => {
        if (isDateLocked(key)) {
          skipped += 1;
          return;
        }
        delete state.map[key];
      });
      state.selectedDates.clear();
      save();
      renderMonth();
      if (skipped > 0) {
        window.alert(fmt(t('locked_dates_skipped', 'Some selected dates are locked by active reservations and were skipped ({count}).'), { count: skipped }));
      }
    });

    host.querySelector('[data-unselect]')?.addEventListener('click', () => {
      state.selectedDates.clear();
      renderMonth();
    });

    host.querySelector('[data-time-add]')?.addEventListener('click', () => {
      const slot = String(timeInput?.value || '').trim();
      if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || state.selectedDates.size === 0) return;
      let skipped = 0;
      state.selectedDates.forEach((date) => {
        if (isDateLocked(date)) {
          skipped += 1;
          return;
        }
        const current = Array.isArray(state.timeOverrides[date]) ? state.timeOverrides[date] : [];
        state.timeOverrides[date] = [...new Set(current.concat([slot]))].sort();
      });
      save();
      renderSummary();
      if (skipped > 0) {
        window.alert(fmt(t('locked_dates_skipped', 'Some selected dates are locked by active reservations and were skipped ({count}).'), { count: skipped }));
      }
    });

    host.querySelector('[data-time-remove]')?.addEventListener('click', () => {
      const slot = String(timeInput?.value || '').trim();
      if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || state.selectedDates.size === 0) return;
      let skipped = 0;
      state.selectedDates.forEach((date) => {
        if (isDateLocked(date)) {
          skipped += 1;
          return;
        }
        const current = Array.isArray(state.timeOverrides[date]) ? state.timeOverrides[date] : [];
        const next = current.filter((v) => v !== slot);
        if (next.length > 0) state.timeOverrides[date] = next;
        else delete state.timeOverrides[date];
      });
      save();
      renderSummary();
      if (skipped > 0) {
        window.alert(fmt(t('locked_dates_skipped', 'Some selected dates are locked by active reservations and were skipped ({count}).'), { count: skipped }));
      }
    });

    renderStatusButtons();
    renderDayModeButtons();
    renderMonth();
  };

  const boot = () => {
    document.querySelectorAll('.abc-admin-manager').forEach(init);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
