<?php

declare(strict_types=1);

/**
 * Slot capacity query guard.
 *
 * Run: php tests/slot-query-test.php
 *
 * Fails if building the checkout slot grid goes back to asking the database
 * once per candidate slot, if the count stops being memoised for the request,
 * if a second location or a second site on a network reuses the first one's
 * counts, if a full slot is
 * still offered (or a slot with room is dropped), if the count stops being
 * restricted to the four statuses that hold a booking, if the join stops
 * counting distinct orders, or if the HPOS and legacy table names get crossed.
 *
 * Plain PHP on purpose: this plugin has no test framework, and adding one to
 * protect these assertions would cost more than it guards.
 */

define('ABSPATH', __DIR__ . '/');
define('PICKUP_DIR', dirname(__DIR__) . '/');

$failures = 0;

function check(string $label, bool $ok): void
{
    global $failures;

    if (! $ok) {
        ++$failures;
        echo "FAIL  {$label}\n";
        return;
    }

    echo "ok    {$label}\n";
}

// --- WordPress / WooCommerce stubs -----------------------------------------

/** @var array<int, array<string, string|int>> Rows the grouped count returns. */
$GLOBALS['rows']            = [];
/** @var array<int, string> Every SQL statement the calculator ran. */
$GLOBALS['queries']         = [];
/** @var int How many times the per-slot order lookup was called. */
$GLOBALS['wc_get_orders']   = 0;
$GLOBALS['hpos']            = false;
/** @var int Current site on the network. */
$GLOBALS['blog_id']         = 1;
/** @var list<string> What wc_get_order_types('view-orders') answers. */
$GLOBALS['order_types']     = ['shop_order', 'shop_order_refund'];

function wp_timezone(): DateTimeZone
{
    return new DateTimeZone('UTC');
}

function apply_filters(string $hook, $value, ...$rest)
{
    return $value;
}

function get_option(string $key, $default = false)
{
    return $default;
}

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function wc_get_orders(array $args)
{
    ++$GLOBALS['wc_get_orders'];

    return [];
}

function wc_get_order_types(string $for = ''): array
{
    return 'view-orders' === $for ? $GLOBALS['order_types'] : ['shop_order'];
}

function get_current_blog_id(): int
{
    return (int) $GLOBALS['blog_id'];
}

class Stub_wpdb
{
    public string $prefix   = 'wp_';
    public string $postmeta = 'wp_postmeta';
    public string $posts    = 'wp_posts';

    public function prepare(string $query, ...$args): string
    {
        $left = count($args);

        $sql = (string) preg_replace_callback(
            '/%[isd]/',
            static function (array $m) use (&$args): string {
                if ($args === []) {
                    throw new RuntimeException('more placeholders than arguments');
                }

                $arg = array_shift($args);

                if ($m[0] === '%i') {
                    return '`' . $arg . '`';
                }
                if ($m[0] === '%d') {
                    return (string) (int) $arg;
                }

                return "'" . $arg . "'";
            },
            $query,
        );

        // Real wpdb::prepare() returns null and warns when the counts disagree,
        // which would turn a miscount into an empty grid rather than a failure.
        if ($args !== []) {
            throw new RuntimeException('more arguments than placeholders: ' . count($args) . ' of ' . $left . ' left over');
        }

        return $sql;
    }

    public function get_results(string $sql, $output = null): array
    {
        $GLOBALS['queries'][] = $sql;

        return $GLOBALS['rows'];
    }
}

if (! defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

$GLOBALS['wpdb'] = new Stub_wpdb();

// The calculator asks WooCommerce whether HPOS is on; this stub lets the test
// drive both branches.
eval(
    'namespace Automattic\\WooCommerce\\Utilities;'
    . 'class OrderUtil { public static function custom_orders_table_usage_is_enabled(): bool'
    . ' { return (bool) $GLOBALS["hpos"]; } }'
);

require dirname(__DIR__) . '/src/Support/SettingsStore.php';
require dirname(__DIR__) . '/src/Service/SlotCalculator.php';

function make_calculator(): \Pickup\Service\SlotCalculator
{
    return new \Pickup\Service\SlotCalculator(new \Pickup\Support\SettingsStore());
}

// --- A grid build is one query, not one per slot ---------------------------

$GLOBALS['rows']    = [];
$GLOBALS['queries'] = [];

$calc     = make_calculator();
$schedule = $calc->schedule('main');
$slots    = array_sum(array_map('count', $schedule));

check('the default horizon offers slots to book', $slots > 20);
check('building the whole grid runs exactly one query, not one per slot', count($GLOBALS['queries']) === 1);
check('the per-slot order lookup is gone', $GLOBALS['wc_get_orders'] === 0);

// --- The count is memoised for the request ---------------------------------

$calc->schedule('main');
$calc->bookedCount('main', array_key_first($schedule), '09:00');
check('a second grid build for the same location adds no query', count($GLOBALS['queries']) === 1);

$calc->schedule('branch');
check('a different location gets its own query', count($GLOBALS['queries']) === 2);
check('that query asks for that location', str_contains((string) ($GLOBALS['queries'][1] ?? ''), "'branch'"));

// --- The memo is per site, not per process ---------------------------------

$GLOBALS['blog_id'] = 2;
$calc->bookedCount('main', (string) array_key_first($schedule), '09:00');
check('another site on the network does not reuse the first site\'s counts', count($GLOBALS['queries']) === 3);

$GLOBALS['blog_id'] = 1;
$calc->bookedCount('main', (string) array_key_first($schedule), '09:00');
check('switching back still answers from memory', count($GLOBALS['queries']) === 3);

// --- Capacity still decides what is offered --------------------------------

$firstDate  = (string) array_key_first($schedule);
$firstSlot  = (string) $schedule[$firstDate][0];
$dayBefore  = count($schedule[$firstDate]);

$GLOBALS['rows'] = [
    ['pickup_date' => $firstDate, 'pickup_slot' => $firstSlot, 'booked' => '5'],
];

$full = make_calculator();
$grid = $full->schedule('main');

check('a slot at capacity is not offered', ! in_array($firstSlot, $grid[$firstDate] ?? [], true));
check('the rest of that day is untouched', count($grid[$firstDate] ?? []) === $dayBefore - 1);
check('bookedCount reports the grouped total', $full->bookedCount('main', $firstDate, $firstSlot) === 5);
check('bookedCount is zero for a slot nobody booked', $full->bookedCount('main', $firstDate, '16:30') === 0);
check('isBookable refuses the full slot', ! $full->isBookable('main', $firstDate, $firstSlot));

$GLOBALS['rows'] = [
    ['pickup_date' => $firstDate, 'pickup_slot' => $firstSlot, 'booked' => '4'],
];

$room = make_calculator();
check('a slot with room left is still offered', in_array($firstSlot, $room->schedule('main')[$firstDate] ?? [], true));

// --- What the query itself must say ----------------------------------------

$sql = (string) ($GLOBALS['queries'][0] ?? '');

foreach (['wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed'] as $status) {
    check("the count is restricted to {$status}", str_contains($sql, "'{$status}'"));
}

check('the count keeps the order types wc_get_orders() used', str_contains($sql, "'shop_order', 'shop_order_refund'"));
check('the count groups by date and slot', str_contains($sql, 'GROUP BY d.meta_value, s.meta_value'));
check('the count counts distinct orders, not meta rows', str_contains($sql, 'COUNT(DISTINCT'));
check('the count ignores dates already past', str_contains($sql, "d.meta_value >= '" . gmdate('Y-m-d') . "'"));
check('the count is not paged by offset', ! str_contains(strtoupper($sql), 'OFFSET'));
check('every placeholder was filled', ! str_contains($sql, '%i') && ! str_contains($sql, '%s'));

// --- HPOS and legacy read their own tables ---------------------------------

$GLOBALS['hpos']    = true;
$GLOBALS['queries'] = [];
make_calculator()->schedule('main');
$hposSql = (string) ($GLOBALS['queries'][0] ?? '');

check('HPOS reads the order meta table', str_contains($hposSql, '`wp_wc_orders_meta`'));
check('HPOS joins the orders table on its own status column', str_contains($hposSql, '`wp_wc_orders`') && str_contains($hposSql, 'o.`status` IN'));
check('HPOS joins meta rows on order_id', str_contains($hposSql, 'd.`order_id`'));
check('HPOS filters on its own type column', str_contains($hposSql, "o.`type` IN ('shop_order', 'shop_order_refund')"));

$GLOBALS['hpos']    = false;
$GLOBALS['queries'] = [];
make_calculator()->schedule('main');
$legacySql = (string) ($GLOBALS['queries'][0] ?? '');

check('legacy reads postmeta', str_contains($legacySql, '`wp_postmeta`'));
check('legacy joins posts on post_status', str_contains($legacySql, '`wp_posts`') && str_contains($legacySql, 'o.`post_status` IN'));
check('legacy joins meta rows on post_id', str_contains($legacySql, 'd.`post_id`'));
check('legacy filters on post_type', str_contains($legacySql, "o.`post_type` IN ('shop_order', 'shop_order_refund')"));

// A site that registers another order type must widen the count, not be ignored.
$GLOBALS['order_types'] = ['shop_order', 'shop_order_refund', 'shop_subscription'];
$GLOBALS['queries']     = [];
make_calculator()->schedule('main');
check(
    'a third registered order type is counted too',
    str_contains((string) ($GLOBALS['queries'][0] ?? ''), "'shop_order', 'shop_order_refund', 'shop_subscription'"),
);
$GLOBALS['order_types'] = ['shop_order', 'shop_order_refund'];

check('nothing in the run fell back to a per-slot order query', $GLOBALS['wc_get_orders'] === 0);

echo $failures === 0 ? "\nAll checks passed.\n" : "\n{$failures} check(s) failed.\n";

exit($failures === 0 ? 0 : 1);
