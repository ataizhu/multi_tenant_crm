<?php

namespace App\Models;

use App\Filament\Traits\HasStatusConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model {
    use HasFactory, HasStatusConfiguration;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'status',
        'balance',
        'apartment_number',
        'building_number',
        'registration_date',
        'additional_info',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'registration_date' => 'date',
        'additional_info' => 'array',
    ];

    public function meters(): HasMany {
        return $this->hasMany(Meter::class);
    }

    public function invoices(): HasMany {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool {
        return $this->status === 'active';
    }

    public function getFullAddress(): string {
        $address = $this->address;
        if ($this->building_number) {
            $address .= ', дом ' . $this->building_number;
        }
        if ($this->apartment_number) {
            $address .= ', кв. ' . $this->apartment_number;
        }
        return $address;
    }

    // Константы для статусов абонентов
    protected static array $statuses = [
        'active' => 'active',
        'inactive' => 'inactive',
        'suspended' => 'suspended',
        'terminated' => 'terminated',
    ];

    // Цвета для статусов
    protected static array $statusColors = [
        'active' => 'success',
        'inactive' => 'gray',
        'suspended' => 'warning',
        'terminated' => 'danger',
    ];

    protected static function getTranslationKey(): string {
        return 'subscribers';
    }
}
