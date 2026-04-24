<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! trait_exists('Rdev_Calendar_Settings_Trait')) {
trait Rdev_Calendar_Settings_Trait {
    private static function is_polish_locale(): bool {
        $locale = strtolower((string) determine_locale());
        return str_starts_with($locale, 'pl');
    }

    private static function locale_tag(): string {
        return self::is_polish_locale() ? 'pl-PL' : 'en-US';
    }

    private static function tr(string $en, string $pl): string {
        return self::is_polish_locale() ? $pl : $en;
    }

    private static function defaults(): array {
        return [
            'section_title' => self::tr('CALENDAR', 'KALENDARZ'),
            'section_subtitle' => self::tr('AVAILABILITY', 'DOSTĘPNOŚĆ'),
            'theme_preset' => 'dark',
            'background_style' => 'gradient',
            'font_preset' => 'modern',
            'legend_toggle_hidden' => 0,
            'advanced_styles_enabled' => 0,
            'custom_colors_enabled' => 0,
            'layout_mode' => 'split',
            'style_preset' => 'classic',
            'density_mode' => 'comfortable',
            'font_size_mode' => 'm',
            'button_shape' => 'rounded',
            'button_border_mode' => 'normal',
            'button_hover_mode' => 'soft',
            'day_cell_style' => 'soft',
            'sticky_booking_panel' => 1,
            'animation_level' => 'subtle',
            'minimal_mode' => 0,
            'custom_bg_color' => '',
            'custom_text_color' => '',
            'custom_accent_color' => '',
            'heading' => self::tr('Check available dates', 'Sprawdź dostępne terminy'),
            'description' => '',
            'months_to_show' => 12,
            'start_month_offset' => 0,
            'cta_label' => '',
            'cta_url' => '',
            'status_map' => '{}',
            'day_mode_default' => 'slots',
            'day_mode_map' => '{}',
            'time_slots_overrides' => '{}',
            'time_slots_reservations' => '{}',
            'booking_notification_email' => '',
            'booking_from_email' => '',
            'booking_from_name' => '',
            'booking_hold_minutes' => 2880,
            'booking_lead_time_hours' => 24,
            'booking_time_buffer_minutes' => 30,
            'booking_history_retention_days' => 90,
            'booking_default_time_slots' => "10:00\n12:00\n14:00\n16:00",
            'booking_hold_notice_text' => self::tr('Your booking is tentative and will be held for {hours}h ({minutes} min). After that, the date becomes available again if not confirmed.', 'Rezerwacja terminu jest wstępna i trwa {hours}h ({minutes} min). Po tym czasie termin wraca do puli wolnych, jeśli nie zostanie potwierdzony.'),
            'booking_hold_note_template' => self::tr('Tentative hold for {hours}h ({minutes} min), until {expires}.', 'Wstępna rezerwacja na {hours}h ({minutes} min), do {expires}.'),
            'booking_success_message' => self::tr('Thank you. Your request has been saved. The date is blocked for {hours}h.', 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na {hours}h.'),
            'booking_error_message' => self::tr('Failed to submit the request. Please verify your data and try again.', 'Nie udało się wysłać zgłoszenia. Sprawdź dane i spróbuj ponownie.'),
            'booking_send_initial_email' => 1,
            'booking_send_expired_email' => 1,
            'booking_send_approved_email' => 1,
            'booking_send_rejected_email' => 1,
            'booking_client_initial_email_subject' => self::tr('Initial booking confirmation', 'Potwierdzenie wstępnej rezerwacji terminu'),
            'booking_client_initial_email_body' => "Dziękuję za zapytanie.\n\nTwój termin został wstępnie zablokowany na {hours}h ({minutes} min).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nHold do: {expires}\n\nSkontaktuję się z Tobą, aby potwierdzić szczegóły.\n\n{site_name}",
            'booking_client_expired_email_subject' => self::tr('Tentative booking expired', 'Wstępna rezerwacja wygasła'),
            'booking_client_expired_email_body' => self::tr("Hi {full_name},\n\nYour tentative booking has expired (no confirmation).\nDate: {date}\nTime: {time}\nService / Package: {option}\nHold duration: {hours}h ({minutes} min)\nExpired at: {expires}\n\nIf you are still interested, please submit a new request.\n\n{site_name}", "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}"),
            'booking_client_approved_email_subject' => self::tr('Booking approved', 'Rezerwacja terminu została potwierdzona'),
            'booking_client_approved_email_body' => self::tr("Hi {full_name},\n\nYour booking has been approved.\nDate: {date}\nTime: {time}\nService / Package: {option}\nStatus: {status}\n\nIf you have questions, reply to this message.\n\n{site_name}", "Cześć {full_name},\n\nTwoja rezerwacja została potwierdzona.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nW razie pytań odpowiedz na tę wiadomość.\n\n{site_name}"),
            'booking_client_rejected_email_subject' => self::tr('Booking not approved', 'Rezerwacja terminu nie została potwierdzona'),
            'booking_client_rejected_email_body' => self::tr("Hi {full_name},\n\nUnfortunately, we could not approve this booking.\nDate: {date}\nTime: {time}\nService / Package: {option}\nStatus: {status}\n\nYou can choose another available date and send a new request.\n\n{site_name}", "Cześć {full_name},\n\nNiestety nie mogliśmy potwierdzić rezerwacji tego terminu.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nMożesz wybrać inny dostępny termin i wysłać nowe zapytanie.\n\n{site_name}"),
            'booking_form_heading' => self::tr('Book a date', 'Zarezerwuj termin'),
            'booking_form_submit_label' => self::tr('Send booking request', 'Wyślij rezerwację'),
            'booking_consent_label' => self::tr('I have reviewed the work style and agree to be contacted back.', 'Zapoznałam/em się z moim stylem pracy i akceptuję kontakt zwrotny.'),
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
            'legend_toggle_hidden' => '_abc_legend_toggle_hidden',
            'advanced_styles_enabled' => '_abc_advanced_styles_enabled',
            'custom_colors_enabled' => '_abc_custom_colors_enabled',
            'layout_mode' => '_abc_layout_mode',
            'style_preset' => '_abc_style_preset',
            'density_mode' => '_abc_density_mode',
            'font_size_mode' => '_abc_font_size_mode',
            'button_shape' => '_abc_button_shape',
            'button_border_mode' => '_abc_button_border_mode',
            'button_hover_mode' => '_abc_button_hover_mode',
            'day_cell_style' => '_abc_day_cell_style',
            'sticky_booking_panel' => '_abc_sticky_booking_panel',
            'animation_level' => '_abc_animation_level',
            'minimal_mode' => '_abc_minimal_mode',
            'custom_bg_color' => '_abc_custom_bg_color',
            'custom_text_color' => '_abc_custom_text_color',
            'custom_accent_color' => '_abc_custom_accent_color',
            'heading' => '_abc_heading',
            'description' => '_abc_description',
            'months_to_show' => '_abc_months_to_show',
            'start_month_offset' => '_abc_start_month_offset',
            'cta_label' => '_abc_cta_label',
            'cta_url' => '_abc_cta_url',
            'status_map' => '_abc_status_map',
            'day_mode_default' => '_abc_day_mode_default',
            'day_mode_map' => '_abc_day_mode_map',
            'booking_notification_email' => '_abc_booking_notification_email',
            'booking_from_email' => '_abc_booking_from_email',
            'booking_from_name' => '_abc_booking_from_name',
            'booking_hold_minutes' => '_abc_booking_hold_minutes',
            'booking_lead_time_hours' => '_abc_booking_lead_time_hours',
            'booking_time_buffer_minutes' => '_abc_booking_time_buffer_minutes',
            'booking_history_retention_days' => '_abc_booking_history_retention_days',
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
        $defaults['booking_lead_time_hours'] = max(0, min(8760, (int) ($defaults['booking_lead_time_hours'] ?? 24)));
        $defaults['booking_time_buffer_minutes'] = max(0, min(720, (int) ($defaults['booking_time_buffer_minutes'] ?? 30)));
        $defaults['booking_history_retention_days'] = max(0, min(3650, (int) ($defaults['booking_history_retention_days'] ?? 90)));

        $defaults['booking_send_initial_email'] = self::to_bool($defaults['booking_send_initial_email']);
        $defaults['booking_send_expired_email'] = self::to_bool($defaults['booking_send_expired_email']);
        $defaults['booking_send_approved_email'] = self::to_bool($defaults['booking_send_approved_email']);
        $defaults['booking_send_rejected_email'] = self::to_bool($defaults['booking_send_rejected_email']);
        $defaults['theme_preset'] = self::sanitize_choice((string) $defaults['theme_preset'], self::theme_presets(), 'dark');
        $defaults['background_style'] = self::sanitize_choice((string) $defaults['background_style'], self::background_styles(), 'gradient');
        $defaults['font_preset'] = self::sanitize_choice((string) $defaults['font_preset'], self::font_presets(), 'modern');
        $defaults['legend_toggle_hidden'] = self::to_bool($defaults['legend_toggle_hidden'] ?? 0);
        $defaults['advanced_styles_enabled'] = self::to_bool($defaults['advanced_styles_enabled'] ?? 0);
        $defaults['custom_colors_enabled'] = self::to_bool($defaults['custom_colors_enabled'] ?? 0);
        $defaults['layout_mode'] = self::sanitize_choice((string) ($defaults['layout_mode'] ?? 'split'), self::layout_modes(), 'split');
        $defaults['style_preset'] = self::sanitize_choice((string) ($defaults['style_preset'] ?? 'classic'), self::style_presets(), 'classic');
        $defaults['density_mode'] = self::sanitize_choice((string) ($defaults['density_mode'] ?? 'comfortable'), self::density_modes(), 'comfortable');
        $defaults['font_size_mode'] = self::sanitize_choice((string) ($defaults['font_size_mode'] ?? 'm'), self::font_size_modes(), 'm');
        $defaults['button_shape'] = self::sanitize_choice((string) ($defaults['button_shape'] ?? 'rounded'), self::button_shapes(), 'rounded');
        $defaults['button_border_mode'] = self::sanitize_choice((string) ($defaults['button_border_mode'] ?? 'normal'), self::button_border_modes(), 'normal');
        $defaults['button_hover_mode'] = self::sanitize_choice((string) ($defaults['button_hover_mode'] ?? 'soft'), self::button_hover_modes(), 'soft');
        $defaults['day_cell_style'] = self::sanitize_choice((string) ($defaults['day_cell_style'] ?? 'soft'), self::day_cell_styles(), 'soft');
        $defaults['animation_level'] = self::sanitize_choice((string) ($defaults['animation_level'] ?? 'subtle'), self::animation_levels(), 'subtle');
        $defaults['sticky_booking_panel'] = self::to_bool($defaults['sticky_booking_panel'] ?? 1);
        $defaults['minimal_mode'] = self::to_bool($defaults['minimal_mode'] ?? 0);
        $defaults['custom_bg_color'] = sanitize_hex_color((string) ($defaults['custom_bg_color'] ?? '')) ?: '';
        $defaults['custom_text_color'] = sanitize_hex_color((string) ($defaults['custom_text_color'] ?? '')) ?: '';
        $defaults['custom_accent_color'] = sanitize_hex_color((string) ($defaults['custom_accent_color'] ?? '')) ?: '';

        if (! is_string($defaults['status_map']) || trim($defaults['status_map']) === '') {
            $defaults['status_map'] = '{}';
        }
        $defaults['day_mode_default'] = self::sanitize_choice((string) ($defaults['day_mode_default'] ?? 'slots'), [
            'slots' => 'slots',
            'all_day' => 'all_day',
            'hybrid' => 'hybrid',
        ], 'slots');
        if (! is_string($defaults['day_mode_map']) || trim($defaults['day_mode_map']) === '') {
            $defaults['day_mode_map'] = '{}';
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
        $tr = static function (string $en, string $pl): string {
            return self::tr($en, $pl);
        };

        $text = static function ($v) {
            return esc_attr((string) $v);
        };
        $textarea = static function ($v) {
            return esc_textarea((string) $v);
        };
        $selected_theme = self::sanitize_choice((string) $s['theme_preset'], self::theme_presets(), 'dark');
        $selected_style_preset = self::sanitize_choice((string) ($s['style_preset'] ?? 'classic'), self::style_presets(), 'classic');
        $selected_palette = self::theme_palette($selected_theme, $selected_style_preset);
        ?>
        <table class="form-table abc-settings-table" role="presentation">
            <tr class="abc-settings-section"><th colspan="2"><?php echo esc_html($tr('General', 'Ogólne')); ?></th></tr>
            <tr><th><label for="abc_section_title"><?php echo esc_html($tr('Section title', 'Tytuł sekcji')); ?></label></th><td><input class="regular-text" id="abc_section_title" name="abc_settings[section_title]" value="<?php echo $text($s['section_title']); ?>"></td></tr>
            <tr><th><label for="abc_section_subtitle"><?php echo esc_html($tr('Section subtitle', 'Podtytuł sekcji')); ?></label></th><td><input class="regular-text" id="abc_section_subtitle" name="abc_settings[section_subtitle]" value="<?php echo $text($s['section_subtitle']); ?>"></td></tr>
            <tr>
                <th><label for="abc_theme_preset"><?php echo esc_html($tr('Theme preset', 'Motyw')); ?></label></th>
                <td>
                    <select id="abc_theme_preset" name="abc_settings[theme_preset]">
                        <?php foreach (self::theme_presets() as $value => $label) : ?>
                            <?php $palette = self::theme_palette((string) $value, $selected_style_preset); ?>
                            <option
                                value="<?php echo esc_attr($value); ?>"
                                data-abc-bg-color="<?php echo esc_attr($palette['bg']); ?>"
                                data-abc-text-color="<?php echo esc_attr($palette['text']); ?>"
                                data-abc-accent-color="<?php echo esc_attr($palette['accent']); ?>"
                                <?php selected($s['theme_preset'], $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="abc_background_style"><?php echo esc_html($tr('Background style', 'Styl tła')); ?></label></th>
                <td>
                    <select id="abc_background_style" name="abc_settings[background_style]">
                        <?php foreach (self::background_styles() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['background_style'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="abc_font_preset"><?php echo esc_html($tr('Font preset', 'Styl fontu')); ?></label></th>
                <td>
                    <select id="abc_font_preset" name="abc_settings[font_preset]">
                        <?php foreach (self::font_presets() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['font_preset'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><?php echo esc_html($tr('Legend toggle button (frontend)', 'Przycisk legendy (frontend)')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" id="abc_legend_toggle_hidden" name="abc_settings[legend_toggle_hidden]" value="1" <?php checked($s['legend_toggle_hidden']); ?>>
                        <?php echo esc_html($tr('Hide button "Show/Hide legend"', 'Ukryj przycisk „Pokaż/Ukryj legendę”')); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><?php echo esc_html($tr('Manual style mode', 'Tryb ręcznej zmiany stylu')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" id="abc_advanced_styles_enabled" name="abc_settings[advanced_styles_enabled]" value="1" data-abc-style-advanced-toggle <?php checked($s['advanced_styles_enabled']); ?>>
                        <?php echo esc_html($tr('Change styles manually', 'Zmień style ręcznie')); ?>
                    </label>
                    <p class="description"><?php echo esc_html($tr('When disabled, the default theme preset styles remain primary.', 'Gdy wyłączone, najważniejsze pozostają domyślne style presetu motywu.')); ?></p>
                </td>
            </tr>
            <tr class="abc-settings-section abc-style-advanced-row"><th colspan="2"><?php echo esc_html($tr('Appearance', 'Wygląd')); ?></th></tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_layout_mode"><?php echo esc_html($tr('Layout mode', 'Układ')); ?></label></th>
                <td>
                    <select id="abc_layout_mode" name="abc_settings[layout_mode]" data-abc-style-control>
                        <?php foreach (self::layout_modes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['layout_mode'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_style_preset"><?php echo esc_html($tr('Style preset', 'Preset stylu')); ?></label></th>
                <td>
                    <select id="abc_style_preset" name="abc_settings[style_preset]" data-abc-style-control>
                        <?php foreach (self::style_presets() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['style_preset'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_density_mode"><?php echo esc_html($tr('Density', 'Gęstość')); ?></label></th>
                <td>
                    <select id="abc_density_mode" name="abc_settings[density_mode]" data-abc-style-control>
                        <?php foreach (self::density_modes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['density_mode'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_font_size_mode"><?php echo esc_html($tr('Typography size', 'Rozmiar typografii')); ?></label></th>
                <td>
                    <select id="abc_font_size_mode" name="abc_settings[font_size_mode]" data-abc-style-control>
                        <?php foreach (self::font_size_modes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['font_size_mode'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_day_cell_style"><?php echo esc_html($tr('Day cell style', 'Styl komórek dni')); ?></label></th>
                <td>
                    <select id="abc_day_cell_style" name="abc_settings[day_cell_style]" data-abc-style-control>
                        <?php foreach (self::day_cell_styles() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['day_cell_style'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_button_shape"><?php echo esc_html($tr('Button shape', 'Kształt przycisków')); ?></label></th>
                <td>
                    <select id="abc_button_shape" name="abc_settings[button_shape]" data-abc-style-control>
                        <?php foreach (self::button_shapes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['button_shape'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_button_border_mode"><?php echo esc_html($tr('Button border', 'Obramowanie przycisków')); ?></label></th>
                <td>
                    <select id="abc_button_border_mode" name="abc_settings[button_border_mode]" data-abc-style-control>
                        <?php foreach (self::button_border_modes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['button_border_mode'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_button_hover_mode"><?php echo esc_html($tr('Button hover intensity', 'Siła efektu hover')); ?></label></th>
                <td>
                    <select id="abc_button_hover_mode" name="abc_settings[button_hover_mode]" data-abc-style-control>
                        <?php foreach (self::button_hover_modes() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['button_hover_mode'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row">
                <th><label for="abc_animation_level"><?php echo esc_html($tr('Animations', 'Animacje')); ?></label></th>
                <td>
                    <select id="abc_animation_level" name="abc_settings[animation_level]" data-abc-style-control>
                        <?php foreach (self::animation_levels() as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($s['animation_level'], $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr class="abc-style-advanced-row"><th><?php echo esc_html($tr('Minimal mode', 'Tryb minimalistyczny')); ?></th><td><label><input type="checkbox" id="abc_minimal_mode" name="abc_settings[minimal_mode]" value="1" data-abc-style-control <?php checked($s['minimal_mode']); ?>> <?php echo esc_html($tr('Reduce visual noise and decorative elements', 'Ogranicz dekoracje i rozpraszające elementy')); ?></label></td></tr>
            <tr class="abc-style-advanced-row"><th><?php echo esc_html($tr('Sticky booking panel (desktop)', 'Przyklejony panel rezerwacji (desktop)')); ?></th><td><label><input type="checkbox" id="abc_sticky_booking_panel" name="abc_settings[sticky_booking_panel]" value="1" data-abc-style-control <?php checked($s['sticky_booking_panel']); ?>> <?php echo esc_html($tr('Keep booking panel visible while scrolling', 'Utrzymuj panel rezerwacji podczas przewijania')); ?></label></td></tr>
            <tr class="abc-style-advanced-row">
                <th><?php echo esc_html($tr('Manual color override', 'Ręczna zmiana kolorów')); ?></th>
                <td>
                    <label>
                        <input type="checkbox" id="abc_custom_colors_enabled" name="abc_settings[custom_colors_enabled]" value="1" data-abc-style-control <?php checked($s['custom_colors_enabled']); ?>>
                        <?php echo esc_html($tr('Change colors manually', 'Zmień kolory ręcznie')); ?>
                    </label>
                    <button type="button" class="button button-secondary" id="abc_reset_theme_styles" data-abc-reset-theme-styles>
                        <?php echo esc_html($tr('Reset theme styles', 'Resetuj style motywu')); ?>
                    </button>
                    <p class="description"><?php echo esc_html($tr('Resets custom colors to selected theme palette and disables manual color override.', 'Przywraca kolory wybranego motywu i wyłącza ręczną zmianę kolorów.')); ?></p>
                </td>
            </tr>
            <tr class="abc-style-advanced-row abc-style-color-row"><th><label for="abc_custom_bg_color"><?php echo esc_html($tr('Custom background color', 'Własny kolor tła')); ?></label></th><td><input type="color" id="abc_custom_bg_color" name="abc_settings[custom_bg_color]" value="<?php echo $text($s['custom_bg_color'] ?: $selected_palette['bg']); ?>" data-abc-style-control></td></tr>
            <tr class="abc-style-advanced-row abc-style-color-row"><th><label for="abc_custom_text_color"><?php echo esc_html($tr('Custom text color', 'Własny kolor tekstu')); ?></label></th><td><input type="color" id="abc_custom_text_color" name="abc_settings[custom_text_color]" value="<?php echo $text($s['custom_text_color'] ?: $selected_palette['text']); ?>" data-abc-style-control></td></tr>
            <tr class="abc-style-advanced-row abc-style-color-row"><th><label for="abc_custom_accent_color"><?php echo esc_html($tr('Custom accent color', 'Własny kolor akcentu')); ?></label></th><td><input type="color" id="abc_custom_accent_color" name="abc_settings[custom_accent_color]" value="<?php echo $text($s['custom_accent_color'] ?: $selected_palette['accent']); ?>" data-abc-style-control></td></tr>
            <tr class="abc-settings-full abc-style-advanced-row">
                <th><?php echo esc_html($tr('Live preview', 'Podgląd na żywo')); ?></th>
                <td>
                    <div class="abc-style-preview-wrap">
                        <div class="abc-style-preview abc-module <?php echo esc_attr(
                            'abc-theme-' . self::sanitize_choice((string) $s['theme_preset'], self::theme_presets(), 'dark')
                            . ' abc-bg-' . self::sanitize_choice((string) $s['background_style'], self::background_styles(), 'gradient')
                            . ' abc-font-' . self::sanitize_choice((string) $s['font_preset'], self::font_presets(), 'modern')
                            . (
                                self::to_bool($s['advanced_styles_enabled'] ?? 0)
                                ? (
                                    ' abc-layout-' . self::sanitize_choice((string) ($s['layout_mode'] ?? 'split'), self::layout_modes(), 'split')
                                    . ' abc-style-' . self::sanitize_choice((string) ($s['style_preset'] ?? 'classic'), self::style_presets(), 'classic')
                                    . ' abc-density-' . self::sanitize_choice((string) ($s['density_mode'] ?? 'comfortable'), self::density_modes(), 'comfortable')
                                    . ' abc-size-' . self::sanitize_choice((string) ($s['font_size_mode'] ?? 'm'), self::font_size_modes(), 'm')
                                    . ' abc-btnshape-' . self::sanitize_choice((string) ($s['button_shape'] ?? 'rounded'), self::button_shapes(), 'rounded')
                                    . ' abc-button-border-' . self::sanitize_choice((string) ($s['button_border_mode'] ?? 'normal'), self::button_border_modes(), 'normal')
                                    . ' abc-button-hover-' . self::sanitize_choice((string) ($s['button_hover_mode'] ?? 'soft'), self::button_hover_modes(), 'soft')
                                    . ' abc-daystyle-' . self::sanitize_choice((string) ($s['day_cell_style'] ?? 'soft'), self::day_cell_styles(), 'soft')
                                    . ' abc-motion-' . self::sanitize_choice((string) ($s['animation_level'] ?? 'subtle'), self::animation_levels(), 'subtle')
                                    . ($s['minimal_mode'] ? ' abc-minimal' : '')
                                    . ($s['sticky_booking_panel'] ? ' abc-sticky-panel' : '')
                                )
                                : ''
                            )
                        ); ?>"
                            data-abc-live-preview
                            style="<?php echo esc_attr(self::build_style_vars($s)); ?>">
                            <div class="abc-wrap">
                                <aside class="abc-side">
                                    <p class="abc-section-title"><?php echo esc_html($tr('Calendar', 'Kalendarz')); ?></p>
                                    <h3 class="abc-heading"><?php echo esc_html($tr('Live style preview', 'Podgląd stylu')); ?></h3>
                                    <?php if (! self::to_bool($s['legend_toggle_hidden'] ?? 0)) : ?>
                                        <button type="button" class="abc-legend-toggle"><?php echo esc_html($tr('Toggle legend', 'Legenda')); ?></button>
                                    <?php endif; ?>
                                </aside>
                                <div class="abc-shell">
                                    <div class="abc-weekdays"><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div></div>
                                    <div class="abc-days">
                                        <button type="button" class="abc-day is-available">12</button>
                                        <button type="button" class="abc-day is-unavailable">13</button>
                                        <button type="button" class="abc-day is-unavailable is-past">14</button>
                                    </div>
                                    <div class="abc-booking"><button type="button" class="abc-open"><?php echo esc_html($tr('Book now', 'Rezerwuj')); ?></button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="description"><?php echo esc_html($tr('Colors are optional. Leave defaults by selecting the same palette as theme.', 'Kolory są opcjonalne. Domyślnie używany jest preset motywu.')); ?></p>
                </td>
            </tr>
            <tr><th><label for="abc_heading"><?php echo esc_html($tr('Heading', 'Nagłówek')); ?></label></th><td><input class="regular-text" id="abc_heading" name="abc_settings[heading]" value="<?php echo $text($s['heading']); ?>"></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_description"><?php echo esc_html($tr('Description', 'Opis')); ?></label></th><td><textarea class="large-text" rows="3" id="abc_description" name="abc_settings[description]"><?php echo $textarea($s['description']); ?></textarea></td></tr>
            <tr><th><label for="abc_months_to_show"><?php echo esc_html($tr('Months to show', 'Liczba miesięcy')); ?></label></th><td><input type="number" min="3" max="24" id="abc_months_to_show" name="abc_settings[months_to_show]" value="<?php echo (int) $s['months_to_show']; ?>"></td></tr>
            <tr><th><label for="abc_start_month_offset"><?php echo esc_html($tr('Start month offset', 'Przesunięcie miesiąca startowego')); ?></label></th><td><input type="number" min="-12" max="12" id="abc_start_month_offset" name="abc_settings[start_month_offset]" value="<?php echo (int) $s['start_month_offset']; ?>"></td></tr>
            <tr><th><label for="abc_cta_label"><?php echo esc_html($tr('CTA label', 'Etykieta CTA')); ?></label></th><td><input class="regular-text" id="abc_cta_label" name="abc_settings[cta_label]" value="<?php echo $text($s['cta_label']); ?>"></td></tr>
            <tr><th><label for="abc_cta_url"><?php echo esc_html($tr('CTA URL', 'Link CTA')); ?></label></th><td><input class="regular-text" id="abc_cta_url" name="abc_settings[cta_url]" value="<?php echo $text($s['cta_url']); ?>"></td></tr>
            <tr>
                <th><label for="abc_day_mode_default"><?php echo esc_html($tr('Default booking mode', 'Domyślny tryb rezerwacji')); ?></label></th>
                <td>
                    <select id="abc_day_mode_default" name="abc_settings[day_mode_default]">
                        <option value="slots" <?php selected((string) $s['day_mode_default'], 'slots'); ?>><?php echo esc_html($tr('Only time slots', 'Tylko godziny')); ?></option>
                        <option value="all_day" <?php selected((string) $s['day_mode_default'], 'all_day'); ?>><?php echo esc_html($tr('Only full day', 'Tylko cały dzień')); ?></option>
                        <option value="hybrid" <?php selected((string) $s['day_mode_default'], 'hybrid'); ?>><?php echo esc_html($tr('Hybrid (per-day in manager)', 'Hybrydowy (per dzień w managerze)')); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="abc-settings-section"><th colspan="2"><?php echo esc_html($tr('Booking Rules', 'Zasady rezerwacji')); ?></th></tr>
            <tr><th><label for="abc_booking_notification_email"><?php echo esc_html($tr('Notification email', 'E-mail powiadomień')); ?></label></th><td><input type="email" class="regular-text" id="abc_booking_notification_email" name="abc_settings[booking_notification_email]" value="<?php echo $text($s['booking_notification_email']); ?>"></td></tr>
            <tr><th><label for="abc_booking_from_email"><?php echo esc_html($tr('From email', 'E-mail nadawcy')); ?></label></th><td><input type="email" class="regular-text" id="abc_booking_from_email" name="abc_settings[booking_from_email]" value="<?php echo $text($s['booking_from_email']); ?>"></td></tr>
            <tr><th><label for="abc_booking_from_name"><?php echo esc_html($tr('From name', 'Nazwa nadawcy')); ?></label></th><td><input class="regular-text" id="abc_booking_from_name" name="abc_settings[booking_from_name]" value="<?php echo $text($s['booking_from_name']); ?>"></td></tr>
            <tr><th><label for="abc_booking_hold_minutes"><?php echo esc_html($tr('Hold duration (minutes)', 'Czas blokady (minuty)')); ?></label></th><td><input type="number" min="1" max="10080" id="abc_booking_hold_minutes" name="abc_settings[booking_hold_minutes]" value="<?php echo (int) $s['booking_hold_minutes']; ?>"><p class="description"><?php echo esc_html($tr('How long a pending request keeps the slot blocked before auto-expiration.', 'Jak długo oczekujące zgłoszenie blokuje termin przed automatycznym wygaśnięciem.')); ?></p></td></tr>
            <tr><th><label for="abc_booking_lead_time_hours"><?php echo esc_html($tr('Lead time (hours)', 'Minimalne wyprzedzenie (godziny)')); ?></label></th><td><input type="number" min="0" max="8760" id="abc_booking_lead_time_hours" name="abc_settings[booking_lead_time_hours]" value="<?php echo (int) $s['booking_lead_time_hours']; ?>"><p class="description"><?php echo esc_html($tr('Minimum advance notice required before a booking can be submitted.', 'Minimalne wyprzedzenie wymagane przed wysłaniem rezerwacji.')); ?></p></td></tr>
            <tr><th><label for="abc_booking_time_buffer_minutes"><?php echo esc_html($tr('Time buffer (minutes)', 'Bufor czasu (minuty)')); ?></label></th><td><input type="number" min="0" max="720" id="abc_booking_time_buffer_minutes" name="abc_settings[booking_time_buffer_minutes]" value="<?php echo (int) $s['booking_time_buffer_minutes']; ?>"><p class="description"><?php echo esc_html($tr('Extra safety margin added on top of lead time to avoid last-minute race conditions.', 'Dodatkowy margines bezpieczeństwa dodawany do wyprzedzenia, aby uniknąć rezerwacji „na styk”.')); ?></p></td></tr>
            <tr><th><label for="abc_booking_history_retention_days"><?php echo esc_html($tr('History retention (days)', 'Retencja historii (dni)')); ?></label></th><td><input type="number" min="0" max="3650" id="abc_booking_history_retention_days" name="abc_settings[booking_history_retention_days]" value="<?php echo (int) $s['booking_history_retention_days']; ?>"><p class="description"><?php echo esc_html($tr('Older status/history entries are removed automatically. Set 0 to disable.', 'Starsze wpisy statusów/historii są usuwane automatycznie. Ustaw 0, aby wyłączyć.')); ?></p></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_default_time_slots"><?php echo esc_html($tr('Default available hours', 'Domyślne dostępne godziny')); ?></label></th><td><textarea class="large-text" rows="5" id="abc_booking_default_time_slots" name="abc_settings[booking_default_time_slots]"><?php echo $textarea($s['booking_default_time_slots']); ?></textarea><p class="description"><?php echo esc_html($tr('One time per line in HH:MM format, e.g. 10:00', 'Jedna godzina w linii w formacie HH:MM, np. 10:00')); ?></p></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_hold_notice_text"><?php echo esc_html($tr('Hold notice text', 'Tekst informacji o blokadzie')); ?></label></th><td><textarea class="large-text" rows="2" id="abc_booking_hold_notice_text" name="abc_settings[booking_hold_notice_text]"><?php echo $textarea($s['booking_hold_notice_text']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {hours}, {minutes}', 'Zmienne: {hours}, {minutes}')); ?></p></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_hold_note_template"><?php echo esc_html($tr('Calendar hold note template', 'Szablon notatki blokady w kalendarzu')); ?></label></th><td><textarea class="large-text" rows="2" id="abc_booking_hold_note_template" name="abc_settings[booking_hold_note_template]"><?php echo $textarea($s['booking_hold_note_template']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {hours}, {minutes}, {expires}', 'Zmienne: {hours}, {minutes}, {expires}')); ?></p></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_success_message"><?php echo esc_html($tr('Success message', 'Komunikat sukcesu')); ?></label></th><td><textarea class="large-text" rows="2" id="abc_booking_success_message" name="abc_settings[booking_success_message]"><?php echo $textarea($s['booking_success_message']); ?></textarea></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_error_message"><?php echo esc_html($tr('Error message', 'Komunikat błędu')); ?></label></th><td><textarea class="large-text" rows="2" id="abc_booking_error_message" name="abc_settings[booking_error_message]"><?php echo $textarea($s['booking_error_message']); ?></textarea></td></tr>

            <tr class="abc-settings-section"><th colspan="2"><?php echo esc_html($tr('Client Emails', 'E-maile klienta')); ?></th></tr>
            <tr><th><?php echo esc_html($tr('Send client email after booking', 'Wyślij e-mail klienta po rezerwacji')); ?></th><td><label><input type="checkbox" name="abc_settings[booking_send_initial_email]" value="1" <?php checked($s['booking_send_initial_email']); ?>> <?php echo esc_html($tr('Enable', 'Włącz')); ?></label></td></tr>
            <tr><th><?php echo esc_html($tr('Send client email when hold expires', 'Wyślij e-mail klienta po wygaśnięciu blokady')); ?></th><td><label><input type="checkbox" name="abc_settings[booking_send_expired_email]" value="1" <?php checked($s['booking_send_expired_email']); ?>> <?php echo esc_html($tr('Enable', 'Włącz')); ?></label></td></tr>
            <tr><th><?php echo esc_html($tr('Send client email when booking is approved', 'Wyślij e-mail klienta po zatwierdzeniu')); ?></th><td><label><input type="checkbox" name="abc_settings[booking_send_approved_email]" value="1" <?php checked($s['booking_send_approved_email']); ?>> <?php echo esc_html($tr('Enable', 'Włącz')); ?></label></td></tr>
            <tr><th><?php echo esc_html($tr('Send client email when booking is rejected', 'Wyślij e-mail klienta po odrzuceniu')); ?></th><td><label><input type="checkbox" name="abc_settings[booking_send_rejected_email]" value="1" <?php checked($s['booking_send_rejected_email']); ?>> <?php echo esc_html($tr('Enable', 'Włącz')); ?></label></td></tr>

            <tr><th><label for="abc_booking_client_initial_email_subject"><?php echo esc_html($tr('Client email subject (after booking)', 'Temat e-maila klienta (po rezerwacji)')); ?></label></th><td><input class="regular-text" id="abc_booking_client_initial_email_subject" name="abc_settings[booking_client_initial_email_subject]" value="<?php echo $text($s['booking_client_initial_email_subject']); ?>"></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_client_initial_email_body"><?php echo esc_html($tr('Client email body (after booking)', 'Treść e-maila klienta (po rezerwacji)')); ?></label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_initial_email_body" name="abc_settings[booking_client_initial_email_body]"><?php echo $textarea($s['booking_client_initial_email_body']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}', 'Zmienne: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}')); ?></p></td></tr>
            <tr><th><label for="abc_booking_client_expired_email_subject"><?php echo esc_html($tr('Client email subject (hold expired)', 'Temat e-maila klienta (po wygaśnięciu blokady)')); ?></label></th><td><input class="regular-text" id="abc_booking_client_expired_email_subject" name="abc_settings[booking_client_expired_email_subject]" value="<?php echo $text($s['booking_client_expired_email_subject']); ?>"></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_client_expired_email_body"><?php echo esc_html($tr('Client email body (hold expired)', 'Treść e-maila klienta (po wygaśnięciu blokady)')); ?></label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_expired_email_body" name="abc_settings[booking_client_expired_email_body]"><?php echo $textarea($s['booking_client_expired_email_body']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}', 'Zmienne: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}')); ?></p></td></tr>
            <tr><th><label for="abc_booking_client_approved_email_subject"><?php echo esc_html($tr('Client email subject (approved)', 'Temat e-maila klienta (zatwierdzona)')); ?></label></th><td><input class="regular-text" id="abc_booking_client_approved_email_subject" name="abc_settings[booking_client_approved_email_subject]" value="<?php echo $text($s['booking_client_approved_email_subject']); ?>"></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_client_approved_email_body"><?php echo esc_html($tr('Client email body (approved)', 'Treść e-maila klienta (zatwierdzona)')); ?></label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_approved_email_body" name="abc_settings[booking_client_approved_email_body]"><?php echo $textarea($s['booking_client_approved_email_body']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}', 'Zmienne: {full_name}, {date}, {time}, {option}, {status}, {site_name}')); ?></p></td></tr>
            <tr><th><label for="abc_booking_client_rejected_email_subject"><?php echo esc_html($tr('Client email subject (rejected)', 'Temat e-maila klienta (odrzucona)')); ?></label></th><td><input class="regular-text" id="abc_booking_client_rejected_email_subject" name="abc_settings[booking_client_rejected_email_subject]" value="<?php echo $text($s['booking_client_rejected_email_subject']); ?>"></td></tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_client_rejected_email_body"><?php echo esc_html($tr('Client email body (rejected)', 'Treść e-maila klienta (odrzucona)')); ?></label></th><td><textarea class="large-text" rows="6" id="abc_booking_client_rejected_email_body" name="abc_settings[booking_client_rejected_email_body]"><?php echo $textarea($s['booking_client_rejected_email_body']); ?></textarea><p class="description"><?php echo esc_html($tr('Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}', 'Zmienne: {full_name}, {date}, {time}, {option}, {status}, {site_name}')); ?></p></td></tr>

            <tr class="abc-settings-section"><th colspan="2"><?php echo esc_html($tr('Form', 'Formularz')); ?></th></tr>
            <tr><th><label for="abc_booking_form_heading"><?php echo esc_html($tr('Form heading', 'Nagłówek formularza')); ?></label></th><td><input class="regular-text" id="abc_booking_form_heading" name="abc_settings[booking_form_heading]" value="<?php echo $text($s['booking_form_heading']); ?>"></td></tr>
            <tr><th><label for="abc_booking_form_submit_label"><?php echo esc_html($tr('Submit button label', 'Etykieta przycisku wyślij')); ?></label></th><td><input class="regular-text" id="abc_booking_form_submit_label" name="abc_settings[booking_form_submit_label]" value="<?php echo $text($s['booking_form_submit_label']); ?>"></td></tr>
            <tr class="abc-settings-full">
                <th><label for="abc_booking_consent_label"><?php echo esc_html($tr('Consent label', 'Etykieta zgody')); ?></label></th>
                <td>
                    <textarea class="large-text" rows="3" id="abc_booking_consent_label" name="abc_settings[booking_consent_label]"><?php echo $textarea($s['booking_consent_label']); ?></textarea>
                    <p class="description"><?php echo esc_html($tr('Allowed HTML: links (<a href target rel>).', 'Dozwolony HTML: linki (<a href target rel>).')); ?></p>
                </td>
            </tr>
            <tr class="abc-settings-full"><th><label for="abc_booking_options"><?php echo esc_html($tr('Service / Package options', 'Opcje usługi / pakietu')); ?></label></th><td><textarea class="large-text" rows="5" id="abc_booking_options" name="abc_settings[booking_options]"><?php echo $textarea($s['booking_options']); ?></textarea><p class="description"><?php echo esc_html($tr('One option per line.', 'Jedna opcja w linii.')); ?></p></td></tr>
        </table>
        <?php
    }

    public static function render_availability_metabox(\WP_Post $post): void {
        $s = self::get_calendar_settings((int) $post->ID);
        $requests_url = admin_url('edit.php?post_type=' . self::REQUEST_CPT);
        ?>
        <p><?php echo esc_html(self::tr('Use manager below to set day statuses and optional notes.', 'Użyj menedżera poniżej, aby ustawić statusy dni i opcjonalne notatki.')); ?></p>
        <p class="description"><?php echo wp_kses_post(sprintf(
            self::tr(
                'Locked (reserved) dates are grayed out. Release them in <a href="%s">Booking Requests</a>.',
                'Zablokowane (zarezerwowane) daty są wyszarzone. Zwolnisz je w sekcji <a href="%s">Zapytania rezerwacji</a>.'
            ),
            esc_url($requests_url)
        )); ?></p>
        <textarea id="abc_status_map" name="abc_settings[status_map]" rows="4" class="large-text code" style="display:none;"><?php echo esc_textarea((string) $s['status_map']); ?></textarea>
        <textarea id="abc_day_mode_map" name="abc_settings[day_mode_map]" rows="4" class="large-text code" style="display:none;"><?php echo esc_textarea((string) ($s['day_mode_map'] ?? '{}')); ?></textarea>
        <textarea id="abc_time_slots_overrides" name="abc_settings[time_slots_overrides]" rows="4" class="large-text code" style="display:none;"><?php echo esc_textarea((string) ($s['time_slots_overrides'] ?? '{}')); ?></textarea>
        <div class="abc-admin-manager"
             data-abc-status-map="<?php echo esc_attr((string) $s['status_map']); ?>"
             data-abc-day-mode-default="<?php echo esc_attr((string) ($s['day_mode_default'] ?? 'slots')); ?>"
             data-abc-day-mode-map="<?php echo esc_attr((string) ($s['day_mode_map'] ?? '{}')); ?>"
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
        $advanced_styles_enabled = isset($incoming['advanced_styles_enabled']);
        $custom_colors_enabled = $advanced_styles_enabled && isset($incoming['custom_colors_enabled']);

        $save = [
            'section_title' => sanitize_text_field((string) ($incoming['section_title'] ?? $defaults['section_title'])),
            'section_subtitle' => sanitize_text_field((string) ($incoming['section_subtitle'] ?? $defaults['section_subtitle'])),
            'theme_preset' => self::sanitize_choice((string) ($incoming['theme_preset'] ?? $defaults['theme_preset']), self::theme_presets(), 'dark'),
            'background_style' => self::sanitize_choice((string) ($incoming['background_style'] ?? $defaults['background_style']), self::background_styles(), 'gradient'),
            'font_preset' => self::sanitize_choice((string) ($incoming['font_preset'] ?? $defaults['font_preset']), self::font_presets(), 'modern'),
            'legend_toggle_hidden' => isset($incoming['legend_toggle_hidden']) ? 1 : 0,
            'advanced_styles_enabled' => $advanced_styles_enabled ? 1 : 0,
            'custom_colors_enabled' => $custom_colors_enabled ? 1 : 0,
            'layout_mode' => self::sanitize_choice((string) ($incoming['layout_mode'] ?? $defaults['layout_mode']), self::layout_modes(), 'split'),
            'style_preset' => self::sanitize_choice((string) ($incoming['style_preset'] ?? $defaults['style_preset']), self::style_presets(), 'classic'),
            'density_mode' => self::sanitize_choice((string) ($incoming['density_mode'] ?? $defaults['density_mode']), self::density_modes(), 'comfortable'),
            'font_size_mode' => self::sanitize_choice((string) ($incoming['font_size_mode'] ?? $defaults['font_size_mode']), self::font_size_modes(), 'm'),
            'button_shape' => self::sanitize_choice((string) ($incoming['button_shape'] ?? $defaults['button_shape']), self::button_shapes(), 'rounded'),
            'button_border_mode' => self::sanitize_choice((string) ($incoming['button_border_mode'] ?? $defaults['button_border_mode']), self::button_border_modes(), 'normal'),
            'button_hover_mode' => self::sanitize_choice((string) ($incoming['button_hover_mode'] ?? $defaults['button_hover_mode']), self::button_hover_modes(), 'soft'),
            'day_cell_style' => self::sanitize_choice((string) ($incoming['day_cell_style'] ?? $defaults['day_cell_style']), self::day_cell_styles(), 'soft'),
            'animation_level' => self::sanitize_choice((string) ($incoming['animation_level'] ?? $defaults['animation_level']), self::animation_levels(), 'subtle'),
            'sticky_booking_panel' => isset($incoming['sticky_booking_panel']) ? 1 : 0,
            'minimal_mode' => isset($incoming['minimal_mode']) ? 1 : 0,
            'custom_bg_color' => $custom_colors_enabled ? (sanitize_hex_color((string) ($incoming['custom_bg_color'] ?? '')) ?: '') : '',
            'custom_text_color' => $custom_colors_enabled ? (sanitize_hex_color((string) ($incoming['custom_text_color'] ?? '')) ?: '') : '',
            'custom_accent_color' => $custom_colors_enabled ? (sanitize_hex_color((string) ($incoming['custom_accent_color'] ?? '')) ?: '') : '',
            'heading' => sanitize_text_field((string) ($incoming['heading'] ?? $defaults['heading'])),
            'description' => sanitize_textarea_field((string) ($incoming['description'] ?? $defaults['description'])),
            'months_to_show' => max(3, min(24, (int) ($incoming['months_to_show'] ?? $defaults['months_to_show']))),
            'start_month_offset' => max(-12, min(12, (int) ($incoming['start_month_offset'] ?? $defaults['start_month_offset']))),
            'cta_label' => sanitize_text_field((string) ($incoming['cta_label'] ?? $defaults['cta_label'])),
            'cta_url' => esc_url_raw((string) ($incoming['cta_url'] ?? $defaults['cta_url'])),
            'day_mode_default' => self::sanitize_choice((string) ($incoming['day_mode_default'] ?? $defaults['day_mode_default']), [
                'slots' => 'slots',
                'all_day' => 'all_day',
                'hybrid' => 'hybrid',
            ], 'slots'),
            'booking_notification_email' => sanitize_email((string) ($incoming['booking_notification_email'] ?? '')),
            'booking_from_email' => sanitize_email((string) ($incoming['booking_from_email'] ?? '')),
            'booking_from_name' => sanitize_text_field((string) ($incoming['booking_from_name'] ?? '')),
            'booking_hold_minutes' => max(1, min(10080, (int) ($incoming['booking_hold_minutes'] ?? $defaults['booking_hold_minutes']))),
            'booking_lead_time_hours' => max(0, min(8760, (int) ($incoming['booking_lead_time_hours'] ?? $defaults['booking_lead_time_hours']))),
            'booking_time_buffer_minutes' => max(0, min(720, (int) ($incoming['booking_time_buffer_minutes'] ?? $defaults['booking_time_buffer_minutes']))),
            'booking_history_retention_days' => max(0, min(3650, (int) ($incoming['booking_history_retention_days'] ?? $defaults['booking_history_retention_days']))),
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
            'booking_consent_label' => self::sanitize_consent_label((string) ($incoming['booking_consent_label'] ?? $defaults['booking_consent_label'])),
            'booking_options' => sanitize_textarea_field((string) ($incoming['booking_options'] ?? $defaults['booking_options'])),
        ];

        $time_overrides_raw = trim((string) wp_unslash($incoming['time_slots_overrides'] ?? '{}'));
        $time_overrides = self::normalize_time_slots_overrides($time_overrides_raw);
        $save['time_slots_overrides'] = wp_json_encode(
            $time_overrides,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $day_mode_map_raw = trim((string) wp_unslash($incoming['day_mode_map'] ?? '{}'));
        $day_mode_map = self::normalize_day_mode_map($day_mode_map_raw);
        $save['day_mode_map'] = wp_json_encode(
            $day_mode_map,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $time_reservations = self::normalize_time_slot_reservations((string) get_post_meta($post_id, '_abc_time_slots_reservations', true));

        $status_map_raw = trim((string) wp_unslash($incoming['status_map'] ?? '{}'));
        $status_map = self::normalize_status_map($status_map_raw);
        $reconciled = self::reconcile_calendar_state($save, $time_overrides, $time_reservations, $status_map);
        $time_reservations = $reconciled['time_reservations'];
        $status_map = $reconciled['status_map'];
        if (! empty($reconciled['changed'])) {
            self::queue_reconcile_notice($post_id);
        }
        $save['time_slots_reservations'] = wp_json_encode(
            $time_reservations,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $save['status_map'] = wp_json_encode($status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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

    private static function sanitize_consent_label(string $value): string {
        $allowed = [
            'a' => [
                'href' => true,
                'target' => true,
                'rel' => true,
            ],
            'br' => [],
        ];

        return wp_kses($value, $allowed);
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
                if (! is_string($slot) || (! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot) && $slot !== 'ALL_DAY') || ! is_array($entry)) {
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

    private static function normalize_day_mode_map(string $raw): array {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $date => $mode) {
            if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                continue;
            }
            $value = self::sanitize_choice((string) $mode, [
                'slots' => 'slots',
                'all_day' => 'all_day',
            ], '');
            if ($value !== '') {
                $out[$date] = $value;
            }
        }
        ksort($out);
        return $out;
    }

    private static function resolve_day_mode(string $date, array $settings, array $day_mode_map): string {
        if (isset($day_mode_map[$date]) && in_array((string) $day_mode_map[$date], ['slots', 'all_day'], true)) {
            return (string) $day_mode_map[$date];
        }
        $default = (string) ($settings['day_mode_default'] ?? 'slots');
        if ($default === 'all_day') {
            return 'all_day';
        }
        return 'slots';
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
        $day_mode_map = self::normalize_day_mode_map((string) ($settings['day_mode_map'] ?? '{}'));
        $resolved_day_mode = self::resolve_day_mode($date, $settings, $day_mode_map);
        $slots = $resolved_day_mode === 'all_day' ? ['ALL_DAY'] : self::get_date_slots($date, $settings, $overrides);
        if (empty($slots)) {
            return $status_map;
        }

        $current_status = is_array($status_map[$date] ?? null) ? sanitize_text_field((string) (($status_map[$date]['status'] ?? 'none'))): 'none';
        $day_reservations = isset($reservations[$date]) && is_array($reservations[$date]) ? $reservations[$date] : [];
        // Admin "booked" acts as a hard day lock and should not be downgraded by slot aggregation.
        if ($current_status === 'booked') {
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

    private static function save_time_reservations(int $calendar_id, array $time_reservations): void {
        update_post_meta($calendar_id, '_abc_time_slots_reservations', wp_json_encode($time_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function reconcile_calendar_state(
        array $settings,
        array $time_overrides,
        array $time_reservations,
        array $status_map
    ): array {
        $normalized_reservations = self::normalize_time_slot_reservations(
            wp_json_encode($time_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $normalized_status_map = self::normalize_status_map(
            wp_json_encode($status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        foreach (array_keys($normalized_reservations) as $reserved_date) {
            $normalized_status_map = self::apply_slot_aggregate_to_status_map(
                $reserved_date,
                $settings,
                $time_overrides,
                $normalized_reservations,
                $normalized_status_map
            );
        }

        $changed = (
            wp_json_encode($normalized_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            !== wp_json_encode($time_reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ) || (
            wp_json_encode($normalized_status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            !== wp_json_encode($status_map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return [
            'time_reservations' => $normalized_reservations,
            'status_map' => $normalized_status_map,
            'changed' => $changed,
        ];
    }

    private static function queue_reconcile_notice(int $calendar_id): void {
        if (! is_admin()) {
            return;
        }
        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            return;
        }
        $key = 'abc_reconcile_notice_' . $user_id;
        set_transient($key, [
            'calendar_id' => $calendar_id,
        ], 2 * MINUTE_IN_SECONDS);
    }

    private static function consume_reconcile_notice(): ?array {
        if (! is_admin()) {
            return null;
        }
        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            return null;
        }
        $key = 'abc_reconcile_notice_' . $user_id;
        $payload = get_transient($key);
        if (! is_array($payload)) {
            return null;
        }
        delete_transient($key);
        return $payload;
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

    private static function build_style_vars(array $settings): string {
        if (! self::to_bool($settings['advanced_styles_enabled'] ?? 0)) {
            return '';
        }
        if (! self::to_bool($settings['custom_colors_enabled'] ?? 0)) {
            return '';
        }

        $bg = sanitize_hex_color((string) ($settings['custom_bg_color'] ?? ''));
        $text = sanitize_hex_color((string) ($settings['custom_text_color'] ?? ''));
        $accent = sanitize_hex_color((string) ($settings['custom_accent_color'] ?? ''));

        $vars = [];
        if ($bg) {
            $vars[] = '--abc-custom-bg:' . $bg;
        }
        if ($text) {
            $vars[] = '--abc-custom-text:' . $text;
        }
        if ($accent) {
            $vars[] = '--abc-custom-accent:' . $accent;
        }
        return implode(';', $vars);
    }

    private static function layout_modes(): array {
        return [
            'split' => self::tr('Split (two columns)', 'Podział (dwie kolumny)'),
            'stacked' => self::tr('Stacked (single column)', 'Kolumnowy (jedna kolumna)'),
        ];
    }

    private static function style_presets(): array {
        return [
            'classic' => self::tr('Classic', 'Klasyczny'),
            'minimal' => self::tr('Minimal', 'Minimalny'),
            'bold' => self::tr('Bold', 'Wyrazisty'),
        ];
    }

    private static function density_modes(): array {
        return [
            'compact' => self::tr('Compact', 'Kompaktowa'),
            'comfortable' => self::tr('Comfortable', 'Wygodna'),
        ];
    }

    private static function font_size_modes(): array {
        return [
            's' => 'S',
            'm' => 'M',
            'l' => 'L',
        ];
    }

    private static function button_shapes(): array {
        return [
            'rounded' => self::tr('Rounded', 'Zaokrąglone'),
            'sharp' => self::tr('Sharp', 'Kanciaste'),
        ];
    }

    private static function button_border_modes(): array {
        return [
            'thin' => self::tr('Thin', 'Cienkie'),
            'normal' => self::tr('Normal', 'Normalne'),
            'strong' => self::tr('Strong', 'Mocne'),
        ];
    }

    private static function button_hover_modes(): array {
        return [
            'off' => self::tr('Off', 'Wyłączone'),
            'soft' => self::tr('Soft', 'Delikatne'),
            'strong' => self::tr('Strong', 'Mocne'),
        ];
    }

    private static function day_cell_styles(): array {
        return [
            'soft' => self::tr('Soft', 'Miękkie'),
            'solid' => self::tr('Solid', 'Pełne'),
            'outline' => self::tr('Outline', 'Obrys'),
        ];
    }

    private static function animation_levels(): array {
        return [
            'off' => self::tr('Off', 'Wyłączone'),
            'subtle' => self::tr('Subtle', 'Delikatne'),
        ];
    }

    private static function theme_palette(string $theme, string $style_preset = 'classic'): array {
        $key = self::sanitize_choice($theme, self::theme_presets(), 'dark');
        $style_key = self::sanitize_choice($style_preset, self::style_presets(), 'classic');
        $palettes = [
            'dark' => ['bg' => '#060709', 'text' => '#f5f7fa'],
            'light' => ['bg' => '#f4f4f1', 'text' => '#161612'],
            'white' => ['bg' => '#ffffff', 'text' => '#0f0f10'],
            'graphite' => ['bg' => '#151a23', 'text' => '#f5f7fa'],
            'sand' => ['bg' => '#e6ddd2', 'text' => '#201a14'],
        ];
        $palette = $palettes[$key] ?? $palettes['dark'];
        $palette['accent'] = self::default_accent_for_style_preset($style_key);
        return $palette;
    }

    private static function default_accent_for_style_preset(string $style_preset): string {
        $style_key = self::sanitize_choice($style_preset, self::style_presets(), 'classic');
        return '#22c55e';
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
