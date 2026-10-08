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

        // 4. Registros ficticios para validar aislamiento entre fundos
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 120.00,
            'valor_venta' => 180.00,
            'created_by' => $individualSofia->id,
        ]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $fundoElena->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Granos',
            'precio' => 2.00,
            'kilogramos' => 80.00,
            'valor_venta' => 160.00,
            'created_by' => $generalElena->id,
        ]);
    }
}
