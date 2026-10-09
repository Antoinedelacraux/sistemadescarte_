<?php

namespace Tests\Feature\Admin;

use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FundoNombresTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $registrador;
    private Fundo $fundoAgritac;
    private Fundo $fundoProcom;
    private Fundo $fundoElNegro;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );

        $roleIndividual = Role::firstOrCreate(
            ['name' => Role::INDIVIDUAL],
            ['display_name' => 'Individual (Pesador / Registrador)']
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin General',
                'password' => Hash::make('password123'),
                'role_id' => $roleAdmin->id,
                'is_active' => true,
            ]
        );

        $this->fundoAgritac = Fundo::firstOrCreate(
            ['code' => 'AGRITAC'],
            [
                'name' => 'AGRITAC',
                'nombre_completo' => 'AGRICOLA TAMBO COLORADO',
                'is_active' => true,
            ]
        );

        $this->fundoProcom = Fundo::firstOrCreate(
            ['code' => 'PROCOM'],
            [
                'name' => 'PROCOM',
                'nombre_completo' => 'AGRICOLA PROCOM',
                'is_active' => true,
            ]
        );

        $this->fundoElNegro = Fundo::firstOrCreate(
            ['code' => 'ELNEGRO'],
            [
                'name' => 'EL NEGRO',
                'nombre_completo' => 'TALSA GRAPE FARMS',
                'is_active' => true,
            ]
        );

        $this->registrador = User::create([
            'name' => 'Registrador Santa Sofía',
            'email' => 'pesador@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
        $this->registrador->fundos()->attach([$this->fundoAgritac->id]);
    }

    public function test_fundo_model_has_clean_short_name_and_full_company_name(): void
    {
        $this->assertEquals('AGRITAC', $this->fundoAgritac->name);
        $this->assertEquals('AGRICOLA TAMBO COLORADO', $this->fundoAgritac->nombre_completo);
        $this->assertEquals('AGRITAC', $this->fundoAgritac->nombre_corto);

        $this->assertEquals('PROCOM', $this->fundoProcom->name);
        $this->assertEquals('AGRICOLA PROCOM', $this->fundoProcom->nombre_completo);

        $this->assertEquals('EL NEGRO', $this->fundoElNegro->name);
        $this->assertEquals('TALSA GRAPE FARMS', $this->fundoElNegro->nombre_completo);
    }

    public function test_welcome_card_displays_full_name_without_parenthesis(): void
    {
        $response = $this->actingAs($this->registrador)->get(route('dashboard'));

        $response->assertOk();
        // Debe mostrar el nombre completo de la empresa en la tarjeta de bienvenida
        $response->assertSee('AGRICOLA TAMBO COLORADO');
        // NO debe contener el texto concatenado con paréntesis
        $response->assertDontSee('AGRICOLA TAMBO COLORADO (AGRITAC)');
    }

    public function test_headbar_chip_displays_short_name(): void
    {
        $response = $this->actingAs($this->registrador)->get(route('dashboard'));

        $response->assertOk();
        // En el headbar debe verse el nombre corto
        $response->assertSee('🏡 AGRITAC', false);
    }

    public function test_sales_registration_shows_clean_fundo_names_in_select(): void
    {
        $response = $this->actingAs($this->admin)->get(route('ventas.create'));

        $response->assertOk();
        $response->assertSee('AGRITAC');
        $response->assertSee('PROCOM');
        $response->assertSee('EL NEGRO');
        $response->assertDontSee('AGRICOLA TAMBO COLORADO (AGRITAC)');
    }

    public function test_admin_can_create_fundo_with_short_and_full_name(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fundos.store'), [
            'name' => 'SANTA SOFIA',
            'nombre_completo' => 'AGRICOLA SANTA SOFIA S.A.C.',
            'code' => 'SANTA_SOFIA',
            'lotes' => 'S01, S02',
        ]);

        $response->assertRedirect(route('admin.fundos'));

        $nuevo = Fundo::where('code', 'SANTA_SOFIA')->first();
        $this->assertNotNull($nuevo);
        $this->assertEquals('SANTA SOFIA', $nuevo->name);
        $this->assertEquals('AGRICOLA SANTA SOFIA S.A.C.', $nuevo->nombre_completo);
        $this->assertEquals(2, $nuevo->lotes()->count());
    }
}
