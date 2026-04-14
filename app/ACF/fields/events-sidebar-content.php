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
    ->addFlexibleContent('content_sections', [
        'label' => 'Content sections',
        'button_label' => 'Add section',
        'instructions' => 'Add sections in any order, e.g. Content -> Gallery -> Content.',
    ])
        ->addLayout('content_block', [
            'label' => 'Content block',
        ])
            ->addWysiwyg('content', [
                'label' => 'Content',
                'tabs' => 'all',
                'toolbar' => 'full',
                'media_upload' => 0,
                'required' => 1,
            ])
        ->addLayout('gallery_block', [
            'label' => 'Gallery block',
        ])
            ->addText('title', [
                'label' => 'Gallery title',
            ])
                ->setWidth(70)
            ->addSelect('layout_style', [
                'label' => 'Gallery layout',
                'default_value' => 'masonry',
                'choices' => [
                    'equal' => 'Equal grid',
                    'masonry' => 'Adaptive masonry',
                    'mixed' => 'Universal mixed',
                ],
            ])
                ->setWidth(30)
            ->addRepeater('images', [
                'label' => 'Images',
                'layout' => 'row',
                'button_label' => 'Add image',
                'min' => 1,
            ])
                ->addImage('image', [
                    'label' => 'Image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'required' => 1,
                ])
                    ->setWidth(70)
                ->addText('caption', [
                    'label' => 'Caption',
                ])
                    ->setWidth(30)
            ->endRepeater()
    ->endFlexibleContent()
    ->addWysiwyg('content', [
        'label' => 'Legacy content (fallback)',
        'instructions' => 'Optional. Used only when Content sections are empty.',
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 0,
    ])
        ->setWidth(100);

return $eventsSidebarContent;
