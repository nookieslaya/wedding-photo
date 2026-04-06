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
    if (! get_current_screen()?->is_block_editor()) {
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
