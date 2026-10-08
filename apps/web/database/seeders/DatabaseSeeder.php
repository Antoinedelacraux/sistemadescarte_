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

        // 2. Fundos ficticios
        $fundoSofia = Fundo::create([
            'name' => 'Fundo Santa Sofía',
            'code' => 'FSOFIA',
            'is_active' => true,
        ]);

        $fundoElena = Fundo::create([
            'name' => 'Fundo Santa Elena',
            'code' => 'FELENA',
            'is_active' => true,
        ]);

        $fundoJose = Fundo::create([
            'name' => 'Fundo San José',
            'code' => 'FJOSE',
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
        $admin->fundos()->attach([$fundoSofia->id, $fundoElena->id, $fundoJose->id]);

        $generalSofia = User::create([
            'name' => 'Jefe Fundo Santa Sofía',
            'email' => 'general.sofia@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleGeneral->id,
            'is_active' => true,
        ]);
        $generalSofia->fundos()->attach([$fundoSofia->id]);

        $generalElena = User::create([
            'name' => 'Jefe Fundo Santa Elena',
            'email' => 'general.elena@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleGeneral->id,
            'is_active' => true,
        ]);
        $generalElena->fundos()->attach([$fundoElena->id]);

        $individualSofia = User::create([
            'name' => 'Registrador Santa Sofía',
            'email' => 'individual.sofia@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
        $individualSofia->fundos()->attach([$fundoSofia->id]);

        $analista = User::create([
            'name' => 'Analista Central',
            'email' => 'analista@fundo.test',
            'password' => $defaultPassword,
            'role_id' => $roleAnalista->id,
            'is_active' => true,
        ]);
        $analista->fundos()->attach([$fundoSofia->id, $fundoElena->id, $fundoJose->id]);

        // 4. Catálogos: Lotes y Cuarteles ficticios por fundo
        $loteSofia1 = \App\Models\Lote::withoutGlobalScopes()->create([
            'fundo_id' => $fundoSofia->id,
            'nombre' => 'Lote 01 - Norte',
        ]);
        \App\Models\Cuartel::create(['lote_id' => $loteSofia1->id, 'nombre' => 'Cuartel 1A']);
        \App\Models\Cuartel::create(['lote_id' => $loteSofia1->id, 'nombre' => 'Cuartel 1B']);

        $loteSofia2 = \App\Models\Lote::withoutGlobalScopes()->create([
            'fundo_id' => $fundoSofia->id,
            'nombre' => 'Lote 02 - Sur',
        ]);
        \App\Models\Cuartel::create(['lote_id' => $loteSofia2->id, 'nombre' => 'Cuartel 2A']);
        \App\Models\Cuartel::create(['lote_id' => $loteSofia2->id, 'nombre' => 'Cuartel 2B']);

        $loteElena1 = \App\Models\Lote::withoutGlobalScopes()->create([
            'fundo_id' => $fundoElena->id,
            'nombre' => 'Lote 01 - Valle',
        ]);
        \App\Models\Cuartel::create(['lote_id' => $loteElena1->id, 'nombre' => 'Cuartel V1']);
        \App\Models\Cuartel::create(['lote_id' => $loteElena1->id, 'nombre' => 'Cuartel V2']);

        $loteJose1 = \App\Models\Lote::withoutGlobalScopes()->create([
            'fundo_id' => $fundoJose->id,
            'nombre' => 'Lote 01 - Colina',
        ]);
        \App\Models\Cuartel::create(['lote_id' => $loteJose1->id, 'nombre' => 'Cuartel C1']);

        // 5. Registros ficticios para validar aislamiento entre fundos
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'lote_id' => $loteSofia1->id,
            'cuartel_manual' => 'Cuartel 1A',
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 120.00,
            'valor_venta' => 180.00,
            'jabas' => 6,
            'peso_jaba' => 20.00,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Pérez',
            'created_by' => $individualSofia->id,
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
            'placa' => 'XYZ-789',
            'conductor' => 'Carlos López',
            'created_by' => $generalElena->id,
        ]);
    }
}
