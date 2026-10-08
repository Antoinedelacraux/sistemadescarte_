<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function fundos(): BelongsToMany
    {
        return $this->belongsToMany(Fundo::class, 'fundo_user')->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
    }

    public function isGeneral(): bool
    {
        return $this->role?->name === Role::GENERAL;
    }

    public function isIndividual(): bool
    {
        return $this->role?->name === Role::INDIVIDUAL;
    }

    public function isAnalista(): bool
    {
        return $this->role?->name === Role::ANALISTA;
    }

    public function hasFundoAccess(int $fundoId): bool
    {
        if ($this->isAdmin() || $this->isAnalista()) {
            return true;
        }

        return $this->fundos()->where('fundos.id', $fundoId)->exists();
    }
}
