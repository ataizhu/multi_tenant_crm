<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model {
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
}
