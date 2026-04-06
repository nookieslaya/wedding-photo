<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$eventsHero = new FieldsBuilder('events-hero', [
    'label' => 'EVENTS • Hero',
]);

$eventsHero
    ->addMessage('events_hero_note', 'Leave fields empty to use data from the current Event post.')
        ->setWidth(100)
    ->addText('back_link_label', [
        'label' => 'Back link label',
        'default_value' => 'Back to Topics',
    ])
        ->setWidth(35)
    ->addLink('back_link', [
        'label' => 'Back link URL',
        'instructions' => 'Optional. If empty, Event archive URL will be used.',
        'return_format' => 'array',
    ])
        ->setWidth(65)
    ->addTextarea('override_title', [
        'label' => 'Override title',
        'instructions' => 'Optional. If empty, Event post title will be used.',
        'rows' => 3,
    ])
        ->setWidth(100)
    ->addText('override_date', [
        'label' => 'Override date',
        'instructions' => 'Optional. Example: 2026.03.27',
    ])
        ->setWidth(34)
    ->addText('override_badge', [
        'label' => 'Override badge',
        'instructions' => 'Optional. Example: Event',
        'default_value' => 'Event',
    ])
        ->setWidth(33)
    ->addText('override_category', [
        'label' => 'Override category label',
        'instructions' => 'Optional. If empty, first Event category will be used.',
    ])
        ->setWidth(33)
    ->addImage('hero_image', [
        'label' => 'Override hero image',
        'instructions' => 'Optional. If empty, featured image from Event post will be used.',
        'return_format' => 'array',
        'preview_size' => 'large',
    ])
        ->setWidth(70)
    ->addTrueFalse('show_top_meta', [
        'label' => 'Show top meta row',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(30);

return $eventsHero;
