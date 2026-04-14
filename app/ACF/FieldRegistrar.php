<?php

namespace App\ACF;

use StoutLogic\AcfBuilder\FieldsBuilder;

class FieldRegistrar
{
    public function register(): void
    {
        add_action('acf/init', [$this, 'registerFlexibleModules']);
    }

    public function registerFlexibleModules(): void
    {
        if (! function_exists('acf_add_local_field_group') || ! class_exists(FieldsBuilder::class)) {
            return;
        }

        $modules = $this->loadModuleLayouts();

        if ($modules === []) {
            return;
        }

        $pageModules = new FieldsBuilder('page-modules', [
            'title' => 'Page Modules',
        ]);

        $flexibleModules = $pageModules->addFlexibleContent('flexible_modules', [
            'label' => 'Flexible modules',
            'button_label' => 'Add module',
        ]);

        foreach ($modules as $module) {
            $flexibleModules->addLayout($module);
        }

        $flexibleModules
            ->endFlexibleContent();

        $pageModules
            ->setLocation('post_type', '==', 'page')
            ->or('post_type', '==', 'event');

        acf_add_local_field_group($pageModules->build());

        $globalSocialMedia = new FieldsBuilder('global-social-media', [
            'title' => 'Global Social Media',
        ]);

        $globalSocialMedia
            ->addTab('social_media_tab', [
                'label' => 'Social Media',
            ])
            ->addText('share_us_on_label', [
                'label' => 'Share title',
                'default_value' => 'Share us on',
            ])
                ->setWidth(50)
            ->addText('share_link_label', [
                'label' => 'Share link label',
                'default_value' => 'Share',
            ])
                ->setWidth(50)
            ->addRepeater('social_links', [
                'label' => 'Social links',
                'layout' => 'row',
                'button_label' => 'Add social link',
                'min' => 1,
            ])
                ->addText('title', [
                    'label' => 'Title',
                    'required' => 1,
                ])
                    ->setWidth(25)
                ->addImage('icon', [
                    'label' => 'Icon',
                    'return_format' => 'array',
                    'preview_size' => 'thumbnail',
                    'required' => 1,
                ])
                    ->setWidth(30)
                ->addLink('url', [
                    'label' => 'URL',
                    'return_format' => 'array',
                    'required' => 1,
                ])
                    ->setWidth(45)
            ->endRepeater()
            ->setLocation('options_page', '==', 'global-settings-social-media');

        acf_add_local_field_group($globalSocialMedia->build());

        $globalNavigation = new FieldsBuilder('global-navigation', [
            'title' => 'Global Navigation',
        ]);

        $globalNavigation
            ->addTab('navigation_tab', [
                'label' => 'Navigation & Footer',
            ])
            ->addImage('header_brand_logo', [
                'label' => 'Header logo',
                'instructions' => 'Optional. Logo used in top navbar (desktop + mobile). If empty, header display name will be used.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
                ->setWidth(50)
            ->addText('header_brand_text', [
                'label' => 'Header display name',
                'default_value' => get_bloginfo('name'),
            ])
                ->setWidth(50)
            ->addImage('mobile_menu_header_logo', [
                'label' => 'Mobile menu header logo/icon',
                'instructions' => 'Optional. Shown at the top-left inside mobile menu panel.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
                ->setWidth(50)
            ->addText('mobile_menu_header_text', [
                'label' => 'Mobile menu header text',
                'instructions' => 'Used when mobile menu header logo/icon is empty.',
                'default_value' => get_bloginfo('name'),
            ])
                ->setWidth(50)
            ->addImage('footer_brand_logo', [
                'label' => 'Footer logo',
                'instructions' => 'Optional. Logo used in footer. If empty, footer display name will be used.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
                ->setWidth(50)
            ->addText('footer_brand_text', [
                'label' => 'Footer display name',
                'default_value' => get_bloginfo('name'),
            ])
                ->setWidth(50)
            ->addText('footer_meta_text', [
                'label' => 'Footer meta text',
                'instructions' => 'You can use {year} placeholder. Example: Animated · {year}',
                'default_value' => get_bloginfo('name') . ' · {year}',
            ])
                ->setWidth(100)
            ->addImage('footer_background_image', [
                'label' => 'Footer background image',
                'instructions' => 'Optional. Background image shown under dark overlay.',
                'return_format' => 'array',
                'preview_size' => 'large',
            ])
                ->setWidth(50)
            ->addNumber('footer_overlay_opacity', [
                'label' => 'Footer overlay opacity (%)',
                'instructions' => '0 = transparent, 100 = fully black',
                'default_value' => 78,
                'min' => 0,
                'max' => 100,
                'step' => 1,
            ])
                ->setWidth(50)
            ->addText('footer_large_text', [
                'label' => 'Footer large bottom text',
                'default_value' => strtoupper(get_bloginfo('name')),
            ])
                ->setWidth(50)
            ->addTrueFalse('footer_show_cards', [
                'label' => 'Show footer cards column',
                'instructions' => 'Disable to hide left footer cards and keep only menu + social links.',
                'default_value' => 1,
                'ui' => 1,
            ])
                ->setWidth(50)
            ->addLink('footer_contact_link', [
                'label' => 'Footer contact card link',
                'return_format' => 'array',
            ])
                ->setWidth(50)
            ->addTextarea('footer_contact_description', [
                'label' => 'Footer contact card description',
                'rows' => 3,
            ])
                ->setWidth(50)
            ->addText('footer_phone', [
                'label' => 'Footer phone',
            ])
                ->setWidth(50)
            ->addText('footer_phone_hours', [
                'label' => 'Footer phone hours',
            ])
                ->setWidth(50)
            ->addLink('footer_linkedin_link', [
                'label' => 'Footer media card link',
                'return_format' => 'array',
            ])
                ->setWidth(40)
            ->addText('footer_linkedin_label', [
                'label' => 'Footer media card label',
                'default_value' => 'Media',
            ])
                ->setWidth(30)
            ->addImage('footer_linkedin_image', [
                'label' => 'Footer media card image',
                'instructions' => 'Optional. If empty, rectangular card with label will be shown.',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])
                ->setWidth(30)
            ->setLocation('options_page', '==', 'global-settings')
            ->or('options_page', '==', 'global-settings-social-media');

        acf_add_local_field_group($globalNavigation->build());
    }

    /**
     * @return array<int, FieldsBuilder>
     */
    private function loadModuleLayouts(): array
    {
        $files = glob(get_theme_file_path('app/ACF/fields/*.php')) ?: [];
        $layouts = [];

        foreach ($files as $file) {
            $layout = require $file;

            if ($layout instanceof FieldsBuilder) {
                $layouts[] = $layout;
            }
        }

        return $layouts;
    }
}
