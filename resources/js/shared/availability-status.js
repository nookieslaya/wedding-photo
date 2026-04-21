export const STATUS_PRIORITY = {
  available: 1,
  tentative: 2,
  booked: 3,
};

export const STATUS_LABEL = {
  available: 'Available',
  tentative: 'Tentative',
  booked: 'Booked',
  none: 'No information',
};

export const parseDate = (value) => {
  if (!value || typeof value !== 'string') {
    return null;
  }

  const raw = value.trim();
  if (raw === '') {
    return null;
  }

  let match = raw.match(/^(\d{4})[-/](\d{2})[-/](\d{2})$/);
  if (match) {
    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
  }

  // ACF hidden datepicker format: yyyymmdd
  match = raw.match(/^(\d{4})(\d{2})(\d{2})$/);
  if (match) {
    return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
  }

  match = raw.match(/^(\d{2})[./-](\d{2})[./-](\d{4})$/);
  if (match) {
    return new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
  }

  const parsed = new Date(raw);
  if (!Number.isNaN(parsed.getTime())) {
    return new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate());
  }

  return null;
};

export const toDateKey = (date) => {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
};

export const normalizeRanges = (raw) => {
  if (!Array.isArray(raw)) {
    return [];
  }

  return raw
    .map((item) => {
      const start = parseDate(item?.start);
      const end = parseDate(item?.end);
      const status = item?.status;
      const note = typeof item?.note === 'string' ? item.note.trim() : '';

      if (!start || !end) {
        return null;
      }

      if (!Object.prototype.hasOwnProperty.call(STATUS_PRIORITY, status)) {
        return null;
      }

      const safeStart = start <= end ? start : end;
      const safeEnd = start <= end ? end : start;

      return {
        start: safeStart,
        end: safeEnd,
        status,
        note,
      };
    })
    .filter(Boolean);
};

export const normalizeStatusMap = (raw) => {
  if (!raw || typeof raw !== 'object' || Array.isArray(raw)) {
    return {};
  }

  const map = {};
  Object.entries(raw).forEach(([key, value]) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(key)) {
      return;
    }

    if (typeof value === 'string') {
      if (Object.prototype.hasOwnProperty.call(STATUS_PRIORITY, value)) {
        map[key] = { status: value, note: '' };
      }
      return;
    }

    if (value && typeof value === 'object') {
      const status = value.status;
      const note = typeof value.note === 'string' ? value.note.trim() : '';
      const holdExpiresAtRaw = value.hold_expires_at;
      const holdExpiresAt = Number.isFinite(Number(holdExpiresAtRaw)) ? Number(holdExpiresAtRaw) : 0;

      if (status === 'tentative' && holdExpiresAt > 0 && holdExpiresAt <= Math.floor(Date.now() / 1000)) {
        map[key] = { status: 'available', note: '' };
        return;
      }

      if (Object.prototype.hasOwnProperty.call(STATUS_PRIORITY, status)) {
        map[key] = { status, note };
      }
    }
  });

  return map;
};

export const resolveDayStatus = (date, ranges, statusMap) => {
  const key = toDateKey(date);
  const mapped = statusMap?.[key];
  if (mapped) {
    return mapped;
  }

  let selected = null;

  for (let i = 0; i < ranges.length; i += 1) {
    const range = ranges[i];

    if (date >= range.start && date <= range.end) {
      if (!selected || STATUS_PRIORITY[range.status] > STATUS_PRIORITY[selected.status]) {
        selected = range;
      }
    }
  }

  return selected
    ? { status: selected.status, note: selected.note }
    : { status: 'none', note: '' };
};
