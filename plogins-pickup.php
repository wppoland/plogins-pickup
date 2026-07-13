<?php
/**
 * Plugin Name:       Pickup - Local Pickup for WooCommerce
 * Plugin URI:        https://plogins.com/plogins-pickup/
 * Description:        Let customers choose a pickup location and time slot at checkout.
 * Version:           1.0.3
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            WPPoland.com
 * Author URI:        https://wppoland.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       plogins-pickup
 * Domain Path:       /languages
 * WC requires at least: 8.0
 *
 * @package Pickup
 */

declare(strict_types=1);

namespace Pickup;

defined('ABSPATH') || exit;

const VERSION     = '1.0.3';
const PLUGIN_FILE = __FILE__;

define('PICKUP_DIR', plugin_dir_path(__FILE__));
define('PICKUP_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/autoload.php';

// Seed default settings on activation so the plugin never runs against an empty
// option set, and the admin screen has sane values out of the box.
register_activation_hook(__FILE__, static function (): void {
    require_once __DIR__ . '/autoload.php';
    Activator::activate();
});

// HPOS + cart/checkout blocks compatibility.
add_action('before_woocommerce_init', static function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('plugins_loaded', static function (): void {
    if (! class_exists('WooCommerce')) {
        add_action('admin_notices', static function (): void {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Pickup - Local Pickup Scheduling for WooCommerce requires WooCommerce to be active.', 'plogins-pickup');
            echo '</p></div>';
        });
        return;
    }

    add_action('init', static function (): void {
        Plugin::instance()->boot();
    }, 0);
}, 10);
