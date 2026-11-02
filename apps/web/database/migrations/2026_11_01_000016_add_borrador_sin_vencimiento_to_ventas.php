<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas_descarte', 'borrador_sin_vencimiento')) {
            Schema::table('ventas_descarte', function (Blueprint $table) {
                $table->boolean('borrador_sin_vencimiento')->default(false);
            });
        }
    }

    // Quitar la columna destruiría información sobre borradores creados tras la migración.
    // La reversión segura requiere restaurar un respaldo verificado.
    public function down(): void
    {
        throw new \RuntimeException('Reversion no automatica: restaurar un respaldo verificado.');
    }
};
