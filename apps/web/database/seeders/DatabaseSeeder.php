<?php

namespace Database\Seeders;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with fictitious initial data.
     */
    public function run(): void
    {
        // 1. Roles del sistema
        $roleAdmin = Role::create([
            'name' => Role::ADMIN,
            'display_name' => 'Administrador',
            'description' => 'Acceso total y configuración del sistema',
        ]);

        $roleGeneral = Role::create([
            'name' => Role::GENERAL,
            'display_name' => 'General (Jefe de Fundo)',
            'description' => 'Visualización de todos los registros de su fundo asignado',
        ]);

        $roleIndividual = Role::create([
            'name' => Role::INDIVIDUAL,
            'display_name' => 'Individual (Pesador / Registrador)',
            'description' => 'Registro y visualización en su fundo asignado',
        ]);

        $roleAnalista = Role::create([
            'name' => Role::ANALISTA,
            'display_name' => 'Analista',
            'description' => 'Visualización y exportación de datos de todos los fundos',
        ]);

        // 2. Fundos Reales del Sistema
        $fundoAgritac = Fundo::create([
            'name' => 'AGRICOLA TAMBO COLORADO (AGRITAC)',
            'code' => 'AGRITAC',
            'is_active' => true,
        ]);

        $fundoProcom = Fundo::create([
            'name' => 'AGRICOLA PROCOM (PROCOM)',
            'code' => 'PROCOM',
            'is_active' => true,
        ]);

        $fundoElNegro = Fundo::create([
            'name' => 'TALSA GRAPE FARMS (EL NEGRO)',
            'code' => 'ELNEGRO',
            'is_active' => true,
        ]);

        // 3. Usuarios de prueba con datos ficticios
        $defaultPassword = Hash::make('password123');

        $admin = User::create([
            'name' => 'Administrador General',
            'email' => 'admin@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);
        $admin->fundos()->attach([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        $generalSofia = User::create([
            'name' => 'Jefe Fundo Agritac',
            'email' => 'general.agritac@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleGeneral->id,
            'is_active' => true,
        ]);
        $generalSofia->fundos()->attach([$fundoAgritac->id]);

        $generalElena = User::create([
            'name' => 'Jefe Fundo Procom',
            'email' => 'general.procom@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleGeneral->id,
            'is_active' => true,
        ]);
        $generalElena->fundos()->attach([$fundoProcom->id]);

        $individualSofia = User::create([
            'name' => 'Registrador Agritac',
            'email' => 'individual.agritac@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
        $individualSofia->fundos()->attach([$fundoAgritac->id]);

        $analista = User::create([
            'name' => 'Analista Central',
            'email' => 'analista@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleAnalista->id,
            'is_active' => true,
        ]);
        $analista->fundos()->attach([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        // 4. Catálogos: Lotes exactos por Fundo
        $lotesAgritac = [
            'A03', 'A05', 'A06', 'L03', 'L06', 'L07',
            'M03', 'M05', 'M04', 'M01', 'M02', 'A04',
            'A02', 'A01', 'L01', 'L02', 'L04', 'L05'
        ];
        $instanciasLotesAgritac = [];
        foreach ($lotesAgritac as $nombreLote) {
            $instanciasLotesAgritac[$nombreLote] = \App\Models\Lote::withoutGlobalScopes()->create([
                'fundo_id' => $fundoAgritac->id,
                'nombre' => $nombreLote,
            ]);
        }

        $lotesProcom = ['H01', 'H02', 'H03', 'H04', 'H05', 'H06', 'H07', 'H08', 'H09'];
        $instanciasLotesProcom = [];
        foreach ($lotesProcom as $nombreLote) {
            $instanciasLotesProcom[$nombreLote] = \App\Models\Lote::withoutGlobalScopes()->create([
                'fundo_id' => $fundoProcom->id,
                'nombre' => $nombreLote,
            ]);
        }

        $lotesElNegro = ['N01', 'N03', 'N04', 'N05', 'N06', 'N07', 'N08'];
        $instanciasLotesElNegro = [];
        foreach ($lotesElNegro as $nombreLote) {
            $instanciasLotesElNegro[$nombreLote] = \App\Models\Lote::withoutGlobalScopes()->create([
                'fundo_id' => $fundoElNegro->id,
                'nombre' => $nombreLote,
            ]);
        }

        // 5. Registros ficticios para validar aislamiento entre fundos y tablas de análisis
        $primerLoteAgritac = $instanciasLotesAgritac['A01'];
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoAgritac->id,
            'lote_id' => $primerLoteAgritac->id,
            'cuartel_manual' => 'Cuartel 1A',
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 120.00,
            'valor_venta' => 180.00,
            'jabas' => 6,
            'peso_jaba' => 20.00,
            'ruc' => '20554433221',
            'cliente' => 'Agroexport del Sur SAC',
            'placa' => 'ABC-123',
            'conductor' => 'Juan Pérez',
            'created_by' => $individualSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia1->id,
            'cuartel_manual' => 'Cuartel 1A',
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.80,
            'kilogramos' => 250.00,
            'valor_venta' => 450.00,
            'jabas' => 12,
            'peso_jaba' => 20.83,
            'ruc' => '20554433221',
            'cliente' => 'Agroexport del Sur SAC',
            'placa' => 'ABC-123',
            'conductor' => 'Juan Pérez',
            'created_by' => $individualSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia2->id,
            'cuartel_manual' => 'Cuartel 2A',
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Granos',
            'precio' => 1.20,
            'kilogramos' => 180.00,
            'valor_venta' => 216.00,
            'jabas' => 9,
            'peso_jaba' => 20.00,
            'ruc' => '20601234567',
            'cliente' => 'Distribuidora Frutas del Norte',
            'placa' => 'T1B-456',
            'conductor' => 'Mario Vargas',
            'created_by' => $generalSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia2->id,
            'cuartel_manual' => 'Cuartel 2B',
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos con plaga',
            'precio' => 0.85,
            'kilogramos' => 310.00,
            'valor_venta' => 263.50,
            'jabas' => 15,
            'peso_jaba' => 20.67,
            'ruc' => '20601234567',
            'cliente' => 'Distribuidora Frutas del Norte',
            'placa' => 'T1B-456',
            'conductor' => 'Mario Vargas',
            'created_by' => $individualSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia1->id,
            'cuartel_manual' => 'Cuartel 1B',
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Racimos',
            'precio' => 2.10,
            'kilogramos' => 150.00,
            'valor_venta' => 315.00,
            'jabas' => 7,
            'peso_jaba' => 21.43,
            'ruc' => '20459876543',
            'cliente' => 'Frutas del Valle EIRL',
            'placa' => 'M9K-321',
            'conductor' => 'Luis Mendoza',
            'created_by' => $generalSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia2->id,
            'cuartel_manual' => 'Cuartel 2B',
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Granos',
            'precio' => 1.40,
            'kilogramos' => 200.00,
            'valor_venta' => 280.00,
            'jabas' => 10,
            'peso_jaba' => 20.00,
            'ruc' => '20459876543',
            'cliente' => 'Frutas del Valle EIRL',
            'placa' => 'M9K-321',
            'conductor' => 'Luis Mendoza',
            'created_by' => $generalSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoElena->id,
            'lote_id' => $loteElena1->id,
            'cuartel_manual' => 'Cuartel V1',
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Granos',
            'precio' => 2.00,
            'kilogramos' => 80.00,
            'valor_venta' => 160.00,
            'jabas' => 4,
            'peso_jaba' => 20.00,
            'ruc' => '20554433221',
            'cliente' => 'Agroexport del Sur SAC',
            'placa' => 'XYZ-789',
            'conductor' => 'Carlos López',
            'created_by' => $generalElena->id,
        ]);
    }
}
