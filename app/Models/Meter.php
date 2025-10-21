<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meter extends Model {
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
}
