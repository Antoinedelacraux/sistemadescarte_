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
        Schema::create('ventas_descarte', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('fundo_id')->constrained('fundos')->cascadeOnDelete();
            $table->date('fecha_produccion');
            $table->string('motivo'); // Campo, Packing, Cosecha Nacional
            $table->string('tipo_descarte'); // Racimos, Racimos con plaga, Granos
            $table->decimal('precio', 10, 2);
            $table->decimal('kilogramos', 10, 2);
            $table->decimal('valor_venta', 10, 2);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_descarte');
    }
};
