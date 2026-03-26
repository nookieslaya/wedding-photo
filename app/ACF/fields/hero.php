<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$hero = new FieldsBuilder('hero', [
    'label' => 'Hero',
]);

$hero
    ->addText('eyebrow', [
        'label' => 'Eyebrow',
    ])
        ->setWidth(20)
    ->addText('heading', [
        'label' => 'Heading',
        'required' => 1,
    ])
        ->setWidth(40)
    ->addTextarea('description', [
        'label' => 'Description',
        'rows' => 4,
    ])
        ->setWidth(40)
    ->addImage('background_image', [
        'label' => 'Background image',
        'return_format' => 'array',
        'preview_size' => 'large',
        'required' => 1,
    ])
        ->setWidth(30)
    ->addImage('mobile_image', [
        'label' => 'Mobile image',
        'instructions' => 'Optional. If empty, Background image will be used on mobile.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])
        ->setWidth(30)
    ->addLink('button', [
        'label' => 'Button',
        'return_format' => 'array',
    ])
        ->setWidth(20)
    ->addTrueFalse('show_scroll_hint', [
        'label' => 'Show scroll hint',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(20);

return $hero;
