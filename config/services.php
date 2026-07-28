<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container as a lazy singleton. Services are thin and self-contained, this
 * plugin has no external runtime dependencies.
 *
 * @package Pickup
 */

declare(strict_types=1);

use Pickup\Admin\Settings;
use Pickup\Container;
use Pickup\Service\CheckoutFields;
use Pickup\Service\OrderDisplay;
use Pickup\Service\SlotCalculator;
use Pickup\Support\SettingsStore;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(SettingsStore::class, static fn (): SettingsStore => new SettingsStore());

    $c->singleton(SlotCalculator::class, static fn (Container $c): SlotCalculator => new SlotCalculator(
        $c->get(SettingsStore::class),
    ));

    $c->singleton(CheckoutFields::class, static fn (Container $c): CheckoutFields => new CheckoutFields(
        $c->get(SettingsStore::class),
        $c->get(SlotCalculator::class),
    ));

    $c->singleton(OrderDisplay::class, static fn (Container $c): OrderDisplay => new OrderDisplay(
        $c->get(SettingsStore::class),
    ));

    if (is_admin()) {
        $c->singleton(Settings::class, static fn (Container $c): Settings => new Settings(
            $c->get(SettingsStore::class),
        ));
    }
};
