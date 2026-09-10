<?php
/**
 * Uninstall cleanup for Pickup.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the options Pickup
 * creates. Per-order pickup meta (_pickup_location / _pickup_date /
 * _pickup_slot) is intentionally left in place: it is order history that must
 * survive plugin removal for accounting and fulfilment records.
 *
 * @package Pickup
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('pickup_settings');

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'pickup_pro_banner_dismissed', '', true);
