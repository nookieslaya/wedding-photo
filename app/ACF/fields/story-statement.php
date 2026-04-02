<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$storyStatement = new FieldsBuilder('story-statement', [
    'label' => 'Story Statement',
]);

$storyStatement
    ->addText('title_line_one', [
        'label' => 'Title line one',
        'required' => 1,
    ])
        ->setWidth(50)
    ->addText('title_line_two', [
        'label' => 'Title line two',
        'required' => 1,
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
        'min' => 3,
    ])
        ->setWidth(100)
        ->addImage('image', [
            'label' => 'Image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'required' => 1,
        ])
        ->endRepeater();

return $storyStatement;
