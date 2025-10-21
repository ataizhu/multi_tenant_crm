<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model {
    protected $connection = 'tenant';

    protected $fillable = [
        'meter_id',
        'reading',
        'reading_date',
        'consumption',
        'status',
        'verified_by',
        'verified_at',
        'notes',
        'additional_info',
    ];

    protected $casts = [
        'reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'reading_date' => 'date',
        'verified_at' => 'datetime',
        'additional_info' => 'array',
    ];

    public function meter(): BelongsTo {
        return $this->belongsTo(Meter::class);
    }
}
