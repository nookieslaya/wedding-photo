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
