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
        // 1. Roles del sistema (5 roles oficiales)
        $roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador', 'description' => 'Acceso total y configuración del sistema']
        );

        $roleJefeFundo = Role::firstOrCreate(
            ['name' => Role::JEFE_FUNDO],
            ['display_name' => 'Jefe de fundo', 'description' => 'Visualización de información de todos los fundos (Panel e Historial)']
        );

        $roleRegistrador = Role::firstOrCreate(
            ['name' => Role::REGISTRADOR],
            ['display_name' => 'Registrador', 'description' => 'Registro de ventas e historial en sus fundos asignados']
        );

        $roleAnalista = Role::firstOrCreate(
            ['name' => Role::ANALISTA],
            ['display_name' => 'Analista', 'description' => 'Visualización de información de todos los fundos (Historial)']
        );

        $roleVentas = Role::firstOrCreate(
            ['name' => Role::VENTAS],
            ['display_name' => 'Ventas', 'description' => 'Visualización de información de todos los fundos (Historial)']
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

        // 3. Administrador del Sistema
        $defaultPassword = Hash::make('password123');
        $adminEmail = env('ADMIN_EMAIL', 'admin@fundo.test');
        $adminPassword = env('ADMIN_PASSWORD') ? Hash::make(env('ADMIN_PASSWORD')) : $defaultPassword;
        $adminName = env('ADMIN_NAME', 'Administrador General');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            ['name' => $adminName, 'password' => $adminPassword, 'role_id' => $roleAdmin->id, 'is_active' => true]
        );
        $admin->fundos()->syncWithoutDetaching([$fundoAgritac->id, $fundoProcom->id, $fundoElNegro->id]);

        // Cuentas limpias por rol para entorno de pruebas/desarrollo
        if (!app()->environment('production')) {
            // Cuenta Jefe de Fundo
            User::firstOrCreate(
                ['email' => 'jefe@fundo.test'],
                ['name' => 'Jefe de Fundo Central', 'password' => $defaultPassword, 'role_id' => $roleJefeFundo->id, 'is_active' => true]
            );

            // Cuenta Registrador (asignado al fundo AGRITAC y PROCOM)
            $registrador = User::firstOrCreate(
                ['email' => 'registrador@fundo.test'],
                ['name' => 'Registrador Agritac', 'password' => $defaultPassword, 'role_id' => $roleRegistrador->id, 'is_active' => true]
            );
            $registrador->fundos()->syncWithoutDetaching([$fundoAgritac->id, $fundoProcom->id]);

            // Cuenta Analista
            User::firstOrCreate(
                ['email' => 'analista@fundo.test'],
                ['name' => 'Analista Central', 'password' => $defaultPassword, 'role_id' => $roleAnalista->id, 'is_active' => true]
            );

            // Cuenta Ventas
            User::firstOrCreate(
                ['email' => 'ventas@fundo.test'],
                ['name' => 'Ventas Comercial', 'password' => $defaultPassword, 'role_id' => $roleVentas->id, 'is_active' => true]
            );
        }

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

        // 5. Inyectar ventas de prueba coherentes (desactivado: el usuario solicitó ventas en cero)
        // if (!app()->environment('production')) {
        //     $this->call(Inyectar50VentasHipoteticasSeeder::class);
        // }
    }
}
