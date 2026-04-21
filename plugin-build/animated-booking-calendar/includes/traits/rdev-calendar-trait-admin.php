<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Admin_Trait')) {
trait Rdev_Calendar_Admin_Trait {
    public static function register_post_types(): void {
        register_post_type(self::CALENDAR_CPT, [
            'labels' => [
                'name' => __('Calendars', 'rdev-calendar'),
                'singular_name' => __('Calendar', 'rdev-calendar'),
                'add_new_item' => __('Add calendar', 'rdev-calendar'),
                'edit_item' => __('Edit calendar', 'rdev-calendar'),
                'all_items' => __('Calendars', 'rdev-calendar'),
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
                'name' => __('Booking Requests', 'rdev-calendar'),
                'singular_name' => __('Booking Request', 'rdev-calendar'),
                'all_items' => __('Booking Requests', 'rdev-calendar'),
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
            __('Calendar settings', 'rdev-calendar'),
            [self::class, 'render_settings_metabox'],
            self::CALENDAR_CPT,
            'normal',
            'high'
        );

        add_meta_box(
            'abc_calendar_availability',
            __('Availability manager', 'rdev-calendar'),
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
        $admin_css_path = plugin_dir_path($base_file) . 'assets/css/admin.css';
        $admin_js_path = plugin_dir_path($base_file) . 'assets/js/admin.js';
        $admin_css_ver = file_exists($admin_css_path) ? (string) filemtime($admin_css_path) : self::VERSION;
        $admin_js_ver = file_exists($admin_js_path) ? (string) filemtime($admin_js_path) : self::VERSION;
        wp_enqueue_style('abc-admin', $base . 'css/admin.css', [], $admin_css_ver);
        wp_enqueue_script('abc-admin', $base . 'js/admin.js', [], $admin_js_ver, true);
        wp_localize_script('abc-admin', 'abcAdminI18n', [
            'status_available' => __('Dostępny', 'rdev-calendar'),
            'status_tentative' => __('Wstępna', 'rdev-calendar'),
            'status_booked' => __('Zajęty', 'rdev-calendar'),
            'clear_selected' => __('Wyczyść zaznaczone', 'rdev-calendar'),
            'time_placeholder' => __('Godzina HH:MM', 'rdev-calendar'),
            'time_add' => __('Dodaj godzinę do zaznaczonych dni', 'rdev-calendar'),
            'time_remove' => __('Usuń godzinę z zaznaczonych dni', 'rdev-calendar'),
            'weekdays' => [__('Pon', 'rdev-calendar'), __('Wt', 'rdev-calendar'), __('Śr', 'rdev-calendar'), __('Czw', 'rdev-calendar'), __('Pt', 'rdev-calendar'), __('Sob', 'rdev-calendar'), __('Nd', 'rdev-calendar')],
            'time_preview_title' => __('Podgląd godzin:', 'rdev-calendar'),
            'select_one_date' => __('Zaznacz jedną datę, aby zobaczyć dostępne godziny.', 'rdev-calendar'),
            'select_single_date' => __('Zaznaczono kilka dat. Podgląd działa dla jednej daty naraz.', 'rdev-calendar'),
            'no_configured_hours' => __('Brak skonfigurowanych godzin.', 'rdev-calendar'),
            'available' => __('Dostępne:', 'rdev-calendar'),
            'busy_hold' => __('Zajęte / hold:', 'rdev-calendar'),
            'no_free_hours' => __('Brak wolnych godzin', 'rdev-calendar'),
            'none' => __('Brak', 'rdev-calendar'),
            'summary_selected_prefix' => __('Zaznaczono:', 'rdev-calendar'),
            'summary_status' => __('Status:', 'rdev-calendar'),
            'summary_idle' => __('Kliknij dni i zastosuj status. Zapisane:', 'rdev-calendar'),
            'summary_overrides' => __('Nadpisania godzin:', 'rdev-calendar'),
        ]);
    }

    public static function request_columns(array $columns): array {
        return [
            'cb' => $columns['cb'] ?? '<input type="checkbox" />',
            'title' => __('Request', 'rdev-calendar'),
            'abc_date' => __('Date', 'rdev-calendar'),
            'abc_time' => __('Time', 'rdev-calendar'),
            'abc_option' => __('Service / Package', 'rdev-calendar'),
            'abc_contact' => __('Contact', 'rdev-calendar'),
            'abc_status' => __('Status', 'rdev-calendar'),
            'abc_hold' => __('Hold to', 'rdev-calendar'),
            'date' => $columns['date'] ?? __('Created', 'rdev-calendar'),
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
        if (! in_array($status, ['hold', 'approved', 'rejected', 'expired'], true)) {
            $status = 'hold';
        }

        $base = admin_url('admin-post.php');

        if ($status !== 'approved') {
            $approve_url = wp_nonce_url(add_query_arg([
                'action' => 'abc_request_decision',
                'request_id' => $post->ID,
                'decision' => 'approve',
            ], $base), 'abc_request_decision_' . $post->ID . '_approve', 'abc_nonce');
            $actions['abc_approve'] = '<a href="' . esc_url($approve_url) . '">Zatwierdź</a>';
        }

        if ($status !== 'rejected') {
            $reject_url = wp_nonce_url(add_query_arg([
                'action' => 'abc_request_decision',
                'request_id' => $post->ID,
                'decision' => 'reject',
            ], $base), 'abc_request_decision_' . $post->ID . '_reject', 'abc_nonce');
            $actions['abc_reject'] = '<a href="' . esc_url($reject_url) . '">Odrzuć</a>';
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
            update_post_meta($request_id, '_abc_status', 'approved');

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time_slot)) {
                $time_reservations[$date][$time_slot] = [
                    'status' => 'booked',
                    'expires_at' => 0,
                    'request_id' => $request_id,
                ];
                update_post_meta($calendar_id, '_abc_time_slots_reservations', wp_json_encode($time_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $status_map = self::apply_slot_aggregate_to_status_map($date, $settings, $time_overrides, $time_reservations, $status_map);
                self::save_status_map($calendar_id, $status_map);
            }

            if (self::to_bool($settings['booking_send_approved_email']) && is_email($email)) {
                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'time' => $time_slot,
                    'option' => $option,
                    'status' => 'Zatwierdzona',
                    'site_name' => get_bloginfo('name'),
                ];
                $subject = self::replace_tokens((string) $settings['booking_client_approved_email_subject'], $ctx);
                $body = self::replace_tokens((string) $settings['booking_client_approved_email_body'], $ctx);
                wp_mail($email, $subject, $body, self::mail_headers($settings));
            }
        } else {
            update_post_meta($request_id, '_abc_status', 'rejected');

            if ($calendar_id > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time_slot)) {
                if (isset($time_reservations[$date][$time_slot]) && (int) ($time_reservations[$date][$time_slot]['request_id'] ?? 0) === $request_id) {
                    unset($time_reservations[$date][$time_slot]);
                    if (empty($time_reservations[$date])) {
                        unset($time_reservations[$date]);
                    }
                    update_post_meta($calendar_id, '_abc_time_slots_reservations', wp_json_encode($time_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                }
                $status_map = self::apply_slot_aggregate_to_status_map($date, $settings, $time_overrides, $time_reservations, $status_map);
                self::save_status_map($calendar_id, $status_map);
            }

            if (self::to_bool($settings['booking_send_rejected_email']) && is_email($email)) {
                $ctx = [
                    'full_name' => $full_name,
                    'date' => $date,
                    'time' => $time_slot,
                    'option' => $option,
                    'status' => 'Odrzucona',
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
        $url = add_query_arg('abc_decision', $decision === 'approve' ? 'approved' : 'rejected', $back);
        wp_safe_redirect($url);
        exit;
    }

    public static function maybe_render_admin_notice(): void {
        if (! is_admin()) {
            return;
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
                echo '<div class="notice notice-success"><p><strong>Calendar shortcode:</strong> <code>' . esc_html($shortcode) . '</code></p><p><small>Alias: <code>' . esc_html($alias_shortcode) . '</code></small></p></div>';
            }
            return;
        }

        if ($screen->post_type !== self::REQUEST_CPT) {
            return;
        }

        $decision = isset($_GET['abc_decision']) ? sanitize_key((string) $_GET['abc_decision']) : '';
        if ($decision === 'approved') {
            echo '<div class="notice notice-success is-dismissible"><p>Status zgłoszenia ustawiono na: Zatwierdzone.</p></div>';
            return;
        }
        if ($decision === 'rejected') {
            echo '<div class="notice notice-warning is-dismissible"><p>Status zgłoszenia ustawiono na: Odrzucone.</p></div>';
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
            echo esc_html((string) get_post_meta($post_id, '_abc_time', true));
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
                $label = 'Hold aktywny';
            } elseif ($status === 'approved') {
                $label = 'Zatwierdzone';
            } elseif ($status === 'rejected') {
                $label = 'Odrzucone';
            } elseif ($status === 'expired') {
                $label = 'Wygasło';
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
