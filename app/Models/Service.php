<?php

namespace App\Models;

use App\Filament\Traits\HasStatusConfiguration;
use App\Filament\Traits\HasTypeConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model {
    use HasStatusConfiguration, HasTypeConfiguration;
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'price',
        'unit',
        'is_active',
        'is_metered',
        'calculation_rules',
        'additional_info',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_metered' => 'boolean',
        'calculation_rules' => 'array',
        'additional_info' => 'array',
    ];

    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    public function scopeMetered($query) {
        return $query->where('is_metered', true);
    }

    // Константы для типов услуг
    protected static array $types = [
        'water' => 'water',
        'electricity' => 'electricity',
        'gas' => 'gas',
        'heating' => 'heating',
        'internet' => 'internet',
        'phone' => 'phone',
        'maintenance' => 'maintenance',
    ];

    // Константы для статусов услуг
    protected static array $statuses = [
        'active' => 'active',
        'inactive' => 'inactive',
        'suspended' => 'suspended',
        'terminated' => 'terminated',
    ];

    // Цвета для типов
    protected static array $typeColors = [
        'water' => 'info',
        'electricity' => 'warning',
        'gas' => 'danger',
        'heating' => 'success',
        'internet' => 'primary',
        'phone' => 'secondary',
        'maintenance' => 'gray',
    ];

    // Цвета для статусов
    protected static array $statusColors = [
        'active' => 'success',
        'inactive' => 'gray',
        'suspended' => 'warning',
        'terminated' => 'danger',
    ];

    protected static function getTranslationKey(): string {
        return 'services';
    }
}
