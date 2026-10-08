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

    public function test_user_can_export_csv_with_all_default_columns(): void
    {
        $response = $this->actingAs($this->analistaUser)->get(route('reportes.exportar'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'ventas_descarte_'));
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));

        // Obtener el contenido del stream
        $content = $response->streamedContent();

        // Debe contener el Fundo y el monto
        $this->assertTrue(str_contains($content, 'Fundo Santa Sofía'));
        $this->assertTrue(str_contains($content, '150.00'));
        // Debe usar delimitador punto y coma
        $this->assertTrue(str_contains($content, ';'));
    }

    public function test_user_can_customize_exported_columns(): void
    {
        // Solo exportar 'fundo' y 'valor_venta'
        $response = $this->actingAs($this->analistaUser)->get(route('reportes.exportar', [
            'columnas' => ['fundo', 'valor_venta']
        ]));

        $response->assertStatus(200);
        $content = $response->streamedContent();

        // Primera línea debe contener solo las columnas solicitadas
        $firstLine = explode("\n", trim($content))[0];
        $this->assertTrue(str_contains($firstLine, 'Fundo'));
        $this->assertTrue(str_contains($firstLine, 'Valor Total'));
        $this->assertFalse(str_contains($firstLine, 'Kilogramos Totales'));
        $this->assertFalse(str_contains($firstLine, 'Observaciones'));
    }
}
