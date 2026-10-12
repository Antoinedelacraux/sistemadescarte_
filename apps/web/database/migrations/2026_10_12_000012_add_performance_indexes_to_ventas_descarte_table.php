<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Crea índices optimizados para consultas de alta velocidad en Dashboard, Historial y Reportes.
     */
    public function up(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            // Índice para ordenamiento cronológico por fecha de producción
            $table->index('fecha_produccion', 'idx_ventas_fecha_produccion');

            // Índice compuesto para filtros por fundo y fecha (aislamiento FundoScope)
            $table->index(['fundo_id', 'fecha_produccion'], 'idx_ventas_fundo_fecha');

            // Índice compuesto para filtros por estado y fecha
            $table->index(['estado', 'fecha_produccion'], 'idx_ventas_estado_fecha');

            // Índice compuesto para reportes y segregación por motivo y tipo
            $table->index(['motivo', 'tipo_descarte'], 'idx_ventas_motivo_tipo');

            // Índice para agrupaciones y búsquedas reactivas por cliente
            $table->index('cliente', 'idx_ventas_cliente');
        });

        Schema::table('lotes', function (Blueprint $table) {
            // Índice para resolución ultrarrápida de lotes por fundo
            $table->index(['fundo_id', 'nombre'], 'idx_lotes_fundo_nombre');
        });

        Schema::table('cuarteles', function (Blueprint $table) {
            // Índice para resolución ultrarrápida de cuarteles por lote
            $table->index(['lote_id', 'nombre'], 'idx_cuarteles_lote_nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_fecha_produccion');
            $table->dropIndex('idx_ventas_fundo_fecha');
            $table->dropIndex('idx_ventas_estado_fecha');
            $table->dropIndex('idx_ventas_motivo_tipo');
            $table->dropIndex('idx_ventas_cliente');
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_fundo_nombre');
        });

        Schema::table('cuarteles', function (Blueprint $table) {
            $table->dropIndex('idx_cuarteles_lote_nombre');
        });
    }
};
