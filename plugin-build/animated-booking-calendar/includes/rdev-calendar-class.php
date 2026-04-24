<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('Rdev_Calendar_Plugin')) {
final class Rdev_Calendar_Plugin {
    use Rdev_Calendar_Settings_Trait;
    use Rdev_Calendar_Admin_Trait;
    use Rdev_Calendar_Frontend_Trait;
    use Rdev_Calendar_Booking_Trait;

    private const VERSION = '1.0.0';
    private const CALENDAR_CPT = 'abc_calendar';
    private const REQUEST_CPT = 'abc_booking_request';

    private static function plugin_base_file(): string {
        return dirname(__DIR__) . '/rdev-calendar.php';
    }

    private static function asset_version(string $relative_path): string {
        $base_file = self::plugin_base_file();
        $path = plugin_dir_path($base_file) . ltrim($relative_path, '/');
        $mtime = file_exists($path) ? (int) filemtime($path) : 0;
        return self::VERSION . ($mtime > 0 ? '.' . $mtime : '');
    }

    public static function boot(): void {
        static $booted = false;
        if ($booted) {
            return;
        }
        $booted = true;

        add_action('init', [self::class, 'register_post_types']);
        add_action('init', [self::class, 'register_shortcodes']);

        add_action('add_meta_boxes', [self::class, 'register_meta_boxes']);
        add_action('save_post_' . self::CALENDAR_CPT, [self::class, 'save_calendar_meta']);

        add_action('admin_enqueue_scripts', [self::class, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [self::class, 'register_front_assets']);

        add_action('admin_post_nopriv_abc_submit_booking_request', [self::class, 'handle_booking_submit']);
        add_action('admin_post_abc_submit_booking_request', [self::class, 'handle_booking_submit']);
        add_action('admin_post_abc_request_decision', [self::class, 'handle_request_decision']);

        add_action('init', [self::class, 'schedule_cleanup']);
        add_action('abc_booking_cleanup_event', [self::class, 'cleanup_expired_holds']);
        add_action('init', [self::class, 'maybe_cleanup_expired_holds'], 30);

        add_filter('manage_' . self::REQUEST_CPT . '_posts_columns', [self::class, 'request_columns']);
        add_action('manage_' . self::REQUEST_CPT . '_posts_custom_column', [self::class, 'render_request_column'], 10, 2);
        add_filter('post_row_actions', [self::class, 'request_row_actions'], 10, 2);
        add_action('admin_notices', [self::class, 'maybe_render_admin_notice']);
    }
}
}
