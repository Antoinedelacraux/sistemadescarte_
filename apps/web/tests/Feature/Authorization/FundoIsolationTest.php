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

        // 1. Crear Roles
        $roleAdmin = Role::create(['name' => Role::ADMIN, 'display_name' => 'Admin']);
        $roleGeneral = Role::create(['name' => Role::GENERAL, 'display_name' => 'General']);
        $roleIndividual = Role::create(['name' => Role::INDIVIDUAL, 'display_name' => 'Individual']);
        $roleAnalista = Role::create(['name' => Role::ANALISTA, 'display_name' => 'Analista']);

        // 2. Crear Fundos
        $this->fundoA = Fundo::create(['name' => 'Fundo Santa Sofía', 'code' => 'FSOFIA', 'is_active' => true]);
        $this->fundoB = Fundo::create(['name' => 'Fundo Santa Elena', 'code' => 'FELENA', 'is_active' => true]);

        // 3. Crear Usuarios
        $pass = Hash::make('secret');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => $pass, 'role_id' => $roleAdmin->id, 'is_active' => true]);
        $this->analista = User::create(['name' => 'Analista', 'email' => 'analista@test.com', 'password' => $pass, 'role_id' => $roleAnalista->id, 'is_active' => true]);

        $this->generalA = User::create(['name' => 'Jefe A', 'email' => 'generalA@test.com', 'password' => $pass, 'role_id' => $roleGeneral->id, 'is_active' => true]);
        $this->generalA->fundos()->attach($this->fundoA->id);

        $this->generalB = User::create(['name' => 'Jefe B', 'email' => 'generalB@test.com', 'password' => $pass, 'role_id' => $roleGeneral->id, 'is_active' => true]);
        $this->generalB->fundos()->attach($this->fundoB->id);

        $this->individualA = User::create(['name' => 'Operador A', 'email' => 'indA@test.com', 'password' => $pass, 'role_id' => $roleIndividual->id, 'is_active' => true]);
        $this->individualA->fundos()->attach($this->fundoA->id);

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
            'created_by' => $this->individualA->id,
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
            'created_by' => $this->generalB->id,
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

    public function test_general_user_can_only_access_records_from_assigned_fundo(): void
    {
        // Jefe de Fundo A
        $this->actingAs($this->generalA);

        $records = VentaDescarte::all();

        $this->assertCount(1, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertFalse($records->contains('id', $this->ventaB->id));

        // Intento directo de buscar por ID de Fundo B devuelve null gracias a FundoScope
        $foreignRecord = VentaDescarte::find($this->ventaB->id);
        $this->assertNull($foreignRecord);
    }

    public function test_individual_user_can_only_access_records_from_assigned_fundo(): void
    {
        // Operador de Fundo A
        $this->actingAs($this->individualA);

        $records = VentaDescarte::all();

        $this->assertCount(1, $records);
        $this->assertTrue($records->contains('id', $this->ventaA->id));
        $this->assertFalse($records->contains('id', $this->ventaB->id));

        // Intento directo de buscar por ID de Fundo B devuelve null
        $foreignRecord = VentaDescarte::find($this->ventaB->id);
        $this->assertNull($foreignRecord);
    }

    public function test_dashboard_renders_only_authorized_fundo_records(): void
    {
        $this->actingAs($this->generalA);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Fundo Santa Sofía');
        $response->assertDontSee('Fundo Santa Elena');
    }

    public function test_user_has_fundo_access_helper_method(): void
    {
        $this->assertTrue($this->admin->hasFundoAccess($this->fundoA->id));
        $this->assertTrue($this->admin->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->analista->hasFundoAccess($this->fundoA->id));
        $this->assertTrue($this->analista->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->generalA->hasFundoAccess($this->fundoA->id));
        $this->assertFalse($this->generalA->hasFundoAccess($this->fundoB->id));

        $this->assertTrue($this->individualA->hasFundoAccess($this->fundoA->id));
        $this->assertFalse($this->individualA->hasFundoAccess($this->fundoB->id));
    }
}
