<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar columna modulos_personalizados a users
        if (!Schema::hasColumn('users', 'modulos_personalizados')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('modulos_personalizados')->nullable()->after('is_active');
            });
        }

        // 2. Configurar los 5 roles definitivos en la tabla roles
        $rolesData = [
            'admin' => [
                'display_name' => 'Administrador',
                'description' => 'Acceso total y configuración de todos los módulos del sistema',
            ],
            'jefe_fundo' => [
                'display_name' => 'Jefe de fundo',
                'description' => 'Visualización de información de todos los fundos (Panel e Historial)',
            ],
            'registrador' => [
                'display_name' => 'Registrador',
                'description' => 'Registro de ventas e historial en sus fundos asignados',
            ],
            'analista' => [
                'display_name' => 'Analista',
                'description' => 'Visualización de información de todos los fundos (Historial)',
            ],
            'ventas' => [
                'display_name' => 'Ventas',
                'description' => 'Visualización de información de todos los fundos (Historial)',
            ],
        ];

        // Mapear roles antiguos si existen
        DB::table('roles')->where('name', 'general')->update(['name' => 'jefe_fundo', 'display_name' => 'Jefe de fundo']);
        DB::table('roles')->where('name', 'individual')->update(['name' => 'registrador', 'display_name' => 'Registrador']);

        foreach ($rolesData as $name => $data) {
            $existing = DB::table('roles')->where('name', $name)->first();
            if ($existing) {
                DB::table('roles')->where('id', $existing->id)->update([
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('roles')->insert([
                    'name' => $name,
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'modulos_personalizados')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('modulos_personalizados');
            });
        }
    }
};
