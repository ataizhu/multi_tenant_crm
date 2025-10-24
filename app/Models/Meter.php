<?php

namespace App\Models;

use App\Filament\Traits\HasStatusConfiguration;
use App\Filament\Traits\HasTypeConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model {
    use HasFactory, HasStatusConfiguration, HasTypeConfiguration;

    protected $connection = 'tenant';

    protected $fillable = [
        'subscriber_id',
        'number',
        'type',
        'model',
        'manufacturer',
        'last_reading',
        'last_reading_date',
        'status',
        'installation_date',
        'verification_date',
        'next_verification_date',
        'additional_info',
    ];

    protected $casts = [
        'last_reading' => 'decimal:2',
        'last_reading_date' => 'date',
        'installation_date' => 'date',
        'verification_date' => 'date',
        'next_verification_date' => 'date',
        'additional_info' => 'array',
    ];

    public function subscriber(): BelongsTo {
        return $this->belongsTo(Subscriber::class);
    }

    public function readings(): HasMany {
        return $this->hasMany(MeterReading::class);
    }

    public function latestReading(): HasMany {
        return $this->hasMany(MeterReading::class)->latest('reading_date');
    }

    // Константы для типов счетчиков
    protected static array $types = [
        'water' => 'water',
        'electricity' => 'electricity',
        'gas' => 'gas',
        'heating' => 'heating',
    ];

    // Константы для статусов счетчиков
    protected static array $statuses = [
        'active' => 'active',
        'inactive' => 'inactive',
        'broken' => 'broken',
        'replaced' => 'replaced',
    ];

    // Цвета для типов
    protected static array $typeColors = [
        'water' => 'info',
        'electricity' => 'warning',
        'gas' => 'danger',
        'heating' => 'success',
    ];

    // Цвета для статусов
    protected static array $statusColors = [
        'active' => 'success',
        'inactive' => 'gray',
        'broken' => 'danger',
        'replaced' => 'warning',
    ];

    protected static function getTranslationKey(): string {
        return 'meters';
    }
}
