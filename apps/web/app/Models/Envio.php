<?php

namespace App\Models;

use App\Models\Scopes\FundoScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Envio extends Model
{
    use HasFactory;

    protected $table = 'envios';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'codigo', 'fundo_id', 'fecha_produccion', 'numero_viaje',
        'cliente', 'placa', 'conductor', 'estado_garita',
        'enviado_garita_at', 'salida_garita_at', 'enviado_garita_by',
        'salida_garita_by', 'observacion_garita',
    ];

    protected $casts = [
        'fecha_produccion' => 'date',
        'numero_viaje' => 'integer',
        'enviado_garita_at' => 'datetime',
        'salida_garita_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FundoScope());
        static::creating(function (self $envio) {
            $envio->id ??= (string) Str::uuid();
        });
    }

    public function fundo(): BelongsTo { return $this->belongsTo(Fundo::class); }
    public function ventas(): HasMany { return $this->hasMany(VentaDescarte::class, 'envio_id'); }

    public function totalKilos(): float { return (float) $this->ventas()->sum('kilogramos'); }
    public function totalJabas(): int { return (int) $this->ventas()->sum('jabas'); }
}
