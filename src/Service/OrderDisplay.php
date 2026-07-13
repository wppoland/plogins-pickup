<?php

declare(strict_types=1);

namespace Pickup\Service;

use Pickup\Contract\HasHooks;
use Pickup\Support\SettingsStore;

defined('ABSPATH') || exit;

/**
 * Surfaces the saved pickup selection wherever the merchant and customer expect
 * it: the admin order screen, the order-confirmation / customer emails, and the
 * "thank you" + account order-details pages. Read-only; all output escaped.
 */
final class OrderDisplay implements HasHooks
{
    public function __construct(private readonly SettingsStore $settings)
    {
    }

    public function registerHooks(): void
    {
        // Admin order screen (HPOS + legacy).
        add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'renderAdmin']);

        // Customer-facing surfaces.
        add_action('woocommerce_order_details_after_order_table', [$this, 'renderFront']);
        add_action('woocommerce_email_after_order_table', [$this, 'renderEmail'], 10, 4);
    }

    /**
     * @return array{location:string,date:string,slot:string}|null
     */
    private function read(\WC_Order $order): ?array
    {
        $locationId = (string) $order->get_meta(CheckoutFields::META_LOCATION);

        if ($locationId === '') {
            return null;
        }

        // Prefer the snapshotted name; fall back to a live lookup, then the id.
        $name = (string) $order->get_meta('_pickup_location_name');
        if ($name === '') {
            $live = $this->settings->findLocation($locationId);
            $name = $live['name'] ?? $locationId;
        }

        return [
            'location' => $name,
            'date'     => (string) $order->get_meta(CheckoutFields::META_DATE),
            'slot'     => (string) $order->get_meta(CheckoutFields::META_SLOT),
        ];
    }

    private function formatDate(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $ts = strtotime($date);
        if (false === $ts) {
            return $date;
        }

        return wp_date((string) get_option('date_format', 'Y-m-d'), $ts);
    }

    public function renderAdmin(\WC_Order $order): void
    {
        $data = $this->read($order);

        if (null === $data) {
            return;
        }
        ?>
        <div class="pickup-admin-order">
            <h3><?php esc_html_e('Pickup', 'plogins-pickup'); ?></h3>
            <p>
                <strong><?php esc_html_e('Location:', 'plogins-pickup'); ?></strong>
                <?php echo esc_html($data['location']); ?>
            </p>
            <?php if ($data['date'] !== '') : ?>
                <p>
                    <strong><?php esc_html_e('When:', 'plogins-pickup'); ?></strong>
                    <?php
                    echo esc_html(trim($this->formatDate($data['date']) . ' ' . $data['slot']));
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public function renderFront(\WC_Order $order): void
    {
        $data = $this->read($order);

        if (null === $data) {
            return;
        }
        ?>
        <section class="pickup-order-details woocommerce-order-details">
            <h2 class="woocommerce-order-details__title"><?php esc_html_e('Pickup details', 'plogins-pickup'); ?></h2>
            <table class="woocommerce-table pickup-order-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Location', 'plogins-pickup'); ?></th>
                        <td><?php echo esc_html($data['location']); ?></td>
                    </tr>
                    <?php if ($data['date'] !== '') : ?>
                        <tr>
                            <th scope="row"><?php esc_html_e('When', 'plogins-pickup'); ?></th>
                            <td><?php echo esc_html(trim($this->formatDate($data['date']) . ' ' . $data['slot'])); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php
    }

    /**
     * @param bool          $sentToAdmin Whether this email is for the admin.
     * @param bool          $plainText   Whether this is the plain-text variant.
     * @param \WC_Email|null $email       The email object.
     */
    public function renderEmail(\WC_Order $order, bool $sentToAdmin = false, bool $plainText = false, $email = null): void
    {
        $data = $this->read($order);

        if (null === $data) {
            return;
        }

        $when = $data['date'] !== ''
            ? trim($this->formatDate($data['date']) . ' ' . $data['slot'])
            : '';

        if ($plainText) {
            echo "\n" . esc_html__('Pickup details', 'plogins-pickup') . "\n";
            echo esc_html__('Location:', 'plogins-pickup') . ' ' . esc_html($data['location']) . "\n";
            if ($when !== '') {
                echo esc_html__('When:', 'plogins-pickup') . ' ' . esc_html($when) . "\n";
            }
            return;
        }
        ?>
        <h2><?php esc_html_e('Pickup details', 'plogins-pickup'); ?></h2>
        <ul>
            <li><strong><?php esc_html_e('Location:', 'plogins-pickup'); ?></strong> <?php echo esc_html($data['location']); ?></li>
            <?php if ($when !== '') : ?>
                <li><strong><?php esc_html_e('When:', 'plogins-pickup'); ?></strong> <?php echo esc_html($when); ?></li>
            <?php endif; ?>
        </ul>
        <?php
    }
}
