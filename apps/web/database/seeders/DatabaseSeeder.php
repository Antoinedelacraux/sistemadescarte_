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
        $roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador', 'description' => 'Acceso total y configuración del sistema']
        );

        $roleGeneral = Role::firstOrCreate(
            ['name' => Role::GENERAL],
            ['display_name' => 'General (Jefe de Fundo)', 'description' => 'Visualización de todos los registros de su fundo asignado']
        );

        $roleIndividual = Role::firstOrCreate(
            ['name' => Role::INDIVIDUAL],
            ['display_name' => 'Individual (Pesador / Registrador)', 'description' => 'Registro y visualización en su fundo asignado']
        );

        $roleAnalista = Role::firstOrCreate(
            ['name' => Role::ANALISTA],
            ['display_name' => 'Analista', 'description' => 'Visualización y exportación de datos de todos los fundos']
        );

        // 2. Fundos Reales del Sistema
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

        // 3. Usuarios de prueba con datos ficticios
        $defaultPassword = Hash::make('password123');

        $admin = User::firstOrCreate(
            ['email' => 'admin@fundo.test'],
            ['name' => 'Administrador General', 'password' => $defaultPassword, 'role_id' => $roleAdmin->id, 'is_active' => true]
        );
        $admin->fundos()->syncWithoutDetaching([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        $generalSofia = User::firstOrCreate(
            ['email' => 'general.agritac@fundo.test'],
            ['name' => 'Jefe Fundo Agritac', 'password' => $defaultPassword, 'role_id' => $roleGeneral->id, 'is_active' => true]
        );
        $generalSofia->fundos()->syncWithoutDetaching([$fundoAgritac->id]);

        $generalElena = User::firstOrCreate(
            ['email' => 'general.procom@fundo.test'],
            ['name' => 'Jefe Fundo Procom', 'password' => $defaultPassword, 'role_id' => $roleGeneral->id, 'is_active' => true]
        );
        $generalElena->fundos()->syncWithoutDetaching([$fundoProcom->id]);

        $individualSofia = User::firstOrCreate(
            ['email' => 'individual.agritac@fundo.test'],
            ['name' => 'Registrador Agritac', 'password' => $defaultPassword, 'role_id' => $roleIndividual->id, 'is_active' => true]
        );
        $individualSofia->fundos()->syncWithoutDetaching([$fundoAgritac->id]);

        $individualProcom = User::firstOrCreate(
            ['email' => 'individual.procom@fundo.test'],
            ['name' => 'Registrador Procom', 'password' => $defaultPassword, 'role_id' => $roleIndividual->id, 'is_active' => true]
        );
        $individualProcom->fundos()->syncWithoutDetaching([$fundoProcom->id]);

        $generalElNegro = User::firstOrCreate(
            ['email' => 'general.elnegro@fundo.test'],
            ['name' => 'Jefe Fundo El Negro', 'password' => $defaultPassword, 'role_id' => $roleGeneral->id, 'is_active' => true]
        );
        $generalElNegro->fundos()->syncWithoutDetaching([$fundoElNegro->id]);

        $individualElNegro = User::firstOrCreate(
            ['email' => 'individual.elnegro@fundo.test'],
            ['name' => 'Registrador El Negro', 'password' => $defaultPassword, 'role_id' => $roleIndividual->id, 'is_active' => true]
        );
        $individualElNegro->fundos()->syncWithoutDetaching([$fundoElNegro->id]);

        $analista = User::firstOrCreate(
            ['email' => 'analista@fundo.test'],
            ['name' => 'Analista Central', 'password' => $defaultPassword, 'role_id' => $roleAnalista->id, 'is_active' => true]
        );
        $analista->fundos()->syncWithoutDetaching([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        // 4. Catálogos: Lotes exactos por Fundo
        $lotesAgritac = [
            'A03', 'A05', 'A06', 'L03', 'L06', 'L07',
            'M03', 'M05', 'M04', 'M01', 'M02', 'A04',
            'A02', 'A01', 'L01', 'L02', 'L04', 'L05'
        ];
        foreach ($lotesAgritac as $nombreLote) {
            \App\Models\Lote::withoutGlobalScopes()->firstOrCreate([
                'fundo_id' => $fundoAgritac->id,
                'nombre' => $nombreLote,
            ]);
        }

        $lotesProcom = ['H01', 'H02', 'H03', 'H04', 'H05', 'H06', 'H07', 'H08', 'H09'];
        foreach ($lotesProcom as $nombreLote) {
            \App\Models\Lote::withoutGlobalScopes()->firstOrCreate([
                'fundo_id' => $fundoProcom->id,
                'nombre' => $nombreLote,
            ]);
        }

        $lotesElNegro = ['N01', 'N03', 'N04', 'N05', 'N06', 'N07', 'N08'];
        foreach ($lotesElNegro as $nombreLote) {
            \App\Models\Lote::withoutGlobalScopes()->firstOrCreate([
                'fundo_id' => $fundoElNegro->id,
                'nombre' => $nombreLote,
            ]);
        }

        // 5. Inyectar ventas de prueba coherentes (SOLO en desarrollo y pruebas, nunca en producción)
        if (!app()->environment('production')) {
            $this->call(Inyectar50VentasHipoteticasSeeder::class);
        }
    }
}
