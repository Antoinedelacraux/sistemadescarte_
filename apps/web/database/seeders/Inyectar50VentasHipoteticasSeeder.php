<?php

namespace Database\Seeders;

use App\Models\Fundo;
use App\Models\Lote;
use App\Models\User;
use App\Models\VentaDescarte;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class Inyectar50VentasHipoteticasSeeder extends Seeder
{
    /**
     * Inyecta 50 registros coherentes de ventas de descarte
     * abarcando desde hace 30 días hasta el día de hoy (2026-10-09).
     */
    public function run(): void
    {
        // 1. Obtener usuario creador por defecto
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Marco Chumbes',
                'email' => 'admin@fundo.test',
                'password' => bcrypt('password123'),
                'is_active' => true,
            ]);
        }

        // 2. Obtener fundos oficiales
        $fundoAgritac = Fundo::firstOrCreate(
            ['code' => 'AGRITAC'],
            ['name' => 'AGRITAC', 'nombre_completo' => 'AGRICOLA TAMBO COLORADO', 'is_active' => true]
        );
        $fundoProcom = Fundo::firstOrCreate(
            ['code' => 'PROCOM'],
            ['name' => 'PROCOM', 'nombre_completo' => 'AGRICOLA PROCOM', 'is_active' => true]
        );
        $fundoElNegro = Fundo::firstOrCreate(
            ['code' => 'ELNEGRO'],
            ['name' => 'EL NEGRO', 'nombre_completo' => 'TALSA GRAPE FARMS', 'is_active' => true]
        );

        // Asociar al usuario a todos los fundos si no los tiene
        $user->fundos()->syncWithoutDetaching([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        // 3. Catálogo de lotes por fundo
        $lotesAgritac = Lote::withoutGlobalScopes()->where('fundo_id', $fundoAgritac->id)->get();
        if ($lotesAgritac->isEmpty()) {
            foreach (['A01', 'A02', 'A03', 'A04', 'L01', 'L02', 'M01', 'M02', 'M03'] as $nom) {
                $lotesAgritac->push(Lote::withoutGlobalScopes()->create(['fundo_id' => $fundoAgritac->id, 'nombre' => $nom]));
            }
        }

        $lotesProcom = Lote::withoutGlobalScopes()->where('fundo_id', $fundoProcom->id)->get();
        if ($lotesProcom->isEmpty()) {
            foreach (['H01', 'H02', 'H03', 'H04', 'H05', 'H06'] as $nom) {
                $lotesProcom->push(Lote::withoutGlobalScopes()->create(['fundo_id' => $fundoProcom->id, 'nombre' => $nom]));
            }
        }

        $lotesElNegro = Lote::withoutGlobalScopes()->where('fundo_id', $fundoElNegro->id)->get();
        if ($lotesElNegro->isEmpty()) {
            foreach (['N01', 'N03', 'N04', 'N05', 'N06'] as $nom) {
                $lotesElNegro->push(Lote::withoutGlobalScopes()->create(['fundo_id' => $fundoElNegro->id, 'nombre' => $nom]));
            }
        }

        // 4. Clientes y transportistas realistas del sector agrícola
        $clientes = [
            ['nombre' => 'FRUTAS DEL VALLE S.A.C.', 'ruc' => '20518923451'],
            ['nombre' => 'COMERCIALIZADORA AGRICOLA ICA S.R.L.', 'ruc' => '20601452398'],
            ['nombre' => 'AGROEXPORTADORA DEL SUR S.A.', 'ruc' => '20489123847'],
            ['nombre' => 'DISTRIBUIDORA SANTA ROSA E.I.R.L.', 'ruc' => '20556789123'],
            ['nombre' => 'MERCADO MAYORISTA DE FRUTAS LIMA', 'ruc' => '20109876543'],
            ['nombre' => 'JOSÉ CARLOS RAMOS MENDOZA', 'ruc' => '10458923187'],
            ['nombre' => 'FRUTÍCOLA CHINCHA S.A.C.', 'ruc' => '20603847192'],
            ['nombre' => 'AGROINDUSTRIAS CASABLANCA S.A.', 'ruc' => '20451298374'],
        ];

        $transportistas = [
            ['placa' => 'AYZ-891', 'conductor' => 'Carlos Mendoza Quispe', 'brevete' => 'Q45129834'],
            ['placa' => 'C4K-712', 'conductor' => 'Jorge Luis Huamán Ramos', 'brevete' => 'A78945123'],
            ['placa' => 'T8M-943', 'conductor' => 'Manuel Rojas Díaz', 'brevete' => 'B12389475'],
            ['placa' => 'B7F-218', 'conductor' => 'Pedro Cárdenas Silva', 'brevete' => 'Q56473829'],
            ['placa' => 'F1P-405', 'conductor' => 'Roberto Ramos Castro', 'brevete' => 'A90817263'],
            ['placa' => 'D9W-382', 'conductor' => 'Luis Alberto Flores Pérez', 'brevete' => 'B34125678'],
        ];

        // 5. Generación de los 50 registros distribuidos en 30 días
        $hoy = Carbon::create(2026, 10, 9);
        $fechaInicio = Carbon::create(2026, 9, 10);
        $diasTotales = $fechaInicio->diffInDays($hoy);

        $this->command?->info("Iniciando inyección de 50 registros coherentes desde {$fechaInicio->format('d/m/Y')} hasta {$hoy->format('d/m/Y')}...");

        $fundosPool = [
            ['fundo' => $fundoAgritac, 'lotes' => $lotesAgritac],
            ['fundo' => $fundoAgritac, 'lotes' => $lotesAgritac], // Mayor peso a Agritac
            ['fundo' => $fundoProcom,  'lotes' => $lotesProcom],
            ['fundo' => $fundoProcom,  'lotes' => $lotesProcom],
            ['fundo' => $fundoElNegro, 'lotes' => $lotesElNegro],
        ];

        $motivosPool = [
            'Cosecha Nacional',
            'Cosecha Nacional',
            'Campo',
            'Campo',
            'Packing',
        ];

        for ($i = 1; $i <= 50; $i++) {
            // Distribuir fechas de forma creciente a lo largo del mes
            $diaOffset = (int) floor(($i - 1) * ($diasTotales / 49));
            $fechaRegistro = (clone $fechaInicio)->addDays($diaOffset);

            // Seleccionar fundo y lote coherente
            $fundoData = $fundosPool[$i % count($fundosPool)];
            $fundo = $fundoData['fundo'];
            $lote = $fundoData['lotes']->random();

            // Motivo y regla de tipos de descarte
            $motivo = $motivosPool[$i % count($motivosPool)];
            if ($motivo === 'Cosecha Nacional') {
                $tipo = ($i % 2 === 0) ? 'Racimos' : 'Granos';
                $cuartel = 'Cuartel ' . (($i % 4) + 1) . chr(65 + ($i % 3)); // Ej: Cuartel 1A, Cuartel 2B
                $precio = round(1.80 + (($i % 7) * 0.10), 2); // S/ 1.80 a S/ 2.40
            } elseif ($motivo === 'Packing') {
                $tipo = ($i % 3 === 0) ? 'Granos' : 'Racimos';
                $cuartel = ($i % 2 === 0) ? ('Cuartel ' . (($i % 3) + 1)) : null;
                $precio = round(1.30 + (($i % 6) * 0.10), 2); // S/ 1.30 a S/ 1.80
            } else { // Campo
                $tiposCampo = ['Racimos', 'Racimos con plaga', 'Granos'];
                $tipo = $tiposCampo[$i % count($tiposCampo)];
                $cuartel = ($i % 3 === 0) ? ('Cuartel ' . (($i % 5) + 1)) : null;
                $precio = round(0.90 + (($i % 8) * 0.10), 2); // S/ 0.90 a S/ 1.60
            }

            // Pesaje y jabas
            $jabas = rand(15, 95);
            $pesoPromedioJaba = round(20.00 + (rand(-150, 150) / 100), 2); // 18.50 a 21.50 kg
            $kilogramos = round($jabas * $pesoPromedioJaba, 2);
            $valorVenta = round($kilogramos * $precio, 2);

            // Cliente y transporte
            $cliente = $clientes[$i % count($clientes)];
            $transporte = $transportistas[$i % count($transportistas)];

            // Estado del registro: 46 activos, 4 anulados para probar filtros y ciclo de vida
            $esAnulado = in_array($i, [7, 19, 34, 46]);
            $estado = $esAnulado ? 'anulado' : 'activo';
            $anuladoAt = $esAnulado ? (clone $fechaRegistro)->addHours(4) : null;
            $motivoAnulacion = $esAnulado ? match ($i) {
                7 => 'Pesaje duplicado por reinicio de balanza electrónica en garita.',
                19 => 'Vehículo no completó tara por fallo mecánico en tolva.',
                34 => 'Reclasificación de lote por inspector de calidad de campo.',
                default => 'Registro cancelado a solicitud de jefatura de fundo.'
            } : null;

            $observacion = match (true) {
                $motivo === 'Cosecha Nacional' => "Venta aprobada Cosecha Nacional. Calidad de fruta conforme.",
                $tipo === 'Racimos con plaga' => "Descarte clasificado de campo con plaga leve. No apto para packing.",
                $tipo === 'Granos' => "Granos sueltos recolectados en pesaje para venta local.",
                default => "Despacho regular en camión autorizado. Guía de salida validada."
            };

            VentaDescarte::withoutGlobalScopes()->create([
                'id' => (string) Str::uuid(),
                'fundo_id' => $fundo->id,
                'lote_id' => $lote->id,
                'cuartel_manual' => $cuartel,
                'fecha_produccion' => $fechaRegistro->format('Y-m-d'),
                'motivo' => $motivo,
                'tipo_descarte' => $tipo,
                'precio' => $precio,
                'kilogramos' => $kilogramos,
                'valor_venta' => $valorVenta,
                'jabas' => $jabas,
                'peso_jaba' => $pesoPromedioJaba,
                'cliente' => $cliente['nombre'],
                'ruc' => $cliente['ruc'],
                'placa' => $transporte['placa'],
                'conductor' => $transporte['conductor'],
                'brevete' => $transporte['brevete'],
                'viaje' => 'Viaje ' . (($i % 4) + 1),
                'observacion' => $observacion,
                'estado' => $estado,
                'anulado_at' => $anuladoAt,
                'anulado_by' => $esAnulado ? $user->id : null,
                'motivo_anulacion' => $motivoAnulacion,
                'created_by' => $user->id,
                'created_at' => (clone $fechaRegistro)->setHour(8 + ($i % 10))->setMinute(15 + ($i % 40)),
                'updated_at' => (clone $fechaRegistro)->setHour(8 + ($i % 10))->setMinute(15 + ($i % 40)),
            ]);
        }

        $this->command?->info("¡Éxito! Se inyectaron 50 registros coherentes de ventas de descarte en el sistema.");
    }
}
