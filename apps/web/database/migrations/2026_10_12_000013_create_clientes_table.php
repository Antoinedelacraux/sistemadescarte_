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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('ruc', 20)->nullable()->index();
            $table->timestamps();
        });

        // Migración de datos existentes: importar clientes únicos desde ventas_descarte
        if (Schema::hasTable('ventas_descarte')) {
            $clientesExistentes = DB::table('ventas_descarte')
                ->whereNotNull('cliente')
                ->where('cliente', '!=', '')
                ->select('cliente', DB::raw('MAX(ruc) as ruc'))
                ->groupBy('cliente')
                ->get();

            foreach ($clientesExistentes as $item) {
                $nombre = trim((string) $item->cliente);
                if ($nombre !== '') {
                    DB::table('clientes')->updateOrInsert(
                        ['nombre' => $nombre],
                        [
                            'ruc' => !empty($item->ruc) ? trim((string) $item->ruc) : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
