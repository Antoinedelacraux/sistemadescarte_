<?php

namespace Tests\Feature\Authorization;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RolesAndModulesAccessTest extends TestCase
{
    use RefreshDatabase;

    private Role $roleAdmin;
    private Role $roleJefeFundo;
    private Role $roleRegistrador;
    private Role $roleAnalista;
    private Role $roleVentas;
    private Fundo $fundo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );
        $this->roleJefeFundo = Role::firstOrCreate(
            ['name' => Role::JEFE_FUNDO],
            ['display_name' => 'Jefe de fundo']
        );
        $this->roleRegistrador = Role::firstOrCreate(
            ['name' => Role::REGISTRADOR],
            ['display_name' => 'Registrador']
        );
        $this->roleAnalista = Role::firstOrCreate(
            ['name' => Role::ANALISTA],
            ['display_name' => 'Analista']
        );
        $this->roleVentas = Role::firstOrCreate(
            ['name' => Role::VENTAS],
            ['display_name' => 'Ventas']
        );

        $this->fundo = Fundo::create([
            'name' => 'Fundo Santa Sofía',
            'code' => 'FSOFIA',
            'is_active' => true,
        ]);
    }

    public function test_jefe_de_fundo_default_access(): void
    {
        $jefe = User::create([
            'name' => 'Jefe Central',
            'email' => 'jefe@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleJefeFundo->id,
            'is_active' => true,
        ]);

        $this->actingAs($jefe);

        // Puede acceder a PANEL e HISTORIAL
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('ventas.index'))->assertStatus(200);

        // No puede acceder a REGISTRO ni ADMIN
        $this->get(route('ventas.create'))->assertStatus(403);
        $this->get(route('admin.fundos'))->assertStatus(403);
        $this->get(route('admin.usuarios'))->assertStatus(403);
    }

    public function test_registrador_default_access(): void
    {
        $registrador = User::create([
            'name' => 'Registrador Fundo',
            'email' => 'registrador@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleRegistrador->id,
            'is_active' => true,
        ]);
        $registrador->fundos()->attach($this->fundo->id);

        $this->actingAs($registrador);

        // Puede acceder a REGISTRO e HISTORIAL
        $this->get(route('ventas.create'))->assertStatus(200);
        $this->get(route('ventas.index'))->assertStatus(200);

        // Al visitar / (dashboard) se redirige automáticamente al primer módulo permitido (ventas.create)
        $this->get('/')->assertRedirect(route('ventas.create'));

        // Intento directo al módulo PANEL da 403
        $this->get(route('dashboard.exportar'))->assertStatus(403);

        // No puede acceder a ADMIN
        $this->get(route('admin.fundos'))->assertStatus(403);
    }

    public function test_analista_default_access(): void
    {
        $analista = User::create([
            'name' => 'Analista Datos',
            'email' => 'analista@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleAnalista->id,
            'is_active' => true,
        ]);

        $this->actingAs($analista);

        // Puede acceder a HISTORIAL solamente
        $this->get(route('ventas.index'))->assertStatus(200);

        // Al visitar / se redirige automáticamente a ventas.index
        $this->get('/')->assertRedirect(route('ventas.index'));

        // No puede acceder a PANEL, REGISTRO ni ADMIN
        $this->get(route('dashboard.exportar'))->assertStatus(403);
        $this->get(route('ventas.create'))->assertStatus(403);
        $this->get(route('admin.usuarios'))->assertStatus(403);
    }

    public function test_ventas_default_access(): void
    {
        $ventas = User::create([
            'name' => 'Ejecutivo Ventas',
            'email' => 'ventas@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleVentas->id,
            'is_active' => true,
        ]);

        $this->actingAs($ventas);

        // Puede acceder a HISTORIAL solamente
        $this->get(route('ventas.index'))->assertStatus(200);

        // Al ingresar a / se redirige automáticamente a ventas.index
        $this->get('/')->assertRedirect(route('ventas.index'));

        // No puede acceder a PANEL, REGISTRO ni ADMIN
        $this->get(route('dashboard.exportar'))->assertStatus(403);
        $this->get(route('ventas.create'))->assertStatus(403);
        $this->get(route('admin.fundos'))->assertStatus(403);
    }

    public function test_administrador_default_access(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleAdmin->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // Administrador accede a todos los módulos
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('ventas.create'))->assertStatus(200);
        $this->get(route('ventas.index'))->assertStatus(200);
        $this->get(route('admin.fundos'))->assertStatus(200);
        $this->get(route('admin.usuarios'))->assertStatus(200);
    }

    public function test_custom_module_override_enables_panel_for_registrador(): void
    {
        // Caso excepcional: A un registrador se le habilita adicionalmente el módulo PANEL
        $registradorExcepcional = User::create([
            'name' => 'Registrador Excepcional',
            'email' => 'reg.excep@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleRegistrador->id,
            'is_active' => true,
            'modulos_personalizados' => ['panel', 'registro', 'historial'],
        ]);
        $registradorExcepcional->fundos()->attach($this->fundo->id);

        $this->actingAs($registradorExcepcional);

        // Ahora sí puede ver el Dashboard directamente sin ser redirigido
        $this->get(route('dashboard'))->assertStatus(200);
        $this->get(route('ventas.create'))->assertStatus(200);
        $this->get(route('ventas.index'))->assertStatus(200);
    }

    public function test_custom_module_override_enables_registro_for_analista(): void
    {
        // Caso excepcional: A un analista se le concede acceso a REGISTRO
        $analistaConRegistro = User::create([
            'name' => 'Analista Senior',
            'email' => 'analista.sr@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleAnalista->id,
            'is_active' => true,
            'modulos_personalizados' => ['historial', 'registro'],
        ]);
        $analistaConRegistro->fundos()->attach($this->fundo->id);

        $this->actingAs($analistaConRegistro);

        $this->get(route('ventas.index'))->assertStatus(200);
        $this->get(route('ventas.create'))->assertStatus(200);
        $this->get(route('admin.fundos'))->assertStatus(403);
    }

    public function test_admin_can_create_user_with_custom_module_overrides(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleAdmin->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $payload = [
            'name' => 'Usuario Caso Excepcional',
            'email' => 'excepcional@fundo.test',
            'password' => 'secret123',
            'role_id' => $this->roleVentas->id,
            'usar_modulos_personalizados' => '1',
            'modulos' => ['historial', 'panel'],
        ];

        $response = $this->post(route('admin.usuarios.store'), $payload);
        $response->assertRedirect(route('admin.usuarios'));

        $user = User::where('email', 'excepcional@fundo.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasCustomModules());
        $this->assertTrue($user->canAccessModule('historial'));
        $this->assertTrue($user->canAccessModule('panel'));
        $this->assertFalse($user->canAccessModule('registro'));
    }

    public function test_admin_can_update_user_module_overrides(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('secret'),
            'role_id' => $this->roleAdmin->id,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Usuario Normal',
            'email' => 'normal@fundo.test',
            'password' => Hash::make('secret123'),
            'role_id' => $this->roleRegistrador->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        // Modificar para darle también PANEL como excepción
        $payload = [
            'name' => 'Usuario Normal Editado',
            'email' => 'normal@fundo.test',
            'role_id' => $this->roleRegistrador->id,
            'usar_modulos_personalizados' => '1',
            'modulos' => ['panel', 'registro', 'historial'],
        ];

        $response = $this->put(route('admin.usuarios.update', $user), $payload);
        $response->assertRedirect(route('admin.usuarios'));

        $user->refresh();
        $this->assertTrue($user->hasCustomModules());
        $this->assertTrue($user->canAccessModule('panel'));
        $this->assertTrue($user->canAccessModule('registro'));
        $this->assertTrue($user->canAccessModule('historial'));
        $this->assertFalse($user->canAccessModule('admin'));
    }
}
