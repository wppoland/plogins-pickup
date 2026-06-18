<?php

declare(strict_types=1);

namespace Pickup\Service;

use Pickup\Contract\HasHooks;
use Pickup\Support\SettingsStore;

defined('ABSPATH') || exit;

/**
 * Storefront integration: when the order uses WooCommerce Local Pickup, render a
 * pickup-location chooser plus a date + time-slot picker on the (classic)
 * checkout, validate the selection, and persist it to the order.
 *
 * All choices are stored as order meta — no custom table — keeping the MVP
 * self-contained. The fields only appear for local-pickup shipping; for every
 * other method they stay hidden and are never required.
 */
final class CheckoutFields implements HasHooks
{
    public const META_LOCATION = '_pickup_location';
    public const META_DATE     = '_pickup_date';
    public const META_SLOT     = '_pickup_slot';

    private const FIELD_LOCATION = 'pickup_location';
    private const FIELD_DATE     = 'pickup_date';
    private const FIELD_SLOT     = 'pickup_slot';
    private const NONCE          = 'pickup_checkout';

    public function __construct(
        private readonly SettingsStore $settings,
        private readonly SlotCalculator $calculator,
    ) {
    }

    public function registerHooks(): void
    {
        if (! $this->settings->isEnabled()) {
            return;
        }

        add_action('woocommerce_after_order_notes', [$this, 'renderFields']);
        add_action('woocommerce_checkout_process', [$this, 'validateFields']);
        add_action('woocommerce_checkout_create_order', [$this, 'saveFields'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_ajax_pickup_slots', [$this, 'ajaxSlots']);
        add_action('wp_ajax_nopriv_pickup_slots', [$this, 'ajaxSlots']);
    }

    /**
     * True when the customer's chosen shipping method is a WooCommerce local
     * pickup method. Robust to method-id variants and the Blocks pickup mode.
     */
    public function isLocalPickupChosen(): bool
    {
        if (! function_exists('WC') || null === WC()->session) {
            return false;
        }

        $chosen = WC()->session->get('chosen_shipping_methods');

        if (! is_array($chosen)) {
            return false;
        }

        foreach ($chosen as $method) {
            $method = (string) $method;
            if (str_starts_with($method, 'local_pickup') || str_starts_with($method, 'pickup_location')) {
                return true;
            }
        }

        return false;
    }

    public function enqueueAssets(): void
    {
        if (! function_exists('is_checkout') || ! is_checkout()) {
            return;
        }

        wp_enqueue_style(
            'pickup-checkout',
            PICKUP_URL . 'assets/css/checkout.css',
            [],
            \Pickup\VERSION,
        );

        wp_enqueue_script(
            'pickup-checkout',
            PICKUP_URL . 'assets/js/checkout.js',
            [],
            \Pickup\VERSION,
            ['in_footer' => true, 'strategy' => 'defer'],
        );

        wp_localize_script('pickup-checkout', 'PickupCheckout', [
            'ajaxUrl'    => admin_url('admin-ajax.php'),
            'nonce'      => wp_create_nonce(self::NONCE),
            'fieldSlot'  => self::FIELD_SLOT,
            'blockedDates' => apply_filters('pickup/blocked_dates', [], ''),
            'i18n'       => [
                'choosePrompt' => __('Select a date to see available times.', 'pickup'),
                'noSlots'      => __('No times available on this date. Please choose another.', 'pickup'),
                'blockedDate'  => __('This date is not available for pickup. Please choose another.', 'pickup'),
                'loading'      => __('Loading times…', 'pickup'),
                'error'        => __('Could not load times. Please try again.', 'pickup'),
            ],
        ]);
    }

    /**
     * Render the pickup fields. Hidden by default and shown by JS only when local
     * pickup is the active method; server-side validation still enforces the rule
     * regardless of JS.
     */
    public function renderFields(): void
    {
        $locations = $this->settings->enabledLocations();
        $active    = $this->isLocalPickupChosen();

        // Misconfigured store: pickup is enabled but no locations exist. Don't
        // block checkout — show a gentle notice instead of broken controls.
        if ($locations === []) {
            if ($active) {
                echo '<div class="pickup-fields pickup-fields--empty" role="status">';
                echo '<p>' . esc_html__('Local pickup is selected but no pickup locations are configured yet. Please contact the store.', 'pickup') . '</p>';
                echo '</div>';
            }
            return;
        }

        $horizon = $this->settings->horizonDays();
        $tz      = wp_timezone();
        $today   = (new \DateTimeImmutable('now', $tz))->format('Y-m-d');
        $maxDate = (new \DateTimeImmutable('now', $tz))->modify(sprintf('+%d days', $horizon))->format('Y-m-d');

        $selLocation = isset($_POST[self::FIELD_LOCATION]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_LOCATION])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- repopulation only; the value is re-validated on submit.
        $selDate     = isset($_POST[self::FIELD_DATE]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_DATE])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- repopulation only.
        $selSlot     = isset($_POST[self::FIELD_SLOT]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_SLOT])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- repopulation only.
        ?>
        <div class="pickup-fields" data-pickup-fields<?php echo $active ? '' : ' hidden'; ?>>
            <h3 class="pickup-fields__title"><?php esc_html_e('Pickup details', 'pickup'); ?></h3>
            <p class="pickup-fields__intro"><?php esc_html_e('Choose where and when you would like to collect your order.', 'pickup'); ?></p>

            <?php wp_nonce_field(self::NONCE, '_pickup_nonce'); ?>

            <p class="form-row form-row-wide pickup-field pickup-field--location">
                <label for="<?php echo esc_attr(self::FIELD_LOCATION); ?>">
                    <?php esc_html_e('Pickup location', 'pickup'); ?>
                    <abbr class="required" title="<?php esc_attr_e('required', 'pickup'); ?>">*</abbr>
                </label>
                <select
                    name="<?php echo esc_attr(self::FIELD_LOCATION); ?>"
                    id="<?php echo esc_attr(self::FIELD_LOCATION); ?>"
                    class="pickup-input"
                    data-pickup-location
                >
                    <option value=""><?php esc_html_e('Select a location…', 'pickup'); ?></option>
                    <?php foreach ($locations as $loc) : ?>
                        <option
                            value="<?php echo esc_attr($loc['id']); ?>"
                            <?php selected($selLocation, $loc['id']); ?>
                        >
                            <?php
                            echo esc_html($loc['name']);
                            if ($loc['address'] !== '') {
                                echo ' — ' . esc_html($loc['address']);
                            }
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p class="form-row form-row-first pickup-field pickup-field--date">
                    <label for="<?php echo esc_attr(self::FIELD_DATE); ?>">
                        <?php esc_html_e('Pickup date', 'pickup'); ?>
                        <abbr class="required" title="<?php esc_attr_e('required', 'pickup'); ?>">*</abbr>
                    </label>
                    <input
                        type="date"
                        name="<?php echo esc_attr(self::FIELD_DATE); ?>"
                        id="<?php echo esc_attr(self::FIELD_DATE); ?>"
                        class="pickup-input"
                        min="<?php echo esc_attr($today); ?>"
                        max="<?php echo esc_attr($maxDate); ?>"
                        value="<?php echo esc_attr($selDate); ?>"
                        data-pickup-date
                        autocomplete="off"
                    />
                </p>

                <p class="form-row form-row-last pickup-field pickup-field--slot">
                    <label for="<?php echo esc_attr(self::FIELD_SLOT); ?>">
                        <?php esc_html_e('Pickup time', 'pickup'); ?>
                        <abbr class="required" title="<?php esc_attr_e('required', 'pickup'); ?>">*</abbr>
                    </label>
                    <select
                        name="<?php echo esc_attr(self::FIELD_SLOT); ?>"
                        id="<?php echo esc_attr(self::FIELD_SLOT); ?>"
                        class="pickup-input"
                        data-pickup-slot
                        aria-live="polite"
                    >
                        <option value=""><?php esc_html_e('Select a date first…', 'pickup'); ?></option>
                        <?php if ($selDate !== '' && $selLocation !== '') : ?>
                            <?php foreach ($this->calculator->schedule($selLocation)[$selDate] ?? [] as $slot) : ?>
                                <option value="<?php echo esc_attr($slot); ?>" <?php selected($selSlot, $slot); ?>>
                                    <?php echo esc_html($slot); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <span class="pickup-field__status" data-pickup-status role="status" aria-live="polite"></span>
                </p>

                <p class="pickup-fields__claim" data-pickup-claim aria-live="polite">
                    <span class="pickup-fields__stamp" aria-hidden="true"><?php esc_html_e('Reserved', 'pickup'); ?></span>
                    <span class="pickup-fields__claim-text"><?php esc_html_e('Reserved — your order will be waiting.', 'pickup'); ?></span>
                </p>
        </div>
        <?php
    }

    /**
     * Server-side validation. Only enforced when local pickup is the chosen
     * method; otherwise the fields are irrelevant and skipped.
     */
    public function validateFields(): void
    {
        if (! $this->isLocalPickupChosen()) {
            return;
        }

        if (
            ! isset($_POST['_pickup_nonce'])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_pickup_nonce'])), self::NONCE)
        ) {
            wc_add_notice(__('Your pickup selection could not be verified. Please try again.', 'pickup'), 'error');
            return;
        }

        $locationId = isset($_POST[self::FIELD_LOCATION]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_LOCATION])) : '';

        if ($locationId === '' || null === $this->settings->findLocation($locationId)) {
            wc_add_notice(__('Please choose a valid pickup location.', 'pickup'), 'error');
            return;
        }

        $date = isset($_POST[self::FIELD_DATE]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_DATE])) : '';
        $slot = isset($_POST[self::FIELD_SLOT]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_SLOT])) : '';

        if ($date === '' || $slot === '') {
            wc_add_notice(__('Please choose a pickup date and time.', 'pickup'), 'error');
            return;
        }

        if (! $this->calculator->isBookable($locationId, $date, $slot)) {
            wc_add_notice(__('That pickup time is no longer available. Please pick another.', 'pickup'), 'error');
        }
    }

    /**
     * Persist the validated selection to the order.
     */
    public function saveFields(\WC_Order $order, mixed $data): void
    {
        if (! $this->isLocalPickupChosen()) {
            return;
        }

        if (
            ! isset($_POST['_pickup_nonce'])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_pickup_nonce'])), self::NONCE)
        ) {
            return;
        }

        $locationId = isset($_POST[self::FIELD_LOCATION]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_LOCATION])) : '';
        $location   = $this->settings->findLocation($locationId);

        if (null === $location) {
            return;
        }

        $order->update_meta_data(self::META_LOCATION, $locationId);
        // Store the human-readable name so historic orders stay readable even if
        // the location is later renamed or removed.
        $order->update_meta_data('_pickup_location_name', $location['name']);

        $date = isset($_POST[self::FIELD_DATE]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_DATE])) : '';
        $slot = isset($_POST[self::FIELD_SLOT]) ? sanitize_text_field(wp_unslash((string) $_POST[self::FIELD_SLOT])) : '';

        if ($date !== '' && $slot !== '') {
            $order->update_meta_data(self::META_DATE, $date);
            $order->update_meta_data(self::META_SLOT, $slot);
        }
    }

    /**
     * AJAX: return the live slot list for a location + date so the time dropdown
     * stays in sync without a page reload.
     */
    public function ajaxSlots(): void
    {
        check_ajax_referer(self::NONCE, 'nonce');

        $locationId = isset($_POST['location']) ? sanitize_text_field(wp_unslash((string) $_POST['location'])) : '';
        $date       = isset($_POST['date']) ? sanitize_text_field(wp_unslash((string) $_POST['date'])) : '';

        if ($locationId === '' || $date === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            wp_send_json_error(['slots' => []]);
        }

        if (null === $this->settings->findLocation($locationId)) {
            wp_send_json_error(['slots' => []]);
        }

        $schedule = $this->calculator->schedule($locationId);

        wp_send_json_success(['slots' => array_values($schedule[$date] ?? [])]);
    }
}
