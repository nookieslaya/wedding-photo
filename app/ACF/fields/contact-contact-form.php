<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$contactContactForm = new FieldsBuilder('contact-contact-form', [
    'label' => 'Contact • Contact Form',
]);

$contactContactForm
    ->addText('sidebar_title', [
        'label' => 'Sidebar title',
        'default_value' => 'CONTACT',
    ])
        ->setWidth(30)
    ->addText('sidebar_subtitle', [
        'label' => 'Sidebar subtitle',
        'default_value' => 'Skontaktujmy sie',
    ])
        ->setWidth(30)
    ->addText('sidebar_steps', [
        'label' => 'Sidebar steps line',
        'default_value' => '[Wyslij]  -  [Odpowiedz]  -  [Start]',
    ])
        ->setWidth(40)
    ->addRepeater('sidebar_address_lines', [
        'label' => 'Sidebar address lines',
        'layout' => 'row',
        'button_label' => 'Add line',
    ])
        ->addText('line', [
            'label' => 'Line',
            'required' => 1,
        ])
    ->endRepeater()
    ->addTextarea('sidebar_note', [
        'label' => 'Sidebar note',
        'rows' => 3,
        'default_value' => 'Wypelnij formularz, a odpowiem najszybciej jak to mozliwe.',
    ])
        ->setWidth(100)
    ->addText('form_shortcode', [
        'label' => 'Form shortcode',
        'instructions' => 'Optional. If provided, this shortcode will be rendered instead of the demo form.',
    ])
        ->setWidth(100);

return $contactContactForm;
