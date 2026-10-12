<?php

namespace Tests\Feature\Reportes;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardExportVolumenDecimalTest extends TestCase
{
    use RefreshDatabase;

    private User $analistaUser;
    private Fundo $fundo;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(
            ['name' => Role::JEFE_FUNDO],
            ['display_name' => 'Jefe de fundo']
        );

        $this->fundo = Fundo::where('code', 'AGRITAC')->first() ?? Fundo::create([
            'name' => 'AGRITAC',
            'code' => 'AGRITAC',
            'is_active' => true,
        ]);

        $this->analistaUser = User::create([
            'name' => 'Analista Test',
            'email' => 'analista.test@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $this->analistaUser->fundos()->attach([$this->fundo->id]);

        // Crear ventas proporcionales
        // Venta 1: 600 kg (15.96% aprox de 3760.20 kg)
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundo->id,
            'fecha_produccion' => '2026-10-12',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos',
            'precio' => 5.20,
            'kilogramos' => 600.00,
            'valor_venta' => 3120.00,
            'created_by' => $this->analistaUser->id,
        ]);

        // Venta 2: 2560.20 kg (68.09% aprox)
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundo->id,
            'fecha_produccion' => '2026-10-12',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Granos',
            'precio' => 0.55,
            'kilogramos' => 2560.20,
            'valor_venta' => 1408.11,
            'created_by' => $this->analistaUser->id,
        ]);

        // Venta 3: 600.00 kg (15.96% aprox)
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundo->id,
            'fecha_produccion' => '2026-10-12',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.20,
            'kilogramos' => 600.00,
            'valor_venta' => 720.00,
            'created_by' => $this->analistaUser->id,
        ]);
    }

    public function test_dashboard_displays_volumen_with_two_decimals(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('dashboard'));

        $response->assertStatus(200);
        // Debe mostrarse con 2 decimales y no como entero redondeado (15.96% en vez de 16%)
        $response->assertSee('15.96%');
        $response->assertSee('68.09%');
        $response->assertSee('100.00%');
    }

    public function test_dashboard_export_motivos_downloads_xlsx_with_decimal_volumen(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('dashboard.exportar', ['tabla' => 'motivos']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="resumen_tipo_descarte_', (string) $response->headers->get('Content-Disposition'));
        $this->assertNotEmpty($response->getContent());
    }

    public function test_dashboard_renders_cliente_matrices_for_kg_and_monto(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('table-matrix-kg');
        $response->assertSee('table-matrix-monto');
        $response->assertSee('DESCARTE CAMPO');
        $response->assertSee('DESCARTE PACKING');
        $response->assertSee('COSECHA NACIONAL');
        $response->assertSee('RACIMOS CON PLAGA');
        $response->assertSee('TOTAL KG');
        $response->assertSee('TOTAL S/');
    }

    public function test_dashboard_export_clientes_downloads_xlsx_with_stacked_matrices_without_total_general(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('dashboard.exportar', ['tabla' => 'clientes']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename="resumen_ventas_clientes_', (string) $response->headers->get('Content-Disposition'));
        
        $content = $response->getContent();
        $this->assertNotEmpty($content);

        // Validar contenido XML descomprimiendo el archivo .xlsx
        $tempZip = tempnam(sys_get_temp_dir(), 'test_xlsx_');
        file_put_contents($tempZip, $content);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempZip));
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tempZip);

        $this->assertNotEmpty($sheetXml);
        // Debe contener los dos títulos/matrices una debajo de la otra
        $this->assertStringContainsString('1. DISTRIBUCIÓN POR VOLUMEN — KILOGRAMOS (KG)', $sheetXml);
        $this->assertStringContainsString('2. DISTRIBUCIÓN ECONÓMICA — MONTOS (SOLES S/.)', $sheetXml);
        $this->assertStringContainsString('TOTAL KG', $sheetXml);
        $this->assertStringContainsString('TOTAL S/', $sheetXml);
        // NO debe contener la fila de TOTAL GENERAL
        $this->assertStringNotContainsString('TOTAL GENERAL', $sheetXml);
    }
}
