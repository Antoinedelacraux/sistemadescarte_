<?php

namespace Tests\Feature\Frontend;

use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResponsiveYCustomSelectTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Fundo $fundo;
    private Lote $lote;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );

        $this->fundo = Fundo::firstOrCreate(
            ['code' => 'AGRITAC'],
            [
                'name' => 'AGRITAC',
                'nombre_completo' => 'AGRICOLA TAMBO COLORADO',
                'is_active' => true,
            ]
        );

        $this->lote = Lote::withoutGlobalScopes()->firstOrCreate(
            ['fundo_id' => $this->fundo->id, 'nombre' => 'A01'],
            ['fundo_id' => $this->fundo->id, 'nombre' => 'A01']
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_responsive@fundo.test'],
            [
                'name' => 'Marco Chumbes',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
        $this->adminUser->fundos()->syncWithoutDetaching([$this->fundo->id]);
    }

    public function test_layout_renders_custom_select_css_and_responsive_rules(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('custom-select-wrap');
        $response->assertSee('custom-select-dropdown');
        $response->assertSee('custom-select-options-list');
        $response->assertSee('custom-select-search-input');
        $response->assertSee('initCustomSelect');
        $response->assertSee('data-custom-select');

        // Responsive Headbar elements
        $response->assertSee('app-header');
        $response->assertSee('header-user-name');
        $response->assertSee('fundo-chip');
        $response->assertSee('net-label');

        // Footer institucional listo para producción
        $response->assertSee('app-footer');
        $response->assertSee('TALSA Grape Farms');
        $response->assertSee('Sistema Web del Fundo');
        $response->assertSee('v1.0.0');
    }

    public function test_ventas_create_renders_custom_select_attributes(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('ventas.create'));

        $response->assertStatus(200);
        $response->assertSee('id="lote_id"', false);
        $response->assertSee('data-custom-select="true"', false);
        $response->assertSee('id="motivo"', false);
        $response->assertSee('id="tipo_descarte"', false);
        $response->assertSee('refreshCustomSelect', false);
    }

    public function test_ventas_edit_renders_custom_select_attributes(): void
    {
        $venta = VentaDescarte::create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->lote->id,
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.75,
            'kilogramos' => 120.00,
            'valor_venta' => 210.00,
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('ventas.edit', $venta));

        $response->assertStatus(200);
        $response->assertSee('id="lote_id"', false);
        $response->assertSee('data-custom-select="true"', false);
        $response->assertSee('refreshCustomSelect', false);
    }

    public function test_registrador_con_un_solo_fundo_muestra_fundo_readonly_sin_select_fantasma(): void
    {
        $regRole = Role::firstOrCreate(
            ['name' => Role::INDIVIDUAL],
            ['display_name' => 'Individual']
        );

        $registrador = User::create([
            'email' => 'registrador_procom@fundo.test',
            'name' => 'Registrador Procom',
            'password' => Hash::make('password123'),
            'role_id' => $regRole->id,
            'is_active' => true,
        ]);
        $registrador->fundos()->sync([$this->fundo->id]);

        $response = $this->actingAs($registrador)->get(route('ventas.create'));

        $response->assertStatus(200);
        // Debe mostrar el input readonly con el nombre del fundo asignado
        $response->assertSee('value="' . $this->fundo->name . '" readonly', false);
        // Debe tener el input hidden para fundo_id
        $response->assertSee('type="hidden" name="fundo_id" id="fundo_id"', false);
        // NO debe tener un select para fundo_id
        $response->assertDontSee('<select name="fundo_id"', false);
        // El motor initCustomSelect debe tener salvaguarda estricta de tagName === "SELECT"
        $response->assertSee("nativeSelect.tagName !== 'SELECT'", false);
    }
}
