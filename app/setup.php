<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_action('admin_head', function () {
    $screen = get_current_screen();

    if (! $screen) {
        return;
    }

    $isPostEditorScreen = in_array($screen->base, ['post', 'post-new'], true);
    $isBlockEditorScreen = method_exists($screen, 'is_block_editor') ? (bool) $screen->is_block_editor() : false;

    if (! $isPostEditorScreen && ! $isBlockEditorScreen) {
        return;
    }

    if (! Vite::isRunningHot()) {
        $dependencies = json_decode(Vite::content('editor.deps.json'));

        foreach ($dependencies as $dependency) {
            if (! wp_script_is($dependency)) {
                wp_enqueue_script($dependency);
            }
        }
    }
    echo Vite::withEntryPoints([
        'resources/css/editor.css',
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Disable on-demand block asset loading.
 *
 * @link https://core.trac.wordpress.org/ticket/61965
 */
add_filter('should_load_separate_core_block_assets', '__return_false');

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

/**
 * Register Events custom post type.
 *
 * @return void
 */
add_action('init', function () {
    register_post_type('event', [
        'labels' => [
            'name' => __('Wydarzenia', 'sage'),
            'singular_name' => __('Wydarzenie', 'sage'),
            'add_new' => __('Dodaj nowe', 'sage'),
            'add_new_item' => __('Dodaj wydarzenie', 'sage'),
            'edit_item' => __('Edytuj wydarzenie', 'sage'),
            'new_item' => __('Nowe wydarzenie', 'sage'),
            'view_item' => __('Zobacz wydarzenie', 'sage'),
            'search_items' => __('Szukaj wydarzeń', 'sage'),
            'not_found' => __('Nie znaleziono wydarzeń', 'sage'),
            'not_found_in_trash' => __('Brak wydarzeń w koszu', 'sage'),
            'all_items' => __('Wszystkie wydarzenia', 'sage'),
            'archives' => __('Archiwum wydarzeń', 'sage'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'wydarzenia',
        'rewrite' => [
            'slug' => 'wydarzenia',
            'with_front' => false,
        ],
        'menu_position' => 21,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
    ]);
});

/**
 * Flush rewrite rules once after registering Event CPT.
 *
 * @return void
 */
add_action('init', function () {
    $flushKey = 'animated_event_rewrite_flushed_v1';

    if (get_option($flushKey) === '1') {
        return;
    }

    flush_rewrite_rules(false);
    update_option($flushKey, '1', true);
}, 20);

/**
 * Register global settings options pages.
 *
 * @return void
 */
add_action('acf/init', function () {
    if (! function_exists('acf_add_options_page') || ! function_exists('acf_add_options_sub_page')) {
        return;
    }

    acf_add_options_page([
        'page_title' => __('Global Settings', 'sage'),
        'menu_title' => __('Global Settings', 'sage'),
        'menu_slug' => 'global-settings',
        'capability' => 'edit_posts',
        'redirect' => true,
        'position' => 60,
        'icon_url' => 'dashicons-admin-generic',
    ]);

    acf_add_options_sub_page([
        'page_title' => __('Social Media', 'sage'),
        'menu_title' => __('Social Media', 'sage'),
        'parent_slug' => 'global-settings',
        'menu_slug' => 'global-settings-social-media',
        'capability' => 'edit_posts',
    ]);
});

/**
 * Register booking requests post type.
 *
 * @return void
 */
add_action('init', function () {
    register_post_type('booking_request', [
        'labels' => [
            'name' => __('Zapytania Rezerwacji', 'sage'),
            'singular_name' => __('Zapytanie Rezerwacji', 'sage'),
            'add_new' => __('Dodaj zapytanie', 'sage'),
            'add_new_item' => __('Dodaj zapytanie rezerwacji', 'sage'),
            'edit_item' => __('Edytuj zapytanie', 'sage'),
            'new_item' => __('Nowe zapytanie', 'sage'),
            'view_item' => __('Zobacz zapytanie', 'sage'),
            'search_items' => __('Szukaj zapytań', 'sage'),
            'not_found' => __('Brak zapytań', 'sage'),
            'all_items' => __('Wszystkie zapytania', 'sage'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 22,
        'menu_icon' => 'dashicons-email-alt2',
        'supports' => ['title'],
        'capability_type' => 'post',
        'map_meta_cap' => true,
    ]);
});

/**
 * Schedule cleanup for expired 48h holds.
 *
 * @return void
 */
add_action('init', function () {
    if (! wp_next_scheduled('animated_booking_hold_cleanup')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'animated_booking_hold_cleanup');
    }
});

add_action('animated_booking_hold_cleanup', function () {
    animated_cleanup_expired_booking_holds();
});

add_action('admin_post_nopriv_animated_submit_booking_request', __NAMESPACE__.'\\animated_handle_booking_request_submit');
add_action('admin_post_animated_submit_booking_request', __NAMESPACE__.'\\animated_handle_booking_request_submit');

add_filter('manage_booking_request_posts_columns', function ($columns) {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => __('Zgłoszenie', 'sage'),
        'abr_date' => __('Data', 'sage'),
        'abr_option' => __('Usługa / Pakiet', 'sage'),
        'abr_contact' => __('Kontakt', 'sage'),
        'abr_status' => __('Status', 'sage'),
        'abr_hold' => __('Hold do', 'sage'),
        'date' => $columns['date'] ?? __('Data utworzenia', 'sage'),
    ];
});

add_action('manage_booking_request_posts_custom_column', function ($column, $postId) {
    if ($column === 'abr_date') {
        echo esc_html((string) get_post_meta($postId, '_abr_date', true));

        return;
    }

    if ($column === 'abr_option') {
        echo esc_html((string) get_post_meta($postId, '_abr_option', true));

        return;
    }

    if ($column === 'abr_contact') {
        $name = (string) get_post_meta($postId, '_abr_full_name', true);
        $email = (string) get_post_meta($postId, '_abr_email', true);
        $phone = (string) get_post_meta($postId, '_abr_phone', true);
        echo esc_html(trim($name));
        echo '<br>';
        echo esc_html(trim($email));
        if (trim($phone) !== '') {
            echo '<br>';
            echo esc_html(trim($phone));
        }

        return;
    }

    if ($column === 'abr_status') {
        $status = (string) get_post_meta($postId, '_abr_status', true);
        $label = match ($status) {
            'hold_48h' => 'Hold 48h',
            'expired' => 'Wygasło',
            'approved' => 'Zatwierdzone',
            'rejected' => 'Odrzucone',
            default => $status !== '' ? $status : '-',
        };
        echo esc_html($label);

        return;
    }

    if ($column === 'abr_hold') {
        $expiresAt = (int) get_post_meta($postId, '_abr_hold_expires_at', true);
        if ($expiresAt > 0) {
            echo esc_html(wp_date('Y-m-d H:i', $expiresAt));
        } else {
            echo '—';
        }
    }
}, 10, 2);

/**
 * Parse YYYY-mm-dd date string and return timestamp at midnight.
 */
function animated_date_key_to_timestamp(string $dateKey): ?int
{
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
        return null;
    }

    $date = \DateTimeImmutable::createFromFormat('Y-m-d', $dateKey);

    if (! $date) {
        return null;
    }

    return (int) $date->setTime(0, 0, 0)->getTimestamp();
}

/**
 * Decode status map payload.
 */
function animated_decode_status_map($raw): array
{
    if (is_array($raw)) {
        return $raw;
    }

    if (! is_string($raw)) {
        return [];
    }

    $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);

    return is_array($decoded) ? $decoded : [];
}

/**
 * Resolve day status for date key from module map + ranges.
 */
function animated_resolve_module_day_status(array $module, string $dateKey): array
{
    $priority = [
        'available' => 1,
        'tentative' => 2,
        'booked' => 3,
    ];

    $statusMap = animated_decode_status_map($module['calendar_status_map'] ?? '{}');
    $mapped = $statusMap[$dateKey] ?? null;

    if (is_string($mapped) && isset($priority[$mapped])) {
        return ['status' => $mapped, 'note' => ''];
    }

    if (is_array($mapped)) {
        $status = trim((string) ($mapped['status'] ?? ''));
        $note = trim((string) ($mapped['note'] ?? ''));
        if (isset($priority[$status])) {
            return ['status' => $status, 'note' => $note];
        }
    }

    $dateTs = animated_date_key_to_timestamp($dateKey);
    if (! $dateTs) {
        return ['status' => 'none', 'note' => ''];
    }

    $selected = null;
    $ranges = is_array($module['date_ranges'] ?? null) ? $module['date_ranges'] : [];
    foreach ($ranges as $row) {
        $start = trim((string) ($row['start_date'] ?? ''));
        $end = trim((string) ($row['end_date'] ?? ''));
        $status = trim((string) ($row['status'] ?? ''));
        $note = trim((string) ($row['note'] ?? ''));

        if (! isset($priority[$status])) {
            continue;
        }

        $startTs = animated_date_key_to_timestamp($start);
        $endTs = animated_date_key_to_timestamp($end);
        if (! $startTs || ! $endTs) {
            continue;
        }

        $rangeStart = min($startTs, $endTs);
        $rangeEnd = max($startTs, $endTs);
        if ($dateTs < $rangeStart || $dateTs > $rangeEnd) {
            continue;
        }

        if (! $selected || $priority[$status] > $priority[$selected['status']]) {
            $selected = [
                'status' => $status,
                'note' => $note,
            ];
        }
    }

    return $selected ?: ['status' => 'none', 'note' => ''];
}

/**
 * Persist day status in module map.
 */
function animated_update_module_day_status(int $postId, int $moduleIndex, string $dateKey, array $payload): bool
{
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return false;
    }

    $modules = get_field('flexible_modules', $postId);
    if (! is_array($modules) || ! isset($modules[$moduleIndex]) || ! is_array($modules[$moduleIndex])) {
        return false;
    }

    $row = $modules[$moduleIndex];
    if (($row['acf_fc_layout'] ?? '') !== 'availability-calendar') {
        return false;
    }

    $statusMap = animated_decode_status_map($row['calendar_status_map'] ?? '{}');
    $statusMap[$dateKey] = $payload;
    $modules[$moduleIndex]['calendar_status_map'] = wp_json_encode($statusMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    update_field('flexible_modules', $modules, $postId);

    return true;
}

/**
 * Cleanup expired hold reservations and release dates.
 *
 * @return void
 */
function animated_cleanup_expired_booking_holds(int $limit = 100): void
{
    $now = time();
    $limit = max(1, min(500, $limit));

    $expired = get_posts([
        'post_type' => 'booking_request',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'fields' => 'ids',
        'orderby' => 'date',
        'order' => 'ASC',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_abr_status',
                'value' => 'hold_48h',
            ],
            [
                'key' => '_abr_hold_expires_at',
                'value' => $now,
                'type' => 'NUMERIC',
                'compare' => '<=',
            ],
        ],
    ]);

    if (empty($expired)) {
        return;
    }

    foreach ($expired as $requestId) {
        $postId = (int) get_post_meta($requestId, '_abr_post_id', true);
        $moduleIndex = (int) get_post_meta($requestId, '_abr_module_index', true);
        $dateKey = trim((string) get_post_meta($requestId, '_abr_date', true));
        if ($postId <= 0 || $dateKey === '') {
            update_post_meta($requestId, '_abr_status', 'expired');
            continue;
        }

        if (function_exists('get_field') && function_exists('update_field')) {
            $modules = get_field('flexible_modules', $postId);
            if (is_array($modules) && isset($modules[$moduleIndex]) && is_array($modules[$moduleIndex])) {
                $statusMap = animated_decode_status_map($modules[$moduleIndex]['calendar_status_map'] ?? '{}');
                $entry = $statusMap[$dateKey] ?? null;

                if (is_array($entry) && (int) ($entry['hold_request_id'] ?? 0) === (int) $requestId) {
                    $statusMap[$dateKey] = [
                        'status' => 'available',
                        'note' => '',
                    ];
                    $modules[$moduleIndex]['calendar_status_map'] = wp_json_encode($statusMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    update_field('flexible_modules', $modules, $postId);
                }
            }
        }

        update_post_meta($requestId, '_abr_status', 'expired');
    }
}

/**
 * Handle booking form submission.
 *
 * @return void
 */
function animated_handle_booking_request_submit(): void
{
    animated_cleanup_expired_booking_holds(20);

    $postId = isset($_POST['booking_post_id']) ? absint($_POST['booking_post_id']) : 0;
    $moduleIndex = isset($_POST['booking_module_index']) ? (int) $_POST['booking_module_index'] : 0;
    $moduleId = sanitize_text_field((string) ($_POST['booking_module_id'] ?? ''));
    $redirectUrl = wp_get_referer() ?: ($postId > 0 ? get_permalink($postId) : home_url('/'));
    $redirectUrl = is_string($redirectUrl) && $redirectUrl !== '' ? $redirectUrl : home_url('/');

    $redirectWith = static function (string $state, string $message) use ($redirectUrl, $moduleId): void {
        $url = add_query_arg([
            'booking_module' => $moduleId,
            'booking_request' => $state,
            'booking_message' => $message,
        ], $redirectUrl);

        wp_safe_redirect($url);
        exit;
    };

    if (! isset($_POST['booking_nonce']) || ! wp_verify_nonce((string) $_POST['booking_nonce'], 'animated_submit_booking_request')) {
        $redirectWith('error', __('Niepoprawny token formularza. Odśwież stronę i spróbuj ponownie.', 'sage'));
    }

    $honeypot = trim((string) ($_POST['booking_honeypot'] ?? ''));
    if ($honeypot !== '') {
        $redirectWith('success', __('Dziękuję. Twoje zgłoszenie zostało zapisane.', 'sage'));
    }

    if ($postId <= 0 || ! function_exists('get_field')) {
        $redirectWith('error', __('Nie udało się odczytać konfiguracji kalendarza.', 'sage'));
    }

    $modules = get_field('flexible_modules', $postId);
    if (! is_array($modules) || ! isset($modules[$moduleIndex]) || ! is_array($modules[$moduleIndex])) {
        $redirectWith('error', __('Nie znaleziono modułu rezerwacji.', 'sage'));
    }

    $module = $modules[$moduleIndex];
    if (($module['acf_fc_layout'] ?? '') !== 'availability-calendar') {
        $redirectWith('error', __('Wybrany moduł nie obsługuje rezerwacji.', 'sage'));
    }

    $dateKey = sanitize_text_field((string) ($_POST['booking_date'] ?? ''));
    $fullName = sanitize_text_field((string) ($_POST['booking_full_name'] ?? ''));
    $option = sanitize_text_field((string) ($_POST['booking_option'] ?? ''));
    $email = sanitize_email((string) ($_POST['booking_email'] ?? ''));
    $phone = sanitize_text_field((string) ($_POST['booking_phone'] ?? ''));
    $message = sanitize_textarea_field((string) ($_POST['booking_message'] ?? ''));
    $consent = ! empty($_POST['booking_consent']);

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey) || $fullName === '' || $option === '' || $email === '' || $phone === '' || ! $consent) {
        $redirectWith('error', __('Uzupełnij wszystkie wymagane pola formularza.', 'sage'));
    }

    if (! is_email($email)) {
        $redirectWith('error', __('Podaj poprawny adres e-mail.', 'sage'));
    }

    $availableOptions = [];
    $bookingOptionsRaw = is_array($module['booking_options'] ?? null) ? $module['booking_options'] : [];
    foreach ($bookingOptionsRaw as $row) {
        $label = sanitize_text_field((string) ($row['label'] ?? ''));
        if ($label !== '') {
            $availableOptions[] = $label;
        }
    }

    if (empty($availableOptions) || ! in_array($option, $availableOptions, true)) {
        $redirectWith('error', __('Wybrana opcja usługi jest nieprawidłowa.', 'sage'));
    }

    $day = animated_resolve_module_day_status($module, $dateKey);
    if (($day['status'] ?? 'none') !== 'available') {
        $redirectWith('error', __('Ten termin nie jest już dostępny do rezerwacji.', 'sage'));
    }

    $holdHours = 48;
    $holdExpiresAt = time() + ($holdHours * HOUR_IN_SECONDS);
    $holdExpiresHuman = wp_date('d.m.Y H:i', $holdExpiresAt);

    $requestTitle = sprintf('%s — %s — %s', $dateKey, $fullName, $option);
    $requestId = wp_insert_post([
        'post_type' => 'booking_request',
        'post_status' => 'publish',
        'post_title' => wp_strip_all_tags($requestTitle),
        'post_content' => $message,
    ], true);

    if (is_wp_error($requestId) || ! $requestId) {
        $redirectWith('error', __('Nie udało się zapisać zgłoszenia. Spróbuj ponownie.', 'sage'));
    }

    update_post_meta($requestId, '_abr_status', 'hold_48h');
    update_post_meta($requestId, '_abr_hold_expires_at', $holdExpiresAt);
    update_post_meta($requestId, '_abr_post_id', $postId);
    update_post_meta($requestId, '_abr_module_index', $moduleIndex);
    update_post_meta($requestId, '_abr_date', $dateKey);
    update_post_meta($requestId, '_abr_option', $option);
    update_post_meta($requestId, '_abr_full_name', $fullName);
    update_post_meta($requestId, '_abr_email', $email);
    update_post_meta($requestId, '_abr_phone', $phone);
    update_post_meta($requestId, '_abr_message', $message);

    $holdTemplate = sanitize_text_field((string) ($module['booking_hold_note_template'] ?? ''));
    if ($holdTemplate === '') {
        $holdTemplate = 'Wstępna rezerwacja na {hours}h (do {expires}).';
    }
    $holdNote = str_replace(['{hours}', '{expires}'], [(string) $holdHours, $holdExpiresHuman], $holdTemplate);

    $updated = animated_update_module_day_status($postId, $moduleIndex, $dateKey, [
        'status' => 'tentative',
        'note' => $holdNote,
        'hold_request_id' => (int) $requestId,
        'hold_expires_at' => $holdExpiresAt,
    ]);

    if (! $updated) {
        wp_delete_post($requestId, true);
        $redirectWith('error', __('Nie udało się zaktualizować kalendarza terminu.', 'sage'));
    }

    $notifyEmail = sanitize_email((string) ($module['booking_notification_email'] ?? ''));
    if (! is_email($notifyEmail)) {
        $notifyEmail = (string) get_option('admin_email');
    }

    $eventTitle = get_the_title($postId);
    $adminSubject = sprintf('Nowe zapytanie rezerwacji: %s', $dateKey);
    $adminBody = implode("\n", [
        'Nowe zapytanie rezerwacji',
        '------------------------',
        'Data: '.$dateKey,
        'Usługa / Pakiet: '.$option,
        'Imię i nazwisko: '.$fullName,
        'E-mail: '.$email,
        'Telefon: '.$phone,
        'Strona: '.$eventTitle,
        'Hold do: '.$holdExpiresHuman,
        '',
        'Wiadomość:',
        $message !== '' ? $message : '-',
        '',
        'ID zgłoszenia: '.$requestId,
    ]);

    wp_mail($notifyEmail, $adminSubject, $adminBody);

    $clientSubject = 'Potwierdzenie przyjęcia zapytania o termin';
    $clientBody = implode("\n", [
        'Dziękuję za zapytanie.',
        'Twój termin został wstępnie zablokowany na 48h.',
        '',
        'Data: '.$dateKey,
        'Usługa / Pakiet: '.$option,
        'Hold do: '.$holdExpiresHuman,
        '',
        'Skontaktuję się z Tobą, aby potwierdzić szczegóły.',
    ]);
    wp_mail($email, $clientSubject, $clientBody);

    $successMessage = trim((string) ($module['booking_success_message'] ?? ''));
    if ($successMessage === '') {
        $successMessage = 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na 48h.';
    }

    $redirectWith('success', $successMessage);
}
