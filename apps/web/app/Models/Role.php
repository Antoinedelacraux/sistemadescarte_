<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ADMIN = 'admin';
    public const JEFE_FUNDO = 'jefe_fundo';
    public const REGISTRADOR = 'registrador';
    public const ANALISTA = 'analista';
    public const VENTAS = 'ventas';

    // Constantes retrocompatibles
    public const GENERAL = 'jefe_fundo';
    public const INDIVIDUAL = 'registrador';

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
