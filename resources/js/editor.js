import domReady from '@wordpress/dom-ready';
import {
  STATUS_LABEL,
  STATUS_PRIORITY,
  normalizeRanges,
  normalizeStatusMap,
  parseDate,
  toDateKey,
} from './shared/availability-status';

const I18N = window.AnimatedEditorI18n || {};
const t = (key, fallback) => {
  const value = I18N[key];
  return typeof value === 'string' && value.trim() !== '' ? value : fallback;
};
const locale = typeof I18N.locale === 'string' && I18N.locale.trim() !== '' ? I18N.locale : 'en-US';
const tWeekdays = Array.isArray(I18N.weekdays) && I18N.weekdays.length === 7
  ? I18N.weekdays
  : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
const statusLabel = {
  ...STATUS_LABEL,
  available: t('status_available', STATUS_LABEL.available),
  tentative: t('status_tentative', STATUS_LABEL.tentative),
  booked: t('status_booked', STATUS_LABEL.booked),
};

const safeJsonParse = (value, fallback) => {
  try {
    return JSON.parse(value);
  } catch (error) {
    return fallback;
  }
};

const parseDayMapRaw = (value) => {
  const raw = String(value || '').trim();
  if (!raw) {
    return {};
  }

  const candidates = [
    raw,
    raw.replace(/&quot;/g, '"'),
    raw.replace(/\\"/g, '"'),
    raw.replace(/^"+|"+$/g, ''),
  ];

  for (let i = 0; i < candidates.length; i += 1) {
    const parsed = safeJsonParse(candidates[i], null);
    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
      return parsed;
    }
  }

  return {};
};

const parseTimeSlots = (value) => {
  const lines = String(value || '').split(/\r?\n/);
  return [...new Set(lines
    .map((line) => line.trim())
    .filter((slot) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot)))].sort();
};

const parseTimeOverridesRaw = (value) => {
  const parsed = parseDayMapRaw(value);
  const out = {};
  Object.entries(parsed).forEach(([key, slots]) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(key) || !Array.isArray(slots)) {
      return;
    }
    out[key] = [...new Set(slots
      .map((slot) => (typeof slot === 'string' ? slot.trim() : ''))
      .filter((slot) => /^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot)))].sort();
  });
  return out;
};

const parseTimeReservationsRaw = (value) => {
  const parsed = parseDayMapRaw(value);
  const out = {};
  Object.entries(parsed).forEach(([dateKey, slots]) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(dateKey) || !slots || typeof slots !== 'object' || Array.isArray(slots)) {
      return;
    }
    Object.entries(slots).forEach(([slot, entry]) => {
      if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || !entry || typeof entry !== 'object' || Array.isArray(entry)) {
        return;
      }
      const status = String(entry.status || '');
      if (!['hold', 'booked'].includes(status)) {
        return;
      }
      out[dateKey] = out[dateKey] || {};
      out[dateKey][slot] = {
        status,
        expires_at: Number(entry.expires_at) || 0,
      };
    });
  });
  return out;
};

const ensureManagerUi = (managerNode) => {
  if (managerNode.dataset.managerReady === '1') {
    return;
  }

  const scope = managerNode.closest('.layout') || managerNode.closest('.acf-fields') || document;
  const storage = scope.querySelector('[data-name="calendar_status_map"] textarea');
  const storageInput = scope.querySelector('input[name*="[calendar_status_map]"]');
  const timeOverridesStorage = scope.querySelector('[data-name="calendar_time_slots_overrides"] textarea')
    || scope.querySelector('input[name*="[calendar_time_slots_overrides]"]');
  const timeReservationsStorage = scope.querySelector('[data-name="calendar_time_slots_reservations"] textarea')
    || scope.querySelector('input[name*="[calendar_time_slots_reservations]"]');
  const monthsInput = scope.querySelector('[data-name="months_to_show"] input');
  const offsetInput = scope.querySelector('[data-name="start_month_offset"] input');
  const defaultSlotsInput = scope.querySelector('[data-name="booking_default_time_slots"] textarea')
    || scope.querySelector('[data-name="booking_default_time_slots"] input');
  let mount = managerNode.querySelector('.availability-admin-manager__mount');

  if (!mount) {
    mount = document.createElement('div');
    mount.className = 'availability-admin-manager__mount';
    managerNode.appendChild(mount);
  }

  if ((!storage && !storageInput) || !mount) {
    return;
  }

  managerNode.dataset.managerReady = '1';

  const storageField = (storage || storageInput)?.closest('.acf-field');
  if (storageField) {
    storageField.classList.add('availability-map-storage--hidden');
  }
  const timeOverridesField = timeOverridesStorage?.closest('.acf-field');
  if (timeOverridesField) {
    timeOverridesField.classList.add('availability-time-overrides-storage--hidden');
  }
  const timeReservationsField = timeReservationsStorage?.closest('.acf-field');
  if (timeReservationsField) {
    timeReservationsField.classList.add('availability-time-reservations-storage--hidden');
  }

  const getStorageRaw = () => {
    if (storage && typeof storage.value === 'string') {
      return storage.value;
    }
    if (storageInput && typeof storageInput.value === 'string') {
      return storageInput.value;
    }
    return '{}';
  };

  const setStorageRaw = (nextValue) => {
    if (storage) {
      storage.value = nextValue;
      storage.dispatchEvent(new Event('input', { bubbles: true }));
      storage.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (storageInput) {
      storageInput.value = nextValue;
      storageInput.dispatchEvent(new Event('input', { bubbles: true }));
      storageInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
  };

  const monthFormatter = new Intl.DateTimeFormat(locale, {
    month: 'long',
    year: 'numeric',
  });

  const state = {
    currentMonthIndex: 0,
    selectedStatus: 'booked',
    selectedDates: new Set(),
    noteDraft: '',
    dayMap: {},
    timeOverrides: {},
    timeReservations: {},
    months: [],
  };

  const loadDayMap = () => {
    const parsed = parseDayMapRaw(getStorageRaw());
    if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
      state.dayMap = {};
      return;
    }

    const normalized = normalizeStatusMap(parsed);

    const repeaterMap = {};
    const rawRanges = [];
    const startFields = scope.querySelectorAll(
      '[data-name="date_ranges"] [data-name="start_date"]',
    );
    startFields.forEach((startField) => {
      if (startField.closest('.acf-clone')) {
        return;
      }

      const row =
        startField.closest('.acf-row') ||
        startField.closest('.layout') ||
        startField.parentElement;
      if (!row) {
        return;
      }

      const endField = row.querySelector('[data-name="end_date"]');
      const statusField = row.querySelector('[data-name="status"]');
      const noteField = row.querySelector('[data-name="note"]');

      const startValue =
        startField.querySelector('input[type="hidden"]')?.value ||
        startField.querySelector('input')?.value ||
        '';
      const endValue =
        endField?.querySelector('input[type="hidden"]')?.value ||
        endField?.querySelector('input')?.value ||
        '';
      const statusValue =
        statusField?.querySelector('select')?.value ||
        statusField?.querySelector('input[type="hidden"]')?.value ||
        statusField?.querySelector('input')?.value ||
        '';
      const noteValue =
        noteField?.querySelector('input')?.value ||
        noteField?.querySelector('textarea')?.value ||
        '';

      rawRanges.push({
        start: startValue,
        end: endValue,
        status: statusValue,
        note: String(noteValue || '').trim(),
      });
    });

    const normalizedRanges = normalizeRanges(rawRanges);
    normalizedRanges.forEach((range) => {
      const from = new Date(range.start);
      const to = new Date(range.end);
      const cursor = new Date(from);

      while (cursor <= to) {
        const key = toDateKey(cursor);
        const existing = repeaterMap[key];
        if (
          !existing ||
          STATUS_PRIORITY[range.status] > STATUS_PRIORITY[existing.status]
        ) {
          repeaterMap[key] = {
            status: range.status,
            note: range.note,
          };
        }
        cursor.setDate(cursor.getDate() + 1);
      }
    });

    state.dayMap = { ...repeaterMap, ...normalized };
    state.repeaterCount = Object.keys(repeaterMap).length;
    state.mapCount = Object.keys(normalized).length;
  };

  const saveDayMap = () => {
    const sorted = Object.keys(state.dayMap)
      .sort()
      .reduce((acc, key) => {
        acc[key] = state.dayMap[key];
        return acc;
      }, {});
    setStorageRaw(JSON.stringify(sorted));
  };

  const getTimeOverridesRaw = () => {
    if (timeOverridesStorage && typeof timeOverridesStorage.value === 'string') {
      return timeOverridesStorage.value;
    }
    return '{}';
  };

  const setTimeOverridesRaw = (nextValue) => {
    if (!timeOverridesStorage) {
      return;
    }
    timeOverridesStorage.value = nextValue;
    timeOverridesStorage.dispatchEvent(new Event('input', { bubbles: true }));
    timeOverridesStorage.dispatchEvent(new Event('change', { bubbles: true }));
  };

  const getTimeReservationsRaw = () => {
    if (timeReservationsStorage && typeof timeReservationsStorage.value === 'string') {
      return timeReservationsStorage.value;
    }
    return '{}';
  };

  const loadTimeData = () => {
    state.timeOverrides = parseTimeOverridesRaw(getTimeOverridesRaw());
    state.timeReservations = parseTimeReservationsRaw(getTimeReservationsRaw());
  };

  const saveTimeOverrides = () => {
    const sorted = Object.keys(state.timeOverrides)
      .sort()
      .reduce((acc, key) => {
        acc[key] = state.timeOverrides[key];
        return acc;
      }, {});
    setTimeOverridesRaw(JSON.stringify(sorted));
  };

  const rebuildMonths = () => {
    const monthsToShowRaw = Number(monthsInput?.value ?? 12);
    const offsetRaw = Number(offsetInput?.value ?? 0);
    const monthsToShow = Number.isFinite(monthsToShowRaw) ? Math.max(3, Math.min(24, monthsToShowRaw)) : 12;
    const offset = Number.isFinite(offsetRaw) ? Math.max(-12, Math.min(12, offsetRaw)) : 0;

    const base = new Date();
    base.setDate(1);
    base.setMonth(base.getMonth() + offset);

    state.months = [];
    for (let i = 0; i < monthsToShow; i += 1) {
      state.months.push(new Date(base.getFullYear(), base.getMonth() + i, 1));
    }
    if (state.currentMonthIndex > state.months.length - 1) {
      state.currentMonthIndex = Math.max(0, state.months.length - 1);
    }
  };

  mount.innerHTML = `
    <div class="availability-admin-ui">
      <div class="availability-admin-ui__bar">
        <button type="button" class="button button-secondary" data-avm-prev>←</button>
        <strong data-avm-month-label>—</strong>
        <button type="button" class="button button-secondary" data-avm-next>→</button>
      </div>
      <div class="availability-admin-ui__tools">
        <div class="availability-admin-ui__statuses" data-avm-statuses>
          <button type="button" class="button" data-status=\"available\">${t('status_available', 'Available')}</button>
          <button type="button" class="button" data-status=\"tentative\">${t('status_tentative', 'Tentative')}</button>
          <button type="button" class="button" data-status=\"booked\">${t('status_booked', 'Booked')}</button>
        </div>
        <input type="text" class="availability-admin-ui__note" data-avm-note placeholder="${t('note_optional', 'Note (optional)')}">
        <div class="availability-admin-ui__actions">
          <button type="button" class="button button-primary" data-avm-apply>${t('apply_selected', 'Apply to selected')}</button>
          <button type="button" class="button" data-avm-clear>${t('clear_selected', 'Wyczyść zaznaczone')}</button>
          <button type="button" class="button" data-avm-unselect>${t('unselect_all', 'Unselect all')}</button>
        </div>
        <div class="availability-admin-ui__time-tools">
          <input type="text" class="availability-admin-ui__time-input" data-avm-time-slot placeholder="${t('time_placeholder', 'Time HH:MM')}">
          <button type="button" class="button" data-avm-time-add>${t('time_add', 'Add time to selected dates')}</button>
          <button type="button" class="button" data-avm-time-remove>${t('time_remove', 'Remove time from selected dates')}</button>
        </div>
        <div class="availability-admin-ui__time-preview" data-avm-time-preview></div>
      </div>
      <div class="availability-admin-ui__weekdays" data-avm-weekdays></div>
      <div class="availability-admin-ui__days" data-avm-days></div>
      <p class="availability-admin-ui__summary" data-avm-summary></p>
    </div>
  `;

  const monthLabel = mount.querySelector('[data-avm-month-label]');
  const weekdays = mount.querySelector('[data-avm-weekdays]');
  const daysGrid = mount.querySelector('[data-avm-days]');
  const statusesWrap = mount.querySelector('[data-avm-statuses]');
  const noteInput = mount.querySelector('[data-avm-note]');
  const summary = mount.querySelector('[data-avm-summary]');
  const timeInput = mount.querySelector('[data-avm-time-slot]');
  const timePreview = mount.querySelector('[data-avm-time-preview]');
  const timeAddButton = mount.querySelector('[data-avm-time-add]');
  const timeRemoveButton = mount.querySelector('[data-avm-time-remove]');
  const prevButton = mount.querySelector('[data-avm-prev]');
  const nextButton = mount.querySelector('[data-avm-next]');
  const applyButton = mount.querySelector('[data-avm-apply]');
  const clearButton = mount.querySelector('[data-avm-clear]');
  const unselectButton = mount.querySelector('[data-avm-unselect]');

  if (!monthLabel || !weekdays || !daysGrid || !statusesWrap || !noteInput || !summary ||
      !prevButton || !nextButton || !applyButton || !clearButton || !unselectButton ||
      !timeInput || !timePreview || !timeAddButton || !timeRemoveButton) {
    return;
  }

  weekdays.innerHTML = tWeekdays
    .map((label) => `<div>${label}</div>`)
    .join('');

  const updateStatusButtons = () => {
    statusesWrap.querySelectorAll('button[data-status]').forEach((button) => {
      button.classList.toggle('is-selected', button.dataset.status === state.selectedStatus);
    });
  };

  const getDayStatus = (dateKey) => state.dayMap[dateKey]?.status || 'none';

  const getDefaultSlots = () => parseTimeSlots(defaultSlotsInput?.value || '');

  const getDateSlots = (dateKey) => {
    if (Array.isArray(state.timeOverrides[dateKey])) {
      return [...state.timeOverrides[dateKey]].sort();
    }
    return getDefaultSlots();
  };

  const getReservedSlots = (dateKey) => {
    const now = Math.floor(Date.now() / 1000);
    const day = state.timeReservations?.[dateKey];
    const reserved = new Set();
    if (!day || typeof day !== 'object' || Array.isArray(day)) {
      return reserved;
    }
    Object.entries(day).forEach(([slot, entry]) => {
      if (!entry || typeof entry !== 'object' || Array.isArray(entry)) {
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

  const renderTimePreview = () => {
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
    const reservedList = configured.filter((slot) => reserved.has(slot));
    timePreview.innerHTML = `
      <strong>${t('time_preview_title', 'Hours preview:')} ${dateKey}</strong><br>
      <span><strong>${t('available', 'Available:')}</strong> ${available.length ? available.join(', ') : t('no_free_hours', 'No free hours')}</span><br>
      <span><strong>${t('busy_hold', 'Booked / hold:')}</strong> ${reservedList.length ? reservedList.join(', ') : t('none', 'None')}</span>
    `;
  };

  const renderSummary = () => {
    const selectedCount = state.selectedDates.size;
    const totalMapped = Object.keys(state.dayMap).length;
    summary.textContent = selectedCount > 0
      ? `${t('summary_selected_prefix', 'Selected dates:')} ${selectedCount} · ${t('summary_status', 'Status:')} ${statusLabel[state.selectedStatus]}`
      : `${t('summary_idle', 'Click dates in the calendar, then choose status and apply. Saved:')} ${totalMapped} (${t('summary_map', 'map')}: ${state.mapCount || 0}, ${t('summary_ranges', 'ranges')}: ${state.repeaterCount || 0})`;
    renderTimePreview();
  };

  const alignMonthToExistingStatuses = () => {
    const keys = Object.keys(state.dayMap).sort();
    if (!keys.length || !state.months.length) {
      return;
    }

    const firstDate = parseDate(keys[0]);
    if (!firstDate) {
      return;
    }

    const targetYear = firstDate.getFullYear();
    const targetMonth = firstDate.getMonth();
    const index = state.months.findIndex(
      (monthDate) => monthDate.getFullYear() === targetYear && monthDate.getMonth() === targetMonth,
    );

    if (index >= 0) {
      state.currentMonthIndex = index;
    }
  };

  const renderMonth = () => {
    const month = state.months[state.currentMonthIndex];
    if (!month) {
      return;
    }

    monthLabel.textContent = monthFormatter.format(month);
    prevButton.disabled = state.currentMonthIndex === 0;
    nextButton.disabled = state.currentMonthIndex >= state.months.length - 1;

    const year = month.getFullYear();
    const monthIdx = month.getMonth();
    const firstWeekday = (new Date(year, monthIdx, 1).getDay() + 6) % 7;
    const daysInMonth = new Date(year, monthIdx + 1, 0).getDate();
    const prevDays = new Date(year, monthIdx, 0).getDate();

    const cells = [];
    for (let i = 0; i < firstWeekday; i += 1) {
      const dayNum = prevDays - firstWeekday + i + 1;
      cells.push(`<button type=\"button\" class=\"availability-admin-day is-muted\" disabled>${dayNum}</button>`);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
      const date = new Date(year, monthIdx, day);
      const key = toDateKey(date);
      const status = getDayStatus(key);
      const selectedClass = state.selectedDates.has(key) ? ' is-picked' : '';
      cells.push(`<button type=\"button\" class=\"availability-admin-day is-${status}${selectedClass}\" data-date=\"${key}\">${day}</button>`);
    }

    const remaining = 42 - cells.length;
    for (let i = 1; i <= remaining; i += 1) {
      cells.push(`<button type=\"button\" class=\"availability-admin-day is-muted\" disabled>${i}</button>`);
    }

    daysGrid.innerHTML = cells.join('');
    renderSummary();
  };

  statusesWrap.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }
    const statusButton = target.closest('button[data-status]');
    if (!(statusButton instanceof HTMLElement)) {
      return;
    }
    const status = statusButton.dataset.status;
    if (status && Object.prototype.hasOwnProperty.call(statusLabel, status)) {
      state.selectedStatus = status;
      updateStatusButtons();
      renderSummary();
    }
  });

  daysGrid.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
      return;
    }
    const dayButton = target.closest('.availability-admin-day[data-date]');
    if (!(dayButton instanceof HTMLElement)) {
      return;
    }
    const key = dayButton.dataset.date;
    if (!key) {
      return;
    }
    if (state.selectedDates.has(key)) {
      state.selectedDates.delete(key);
    } else {
      state.selectedDates.add(key);
    }
    renderMonth();
  });

  applyButton.addEventListener('click', () => {
    if (state.selectedDates.size === 0) {
      return;
    }
    const note = String(noteInput.value || '').trim();
    state.selectedDates.forEach((dateKey) => {
      state.dayMap[dateKey] = {
        status: state.selectedStatus,
        note,
      };
    });
    saveDayMap();
    renderMonth();
  });

  clearButton.addEventListener('click', () => {
    if (state.selectedDates.size === 0) {
      return;
    }
    state.selectedDates.forEach((dateKey) => {
      delete state.dayMap[dateKey];
    });
    saveDayMap();
    renderMonth();
  });

  unselectButton.addEventListener('click', () => {
    state.selectedDates.clear();
    renderMonth();
  });

  timeAddButton.addEventListener('click', () => {
    const slot = String(timeInput.value || '').trim();
    if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || state.selectedDates.size === 0) {
      return;
    }
    state.selectedDates.forEach((dateKey) => {
      const current = Array.isArray(state.timeOverrides[dateKey]) ? state.timeOverrides[dateKey] : [];
      state.timeOverrides[dateKey] = [...new Set(current.concat([slot]))].sort();
    });
    saveTimeOverrides();
    renderSummary();
  });

  timeRemoveButton.addEventListener('click', () => {
    const slot = String(timeInput.value || '').trim();
    if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(slot) || state.selectedDates.size === 0) {
      return;
    }
    state.selectedDates.forEach((dateKey) => {
      const current = Array.isArray(state.timeOverrides[dateKey]) ? state.timeOverrides[dateKey] : [];
      const next = current.filter((v) => v !== slot);
      if (next.length > 0) {
        state.timeOverrides[dateKey] = next;
      } else {
        delete state.timeOverrides[dateKey];
      }
    });
    saveTimeOverrides();
    renderSummary();
  });

  prevButton.addEventListener('click', () => {
    if (state.currentMonthIndex > 0) {
      state.currentMonthIndex -= 1;
      renderMonth();
    }
  });

  nextButton.addEventListener('click', () => {
    if (state.currentMonthIndex < state.months.length - 1) {
      state.currentMonthIndex += 1;
      renderMonth();
    }
  });

  monthsInput?.addEventListener('change', () => {
    rebuildMonths();
    renderMonth();
  });
  offsetInput?.addEventListener('change', () => {
    rebuildMonths();
    renderMonth();
  });

  const renderAll = () => {
    loadDayMap();
    loadTimeData();
    rebuildMonths();
    alignMonthToExistingStatuses();
    updateStatusButtons();
    renderMonth();
  };

  renderAll();
  window.setTimeout(renderAll, 80);
  window.setTimeout(renderAll, 260);

  const watchSelectors = [
    '[data-name="date_ranges"] [data-name="start_date"] input',
    '[data-name="date_ranges"] [data-name="end_date"] input',
    '[data-name="date_ranges"] [data-name="status"] select',
    '[data-name="date_ranges"] [data-name="note"] input',
    '[data-name="date_ranges"] [data-name="note"] textarea',
    '[data-name="months_to_show"] input',
    '[data-name="start_month_offset"] input',
    '[data-name="booking_default_time_slots"] textarea',
    '[data-name="booking_default_time_slots"] input',
  ];

  scope.addEventListener('change', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) {
      return;
    }
    if (watchSelectors.some((selector) => target.matches(selector))) {
      window.setTimeout(renderAll, 20);
    }
  });
};

const initAvailabilityManagers = () => {
  const managers = document.querySelectorAll('[data-availability-admin-manager]');
  managers.forEach((manager) => ensureManagerUi(manager));

  // Fallback: when ACF message field HTML is not rendered, inject manager host
  // right after the storage field so editor tool is still available.
  const storageFields = document.querySelectorAll('[data-name="calendar_status_map"]');
  storageFields.forEach((storageField) => {
    const scope = storageField.closest('.layout') || storageField.closest('.acf-fields') || storageField.parentElement;
    if (!scope) {
      return;
    }

    const hasNativeManager = scope.querySelector('[data-availability-admin-manager]');
    if (hasNativeManager) {
      return;
    }

    let host = scope.querySelector('.availability-admin-manager-fallback');
    if (!host) {
      host = document.createElement('div');
      host.className = 'acf-field availability-admin-manager-fallback';
      host.setAttribute('data-name', 'calendar_visual_manager_fallback');
      host.innerHTML = '<div class="availability-admin-manager" data-availability-admin-manager><div class="availability-admin-manager__mount"></div></div>';
      storageField.insertAdjacentElement('afterend', host);
    }

    const managerNode = host.querySelector('[data-availability-admin-manager]');
    if (managerNode) {
      ensureManagerUi(managerNode);
    }
  });
};

domReady(() => {
  initAvailabilityManagers();

  if (window.acf && typeof window.acf.addAction === 'function') {
    window.acf.addAction('append', initAvailabilityManagers);
    window.acf.addAction('ready', initAvailabilityManagers);
  }
});
