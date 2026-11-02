<?php

namespace App\Models;

use App\Models\Scopes\FundoScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VentaDescarte extends Model
{
    use HasFactory;

    protected $table = 'ventas_descarte';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'fundo_id',
        'envio_id',
        'lote_id',
        'cuartel_id',
        'cuartel_manual',
        'fecha_produccion',
        'motivo',
        'tipo_descarte',
        'precio',
        'kilogramos',
        'valor_venta',
        'jabas',
        'peso_jaba',
        'brevete',
        'ruc',
        'cliente',
        'placa',
        'conductor',
        'viaje',
        'borrador_sin_vencimiento',
        'observacion',
        'created_by',
        'updated_by',
        'estado',
        'estado_registro',
        'estado_garita',
        'enviado_garita_at',
        'enviado_garita_by',
        'salida_garita_at',
        'salida_garita_by',
        'observacion_garita',
        'anulado_at',
        'anulado_by',
        'motivo_anulacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_produccion' => 'date',
            'precio' => 'decimal:2',
            'kilogramos' => 'decimal:2',
            'valor_venta' => 'decimal:2',
            'jabas' => 'integer',
            'peso_jaba' => 'decimal:2',
            'borrador_sin_vencimiento' => 'boolean',
            'anulado_at' => 'datetime',
            'enviado_garita_at' => 'datetime',
            'salida_garita_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new FundoScope());

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->estado)) {
                $model->estado = 'activo';
            }
            if (empty($model->estado_garita)) {
                $model->estado_garita = 'borrador';
            }
        });
    }

    public function fundo(): BelongsTo
    {
        return $this->belongsTo(Fundo::class);
    }

    public function envio(): BelongsTo
    {
        return $this->belongsTo(Envio::class, 'envio_id');
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function cuartel(): BelongsTo
    {
        return $this->belongsTo(Cuartel::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_by');
    }

    public function enviadoGaritaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_garita_by');
    }

    public function salidaGaritaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salida_garita_by');
    }

    public function isPendienteGarita(): bool
    {
        return ($this->estado_garita ?? 'borrador') === 'pendiente';
    }

    public function isValidadoGarita(): bool
    {
        return ($this->estado_garita ?? 'borrador') === 'validado';
    }

    public function isBorradorGarita(): bool
    {
        return in_array($this->estado_garita ?? 'borrador', ['borrador', null], true);
    }

    public function isBorradorRegistro(): bool
    {
        return ($this->estado_registro ?? 'oficial') === 'borrador';
    }

    public function isRegistroOficial(): bool
    {
        return !$this->isBorradorRegistro();
    }

    public function tieneBorradorSinVencimiento(): bool
    {
        return (bool) $this->borrador_sin_vencimiento && $this->isBorradorGarita();
    }

    public function scopePendientesGarita($query)
    {
        return $query->where('estado_garita', 'pendiente');
    }

    public function scopeValidadosGarita($query)
    {
        return $query->where('estado_garita', 'validado');
    }

    public function isActivo(): bool
    {
        return ($this->estado ?? 'activo') === 'activo';
    }

    public function isAnulado(): bool
    {
        return ($this->estado ?? 'activo') === 'anulado';
    }

    public function scopeActivos($query)
    {
        return $query->where(function($q) {
            $q->where('estado', 'activo')->orWhereNull('estado');
        });
    }

    public function scopeAnulados($query)
    {
        return $query->where('estado', 'anulado');
    }

    /**
     * Determina si la venta superó la ventana de 24 horas desde su creación.
     */
    public function isCerradoParaEdicion(): bool
    {
        if (!$this->created_at) {
            return false;
        }

        return $this->created_at->copy()->addHours(24)->isPast();
    }

    /**
     * Retorna el tiempo restante para editar antes del cierre de 24 horas, o null si ya expiró.
     */
    public function tiempoRestanteEdicion(): ?string
    {
        if (!$this->created_at) {
            return null;
        }

        $limite = $this->created_at->copy()->addHours(24);
        if ($limite->isPast()) {
            return null;
        }

        $horas = (int) now()->diffInHours($limite, false);
        $minutos = (int) now()->diffInMinutes($limite, false) % 60;

        if ($horas > 0) {
            return "{$horas}h {$minutos}m restantes";
        }

        return "{$minutos}m restantes";
    }

    /**
     * Determina si la venta puede ser editada por un usuario dado.
     *
     * Reglas de negocio:
     * 1. Registros anulados nunca pueden ser editados.
     * 2. Administrador: puede editar en cualquier momento sin límite de tiempo.
     * 3. Registrador: solo dentro de las 24 horas posteriores a created_at.
     * 4. Demás usuarios/roles: no pueden editar.
     */
    public function puedeSerEditadoPor(?User $user): bool
    {
        if ($this->isValidadoGarita() || $this->isPendienteGarita()) {
            return false;
        }

        if (!$user || !$this->isActivo()) {
            return false;
        }

        // El Administrador mantiene potestad total de edición sin límite temporal
        if ($user->isAdmin()) {
            return true;
        }

        // Requiere tener acceso al módulo de registro
        if (!$user->canAccessModule('registro')) {
            return false;
        }

        // Para registradores: solo dentro de las primeras 24 horas y mientras no haya sido validado por Garita ni esté pendiente
        if ($this->isValidadoGarita() || $this->isPendienteGarita()) {
            return false;
        }

        return $this->isBorradorRegistro() || $this->tieneBorradorSinVencimiento() || !$this->isCerradoParaEdicion();
    }

    /**
     * Determina si la venta puede ser anulada por un usuario dado.
     *
     * Reglas de negocio:
     * 1. Solo registros activos pueden anularse.
     * 2. Administrador: puede anular en cualquier momento.
     * 3. Registrador: solo dentro de las primeras 24 horas y mientras no haya sido validado por Garita.
     */
    public function puedeSerAnuladoPor(?User $user): bool
    {
        if (!$user || !$this->isActivo()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->canAccessModule('registro')) {
            return false;
        }

        if ($this->isValidadoGarita() || $this->isPendienteGarita()) {
            return false;
        }

        return $this->isBorradorRegistro() || !$this->isCerradoParaEdicion();
    }

    /**
     * Determina si el registrador o admin puede enviar el registro a Garita.
     */
    public function puedeEnviarAGarita(?User $user): bool
    {
        if (!$user || !$this->isActivo()) {
            return false;
        }

        if (!$user->isAdmin() && !$user->canAccessModule('registro')) {
            return false;
        }

        return $this->isRegistroOficial() && $this->isBorradorGarita();
    }

    /** Campos necesarios para que Garita pueda identificar y verificar un envío. */
    public function camposFaltantesGarita(): array
    {
        $faltantes = [];
        foreach (['cliente' => 'cliente', 'conductor' => 'conductor', 'placa' => 'placa', 'viaje' => 'número de viaje'] as $campo => $etiqueta) {
            if (trim((string) $this->{$campo}) === '') {
                $faltantes[] = $etiqueta;
            }
        }
        if (trim((string) $this->cliente) !== '' && !preg_match('/\p{L}/u', (string) $this->cliente)) {
            $faltantes[] = 'cliente válido';
        }
        if (trim((string) $this->conductor) !== '' && !preg_match("/^\\p{L}+(?:[ .'-]+\\p{L}+)+$/u", (string) $this->conductor)) {
            $faltantes[] = 'conductor con nombre y apellido válidos';
        }
        if (trim((string) $this->viaje) !== '' && (!ctype_digit((string) $this->viaje) || (int) $this->viaje < 1)) {
            $faltantes[] = 'número de viaje válido';
        }
        if (trim((string) $this->ruc) !== '' && !preg_match('/^\d{11}$/', (string) $this->ruc)) {
            $faltantes[] = 'RUC de 11 dígitos';
        }
        if ($this->jabas === null || $this->jabas <= 0) {
            $faltantes[] = 'cantidad de jabas mayor que cero';
        }
        if ($this->kilogramos === null || $this->kilogramos <= 0) {
            $faltantes[] = 'kilogramos mayores que cero';
        }
        if (!$this->fundo_id || !$this->lote_id || !$this->fecha_produccion) {
            $faltantes[] = 'fundo, lote y fecha de producción';
        }
        if ($this->fecha_produccion && $this->fecha_produccion->isFuture()) {
            $faltantes[] = 'fecha de producción no futura';
        }

        return $faltantes;
    }

    /**
     * Determina si se puede cancelar el envío a Garita para retornar a borrador y permitir edición.
     */
    public function puedeCancelarEnvioGarita(?User $user): bool
    {
        if (!$user || !$this->isActivo()) {
            return false;
        }

        if (!$user->isAdmin() && !$user->canAccessModule('registro')) {
            return false;
        }

        // Solo se puede cancelar si está pendiente y todavía NO ha sido validado en Garita
        return $this->isPendienteGarita();
    }
}
