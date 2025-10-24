<?php

namespace App\Filament\Traits;

trait HasTypeConfiguration {
    public static function getTypes(): array {
        return static::$types ?? [];
    }

    public static function getTypeColors(): array {
        return static::$typeColors ?? [];
    }

    public static function getTypeLabels(): array {
        $types = static::getTypes();
        $labels = [];

        foreach ($types as $key => $value) {
            $labels[$key] = __(static::getTranslationKey() . '.types.' . $key);
        }

        return $labels;
    }

    abstract protected static function getTranslationKey(): string;
}
