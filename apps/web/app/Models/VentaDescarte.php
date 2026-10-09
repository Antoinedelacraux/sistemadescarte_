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
        'observacion',
        'created_by',
        'updated_by',
        'estado',
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
            'anulado_at' => 'datetime',
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
        });
    }

    public function fundo(): BelongsTo
    {
        return $this->belongsTo(Fundo::class);
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
}
