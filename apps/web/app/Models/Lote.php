<?php

namespace App\Models;

use App\Models\Scopes\FundoScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lote extends Model
{
    use HasFactory;

    protected $table = 'lotes';

    protected $fillable = [
        'fundo_id',
        'nombre',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new FundoScope());
    }

    public function fundo(): BelongsTo
    {
        return $this->belongsTo(Fundo::class);
    }

    public function cuarteles(): HasMany
    {
        return $this->hasMany(Cuartel::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaDescarte::class);
    }
}
