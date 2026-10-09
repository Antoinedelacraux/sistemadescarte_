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

class PaginationDesignTest extends TestCase
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
            ['code' => 'PROCOM'],
            [
                'name' => 'PROCOM',
                'nombre_completo' => 'AGRICOLA PROCOM',
                'is_active' => true,
            ]
        );

        $this->lote = Lote::withoutGlobalScopes()->firstOrCreate(
            ['fundo_id' => $this->fundo->id, 'nombre' => 'H01'],
            ['fundo_id' => $this->fundo->id, 'nombre' => 'H01']
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_pagination@fundo.test'],
            [
                'name' => 'Admin Pagination',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
        $this->adminUser->fundos()->syncWithoutDetaching([$this->fundo->id]);
    }

    public function test_ventas_index_renders_custom_styled_pagination_when_multiple_pages_exist(): void
    {
        // Crear 23 registros para simular exactamente el caso del usuario (1 a 15 de 23)
        for ($i = 1; $i <= 23; $i++) {
            VentaDescarte::create([
                'id' => (string) Str::uuid(),
                'fundo_id' => $this->fundo->id,
                'lote_id' => $this->lote->id,
                'fecha_produccion' => '2026-09-22',
                'motivo' => 'Campo',
                'tipo_descarte' => 'Racimos',
                'precio' => 1.50,
                'kilogramos' => 100.00,
                'valor_venta' => 150.00,
                'created_by' => $this->adminUser->id,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('ventas.index'));

        $response->assertStatus(200);

        // Clases del diseño agrícola
        $response->assertSee('fundo-pagination-container');
        $response->assertSee('fundo-pagination-summary');
        $response->assertSee('fundo-pagination-nav');
        $response->assertSee('pagination-btn-active');

        // Textos en español claros y profesionales
        $response->assertSee('Mostrando del');
        $response->assertSee('al');
        $response->assertSee('de');
        $response->assertSee('23');
        $response->assertSee('registros');
        $response->assertSee('Anterior');
        $response->assertSee('Siguiente');

        // NUNCA debe mostrar las claves crudas sin traducir
        $response->assertDontSee('pagination.previous');
        $response->assertDontSee('pagination.next');

        // Dimensiones acotadas en iconos SVG
        $response->assertSee('width="14"', false);
        $response->assertSee('height="14"', false);

        // Estilos CSS presentes en el layout
        $response->assertSee('nav[role="navigation"] svg', false);
        $response->assertSee('width: 14px !important', false);
    }
}
