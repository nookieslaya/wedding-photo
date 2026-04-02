<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class FlexibleModules extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'single',
        'page',
        'front-page',
        'single-event',
    ];

    /**
     * Provide flexible content rows for module rendering.
     */
    public function flexibleModules(): array
    {
        if (! function_exists('get_field')) {
            return [];
        }

        $modules = get_field('flexible_modules');

        return is_array($modules) ? $modules : [];
    }
}
