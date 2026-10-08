<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->foreignId('lote_id')->nullable()->after('fundo_id')->constrained('lotes')->nullOnDelete();
            $table->foreignId('cuartel_id')->nullable()->after('lote_id')->constrained('cuarteles')->nullOnDelete();
            $table->string('cuartel_manual')->nullable()->after('cuartel_id');
            $table->integer('jabas')->nullable()->after('valor_venta');
            $table->decimal('peso_jaba', 10, 2)->nullable()->after('jabas');
            $table->string('brevete')->nullable()->after('peso_jaba');
            $table->string('ruc')->nullable()->after('brevete');
            $table->string('placa')->nullable()->after('ruc');
            $table->string('conductor')->nullable()->after('placa');
            $table->string('viaje')->nullable()->after('conductor');
            $table->text('observacion')->nullable()->after('viaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->dropForeign(['cuartel_id']);
            $table->dropColumn([
                'lote_id',
                'cuartel_id',
                'cuartel_manual',
                'jabas',
                'peso_jaba',
                'brevete',
                'ruc',
                'placa',
                'conductor',
                'viaje',
                'observacion',
            ]);
        });
    }
};
