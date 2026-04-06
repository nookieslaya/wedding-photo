<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$eventsSidebarContent = new FieldsBuilder('events-sidebar-content', [
    'label' => 'EVENTS • Sidebar Content',
]);

$eventsSidebarContent
    ->addText('back_link_label', [
        'label' => 'Back link label',
        'default_value' => 'Back to Topics',
    ])
        ->setWidth(40)
    ->addLink('back_link', [
        'label' => 'Back link URL',
        'instructions' => 'Optional. If empty, Event archive URL will be used.',
        'return_format' => 'array',
    ])
        ->setWidth(60)
    ->addWysiwyg('content', [
        'label' => 'Content',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 0,
        'required' => 1,
    ])
        ->setWidth(100);

return $eventsSidebarContent;
