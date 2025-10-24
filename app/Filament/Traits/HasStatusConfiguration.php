<?php

namespace App\Filament\Traits;

trait HasStatusConfiguration {
    public static function getStatuses(): array {
        return static::$statuses ?? [];
    }

    public static function getStatusColors(): array {
        return static::$statusColors ?? [];
    }

    public static function getStatusLabels(): array {
        $statuses = static::getStatuses();
        $labels = [];

        foreach ($statuses as $key => $value) {
            $labels[$key] = __(static::getTranslationKey() . '.statuses.' . $key);
        }

        return $labels;
    }

    abstract protected static function getTranslationKey(): string;
}
