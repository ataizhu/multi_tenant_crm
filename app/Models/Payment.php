<?php

namespace App\Models;

use App\Filament\Traits\HasStatusConfiguration;
use App\Filament\Traits\HasTypeConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model {
    use HasFactory, HasStatusConfiguration, HasTypeConfiguration;

    protected $connection = 'tenant';

    protected $fillable = [
        'subscriber_id',
        'invoice_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'reference_number',
        'notes',
        'additional_info',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'additional_info' => 'array',
    ];

    public function subscriber(): BelongsTo {
        return $this->belongsTo(Subscriber::class);
    }

    public function invoice(): BelongsTo {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    // Константы для способов оплаты
    protected static array $types = [
        'cash' => 'cash',
        'card' => 'card',
        'bank_transfer' => 'bank_transfer',
        'online' => 'online',
        'check' => 'check',
    ];

    // Константы для статусов платежей
    protected static array $statuses = [
        'pending' => 'pending',
        'completed' => 'completed',
        'failed' => 'failed',
        'refunded' => 'refunded',
        'cancelled' => 'cancelled',
    ];

    // Цвета для способов оплаты
    protected static array $typeColors = [
        'cash' => 'success',
        'card' => 'info',
        'bank_transfer' => 'primary',
        'online' => 'warning',
        'check' => 'gray',
    ];

    // Цвета для статусов
    protected static array $statusColors = [
        'pending' => 'warning',
        'completed' => 'success',
        'failed' => 'danger',
        'refunded' => 'info',
        'cancelled' => 'gray',
    ];

    protected static function getTranslationKey(): string {
        return 'payments';
    }
}
