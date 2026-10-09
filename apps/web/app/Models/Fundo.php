<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fundo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nombre_completo',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Obtener el nombre completo o razón social del fundo (ej. AGRICOLA TAMBO COLORADO).
     */
    public function getNombreCompletoAttribute(): string
    {
        $val = $this->attributes['nombre_completo'] ?? null;
        if (!empty($val)) {
            return $val;
        }

        $code = strtoupper(trim($this->attributes['code'] ?? ''));
        $name = strtoupper(trim($this->attributes['name'] ?? ''));

        if ($code === 'AGRITAC' || str_contains($name, 'AGRITAC') || str_contains($name, 'COLORADO')) {
            return 'AGRICOLA TAMBO COLORADO';
        }
        if ($code === 'PROCOM' || str_contains($name, 'PROCOM')) {
            return 'AGRICOLA PROCOM';
        }
        if ($code === 'ELNEGRO' || str_contains($name, 'EL NEGRO') || str_contains($name, 'TALSA')) {
            return 'TALSA GRAPE FARMS';
        }

        return $this->attributes['name'] ?? '';
    }

    /**
     * Obtener el nombre corto del fundo (ej. AGRITAC, PROCOM, EL NEGRO).
     */
    public function getNombreCortoAttribute(): string
    {
        $name = trim($this->attributes['name'] ?? '');
        if (preg_match('/\((.*?)\)/', $name, $matches)) {
            return trim($matches[1]);
        }
        return $name ?: ($this->attributes['code'] ?? '');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'fundo_user')->withTimestamps();
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(VentaDescarte::class);
    }
}
