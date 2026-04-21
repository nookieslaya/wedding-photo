<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Settings_Trait')) {
trait Rdev_Calendar_Settings_Trait {
    private static function defaults(): array {
        return [
            'section_title' => 'KALENDARZ',
            'section_subtitle' => 'DOSTĘPNOŚĆ',
            'theme_preset' => 'dark',
            'background_style' => 'gradient',
            'font_preset' => 'modern',
            'heading' => 'Sprawdź dostępne terminy',
            'description' => '',
            'months_to_show' => 12,
            'start_month_offset' => 0,
            'cta_label' => '',
            'cta_url' => '',
            'status_map' => '{}',
            'booking_notification_email' => '',
            'booking_from_email' => '',
            'booking_from_name' => '',
            'booking_hold_minutes' => 2880,
            'booking_default_time_slots' => "10:00\n12:00\n14:00\n16:00",
            'booking_hold_notice_text' => 'Rezerwacja terminu jest wstępna i trwa {hours}h ({minutes} min). Po tym czasie termin wraca do puli wolnych, jeśli nie zostanie potwierdzony.',
            'booking_hold_note_template' => 'Wstępna rezerwacja na {hours}h ({minutes} min), do {expires}.',
            'booking_success_message' => 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na {hours}h.',
            'booking_error_message' => 'Nie udało się wysłać zgłoszenia. Sprawdź dane i spróbuj ponownie.',
            'booking_send_initial_email' => 1,
            'booking_send_expired_email' => 1,
            'booking_send_approved_email' => 1,
            'booking_send_rejected_email' => 1,
            'booking_client_initial_email_subject' => 'Potwierdzenie wstępnej rezerwacji terminu',
            'booking_client_initial_email_body' => "Dziękuję za zapytanie.\n\nTwój termin został wstępnie zablokowany na {hours}h ({minutes} min).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nHold do: {expires}\n\nSkontaktuję się z Tobą, aby potwierdzić szczegóły.\n\n{site_name}",
            'booking_client_expired_email_subject' => 'Wstępna rezerwacja wygasła',
            'booking_client_expired_email_body' => "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}",
            'booking_client_approved_email_subject' => 'Rezerwacja terminu została potwierdzona',
            'booking_client_approved_email_body' => "Cześć {full_name},\n\nTwoja rezerwacja została potwierdzona.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nW razie pytań odpowiedz na tę wiadomość.\n\n{site_name}",
            'booking_client_rejected_email_subject' => 'Rezerwacja terminu nie została potwierdzona',
            'booking_client_rejected_email_body' => "Cześć {full_name},\n\nNiestety nie mogliśmy potwierdzić rezerwacji tego terminu.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nMożesz wybrać inny dostępny termin i wysłać nowe zapytanie.\n\n{site_name}",
            'booking_form_heading' => 'Zarezerwuj termin',
            'booking_form_submit_label' => 'Wyślij rezerwację',
            'booking_consent_label' => 'Zapoznałam/em się z moim stylem pracy i akceptuję kontakt zwrotny.',
            'booking_options' => "Ślub — Pakiet Premium\nPrzyjęcie — Reportaż\nSesja rodzinna — Mini",
        ];
    }

    private static function key_map(): array {
        return [
            'section_title' => '_abc_section_title',
            'section_subtitle' => '_abc_section_subtitle',
            'theme_preset' => '_abc_theme_preset',
            'background_style' => '_abc_background_style',
            'font_preset' => '_abc_font_preset',
            'heading' => '_abc_heading',
            'description' => '_abc_description',
            'months_to_show' => '_abc_months_to_show',
            'start_month_offset' => '_abc_start_month_offset',
            'cta_label' => '_abc_cta_label',
            'cta_url' => '_abc_cta_url',
            'status_map' => '_abc_status_map',
            'booking_notification_email' => '_abc_booking_notification_email',
            'booking_from_email' => '_abc_booking_from_email',
            'booking_from_name' => '_abc_booking_from_name',
            'booking_hold_minutes' => '_abc_booking_hold_minutes',
            'booking_default_time_slots' => '_abc_booking_default_time_slots',
            'booking_hold_notice_text' => '_abc_booking_hold_notice_text',
            'booking_hold_note_template' => '_abc_booking_hold_note_template',
            'booking_success_message' => '_abc_booking_success_message',
            'booking_error_message' => '_abc_booking_error_message',
            'booking_send_initial_email' => '_abc_booking_send_initial_email',
            'booking_send_expired_email' => '_abc_booking_send_expired_email',
            'booking_send_approved_email' => '_abc_booking_send_approved_email',
            'booking_send_rejected_email' => '_abc_booking_send_rejected_email',
            'booking_client_initial_email_subject' => '_abc_booking_client_initial_email_subject',
            'booking_client_initial_email_body' => '_abc_booking_client_initial_email_body',
            'booking_client_expired_email_subject' => '_abc_booking_client_expired_email_subject',
            'booking_client_expired_email_body' => '_abc_booking_client_expired_email_body',
            'booking_client_approved_email_subject' => '_abc_booking_client_approved_email_subject',
            'booking_client_approved_email_body' => '_abc_booking_client_approved_email_body',
            'booking_client_rejected_email_subject' => '_abc_booking_client_rejected_email_subject',
            'booking_client_rejected_email_body' => '_abc_booking_client_rejected_email_body',
            'booking_form_heading' => '_abc_booking_form_heading',
            'booking_form_submit_label' => '_abc_booking_form_submit_label',
            'booking_consent_label' => '_abc_booking_consent_label',
            'booking_options' => '_abc_booking_options',
            'time_slots_overrides' => '_abc_time_slots_overrides',
            'time_slots_reservations' => '_abc_time_slots_reservations',
        ];
    }

    private static function get_calendar_settings(int $calendar_id): array {
        $defaults = self::defaults();
        foreach (self::key_map() as $field => $meta_key) {
            $value = get_post_meta($calendar_id, $meta_key, true);
            if ($value !== '' && $value !== null) {
                $defaults[$field] = $value;
            }
        }

        $defaults['months_to_show'] = max(3, min(24, (int) $defaults['months_to_show']));
        $defaults['start_month_offset'] = max(-12, min(12, (int) $defaults['start_month_offset']));
        $defaults['booking_hold_minutes'] = max(1, min(10080, (int) $defaults['booking_hold_minutes']));

        $defaults['booking_send_initial_email'] = self::to_bool($defaults['booking_send_initial_email']);
        $defaults['booking_send_expired_email'] = self::to_bool($defaults['booking_send_expired_email']);
        $defaults['booking_send_approved_email'] = self::to_bool($defaults['booking_send_approved_email']);
        $defaults['booking_send_rejected_email'] = self::to_bool($defaults['booking_send_rejected_email']);
        $defaults['theme_preset'] = self::sanitize_choice((string) $defaults['theme_preset'], self::theme_presets(), 'dark');
        $defaults['background_style'] = self::sanitize_choice((string) $defaults['background_style'], self::background_styles(), 'gradient');
        $defaults['font_preset'] = self::sanitize_choice((string) $defaults['font_preset'], self::font_presets(), 'modern');

        if (! is_string($defaults['status_map']) || trim($defaults['status_map']) === '') {
            $defaults['status_map'] = '{}';
        }
        if (! is_string($defaults['time_slots_overrides']) || trim($defaults['time_slots_overrides']) === '') {
            $defaults['time_slots_overrides'] = '{}';
        }
        if (! is_string($defaults['time_slots_reservations']) || trim($defaults['time_slots_reservations']) === '') {
            $defaults['time_slots_reservations'] = '{}';
        }

        $defaults['booking_default_time_slots'] = implode("\n", self::parse_time_slots((string) $defaults['booking_default_time_slots']));

        return $defaults;
    }

    public static function render_settings_metabox(\WP_Post $post): void {
        $s = self::get_calendar_settings((int) $post->ID);
        wp_nonce_field('abc_save_calendar_meta', 'abc_calendar_meta_nonce');

        $text = static function ($v) {
            return esc_attr((string) $v);
        };
        $textarea = static function ($v) {
            return esc_textarea((string) $v);
        };
        ?>
        <table class="form-table abc-settings-table" role="presentation">
            <tr><th><label for="abc_section_title">Section title</label></th><td><input class="regular-text" id="abc_section_title" name="abc_settings[section_title]" value="<?php echo $text($s['section_title']); ?>"></td></tr>
            <tr><th><label for="abc_section_subtitle">Section subtitle</label></th><td><input class="regular-text" id="abc_section_subtitle" name="abc_settings[section_subtitle]" value="<?php echo $text($s['section_subtitle']); ?>"></td></tr>
            <tr>
                <th><label for="abc_theme_preset">Theme preset</label></th>
                <td>
                    <select id="abc_theme_preset" name="abc_settings[theme_preset]">
                        <?php foreach (self::theme_presets() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['theme_preset'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="abc_background_style">Background style</label></th>
                <td>
                    <select id="abc_background_style" name="abc_settings[background_style]">
                        <?php foreach (self::background_styles() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['background_style'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="abc_font_preset">Font preset</label></th>
                <td>
                    <select id="abc_font_preset" name="abc_settings[font_preset]">
                        <?php foreach (self::font_presets() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['font_preset'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr><th><label for="abc_heading">Heading</label></th><td><input class="regular-text" id="abc_heading" name="abc_settings[heading]" value="<?php echo $text($s['heading']); ?>"></td></tr>
            <tr><th><label for="abc_description">Description</label></th><td><textarea class="large-text" rows="3" id="abc_description" name="abc_settings[description]"><?php echo $textarea($s['description']); ?></textarea></td></tr>
            <tr><th><label for="abc_months_to_show">Months to show</label></th><td><input type="number" min="3" max="24" id="abc_months_to_show" name="abc_settings[months_to_show]" value="<?php echo (int) $s['months_to_show']; ?>"></td></tr>
            <tr><th><label for="abc_start_month_offset">Start month offset</label></th><td><input type="number" min="-12" max="12" id="abc_start_month_offset" name="abc_settings[start_month_offset]" value="<?php echo (int) $s['start_month_offset']; ?>"></td></tr>
            <tr><th><label for="abc_cta_label">CTA label</label></th><td><input class="regular-text" id="abc_cta_label" name="abc_settings[cta_label]" value="<?php echo $text($s['cta_label']); ?>"></td></tr>
            <tr><th><label for="abc_cta_url">CTA URL</label></th><td><input class="regular-text" id="abc_cta_url" name="abc_settings[cta_url]" value="<?php echo $text($s['cta_url']); ?>"></td></tr>
            <tr><th><label for="abc_booking_notification_email">Notification email</label></th><td><input type="email" class="regular-text" id="abc_booking_notification_email" name="abc_settings[booking_notification_email]" value="<?php echo $text($s['booking_notification_email']); ?>"></td></tr>
            <tr><th><label for="abc_booking_from_email">From email</label></th><td><input type="email" class="regular-text" id="abc_booking_from_email" name="abc_settings[booking_from_email]" value="<?php echo $text($s['booking_from_email']); ?>"></td></tr>
            <tr><th><label for="abc_booking_from_name">From name</label></th><td><input class="regular-text" id="abc_booking_from_name" name="abc_settings[booking_from_name]" value="<?php echo $text($s['booking_from_name']); ?>"></td></tr>
            <tr><th><label for="abc_booking_hold_minutes">Hold duration (minutes)</label></th><td><input type="number" min="1" max="10080" id="abc_booking_hold_minutes" name="abc_settings[booking_hold_minutes]" value="<?php echo (int) $s['booking_hold_minutes']; ?>"></td></tr>
            <tr><th><label for="abc_booking_default_time_slots">Default available hours</label></th><td><textarea class="large-text" rows="5" id="abc_booking_default_time_slots" name="abc_settings[booking_default_time_slots]"><?php echo $textarea($s['booking_default_time_slots']); ?></textarea><p class="description">One time per line in HH:MM format, e.g. 10:00</p></td></tr>
            <tr><th><label for="abc_booking_hold_notice_text">Hold notice text</label></th><td><textarea class="large-text" rows="2" id="abc_booking_hold_notice_text" name="abc_settings[booking_hold_notice_text]"><?php echo $textarea($s['booking_hold_notice_text']); ?></textarea><p class="description">Placeholders: {hours}, {minutes}</p></td></tr>
            <tr><th><label for="abc_booking_hold_note_template">Calendar hold note template</label></th><td><textarea class="large-text" rows="2" id="abc_booking_hold_note_template" name="abc_settings[booking_hold_note_template]"><?php echo $textarea($s['booking_hold_note_template']); ?></textarea><p class="description">Placeholders: {hours}, {minutes}, {expires}</p></td></tr>
            <tr><th><label for="abc_booking_success_message">Success message</label></th><td><textarea class="large-text" rows="2" id="abc_booking_success_message" name="abc_settings[booking_success_message]"><?php echo $textarea($s['booking_success_message']); ?></textarea></td></tr>
            <tr><th><label for="abc_booking_error_message">Error message</label></th><td><textarea class="large-text" rows="2" id="abc_booking_error_message" name="abc_settings[booking_error_message]"><?php echo $textarea($s['booking_error_message']); ?></textarea></td></tr>

            <tr><th>Send client email after booking</th><td><label><input type="checkbox" name="abc_settings[booking_send_initial_email]" value="1" <?php checked($s['booking_send_initial_email']); ?>> Enable</label></td></tr>
            <tr><th>Send client email when hold expires</th><td><label><input type="checkbox" name="abc_settings[booking_send_expired_email]" value="1" <?php checked($s['booking_send_expired_email']); ?>> Enable</label></td></tr>
            <tr><th>Send client email when booking is approved</th><td><label><input type="checkbox" name="abc_settings[booking_send_approved_email]" value="1" <?php checked($s['booking_send_approved_email']); ?>> Enable</label></td></tr>
            <tr><th>Send client email when booking is rejected</th><td><label><input type="checkbox" name="abc_settings[booking_send_rejected_email]" value="1" <?php checked($s['booking_send_rejected_email']); ?>> Enable</label></td></tr>

            <tr><th><label for="abc_booking_client_initial_email_subject">Client email subject (after booking)</label></th><td><input class="regular-text" id="abc_booking_client_initial_email_subject" name="abc_settings[booking_client_initial_email_subject]" value="<?php echo $text($s['booking_client_initial_email_subject']); ?>"></td></tr>
            <tr><th><label for="abc_booking_client_initial_email_body">Client email body (after booking)</label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_initial_email_body" name="abc_settings[booking_client_initial_email_body]"><?php echo $textarea($s['booking_client_initial_email_body']); ?></textarea><p class="description">Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}</p></td></tr>
            <tr><th><label for="abc_booking_client_expired_email_subject">Client email subject (hold expired)</label></th><td><input class="regular-text" id="abc_booking_client_expired_email_subject" name="abc_settings[booking_client_expired_email_subject]" value="<?php echo $text($s['booking_client_expired_email_subject']); ?>"></td></tr>
            <tr><th><label for="abc_booking_client_expired_email_body">Client email body (hold expired)</label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_expired_email_body" name="abc_settings[booking_client_expired_email_body]"><?php echo $textarea($s['booking_client_expired_email_body']); ?></textarea><p class="description">Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}</p></td></tr>
            <tr><th><label for="abc_booking_client_approved_email_subject">Client email subject (approved)</label></th><td><input class="regular-text" id="abc_booking_client_approved_email_subject" name="abc_settings[booking_client_approved_email_subject]" value="<?php echo $text($s['booking_client_approved_email_subject']); ?>"></td></tr>
            <tr><th><label for="abc_booking_client_approved_email_body">Client email body (approved)</label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_approved_email_body" name="abc_settings[booking_client_approved_email_body]"><?php echo $textarea($s['booking_client_approved_email_body']); ?></textarea><p class="description">Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}</p></td></tr>
            <tr><th><label for="abc_booking_client_rejected_email_subject">Client email subject (rejected)</label></th><td><input class="regular-text" id="abc_booking_client_rejected_email_subject" name="abc_settings[booking_client_rejected_email_subject]" value="<?php echo $text($s['booking_client_rejected_email_subject']); ?>"></td></tr>
            <tr><th><label for="abc_booking_client_rejected_email_body">Client email body (rejected)</label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_rejected_email_body" name="abc_settings[booking_client_rejected_email_body]"><?php echo $textarea($s['booking_client_rejected_email_body']); ?></textarea><p class="description">Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}</p></td></tr>

            <tr><th><label for="abc_booking_form_heading">Form heading</label></th><td><input class="regular-text" id="abc_booking_form_heading" name="abc_settings[booking_form_heading]" value="<?php echo $text($s['booking_form_heading']); ?>"></td></tr>
            <tr><th><label for="abc_booking_form_submit_label">Submit button label</label></th><td><input class="regular-text" id="abc_booking_form_submit_label" name="abc_settings[booking_form_submit_label]" value="<?php echo $text($s['booking_form_submit_label']); ?>"></td></tr>
            <tr><th><label for="abc_booking_consent_label">Consent label</label></th><td><input class="large-text" id="abc_booking_consent_label" name="abc_settings[booking_consent_label]" value="<?php echo $text($s['booking_consent_label']); ?>"></td></tr>
            <tr><th><label for="abc_booking_options">Service / Package options</label></th><td><textarea class="large-text" rows="5" id="abc_booking_options" name="abc_settings[booking_options]"><?php echo $textarea($s['booking_options']); ?></textarea><p class="description">One option per line.</p></td></tr>
        </table>
        <?php
    }

    public static function render_availability_metabox(\WP_Post $post): void {
        $s = self::get_calendar_settings((int) $post->ID);
        ?>
        <p>Use manager below to set day statuses and optional notes.</p>
        <textarea id="abc_status_map" name="abc_settings[status_map]" rows="4" class="large-text code" style="display:none;"><?php echo esc_textarea((string) $s['status_map']); ?></textarea>
        <textarea id="abc_time_slots_overrides" name="abc_settings[time_slots_overrides]" rows="4" class="large-text code" style="display:none;"><?php echo esc_textarea((string) ($s['time_slots_overrides'] ?? '{}')); ?></textarea>
        <div class="abc-admin-manager"
             data-abc-status-map="<?php echo esc_attr((string) $s['status_map']); ?>"
             data-abc-time-overrides="<?php echo esc_attr((string) ($s['time_slots_overrides'] ?? '{}')); ?>"
             data-abc-time-reservations="<?php echo esc_attr((string) ($s['time_slots_reservations'] ?? '{}')); ?>"
             data-abc-months="<?php echo (int) $s['months_to_show']; ?>"
             data-abc-offset="<?php echo (int) $s['start_month_offset']; ?>"></div>
        <?php
    }

    public static function save_calendar_meta(int $post_id): void {
        if (! isset($_POST['abc_calendar_meta_nonce']) || ! wp_verify_nonce((string) $_POST['abc_calendar_meta_nonce'], 'abc_save_calendar_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        $incoming = $_POST['abc_settings'] ?? null;
        if (! is_array($incoming)) {
            return;
        }

        $defaults = self::defaults();

        $save = [
            'section_title' => sanitize_text_field((string) ($incoming['section_title'] ?? $defaults['section_title'])),
            'section_subtitle' => sanitize_text_field((string) ($incoming['section_subtitle'] ?? $defaults['section_subtitle'])),
            'theme_preset' => self::sanitize_choice((string) ($incoming['theme_preset'] ?? $defaults['theme_preset']), self::theme_presets(), 'dark'),
            'background_style' => self::sanitize_choice((string) ($incoming['background_style'] ?? $defaults['background_style']), self::background_styles(), 'gradient'),
            'font_preset' => self::sanitize_choice((string) ($incoming['font_preset'] ?? $defaults['font_preset']), self::font_presets(), 'modern'),
            'heading' => sanitize_text_field((string) ($incoming['heading'] ?? $defaults['heading'])),
            'description' => sanitize_textarea_field((string) ($incoming['description'] ?? $defaults['description'])),
            'months_to_show' => max(3, min(24, (int) ($incoming['months_to_show'] ?? $defaults['months_to_show']))),
            'start_month_offset' => max(-12, min(12, (int) ($incoming['start_month_offset'] ?? $defaults['start_month_offset']))),
            'cta_label' => sanitize_text_field((string) ($incoming['cta_label'] ?? $defaults['cta_label'])),
            'cta_url' => esc_url_raw((string) ($incoming['cta_url'] ?? $defaults['cta_url'])),
            'booking_notification_email' => sanitize_email((string) ($incoming['booking_notification_email'] ?? '')),
            'booking_from_email' => sanitize_email((string) ($incoming['booking_from_email'] ?? '')),
            'booking_from_name' => sanitize_text_field((string) ($incoming['booking_from_name'] ?? '')),
            'booking_hold_minutes' => max(1, min(10080, (int) ($incoming['booking_hold_minutes'] ?? $defaults['booking_hold_minutes']))),
            'booking_default_time_slots' => sanitize_textarea_field((string) ($incoming['booking_default_time_slots'] ?? $defaults['booking_default_time_slots'])),
            'booking_hold_notice_text' => sanitize_textarea_field((string) ($incoming['booking_hold_notice_text'] ?? $defaults['booking_hold_notice_text'])),
            'booking_hold_note_template' => sanitize_textarea_field((string) ($incoming['booking_hold_note_template'] ?? $defaults['booking_hold_note_template'])),
            'booking_success_message' => sanitize_textarea_field((string) ($incoming['booking_success_message'] ?? $defaults['booking_success_message'])),
            'booking_error_message' => sanitize_textarea_field((string) ($incoming['booking_error_message'] ?? $defaults['booking_error_message'])),
            'booking_send_initial_email' => isset($incoming['booking_send_initial_email']) ? 1 : 0,
            'booking_send_expired_email' => isset($incoming['booking_send_expired_email']) ? 1 : 0,
            'booking_send_approved_email' => isset($incoming['booking_send_approved_email']) ? 1 : 0,
            'booking_send_rejected_email' => isset($incoming['booking_send_rejected_email']) ? 1 : 0,
            'booking_client_initial_email_subject' => sanitize_text_field((string) ($incoming['booking_client_initial_email_subject'] ?? $defaults['booking_client_initial_email_subject'])),
            'booking_client_initial_email_body' => sanitize_textarea_field((string) ($incoming['booking_client_initial_email_body'] ?? $defaults['booking_client_initial_email_body'])),
            'booking_client_expired_email_subject' => sanitize_text_field((string) ($incoming['booking_client_expired_email_subject'] ?? $defaults['booking_client_expired_email_subject'])),
            'booking_client_expired_email_body' => sanitize_textarea_field((string) ($incoming['booking_client_expired_email_body'] ?? $defaults['booking_client_expired_email_body'])),
            'booking_client_approved_email_subject' => sanitize_text_field((string) ($incoming['booking_client_approved_email_subject'] ?? $defaults['booking_client_approved_email_subject'])),
            'booking_client_approved_email_body' => sanitize_textarea_field((string) ($incoming['booking_client_approved_email_body'] ?? $defaults['booking_client_approved_email_body'])),
            'booking_client_rejected_email_subject' => sanitize_text_field((string) ($incoming['booking_client_rejected_email_subject'] ?? $defaults['booking_client_rejected_email_subject'])),
            'booking_client_rejected_email_body' => sanitize_textarea_field((string) ($incoming['booking_client_rejected_email_body'] ?? $defaults['booking_client_rejected_email_body'])),
            'booking_form_heading' => sanitize_text_field((string) ($incoming['booking_form_heading'] ?? $defaults['booking_form_heading'])),
            'booking_form_submit_label' => sanitize_text_field((string) ($incoming['booking_form_submit_label'] ?? $defaults['booking_form_submit_label'])),
            'booking_consent_label' => sanitize_text_field((string) ($incoming['booking_consent_label'] ?? $defaults['booking_consent_label'])),
            'booking_options' => sanitize_textarea_field((string) ($incoming['booking_options'] ?? $defaults['booking_options'])),
            'time_slots_reservations' => (string) get_post_meta($post_id, '_abc_time_slots_reservations', true),
        ];

        $status_map_raw = trim((string) wp_unslash($incoming['status_map'] ?? '{}'));
        $status_map = self::normalize_status_map($status_map_raw);
        $save['status_map'] = wp_json_encode($status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $time_overrides_raw = trim((string) wp_unslash($incoming['time_slots_overrides'] ?? '{}'));
        $save['time_slots_overrides'] = wp_json_encode(
            self::normalize_time_slots_overrides($time_overrides_raw),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        foreach (self::key_map() as $field => $meta_key) {
            update_post_meta($post_id, $meta_key, $save[$field]);
        }
    }

    private static function parse_options(string $raw): array {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $result = [];
        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $result[] = sanitize_text_field($line);
            }
        }
        return array_values(array_unique($result));
    }

    private static function mail_headers(array $settings): array {
        $from_email = sanitize_email((string) ($settings['booking_from_email'] ?? ''));
        $from_name = sanitize_text_field((string) ($settings['booking_from_name'] ?? ''));
        if (! is_email($from_email)) {
            return [];
        }
        if ($from_name !== '') {
            return ['From: ' . $from_name . ' <' . $from_email . '>'];
        }
        return ['From: ' . $from_email];
    }

    private static function parse_time_slots(string $raw): array {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $result = [];
        foreach ($lines as $line) {
            $slot = trim((string) $line);
            if ($slot === '') {
                continue;
            }
            if (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
                continue;
            }
            $result[] = $slot;
        }
        $result = array_values(array_unique($result));
        sort($result);
        return $result;
    }

    private static function normalize_time_slots_overrides(string $raw): array {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach ($decoded as $date => $slots) {
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }
            $slot_list = [];
            if (is_array($slots)) {
                foreach ($slots as $slot) {
                    $slot = is_string($slot) ? trim($slot) : '';
                    if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
                        $slot_list[] = $slot;
                    }
                }
            }
            $slot_list = array_values(array_unique($slot_list));
            sort($slot_list);
            $out[$date] = $slot_list;
        }
        ksort($out);
        return $out;
    }

    private static function normalize_time_slot_reservations(string $raw): array {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $date => $slots) {
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! is_array($slots)) {
                continue;
            }
            foreach ($slots as $slot => $entry) {
                if (! is_string($slot) || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot) || ! is_array($entry)) {
                    continue;
                }
                $status = sanitize_text_field((string) ($entry['status'] ?? ''));
                if (! in_array($status, ['hold', 'booked'], true)) {
                    continue;
                }
                $expires = isset($entry['expires_at']) ? (int) $entry['expires_at'] : 0;
                $request_id = isset($entry['request_id']) ? (int) $entry['request_id'] : 0;
                if ($status === 'hold' && $expires > 0 && $expires <= time()) {
                    continue;
                }
                $out[$date][$slot] = [
                    'status' => $status,
                    'expires_at' => $expires,
                    'request_id' => $request_id,
                ];
            }
            if (isset($out[$date])) {
                ksort($out[$date]);
            }
        }
        ksort($out);
        return $out;
    }

    private static function get_date_slots(string $date, array $settings, array $overrides): array {
        $default_slots = self::parse_time_slots((string) ($settings['booking_default_time_slots'] ?? ''));
        $slots = $default_slots;
        if (isset($overrides[$date]) && is_array($overrides[$date])) {
            $slots = [];
            foreach ($overrides[$date] as $slot) {
                if (is_string($slot) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
                    $slots[] = $slot;
                }
            }
            $slots = array_values(array_unique($slots));
            sort($slots);
        }
        return $slots;
    }

    private static function apply_slot_aggregate_to_status_map(
        string $date,
        array $settings,
        array $overrides,
        array $reservations,
        array $status_map
    ): array {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $status_map;
        }
        $slots = self::get_date_slots($date, $settings, $overrides);
        if (empty($slots)) {
            return $status_map;
        }

        $current_status = is_array($status_map[$date] ?? null) ? sanitize_text_field((string) (($status_map[$date]['status'] ?? 'none'))): 'none';
        $day_reservations = isset($reservations[$date]) && is_array($reservations[$date]) ? $reservations[$date] : [];
        if ($current_status === 'booked' && empty($day_reservations)) {
            return $status_map;
        }

        $reserved = 0;
        $has_hold = false;
        $has_booked = false;
        foreach ($slots as $slot) {
            $entry = $day_reservations[$slot] ?? null;
            if (! is_array($entry)) {
                continue;
            }
            $status = sanitize_text_field((string) ($entry['status'] ?? ''));
            if ($status === 'hold') {
                $expires = (int) ($entry['expires_at'] ?? 0);
                if ($expires > 0 && $expires <= time()) {
                    continue;
                }
                $reserved++;
                $has_hold = true;
                continue;
            }
            if ($status === 'booked') {
                $reserved++;
                $has_booked = true;
            }
        }

        $free = max(0, count($slots) - $reserved);
        if ($free > 0) {
            $status_map[$date] = ['status' => 'available', 'note' => ''];
        } elseif ($has_booked && ! $has_hold) {
            $status_map[$date] = ['status' => 'booked', 'note' => ''];
        } else {
            $status_map[$date] = ['status' => 'tentative', 'note' => ''];
        }

        return $status_map;
    }

    private static function normalize_status_map(string $raw): array {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }

        $allowed = ['available', 'tentative', 'booked'];
        $out = [];
        foreach ($decoded as $date => $value) {
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }

            $status = '';
            $note = '';
            $hold_expires = 0;
            $hold_request_id = 0;

            if (is_string($value)) {
                $status = trim($value);
            } elseif (is_array($value)) {
                $status = sanitize_text_field((string) ($value['status'] ?? ''));
                $note = sanitize_text_field((string) ($value['note'] ?? ''));
                $hold_expires = isset($value['hold_expires_at']) ? (int) $value['hold_expires_at'] : 0;
                $hold_request_id = isset($value['hold_request_id']) ? (int) $value['hold_request_id'] : 0;
            }

            if (! in_array($status, $allowed, true)) {
                continue;
            }

            if ($status === 'tentative' && $hold_expires > 0 && $hold_expires <= time()) {
                $out[$date] = ['status' => 'available', 'note' => ''];
                continue;
            }

            $entry = ['status' => $status, 'note' => $note];
            if ($hold_expires > 0) {
                $entry['hold_expires_at'] = $hold_expires;
            }
            if ($hold_request_id > 0) {
                $entry['hold_request_id'] = $hold_request_id;
            }

            $out[$date] = $entry;
        }

        return $out;
    }

    private static function resolve_day_status(string $date_key, array $status_map): array {
        $entry = $status_map[$date_key] ?? null;
        if (! is_array($entry)) {
            return ['status' => 'none', 'note' => ''];
        }

        $status = sanitize_text_field((string) ($entry['status'] ?? 'none'));
        $note = sanitize_text_field((string) ($entry['note'] ?? ''));
        $hold_expires = isset($entry['hold_expires_at']) ? (int) $entry['hold_expires_at'] : 0;

        if ($status === 'tentative' && $hold_expires > 0 && $hold_expires <= time()) {
            return ['status' => 'available', 'note' => ''];
        }

        return ['status' => $status, 'note' => $note];
    }

    private static function save_status_map(int $calendar_id, array $status_map): void {
        update_post_meta($calendar_id, '_abc_status_map', wp_json_encode($status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function replace_tokens(string $template, array $tokens): string {
        $pairs = [];
        foreach ($tokens as $k => $v) {
            $pairs['{' . $k . '}'] = (string) $v;
        }
        return strtr($template, $pairs);
    }

    private static function to_bool($value): bool {
        return ! in_array($value, [0, '0', false, 'false', '', null], true);
    }

    private static function sanitize_choice(string $value, array $allowed_map, string $fallback): string {
        $key = sanitize_key($value);
        return array_key_exists($key, $allowed_map) ? $key : $fallback;
    }

    private static function theme_presets(): array {
        return [
            'dark' => 'Dark',
            'light' => 'Light',
            'white' => 'White',
            'graphite' => 'Graphite',
            'sand' => 'Sand',
        ];
    }

    private static function background_styles(): array {
        return [
            'solid' => 'Solid',
            'gradient' => 'Gradient',
            'mesh' => 'Mesh',
        ];
    }

    private static function font_presets(): array {
        return [
            'modern' => 'Modern Sans',
            'editorial' => 'Editorial Serif',
            'clean' => 'Clean UI',
            'mono' => 'Monospace',
        ];
    }
}
}
