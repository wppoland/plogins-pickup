<?php
/**
 * Uninstall cleanup for Pickup.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the options Pickup
 * creates. Per-order pickup meta (_pickup_location / _pickup_date /
 * _pickup_slot) is intentionally left in place: it is order history that must
 * survive plugin removal for accounting and fulfilment records.
 *
 * Multisite-aware: the settings are a per-site option, so deleting the plugin
 * from a network has to walk every site. Uninstall runs once, on whichever
 * site the deletion was triggered from, so without the loop every other
 * site's row stays in the database for good.
 *
 * @package Pickup
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

/**
 * Remove Pickup's per-site data.
 */
function pickup_uninstall_site(): void
{
    delete_option('pickup_settings');
}

if (is_multisite()) {
    $pickup_site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

    foreach ($pickup_site_ids as $pickup_site_id) {
        switch_to_blog((int) $pickup_site_id);
        pickup_uninstall_site();
        restore_current_blog();
    }

    unset($pickup_site_ids, $pickup_site_id);
} else {
    pickup_uninstall_site();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog, and why it stays outside the
// per-site loop above.
delete_metadata('user', 0, 'pickup_pro_banner_dismissed', '', true);
