<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Columnas para el ciclo de Garita en ventas_descarte
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->string('estado_garita', 20)->default('borrador')->after('estado');
            $table->timestamp('enviado_garita_at')->nullable()->after('estado_garita');
            $table->foreignId('enviado_garita_by')->nullable()->after('enviado_garita_at')->constrained('users')->nullOnDelete();
            $table->timestamp('salida_garita_at')->nullable()->after('enviado_garita_by');
            $table->foreignId('salida_garita_by')->nullable()->after('salida_garita_at')->constrained('users')->nullOnDelete();
            $table->text('observacion_garita')->nullable()->after('salida_garita_by');

            $table->index(['fundo_id', 'estado_garita', 'fecha_produccion'], 'idx_ventas_garita_fundo_fecha');
            $table->index(['fecha_produccion', 'viaje', 'placa'], 'idx_ventas_garita_viaje');
        });

        // 2. Rol Garita en la tabla roles
        $garitaRole = DB::table('roles')->where('name', 'garita')->first();
        if (!$garitaRole) {
            DB::table('roles')->insert([
                'name' => 'garita',
                'display_name' => 'Garita',
                'description' => 'Control y confirmación de salida de camiones en garita de control',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_garita_fundo_fecha');
            $table->dropIndex('idx_ventas_garita_viaje');

            $table->dropConstrainedForeignId('enviado_garita_by');
            $table->dropConstrainedForeignId('salida_garita_by');
            $table->dropColumn([
                'estado_garita',
                'enviado_garita_at',
                'salida_garita_at',
                'observacion_garita',
            ]);
        });

        DB::table('roles')->where('name', 'garita')->delete();
    }
};

