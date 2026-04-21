@php
    $currentLocale = strtolower((string) determine_locale());
    $isPolishLocale = str_starts_with($currentLocale, 'pl');
    $tr = static fn (string $en, string $pl): string => $isPolishLocale ? $pl : $en;

    if (function_exists('\App\animated_cleanup_expired_booking_holds')) {
        \App\animated_cleanup_expired_booking_holds(20);
    }

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
    $moduleIndex = isset($moduleIndex) && is_numeric($moduleIndex) ? (int) $moduleIndex : 0;
    $postId = get_the_ID();
    $moduleId = 'availability-' . $postId . '-' . $moduleIndex;
    $themePreset = trim((string) ($module['theme_preset'] ?? 'dark'));
    if (!in_array($themePreset, ['dark', 'graphite', 'smoke'], true)) {
        $themePreset = 'dark';
    }
    $backgroundStyle = trim((string) ($module['background_style'] ?? 'gradient'));
    if (!in_array($backgroundStyle, ['gradient', 'plain', 'mesh'], true)) {
        $backgroundStyle = 'gradient';
    }
    $fontPreset = trim((string) ($module['font_preset'] ?? 'modern'));
    if (!in_array($fontPreset, ['modern', 'editorial', 'mono'], true)) {
        $fontPreset = 'modern';
    }
    $bookingHoldMinutesRaw = $module['booking_hold_minutes'] ?? 2880;
    $bookingHoldMinutes = is_numeric($bookingHoldMinutesRaw) ? (int) $bookingHoldMinutesRaw : 2880;
    $bookingHoldMinutes = max(1, min(10080, $bookingHoldMinutes));
    $bookingHoldHours = round($bookingHoldMinutes / 60, 2);
    $bookingHoldHoursLabel = rtrim(rtrim(number_format($bookingHoldHours, 2, '.', ''), '0'), '.');

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

        $statusEntry = [
            'status' => $status,
            'note' => $note,
        ];
        if (is_array($dayValue) && isset($dayValue['hold_expires_at']) && is_numeric($dayValue['hold_expires_at'])) {
            $statusEntry['hold_expires_at'] = (int) $dayValue['hold_expires_at'];
        }

        $statusMap[$dayKey] = $statusEntry;
    }

    $bookingHoldNoticeText = trim((string) ($module['booking_hold_notice_text'] ?? ''));
    if ($bookingHoldNoticeText === '') {
        $bookingHoldNoticeText = 'Rezerwacja terminu jest wstępna i trwa {hours}h ({minutes} min). Po tym czasie termin wraca do puli wolnych, jeśli nie zostanie potwierdzony.';
    }
    $bookingHoldNoticeText = str_replace(
        ['{hours}', '{minutes}'],
        [$bookingHoldHoursLabel, (string) $bookingHoldMinutes],
        $bookingHoldNoticeText,
    );

    $bookingSuccessMessage = trim((string) ($module['booking_success_message'] ?? ''));
    if ($bookingSuccessMessage === '') {
        $bookingSuccessMessage = 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na {hours}h.';
    }
    $bookingSuccessMessage = str_replace(
        ['{hours}', '{minutes}'],
        [$bookingHoldHoursLabel, (string) $bookingHoldMinutes],
        $bookingSuccessMessage,
    );

    $bookingErrorMessage = trim((string) ($module['booking_error_message'] ?? ''));
    if ($bookingErrorMessage === '') {
        $bookingErrorMessage = 'Nie udało się wysłać zgłoszenia. Sprawdź dane i spróbuj ponownie.';
    }

    $bookingFormHeading = trim((string) ($module['booking_form_heading'] ?? ''));
    if ($bookingFormHeading === '') {
        $bookingFormHeading = 'Zarezerwuj termin';
    }

    $bookingFormSubmitLabel = trim((string) ($module['booking_form_submit_label'] ?? ''));
    if ($bookingFormSubmitLabel === '') {
        $bookingFormSubmitLabel = 'Wyślij rezerwację';
    }

    $bookingConsentLabel = trim((string) ($module['booking_consent_label'] ?? ''));
    if ($bookingConsentLabel === '') {
        $bookingConsentLabel = 'Zapoznałam/em się z moim stylem pracy i akceptuję kontakt zwrotny.';
    }

    $bookingOptionsRaw = $module['booking_options'] ?? [];
    $bookingOptionsRaw = is_array($bookingOptionsRaw) ? $bookingOptionsRaw : [];
    $bookingOptions = [];
    foreach ($bookingOptionsRaw as $optionRow) {
        $optionLabel = trim((string) ($optionRow['label'] ?? ''));
        if ($optionLabel === '') {
            continue;
        }
        $bookingOptions[] = $optionLabel;
    }

    $defaultTimeSlotsRaw = preg_split('/\r\n|\r|\n/', (string) ($module['booking_default_time_slots'] ?? '')) ?: [];
    $defaultTimeSlots = [];
    foreach ($defaultTimeSlotsRaw as $slotLine) {
        $slot = trim((string) $slotLine);
        if ($slot !== '' && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
            $defaultTimeSlots[] = $slot;
        }
    }
    $defaultTimeSlots = array_values(array_unique($defaultTimeSlots));
    sort($defaultTimeSlots);

    $timeOverridesRaw = trim((string) ($module['calendar_time_slots_overrides'] ?? '{}'));
    $timeOverridesDecoded = json_decode($timeOverridesRaw !== '' ? $timeOverridesRaw : '{}', true);
    $timeOverridesDecoded = is_array($timeOverridesDecoded) ? $timeOverridesDecoded : [];

    $timeReservationsRaw = trim((string) ($module['calendar_time_slots_reservations'] ?? '{}'));
    $timeReservationsDecoded = json_decode($timeReservationsRaw !== '' ? $timeReservationsRaw : '{}', true);
    $timeReservationsDecoded = is_array($timeReservationsDecoded) ? $timeReservationsDecoded : [];
    if (function_exists('\App\animated_collect_live_time_reservations')) {
        $liveReservations = \App\animated_collect_live_time_reservations((int) $postId, (int) $moduleIndex);
        if (is_array($liveReservations)) {
            foreach ($liveReservations as $dateKey => $slotsMap) {
                if (!is_array($slotsMap)) {
                    continue;
                }
                foreach ($slotsMap as $slotKey => $slotEntry) {
                    $timeReservationsDecoded[$dateKey][$slotKey] = $slotEntry;
                }
            }
        }
    }

    $bookingResultModule = trim((string) request()->query('booking_module', ''));
    $bookingResult = trim((string) request()->query('booking_request', ''));
    $bookingResultMessage = trim((string) request()->query('booking_message', ''));
    $showBookingResult = $bookingResultModule === $moduleId && in_array($bookingResult, ['success', 'error'], true);
    $calendarI18n = [
        'locale' => $isPolishLocale ? 'pl-PL' : 'en-US',
        'status_available' => $tr('Available', 'Dostępny'),
        'status_tentative' => $tr('Tentative booking', 'Wstępna rezerwacja'),
        'status_booked' => $tr('Booked', 'Zajęty'),
        'status_none' => $tr('No information', 'Brak informacji'),
        'weekdays' => $isPolishLocale ? ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'choose_hour' => $tr('Choose time', 'Wybierz godzinę'),
        'no_data_month' => $tr('No data for selected month.', 'Brak danych dla wybranego miesiąca.'),
        'click_day_status' => $tr('Click a day to see status.', 'Kliknij dzień, aby zobaczyć status.'),
        'hours_prefix' => $tr('Hours:', 'Godziny:'),
    ];
@endphp

<section class="availability-calendar-module availability-theme-{{ $themePreset }} availability-bg-{{ $backgroundStyle }} availability-font-{{ $fontPreset }} relative text-white" data-availability-calendar-module>
    <div class="mx-auto w-full max-w-[1900px] px-4 pb-16 pt-24 md:px-10 md:pt-32 md:pb-24">
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
                        <span>{{ $tr('Available', 'Dostępny') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white/78">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#f59e0b]"></span>
                        <span>{{ $tr('Tentative Booking', 'Wstępna Rezerwacja') }}</span>
                    </div>
                    <div class="flex items-center gap-2.5 text-white/78">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#ef4444]"></span>
                        <span>{{ $tr('Booked', 'Zajęty') }}</span>
                    </div>
                </div>
            </aside>

            <div class="availability-calendar-shell rounded-[4px] border border-white/12 bg-white/[0.03] p-4 md:p-6"
                data-availability-calendar
                data-availability-module-id="{{ $moduleId }}"
                data-availability-ranges='@json($ranges)'
                data-availability-map='@json($statusMap)'
                data-availability-time-default='@json($defaultTimeSlots)'
                data-availability-time-overrides='@json($timeOverridesDecoded)'
                data-availability-time-reservations='@json($timeReservationsDecoded)'
                data-availability-i18n='@json($calendarI18n)'
                data-availability-months="{{ $monthsToShow }}"
                data-availability-offset="{{ $startMonthOffset }}">
                <div class="flex items-center justify-between gap-3 border-b border-white/12 pb-4">
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-sm border border-white/22 text-white/85 transition hover:border-white hover:text-white"
                        data-availability-prev aria-label="{{ $tr('Previous month', 'Poprzedni miesiąc') }}">
                        ←
                    </button>
                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-white/90"
                        data-availability-month-label>
                        —
                    </h3>
                    <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-sm border border-white/22 text-white/85 transition hover:border-white hover:text-white"
                        data-availability-next aria-label="{{ $tr('Next month', 'Następny miesiąc') }}">
                        →
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-[0.62rem] uppercase tracking-[0.12em] text-white/46"
                    data-availability-weekdays></div>
                <div class="mt-2 grid grid-cols-7 gap-1" data-availability-days></div>

                <p class="mt-4 min-h-5 text-[0.72rem] uppercase tracking-[0.1em] text-white/62" data-availability-note>
                    {{ __('Kliknij dzień, aby zobaczyć status.', 'sage') }}
                </p>

                @if ($showBookingResult)
                    <div
                        class="mt-4 rounded-sm border px-3 py-2 text-sm {{ $bookingResult === 'success' ? 'border-[#22c55e]/60 bg-[#22c55e]/10 text-white/90' : 'border-[#ef4444]/60 bg-[#ef4444]/10 text-white/90' }}">
                        {{ $bookingResultMessage !== '' ? $bookingResultMessage : ($bookingResult === 'success' ? $bookingSuccessMessage : $bookingErrorMessage) }}
                    </div>
                @endif

                <div class="availability-booking-panel mt-5 rounded-sm border border-white/14 bg-black/35 p-4 md:p-5"
                    data-availability-booking-panel hidden>
                    <h4 class="text-sm font-semibold uppercase tracking-[0.16em] text-white/90">{{ $bookingFormHeading }}</h4>
                    <p class="mt-2 text-xs leading-relaxed text-white/72">{{ $bookingHoldNoticeText }}</p>

                    @if (!empty($bookingOptions))
                        <div class="availability-booking-cta mt-4" data-availability-booking-cta>
                            <button type="button" class="availability-booking-open" data-availability-booking-open>
                                {{ __('Zarezerwuj', 'sage') }}
                            </button>
                        </div>

                        <form class="mt-4 grid grid-cols-1 gap-3 md:gap-4"
                            method="post"
                            action="{{ esc_url(admin_url('admin-post.php')) }}"
                            data-availability-booking-form
                            hidden>
                            <input type="hidden" name="action" value="animated_submit_booking_request">
                            <input type="hidden" name="booking_post_id" value="{{ $postId }}">
                            <input type="hidden" name="booking_module_index" value="{{ $moduleIndex }}">
                            <input type="hidden" name="booking_module_id" value="{{ $moduleId }}">
                            <input type="hidden" name="booking_honeypot" value="">
                            @php(wp_nonce_field('animated_submit_booking_request', 'booking_nonce'))

                            <label class="availability-booking-field">
                                <span>{{ __('Data wydarzenia', 'sage') }} *</span>
                                <input type="text" name="booking_date_display" value="" readonly data-availability-booking-date-display>
                                <input type="hidden" name="booking_date" value="" data-availability-booking-date>
                            </label>

                            <label class="availability-booking-field">
                                <span>Godzina *</span>
                                <select name="booking_time" required data-availability-booking-time>
                                    <option value="">{{ __('Wybierz godzinę', 'sage') }}</option>
                                </select>
                            </label>

                            <label class="availability-booking-field">
                                <span>{{ __('Imię i nazwisko', 'sage') }} *</span>
                                <input type="text" name="booking_full_name" required maxlength="120" autocomplete="name">
                            </label>

                            <label class="availability-booking-field">
                                <span>{{ __('Usługa / Pakiet', 'sage') }} *</span>
                                <select name="booking_option" required>
                                    <option value="">{{ __('Wybierz', 'sage') }}</option>
                                    @foreach ($bookingOptions as $optionLabel)
                                        <option value="{{ $optionLabel }}">{{ $optionLabel }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="availability-booking-field">
                                <span>{{ __('Adres e-mail', 'sage') }} *</span>
                                <input type="email" name="booking_email" required maxlength="190" autocomplete="email">
                            </label>

                            <label class="availability-booking-field">
                                <span>{{ __('Numer telefonu', 'sage') }} *</span>
                                <input type="tel" name="booking_phone" required maxlength="50" autocomplete="tel">
                            </label>

                            <label class="availability-booking-field">
                                <span>{{ __('Wiadomość', 'sage') }}</span>
                                <textarea name="booking_message" rows="4" maxlength="1500"></textarea>
                            </label>

                            <label class="availability-booking-consent">
                                <input type="checkbox" name="booking_consent" value="1" required>
                                <span>{{ $bookingConsentLabel }} *</span>
                            </label>

                            <button type="submit" class="availability-booking-submit">
                                {{ $bookingFormSubmitLabel }}
                            </button>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-white/70">
                            {{ __('Uzupełnij opcje „Usługa / Pakiet” w ustawieniach modułu, aby włączyć formularz rezerwacji.', 'sage') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
