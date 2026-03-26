<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$aboutUs = new FieldsBuilder('about-us', [
    'label' => 'About Us',
]);

$aboutUs
    ->addText('section_title', [
        'label' => 'Section title',
    ])
        ->setWidth(30)
    ->addText('heading', [
        'label' => 'Heading',
    ])
        ->setWidth(30)
    ->addTextarea('description', [
        'label' => 'Description',
        'rows' => 4,
    ])
        ->setWidth(30)
    ->addImage('image', [
        'label' => 'Image',
        'return_format' => 'array',
        'preview_size' => 'large',
        'required' => 1,
    ])
        ->setWidth(30)
    ->addRepeater('stats', [
        'label' => 'Stats',
        'layout' => 'block',
        'button_label' => 'Add stat',
    ])
        ->addText('label', [
            'label' => 'Label',
        ])
        ->addText('value', [
            'label' => 'Value',
        ])
    ->endRepeater()
    ->addLink('button', [
        'label' => 'Button',
        'return_format' => 'array',
    ]);

return $aboutUs;
