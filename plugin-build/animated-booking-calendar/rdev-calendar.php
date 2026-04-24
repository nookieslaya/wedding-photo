<?php
/**
 * Plugin Name: rdev-calendar
 * Description: Professional booking calendar for WordPress with availability management, temporary holds, booking requests, and email notifications.
 * Version: 1.0.0
 * Author: rdev.website
 * Text Domain: rdev-calendar
 * Requires PHP: 7.1
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('str_starts_with')) {
    /**
     * PHP < 8.0 compatibility polyfill.
     *
     * @param string $haystack
     * @param string $needle
     */
    function str_starts_with($haystack, $needle) {
        $haystack = (string) $haystack;
        $needle = (string) $needle;
        if ($needle === '') {
            return true;
        }
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}

add_action('plugins_loaded', static function () {
    load_plugin_textdomain('rdev-calendar', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

$rdev_calendar_boot_error = '';

$required_files = [
    __DIR__ . '/includes/traits/rdev-calendar-trait-settings.php',
    __DIR__ . '/includes/traits/rdev-calendar-trait-admin.php',
    __DIR__ . '/includes/traits/rdev-calendar-trait-frontend.php',
    __DIR__ . '/includes/traits/rdev-calendar-trait-booking.php',
    __DIR__ . '/includes/rdev-calendar-class.php',
];

if (version_compare(PHP_VERSION, '7.1.0', '<')) {
    $rdev_calendar_boot_error = 'rdev-calendar requires PHP 7.1+; current version: ' . PHP_VERSION;
}

if ($rdev_calendar_boot_error === '') {
    try {
        foreach ($required_files as $required_file) {
            if (! file_exists($required_file)) {
                throw new \RuntimeException('Missing file: ' . $required_file . '. Reinstall plugin package.');
            }
            require_once $required_file;
        }

        if (class_exists('Rdev_Calendar_Plugin') && method_exists('Rdev_Calendar_Plugin', 'boot')) {
            Rdev_Calendar_Plugin::boot();
        } else {
            throw new \RuntimeException('Bootstrap class Rdev_Calendar_Plugin not found after loading files.');
        }
    } catch (\Throwable $e) {
        $rdev_calendar_boot_error = $e->getMessage();
        if (function_exists('error_log')) {
            error_log('[rdev-calendar] Boot error: ' . $rdev_calendar_boot_error);
        }
    }
}

if ($rdev_calendar_boot_error !== '') {
    add_action('admin_notices', static function () use ($rdev_calendar_boot_error) {
        echo '<div class="notice notice-error"><p><strong>rdev-calendar:</strong> ' . esc_html($rdev_calendar_boot_error) . '</p></div>';
    });
}
