<?php

namespace Tests\Feature\Ventas;

use App\Models\Cliente;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VentaEdicion24HorasTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $registradorUser;
    private Fundo $fundo;
    private Lote $lote;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );

        $roleRegistrador = Role::firstOrCreate(
            ['name' => Role::REGISTRADOR],
            ['display_name' => 'Registrador']
        );

        $this->adminUser = User::factory()->create([
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $this->registradorUser = User::factory()->create([
            'role_id' => $roleRegistrador->id,
            'is_active' => true,
        ]);

        $this->fundo = Fundo::create([
            'name' => 'Fundo Procom',
            'code' => 'PROCOM',
            'is_active' => true,
        ]);

        $this->fundo->users()->attach($this->registradorUser->id);

        $this->lote = Lote::withoutGlobalScopes()->create([
            'fundo_id' => $this->fundo->id,
            'nombre' => 'Lote H01',
        ]);
    }

    private function crearVentaConAntiguedad(int $horas): VentaDescarte
    {
        $fecha = Carbon::now()->subHours($horas);

        $venta = VentaDescarte::withoutGlobalScopes()->create([
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->lote->id,
            'fecha_produccion' => $fecha->toDateString(),
            'motivo' => 'Campo',
            'tipo_descarte' => 'Granos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->registradorUser->id,
            'estado' => 'activo',
        ]);

        // Forzar created_at exacto en base de datos
        $venta->created_at = $fecha;
        $venta->saveQuietly();

        return $venta->fresh();
    }

    public function test_registrador_puede_editar_venta_dentro_de_las_24_horas(): void
    {
        $venta = $this->crearVentaConAntiguedad(5);

        $this->assertTrue($venta->puedeSerEditadoPor($this->registradorUser));
        $this->assertFalse($venta->isCerradoParaEdicion());

        $response = $this->actingAs($this->registradorUser)
            ->get(route('ventas.edit', $venta));

        $response->assertStatus(200);
        $response->assertViewIs('ventas.edit');

        // Probar update
        $updateResponse = $this->actingAs($this->registradorUser)
            ->put(route('ventas.update', $venta), [
                'fundo_id' => $this->fundo->id,
                'lote_id' => $this->lote->id,
                'fecha_produccion' => now()->toDateString(),
                'motivo' => 'Campo',
                'tipo_descarte' => 'Granos',
                'precio' => 2.00,
                'kilogramos' => 120.00,
            ]);

        $updateResponse->assertRedirect(route('ventas.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertEquals(2.00, (float) $venta->fresh()->precio);
        $this->assertEquals(120.00, (float) $venta->fresh()->kilogramos);
    }

    public function test_registrador_no_puede_editar_venta_despues_de_las_24_horas(): void
    {
        $venta = $this->crearVentaConAntiguedad(25);

        $this->assertFalse($venta->puedeSerEditadoPor($this->registradorUser));
        $this->assertTrue($venta->isCerradoParaEdicion());

        // Intentar acceder por URL
        $response = $this->actingAs($this->registradorUser)
            ->get(route('ventas.edit', $venta));

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('error');

        // Intentar actualizar por PUT
        $updateResponse = $this->actingAs($this->registradorUser)
            ->put(route('ventas.update', $venta), [
                'fundo_id' => $this->fundo->id,
                'lote_id' => $this->lote->id,
                'fecha_produccion' => now()->toDateString(),
                'motivo' => 'Campo',
                'tipo_descarte' => 'Granos',
                'precio' => 9.99,
                'kilogramos' => 999.00,
            ]);

        $updateResponse->assertRedirect(route('ventas.index'));
        $updateResponse->assertSessionHas('error');

        // El registro no debe haber cambiado
        $this->assertEquals(1.50, (float) $venta->fresh()->precio);
        $this->assertEquals(100.00, (float) $venta->fresh()->kilogramos);
    }

    public function test_administrador_puede_editar_venta_incluso_despues_de_las_24_horas(): void
    {
        $venta = $this->crearVentaConAntiguedad(72); // 3 días después

        $this->assertTrue($venta->puedeSerEditadoPor($this->adminUser));
        $this->assertTrue($venta->isCerradoParaEdicion()); // Está cerrado para otros, pero admin puede

        $response = $this->actingAs($this->adminUser)
            ->get(route('ventas.edit', $venta));

        $response->assertStatus(200);

        $updateResponse = $this->actingAs($this->adminUser)
            ->put(route('ventas.update', $venta), [
                'fundo_id' => $this->fundo->id,
                'lote_id' => $this->lote->id,
                'fecha_produccion' => now()->toDateString(),
                'motivo' => 'Campo',
                'tipo_descarte' => 'Granos',
                'precio' => 3.00,
                'kilogramos' => 200.00,
            ]);

        $updateResponse->assertRedirect(route('ventas.index'));
        $this->assertEquals(3.00, (float) $venta->fresh()->precio);
    }

    public function test_registrador_no_puede_anular_venta_despues_de_las_24_horas(): void
    {
        $venta = $this->crearVentaConAntiguedad(26);

        $this->assertFalse($venta->puedeSerAnuladoPor($this->registradorUser));

        $response = $this->actingAs($this->registradorUser)
            ->post(route('ventas.anular', $venta), ['motivo_anulacion' => 'Error de pesaje']);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('error');

        $this->assertTrue($venta->fresh()->isActivo());
    }

    public function test_administrador_puede_anular_venta_despues_de_las_24_horas(): void
    {
        $venta = $this->crearVentaConAntiguedad(48);

        $this->assertTrue($venta->puedeSerAnuladoPor($this->adminUser));

        $response = $this->actingAs($this->adminUser)
            ->post(route('ventas.anular', $venta), ['motivo_anulacion' => 'Auditoría administrativa']);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        $this->assertTrue($venta->fresh()->isAnulado());
    }

    public function test_vistas_ocultan_botones_de_edicion_para_registrador_cuando_supera_24_horas(): void
    {
        $ventaCerrada = $this->crearVentaConAntiguedad(30);

        $responseIndex = $this->actingAs($this->registradorUser)
            ->get(route('ventas.index'));

        $responseIndex->assertStatus(200);
        // data-url-edit no debe estar presente para la venta cerrada
        $responseIndex->assertDontSee('data-url-edit="' . route('ventas.edit', $ventaCerrada) . '"', false);
        // El link de edición directo no debe estar
        $responseIndex->assertDontSee('href="' . route('ventas.edit', $ventaCerrada) . '"', false);

        // En show.blade.php
        $responseShow = $this->actingAs($this->registradorUser)
            ->get(route('ventas.show', $ventaCerrada));

        $responseShow->assertStatus(200);
        $responseShow->assertSee('Cerrado (&gt;24h)', false);
        $responseShow->assertDontSee('✏️ Editar Registro');
    }
}
