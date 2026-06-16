<?php

declare(strict_types=1);

namespace Pickup\Admin;

use Pickup\Contract\HasHooks;
use Pickup\Support\SettingsStore;

defined('ABSPATH') || exit;

/**
 * Admin management screen registered as a WooCommerce submenu.
 *
 * One form drives everything: the master toggle and rules (slot length, lead
 * time, capacity, booking horizon, require-slot), the weekly opening windows,
 * and the list of pickup locations. Output is escaped; input is sanitised and
 * clamped on save behind a nonce + the manage_woocommerce capability.
 */
final class Settings implements HasHooks
{
    private const PAGE  = 'pickup-settings';
    private const NONCE = 'pickup_save_settings';

    public function __construct(private readonly SettingsStore $settings)
    {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_post_pickup_save_settings', [$this, 'handleSave']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Pickup Scheduling', 'pickup'),
            __('Pickup', 'pickup'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function enqueueAssets(string $hook): void
    {
        if ($hook !== 'woocommerce_page_' . self::PAGE) {
            return;
        }

        wp_enqueue_style(
            'pickup-admin',
            PICKUP_URL . 'assets/css/admin.css',
            [],
            \Pickup\VERSION,
        );

        wp_enqueue_script(
            'pickup-admin',
            PICKUP_URL . 'assets/js/admin.js',
            [],
            \Pickup\VERSION,
            ['in_footer' => true, 'strategy' => 'defer'],
        );

        wp_localize_script('pickup-admin', 'PickupAdmin', [
            'i18n' => [
                'remove'      => __('Remove', 'pickup'),
                'confirmGone' => __('Remove this item?', 'pickup'),
            ],
        ]);
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $s         = $this->settings;
        $locations = $s->locations();
        $windows   = $s->windows();
        $saved     = isset($_GET['updated']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flash flag.
        ?>
        <div class="wrap pickup-admin">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if ($saved) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Pickup settings saved.', 'pickup'); ?></p>
                </div>
            <?php endif; ?>

            <div class="pickup-intro">
                <h2><?php esc_html_e('Let customers book a pickup time', 'pickup'); ?></h2>
                <p>
                    <?php esc_html_e('When an order uses WooCommerce Local Pickup, shoppers choose a location and a time slot at checkout. Define your locations, weekly opening hours, slot length and capacity below.', 'pickup'); ?>
                </p>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="pickup-form">
                <input type="hidden" name="action" value="pickup_save_settings" />
                <?php wp_nonce_field(self::NONCE, '_pickup_nonce'); ?>

                <div class="pickup-card">
                    <h2><?php esc_html_e('General', 'pickup'); ?></h2>
                    <p class="description">
                        <?php esc_html_e('The booking rules that shape which time slots customers can pick. The defaults work for most shops — adjust only if your pickup desk needs tighter or looser timing.', 'pickup'); ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Enable pickup scheduling', 'pickup'); ?>
                                </th>
                                <td>
                                    <label for="pickup_enabled">
                                        <input type="checkbox" id="pickup_enabled" name="enabled" value="1" <?php checked($s->isEnabled(), true); ?> />
                                        <?php esc_html_e('Show pickup fields when Local Pickup is selected.', 'pickup'); ?>
                                    </label>
                                    <p class="description pickup-help">
                                        <?php esc_html_e('Turn this off to keep your locations and hours saved but stop showing the time picker at checkout.', 'pickup'); ?>
                                    </p>
                                </td>
                            </tr>
                            <?php
                            $this->numberRow(
                                'slot_minutes',
                                __('Slot length (minutes)', 'pickup'),
                                $s->slotMinutes(),
                                5,
                                __('How far apart pickup times are offered. 30 gives slots at 09:00, 09:30, 10:00 and so on.', 'pickup'),
                                __('Default: 30', 'pickup'),
                            );
                            $this->numberRow(
                                'capacity',
                                __('Capacity per slot', 'pickup'),
                                $s->capacity(),
                                1,
                                __('How many orders may book the same location and time before that slot shows as full and is hidden.', 'pickup'),
                                __('Default: 5', 'pickup'),
                            );
                            $this->numberRow(
                                'lead_hours',
                                __('Lead time (hours)', 'pickup'),
                                $s->leadHours(),
                                0,
                                __('The minimum notice before the earliest bookable slot, so staff have time to prepare. 2 hides any slot less than two hours away.', 'pickup'),
                                __('Default: 2', 'pickup'),
                            );
                            $this->numberRow(
                                'horizon_days',
                                __('Booking horizon (days)', 'pickup'),
                                $s->horizonDays(),
                                1,
                                __('How far ahead customers may book. 14 lets them choose any open slot within the next two weeks.', 'pickup'),
                                __('Default: 14', 'pickup'),
                            );
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="pickup-card">
                    <h2><?php esc_html_e('Weekly opening hours', 'pickup'); ?></h2>
                    <p class="description">
                        <?php esc_html_e('Set one or more time windows per day using 24-hour HH:MM. Leave a day blank to close it. Slots are generated inside these windows using the slot length above.', 'pickup'); ?>
                    </p>
                    <table class="form-table pickup-windows" role="presentation">
                        <tbody>
                            <?php foreach ($this->weekdays() as $day => $label) : ?>
                                <tr>
                                    <th scope="row"><?php echo esc_html($label); ?></th>
                                    <td>
                                        <?php
                                        $entry = $windows[$day][0] ?? ['start' => '', 'end' => ''];
                                        ?>
                                        <label class="pickup-window">
                                            <span class="screen-reader-text">
                                                <?php
                                                /* translators: %s: weekday name. */
                                                echo esc_html(sprintf(__('%s opening time', 'pickup'), $label));
                                                ?>
                                            </span>
                                            <input type="time" name="windows[<?php echo esc_attr((string) $day); ?>][start]" value="<?php echo esc_attr($entry['start']); ?>" />
                                        </label>
                                        <span aria-hidden="true">–</span>
                                        <label class="pickup-window">
                                            <span class="screen-reader-text">
                                                <?php
                                                /* translators: %s: weekday name. */
                                                echo esc_html(sprintf(__('%s closing time', 'pickup'), $label));
                                                ?>
                                            </span>
                                            <input type="time" name="windows[<?php echo esc_attr((string) $day); ?>][end]" value="<?php echo esc_attr($entry['end']); ?>" />
                                        </label>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pickup-card">
                    <h2><?php esc_html_e('Pickup locations', 'pickup'); ?></h2>
                    <p class="description">
                        <?php esc_html_e('Add the places customers can collect from. Disable a location to hide it from checkout without losing its details. At least one enabled location is needed for the checkout fields to appear.', 'pickup'); ?>
                    </p>
                    <div class="pickup-locations" data-pickup-locations>
                        <?php
                        $rows = $locations === [] ? [['id' => '', 'name' => '', 'address' => '', 'enabled' => true]] : $locations;
                        foreach ($rows as $i => $loc) {
                            $this->locationRow((int) $i, $loc);
                        }
                        ?>
                    </div>
                    <p>
                        <button type="button" class="button pickup-add-location" data-pickup-add>
                            <?php esc_html_e('Add location', 'pickup'); ?>
                        </button>
                    </p>
                    <template data-pickup-template>
                        <?php $this->locationRow(9999, ['id' => '', 'name' => '', 'address' => '', 'enabled' => true]); ?>
                    </template>
                </div>

                <?php submit_button(__('Save pickup settings', 'pickup')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * @param array{id:string,name:string,address:string,enabled:bool} $loc
     */
    private function locationRow(int $index, array $loc): void
    {
        ?>
        <fieldset class="pickup-location-row" data-pickup-row>
            <legend class="screen-reader-text"><?php esc_html_e('Pickup location', 'pickup'); ?></legend>
            <p class="pickup-location-row__field">
                <label>
                    <span><?php esc_html_e('Name', 'pickup'); ?></span>
                    <input type="text" name="locations[<?php echo esc_attr((string) $index); ?>][name]" value="<?php echo esc_attr($loc['name']); ?>" class="regular-text" placeholder="<?php esc_attr_e('e.g. Downtown store', 'pickup'); ?>" />
                </label>
            </p>
            <p class="pickup-location-row__field">
                <label>
                    <span><?php esc_html_e('Address', 'pickup'); ?></span>
                    <input type="text" name="locations[<?php echo esc_attr((string) $index); ?>][address]" value="<?php echo esc_attr($loc['address']); ?>" class="regular-text" placeholder="<?php esc_attr_e('Optional', 'pickup'); ?>" />
                </label>
            </p>
            <p class="pickup-location-row__field pickup-location-row__field--toggle">
                <label>
                    <input type="checkbox" name="locations[<?php echo esc_attr((string) $index); ?>][enabled]" value="1" <?php checked($loc['enabled'], true); ?> />
                    <?php esc_html_e('Enabled', 'pickup'); ?>
                </label>
                <button type="button" class="button-link pickup-remove" data-pickup-remove>
                    <?php esc_html_e('Remove', 'pickup'); ?>
                </button>
            </p>
        </fieldset>
        <?php
    }

    private function numberRow(string $key, string $label, int $value, int $min, string $help = '', string $defaultHint = ''): void
    {
        $id     = 'pickup_' . $key;
        $helpId = $id . '_help';
        ?>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr($id); ?>"><?php echo esc_html($label); ?></label>
            </th>
            <td>
                <span class="pickup-number">
                    <input type="number" min="<?php echo esc_attr((string) $min); ?>" step="1" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr((string) $value); ?>" class="small-text"<?php echo $help !== '' ? ' aria-describedby="' . esc_attr($helpId) . '"' : ''; ?> />
                    <?php if ($defaultHint !== '') : ?>
                        <span class="pickup-default-hint"><?php echo esc_html($defaultHint); ?></span>
                    <?php endif; ?>
                </span>
                <?php if ($help !== '') : ?>
                    <p class="description pickup-help" id="<?php echo esc_attr($helpId); ?>"><?php echo esc_html($help); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    /**
     * @return array<int, string>
     */
    private function weekdays(): array
    {
        return [
            1 => __('Monday', 'pickup'),
            2 => __('Tuesday', 'pickup'),
            3 => __('Wednesday', 'pickup'),
            4 => __('Thursday', 'pickup'),
            5 => __('Friday', 'pickup'),
            6 => __('Saturday', 'pickup'),
            7 => __('Sunday', 'pickup'),
        ];
    }

    /**
     * Validate the nonce + capability, sanitise everything, persist, and redirect
     * back with a flash flag (PRG pattern, no resubmit on refresh).
     */
    public function handleSave(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You do not have permission to do this.', 'pickup'));
        }

        if (
            ! isset($_POST['_pickup_nonce'])
            || ! wp_verify_nonce(sanitize_text_field(wp_unslash((string) $_POST['_pickup_nonce'])), self::NONCE)
        ) {
            wp_die(esc_html__('Security check failed. Please try again.', 'pickup'));
        }

        $settings = [
            'enabled'      => isset($_POST['enabled']),
            'slot_minutes' => isset($_POST['slot_minutes']) ? max(5, (int) $_POST['slot_minutes']) : 30,
            'capacity'     => isset($_POST['capacity']) ? max(1, (int) $_POST['capacity']) : 5,
            'lead_hours'   => isset($_POST['lead_hours']) ? max(0, (int) $_POST['lead_hours']) : 2,
            'horizon_days' => isset($_POST['horizon_days']) ? max(1, (int) $_POST['horizon_days']) : 14,
            // Nested arrays are unslashed here and each scalar is sanitised
            // inside the helpers (per-field sanitize_text_field / regex / int).
            'windows'      => $this->sanitizeWindows(isset($_POST['windows']) ? wp_unslash($_POST['windows']) : null), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per-field in helper.
            'locations'    => $this->sanitizeLocations(isset($_POST['locations']) ? wp_unslash($_POST['locations']) : null), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per-field in helper.
        ];

        $this->settings->save($settings);

        wp_safe_redirect(add_query_arg(
            ['page' => self::PAGE, 'updated' => '1'],
            admin_url('admin.php'),
        ));
        exit;
    }

    /**
     * @param mixed $raw
     * @return array<int, array<int, array{start:string,end:string}>>
     */
    private function sanitizeWindows(mixed $raw): array
    {
        $clean = [];

        for ($day = 1; $day <= 7; $day++) {
            $clean[$day] = [];
            $entry       = is_array($raw) && is_array($raw[$day] ?? null) ? $raw[$day] : [];

            $start = $this->time(isset($entry['start']) ? (string) $entry['start'] : '');
            $end   = $this->time(isset($entry['end']) ? (string) $entry['end'] : '');

            // Only keep a valid, non-empty, ordered window.
            if ($start !== '' && $end !== '' && $end > $start) {
                $clean[$day][] = ['start' => $start, 'end' => $end];
            }
        }

        return $clean;
    }

    /**
     * @param mixed $raw
     * @return array<int, array{id:string,name:string,address:string,enabled:bool}>
     */
    private function sanitizeLocations(mixed $raw): array
    {
        $clean = [];
        $used  = [];

        foreach (is_array($raw) ? $raw : [] as $row) {
            if (! is_array($row)) {
                continue;
            }

            // $raw was already wp_unslash()'d at the call site.
            $name = isset($row['name']) ? sanitize_text_field((string) $row['name']) : '';

            if ($name === '') {
                continue;
            }

            $id = sanitize_title($name);
            if ($id === '') {
                $id = 'loc';
            }
            // Ensure a stable, unique id even for duplicate names.
            $base    = $id;
            $counter = 2;
            while (in_array($id, $used, true)) {
                $id = $base . '-' . $counter;
                $counter++;
            }
            $used[] = $id;

            $clean[] = [
                'id'      => $id,
                'name'    => $name,
                'address' => isset($row['address']) ? sanitize_text_field((string) $row['address']) : '',
                'enabled' => ! empty($row['enabled']),
            ];
        }

        return $clean;
    }

    private function time(string $value): string
    {
        $value = trim($value);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : '';
    }
}
