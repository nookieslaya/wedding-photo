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
    ->addEmail('booking_notification_email', [
        'label' => 'Booking notification email',
        'instructions' => 'Optional. If empty, admin email will be used.',
    ])
        ->setWidth(50)
    ->addTextarea('booking_hold_notice_text', [
        'label' => 'Booking 48h notice text',
        'instructions' => 'Visible above the booking form. You can use {hours} placeholder.',
        'rows' => 2,
        'default_value' => 'Rezerwacja terminu jest wstępna i trwa {hours}h. Po tym czasie termin wraca do puli wolnych, jeśli nie zostanie potwierdzony.',
    ])
        ->setWidth(100)
    ->addTextarea('booking_hold_note_template', [
        'label' => 'Calendar hold note template',
        'instructions' => 'Used in day note for tentative hold. Placeholders: {hours}, {expires}.',
        'rows' => 2,
        'default_value' => 'Wstępna rezerwacja na {hours}h (do {expires}).',
    ])
        ->setWidth(100)
    ->addTextarea('booking_success_message', [
        'label' => 'Booking success message',
        'rows' => 2,
        'default_value' => 'Dziękuję. Twoje zgłoszenie zostało zapisane. Termin jest zablokowany na 48h.',
    ])
        ->setWidth(100)
    ->addTextarea('booking_error_message', [
        'label' => 'Booking error message',
        'rows' => 2,
        'default_value' => 'Nie udało się wysłać zgłoszenia. Sprawdź dane i spróbuj ponownie.',
    ])
        ->setWidth(100)
    ->addText('booking_form_heading', [
        'label' => 'Booking form heading',
        'default_value' => 'Zarezerwuj termin',
    ])
        ->setWidth(40)
    ->addText('booking_form_submit_label', [
        'label' => 'Booking submit button label',
        'default_value' => 'Wyślij rezerwację',
    ])
        ->setWidth(30)
    ->addText('booking_consent_label', [
        'label' => 'Booking consent label',
        'default_value' => 'Zapoznałam/em się z moim stylem pracy i akceptuję kontakt zwrotny.',
    ])
        ->setWidth(30)
    ->addRepeater('booking_options', [
        'label' => 'Service / Package options',
        'instructions' => 'Options shown in booking form select field.',
        'layout' => 'row',
        'button_label' => 'Add option',
        'min' => 1,
    ])
        ->setWidth(100)
        ->addText('label', [
            'label' => 'Option label',
            'required' => 1,
        ])
            ->setWidth(100)
    ->endRepeater()
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
