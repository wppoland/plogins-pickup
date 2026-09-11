<?php

declare(strict_types=1);

namespace Pickup\Support;

defined('ABSPATH') || exit;

/**
 * Single source of truth for reading and writing the plugin's settings.
 *
 * Settings live in one option (`pickup_settings`) as a typed array, merged over
 * packaged defaults so a missing or corrupt option never produces a fatal or a
 * blank screen. Callers get a fully-shaped array every time.
 */
final class SettingsStore
{
    public const OPTION = 'pickup_settings';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /**
     * Stored settings merged over packaged defaults.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (is_array($this->cache)) {
            return $this->cache;
        }

        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require PICKUP_DIR . 'config/defaults.php';

        $merged = array_merge($defaults, $stored);

        // Guard the nested shapes so downstream code never type-juggles.
        if (! is_array($merged['windows'] ?? null)) {
            $merged['windows'] = $defaults['windows'];
        }
        if (! is_array($merged['locations'] ?? null)) {
            $merged['locations'] = $defaults['locations'];
        }

        return $this->cache = $merged;
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->all()['enabled'] ?? false);
    }

    public function slotMinutes(): int
    {
        return max(5, (int) ($this->all()['slot_minutes'] ?? 30));
    }

    public function capacity(): int
    {
        return max(1, (int) ($this->all()['capacity'] ?? 5));
    }

    public function leadHours(): int
    {
        return max(0, (int) ($this->all()['lead_hours'] ?? 2));
    }

    public function horizonDays(): int
    {
        return max(1, (int) ($this->all()['horizon_days'] ?? 14));
    }

    /**
     * Weekly opening windows keyed by ISO weekday (1=Mon..7=Sun).
     *
     * @return array<int, array<int, array{start:string,end:string}>>
     */
    public function windows(): array
    {
        $raw    = $this->all()['windows'] ?? [];
        $result = [];

        for ($day = 1; $day <= 7; $day++) {
            $result[$day] = [];
            $entries      = is_array($raw[$day] ?? null) ? $raw[$day] : [];

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $start = isset($entry['start']) ? (string) $entry['start'] : '';
                $end   = isset($entry['end']) ? (string) $entry['end'] : '';
                if ($start === '' || $end === '') {
                    continue;
                }
                $result[$day][] = ['start' => $start, 'end' => $end];
            }
        }

        return $result;
    }

    /**
     * All configured locations (enabled and disabled).
     *
     * @return array<int, array{id:string,name:string,address:string,enabled:bool}>
     */
    public function locations(): array
    {
        $raw    = $this->all()['locations'] ?? [];
        $result = [];

        foreach (is_array($raw) ? $raw : [] as $loc) {
            if (! is_array($loc)) {
                continue;
            }
            $id   = isset($loc['id']) ? (string) $loc['id'] : '';
            $name = isset($loc['name']) ? trim((string) $loc['name']) : '';

            if ($id === '') {
                continue;
            }

            // The seed row ships with no name so that the default can be
            // translated here rather than frozen in English in a config file.
            // A location the merchant actually added and then blanked is still
            // dropped, as before: only the untouched seed has a fallback.
            if ($name === '') {
                if ($id !== 'main') {
                    continue;
                }

                $name = __('Main store', 'plogins-pickup');
            }
            $result[] = [
                'id'      => $id,
                'name'    => $name,
                'address' => isset($loc['address']) ? (string) $loc['address'] : '',
                'enabled' => ! empty($loc['enabled']),
            ];
        }

        return $result;
    }

    /**
     * Only the enabled locations, suitable for the storefront chooser.
     *
     * @return array<int, array{id:string,name:string,address:string,enabled:bool}>
     */
    public function enabledLocations(): array
    {
        return array_values(array_filter(
            $this->locations(),
            static fn (array $loc): bool => $loc['enabled'],
        ));
    }

    /**
     * @return array{id:string,name:string,address:string,enabled:bool}|null
     */
    public function findLocation(string $id): ?array
    {
        foreach ($this->enabledLocations() as $loc) {
            if ($loc['id'] === $id) {
                return $loc;
            }
        }

        return null;
    }

    /**
     * Persist a fully-sanitised settings array and refresh the cache.
     *
     * @param array<string, mixed> $settings
     */
    public function save(array $settings): void
    {
        update_option(self::OPTION, $settings, false);
        $this->cache = null;
    }
}
