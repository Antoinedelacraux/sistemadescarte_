<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('codigo', 80)->unique();
            $table->foreignId('fundo_id')->constrained('fundos')->restrictOnDelete();
            $table->date('fecha_produccion');
            $table->unsignedInteger('numero_viaje');
            $table->string('cliente', 150)->nullable();
            $table->string('placa', 20)->nullable();
            $table->string('conductor', 150)->nullable();
            $table->string('estado_garita', 20)->default('borrador');
            $table->timestamp('enviado_garita_at')->nullable();
            $table->timestamp('salida_garita_at')->nullable();
            $table->foreignId('enviado_garita_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('salida_garita_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacion_garita')->nullable();
            $table->timestamps();

            $table->unique(['fundo_id', 'fecha_produccion', 'numero_viaje'], 'uq_envios_fundo_fecha_viaje');
            $table->index(['fecha_produccion', 'estado_garita'], 'idx_envios_fecha_estado');
        });

        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->foreignUuid('envio_id')->nullable()->after('fundo_id')->constrained('envios')->nullOnDelete();
            $table->index('envio_id', 'idx_ventas_envio');
        });
    }

    public function down(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            $table->dropIndex('idx_ventas_envio');
            $table->dropForeign(['envio_id']);
            $table->dropColumn('envio_id');
        });
        Schema::dropIfExists('envios');
    }
};
