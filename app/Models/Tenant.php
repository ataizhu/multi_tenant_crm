<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'locale',
        'email',
        'phone',
        'address',
        'contact_person',
        'database',
        'settings',
        'status',
        'deleted',
    ];

    protected $casts = [
        'settings' => 'array',
        'deleted' => 'boolean',
    ];

    /**
     * Связь с пользователями тенанта
     */
    public function tenantUsers(): HasMany {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Связь с пользователями
     */
    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    /**
     * Связь с корзиной
     */
    public function trash(): HasOne {
        return $this->hasOne(TenantTrash::class);
    }

    /**
     * Scope для активных тенантов (не удаленных)
     */
    public function scopeActive($query) {
        return $query->where('deleted', false);
    }

    /**
     * Scope для удаленных тенантов
     */
    public function scopeDeleted($query) {
        return $query->where('deleted', true);
    }

    /**
     * Проверить, удален ли тенант
     */
    public function isDeleted(): bool {
        return $this->deleted;
    }

    /**
     * Пометить тенанта как удаленного
     */
    public function markAsDeleted(): void {
        $this->update(['deleted' => true]);
    }

    /**
     * Восстановить тенанта
     */
    public function restore(): void {
        $this->update(['deleted' => false]);
    }
}
