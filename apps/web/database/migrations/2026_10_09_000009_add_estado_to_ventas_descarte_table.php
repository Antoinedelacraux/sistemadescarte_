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
            if (!Schema::hasColumn('ventas_descarte', 'estado')) {
                $table->string('estado', 20)->default('activo')->after('valor_venta');
            }
            if (!Schema::hasColumn('ventas_descarte', 'anulado_at')) {
                $table->timestamp('anulado_at')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('ventas_descarte', 'anulado_by')) {
                $table->foreignId('anulado_by')->nullable()->constrained('users')->after('anulado_at');
            }
            if (!Schema::hasColumn('ventas_descarte', 'motivo_anulacion')) {
                $table->string('motivo_anulacion', 255)->nullable()->after('anulado_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas_descarte', function (Blueprint $table) {
            if (Schema::hasColumn('ventas_descarte', 'anulado_by')) {
                $table->dropForeign(['anulado_by']);
            }
            $colsToDrop = [];
            foreach (['estado', 'anulado_at', 'anulado_by', 'motivo_anulacion'] as $col) {
                if (Schema::hasColumn('ventas_descarte', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
