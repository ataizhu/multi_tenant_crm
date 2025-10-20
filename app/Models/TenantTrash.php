<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantTrash extends Model {
    use HasFactory;

    protected $table = 'tenant_trash';

    protected $fillable = [
        'tenant_id',
        'deleted_by',
        'deleted_at',
        'deletion_reason',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Связь с тенантом
     */
    public function tenant(): BelongsTo {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Связь с пользователем, который удалил
     */
    public function deletedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
