<?php

declare(strict_types=1);

namespace Pickup\Service;

use Pickup\Support\SettingsStore;

defined('ABSPATH') || exit;

/**
 * Turns the admin-defined weekly opening windows + slot length + lead time +
 * horizon into a concrete list of bookable {date => [slots]} entries, and
 * validates a chosen (date, slot) against that grid plus per-slot capacity.
 *
 * Pure, store-timezone-aware date maths; capacity is checked against existing
 * order meta so no custom table is required for the MVP.
 */
final class SlotCalculator
{
    private const META_DATE     = '_pickup_date';
    private const META_SLOT     = '_pickup_slot';
    private const META_LOCATION = '_pickup_location';

    /** @var array<string, array<string, int>> Booked counts per location, keyed "date|slot". */
    private array $counts = [];

    public function __construct(private readonly SettingsStore $settings)
    {
    }

    /**
     * Build the bookable schedule: an ordered map of date (Y-m-d) to the list of
     * still-bookable slot start times ("HH:MM") for a given location.
     *
     * @return array<string, array<int, string>>
     */
    public function schedule(string $locationId): array
    {
        $windows  = $this->settings->windows();
        $minutes  = $this->settings->slotMinutes();
        $horizon  = $this->settings->horizonDays();
        $earliest = $this->earliestBookableTimestamp();

        $tz       = wp_timezone();
        $now      = new \DateTimeImmutable('now', $tz);
        $schedule = [];

        for ($offset = 0; $offset <= $horizon; $offset++) {
            $day     = $now->modify(sprintf('+%d days', $offset));
            $weekday = (int) $day->format('N');
            $dateKey = $day->format('Y-m-d');

            /**
             * Filter whether a calendar date is bookable for the location.
             *
             * @param bool   $available  Whether the date is available.
             * @param string $locationId Pickup location id.
             * @param string $dateKey    Date in Y-m-d format.
             */
            if (! apply_filters('pickup/date_available', true, $locationId, $dateKey)) {
                continue;
            }

            foreach ($windows[$weekday] ?? [] as $window) {
                foreach ($this->slotsInWindow($dateKey, $window, $minutes, $tz) as $slot) {
                    if ($slot['ts'] < $earliest) {
                        continue;
                    }

                    /**
                     * Filter whether an individual slot is bookable.
                     *
                     * @param bool   $available  Whether the slot is available.
                     * @param string $locationId Pickup location id.
                     * @param string $dateKey    Date in Y-m-d format.
                     * @param string $slotLabel  Slot start time (HH:MM).
                     */
                    if (! apply_filters('pickup/slot_available', true, $locationId, $dateKey, $slot['label'])) {
                        continue;
                    }

                    if ($this->isFull($locationId, $dateKey, $slot['label'])) {
                        continue;
                    }
                    $schedule[$dateKey][] = $slot['label'];
                }
            }

            if (isset($schedule[$dateKey])) {
                $schedule[$dateKey] = array_values(array_unique($schedule[$dateKey]));
                sort($schedule[$dateKey]);
            }
        }

        return $schedule;
    }

    /**
     * Optional fee (or discount when negative) for a bookable slot.
     */
    public function slotFee(string $locationId, string $dateKey, string $slotLabel): float
    {
        /**
         * Filter the cart fee for a pickup slot.
         *
         * @param float  $fee        Default fee (0 = no charge).
         * @param string $locationId Pickup location id.
         * @param string $dateKey    Date in Y-m-d format.
         * @param string $slotLabel  Slot start time (HH:MM).
         */
        return (float) apply_filters('pickup/slot_fee', 0.0, $locationId, $dateKey, $slotLabel);
    }

    /**
     * Validate a chosen date + slot for a location. Returns true only when the
     * slot exists in the live schedule (which already excludes past, lead-time
     * and full slots).
     */
    public function isBookable(string $locationId, string $date, string $slot): bool
    {
        $schedule = $this->schedule($locationId);

        return in_array($slot, $schedule[$date] ?? [], true);
    }

    /**
     * Count orders already booked into a location + date + slot, then compare to
     * the configured capacity.
     */
    private function isFull(string $locationId, string $date, string $slot): bool
    {
        $default = max(1, $this->settings->capacity());

        /**
         * Filter the per-slot capacity for a location, date and time.
         *
         * @param int    $capacity   Store-wide default capacity.
         * @param string $locationId Pickup location id.
         * @param string $date       Date in Y-m-d format.
         * @param string $slot       Slot start time (HH:MM).
         */
        $capacity = (int) apply_filters('pickup/slot_capacity', $default, $locationId, $date, $slot);
        $capacity = max(1, $capacity);

        return $this->bookedCount($locationId, $date, $slot) >= $capacity;
    }

    /**
     * How many existing orders hold this exact location + date + slot.
     *
     * Dates before today are not counted: the schedule never offers them, and
     * leaving them out keeps the lookup from growing with the order history.
     */
    public function bookedCount(string $locationId, string $date, string $slot): int
    {
        return $this->bookedCounts($locationId)[$date . '|' . $slot] ?? 0;
    }

    /**
     * Booked order counts for one location, keyed "date|slot", for today onwards.
     *
     * One grouped query per location per request, kept in memory afterwards. The
     * grid asks about every slot across the whole horizon and is rebuilt on every
     * cart total recalculation, so a query per slot would be hundreds of queries
     * on a single checkout page load.
     *
     * @return array<string, int>
     */
    private function bookedCounts(string $locationId): array
    {
        if (isset($this->counts[$locationId])) {
            return $this->counts[$locationId];
        }

        global $wpdb;

        $hpos = class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)
            && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

        $metaTable  = $hpos ? $wpdb->prefix . 'wc_orders_meta' : $wpdb->postmeta;
        $orderTable = $hpos ? $wpdb->prefix . 'wc_orders' : $wpdb->posts;
        $metaIdCol  = $hpos ? 'order_id' : 'post_id';
        $orderIdCol = $hpos ? 'id' : 'ID';
        $statusCol  = $hpos ? 'status' : 'post_status';
        $typeCol    = $hpos ? 'type' : 'post_type';

        $today = (new \DateTimeImmutable('now', wp_timezone()))->format('Y-m-d');

        // %i is an identifier placeholder, so the table and column names that differ
        // between HPOS and the legacy tables go through prepare() like the values do.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- no core API returns counts grouped by two meta keys; the result is held for the request.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT d.meta_value AS pickup_date, s.meta_value AS pickup_slot, COUNT(DISTINCT d.%i) AS booked
                   FROM %i AS d
                   INNER JOIN %i AS s ON s.%i = d.%i AND s.meta_key = %s
                   INNER JOIN %i AS l ON l.%i = d.%i AND l.meta_key = %s AND l.meta_value = %s
                   INNER JOIN %i AS o ON o.%i = d.%i AND o.%i = %s AND o.%i IN (%s, %s, %s, %s)
                  WHERE d.meta_key = %s AND d.meta_value >= %s
                  GROUP BY d.meta_value, s.meta_value',
                $metaIdCol,
                $metaTable,
                $metaTable,
                $metaIdCol,
                $metaIdCol,
                self::META_SLOT,
                $metaTable,
                $metaIdCol,
                $metaIdCol,
                self::META_LOCATION,
                $locationId,
                $orderTable,
                $orderIdCol,
                $metaIdCol,
                $typeCol,
                'shop_order',
                $statusCol,
                'wc-pending',
                'wc-processing',
                'wc-on-hold',
                'wc-completed',
                self::META_DATE,
                $today,
            ),
            ARRAY_A,
        );

        $counts = [];

        foreach (is_array($rows) ? $rows : [] as $row) {
            $key          = (string) $row['pickup_date'] . '|' . (string) $row['pickup_slot'];
            $counts[$key] = (int) $row['booked'];
        }

        return $this->counts[$locationId] = $counts;
    }

    /**
     * Expand a single opening window into slot start labels with timestamps.
     *
     * @param array{start:string,end:string} $window
     * @return array<int, array{label:string,ts:int}>
     */
    private function slotsInWindow(string $dateKey, array $window, int $minutes, \DateTimeZone $tz): array
    {
        $start = $this->makeTime($dateKey, $window['start'], $tz);
        $end   = $this->makeTime($dateKey, $window['end'], $tz);

        if (! $start instanceof \DateTimeImmutable || ! $end instanceof \DateTimeImmutable || $end <= $start) {
            return [];
        }

        $slots   = [];
        $cursor  = $start;
        $step    = sprintf('+%d minutes', $minutes);
        $guard   = 0;

        while ($cursor < $end && $guard < 500) {
            $slots[] = [
                'label' => $cursor->format('H:i'),
                'ts'    => $cursor->getTimestamp(),
            ];
            $cursor = $cursor->modify($step);
            $guard++;
        }

        return $slots;
    }

    private function makeTime(string $dateKey, string $hhmm, \DateTimeZone $tz): ?\DateTimeImmutable
    {
        if (! preg_match('/^\d{2}:\d{2}$/', $hhmm)) {
            return null;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateKey . ' ' . $hhmm, $tz);

        return $dt instanceof \DateTimeImmutable ? $dt : null;
    }

    private function earliestBookableTimestamp(): int
    {
        $tz  = wp_timezone();
        $now = new \DateTimeImmutable('now', $tz);

        return $now->modify(sprintf('+%d hours', $this->settings->leadHours()))->getTimestamp();
    }
}
