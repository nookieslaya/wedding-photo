(() => {
  const STATUSS = [
    ['available', 'Dostępny'],
    ['tentative', 'Wstępna'],
    ['booked', 'Zajęty'],
  ];

  const parseMap = (raw) => {
    try {
      const parsed = JSON.parse(String(raw || '{}'));
      return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
    } catch (_e) {
      return {};
    }
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
    if (!storage) return;

    let state = {
      map: normalize(parseMap(storage.value || host.dataset.abcStatusMap || '{}')),
      monthIndex: 0,
      selectedStatus: 'booked',
      selectedDates: new Set(),
      note: '',
    };

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
          <input type="text" data-note placeholder="Notatka (opcjonalnie)">
          <div class="abc-admin-actions">
            <button type="button" class="button button-primary" data-apply>Zastosuj do zaznaczonych</button>
            <button type="button" class="button" data-clear>Wyczyść zaznaczone</button>
            <button type="button" class="button" data-unselect>Odznacz wszystko</button>
          </div>
        </div>
        <div class="abc-admin-weekdays">${['Pon','Wt','Śr','Czw','Pt','Sob','Nd'].map((d)=>`<div>${d}</div>`).join('')}</div>
        <div class="abc-admin-days" data-days></div>
        <p class="abc-admin-summary" data-summary></p>
      </div>
    `;

    const monthLabel = host.querySelector('[data-month]');
    const daysGrid = host.querySelector('[data-days]');
    const statuses = host.querySelector('[data-statuses]');
    const noteInput = host.querySelector('[data-note]');
    const summary = host.querySelector('[data-summary]');

    const monthFormatter = new Intl.DateTimeFormat('pl-PL', { month: 'long', year: 'numeric' });

    const save = () => {
      const sorted = Object.keys(state.map).sort().reduce((acc, key) => {
        acc[key] = state.map[key];
        return acc;
      }, {});
      storage.value = JSON.stringify(sorted);
      storage.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const renderSummary = () => {
      const selectedCount = state.selectedDates.size;
      const total = Object.keys(state.map).length;
      summary.textContent = selectedCount > 0
        ? `Zaznaczono: ${selectedCount} · Status: ${state.selectedStatus}`
        : `Kliknij dni i zastosuj status. Zapisane: ${total}`;
    };

    const renderStatusButtons = () => {
      statuses.querySelectorAll('button[data-status]').forEach((btn) => {
        btn.classList.toggle('is-selected', btn.dataset.status === state.selectedStatus);
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
        const picked = state.selectedDates.has(key) ? ' is-picked' : '';
        cells.push(`<button type="button" class="abc-admin-day is-${status}${picked}" data-day="${key}">${d}</button>`);
      }
      while (cells.length < 42) {
        cells.push('<button type="button" class="abc-admin-day is-muted" disabled>·</button>');
      }
      daysGrid.innerHTML = cells.join('');
      renderSummary();
    };

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

      if (state.selectedDates.has(key)) state.selectedDates.delete(key);
      else state.selectedDates.add(key);

      renderMonth();
    });

    host.querySelector('[data-apply]')?.addEventListener('click', () => {
      if (state.selectedDates.size === 0) return;
      state.selectedDates.forEach((key) => {
        state.map[key] = { status: state.selectedStatus, note: state.note };
      });
      state.selectedDates.clear();
      save();
      renderMonth();
    });

    host.querySelector('[data-clear]')?.addEventListener('click', () => {
      if (state.selectedDates.size === 0) return;
      state.selectedDates.forEach((key) => {
        delete state.map[key];
      });
      state.selectedDates.clear();
      save();
      renderMonth();
    });

    host.querySelector('[data-unselect]')?.addEventListener('click', () => {
      state.selectedDates.clear();
      renderMonth();
    });

    renderStatusButtons();
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
