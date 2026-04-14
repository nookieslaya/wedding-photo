<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$availabilityCalendar = new FieldsBuilder('availability-calendar', [
    'label' => 'Availability Calendar',
]);

$availabilityCalendar
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
        'label' => 'Description',
        'rows' => 4,
    ])
        ->setWidth(100)
    ->addNumber('months_to_show', [
        'label' => 'Months to show',
        'default_value' => 12,
        'min' => 3,
        'max' => 24,
        'step' => 1,
    ])
        ->setWidth(25)
    ->addNumber('start_month_offset', [
        'label' => 'Start month offset',
        'instructions' => '0 = current month, 1 = next month, -1 = previous month.',
        'default_value' => 0,
        'min' => -12,
        'max' => 12,
        'step' => 1,
    ])
        ->setWidth(25)
    ->addLink('cta_button', [
        'label' => 'CTA button',
        'return_format' => 'array',
    ])
        ->setWidth(50)
    ->addTextarea('calendar_status_map', [
        'label' => 'Calendar status map',
        'instructions' => 'Managed by the visual calendar tool below. Leave empty if not used.',
        'rows' => 3,
        'default_value' => '{}',
        'wrapper' => [
            'class' => 'availability-map-storage',
        ],
    ])
        ->setWidth(100)
    ->addMessage(
        'Visual calendar manager',
        '<div class="availability-admin-manager" data-availability-admin-manager><div class="availability-admin-manager__mount"></div></div>',
        [
            'name' => 'calendar_visual_manager',
            'new_lines' => 'none',
            'esc_html' => 0,
        ],
    )
        ->setWidth(100)
    ->addRepeater('date_ranges', [
        'label' => 'Date ranges',
        'instructions' => 'Optional advanced ranges. Visual manager values have priority on frontend.',
        'layout' => 'row',
        'button_label' => 'Add date range',
    ])
        ->addDatePicker('start_date', [
            'label' => 'Start date',
            'display_format' => 'Y-m-d',
            'return_format' => 'Y-m-d',
            'first_day' => 1,
            'required' => 1,
        ])
            ->setWidth(25)
        ->addDatePicker('end_date', [
            'label' => 'End date',
            'display_format' => 'Y-m-d',
            'return_format' => 'Y-m-d',
            'first_day' => 1,
            'required' => 1,
        ])
            ->setWidth(25)
        ->addSelect('status', [
            'label' => 'Status',
            'choices' => [
                'available' => 'Dostępny',
                'tentative' => 'Wstępna rezerwacja',
                'booked' => 'Zajęty',
            ],
            'default_value' => 'available',
            'allow_null' => 0,
            'ui' => 1,
            'required' => 1,
        ])
            ->setWidth(25)
        ->addText('note', [
            'label' => 'Note',
            'instructions' => 'Optional. Example: only short sessions available.',
        ])
            ->setWidth(25)
    ->endRepeater();

return $availabilityCalendar;
