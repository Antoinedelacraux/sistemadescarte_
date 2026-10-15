<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fundo:crear-admin {--email=} {--name=} {--password=}', function () {
    $email = $this->option('email') ?: $this->ask('Introduce el correo electrónico del Administrador');
    $email = trim((string) $email);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Debes ingresar un correo electrónico válido.');
        return 1;
    }

    $name = $this->option('name') ?: $this->ask('Introduce tu nombre completo', 'Administrador Principal');
    $password = $this->option('password') ?: $this->secret('Introduce la contraseña (mínimo 6 caracteres)');

    if (empty($password) || strlen($password) < 6) {
        $this->error('La contraseña debe tener al menos 6 caracteres.');
        return 1;
    }

    $roleAdmin = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::ADMIN],
        ['display_name' => 'Administrador', 'description' => 'Acceso total y configuración del sistema']
    );

    $user = \App\Models\User::firstOrNew(['email' => $email]);
    $user->name = $name;
    $user->password = \Illuminate\Support\Facades\Hash::make($password);
    $user->role_id = $roleAdmin->id;
    $user->is_active = true;
    $user->save();

    // Asignar todos los fundos del sistema
    $fundos = \App\Models\Fundo::pluck('id');
    $user->fundos()->sync($fundos);

    $this->info("==================================================");
    $this->info("¡Usuario Administrador configurado con éxito!");
    $this->info("Correo:      {$user->email}");
    $this->info("Nombre:      {$user->name}");
    $this->info("Fundos:      " . \App\Models\Fundo::pluck('name')->join(', '));
    $this->info("==================================================");
    $this->comment("Ya puedes iniciar sesión con estas credenciales en el sistema.");
    return 0;
})->purpose('Crea o actualiza interactivamente una cuenta Administrador para operar el sistema');

Artisan::command('fundo:resetear-usuarios', function () {
    if (app()->environment('production')) {
        $this->error('BLOQUEO DE SEGURIDAD: Este comando destructivo está prohibido en entorno de PRODUCCIÓN.');
        return 1;
    }

    $this->info("Iniciando reseteo de cuentas a los 5 roles oficiales...");

    // 1. Roles oficiales
    $roleAdmin = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::ADMIN],
        ['display_name' => 'Administrador', 'description' => 'Acceso total y configuración de todos los módulos']
    );
    $roleJefe = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::JEFE_FUNDO],
        ['display_name' => 'Jefe de fundo', 'description' => 'Visualización de información de todos los fundos (Panel e Historial)']
    );
    $roleReg = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::REGISTRADOR],
        ['display_name' => 'Registrador', 'description' => 'Panel de control, registro de ventas e historial en sus fundos asignados']
    );
    $roleAna = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::ANALISTA],
        ['display_name' => 'Analista', 'description' => 'Visualización de información de todos los fundos (Historial)']
    );
    $roleVen = \App\Models\Role::firstOrCreate(
        ['name' => \App\Models\Role::VENTAS],
        ['display_name' => 'Ventas', 'description' => 'Visualización de información de todos los fundos (Historial)']
    );

    \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
    \Illuminate\Support\Facades\DB::table('fundo_user')->delete();
    \Illuminate\Support\Facades\DB::table('ventas_descarte')->delete();
    \Illuminate\Support\Facades\DB::table('users')->delete();
    \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

    $pass = \Illuminate\Support\Facades\Hash::make('password123');
    $fundos = \App\Models\Fundo::where('is_active', true)->get();

    // 1. Admin
    $admin = \App\Models\User::create([
        'name' => 'Administrador General',
        'email' => 'admin@fundo.test',
        'password' => $pass,
        'role_id' => $roleAdmin->id,
        'is_active' => true,
        'modulos_personalizados' => null,
    ]);
    $admin->fundos()->sync($fundos->pluck('id'));

    // 2. Jefe de fundo
    $jefe = \App\Models\User::create([
        'name' => 'Jefe de Fundo Central',
        'email' => 'jefe@fundo.test',
        'password' => $pass,
        'role_id' => $roleJefe->id,
        'is_active' => true,
        'modulos_personalizados' => null,
    ]);

    // 3. Registrador
    $reg = \App\Models\User::create([
        'name' => 'Registrador de Campo',
        'email' => 'registrador@fundo.test',
        'password' => $pass,
        'role_id' => $roleReg->id,
        'is_active' => true,
        'modulos_personalizados' => null,
    ]);
    // Asignar todos los fundos activos para que pueda registrar en cualquiera
    $reg->fundos()->sync($fundos->pluck('id'));

    // 4. Analista
    $ana = \App\Models\User::create([
        'name' => 'Analista Central',
        'email' => 'analista@fundo.test',
        'password' => $pass,
        'role_id' => $roleAna->id,
        'is_active' => true,
        'modulos_personalizados' => null,
    ]);

    // 5. Ventas
    $ven = \App\Models\User::create([
        'name' => 'Ventas Comercial',
        'email' => 'ventas@fundo.test',
        'password' => $pass,
        'role_id' => $roleVen->id,
        'is_active' => true,
        'modulos_personalizados' => null,
    ]);

    $this->info("================================================================================");
    $this->info("¡CUENTAS REINICIALIZADAS CON ÉXITO! Contraseña para todas: password123");
    $this->info("================================================================================");
    $this->line("1. Administrador: admin@fundo.test       | Rol: Administrador  | Módulos: TODOS | Fundos: TODOS");
    $this->line("2. Jefe de fundo: jefe@fundo.test        | Rol: Jefe de fundo  | Módulos: PANEL, HISTORIAL | Fundos: TODOS");
    $this->line("3. Registrador:   registrador@fundo.test | Rol: Registrador    | Módulos: PANEL, REGISTRO, HISTORIAL | Fundos: ASIGNADOS");
    $this->line("4. Analista:      analista@fundo.test    | Rol: Analista       | Módulos: HISTORIAL | Fundos: TODOS");
    $this->line("5. Ventas:        ventas@fundo.test      | Rol: Ventas         | Módulos: HISTORIAL | Fundos: TODOS");
    $this->info("================================================================================");

    return 0;
})->purpose('Crea las 5 cuentas oficiales con la nueva arquitectura de roles y módulos');
