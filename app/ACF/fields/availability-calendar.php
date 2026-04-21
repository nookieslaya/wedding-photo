<?php

namespace App;

use StoutLogic\AcfBuilder\FieldsBuilder;

$acfLocale = strtolower((string) determine_locale());
$acfIsPolish = str_starts_with($acfLocale, 'pl');
$acfTr = static fn (string $en, string $pl): string => $acfIsPolish ? $pl : $en;

$availabilityCalendar = new FieldsBuilder('availability-calendar', [
    'label' => 'Availability Calendar',
]);

$availabilityCalendar
    ->addText('section_title', [
        'label' => $acfTr('Section title', 'Tytuł sekcji'),
    ])
        ->setWidth(30)
    ->addText('section_subtitle', [
        'label' => $acfTr('Section subtitle', 'Podtytuł sekcji'),
    ])
        ->setWidth(30)
    ->addSelect('theme_preset', [
        'label' => 'Theme preset',
        'choices' => [
            'dark' => 'Dark',
            'graphite' => 'Graphite',
            'smoke' => 'Smoke',
        ],
        'default_value' => 'dark',
        'allow_null' => 0,
        'ui' => 1,
    ])
        ->setWidth(20)
    ->addSelect('background_style', [
        'label' => 'Background style',
        'choices' => [
            'gradient' => 'Gradient',
            'plain' => 'Plain',
            'mesh' => 'Mesh',
        ],
        'default_value' => 'gradient',
        'allow_null' => 0,
        'ui' => 1,
    ])
        ->setWidth(20)
    ->addSelect('font_preset', [
        'label' => 'Font preset',
        'choices' => [
            'modern' => 'Modern',
            'editorial' => 'Editorial',
            'mono' => 'Mono',
        ],
        'default_value' => 'modern',
        'allow_null' => 0,
        'ui' => 1,
    ])
        ->setWidth(20)
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
    ->addEmail('booking_from_email', [
        'label' => 'Booking "From" email',
        'instructions' => 'Optional sender email for booking messages.',
    ])
        ->setWidth(25)
    ->addText('booking_from_name', [
        'label' => 'Booking "From" name',
        'instructions' => 'Optional sender name for booking messages.',
    ])
        ->setWidth(25)
    ->addNumber('booking_hold_minutes', [
        'label' => 'Booking hold duration (minutes)',
        'instructions' => 'How long tentative reservation should be held before auto-release.',
        'default_value' => 2880,
        'min' => 1,
        'max' => 10080,
        'step' => 1,
    ])
        ->setWidth(50)
    ->addTextarea('booking_default_time_slots', [
        'label' => 'Default available hours',
        'instructions' => 'One time per line in HH:MM format, e.g. 10:00',
        'rows' => 5,
        'default_value' => "10:00\n12:00\n14:00\n16:00",
    ])
        ->setWidth(50)
    ->addTextarea('booking_hold_notice_text', [
        'label' => 'Booking hold notice text',
        'instructions' => 'Visible above the booking form. You can use {hours} and {minutes} placeholders.',
        'rows' => 2,
        'default_value' => 'Rezerwacja terminu jest wstępna i trwa {hours}h ({minutes} min). Po tym czasie termin wraca do puli wolnych, jeśli nie zostanie potwierdzony.',
    ])
        ->setWidth(100)
    ->addTextarea('booking_hold_note_template', [
        'label' => 'Calendar hold note template',
        'instructions' => 'Used in day note for tentative hold. Placeholders: {hours}, {minutes}, {expires}.',
        'rows' => 2,
        'default_value' => 'Wstępna rezerwacja na {hours}h ({minutes} min), do {expires}.',
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
    ->addTrueFalse('booking_send_initial_email', [
        'label' => 'Send client email after booking',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(50)
    ->addTrueFalse('booking_send_expired_email', [
        'label' => 'Send client email when hold expires',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(50)
    ->addTrueFalse('booking_send_approved_email', [
        'label' => 'Send client email when booking is approved',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(50)
    ->addTrueFalse('booking_send_rejected_email', [
        'label' => 'Send client email when booking is rejected',
        'default_value' => 1,
        'ui' => 1,
    ])
        ->setWidth(50)
    ->addText('booking_client_initial_email_subject', [
        'label' => 'Client email subject (after booking)',
        'default_value' => 'Potwierdzenie wstępnej rezerwacji terminu',
    ])
        ->setWidth(50)
    ->addTextarea('booking_client_initial_email_body', [
        'label' => 'Client email body (after booking)',
        'instructions' => 'Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}.',
        'rows' => 6,
        'default_value' => "Dziękuję za zapytanie.\n\nTwój termin został wstępnie zablokowany na {hours}h ({minutes} min).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nHold do: {expires}\n\nSkontaktuję się z Tobą, aby potwierdzić szczegóły.\n\n{site_name}",
    ])
        ->setWidth(50)
    ->addText('booking_client_expired_email_subject', [
        'label' => 'Client email subject (hold expired)',
        'default_value' => 'Wstępna rezerwacja wygasła',
    ])
        ->setWidth(50)
    ->addTextarea('booking_client_expired_email_body', [
        'label' => 'Client email body (hold expired)',
        'instructions' => 'Placeholders: {full_name}, {date}, {time}, {option}, {hours}, {minutes}, {expires}, {site_name}.',
        'rows' => 6,
        'default_value' => "Cześć {full_name},\n\nWstępna rezerwacja terminu wygasła (brak potwierdzenia).\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nCzas holda: {hours}h ({minutes} min)\nWygasła: {expires}\n\nJeśli termin jest nadal aktualny, wyślij nowe zapytanie.\n\n{site_name}",
    ])
        ->setWidth(50)
    ->addText('booking_client_approved_email_subject', [
        'label' => 'Client email subject (approved)',
        'default_value' => 'Rezerwacja terminu została potwierdzona',
    ])
        ->setWidth(50)
    ->addTextarea('booking_client_approved_email_body', [
        'label' => 'Client email body (approved)',
        'instructions' => 'Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}.',
        'rows' => 6,
        'default_value' => "Cześć {full_name},\n\nTwoja rezerwacja została potwierdzona.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nW razie pytań odpowiedz na tę wiadomość.\n\n{site_name}",
    ])
        ->setWidth(50)
    ->addText('booking_client_rejected_email_subject', [
        'label' => 'Client email subject (rejected)',
        'default_value' => 'Rezerwacja terminu nie została potwierdzona',
    ])
        ->setWidth(50)
    ->addTextarea('booking_client_rejected_email_body', [
        'label' => 'Client email body (rejected)',
        'instructions' => 'Placeholders: {full_name}, {date}, {time}, {option}, {status}, {site_name}.',
        'rows' => 6,
        'default_value' => "Cześć {full_name},\n\nNiestety nie mogliśmy potwierdzić rezerwacji tego terminu.\nData: {date}\nGodzina: {time}\nUsługa / Pakiet: {option}\nStatus: {status}\n\nMożesz wybrać inny dostępny termin i wysłać nowe zapytanie.\n\n{site_name}",
    ])
        ->setWidth(50)
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
    ->addTextarea('calendar_time_slots_overrides', [
        'label' => 'Calendar time slots overrides',
        'instructions' => 'Managed by the visual calendar tool below.',
        'rows' => 3,
        'default_value' => '{}',
        'wrapper' => [
            'class' => 'availability-time-overrides-storage',
        ],
    ])
        ->setWidth(100)
    ->addTextarea('calendar_time_slots_reservations', [
        'label' => 'Calendar time slots reservations',
        'instructions' => 'Managed automatically by booking flow.',
        'rows' => 3,
        'default_value' => '{}',
        'wrapper' => [
            'class' => 'availability-time-reservations-storage',
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
