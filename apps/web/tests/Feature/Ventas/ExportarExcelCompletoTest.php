<?php

namespace Tests\Feature\Ventas;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExportarExcelCompletoTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private Fundo $fundoAgritac;
    private Fundo $fundoProcom;
    private Fundo $fundoElNegro;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => Role::ADMIN,
            'display_name' => 'Administrador',
        ]);

        $this->fundoAgritac = Fundo::create([
            'name' => 'AGRITAC',
            'nombre_completo' => 'AGRICOLA TAMBO COLORADO',
            'code' => 'AGRITAC',
            'is_active' => true,
        ]);

        $this->fundoProcom = Fundo::create([
            'name' => 'PROCOM',
            'nombre_completo' => 'AGRICOLA PROCOM',
            'code' => 'PROCOM',
            'is_active' => true,
        ]);

        $this->fundoElNegro = Fundo::create([
            'name' => 'EL NEGRO',
            'nombre_completo' => 'TALSA GRAPE FARMS',
            'code' => 'ELNEGRO',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);
        $this->adminUser->fundos()->attach([
            $this->fundoAgritac->id,
            $this->fundoProcom->id,
            $this->fundoElNegro->id,
        ]);

        // Registrar ventas representativas
        VentaDescarte::create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoAgritac->id,
            'fecha_produccion' => '2026-10-09',
            'cliente' => 'Distribuidora Frutas del Norte',
            'ruc' => '20601234567',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Granos',
            'precio' => 1.20,
            'kilogramos' => 180.00,
            'valor_venta' => 216.00,
            'jabas' => 10,
            'peso_jaba' => 18.00,
            'created_by' => $this->adminUser->id,
        ]);

        VentaDescarte::create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoProcom->id,
            'fecha_produccion' => '2026-10-09',
            'cliente' => 'Agroexport del Sur SAC',
            'ruc' => '20554433221',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 120.00,
            'valor_venta' => 180.00,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_ventas_historial_exporta_directamente_xlsx_valido(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('ventas.exportar'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), '.xlsx'));
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'ventas_historial_'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        $binary = $response->getContent();
        // Magic number PKZIP de OpenXML
        $this->assertStringStartsWith("PK\x03\x04", $binary);
        $this->assertGreaterThan(1000, strlen($binary));
    }

    public function test_ventas_exportar_con_filtros_activos(): void
    {
        // Filtrar por motivo 'Campo'
        $response = $this->actingAs($this->adminUser)->get(route('ventas.exportar', [
            'motivo' => 'Campo',
        ]));

        $response->assertStatus(200);
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }

    public function test_dashboard_exportar_clientes_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.exportar', [
            'tabla' => 'clientes',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'resumen_ventas_clientes_'));
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }

    public function test_dashboard_exportar_clientes_con_filtro_motivo(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.exportar', [
            'tabla' => 'clientes',
            'motivo' => 'Cosecha Nacional',
        ]));

        $response->assertStatus(200);
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }

    public function test_dashboard_exportar_motivos_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.exportar', [
            'tabla' => 'motivos',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'resumen_tipo_descarte_'));
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }

    public function test_dashboard_exportar_recientes_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.exportar', [
            'tabla' => 'recientes',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'ultimos_envios_'));
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }

    public function test_dashboard_exportar_completo_xlsx(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.exportar', [
            'tabla' => 'completo',
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'panel_control_completo_'));
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }
}
