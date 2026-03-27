<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$pWeddingPhoto = new FieldsBuilder('p-wedding-photo', [
    'label' => 'P Wedding Photo',
]);

$pWeddingPhoto
    ->addText('heading', [
        'label' => 'Heading',
        'required' => 1,
    ])
        ->setWidth(60)
    ->addTextarea('description', [
        'label' => 'Description',
        'rows' => 3,
    ])
        ->setWidth(40)
    ->addRepeater('photos', [
        'label' => 'Photos',
        'layout' => 'block',
        'button_label' => 'Add photo',
        'min' => 1,
    ])
        ->addImage('image', [
            'label' => 'Image',
            'return_format' => 'array',
            'preview_size' => 'medium',
            'required' => 1,
        ])
            ->setWidth(50)
        ->addText('caption', [
            'label' => 'Caption',
        ])
            ->setWidth(50)
    ->endRepeater();

return $pWeddingPhoto;
