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
            $go(0, 'error', 'Brak kalendarza.');
        }

        if (! isset($_POST['abc_nonce']) || ! wp_verify_nonce((string) $_POST['abc_nonce'], 'abc_submit_booking_request_' . $calendar_id)) {
            $go($calendar_id, 'error', 'Niepoprawny token formularza.');
        }

        $honeypot = trim((string) ($_POST['abc_honeypot'] ?? ''));
        if ($honeypot !== '') {
            $go($calendar_id, 'success', 'Dziękuję.');
        }

        $settings = self::get_calendar_settings($calendar_id);
        $status_map = self::normalize_status_map((string) $settings['status_map']);

        $date = sanitize_text_field((string) ($_POST['abc_date'] ?? ''));
        $full_name = sanitize_text_field((string) ($_POST['abc_full_name'] ?? ''));
        $option = sanitize_text_field((string) ($_POST['abc_option'] ?? ''));
        $email = sanitize_email((string) ($_POST['abc_email'] ?? ''));
        $phone = sanitize_text_field((string) ($_POST['abc_phone'] ?? ''));
        $message = sanitize_textarea_field((string) ($_POST['abc_message'] ?? ''));
        $consent = ! empty($_POST['abc_consent']);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $full_name === '' || $option === '' || $email === '' || $phone === '' || ! $consent) {
            $go($calendar_id, 'error', 'Uzupełnij wszystkie wymagane pola.');
        }
        if (! is_email($email)) {
            $go($calendar_id, 'error', 'Podaj poprawny adres e-mail.');
        }

        $options = self::parse_options((string) $settings['booking_options']);
        if (! in_array($option, $options, true)) {
            $go($calendar_id, 'error', 'Nieprawidłowy pakiet.');
        }

        $day = self::resolve_day_status($date, $status_map);
        if (($day['status'] ?? 'none') !== 'available') {
            $go($calendar_id, 'error', 'Ten termin nie jest już dostępny.');
        }

        $hold_minutes = (int) $settings['booking_hold_minutes'];
        $hold_minutes = max(1, min(10080, $hold_minutes));
        $hold_expires = time() + ($hold_minutes * MINUTE_IN_SECONDS);
        $hold_hours = rtrim(rtrim(number_format($hold_minutes / 60, 2, '.', ''), '0'), '.');
        $expires_human = wp_date('d.m.Y H:i', $hold_expires);

        $request_id = wp_insert_post([
            'post_type' => self::REQUEST_CPT,
            'post_status' => 'publish',
            'post_title' => wp_strip_all_tags($date . ' — ' . $full_name . ' — ' . $option),
            'post_content' => $message,
        ], true);

        if (is_wp_error($request_id) || ! $request_id) {
            $go($calendar_id, 'error', 'Nie udało się zapisać zgłoszenia.');
        }

        update_post_meta($request_id, '_abc_status', 'hold');
        update_post_meta($request_id, '_abc_hold_expires_at', $hold_expires);
        update_post_meta($request_id, '_abc_hold_minutes', $hold_minutes);
        update_post_meta($request_id, '_abc_calendar_id', $calendar_id);
        update_post_meta($request_id, '_abc_date', $date);
        update_post_meta($request_id, '_abc_option', $option);
        update_post_meta($request_id, '_abc_full_name', $full_name);
        update_post_meta($request_id, '_abc_email', $email);
        update_post_meta($request_id, '_abc_phone', $phone);
        update_post_meta($request_id, '_abc_message', $message);
        update_post_meta($request_id, '_abc_send_expired_email', self::to_bool($settings['booking_send_expired_email']) ? '1' : '0');
        update_post_meta($request_id, '_abc_expired_email_subject_template', (string) $settings['booking_client_expired_email_subject']);
        update_post_meta($request_id, '_abc_expired_email_body_template', (string) $settings['booking_client_expired_email_body']);

        $hold_note = self::replace_tokens((string) $settings['booking_hold_note_template'], [
            'hours' => $hold_hours,
            'minutes' => (string) $hold_minutes,
            'expires' => $expires_human,
        ]);

        $status_map[$date] = [
            'status' => 'tentative',
            'note' => $hold_note,
            'hold_expires_at' => $hold_expires,
            'hold_request_id' => (int) $request_id,
        ];
        self::save_status_map($calendar_id, $status_map);

        $notify = sanitize_email((string) $settings['booking_notification_email']);
        if (! is_email($notify)) {
            $notify = (string) get_option('admin_email');
        }

        $admin_subject = 'Nowe zapytanie rezerwacji: ' . $date;
        $admin_body = implode("\n", [
            'Nowe zapytanie rezerwacji',
            '------------------------',
            'Data: ' . $date,
            'Usługa / Pakiet: ' . $option,
            'Imię i nazwisko: ' . $full_name,
            'E-mail: ' . $email,
            'Telefon: ' . $phone,
            'Hold do: ' . $expires_human,
            '',
            'Wiadomość:',
            $message !== '' ? $message : '-',
        ]);
        wp_mail($notify, $admin_subject, $admin_body);

        if (self::to_bool($settings['booking_send_initial_email']) && is_email($email)) {
            $subject = self::replace_tokens((string) $settings['booking_client_initial_email_subject'], [
                'full_name' => $full_name,
                'date' => $date,
                'option' => $option,
                'hours' => $hold_hours,
                'minutes' => (string) $hold_minutes,
                'expires' => $expires_human,
                'site_name' => get_bloginfo('name'),
            ]);
            $body = self::replace_tokens((string) $settings['booking_client_initial_email_body'], [
                'full_name' => $full_name,
                'date' => $date,
                'option' => $option,
                'hours' => $hold_hours,
                'minutes' => (string) $hold_minutes,
                'expires' => $expires_human,
                'site_name' => get_bloginfo('name'),
            ]);
            wp_mail($email, $subject, $body);
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
            $hold_expires = (int) get_post_meta($request_id, '_abc_hold_expires_at', true);
            $hold_minutes = max(1, (int) get_post_meta($request_id, '_abc_hold_minutes', true));
            $hold_hours = rtrim(rtrim(number_format($hold_minutes / 60, 2, '.', ''), '0'), '.');

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $status_map = self::normalize_status_map((string) get_post_meta($calendar_id, '_abc_status_map', true));
                $entry = $status_map[$date] ?? null;
                if (is_array($entry) && (int) ($entry['hold_request_id'] ?? 0) === (int) $request_id) {
                    $status_map[$date] = ['status' => 'available', 'note' => ''];
                    self::save_status_map($calendar_id, $status_map);
                }
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
                    $subject_tpl = 'Wstępna rezerwacja wygasła';
                }
                if ($body_tpl === '') {
                    $body_tpl = "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}";
                }

                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'option' => $option,
                    'hours' => $hold_hours,
                    'minutes' => (string) $hold_minutes,
                    'expires' => $expires_human,
                    'site_name' => get_bloginfo('name'),
                ];
                wp_mail($email, self::replace_tokens($subject_tpl, $ctx), self::replace_tokens($body_tpl, $ctx));
            }

            update_post_meta($request_id, '_abc_status', 'expired');
        }
    }

}
}
