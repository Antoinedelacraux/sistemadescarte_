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

    public const MODULOS = [
        'panel' => 'Panel de Control',
        'registro' => 'Registrar Venta',
        'historial' => 'Historial de Ventas',
        'admin' => 'Administración',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
        'modulos_personalizados',
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
            'modulos_personalizados' => 'array',
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

    public function isJefeFundo(): bool
    {
        return in_array($this->role?->name, [Role::JEFE_FUNDO, 'general'], true);
    }

    public function isRegistrador(): bool
    {
        return in_array($this->role?->name, [Role::REGISTRADOR, 'individual'], true);
    }

    public function isAnalista(): bool
    {
        return $this->role?->name === Role::ANALISTA;
    }

    public function isVentas(): bool
    {
        return $this->role?->name === Role::VENTAS;
    }

    // Métodos alias para retrocompatibilidad
    public function isGeneral(): bool
    {
        return $this->isJefeFundo();
    }

    public function isIndividual(): bool
    {
        return $this->isRegistrador();
    }

    /**
     * Determina si el usuario tiene acceso irrestricto a todos los fundos del sistema.
     * Roles con acceso a todos los fundos: Administrador, Analista, Ventas.
     * Jefe de fundo y Registrador están restringidos a sus fundos asignados.
     */
    public function hasAccessToAllFundos(): bool
    {
        return $this->isAdmin() || $this->isAnalista() || $this->isVentas();
    }

    public function hasFundoAccess(int $fundoId): bool
    {
        if ($this->hasAccessToAllFundos()) {
            return true;
        }

        return $this->fundos()->where('fundos.id', $fundoId)->exists();
    }

    /**
     * Módulos predeterminados asignados a cada rol por defecto.
     */
    public static function getModulosPorDefecto(?string $roleName): array
    {
        return match ($roleName) {
            Role::ADMIN => ['panel', 'registro', 'historial', 'admin'],
            Role::JEFE_FUNDO, 'general' => ['panel', 'historial'],
            Role::REGISTRADOR, 'individual' => ['panel', 'registro', 'historial'],
            Role::ANALISTA => ['historial'],
            Role::VENTAS => ['historial'],
            default => [],
        };
    }

    /**
     * Retorna la lista de módulos habilitados para este usuario concreto.
     * Si el usuario tiene una configuración personalizada (caso excepcional), se usa esa.
     * De lo contrario, se aplican los módulos por defecto de su rol.
     */
    public function getModulosHabilitados(): array
    {
        if (is_array($this->modulos_personalizados)) {
            return $this->modulos_personalizados;
        }

        return self::getModulosPorDefecto($this->role?->name);
    }

    /**
     * Verifica si el usuario tiene acceso a un módulo específico.
     */
    public function canAccessModule(string $modulo): bool
    {
        return in_array($modulo, $this->getModulosHabilitados(), true);
    }

    /**
     * Indica si el usuario cuenta con una excepción personalizada en sus módulos.
     */
    public function hasCustomModules(): bool
    {
        return is_array($this->modulos_personalizados);
    }
}
