<?php
/**
 * Default settings, merged under the option key `pickup_settings`.
 *
 * Schema:
 *  - enabled:        master switch for the checkout pickup scheduler.
 *  - slot_minutes:   length of each bookable slot, in minutes.
 *  - capacity:       how many orders may book the same location + slot.
 *  - lead_hours:     minimum notice (hours) before the earliest bookable slot.
 *  - horizon_days:   how many days ahead the date picker exposes.
 *  - windows:        weekly opening windows keyed by ISO weekday (1=Mon..7=Sun),
 *                    each a list of {start,end} "HH:MM" strings.
 *  - locations:      list of {id,name,address,enabled} pickup points.
 *
 * @package Pickup
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    'enabled'      => true,
    'slot_minutes' => 30,
    'capacity'     => 5,
    'lead_hours'   => 2,
    'horizon_days' => 14,
    'windows'      => [
        1 => [['start' => '09:00', 'end' => '17:00']],
        2 => [['start' => '09:00', 'end' => '17:00']],
        3 => [['start' => '09:00', 'end' => '17:00']],
        4 => [['start' => '09:00', 'end' => '17:00']],
        5 => [['start' => '09:00', 'end' => '17:00']],
        6 => [],
        7 => [],
    ],
    'locations'    => [
        [
            'id'      => 'main',
            // Blank on purpose: a literal here can never reach the .pot, so it
            // would print English at checkout in every shop that has not yet
            // renamed this seed row. SettingsStore::locations() supplies the
            // translated name when it is empty.
            'name'    => '',
            'address' => '',
            'enabled' => true,
        ],
    ],
];
