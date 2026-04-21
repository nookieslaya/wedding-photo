<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$acfLocale = strtolower((string) determine_locale());
$acfIsPolish = str_starts_with($acfLocale, 'pl');
$acfTr = static fn (string $en, string $pl): string => $acfIsPolish ? $pl : $en;

$aboutUs = new FieldsBuilder('about-us', [
    'label' => 'About Us',
]);

$aboutUs
    ->addText('section_title', [
        'label' => $acfTr('Section title', 'Tytuł sekcji'),
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
