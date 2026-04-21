<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Booking_Trait')) {
trait Rdev_Calendar_Booking_Trait {
    public static function handle_booking_submit(): void {
        $calendar_id = isset($_POST['abc_calendar_id']) ? absint($_POST['abc_calendar_id']) : 0;
        $redirect = wp_get_referer() ?: home_url('/');
        $redirect = is_string($redirect) && $redirect !== '' ? $redirect : home_url('/');

        $go = static function (int $calendar, string $state, string $msg) use ($redirect): void {
            $url = add_query_arg([
                'abc_calendar' => $calendar,
                'abc_booking' => $state,
                'abc_msg' => $msg,
            ], $redirect);
            wp_safe_redirect($url);
            exit;
        };

        if ($calendar_id <= 0) {
            $go(0, 'error', self::tr('Missing calendar.', 'Brak kalendarza.'));
        }

        if (! isset($_POST['abc_nonce']) || ! wp_verify_nonce((string) $_POST['abc_nonce'], 'abc_submit_booking_request_' . $calendar_id)) {
            $go($calendar_id, 'error', self::tr('Invalid form token.', 'Niepoprawny token formularza.'));
        }

        $honeypot = trim((string) ($_POST['abc_honeypot'] ?? ''));
        if ($honeypot !== '') {
            $go($calendar_id, 'success', self::tr('Thank you.', 'Dziękuję.'));
        }

        $settings = self::get_calendar_settings($calendar_id);
        $status_map = self::normalize_status_map((string) $settings['status_map']);

        $date = sanitize_text_field((string) ($_POST['abc_date'] ?? ''));
        $time_slot = sanitize_text_field((string) ($_POST['abc_time'] ?? ''));
        $is_all_day = ! empty($_POST['abc_is_all_day']) && in_array((string) $_POST['abc_is_all_day'], ['1', 'true', 'on'], true);
        $full_name = sanitize_text_field((string) ($_POST['abc_full_name'] ?? ''));
        $option = sanitize_text_field((string) ($_POST['abc_option'] ?? ''));
        $email = sanitize_email((string) ($_POST['abc_email'] ?? ''));
        $phone = sanitize_text_field((string) ($_POST['abc_phone'] ?? ''));
        $message = sanitize_textarea_field((string) ($_POST['abc_message'] ?? ''));
        $consent = ! empty($_POST['abc_consent']);

        if ($is_all_day) {
            $time_slot = 'ALL_DAY';
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || (! $is_all_day && ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time_slot)) || $full_name === '' || $option === '' || $email === '' || $phone === '' || ! $consent) {
            $go($calendar_id, 'error', self::tr('Please fill all required fields.', 'Uzupełnij wszystkie wymagane pola.'));
        }
        if (! is_email($email)) {
            $go($calendar_id, 'error', self::tr('Provide a valid email address.', 'Podaj poprawny adres e-mail.'));
        }

        $options = self::parse_options((string) $settings['booking_options']);
        if (! in_array($option, $options, true)) {
            $go($calendar_id, 'error', self::tr('Invalid package.', 'Nieprawidłowy pakiet.'));
        }

        $day = self::resolve_day_status($date, $status_map);
        if (($day['status'] ?? 'none') !== 'available') {
            $go($calendar_id, 'error', self::tr('This date is no longer available.', 'Ten termin nie jest już dostępny.'));
        }

        $day_mode_map = self::normalize_day_mode_map((string) get_post_meta($calendar_id, '_abc_day_mode_map', true));
        $resolved_day_mode = self::resolve_day_mode($date, $settings, $day_mode_map);
        if ($resolved_day_mode === 'all_day' && ! $is_all_day) {
            $is_all_day = true;
            $time_slot = 'ALL_DAY';
        } elseif ($resolved_day_mode === 'slots' && $is_all_day) {
            $go($calendar_id, 'error', self::tr('This date accepts hourly booking only.', 'Ten termin przyjmuje wyłącznie rezerwacje godzinowe.'));
        }

        $time_overrides = self::normalize_time_slots_overrides((string) get_post_meta($calendar_id, '_abc_time_slots_overrides', true));
        $time_reservations = self::normalize_time_slot_reservations((string) get_post_meta($calendar_id, '_abc_time_slots_reservations', true));
        $day_slots = $is_all_day ? ['ALL_DAY'] : self::get_date_slots($date, $settings, $time_overrides);
        if (! $is_all_day) {
            if (empty($day_slots)) {
                $go($calendar_id, 'error', self::tr('No available time slots for selected date.', 'Brak dostępnych godzin dla wybranej daty.'));
            }
            if (! in_array($time_slot, $day_slots, true)) {
                $go($calendar_id, 'error', self::tr('Selected time is not available for this date.', 'Wybrana godzina nie jest dostępna dla tej daty.'));
            }
            $existing_all_day = $time_reservations[$date]['ALL_DAY'] ?? null;
            if (is_array($existing_all_day)) {
                $slot_status = (string) ($existing_all_day['status'] ?? '');
                $slot_expires = (int) ($existing_all_day['expires_at'] ?? 0);
                if ($slot_status === 'booked') {
                    $go($calendar_id, 'error', self::tr('Selected date is already booked as full day.', 'Wybrana data jest już zajęta jako cały dzień.'));
                }
                if ($slot_status === 'hold' && ($slot_expires <= 0 || $slot_expires > time())) {
                    $go($calendar_id, 'error', self::tr('Selected date is temporarily held as full day.', 'Wybrana data jest chwilowo zarezerwowana jako cały dzień.'));
                }
            }
        }

        if ($is_all_day) {
            $existing_day = isset($time_reservations[$date]) && is_array($time_reservations[$date]) ? $time_reservations[$date] : [];
            foreach ($existing_day as $slot_key => $slot_entry) {
                if (! is_array($slot_entry)) {
                    continue;
                }
                $slot_status = (string) ($slot_entry['status'] ?? '');
                $slot_expires = (int) ($slot_entry['expires_at'] ?? 0);
                if ($slot_status === 'booked') {
                    $go($calendar_id, 'error', self::tr('Selected date is already booked.', 'Wybrana data jest już zajęta.'));
                }
                if ($slot_status === 'hold' && ($slot_expires <= 0 || $slot_expires > time())) {
                    $go($calendar_id, 'error', self::tr('Selected date is temporarily held.', 'Wybrana data jest chwilowo zarezerwowana.'));
                }
                unset($time_reservations[$date][$slot_key]);
            }
            if (isset($time_reservations[$date]) && empty($time_reservations[$date])) {
                unset($time_reservations[$date]);
            }
        }
        $existing_slot = $time_reservations[$date][$time_slot] ?? null;
        if (is_array($existing_slot)) {
            $slot_status = (string) ($existing_slot['status'] ?? '');
            $slot_expires = (int) ($existing_slot['expires_at'] ?? 0);
            if ($slot_status === 'booked') {
                $go($calendar_id, 'error', self::tr('Selected time is already booked.', 'Wybrana godzina jest już zajęta.'));
            }
            if ($slot_status === 'hold' && ($slot_expires <= 0 || $slot_expires > time())) {
                $go($calendar_id, 'error', self::tr('Selected time is temporarily held.', 'Wybrana godzina jest chwilowo zarezerwowana.'));
            }
            unset($time_reservations[$date][$time_slot]);
        }

        $hold_minutes = (int) $settings['booking_hold_minutes'];
        $hold_minutes = max(1, min(10080, $hold_minutes));
        $hold_expires = time() + ($hold_minutes * MINUTE_IN_SECONDS);
        $hold_hours = rtrim(rtrim(number_format($hold_minutes / 60, 2, '.', ''), '0'), '.');
        $expires_human = wp_date('d.m.Y H:i', $hold_expires);

        $request_id = wp_insert_post([
            'post_type' => self::REQUEST_CPT,
            'post_status' => 'publish',
            'post_title' => wp_strip_all_tags($date . ' ' . ($is_all_day ? self::tr('ALL DAY', 'CAŁY DZIEŃ') : $time_slot) . ' — ' . $full_name . ' — ' . $option),
            'post_content' => $message,
        ], true);

        if (is_wp_error($request_id) || ! $request_id) {
            $go($calendar_id, 'error', self::tr('Could not save your request.', 'Nie udało się zapisać zgłoszenia.'));
        }

        update_post_meta($request_id, '_abc_status', 'hold');
        update_post_meta($request_id, '_abc_hold_expires_at', $hold_expires);
        update_post_meta($request_id, '_abc_hold_minutes', $hold_minutes);
        update_post_meta($request_id, '_abc_calendar_id', $calendar_id);
        update_post_meta($request_id, '_abc_date', $date);
        update_post_meta($request_id, '_abc_time', $time_slot);
        update_post_meta($request_id, '_abc_is_all_day', $is_all_day ? '1' : '0');
        update_post_meta($request_id, '_abc_option', $option);
        update_post_meta($request_id, '_abc_full_name', $full_name);
        update_post_meta($request_id, '_abc_email', $email);
        update_post_meta($request_id, '_abc_phone', $phone);
        update_post_meta($request_id, '_abc_message', $message);
        update_post_meta($request_id, '_abc_send_expired_email', self::to_bool($settings['booking_send_expired_email']) ? '1' : '0');
        update_post_meta($request_id, '_abc_expired_email_subject_template', (string) $settings['booking_client_expired_email_subject']);
        update_post_meta($request_id, '_abc_expired_email_body_template', (string) $settings['booking_client_expired_email_body']);

        $time_reservations[$date][$time_slot] = [
            'status' => 'hold',
            'expires_at' => $hold_expires,
            'request_id' => (int) $request_id,
        ];
        $reconciled = self::reconcile_calendar_state($settings, $time_overrides, $time_reservations, $status_map);
        $time_reservations = $reconciled['time_reservations'];
        $status_map = $reconciled['status_map'];
        self::save_status_map($calendar_id, $status_map);
        self::save_time_reservations($calendar_id, $time_reservations);

        $notify = sanitize_email((string) $settings['booking_notification_email']);
        if (! is_email($notify)) {
            $notify = (string) get_option('admin_email');
        }

        $admin_subject = self::tr('New booking request: ', 'Nowe zapytanie rezerwacji: ') . $date;
        $admin_body = implode("\n", [
            self::tr('New booking request', 'Nowe zapytanie rezerwacji'),
            '------------------------',
            self::tr('Date: ', 'Data: ') . $date,
            self::tr('Time: ', 'Godzina: ') . ($is_all_day ? self::tr('Full day', 'Cały dzień') : $time_slot),
            self::tr('Service / Package: ', 'Usługa / Pakiet: ') . $option,
            self::tr('Full name: ', 'Imię i nazwisko: ') . $full_name,
            self::tr('Email: ', 'E-mail: ') . $email,
            self::tr('Phone: ', 'Telefon: ') . $phone,
            self::tr('Hold until: ', 'Hold do: ') . $expires_human,
            '',
            self::tr('Message:', 'Wiadomość:'),
            $message !== '' ? $message : '-',
        ]);
        $mail_headers = self::mail_headers($settings);
        wp_mail($notify, $admin_subject, $admin_body, $mail_headers);

        if (self::to_bool($settings['booking_send_initial_email']) && is_email($email)) {
            $subject = self::replace_tokens((string) $settings['booking_client_initial_email_subject'], [
                'full_name' => $full_name,
                'date' => $date,
                'time' => $time_slot,
                'option' => $option,
                'hours' => $hold_hours,
                'minutes' => (string) $hold_minutes,
                'expires' => $expires_human,
                'site_name' => get_bloginfo('name'),
            ]);
            $body = self::replace_tokens((string) $settings['booking_client_initial_email_body'], [
                'full_name' => $full_name,
                'date' => $date,
                'time' => $time_slot,
                'option' => $option,
                'hours' => $hold_hours,
                'minutes' => (string) $hold_minutes,
                'expires' => $expires_human,
                'site_name' => get_bloginfo('name'),
            ]);
            wp_mail($email, $subject, $body, $mail_headers);
        }

        $msg = self::replace_tokens((string) $settings['booking_success_message'], [
            'hours' => $hold_hours,
            'minutes' => (string) $hold_minutes,
        ]);

        $go($calendar_id, 'success', $msg);
    }

    public static function schedule_cleanup(): void {
        if (! wp_next_scheduled('abc_booking_cleanup_event')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'abc_booking_cleanup_event');
        }
    }

    public static function maybe_cleanup_expired_holds(): void {
        $key = 'abc_cleanup_last_run';
        $last = (int) get_transient($key);
        $now = time();
        if ($last > 0 && ($now - $last) < (10 * MINUTE_IN_SECONDS)) {
            return;
        }
        set_transient($key, $now, 15 * MINUTE_IN_SECONDS);
        self::cleanup_expired_holds(50);
    }

    public static function cleanup_expired_holds(int $limit = 100): void {
        $limit = max(1, min(500, $limit));
        $now = time();

        $requests = get_posts([
            'post_type' => self::REQUEST_CPT,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids',
            'meta_query' => [
                'relation' => 'AND',
                ['key' => '_abc_status', 'value' => 'hold'],
                ['key' => '_abc_hold_expires_at', 'value' => $now, 'type' => 'NUMERIC', 'compare' => '<='],
            ],
        ]);

        if (empty($requests)) {
            return;
        }

        foreach ($requests as $request_id) {
            $calendar_id = (int) get_post_meta($request_id, '_abc_calendar_id', true);
            $date = sanitize_text_field((string) get_post_meta($request_id, '_abc_date', true));
            $time_slot = sanitize_text_field((string) get_post_meta($request_id, '_abc_time', true));
            $hold_expires = (int) get_post_meta($request_id, '_abc_hold_expires_at', true);
            $hold_minutes = max(1, (int) get_post_meta($request_id, '_abc_hold_minutes', true));
            $hold_hours = rtrim(rtrim(number_format($hold_minutes / 60, 2, '.', ''), '0'), '.');

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $status_map = self::normalize_status_map((string) get_post_meta($calendar_id, '_abc_status_map', true));
                $time_overrides = self::normalize_time_slots_overrides((string) get_post_meta($calendar_id, '_abc_time_slots_overrides', true));
                $time_reservations = self::normalize_time_slot_reservations((string) get_post_meta($calendar_id, '_abc_time_slots_reservations', true));

                if ($time_slot !== '' && isset($time_reservations[$date][$time_slot])) {
                    $entry = $time_reservations[$date][$time_slot];
                    if ((int) ($entry['request_id'] ?? 0) === (int) $request_id) {
                        unset($time_reservations[$date][$time_slot]);
                        if (empty($time_reservations[$date])) {
                            unset($time_reservations[$date]);
                        }
                        self::save_time_reservations($calendar_id, $time_reservations);
                    }
                }
                $calendar_settings = self::get_calendar_settings($calendar_id);
                $reconciled = self::reconcile_calendar_state($calendar_settings, $time_overrides, $time_reservations, $status_map);
                self::save_time_reservations($calendar_id, $reconciled['time_reservations']);
                self::save_status_map($calendar_id, $reconciled['status_map']);
            }

            $send = (string) get_post_meta($request_id, '_abc_send_expired_email', true) === '1';
            $email = sanitize_email((string) get_post_meta($request_id, '_abc_email', true));
            if ($send && is_email($email)) {
                $full_name = sanitize_text_field((string) get_post_meta($request_id, '_abc_full_name', true));
                $option = sanitize_text_field((string) get_post_meta($request_id, '_abc_option', true));
                $expires_human = $hold_expires > 0 ? wp_date('d.m.Y H:i', $hold_expires) : wp_date('d.m.Y H:i');

                $subject_tpl = trim((string) get_post_meta($request_id, '_abc_expired_email_subject_template', true));
                $body_tpl = trim((string) get_post_meta($request_id, '_abc_expired_email_body_template', true));
                if ($subject_tpl === '') {
                    $subject_tpl = self::tr('Tentative booking expired', 'Wstępna rezerwacja wygasła');
                }
                if ($body_tpl === '') {
                    $body_tpl = self::tr("Hi {full_name},\n\nYour tentative booking has expired (no confirmation).\nDate: {date}\nTime: {time}\nService / Package: {option}\nHold duration: {hours}h ({minutes} min)\nExpired at: {expires}\n\nIf you are still interested, please submit a new request.\n\n{site_name}", "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}");
                }

                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'time' => $time_slot,
                    'option' => $option,
                    'hours' => $hold_hours,
                    'minutes' => (string) $hold_minutes,
                    'expires' => $expires_human,
                    'site_name' => get_bloginfo('name'),
                ];
                $calendar_settings = $calendar_id > 0 ? self::get_calendar_settings($calendar_id) : self::defaults();
                wp_mail($email, self::replace_tokens($subject_tpl, $ctx), self::replace_tokens($body_tpl, $ctx), self::mail_headers($calendar_settings));
            }

            update_post_meta($request_id, '_abc_status', 'expired');
        }
    }

}
}
