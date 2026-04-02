<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$eventsList = new FieldsBuilder('events-list', [
    'label' => 'Events List',
]);

$eventsList
    ->addText('section_title', [
        'label' => 'Section title',
        'default_value' => 'TOPICS',
    ])
        ->setWidth(50)
    ->addText('section_subtitle', [
        'label' => 'Section subtitle',
        'default_value' => '(ALL SECTION)',
    ])
        ->setWidth(50)
    ->addText('view_more_label', [
        'label' => 'View more label',
        'default_value' => 'VIEW MORE',
    ])
        ->setWidth(50);

return $eventsList;
