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
        ->setWidth(50)
    ->addImage('bottom_gif', [
        'label' => 'Bottom GIF',
        'instructions' => 'Upload animated GIF image for the bottom visual.',
        'return_format' => 'array',
        'preview_size' => 'medium',
    ])
        ->setWidth(50);

return $storyStatement;
