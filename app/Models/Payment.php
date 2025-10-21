<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model {
    use HasFactory;

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
}
