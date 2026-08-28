<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package plogins-pickup-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Pickup Pro',
    'url'        => 'https://plogins.com/plogins-pickup-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'lead'       => [
        'en' => 'Per-location capacity, blackouts, slot pricing and calendar export ship in the 0.4.0 release.',
        'pl' => 'Pojemność per lokalizacja, blackouty, ceny za slot i eksport do kalendarza są dostępne w wydaniu 0.4.0.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Per-location capacity', 'desc' => 'Override the global per-slot limit for individual pickup locations.'],
            'pl' => ['title' => 'Pojemność per lokalizacja', 'desc' => 'Nadpisz globalny limit slotów dla wybranych punktów odbioru.'],
        ],
        [
            'en' => ['title' => 'Blackout dates', 'desc' => 'Block holidays and one-off closures so slots are never offered.'],
            'pl' => ['title' => 'Dni wyłączone', 'desc' => 'Blokuj święta i jednorazowe zamknięcia, aby sloty nie były oferowane.'],
        ],
        [
            'en' => ['title' => 'Slot blackouts', 'desc' => 'Disable specific time windows on a date, optionally for one location only (shipped).'],
            'pl' => ['title' => 'Blackouty slotów', 'desc' => 'Wyłącz konkretne przedziały czasowe w wybranym dniu lub dla jednej lokalizacji (wdrożone).'],
        ],
        [
            'en' => ['title' => 'Per-slot pricing', 'desc' => 'Charge a fee or offer a discount for specific pickup slots (shipped).'],
            'pl' => ['title' => 'Cena za slot', 'desc' => 'Nalicz opłatę lub rabat za wybrane terminy odbioru (wdrożone).'],
        ],
        [
            'en' => ['title' => 'Calendar export', 'desc' => 'Download booked pickups as an iCalendar (.ics) file per location (shipped).'],
            'pl' => ['title' => 'Eksport do kalendarza', 'desc' => 'Pobierz zarezerwowane odbiory jako plik iCalendar (.ics) per lokalizacja (wdrożone).'],
        ],
    ],
];
