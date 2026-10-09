<?php

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Definición exacta de los 3 fundos requeridos y sus lotes
        $fundosConfig = [
            'AGRITAC' => [
                'name' => 'AGRICOLA TAMBO COLORADO (AGRITAC)',
                'code' => 'AGRITAC',
                'lotes' => [
                    'A03', 'A05', 'A06', 'L03', 'L06', 'L07',
                    'M03', 'M05', 'M04', 'M01', 'M02', 'A04',
                    'A02', 'A01', 'L01', 'L02', 'L04', 'L05'
                ],
            ],
            'PROCOM' => [
                'name' => 'AGRICOLA PROCOM (PROCOM)',
                'code' => 'PROCOM',
                'lotes' => [
                    'H01', 'H02', 'H03', 'H04', 'H05', 'H06', 'H07', 'H08', 'H09'
                ],
            ],
            'ELNEGRO' => [
                'name' => 'TALSA GRAPE FARMS (EL NEGRO)',
                'code' => 'ELNEGRO',
                'lotes' => [
                    'N01', 'N03', 'N04', 'N05', 'N06', 'N07', 'N08'
                ],
            ],
        ];

        // 2. Crear o actualizar los 3 fundos reales
        $instanciasFundos = [];
        foreach ($fundosConfig as $key => $config) {
            $fundo = Fundo::where('code', $config['code'])
                ->orWhere('name', $config['name'])
                ->first();

            if (!$fundo) {
                $fundo = Fundo::create([
                    'name' => $config['name'],
                    'code' => $config['code'],
                    'is_active' => true,
                ]);
            } else {
                $fundo->update([
                    'name' => $config['name'],
                    'code' => $config['code'],
                    'is_active' => true,
                ]);
            }

            $instanciasFundos[$key] = $fundo;

            // Crear los lotes exactos para este fundo
            foreach ($config['lotes'] as $nombreLote) {
                Lote::firstOrCreate([
                    'fundo_id' => $fundo->id,
                    'nombre' => $nombreLote,
                ]);
            }
        }

        // 3. Migrar ventas existentes que apuntaban a fundos ficticios hacia los nuevos fundos
        $fundoSofia = Fundo::where('code', 'FSOFIA')->first();
        $fundoElena = Fundo::where('code', 'FELENA')->first();
        $fundoJose = Fundo::where('code', 'FJOSE')->first();

        $primerLoteAgritac = Lote::where('fundo_id', $instanciasFundos['AGRITAC']->id)->first();
        $primerLoteProcom = Lote::where('fundo_id', $instanciasFundos['PROCOM']->id)->first();
        $primerLoteElNegro = Lote::where('fundo_id', $instanciasFundos['ELNEGRO']->id)->first();

        if ($fundoSofia) {
            VentaDescarte::withoutGlobalScopes()
                ->where('fundo_id', $fundoSofia->id)
                ->update([
                    'fundo_id' => $instanciasFundos['AGRITAC']->id,
                    'lote_id' => $primerLoteAgritac?->id,
                ]);
            $fundoSofia->delete();
        }

        if ($fundoElena) {
            VentaDescarte::withoutGlobalScopes()
                ->where('fundo_id', $fundoElena->id)
                ->update([
                    'fundo_id' => $instanciasFundos['PROCOM']->id,
                    'lote_id' => $primerLoteProcom?->id,
                ]);
            $fundoElena->delete();
        }

        if ($fundoJose) {
            VentaDescarte::withoutGlobalScopes()
                ->where('fundo_id', $fundoJose->id)
                ->update([
                    'fundo_id' => $instanciasFundos['ELNEGRO']->id,
                    'lote_id' => $primerLoteElNegro?->id,
                ]);
            $fundoJose->delete();
        }

        // 4. Asignar los 3 fundos a todos los usuarios administradores y analistas
        $todosFundosIds = collect($instanciasFundos)->pluck('id')->toArray();
        $usuariosGlobales = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['admin', 'analista']);
        })->get();

        foreach ($usuariosGlobales as $u) {
            $u->fundos()->syncWithoutDetaching($todosFundosIds);
        }

        // Asignar al menos un fundo real a usuarios existentes con fundos vacíos
        $usuariosRestantes = User::whereDoesntHave('fundos')->get();
        foreach ($usuariosRestantes as $ur) {
            $ur->fundos()->syncWithoutDetaching([$instanciasFundos['AGRITAC']->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructivo
    }
};
