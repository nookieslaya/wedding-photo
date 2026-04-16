<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$storyStatement = new FieldsBuilder('story-statement', [
    'label' => 'Story Statement',
]);

$storyStatement
    ->addText('title_line_one', [
        'label' => 'Title line one',
    ])
        ->setWidth(50)
    ->addText('title_line_two', [
        'label' => 'Title line two',
    ])
        ->setWidth(50)
    ->addNumber('title_font_size_mobile', [
        'label' => 'Title font size (mobile, rem)',
        'instructions' => 'Optional. Example: 2.2',
        'step' => 0.1,
        'min' => 1,
        'max' => 12,
    ])
        ->setWidth(50)
    ->addNumber('title_font_size_desktop', [
        'label' => 'Title font size (desktop, rem)',
        'instructions' => 'Optional. Example: 8',
        'step' => 0.1,
        'min' => 1,
        'max' => 20,
    ])
        ->setWidth(50)
    ->addTextarea('description', [
        'label' => 'Description',
        'rows' => 4,
    ])
        ->setWidth(100)
    ->addRepeater('gallery', [
        'label' => 'Carousel gallery',
        'instructions' => 'Add images for the story carousel (desktop: 3 visible).',
        'layout' => 'row',
        'button_label' => 'Add image',
        'min' => 0,
    ])
        ->setWidth(100)
        ->addImage('image', [
            'label' => 'Image',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])
        ->endRepeater();

return $storyStatement;
