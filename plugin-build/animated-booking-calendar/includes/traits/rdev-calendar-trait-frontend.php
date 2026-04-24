<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Frontend_Trait')) {
trait Rdev_Calendar_Frontend_Trait {
    public static function register_shortcodes(): void {
        add_shortcode('rdev_calendar', [self::class, 'render_shortcode']);
        add_shortcode('rdev_booking_calendar', [self::class, 'render_shortcode']);
    }

    public static function register_front_assets(): void {
        $base_file = self::plugin_base_file();
        $base = plugin_dir_url($base_file) . 'assets/';
        $front_css_ver = self::asset_version('assets/css/frontend.css');
        $front_js_ver = self::asset_version('assets/js/frontend.js');
        wp_register_style('abc-frontend', $base . 'css/frontend.css', [], $front_css_ver);
        wp_register_script('abc-frontend', $base . 'js/frontend.js', [], $front_js_ver, true);
        $is_pl = self::is_polish_locale();
        wp_localize_script('abc-frontend', 'abcCalendarI18n', [
            'locale' => self::locale_tag(),
            'status_available' => self::tr('Available', 'Dostępny'),
            'status_unavailable' => self::tr('Unavailable', 'Niedostępny'),
            'status_tentative' => self::tr('Tentative booking', 'Wstępna rezerwacja'),
            'status_booked' => self::tr('Booked', 'Zajęty'),
            'status_none' => self::tr('No information', 'Brak informacji'),
            'legend_show' => self::tr('Show legend', 'Pokaż legendę'),
            'legend_hide' => self::tr('Hide legend', 'Ukryj legendę'),
            'weekdays' => $is_pl ? ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'choose_hour' => self::tr('Choose time', 'Wybierz godzinę'),
            'no_data_month' => self::tr('No data for selected month.', 'Brak danych dla wybranego miesiąca.'),
            'hours_prefix' => self::tr('Hours:', 'Godziny:'),
            'all_day' => self::tr('Full day', 'Cały dzień'),
            'click_day_status' => self::tr('Click a day to see status.', 'Kliknij dzień, aby zobaczyć status.'),
        ]);
    }

    public static function render_shortcode(array $atts): string {
        $atts = shortcode_atts(['id' => 0], $atts, 'rdev_calendar');
        $calendar_id = absint($atts['id']);
        if ($calendar_id <= 0) {
            return '<div class="abc-error">' . esc_html__('Missing calendar ID.', 'rdev-calendar') . '</div>';
        }

        $post = get_post($calendar_id);
        if (! $post || $post->post_type !== self::CALENDAR_CPT) {
            return '<div class="abc-error">' . esc_html__('Calendar not found.', 'rdev-calendar') . '</div>';
        }

        self::maybe_cleanup_expired_holds();

        $s = self::get_calendar_settings($calendar_id);
        $status_map = self::normalize_status_map((string) $s['status_map']);
        $day_mode_map = self::normalize_day_mode_map((string) ($s['day_mode_map'] ?? '{}'));
        $time_overrides = self::normalize_time_slots_overrides((string) ($s['time_slots_overrides'] ?? '{}'));
        $time_reservations = self::normalize_time_slot_reservations((string) ($s['time_slots_reservations'] ?? '{}'));
        $default_time_slots = self::parse_time_slots((string) ($s['booking_default_time_slots'] ?? ''));
        $today_key = wp_date('Y-m-d');
        $status_map = array_filter($status_map, static function ($entry, $date_key) use ($today_key) {
            return is_string($date_key) && $date_key >= $today_key;
        }, ARRAY_FILTER_USE_BOTH);
        $day_mode_map = array_filter($day_mode_map, static function ($mode, $date_key) use ($today_key) {
            return is_string($date_key) && $date_key >= $today_key;
        }, ARRAY_FILTER_USE_BOTH);
        $time_overrides = array_filter($time_overrides, static function ($slots, $date_key) use ($today_key) {
            return is_string($date_key) && $date_key >= $today_key;
        }, ARRAY_FILTER_USE_BOTH);
        $time_reservations = array_filter($time_reservations, static function ($slots, $date_key) use ($today_key) {
            return is_string($date_key) && $date_key >= $today_key;
        }, ARRAY_FILTER_USE_BOTH);
        $lead_hours = max(0, min(8760, (int) ($s['booking_lead_time_hours'] ?? 24)));
        $buffer_minutes = max(0, min(720, (int) ($s['booking_time_buffer_minutes'] ?? 30)));
        $booking_cutoff_ts = time() + ($lead_hours * HOUR_IN_SECONDS) + ($buffer_minutes * MINUTE_IN_SECONDS);

        wp_enqueue_style('abc-frontend');
        wp_enqueue_script('abc-frontend');

        $hold_minutes = (int) $s['booking_hold_minutes'];
        $hold_hours = rtrim(rtrim(number_format($hold_minutes / 60, 2, '.', ''), '0'), '.');

        $notice_text = self::replace_tokens((string) $s['booking_hold_notice_text'], [
            'hours' => $hold_hours,
            'minutes' => (string) $hold_minutes,
        ]);

        $success_message = self::replace_tokens((string) $s['booking_success_message'], [
            'hours' => $hold_hours,
            'minutes' => (string) $hold_minutes,
        ]);

        $result_calendar = isset($_GET['abc_calendar']) ? absint($_GET['abc_calendar']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $result_state = isset($_GET['abc_booking']) ? sanitize_text_field((string) $_GET['abc_booking']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $result_msg = isset($_GET['abc_msg']) ? sanitize_text_field((string) $_GET['abc_msg']) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $show_result = $result_calendar === $calendar_id && in_array($result_state, ['success', 'error'], true);

        $options = self::parse_options((string) $s['booking_options']);
        $theme_class = 'abc-theme-' . self::sanitize_choice((string) $s['theme_preset'], self::theme_presets(), 'dark');
        $bg_class = 'abc-bg-' . self::sanitize_choice((string) $s['background_style'], self::background_styles(), 'gradient');
        $font_class = 'abc-font-' . self::sanitize_choice((string) $s['font_preset'], self::font_presets(), 'modern');
        $advanced_styles_enabled = self::to_bool($s['advanced_styles_enabled'] ?? 0);
        $layout_class = $advanced_styles_enabled ? 'abc-layout-' . self::sanitize_choice((string) ($s['layout_mode'] ?? 'split'), self::layout_modes(), 'split') : '';
        $style_preset_class = $advanced_styles_enabled ? 'abc-style-' . self::sanitize_choice((string) ($s['style_preset'] ?? 'classic'), self::style_presets(), 'classic') : '';
        $density_class = $advanced_styles_enabled ? 'abc-density-' . self::sanitize_choice((string) ($s['density_mode'] ?? 'comfortable'), self::density_modes(), 'comfortable') : '';
        $size_class = $advanced_styles_enabled ? 'abc-size-' . self::sanitize_choice((string) ($s['font_size_mode'] ?? 'm'), self::font_size_modes(), 'm') : '';
        $button_shape_class = $advanced_styles_enabled ? 'abc-btnshape-' . self::sanitize_choice((string) ($s['button_shape'] ?? 'rounded'), self::button_shapes(), 'rounded') : '';
        $button_border_class = $advanced_styles_enabled ? 'abc-button-border-' . self::sanitize_choice((string) ($s['button_border_mode'] ?? 'normal'), self::button_border_modes(), 'normal') : '';
        $button_hover_class = $advanced_styles_enabled ? 'abc-button-hover-' . self::sanitize_choice((string) ($s['button_hover_mode'] ?? 'soft'), self::button_hover_modes(), 'soft') : '';
        $day_cell_style_class = $advanced_styles_enabled ? 'abc-daystyle-' . self::sanitize_choice((string) ($s['day_cell_style'] ?? 'soft'), self::day_cell_styles(), 'soft') : '';
        $animation_class = $advanced_styles_enabled ? 'abc-motion-' . self::sanitize_choice((string) ($s['animation_level'] ?? 'subtle'), self::animation_levels(), 'subtle') : '';
        $minimal_class = $advanced_styles_enabled && self::to_bool($s['minimal_mode'] ?? 0) ? ' abc-minimal' : '';
        $sticky_panel_class = $advanced_styles_enabled && self::to_bool($s['sticky_booking_panel'] ?? 1) ? ' abc-sticky-panel' : '';
        $style_vars = self::build_style_vars($s);

        ob_start();
        ?>
        <section class="abc-module <?php echo esc_attr(
            $theme_class . ' '
            . $bg_class . ' '
            . $font_class . ' '
            . $layout_class . ' '
            . $style_preset_class . ' '
            . $density_class . ' '
            . $size_class . ' '
            . $button_shape_class . ' '
            . $button_border_class . ' '
            . $button_hover_class . ' '
            . $day_cell_style_class . ' '
            . $animation_class
            . $minimal_class
            . $sticky_panel_class
        ); ?>" data-abc-module<?php echo $style_vars !== '' ? ' style="' . esc_attr($style_vars) . '"' : ''; ?>>
            <div class="abc-wrap">
                <aside class="abc-side">
                    <?php if ($s['section_title'] !== '') : ?><p class="abc-section-title"><?php echo esc_html((string) $s['section_title']); ?></p><?php endif; ?>
                    <?php if ($s['section_subtitle'] !== '') : ?><p class="abc-section-subtitle"><?php echo esc_html((string) $s['section_subtitle']); ?></p><?php endif; ?>
                    <?php if ($s['heading'] !== '') : ?><h2 class="abc-heading"><?php echo esc_html((string) $s['heading']); ?></h2><?php endif; ?>
                    <?php if ($s['description'] !== '') : ?><p class="abc-desc"><?php echo nl2br(esc_html((string) $s['description'])); ?></p><?php endif; ?>
                    <?php if ($s['cta_label'] !== '' && $s['cta_url'] !== '') : ?>
                        <a class="abc-cta" href="<?php echo esc_url((string) $s['cta_url']); ?>"><?php echo esc_html((string) $s['cta_label']); ?></a>
                    <?php endif; ?>
                    <?php if (! self::to_bool($s['legend_toggle_hidden'] ?? 0)) : ?>
                        <button type="button" class="abc-legend-toggle" data-abc-legend-toggle aria-expanded="true"><?php echo esc_html(self::tr('Hide legend', 'Ukryj legendę')); ?></button>
                    <?php endif; ?>
                    <div class="abc-legend" data-abc-legend>
                        <div><span class="dot available"></span><?php echo esc_html(self::tr('Available', 'Dostępny')); ?></div>
                        <div><span class="dot unavailable"></span><?php echo esc_html(self::tr('Unavailable', 'Niedostępny')); ?></div>
                    </div>
                </aside>

                <div class="abc-shell"
                    data-abc-calendar
                    data-abc-calendar-id="<?php echo (int) $calendar_id; ?>"
                    data-abc-months="<?php echo (int) $s['months_to_show']; ?>"
                    data-abc-offset="<?php echo (int) $s['start_month_offset']; ?>"
                    data-abc-today-key="<?php echo esc_attr($today_key); ?>"
                    data-abc-now-time="<?php echo esc_attr(wp_date('H:i')); ?>"
                    data-abc-booking-lead-hours="<?php echo (int) $lead_hours; ?>"
                    data-abc-booking-buffer-minutes="<?php echo (int) $buffer_minutes; ?>"
                    data-abc-booking-cutoff-date="<?php echo esc_attr(wp_date('Y-m-d', $booking_cutoff_ts)); ?>"
                    data-abc-booking-cutoff-time="<?php echo esc_attr(wp_date('H:i', $booking_cutoff_ts)); ?>"
                    data-abc-status-map="<?php echo esc_attr(wp_json_encode($status_map)); ?>"
                    data-abc-day-mode-default="<?php echo esc_attr((string) ($s['day_mode_default'] ?? 'slots')); ?>"
                    data-abc-day-mode-map="<?php echo esc_attr(wp_json_encode($day_mode_map)); ?>"
                    data-abc-time-default="<?php echo esc_attr(wp_json_encode($default_time_slots)); ?>"
                    data-abc-time-overrides="<?php echo esc_attr(wp_json_encode($time_overrides)); ?>"
                    data-abc-time-reservations="<?php echo esc_attr(wp_json_encode($time_reservations)); ?>">

                    <div class="abc-nav">
                        <button type="button" data-abc-prev aria-label="<?php echo esc_attr__('Prev', 'rdev-calendar'); ?>">←</button>
                        <h3 data-abc-month-label>—</h3>
                        <button type="button" data-abc-next aria-label="<?php echo esc_attr__('Next', 'rdev-calendar'); ?>">→</button>
                    </div>

                    <div class="abc-weekdays" data-abc-weekdays></div>
                    <div class="abc-days" data-abc-days></div>
                    <p class="abc-note" data-abc-note><?php echo esc_html(self::tr('Click a day to see status.', 'Kliknij dzień, aby zobaczyć status.')); ?></p>

                    <?php if ($show_result) : ?>
                        <div class="abc-result <?php echo $result_state === 'success' ? 'ok' : 'err'; ?>">
                            <?php echo esc_html($result_msg !== '' ? $result_msg : ($result_state === 'success' ? $success_message : (string) $s['booking_error_message'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="abc-booking" data-abc-booking-panel hidden>
                        <h4><?php echo esc_html((string) $s['booking_form_heading']); ?></h4>
                        <p><?php echo esc_html($notice_text); ?></p>

                        <?php if (! empty($options)) : ?>
                            <button type="button" class="abc-open" data-abc-open><?php echo esc_html(self::tr('Book now', 'Zarezerwuj')); ?></button>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-abc-form hidden>
                                <input type="hidden" name="action" value="abc_submit_booking_request">
                                <input type="hidden" name="abc_calendar_id" value="<?php echo (int) $calendar_id; ?>">
                                <input type="hidden" name="abc_honeypot" value="">
                                <?php wp_nonce_field('abc_submit_booking_request_' . $calendar_id, 'abc_nonce'); ?>

                                <label><?php echo esc_html(self::tr('Event date', 'Data wydarzenia')); ?> *
                                    <input type="text" name="abc_date_display" data-abc-date-display readonly>
                                    <input type="hidden" name="abc_date" data-abc-date>
                                </label>
                                <label><?php echo esc_html(self::tr('Time', 'Godzina')); ?> *
                                    <select name="abc_time" data-abc-time-select required>
                                        <option value=""><?php echo esc_html(self::tr('Choose time', 'Wybierz godzinę')); ?></option>
                                    </select>
                                </label>
                                <input type="hidden" name="abc_is_all_day" value="0" data-abc-is-all-day>
                                <label><?php echo esc_html(self::tr('Full name', 'Imię i nazwisko')); ?> *<input type="text" name="abc_full_name" required maxlength="120"></label>
                                <label><?php echo esc_html(self::tr('Service / Package', 'Usługa / Pakiet')); ?> *
                                    <select name="abc_option" required>
                                        <option value=""><?php echo esc_html(self::tr('Choose', 'Wybierz')); ?></option>
                                        <?php foreach ($options as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label><?php echo esc_html(self::tr('Email', 'E-mail')); ?> *<input type="email" name="abc_email" required maxlength="190"></label>
                                <label><?php echo esc_html(self::tr('Phone number', 'Numer telefonu')); ?> *<input type="text" name="abc_phone" required maxlength="50"></label>
                                <label><?php echo esc_html(self::tr('Message', 'Wiadomość')); ?><textarea name="abc_message" rows="4" maxlength="1500"></textarea></label>
                                <label class="abc-consent">
                                    <input type="checkbox" name="abc_consent" value="1" required>
                                    <span class="abc-consent-text"><?php echo wp_kses((string) $s['booking_consent_label'], ['a' => ['href' => true, 'target' => true, 'rel' => true], 'br' => []]); ?> *</span>
                                </label>
                                <button type="submit"><?php echo esc_html((string) $s['booking_form_submit_label']); ?></button>
                            </form>
                        <?php else : ?>
                            <p><?php echo esc_html(self::tr('Add "Service / Package" options in calendar settings.', 'Dodaj opcje „Usługa / Pakiet” w ustawieniach kalendarza.')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php

        return (string) ob_get_clean();
    }

}
}
