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
    $locale = strtolower((string) determine_locale());
    $isPolish = str_starts_with($locale, 'pl');
    $tr = static fn (string $en, string $pl): string => $isPolish ? $pl : $en;

    $editorI18n = [
        'locale' => $isPolish ? 'pl-PL' : 'en-US',
        'status_available' => $tr('Available', 'Dostępny'),
        'status_tentative' => $tr('Tentative', 'Wstępna'),
        'status_booked' => $tr('Booked', 'Zajęty'),
        'clear_selected' => $tr('Clear selected', 'Wyczyść zaznaczone'),
        'note_optional' => $tr('Note (optional)', 'Notatka (opcjonalnie)'),
        'apply_selected' => $tr('Apply to selected', 'Zastosuj do zaznaczonych'),
        'unselect_all' => $tr('Unselect all', 'Odznacz wszystko'),
        'time_placeholder' => $tr('Time HH:MM', 'Godzina HH:MM'),
        'time_add' => $tr('Add time to selected dates', 'Dodaj godzinę do zaznaczonych dni'),
        'time_remove' => $tr('Remove time from selected dates', 'Usuń godzinę z zaznaczonych dni'),
        'weekdays' => $isPolish ? ['Pon', 'Wt', 'Śr', 'Czw', 'Pt', 'Sob', 'Nd'] : ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'time_preview_title' => $tr('Hours preview:', 'Podgląd godzin:'),
        'select_one_date' => $tr('Select one date to preview available hours.', 'Zaznacz jedną datę, aby zobaczyć dostępne godziny.'),
        'select_single_date' => $tr('Multiple dates selected. Preview works for one date at a time.', 'Zaznaczono kilka dat. Podgląd działa dla jednej daty naraz.'),
        'no_configured_hours' => $tr('No configured hours.', 'Brak skonfigurowanych godzin.'),
        'available' => $tr('Available:', 'Dostępne:'),
        'busy_hold' => $tr('Booked / hold:', 'Zajęte / hold:'),
        'no_free_hours' => $tr('No free hours', 'Brak wolnych godzin'),
        'none' => $tr('None', 'Brak'),
        'summary_selected_prefix' => $tr('Selected dates:', 'Zaznaczono dni:'),
        'summary_status' => $tr('Status:', 'Status:'),
        'summary_idle' => $tr('Click dates in the calendar, then choose status and apply. Saved:', 'Kliknij dni w kalendarzu, potem wybierz status i zastosuj. Zapisane:'),
        'summary_map' => $tr('map', 'mapa'),
        'summary_ranges' => $tr('ranges', 'zakresy'),
    ];
    echo '<script>window.AnimatedEditorI18n=' . wp_json_encode($editorI18n, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';

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
    load_theme_textdomain('sage', get_template_directory() . '/resources/lang');

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
 * Schedule cleanup for expired tentative holds.
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

add_action('init', function () {
    animated_maybe_cleanup_expired_booking_holds();
}, 30);

add_action('admin_post_nopriv_animated_submit_booking_request', __NAMESPACE__.'\\animated_handle_booking_request_submit');
add_action('admin_post_animated_submit_booking_request', __NAMESPACE__.'\\animated_handle_booking_request_submit');
add_action('admin_post_animated_booking_request_decision', __NAMESPACE__.'\\animated_handle_booking_request_decision');

add_filter('manage_booking_request_posts_columns', function ($columns) {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => __('Zgłoszenie', 'sage'),
        'abr_date' => __('Data', 'sage'),
        'abr_time' => __('Godzina', 'sage'),
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

    if ($column === 'abr_time') {
        echo esc_html((string) get_post_meta($postId, '_abr_time', true));

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
            'hold_48h' => __('Hold aktywny', 'sage'),
            'expired' => __('Wygasło', 'sage'),
            'approved' => __('Zatwierdzone', 'sage'),
            'rejected' => __('Odrzucone', 'sage'),
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

add_filter('post_row_actions', function ($actions, $post) {
    if (! $post instanceof \WP_Post || $post->post_type !== 'booking_request') {
        return $actions;
    }
    if (! current_user_can('edit_post', $post->ID)) {
        return $actions;
    }

    $status = (string) get_post_meta($post->ID, '_abr_status', true);
    if (! in_array($status, ['hold_48h', 'approved', 'rejected', 'expired'], true)) {
        $status = 'hold_48h';
    }

    $base = admin_url('admin-post.php');
    if ($status !== 'approved') {
        $approveUrl = wp_nonce_url(add_query_arg([
            'action' => 'animated_booking_request_decision',
            'request_id' => $post->ID,
            'decision' => 'approve',
        ], $base), 'animated_booking_request_decision_'.$post->ID.'_approve', 'abr_nonce');
        $actions['abr_approve'] = '<a href="'.esc_url($approveUrl).'">'.esc_html__('Zatwierdź', 'sage').'</a>';
    }

    if ($status !== 'rejected') {
        $rejectUrl = wp_nonce_url(add_query_arg([
            'action' => 'animated_booking_request_decision',
            'request_id' => $post->ID,
            'decision' => 'reject',
        ], $base), 'animated_booking_request_decision_'.$post->ID.'_reject', 'abr_nonce');
        $actions['abr_reject'] = '<a href="'.esc_url($rejectUrl).'">'.esc_html__('Odrzuć', 'sage').'</a>';
    }

    return $actions;
}, 10, 2);

add_action('admin_notices', function () {
    if (! is_admin()) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (! $screen || $screen->post_type !== 'booking_request') {
        return;
    }
    $decision = isset($_GET['abr_decision']) ? sanitize_key((string) $_GET['abr_decision']) : '';
    if ($decision === 'approved') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Status zgłoszenia ustawiono na: Zatwierdzone.', 'sage') . '</p></div>';
        return;
    }
    if ($decision === 'rejected') {
        echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('Status zgłoszenia ustawiono na: Odrzucone.', 'sage') . '</p></div>';
    }
});

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
 * Parse HH:MM slots from textarea value.
 */
function animated_parse_time_slots(string $raw): array
{
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

/**
 * Normalize overrides payload: date => [HH:MM...].
 */
function animated_normalize_time_slots_overrides($raw): array
{
    if (is_array($raw)) {
        $decoded = $raw;
    } elseif (is_string($raw)) {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }
    } else {
        return [];
    }

    $out = [];
    foreach ($decoded as $date => $slots) {
        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! is_array($slots)) {
            continue;
        }
        $valid = [];
        foreach ($slots as $slot) {
            $slot = is_string($slot) ? trim($slot) : '';
            if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
                $valid[] = $slot;
            }
        }
        $valid = array_values(array_unique($valid));
        sort($valid);
        $out[$date] = $valid;
    }

    ksort($out);

    return $out;
}

/**
 * Normalize reservations payload: date => time => {status,expires_at,request_id}.
 */
function animated_normalize_time_slot_reservations($raw): array
{
    if (is_array($raw)) {
        $decoded = $raw;
    } elseif (is_string($raw)) {
        $decoded = json_decode(trim($raw) !== '' ? $raw : '{}', true);
        if (! is_array($decoded)) {
            return [];
        }
    } else {
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
            $requestId = isset($entry['request_id']) ? (int) $entry['request_id'] : 0;
            if ($status === 'hold' && $expires > 0 && $expires <= time()) {
                continue;
            }
            $out[$date][$slot] = [
                'status' => $status,
                'expires_at' => $expires,
                'request_id' => $requestId,
            ];
        }
        if (isset($out[$date])) {
            ksort($out[$date]);
        }
    }

    ksort($out);

    return $out;
}

/**
 * Get effective slots for date (override or default).
 */
function animated_get_module_date_slots(array $module, string $dateKey, array $overrides): array
{
    $defaultSlots = animated_parse_time_slots((string) ($module['booking_default_time_slots'] ?? ''));

    if (! isset($overrides[$dateKey]) || ! is_array($overrides[$dateKey])) {
        return $defaultSlots;
    }

    $slots = [];
    foreach ($overrides[$dateKey] as $slot) {
        if (is_string($slot) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $slot)) {
            $slots[] = $slot;
        }
    }
    $slots = array_values(array_unique($slots));
    sort($slots);

    return $slots;
}

/**
 * Aggregate day status from slot reservations.
 */
function animated_apply_slot_aggregate_to_status_map(
    string $dateKey,
    array $module,
    array $overrides,
    array $reservations,
    array $statusMap
): array {
    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey)) {
        return $statusMap;
    }

    $slots = animated_get_module_date_slots($module, $dateKey, $overrides);
    if (empty($slots)) {
        return $statusMap;
    }

    $dayReservations = isset($reservations[$dateKey]) && is_array($reservations[$dateKey]) ? $reservations[$dateKey] : [];
    $currentStatus = is_array($statusMap[$dateKey] ?? null) ? sanitize_text_field((string) (($statusMap[$dateKey]['status'] ?? 'none'))) : 'none';
    if ($currentStatus === 'booked' && empty($dayReservations)) {
        return $statusMap;
    }

    $reserved = 0;
    $hasHold = false;
    $hasBooked = false;
    foreach ($slots as $slot) {
        $entry = $dayReservations[$slot] ?? null;
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
            $hasHold = true;
            continue;
        }
        if ($status === 'booked') {
            $reserved++;
            $hasBooked = true;
        }
    }

    $free = max(0, count($slots) - $reserved);
    if ($free > 0) {
        $statusMap[$dateKey] = ['status' => 'available', 'note' => ''];
    } elseif ($hasBooked && ! $hasHold) {
        $statusMap[$dateKey] = ['status' => 'booked', 'note' => ''];
    } else {
        $statusMap[$dateKey] = ['status' => 'tentative', 'note' => ''];
    }

    return $statusMap;
}

/**
 * Build live reservations map from booking requests for a module.
 */
function animated_collect_live_time_reservations(int $postId, int $moduleIndex): array
{
    if ($postId <= 0) {
        return [];
    }

    $requestIds = get_posts([
        'post_type' => 'booking_request',
        'post_status' => 'publish',
        'posts_per_page' => 500,
        'fields' => 'ids',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_abr_post_id',
                'value' => $postId,
                'type' => 'NUMERIC',
            ],
            [
                'key' => '_abr_module_index',
                'value' => $moduleIndex,
                'type' => 'NUMERIC',
            ],
            [
                'key' => '_abr_status',
                'value' => ['hold_48h', 'approved'],
                'compare' => 'IN',
            ],
        ],
    ]);

    if (empty($requestIds)) {
        return [];
    }

    $now = time();
    $map = [];
    foreach ($requestIds as $requestId) {
        $dateKey = sanitize_text_field((string) get_post_meta($requestId, '_abr_date', true));
        $timeSlot = sanitize_text_field((string) get_post_meta($requestId, '_abr_time', true));
        $status = sanitize_text_field((string) get_post_meta($requestId, '_abr_status', true));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey) || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeSlot)) {
            continue;
        }

        if ($status === 'approved') {
            $map[$dateKey][$timeSlot] = [
                'status' => 'booked',
                'expires_at' => 0,
                'request_id' => (int) $requestId,
            ];
            continue;
        }

        if ($status === 'hold_48h') {
            $expiresAt = (int) get_post_meta($requestId, '_abr_hold_expires_at', true);
            if ($expiresAt > 0 && $expiresAt <= $now) {
                continue;
            }
            $map[$dateKey][$timeSlot] = [
                'status' => 'hold',
                'expires_at' => $expiresAt,
                'request_id' => (int) $requestId,
            ];
        }
    }

    return animated_normalize_time_slot_reservations($map);
}

/**
 * Return availability module row by post/module index.
 */
function animated_get_availability_module(int $postId, int $moduleIndex): ?array
{
    if (! function_exists('get_field')) {
        return null;
    }

    $modules = get_field('flexible_modules', $postId);
    if (! is_array($modules) || ! isset($modules[$moduleIndex]) || ! is_array($modules[$moduleIndex])) {
        return null;
    }

    $module = $modules[$moduleIndex];
    if (($module['acf_fc_layout'] ?? '') !== 'availability-calendar') {
        return null;
    }

    return $module;
}

/**
 * Fill template placeholders for booking messages.
 */
function animated_booking_replace_tokens(string $template, array $context): string
{
    $pairs = [];
    foreach ($context as $key => $value) {
        $pairs['{'.$key.'}'] = (string) $value;
    }

    return strtr($template, $pairs);
}

/**
 * Build optional wp_mail headers from module sender settings.
 */
function animated_booking_mail_headers(array $module): array
{
    $fromEmail = sanitize_email((string) ($module['booking_from_email'] ?? ''));
    $fromName = sanitize_text_field((string) ($module['booking_from_name'] ?? ''));
    if (! is_email($fromEmail)) {
        return [];
    }

    if ($fromName !== '') {
        return ['From: '.$fromName.' <'.$fromEmail.'>'];
    }

    return ['From: '.$fromEmail];
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
        $holdExpiresAt = isset($mapped['hold_expires_at']) ? (int) $mapped['hold_expires_at'] : 0;

        if ($status === 'tentative' && $holdExpiresAt > 0 && $holdExpiresAt <= time()) {
            return ['status' => 'available', 'note' => ''];
        }

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
        $timeSlot = sanitize_text_field((string) get_post_meta($requestId, '_abr_time', true));
        $holdExpiresAt = (int) get_post_meta($requestId, '_abr_hold_expires_at', true);
        $holdMinutes = (int) get_post_meta($requestId, '_abr_hold_minutes', true);
        $holdMinutes = max(1, min(10080, $holdMinutes > 0 ? $holdMinutes : 2880));
        $holdHours = round($holdMinutes / 60, 2);
        $holdHoursLabel = rtrim(rtrim(number_format($holdHours, 2, '.', ''), '0'), '.');

        if ($postId > 0 && $dateKey !== '' && function_exists('get_field') && function_exists('update_field')) {
            $modules = get_field('flexible_modules', $postId);
            if (is_array($modules) && isset($modules[$moduleIndex]) && is_array($modules[$moduleIndex])) {
                $module = $modules[$moduleIndex];
                if (($module['acf_fc_layout'] ?? '') === 'availability-calendar') {
                    $statusMap = animated_decode_status_map($module['calendar_status_map'] ?? '{}');
                    $overrides = animated_normalize_time_slots_overrides($module['calendar_time_slots_overrides'] ?? '{}');
                    $reservations = animated_normalize_time_slot_reservations($module['calendar_time_slots_reservations'] ?? '{}');

                    if ($timeSlot !== '' && isset($reservations[$dateKey][$timeSlot])) {
                        $entry = $reservations[$dateKey][$timeSlot];
                        if ((int) ($entry['request_id'] ?? 0) === (int) $requestId) {
                            unset($reservations[$dateKey][$timeSlot]);
                            if (empty($reservations[$dateKey])) {
                                unset($reservations[$dateKey]);
                            }
                        }
                    }

                    $statusMap = animated_apply_slot_aggregate_to_status_map($dateKey, $module, $overrides, $reservations, $statusMap);
                    $modules[$moduleIndex]['calendar_status_map'] = wp_json_encode($statusMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $modules[$moduleIndex]['calendar_time_slots_reservations'] = wp_json_encode($reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    update_field('flexible_modules', $modules, $postId);
                }
            }
        }

        $module = animated_get_availability_module($postId, $moduleIndex);
        $module = is_array($module) ? $module : [];
        $sendExpiredEmailRaw = $module['booking_send_expired_email'] ?? 1;
        $sendExpiredEmail = ! in_array($sendExpiredEmailRaw, [0, '0', false, 'false'], true);
        $clientEmail = sanitize_email((string) get_post_meta($requestId, '_abr_email', true));
        if ($sendExpiredEmail && is_email($clientEmail)) {
            $fullName = sanitize_text_field((string) get_post_meta($requestId, '_abr_full_name', true));
            $option = sanitize_text_field((string) get_post_meta($requestId, '_abr_option', true));
            $siteName = get_bloginfo('name');
            $expiresHuman = $holdExpiresAt > 0 ? wp_date('d.m.Y H:i', $holdExpiresAt) : wp_date('d.m.Y H:i');

            $subjectTemplate = trim((string) ($module['booking_client_expired_email_subject'] ?? ''));
            if ($subjectTemplate === '') {
                $subjectTemplate = 'Wstępna rezerwacja wygasła';
            }
            $bodyTemplate = trim((string) ($module['booking_client_expired_email_body'] ?? ''));
            if ($bodyTemplate === '') {
                $bodyTemplate = "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}";
            }

            $tokenContext = [
                'full_name' => $fullName,
                'date' => $dateKey,
                'time' => $timeSlot,
                'option' => $option,
                'hours' => $holdHoursLabel,
                'minutes' => (string) $holdMinutes,
                'expires' => $expiresHuman,
                'site_name' => $siteName,
            ];

            $subject = animated_booking_replace_tokens($subjectTemplate, $tokenContext);
            $body = animated_booking_replace_tokens($bodyTemplate, $tokenContext);
            wp_mail($clientEmail, $subject, $body, animated_booking_mail_headers($module));
        }

        update_post_meta($requestId, '_abr_status', 'expired');
    }
}

/**
 * Opportunistic cleanup fallback when WP Cron is not reliably triggered.
 *
 * @return void
 */
function animated_maybe_cleanup_expired_booking_holds(): void
{
    $cacheKey = 'animated_booking_cleanup_last_run';
    $now = time();
    $lastRun = (int) get_transient($cacheKey);

    if ($lastRun > 0 && ($now - $lastRun) < (10 * MINUTE_IN_SECONDS)) {
        return;
    }

    set_transient($cacheKey, $now, 15 * MINUTE_IN_SECONDS);
    animated_cleanup_expired_booking_holds(40);
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
    $timeSlot = sanitize_text_field((string) ($_POST['booking_time'] ?? ''));
    $fullName = sanitize_text_field((string) ($_POST['booking_full_name'] ?? ''));
    $option = sanitize_text_field((string) ($_POST['booking_option'] ?? ''));
    $email = sanitize_email((string) ($_POST['booking_email'] ?? ''));
    $phone = sanitize_text_field((string) ($_POST['booking_phone'] ?? ''));
    $message = sanitize_textarea_field((string) ($_POST['booking_message'] ?? ''));
    $consent = ! empty($_POST['booking_consent']);

    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey) || ! preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeSlot) || $fullName === '' || $option === '' || $email === '' || $phone === '' || ! $consent) {
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

    $statusMap = animated_decode_status_map($module['calendar_status_map'] ?? '{}');
    $overrides = animated_normalize_time_slots_overrides($module['calendar_time_slots_overrides'] ?? '{}');
    $reservations = animated_normalize_time_slot_reservations($module['calendar_time_slots_reservations'] ?? '{}');
    $liveReservations = animated_collect_live_time_reservations($postId, $moduleIndex);
    foreach ($liveReservations as $date => $slots) {
        foreach ($slots as $slot => $entry) {
            $reservations[$date][$slot] = $entry;
        }
    }
    $daySlots = animated_get_module_date_slots($module, $dateKey, $overrides);
    if (empty($daySlots)) {
        $redirectWith('error', __('Brak dostępnych godzin dla wybranej daty.', 'sage'));
    }
    if (! in_array($timeSlot, $daySlots, true)) {
        $redirectWith('error', __('Wybrana godzina nie jest dostępna dla tej daty.', 'sage'));
    }

    $existingSlot = $reservations[$dateKey][$timeSlot] ?? null;
    if (is_array($existingSlot)) {
        $slotStatus = (string) ($existingSlot['status'] ?? '');
        $slotExpires = (int) ($existingSlot['expires_at'] ?? 0);
        if ($slotStatus === 'booked') {
            $redirectWith('error', __('Wybrana godzina jest już zajęta.', 'sage'));
        }
        if ($slotStatus === 'hold' && ($slotExpires <= 0 || $slotExpires > time())) {
            $redirectWith('error', __('Wybrana godzina jest chwilowo zarezerwowana.', 'sage'));
        }
        unset($reservations[$dateKey][$timeSlot]);
    }

    $holdMinutesRaw = $module['booking_hold_minutes'] ?? 2880;
    $holdMinutes = is_numeric($holdMinutesRaw) ? (int) $holdMinutesRaw : 2880;
    $holdMinutes = max(1, min(10080, $holdMinutes));
    $holdHours = round($holdMinutes / 60, 2);
    $holdHoursLabel = rtrim(rtrim(number_format($holdHours, 2, '.', ''), '0'), '.');
    $holdExpiresAt = time() + ($holdMinutes * MINUTE_IN_SECONDS);
    $holdExpiresHuman = wp_date('d.m.Y H:i', $holdExpiresAt);

    $requestTitle = sprintf('%s %s — %s — %s', $dateKey, $timeSlot, $fullName, $option);
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
    update_post_meta($requestId, '_abr_hold_minutes', $holdMinutes);
    update_post_meta($requestId, '_abr_post_id', $postId);
    update_post_meta($requestId, '_abr_module_index', $moduleIndex);
    update_post_meta($requestId, '_abr_date', $dateKey);
    update_post_meta($requestId, '_abr_time', $timeSlot);
    update_post_meta($requestId, '_abr_option', $option);
    update_post_meta($requestId, '_abr_full_name', $fullName);
    update_post_meta($requestId, '_abr_email', $email);
    update_post_meta($requestId, '_abr_phone', $phone);
    update_post_meta($requestId, '_abr_message', $message);

    $holdTemplate = sanitize_text_field((string) ($module['booking_hold_note_template'] ?? ''));
    if ($holdTemplate === '') {
        $holdTemplate = 'Wstępna rezerwacja na {hours}h ({minutes} min), do {expires}.';
    }
    $holdNote = str_replace(
        ['{hours}', '{minutes}', '{expires}'],
        [$holdHoursLabel, (string) $holdMinutes, $holdExpiresHuman],
        $holdTemplate,
    );

    $reservations[$dateKey][$timeSlot] = [
        'status' => 'hold',
        'expires_at' => $holdExpiresAt,
        'request_id' => (int) $requestId,
    ];
    $statusMap = animated_apply_slot_aggregate_to_status_map($dateKey, $module, $overrides, $reservations, $statusMap);

    if (! function_exists('update_field')) {
        wp_delete_post($requestId, true);
        $redirectWith('error', __('Nie udało się zaktualizować kalendarza terminu.', 'sage'));
    }
    $modules[$moduleIndex]['calendar_status_map'] = wp_json_encode($statusMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $modules[$moduleIndex]['calendar_time_slots_reservations'] = wp_json_encode($reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    update_field('flexible_modules', $modules, $postId);

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
        'Godzina: '.$timeSlot,
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

    $mailHeaders = animated_booking_mail_headers($module);
    wp_mail($notifyEmail, $adminSubject, $adminBody, $mailHeaders);

    $sendInitialEmailRaw = $module['booking_send_initial_email'] ?? 1;
    $sendInitialEmail = ! in_array($sendInitialEmailRaw, [0, '0', false, 'false'], true);
    if ($sendInitialEmail && is_email($email)) {
        $clientSubjectTemplate = trim((string) ($module['booking_client_initial_email_subject'] ?? ''));
        if ($clientSubjectTemplate === '') {
            $clientSubjectTemplate = 'Potwierdzenie wstępnej rezerwacji terminu';
        }
        $clientBodyTemplate = trim((string) ($module['booking_client_initial_email_body'] ?? ''));
        if ($clientBodyTemplate === '') {
            $clientBodyTemplate = "Dziękuję za zapytanie.\n\nTwój termin został wstępnie zablokowany na {hours}h ({minutes} min).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nHold do: {expires}\n\nSkontaktuję się z Tobą, aby potwierdzić szczegóły.\n\n{site_name}";
        }
        $clientTokenContext = [
            'full_name' => $fullName,
            'date' => $dateKey,
            'time' => $timeSlot,
            'option' => $option,
            'hours' => $holdHoursLabel,
            'minutes' => (string) $holdMinutes,
            'expires' => $holdExpiresHuman,
            'site_name' => get_bloginfo('name'),
        ];
        $clientSubject = animated_booking_replace_tokens($clientSubjectTemplate, $clientTokenContext);
        $clientBody = animated_booking_replace_tokens($clientBodyTemplate, $clientTokenContext);
        wp_mail($email, $clientSubject, $clientBody, $mailHeaders);
    }

    $successMessage = trim((string) ($module['booking_success_message'] ?? ''));
    if ($successMessage === '') {
        $successMessage = 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na {hours}h.';
    }
    $successMessage = str_replace(
        ['{hours}', '{minutes}'],
        [$holdHoursLabel, (string) $holdMinutes],
        $successMessage,
    );

    $redirectWith('success', $successMessage);
}

/**
 * Handle approve/reject decision for booking request from admin list.
 */
function animated_handle_booking_request_decision(): void
{
    if (! is_admin()) {
        wp_die('Forbidden', 403);
    }

    $requestId = isset($_GET['request_id']) ? absint($_GET['request_id']) : 0;
    $decision = isset($_GET['decision']) ? sanitize_key((string) $_GET['decision']) : '';
    if ($requestId <= 0 || ! in_array($decision, ['approve', 'reject'], true)) {
        wp_die('Invalid request');
    }
    if (! current_user_can('edit_post', $requestId)) {
        wp_die('Forbidden', 403);
    }
    $nonce = isset($_GET['abr_nonce']) ? (string) $_GET['abr_nonce'] : '';
    if (! wp_verify_nonce($nonce, 'animated_booking_request_decision_'.$requestId.'_'.$decision)) {
        wp_die('Invalid nonce');
    }

    $request = get_post($requestId);
    if (! $request || $request->post_type !== 'booking_request') {
        wp_die('Request not found');
    }

    $postId = (int) get_post_meta($requestId, '_abr_post_id', true);
    $moduleIndex = (int) get_post_meta($requestId, '_abr_module_index', true);
    $dateKey = sanitize_text_field((string) get_post_meta($requestId, '_abr_date', true));
    $timeSlot = sanitize_text_field((string) get_post_meta($requestId, '_abr_time', true));
    $fullName = sanitize_text_field((string) get_post_meta($requestId, '_abr_full_name', true));
    $option = sanitize_text_field((string) get_post_meta($requestId, '_abr_option', true));
    $email = sanitize_email((string) get_post_meta($requestId, '_abr_email', true));

    $module = animated_get_availability_module($postId, $moduleIndex);
    $module = is_array($module) ? $module : [];

    if ($postId > 0 && function_exists('get_field') && function_exists('update_field')) {
        $modules = get_field('flexible_modules', $postId);
        if (is_array($modules) && isset($modules[$moduleIndex]) && is_array($modules[$moduleIndex])) {
            $targetModule = $modules[$moduleIndex];
            if (($targetModule['acf_fc_layout'] ?? '') === 'availability-calendar') {
                $statusMap = animated_decode_status_map($targetModule['calendar_status_map'] ?? '{}');
                $overrides = animated_normalize_time_slots_overrides($targetModule['calendar_time_slots_overrides'] ?? '{}');
                $reservations = animated_normalize_time_slot_reservations($targetModule['calendar_time_slots_reservations'] ?? '{}');

                if ($decision === 'approve') {
                    update_post_meta($requestId, '_abr_status', 'approved');
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeSlot)) {
                        $reservations[$dateKey][$timeSlot] = [
                            'status' => 'booked',
                            'expires_at' => 0,
                            'request_id' => $requestId,
                        ];
                        $statusMap = animated_apply_slot_aggregate_to_status_map($dateKey, $targetModule, $overrides, $reservations, $statusMap);
                    }
                } else {
                    update_post_meta($requestId, '_abr_status', 'rejected');
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateKey) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $timeSlot)) {
                        if (isset($reservations[$dateKey][$timeSlot]) && (int) ($reservations[$dateKey][$timeSlot]['request_id'] ?? 0) === $requestId) {
                            unset($reservations[$dateKey][$timeSlot]);
                            if (empty($reservations[$dateKey])) {
                                unset($reservations[$dateKey]);
                            }
                        }
                        $statusMap = animated_apply_slot_aggregate_to_status_map($dateKey, $targetModule, $overrides, $reservations, $statusMap);
                    }
                }

                $modules[$moduleIndex]['calendar_status_map'] = wp_json_encode($statusMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $modules[$moduleIndex]['calendar_time_slots_reservations'] = wp_json_encode($reservations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                update_field('flexible_modules', $modules, $postId);
            }
        }
    }

    if ($decision === 'approve') {
        $sendRaw = $module['booking_send_approved_email'] ?? 1;
        $send = ! in_array($sendRaw, [0, '0', false, 'false'], true);
        if ($send && is_email($email)) {
            $subjectTpl = trim((string) ($module['booking_client_approved_email_subject'] ?? ''));
            if ($subjectTpl === '') {
                $subjectTpl = 'Rezerwacja terminu została potwierdzona';
            }
            $bodyTpl = trim((string) ($module['booking_client_approved_email_body'] ?? ''));
            if ($bodyTpl === '') {
                $bodyTpl = "Cześć {full_name},\n\nTwoja rezerwacja została potwierdzona.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nW razie pytań odpowiedz na tę wiadomość.\n\n{site_name}";
            }
            $ctx = [
                'full_name' => $fullName,
                'date' => $dateKey,
                'time' => $timeSlot,
                'option' => $option,
                'status' => 'Zatwierdzona',
                'site_name' => get_bloginfo('name'),
            ];
            wp_mail($email, animated_booking_replace_tokens($subjectTpl, $ctx), animated_booking_replace_tokens($bodyTpl, $ctx), animated_booking_mail_headers($module));
        }
    } else {
        $sendRaw = $module['booking_send_rejected_email'] ?? 1;
        $send = ! in_array($sendRaw, [0, '0', false, 'false'], true);
        if ($send && is_email($email)) {
            $subjectTpl = trim((string) ($module['booking_client_rejected_email_subject'] ?? ''));
            if ($subjectTpl === '') {
                $subjectTpl = 'Rezerwacja terminu nie została potwierdzona';
            }
            $bodyTpl = trim((string) ($module['booking_client_rejected_email_body'] ?? ''));
            if ($bodyTpl === '') {
                $bodyTpl = "Cześć {full_name},\n\nNiestety nie mogliśmy potwierdzić rezerwacji tego terminu.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nMożesz wybrać inny dostępny termin i wysłać nowe zapytanie.\n\n{site_name}";
            }
            $ctx = [
                'full_name' => $fullName,
                'date' => $dateKey,
                'time' => $timeSlot,
                'option' => $option,
                'status' => 'Odrzucona',
                'site_name' => get_bloginfo('name'),
            ];
            wp_mail($email, animated_booking_replace_tokens($subjectTpl, $ctx), animated_booking_replace_tokens($bodyTpl, $ctx), animated_booking_mail_headers($module));
        }
    }

    $back = wp_get_referer();
    if (! is_string($back) || $back === '') {
        $back = admin_url('edit.php?post_type=booking_request');
    }
    wp_safe_redirect(add_query_arg('abr_decision', $decision === 'approve' ? 'approved' : 'rejected', $back));
    exit;
}
