<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$acfLocale = strtolower((string) determine_locale());
$acfIsPolish = str_starts_with($acfLocale, 'pl');
$acfTr = static fn (string $en, string $pl): string => $acfIsPolish ? $pl : $en;

$eventsList = new FieldsBuilder('events-list', [
    'label' => 'Events List',
]);

$eventsList
    ->addText('section_title', [
        'label' => $acfTr('Section title', 'Tytuł sekcji'),
        'default_value' => 'TOPICS',
    ])
        ->setWidth(50)
    ->addText('section_subtitle', [
        'label' => $acfTr('Section subtitle', 'Podtytuł sekcji'),
        'default_value' => '(ALL SECTION)',
    ])
        ->setWidth(50)
    ->addText('view_more_label', [
        'label' => 'View more label',
        'default_value' => 'VIEW MORE',
    ])
        ->setWidth(50);

return $eventsList;
