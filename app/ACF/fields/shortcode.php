<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$shortcode = new FieldsBuilder('shortcode', [
    'label' => 'Shortcode',
]);

$shortcode
    ->addText('section_title', [
        'label' => 'Section title',
    ])
        ->setWidth(40)
    ->addText('section_subtitle', [
        'label' => 'Section subtitle',
    ])
        ->setWidth(40)
    ->addTrueFalse('full_width', [
        'label' => 'Full width',
        'instructions' => 'Enable to render shortcode container across full viewport width.',
        'default_value' => 0,
        'ui' => 1,
    ])
        ->setWidth(20)
    ->addTextarea('shortcode', [
        'label' => 'Shortcode',
        'instructions' => 'Example: [rdev_calendar id="123"]',
        'required' => 1,
        'rows' => 3,
    ])
        ->setWidth(100);

return $shortcode;

