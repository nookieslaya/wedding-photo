<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Admin_Trait')) {
trait Rdev_Calendar_Admin_Trait {
    public static function register_post_types(): void {
        register_post_type(self::CALENDAR_CPT, [
            'labels' => [
                'name' => self::tr('Calendars', 'Kalendarze'),
                'singular_name' => self::tr('Calendar', 'Kalendarz'),
                'menu_name' => self::tr('Calendars', 'Kalendarze'),
                'name_admin_bar' => self::tr('Calendar', 'Kalendarz'),
                'add_new' => self::tr('Add new', 'Dodaj nowy'),
                'add_new_item' => self::tr('Add calendar', 'Dodaj kalendarz'),
                'edit_item' => self::tr('Edit calendar', 'Edytuj kalendarz'),
                'new_item' => self::tr('New calendar', 'Nowy kalendarz'),
                'view_item' => self::tr('View calendar', 'Zobacz kalendarz'),
                'search_items' => self::tr('Search calendars', 'Szukaj kalendarzy'),
                'not_found' => self::tr('No calendars found', 'Nie znaleziono kalendarzy'),
                'not_found_in_trash' => self::tr('No calendars found in Trash', 'Brak kalendarzy w koszu'),
                'all_items' => self::tr('Calendars', 'Kalendarze'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title'],
            'menu_position' => 24,
        ]);

        register_post_type(self::REQUEST_CPT, [
            'labels' => [
                'name' => self::tr('Booking Requests', 'Zapytania rezerwacji'),
                'singular_name' => self::tr('Booking Request', 'Zapytanie rezerwacji'),
                'menu_name' => self::tr('Booking Requests', 'Zapytania rezerwacji'),
                'name_admin_bar' => self::tr('Booking Request', 'Zapytanie rezerwacji'),
                'add_new' => self::tr('Add new', 'Dodaj nowe'),
                'add_new_item' => self::tr('Add booking request', 'Dodaj zapytanie rezerwacji'),
                'edit_item' => self::tr('Edit booking request', 'Edytuj zapytanie rezerwacji'),
                'new_item' => self::tr('New booking request', 'Nowe zapytanie rezerwacji'),
                'view_item' => self::tr('View booking request', 'Zobacz zapytanie rezerwacji'),
                'search_items' => self::tr('Search booking requests', 'Szukaj zapytań rezerwacji'),
                'not_found' => self::tr('No booking requests found', 'Nie znaleziono zapytań rezerwacji'),
                'not_found_in_trash' => self::tr('No booking requests found in Trash', 'Brak zapytań rezerwacji w koszu'),
                'all_items' => self::tr('Booking Requests', 'Zapytania rezerwacji'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-email-alt2',
            'supports' => ['title'],
            'menu_position' => 25,
        ]);
    }

    public static function register_meta_boxes(): void {
        add_meta_box(
            'abc_calendar_settings',
            self::tr('Calendar settings', 'Ustawienia kalendarza'),
            [self::class, 'render_settings_metabox'],
            self::CALENDAR_CPT,
            'normal',
            'high'
        );

        add_meta_box(
            'abc_calendar_availability',
            self::tr('Availability manager', 'Menedżer dostępności'),
            [self::class, 'render_availability_metabox'],
            self::CALENDAR_CPT,
            'normal',
            'default'
        );
    }

    public static function enqueue_admin_assets(string $hook): void {
        if (! in_array($hook, ['post.php', 'post-new.php'], true)) {
            return;
        }
        $screen = get_current_screen();
        if (! $screen || $screen->post_type !== self::CALENDAR_CPT) {
            return;
        }

        $base_file = self::plugin_base_file();
        $base = plugin_dir_url($base_file) . 'assets/';
        $admin_css_ver = self::asset_version('assets/css/admin.css');
        $admin_js_ver = self::asset_version('assets/js/admin.js');
        wp_enqueue_style('abc-admin', $base . 'css/admin.css', [], $admin_css_ver);
        wp_enqueue_script('abc-admin', $base . 'js/admin.js', [], $admin_js_ver, true);
        $is_pl = self::is_polish_locale();
        wp_localize_script('abc-admin', 'abcAdminI18n', [
            'locale' => self::locale_tag(),
            'status_available' => self::tr('Available', 'Dostępny'),
            'status_tentative' => self::tr('Tentative', 'Wstępna'),
            'status_booked' => self::tr('Booked', 'Zajęty'),
            'clear_selected' => self::tr('Clear selected', 'Wyczyść zaznaczone'),
            'time_placeholder' => self::tr('Time HH:MM', 'Godzina HH:MM'),
            'time_add' => self::tr('Add time to selected dates', 'Dodaj godzinę do zaznaczonych dni'),
            'time_remove' => self::tr('Remove time from selected dates', 'Usuń godzinę z zaznaczonych dni'),
            'weekdays' => $is_pl ? ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'time_preview_title' => self::tr('Hours preview:', 'Podgląd godzin:'),
            'select_one_date' => self::tr('Select one date to preview available hours.', 'Zaznacz jedną datę, aby zobaczyć dostępne godziny.'),
            'select_single_date' => self::tr('Multiple dates selected. Preview works for one date at a time.', 'Zaznaczono kilka dat. Podgląd działa dla jednej daty naraz.'),
            'no_configured_hours' => self::tr('No configured hours.', 'Brak skonfigurowanych godzin.'),
            'available' => self::tr('Available:', 'Dostępne:'),
            'busy_hold' => self::tr('Booked / hold:', 'Zajęte / hold:'),
            'no_free_hours' => self::tr('No free hours', 'Brak wolnych godzin'),
            'none' => self::tr('None', 'Brak'),
            'summary_selected_prefix' => self::tr('Selected:', 'Zaznaczono:'),
            'summary_status' => self::tr('Status:', 'Status:'),
            'summary_idle' => self::tr('Click days and apply status. Saved:', 'Kliknij dni i zastosuj status. Zapisane:'),
            'summary_overrides' => self::tr('Time overrides:', 'Nadpisania godzin:'),
            'note_optional' => self::tr('Note (optional)', 'Notatka (opcjonalnie)'),
            'apply_selected' => self::tr('Apply to selected', 'Zastosuj do zaznaczonych'),
            'unselect_all' => self::tr('Unselect all', 'Odznacz wszystko'),
            'mode_slots' => self::tr('Hours', 'Godziny'),
            'mode_all_day' => self::tr('Full day', 'Cały dzień'),
            'locked_dates_skipped' => self::tr('Some selected dates are locked by active reservations and were skipped ({count}).', 'Część zaznaczonych dat jest zablokowana aktywnymi rezerwacjami i została pominięta ({count}).'),
            'locked_date_click_info' => self::tr('This date is locked by an active reservation. To release it, go to Booking Requests and use "Release date".', 'Ta data jest zablokowana aktywną rezerwacją. Aby ją zwolnić, przejdź do Zapytania rezerwacji i użyj akcji „Zwolnij termin”.'),
        ]);
    }

    public static function request_columns(array $columns): array {
        return [
            'cb' => $columns['cb'] ?? '<input type="checkbox" />',
            'title' => self::tr('Request', 'Zapytanie'),
            'abc_date' => self::tr('Date', 'Data'),
            'abc_time' => self::tr('Time', 'Godzina'),
            'abc_option' => self::tr('Service / Package', 'Usługa / Pakiet'),
            'abc_contact' => self::tr('Contact', 'Kontakt'),
            'abc_status' => self::tr('Status', 'Status'),
            'abc_hold' => self::tr('Hold to', 'Blokada do'),
            'date' => $columns['date'] ?? self::tr('Created', 'Utworzono'),
        ];
    }

    public static function request_row_actions(array $actions, \WP_Post $post): array {
        if ($post->post_type !== self::REQUEST_CPT) {
            return $actions;
        }
        if (! current_user_can('edit_post', $post->ID)) {
            return $actions;
        }

        $status = (string) get_post_meta($post->ID, '_abc_status', true);
        if (! in_array($status, ['hold', 'approved', 'rejected', 'expired', 'released'], true)) {
            $status = 'hold';
        }

        $base = admin_url('admin-post.php');

        if (in_array($status, ['hold', 'rejected', 'expired', 'released'], true)) {
            $approve_url = wp_nonce_url(add_query_arg([
                'action' => 'abc_request_decision',
                'request_id' => $post->ID,
                'decision' => 'approve',
            ], $base), 'abc_request_decision_' . $post->ID . '_approve', 'abc_nonce');
            $actions['abc_approve'] = '<a href="' . esc_url($approve_url) . '">' . esc_html(self::tr('Approve', 'Zatwierdź')) . '</a>';
        }

        if (in_array($status, ['hold', 'approved'], true)) {
            $reject_url = wp_nonce_url(add_query_arg([
                'action' => 'abc_request_decision',
                'request_id' => $post->ID,
                'decision' => 'reject',
            ], $base), 'abc_request_decision_' . $post->ID . '_reject', 'abc_nonce');
            $actions['abc_reject'] = '<a href="' . esc_url($reject_url) . '">' . esc_html($status === 'approved' ? self::tr('Release date', 'Zwolnij termin') : self::tr('Reject', 'Odrzuć')) . '</a>';
        }

        return $actions;
    }

    public static function handle_request_decision(): void {
        if (! is_admin()) {
            wp_die('Forbidden', 403);
        }

        $request_id = isset($_GET['request_id']) ? absint($_GET['request_id']) : 0;
        $decision = isset($_GET['decision']) ? sanitize_key((string) $_GET['decision']) : '';

        if ($request_id <= 0 || ! in_array($decision, ['approve', 'reject'], true)) {
            wp_die('Invalid request');
        }

        if (! current_user_can('edit_post', $request_id)) {
            wp_die('Forbidden', 403);
        }

        $nonce = isset($_GET['abc_nonce']) ? (string) $_GET['abc_nonce'] : '';
        if (! wp_verify_nonce($nonce, 'abc_request_decision_' . $request_id . '_' . $decision)) {
            wp_die('Invalid nonce');
        }

        $request = get_post($request_id);
        if (! $request || $request->post_type !== self::REQUEST_CPT) {
            wp_die('Request not found');
        }

        $calendar_id = (int) get_post_meta($request_id, '_abc_calendar_id', true);
        $previous_status = (string) get_post_meta($request_id, '_abc_status', true);
        if (! in_array($previous_status, ['hold', 'approved', 'rejected', 'expired', 'released'], true)) {
            $previous_status = 'hold';
        }
        $date = sanitize_text_field((string) get_post_meta($request_id, '_abc_date', true));
        $time_slot = sanitize_text_field((string) get_post_meta($request_id, '_abc_time', true));
        $full_name = sanitize_text_field((string) get_post_meta($request_id, '_abc_full_name', true));
        $option = sanitize_text_field((string) get_post_meta($request_id, '_abc_option', true));
        $email = sanitize_email((string) get_post_meta($request_id, '_abc_email', true));
        $settings = $calendar_id > 0 ? self::get_calendar_settings($calendar_id) : self::defaults();
        $status_map = $calendar_id > 0 ? self::normalize_status_map((string) get_post_meta($calendar_id, '_abc_status_map', true)) : [];
        $time_overrides = $calendar_id > 0 ? self::normalize_time_slots_overrides((string) get_post_meta($calendar_id, '_abc_time_slots_overrides', true)) : [];
        $time_reservations = $calendar_id > 0 ? self::normalize_time_slot_reservations((string) get_post_meta($calendar_id, '_abc_time_slots_reservations', true)) : [];

        if ($decision === 'approve') {
            if (! in_array($previous_status, ['hold', 'rejected', 'expired', 'released'], true)) {
                $decision_key = 'invalid_transition';
                $back = wp_get_referer();
                if (! is_string($back) || $back === '') {
                    $back = admin_url('edit.php?post_type=' . self::REQUEST_CPT);
                }
                wp_safe_redirect(add_query_arg('abc_decision', $decision_key, $back));
                exit;
            }

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time_slot) || $time_slot === 'ALL_DAY')) {
                $existing = $time_reservations[$date][$time_slot] ?? null;
                if (is_array($existing)) {
                    $existing_status = (string) ($existing['status'] ?? '');
                    $existing_request = (int) ($existing['request_id'] ?? 0);
                    $existing_expires = (int) ($existing['expires_at'] ?? 0);
                    $active_hold = $existing_status === 'hold' && ($existing_expires <= 0 || $existing_expires > time());
                    if ($existing_request !== $request_id && ($existing_status === 'booked' || $active_hold)) {
                        $decision_key = 'approve_conflict';
                        $back = wp_get_referer();
                        if (! is_string($back) || $back === '') {
                            $back = admin_url('edit.php?post_type=' . self::REQUEST_CPT);
                        }
                        wp_safe_redirect(add_query_arg('abc_decision', $decision_key, $back));
                        exit;
                    }
                }
                $time_reservations[$date][$time_slot] = [
                    'status' => 'booked',
                    'expires_at' => 0,
                    'request_id' => $request_id,
                ];
                $reconciled = self::reconcile_calendar_state($settings, $time_overrides, $time_reservations, $status_map);
                $time_reservations = $reconciled['time_reservations'];
                $status_map = $reconciled['status_map'];
                self::save_time_reservations($calendar_id, $time_reservations);
                self::save_status_map($calendar_id, $status_map);
                if (! empty($reconciled['changed'])) {
                    self::queue_reconcile_notice($calendar_id);
                }
            }
            update_post_meta($request_id, '_abc_status', 'approved');
            update_post_meta($request_id, '_abc_hold_expires_at', 0);

            if (self::to_bool($settings['booking_send_approved_email']) && is_email($email)) {
                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'time' => $time_slot === 'ALL_DAY' ? self::tr('Full day', 'Cały dzień') : $time_slot,
                    'option' => $option,
                    'status' => self::tr('Approved', 'Zatwierdzona'),
                    'site_name' => get_bloginfo('name'),
                ];
                $subject = self::replace_tokens((string) $settings['booking_client_approved_email_subject'], $ctx);
                $body = self::replace_tokens((string) $settings['booking_client_approved_email_body'], $ctx);
                wp_mail($email, $subject, $body, self::mail_headers($settings));
            }
        } else {
            if (! in_array($previous_status, ['hold', 'approved'], true)) {
                $decision_key = 'invalid_transition';
                $back = wp_get_referer();
                if (! is_string($back) || $back === '') {
                    $back = admin_url('edit.php?post_type=' . self::REQUEST_CPT);
                }
                wp_safe_redirect(add_query_arg('abc_decision', $decision_key, $back));
                exit;
            }
            $next_status = $previous_status === 'approved' ? 'released' : 'rejected';
            update_post_meta($request_id, '_abc_status', $next_status);
            update_post_meta($request_id, '_abc_hold_expires_at', 0);

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time_slot) || $time_slot === 'ALL_DAY')) {
                if (isset($time_reservations[$date][$time_slot]) && (int) ($time_reservations[$date][$time_slot]['request_id'] ?? 0) === $request_id) {
                    unset($time_reservations[$date][$time_slot]);
                    if (empty($time_reservations[$date])) {
                        unset($time_reservations[$date]);
                    }
                    self::save_time_reservations($calendar_id, $time_reservations);
                }
                $reconciled = self::reconcile_calendar_state($settings, $time_overrides, $time_reservations, $status_map);
                $time_reservations = $reconciled['time_reservations'];
                $status_map = $reconciled['status_map'];
                self::save_time_reservations($calendar_id, $time_reservations);
                self::save_status_map($calendar_id, $status_map);
                if (! empty($reconciled['changed'])) {
                    self::queue_reconcile_notice($calendar_id);
                }
            }

            if (self::to_bool($settings['booking_send_rejected_email']) && is_email($email)) {
                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'time' => $time_slot === 'ALL_DAY' ? self::tr('Full day', 'Cały dzień') : $time_slot,
                    'option' => $option,
                    'status' => $next_status === 'released' ? self::tr('Released', 'Zwolniona') : self::tr('Rejected', 'Odrzucona'),
                    'site_name' => get_bloginfo('name'),
                ];
                $subject = self::replace_tokens((string) $settings['booking_client_rejected_email_subject'], $ctx);
                $body = self::replace_tokens((string) $settings['booking_client_rejected_email_body'], $ctx);
                wp_mail($email, $subject, $body, self::mail_headers($settings));
            }
        }

        $back = wp_get_referer();
        if (! is_string($back) || $back === '') {
            $back = admin_url('edit.php?post_type=' . self::REQUEST_CPT);
        }
        $decision_key = $decision === 'approve'
            ? 'approved'
            : ($previous_status === 'approved' ? 'released' : 'rejected');
        $url = add_query_arg('abc_decision', $decision_key, $back);
        wp_safe_redirect($url);
        exit;
    }

    public static function maybe_render_admin_notice(): void {
        if (! is_admin()) {
            return;
        }
        $reconcile_notice = self::consume_reconcile_notice();
        if (is_array($reconcile_notice)) {
            $calendar_id = isset($reconcile_notice['calendar_id']) ? (int) $reconcile_notice['calendar_id'] : 0;
            $label = $calendar_id > 0 ? ' #' . $calendar_id : '';
            echo '<div class="notice notice-info is-dismissible"><p>' . esc_html(self::tr('Calendar data were auto-repaired to keep statuses consistent', 'Dane kalendarza zostały automatycznie naprawione dla zachowania spójności statusów') . $label . '.') . '</p></div>';
        }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (! $screen) {
            return;
        }

        if ($screen->post_type === self::CALENDAR_CPT) {
            $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
            $message = isset($_GET['message']) ? absint($_GET['message']) : 0;
            if ($post_id > 0 && $message > 0) {
                $shortcode = '[rdev_calendar id="' . $post_id . '"]';
                $alias_shortcode = '[rdev_booking_calendar id="' . $post_id . '"]';
                echo '<div class="notice notice-success"><p><strong>' . esc_html(self::tr('Calendar shortcode:', 'Shortcode kalendarza:')) . '</strong> <code>' . esc_html($shortcode) . '</code></p><p><small>' . esc_html(self::tr('Alias:', 'Alias:')) . ' <code>' . esc_html($alias_shortcode) . '</code></small></p></div>';
            }
            return;
        }

        if ($screen->post_type !== self::REQUEST_CPT) {
            return;
        }

        $decision = isset($_GET['abc_decision']) ? sanitize_key((string) $_GET['abc_decision']) : '';
        if ($decision === 'approved') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(self::tr('Request status changed to: Approved.', 'Status zgłoszenia ustawiono na: Zatwierdzone.')) . '</p></div>';
            return;
        }
        if ($decision === 'rejected') {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html(self::tr('Request status changed to: Rejected.', 'Status zgłoszenia ustawiono na: Odrzucone.')) . '</p></div>';
            return;
        }
        if ($decision === 'released') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(self::tr('Booking released. Date is available again.', 'Termin został zwolniony. Data jest ponownie dostępna.')) . '</p></div>';
            return;
        }
        if ($decision === 'approve_conflict') {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(self::tr('Cannot approve: this slot is already reserved by another request.', 'Nie można zatwierdzić: ten termin jest już zajęty przez inne zgłoszenie.')) . '</p></div>';
            return;
        }
        if ($decision === 'invalid_transition') {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html(self::tr('Invalid status transition for this request.', 'Nieprawidłowe przejście statusu dla tego zgłoszenia.')) . '</p></div>';
        }
    }

    public static function render_request_column(string $column, int $post_id): void {
        if ($column === 'abc_date') {
            echo esc_html((string) get_post_meta($post_id, '_abc_date', true));
            return;
        }
        if ($column === 'abc_option') {
            echo esc_html((string) get_post_meta($post_id, '_abc_option', true));
            return;
        }
        if ($column === 'abc_time') {
            $time = (string) get_post_meta($post_id, '_abc_time', true);
            echo esc_html($time === 'ALL_DAY' ? self::tr('Full day', 'Cały dzień') : $time);
            return;
        }
        if ($column === 'abc_contact') {
            echo esc_html((string) get_post_meta($post_id, '_abc_full_name', true));
            echo '<br>';
            echo esc_html((string) get_post_meta($post_id, '_abc_email', true));
            $phone = (string) get_post_meta($post_id, '_abc_phone', true);
            if (trim($phone) !== '') {
                echo '<br>' . esc_html($phone);
            }
            return;
        }
        if ($column === 'abc_status') {
            $status = (string) get_post_meta($post_id, '_abc_status', true);
            if ($status === 'hold') {
                $label = self::tr('Hold active', 'Hold aktywny');
            } elseif ($status === 'approved') {
                $label = self::tr('Approved', 'Zatwierdzone');
            } elseif ($status === 'rejected') {
                $label = self::tr('Rejected', 'Odrzucone');
            } elseif ($status === 'expired') {
                $label = self::tr('Expired', 'Wygasło');
            } elseif ($status === 'released') {
                $label = self::tr('Released', 'Zwolnione');
            } else {
                $label = $status !== '' ? $status : '—';
            }
            echo esc_html($label);
            return;
        }
        if ($column === 'abc_hold') {
            $expires = (int) get_post_meta($post_id, '_abc_hold_expires_at', true);
            echo $expires > 0 ? esc_html(wp_date('Y-m-d H:i', $expires)) : '—';
        }
    }

}
}
