<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuartel extends Model
{
    use HasFactory;

    protected $table = 'cuarteles';

    protected $fillable = [
        'lote_id',
        'nombre',
    ];

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
