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
