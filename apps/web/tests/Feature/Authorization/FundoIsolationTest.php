<?php

namespace Tests\Feature\Authorization;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FundoIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Fundo $fundoA;
    private Fundo $fundoB;
    private User $admin;
    private User $analista;
    private User $generalA;
    private User $generalB;
    private User $individualA;
    private VentaDescarte $ventaA;
    private VentaDescarte $ventaB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear Roles Oficiales
        $roleAdmin = Role::firstOrCreate(['name' => Role::ADMIN], ['display_name' => 'Administrador']);
        $roleJefeFundo = Role::firstOrCreate(['name' => Role::JEFE_FUNDO], ['display_name' => 'Jefe de fundo']);
        $roleRegistrador = Role::firstOrCreate(['name' => Role::REGISTRADOR], ['display_name' => 'Registrador']);
        $roleAnalista = Role::firstOrCreate(['name' => Role::ANALISTA], ['display_name' => 'Analista']);
        $roleVentas = Role::firstOrCreate(['name' => Role::VENTAS], ['display_name' => 'Ventas']);

        // 2. Crear Fundos
        $this->fundoA = Fundo::create(['name' => 'Fundo Santa Sofía', 'code' => 'FSOFIA', 'is_active' => true]);
        $this->fundoB = Fundo::create(['name' => 'Fundo Santa Elena', 'code' => 'FELENA', 'is_active' => true]);

        // 3. Crear Usuarios
        $pass = Hash::make('secret');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => $pass, 'role_id' => $roleAdmin->id, 'is_active' => true]);
        $this->analista = User::create(['name' => 'Analista', 'email' => 'analista@test.com', 'password' => $pass, 'role_id' => $roleAnalista->id, 'is_active' => true]);
        $this->ventas = User::create(['name' => 'Ventas', 'email' => 'ventas@test.com', 'password' => $pass, 'role_id' => $roleVentas->id, 'is_active' => true]);

        // Jefe de fundo se le asigna fundo específico (Fundo A)
        $this->jefeFundo = User::create(['name' => 'Jefe Fundo', 'email' => 'jefe@test.com', 'password' => $pass, 'role_id' => $roleJefeFundo->id, 'is_active' => true]);
        $this->jefeFundo->fundos()->attach($this->fundoA->id);

        // Registrador se le asigna fundo específico
        $this->registradorA = User::create(['name' => 'Registrador A', 'email' => 'regA@test.com', 'password' => $pass, 'role_id' => $roleRegistrador->id, 'is_active' => true]);
        $this->registradorA->fundos()->attach($this->fundoA->id);

        // 4. Crear Registros en cada fundo
        $this->ventaA = VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoA->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->registradorA->id,
        ]);

        $this->ventaB = VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoB->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Granos',
            'precio' => 2.00,
            'kilogramos' => 50.00,
            'valor_venta' => 100.00,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_access_records_from_all_fundos(): void
    {
        $this->actingAs($this->admin);

        $records = VentaDescarte::all();

        $this->assertCount(2, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertTrue($records->contains('id', $this->ventaB->id));
    }

    public function test_analista_can_access_records_from_all_fundos(): void
    {
        $this->actingAs($this->analista);

        $records = VentaDescarte::all();

        $this->assertCount(2, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertTrue($records->contains('id', $this->ventaB->id));
    }

    public function test_ventas_can_access_records_from_all_fundos(): void
    {
        $this->actingAs($this->ventas);

        $records = VentaDescarte::all();

        $this->assertCount(2, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertTrue($records->contains('id', $this->ventaB->id));
    }

    public function test_jefe_fundo_user_can_only_access_records_from_assigned_fundo(): void
    {
        $this->actingAs($this->jefeFundo);

        $records = VentaDescarte::all();

        $this->assertCount(1, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertFalse($records->contains('id', $this->ventaB->id));
    }

    public function test_registrador_user_can_only_access_records_from_assigned_fundo(): void
    {
        // Registrador asignado a Fundo A
        $this->actingAs($this->registradorA);

        $records = VentaDescarte::all();

        $this->assertCount(1, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertFalse($records->contains('id', $this->ventaB->id));

        // Intento directo de buscar por ID de Fundo B devuelve null gracias a FundoScope
        $foreignRecord = VentaDescarte::find($this->ventaB->id);
        $this->assertNull($foreignRecord);
    }

    public function test_user_has_fundo_access_helper_method(): void
    {
        $this->assertTrue($this->admin->hasFundoAccess($this->fundoA->id));
        $this->assertTrue($this->admin->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->jefeFundo->hasFundoAccess($this->fundoA->id));
        $this->assertFalse($this->jefeFundo->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->analista->hasFundoAccess($this->fundoA->id));
        $this->assertTrue($this->analista->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->ventas->hasFundoAccess($this->fundoA->id));
        $this->assertTrue($this->ventas->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->registradorA->hasFundoAccess($this->fundoA->id));
        $this->assertFalse($this->registradorA->hasFundoAccess($this->fundoB->id));
    }
}
