<?php

namespace Tests\Feature\Performance;

use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Cuartel;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SpeedAndLatencyTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $registradorUser;
    private Fundo $fundo;
    private Lote $lote;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );

        $individualRole = Role::firstOrCreate(
            ['name' => Role::INDIVIDUAL],
            ['display_name' => 'Pesador / Registrador']
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

        Cuartel::firstOrCreate(
            ['lote_id' => $this->lote->id, 'nombre' => 'C01'],
            ['lote_id' => $this->lote->id, 'nombre' => 'C01']
        );

        $this->adminUser = User::firstOrCreate(
            ['email' => 'admin_perf@fundo.test'],
            [
                'name' => 'Admin Speed Test',
                'password' => Hash::make('password123'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
        $this->adminUser->fundos()->syncWithoutDetaching([$this->fundo->id]);

        $this->registradorUser = User::firstOrCreate(
            ['email' => 'pesador_perf@fundo.test'],
            [
                'name' => 'Pesador Speed Test',
                'password' => Hash::make('password123'),
                'role_id' => $individualRole->id,
                'is_active' => true,
            ]
        );
        $this->registradorUser->fundos()->syncWithoutDetaching([$this->fundo->id]);

        // Crear lote de ventas de prueba para simular carga real
        for ($i = 1; $i <= 20; $i++) {
            VentaDescarte::create([
                'id' => (string) Str::uuid(),
                'fundo_id' => $this->fundo->id,
                'lote_id' => $this->lote->id,
                'fecha_produccion' => '2026-10-10',
                'motivo' => $i % 2 === 0 ? 'Campo' : 'Cosecha Nacional',
                'tipo_descarte' => 'Racimos',
                'precio' => 1.80,
                'kilogramos' => 100 + $i,
                'valor_venta' => round(1.80 * (100 + $i), 2),
                'cliente' => 'Cliente Mayorista ' . ($i % 5),
                'created_by' => $this->registradorUser->id,
                'estado' => 'activo',
            ]);
        }
    }

    /**
     * Prueba que el Dashboard carga con máxima velocidad para administradores.
     */
    public function test_dashboard_loads_quickly_under_benchmark(): void
    {
        $start = microtime(true);
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $response->assertSee('Últimos Envíos Registrados');
        $this->assertLessThan(400, $elapsedMs, "El Dashboard tardó {$elapsedMs}ms, excediendo el límite de 400ms.");
    }

    /**
     * Prueba que la pantalla crítica de registro en campo carga instantáneamente para pesadores.
     */
    public function test_ventas_create_loads_quickly_without_redundant_queries(): void
    {
        DB::enableQueryLog();

        $start = microtime(true);
        $response = $this->actingAs($this->registradorUser)->get(route('ventas.create'));
        $elapsedMs = (microtime(true) - $start) * 1000;

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertStatus(200);
        $response->assertSee('Registrar Venta de Descarte');
        $this->assertLessThan(300, $elapsedMs, "La pantalla de registro tardó {$elapsedMs}ms, excediendo el límite de 300ms.");

        // Verificar que no se hacen consultas duplicadas o no preparadas
        $this->assertNotEmpty($queries);
    }

    /**
     * Prueba que el historial de ventas paginado carga velozmente.
     */
    public function test_ventas_index_loads_quickly(): void
    {
        $start = microtime(true);
        $response = $this->actingAs($this->adminUser)->get(route('ventas.index'));
        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $response->assertSee('Historial de Ventas');
        $this->assertLessThan(350, $elapsedMs, "El historial tardó {$elapsedMs}ms, excediendo el límite de 350ms.");
    }

    /**
     * Prueba que el módulo de administración carga velozmente.
     */
    public function test_admin_fundos_loads_quickly(): void
    {
        $start = microtime(true);
        $response = $this->actingAs($this->adminUser)->get(route('admin.fundos'));
        $elapsedMs = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $response->assertSee('Administración de Fundos');
        $this->assertLessThan(350, $elapsedMs, "El módulo de fundos tardó {$elapsedMs}ms, excediendo el límite de 350ms.");
    }

    /**
     * Prueba que los activos PWA (manifest y service worker) son válidos y contienen optimizaciones v6.
     */
    public function test_pwa_assets_and_service_worker_contain_speed_optimizations(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);
        $manifestJson = json_decode(file_get_contents($manifestPath), true);

        $this->assertSame('standalone', $manifestJson['display']);
        $this->assertSame('/', $manifestJson['start_url']);
        $this->assertNotEmpty($manifestJson['shortcuts']);

        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);
        $swContent = file_get_contents($swPath);

        $this->assertStringContainsString('fundo-pwa-v6', $swContent);
        $this->assertStringContainsString('/ventas/registrar', $swContent);
        $this->assertStringContainsString('/ventas', $swContent);
    }

    /**
     * Prueba que el layout HTML inyecta las reglas de navegación instantánea (Speculation Rules y prefetch).
     */
    public function test_instant_navigation_rules_and_prefetch_are_present(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('speculationrules');
        $response->assertSee('prefetchUrl');
        $response->assertSee('touch-action: manipulation');
    }
}
