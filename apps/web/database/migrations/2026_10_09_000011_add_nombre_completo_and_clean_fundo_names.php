<?php

use App\Models\Fundo;
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
        // 1. Agregar columna nombre_completo a la tabla fundos si no existe
        if (!Schema::hasColumn('fundos', 'nombre_completo')) {
            Schema::table('fundos', function (Blueprint $table) {
                $table->string('nombre_completo')->nullable()->after('name');
            });
        }

        // 2. Actualizar los 3 fundos oficiales con sus nombres limpios:
        //    - name (nombre de fundo / registro / tablas / headbar): AGRITAC, PROCOM, EL NEGRO
        //    - nombre_completo (razón social / tarjeta de bienvenida): AGRICOLA TAMBO COLORADO, etc.
        $mapeo = [
            'AGRITAC' => [
                'name' => 'AGRITAC',
                'nombre_completo' => 'AGRICOLA TAMBO COLORADO',
                'code' => 'AGRITAC',
            ],
            'PROCOM' => [
                'name' => 'PROCOM',
                'nombre_completo' => 'AGRICOLA PROCOM',
                'code' => 'PROCOM',
            ],
            'ELNEGRO' => [
                'name' => 'EL NEGRO',
                'nombre_completo' => 'TALSA GRAPE FARMS',
                'code' => 'ELNEGRO',
            ],
        ];

        foreach ($mapeo as $code => $data) {
            $fundo = Fundo::where('code', $code)
                ->orWhere('name', 'LIKE', "%{$code}%")
                ->first();

            if ($fundo) {
                $fundo->update([
                    'name' => $data['name'],
                    'nombre_completo' => $data['nombre_completo'],
                    'code' => $data['code'],
                ]);
            }
        }

        // También limpiar cualquier fundo residual que contenga paréntesis
        $fundosConParentesis = Fundo::where('name', 'LIKE', '%(%')->get();
        foreach ($fundosConParentesis as $f) {
            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $f->name, $matches)) {
                $largo = trim($matches[1]);
                $corto = trim($matches[2]);
                $f->update([
                    'name' => $corto,
                    'nombre_completo' => $largo,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('fundos', 'nombre_completo')) {
            Schema::table('fundos', function (Blueprint $table) {
                $table->dropColumn('nombre_completo');
            });
        }
    }
};
