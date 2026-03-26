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

        $pageModules->setLocation('post_type', '==', 'page');

        acf_add_local_field_group($pageModules->build());
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
