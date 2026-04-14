<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$products = new FieldsBuilder('products', [
    'label' => 'Products',
]);

$products
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
        ->setWidth(70)
    ->addImage('main_image', [
        'label' => 'Main image',
        'instructions' => 'Optional image displayed in left column.',
        'return_format' => 'array',
        'preview_size' => 'large',
    ])
        ->setWidth(30)
    ->addRepeater('product_cards', [
        'label' => 'Product cards',
        'layout' => 'row',
        'button_label' => 'Add product card',
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
            'instructions' => 'Example: od 390 PLN',
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
            ->setWidth(75)
        ->addImage('image', [
            'label' => 'Card image',
            'return_format' => 'array',
            'preview_size' => 'medium',
        ])
            ->setWidth(25)
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

return $products;
