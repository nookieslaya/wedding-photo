<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$offer = new FieldsBuilder('offer', [
    'label' => 'Offer',
]);

$offer
    ->addText('section_title', [
        'label' => 'Section title',
    ])
        ->setWidth(30)
    ->addText('section_subtitle', [
        'label' => 'Section subtitle',
    ])
        ->setWidth(30)
    ->addText('heading', [
        'label' => 'Heading',
    ])
        ->setWidth(40)
    ->addTextarea('description', [
        'label' => 'Intro description',
        'rows' => 4,
    ])
        ->setWidth(100)
    ->addRepeater('offer_cards', [
        'label' => 'Offer cards',
        'layout' => 'row',
        'button_label' => 'Add offer card',
        'min' => 1,
    ])
        ->addText('label', [
            'label' => 'Small label',
        ])
            ->setWidth(25)
        ->addText('title', [
            'label' => 'Title',
            'required' => 1,
        ])
            ->setWidth(35)
        ->addText('price', [
            'label' => 'Price / from',
            'instructions' => 'Example: od 2900 PLN',
        ])
            ->setWidth(25)
        ->addLink('button', [
            'label' => 'Button link',
            'return_format' => 'array',
        ])
            ->setWidth(15)
        ->addTextarea('description', [
            'label' => 'Description',
            'rows' => 4,
        ])
            ->setWidth(100)
        ->addRepeater('features', [
            'label' => 'Features',
            'layout' => 'table',
            'button_label' => 'Add feature',
        ])
            ->addText('text', [
                'label' => 'Feature',
            ])
        ->endRepeater()
    ->endRepeater();

return $offer;
