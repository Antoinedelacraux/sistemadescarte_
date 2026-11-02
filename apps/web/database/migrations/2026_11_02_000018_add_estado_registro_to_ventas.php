<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ventas_descarte', 'estado_registro')) {
            Schema::table('ventas_descarte', function (Blueprint $table) {
                // Los registros existentes ya estaban operativos; se conservan como oficiales.
                $table->string('estado_registro', 20)->default('oficial')->after('estado');
                $table->index(['estado_registro', 'estado_garita'], 'idx_ventas_estado_registro_garita');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ventas_descarte', 'estado_registro')) {
            Schema::table('ventas_descarte', function (Blueprint $table) {
                $table->dropIndex('idx_ventas_estado_registro_garita');
                $table->dropColumn('estado_registro');
            });
        }
    }
};
