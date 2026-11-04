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

        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
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

    public function test_ventas_historial_excel_no_incluye_fila_total_general(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('ventas.exportar'));
        $response->assertStatus(200);

        $binary = $response->getContent();
        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        file_put_contents($tmpFile, $binary);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmpFile) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tmpFile);

        $this->assertStringNotContainsString('TOTAL GENERAL', $sheetXml);
    }

    public function test_ventas_historial_excel_conserva_orden_y_cantidad_exacta_de_columnas(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('ventas.exportar'));
        $response->assertOk();

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_columns_');
        file_put_contents($tmpFile, $response->getContent());
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmpFile) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tmpFile);

        preg_match('/<row r="1"[^>]*>(.*?)<\/row>/s', $sheetXml, $headerRow);
        $this->assertSame(22, substr_count($headerRow[1] ?? '', '<c '));
        $this->assertStringContainsString('<autoFilter ref="A1:V3"/>', $sheetXml);
        $this->assertStringNotContainsString('CÓDIGO DEL ENVÍO', $sheetXml);
        $this->assertTrue(strpos($sheetXml, 'FUNDO') < strpos($sheetXml, 'FECHA PRODUCCIÓN'));
        $this->assertTrue(strpos($sheetXml, 'FECHA Y HORA REGISTRO') < strpos($sheetXml, 'FECHA Y HORA VALIDACIÓN GARITA'));
        $this->assertMatchesRegularExpression('/<c r="A2" s="4" t="inlineStr">/', $sheetXml);
        $this->assertMatchesRegularExpression('/<c r="I2" s="12"><v>[0-9.]+<\/v><\/c>/', $sheetXml);
        $this->assertMatchesRegularExpression('/<c r="L[23]" s="13"><v>10<\/v><\/c>/', $sheetXml);
    }

    public function test_exportaciones_del_historial_exigen_permiso_del_modulo(): void
    {
        $sinPermiso = User::create([
            'name' => 'Sin historial',
            'email' => 'sin-historial@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => Role::firstOrCreate(['name' => Role::GARITA], ['display_name' => 'Garita'])->id,
            'is_active' => true,
        ]);

        $this->actingAs($sinPermiso)->get(route('ventas.exportar'))->assertForbidden();
    }

    public function test_las_rutas_de_exportacion_pdf_fueron_eliminadas(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('ventas.exportar-pdf'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('garita.historial.exportar-pdf'));
    }

    public function test_ventas_excel_incluye_fecha_y_hora_de_validacion_garita(): void
    {
        $fechaValidacion = now()->setDate(2026, 10, 31)->setTime(10, 6);
        $venta = VentaDescarte::first();
        $venta->update([
            'estado_garita' => 'validado',
            'salida_garita_at' => $fechaValidacion,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('ventas.exportar'));
        $response->assertOk();

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_garita_');
        file_put_contents($tmpFile, $response->getContent());
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tmpFile) === true);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tmpFile);

        $this->assertStringContainsString('FECHA Y HORA VALIDACIÓN GARITA', $sheetXml);
        $this->assertStringContainsString($fechaValidacion->format('d/m/Y H:i'), $sheetXml);
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
