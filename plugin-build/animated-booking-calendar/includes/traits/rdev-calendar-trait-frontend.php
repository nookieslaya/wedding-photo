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
        $front_css_path = plugin_dir_path($base_file) . 'assets/css/frontend.css';
        $front_js_path = plugin_dir_path($base_file) . 'assets/js/frontend.js';
        $front_css_ver = file_exists($front_css_path) ? (string) filemtime($front_css_path) : self::VERSION;
        $front_js_ver = file_exists($front_js_path) ? (string) filemtime($front_js_path) : self::VERSION;
        wp_register_style('abc-frontend', $base . 'css/frontend.css', [], $front_css_ver);
        wp_register_script('abc-frontend', $base . 'js/frontend.js', [], $front_js_ver, true);
    }

    public static function render_shortcode(array $atts): string {
        $atts = shortcode_atts(['id' => 0], $atts, 'rdev_calendar');
        $calendar_id = absint($atts['id']);
        if ($calendar_id <= 0) {
            return '<div class="abc-error">Missing calendar ID.</div>';
        }

        $post = get_post($calendar_id);
        if (! $post || $post->post_type !== self::CALENDAR_CPT) {
            return '<div class="abc-error">Calendar not found.</div>';
        }

        self::maybe_cleanup_expired_holds();

        $s = self::get_calendar_settings($calendar_id);
        $status_map = self::normalize_status_map((string) $s['status_map']);
        $time_overrides = self::normalize_time_slots_overrides((string) ($s['time_slots_overrides'] ?? '{}'));
        $time_reservations = self::normalize_time_slot_reservations((string) ($s['time_slots_reservations'] ?? '{}'));
        $default_time_slots = self::parse_time_slots((string) ($s['booking_default_time_slots'] ?? ''));

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

        ob_start();
        ?>
        <section class="abc-module <?php echo esc_attr($theme_class . ' ' . $bg_class . ' ' . $font_class); ?>" data-abc-module>
            <div class="abc-wrap">
                <aside class="abc-side">
                    <?php if ($s['section_title'] !== '') : ?><p class="abc-section-title"><?php echo esc_html((string) $s['section_title']); ?></p><?php endif; ?>
                    <?php if ($s['section_subtitle'] !== '') : ?><p class="abc-section-subtitle"><?php echo esc_html((string) $s['section_subtitle']); ?></p><?php endif; ?>
                    <?php if ($s['heading'] !== '') : ?><h2 class="abc-heading"><?php echo esc_html((string) $s['heading']); ?></h2><?php endif; ?>
                    <?php if ($s['description'] !== '') : ?><p class="abc-desc"><?php echo nl2br(esc_html((string) $s['description'])); ?></p><?php endif; ?>
                    <?php if ($s['cta_label'] !== '' && $s['cta_url'] !== '') : ?>
                        <a class="abc-cta" href="<?php echo esc_url((string) $s['cta_url']); ?>"><?php echo esc_html((string) $s['cta_label']); ?></a>
                    <?php endif; ?>
                    <div class="abc-legend">
                        <div><span class="dot available"></span>Dostępny</div>
                        <div><span class="dot tentative"></span>Wstępna Rezerwacja</div>
                        <div><span class="dot booked"></span>Zajęty</div>
                    </div>
                </aside>

                <div class="abc-shell"
                    data-abc-calendar
                    data-abc-calendar-id="<?php echo (int) $calendar_id; ?>"
                    data-abc-months="<?php echo (int) $s['months_to_show']; ?>"
                    data-abc-offset="<?php echo (int) $s['start_month_offset']; ?>"
                    data-abc-status-map="<?php echo esc_attr(wp_json_encode($status_map)); ?>"
                    data-abc-time-default="<?php echo esc_attr(wp_json_encode($default_time_slots)); ?>"
                    data-abc-time-overrides="<?php echo esc_attr(wp_json_encode($time_overrides)); ?>"
                    data-abc-time-reservations="<?php echo esc_attr(wp_json_encode($time_reservations)); ?>">

                    <div class="abc-nav">
                        <button type="button" data-abc-prev aria-label="Prev">←</button>
                        <h3 data-abc-month-label>—</h3>
                        <button type="button" data-abc-next aria-label="Next">→</button>
                    </div>

                    <div class="abc-weekdays" data-abc-weekdays></div>
                    <div class="abc-days" data-abc-days></div>
                    <p class="abc-note" data-abc-note>Kliknij dzień, aby zobaczyć status.</p>

                    <?php if ($show_result) : ?>
                        <div class="abc-result <?php echo $result_state === 'success' ? 'ok' : 'err'; ?>">
                            <?php echo esc_html($result_msg !== '' ? $result_msg : ($result_state === 'success' ? $success_message : (string) $s['booking_error_message'])); ?>
                        </div>
                    <?php endif; ?>

                    <div class="abc-booking" data-abc-booking-panel hidden>
                        <h4><?php echo esc_html((string) $s['booking_form_heading']); ?></h4>
                        <p><?php echo esc_html($notice_text); ?></p>

                        <?php if (! empty($options)) : ?>
                            <button type="button" class="abc-open" data-abc-open>Zarezerwuj</button>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-abc-form hidden>
                                <input type="hidden" name="action" value="abc_submit_booking_request">
                                <input type="hidden" name="abc_calendar_id" value="<?php echo (int) $calendar_id; ?>">
                                <input type="hidden" name="abc_honeypot" value="">
                                <?php wp_nonce_field('abc_submit_booking_request_' . $calendar_id, 'abc_nonce'); ?>

                                <label>Data wydarzenia *
                                    <input type="text" name="abc_date_display" data-abc-date-display readonly>
                                    <input type="hidden" name="abc_date" data-abc-date>
                                </label>
                                <label>Godzina *
                                    <select name="abc_time" data-abc-time-select required>
                                        <option value="">Wybierz godzinę</option>
                                    </select>
                                </label>
                                <label>Imię i nazwisko *<input type="text" name="abc_full_name" required maxlength="120"></label>
                                <label>Usługa / Pakiet *
                                    <select name="abc_option" required>
                                        <option value="">Wybierz</option>
                                        <?php foreach ($options as $opt) : ?>
                                            <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>E-mail *<input type="email" name="abc_email" required maxlength="190"></label>
                                <label>Numer telefonu *<input type="text" name="abc_phone" required maxlength="50"></label>
                                <label>Wiadomość<textarea name="abc_message" rows="4" maxlength="1500"></textarea></label>
                                <label class="abc-consent"><input type="checkbox" name="abc_consent" value="1" required> <?php echo esc_html((string) $s['booking_consent_label']); ?> *</label>
                                <button type="submit"><?php echo esc_html((string) $s['booking_form_submit_label']); ?></button>
                            </form>
                        <?php else : ?>
                            <p>Dodaj opcje „Usługa / Pakiet” w ustawieniach kalendarza.</p>
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
