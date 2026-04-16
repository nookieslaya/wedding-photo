@php
    $sectionTitle = trim((string) ($module['section_title'] ?? ''));
    $sectionSubtitle = trim((string) ($module['section_subtitle'] ?? ''));
    $heading = trim((string) ($module['heading'] ?? ''));
    $description = trim((string) ($module['description'] ?? ''));
    $monthsToShowRaw = $module['months_to_show'] ?? 12;
    $monthsToShow = is_numeric($monthsToShowRaw) ? (int) $monthsToShowRaw : 12;
    $monthsToShow = max(3, min(24, $monthsToShow));

    $startMonthOffsetRaw = $module['start_month_offset'] ?? 0;
    $startMonthOffset = is_numeric($startMonthOffsetRaw) ? (int) $startMonthOffsetRaw : 0;
    $startMonthOffset = max(-12, min(12, $startMonthOffset));

    $ctaButton = $module['cta_button'] ?? null;

    $rangesRaw = $module['date_ranges'] ?? [];
    $rangesRaw = is_array($rangesRaw) ? $rangesRaw : [];
    $ranges = [];
    foreach ($rangesRaw as $row) {
        $startDate = trim((string) ($row['start_date'] ?? ''));
        $endDate = trim((string) ($row['end_date'] ?? ''));
        $status = trim((string) ($row['status'] ?? ''));
        $note = trim((string) ($row['note'] ?? ''));
        if ($startDate === '' || $endDate === '') {
            continue;
        }
        if (!in_array($status, ['available', 'tentative', 'booked'], true)) {
            $status = 'available';
        }
        $ranges[] = [
            'start' => $startDate,
            'end' => $endDate,
            'status' => $status,
            'note' => $note,
        ];
    }

    $statusMapRaw = trim((string) ($module['calendar_status_map'] ?? ''));
    $statusMapDecoded = json_decode($statusMapRaw !== '' ? $statusMapRaw : '{}', true);
    $statusMapDecoded = is_array($statusMapDecoded) ? $statusMapDecoded : [];
    $statusMap = [];
    foreach ($statusMapDecoded as $dayKey => $dayValue) {
        if (!is_string($dayKey) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dayKey)) {
            continue;
        }
        $status = '';
        $note = '';
        if (is_string($dayValue)) {
            $status = $dayValue;
        } elseif (is_array($dayValue)) {
            $status = trim((string) ($dayValue['status'] ?? ''));
            $note = trim((string) ($dayValue['note'] ?? ''));
        }

        if (!in_array($status, ['available', 'tentative', 'booked'], true)) {
            continue;
        }

        $statusMap[$dayKey] = [
            'status' => $status,
            'note' => $note,
        ];
    }
@endphp

<section class="availability-calendar-module relative bg-black text-white" data-availability-calendar-module>
    <div class="mx-auto w-full max-w-[1900px] px-4 py-16 md:px-10 md:pt-32 md:pb-24">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-[32%_1fr] md:gap-14">
            <aside class="md:sticky md:top-28 md:self-start">
                @if ($sectionTitle !== '')
                    <p class="text-[0.7rem] uppercase tracking-[0.24em] text-white/55 md:text-[0.74rem]">{{ $sectionTitle }}</p>
                @endif
                @if ($sectionSubtitle !== '')
                    <p class="mt-2 text-[0.72rem] uppercase tracking-[0.16em] text-white/36">{{ $sectionSubtitle }}</p>
                @endif

                @if ($heading !== '')
                    <h2 class="mt-7 text-[clamp(1.65rem,3.1vw,3.15rem)] font-semibold uppercase leading-[0.94] tracking-[0.01em]">
                        {{ $heading }}
                    </h2>
                @endif

                @if ($description !== '')
                    <p class="mt-6 max-w-[40ch] text-sm leading-relaxed text-white/72 md:text-base">
                        {!! nl2br(e($description)) !!}
                    </p>
                @endif

                @if (!empty($ctaButton['url']) && !empty($ctaButton['title']))
                    <a href="{{ $ctaButton['url'] }}" target="{{ $ctaButton['target'] ?? '_self' }}"
                        class="mt-7 inline-flex items-center gap-2 border border-white/25 px-4 py-2 text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-white no-underline transition hover:border-[#ef4444] hover:text-[#ef4444]"
                        style="text-decoration:none !important;">
                        <span>{{ $ctaButton['title'] }}</span>
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 7h10v10M17 7L7 17" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                @endif

                <div class="mt-8 space-y-2.5 text-[0.72rem] uppercase tracking-[0.14em]">
                    <div class="flex items-center gap-2.5 text-white/78">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#22c55e]"></span>
                        <span>Dostępny</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white/78">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#f59e0b]"></span>
                        <span>Wstępna Rezerwacja</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white/78">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#ef4444]"></span>
                        <span>Zajęty</span>
                    </div>
                </div>
            </aside>

            <div class="availability-calendar-shell rounded-[4px] border border-white/12 bg-white/[0.03] p-4 md:p-6"
                data-availability-calendar
                data-availability-ranges='@json($ranges)'
                data-availability-map='@json($statusMap)'
                data-availability-months="{{ $monthsToShow }}"
                data-availability-offset="{{ $startMonthOffset }}">
                <div class="flex items-center justify-between gap-3 border-b border-white/12 pb-4">
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-sm border border-white/22 text-white/85 transition hover:border-white hover:text-white"
                        data-availability-prev aria-label="Poprzedni miesiąc">
                        ←
                    </button>
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-white/90"
                        data-availability-month-label>
                        —
                    </h3>
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-sm border border-white/22 text-white/85 transition hover:border-white hover:text-white"
                        data-availability-next aria-label="Następny miesiąc">
                        →
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[0.62rem] uppercase tracking-[0.12em] text-white/46"
                    data-availability-weekdays></div>
                <div class="mt-2 grid grid-cols-7 gap-1" data-availability-days></div>

                <p class="mt-4 min-h-5 text-[0.72rem] uppercase tracking-[0.1em] text-white/62" data-availability-note>
                    Kliknij dzień, aby zobaczyć status.
                </p>
            </div>
        </div>
    </div>
</section>
