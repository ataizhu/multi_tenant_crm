<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model {
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'subscriber_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'period_start',
        'period_end',
        'amount',
        'tax_amount',
        'total_amount',
        'status',
        'notes',
        'line_items',
        'additional_info',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'line_items' => 'array',
        'additional_info' => 'array',
    ];

    public function subscriber(): BelongsTo {
        return $this->belongsTo(Subscriber::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }

    public function scopePaid($query) {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query) {
        return $query->where('status', 'overdue');
    }

    public function scopePending($query) {
        return $query->whereIn('status', ['draft', 'sent']);
    }
}
