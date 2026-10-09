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

class ReporteTest extends TestCase
{
    use RefreshDatabase;

    private User $analistaUser;
    private Fundo $fundoSofia;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAnalista = Role::create([
            'name' => Role::ANALISTA,
            'display_name' => 'Analista',
        ]);

        $this->fundoSofia = Fundo::create([
            'name' => 'Fundo Santa Sofía',
            'code' => 'FSOFIA',
            'is_active' => true,
        ]);

        $this->analistaUser = User::create([
            'name' => 'Analista Central',
            'email' => 'analista@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $roleAnalista->id,
            'is_active' => true,
        ]);
        $this->analistaUser->fundos()->attach([$this->fundoSofia->id]);

        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->analistaUser->id,
        ]);
    }

    public function test_analista_can_view_reportes_index(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('reportes.index'));

        $response->assertStatus(200);
        $response->assertSee('Reportes y Análisis de Descarte');
        $response->assertSee('Descargar Archivo Excel');
    }

    public function test_user_can_export_xlsx_file(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('reportes.exportar'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), '.xlsx'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));

        $binary = $response->getContent();
        // Verificar que inicia con el magic number de ZIP/XLSX: PK\x03\x04 (0x504b0304)
        $this->assertStringStartsWith("PK\x03\x04", $binary);
        $this->assertGreaterThan(500, strlen($binary));
    }

    public function test_user_can_customize_exported_columns(): void
    {
        // Solo exportar 'fundo' y 'valor_venta'
        $response = $this->actingAs($this->analistaUser)->get(route('reportes.exportar', [
            'columnas' => ['fundo', 'valor_venta']
        ]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), '.xlsx'));
        $binary = $response->getContent();
        $this->assertStringStartsWith("PK\x03\x04", $binary);
    }
}
