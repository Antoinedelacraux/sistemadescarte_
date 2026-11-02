<?php

namespace Tests\Feature\Garita;

use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GaritaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $garitaUser;
    protected User $registradorUser;
    protected Fundo $fundo;
    protected Lote $loteA;
    protected Lote $loteB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => Role::ADMIN], ['display_name' => 'Admin']);
        $garitaRole = Role::firstOrCreate(['name' => Role::GARITA], ['display_name' => 'Garita']);
        $registradorRole = Role::firstOrCreate(['name' => Role::REGISTRADOR], ['display_name' => 'Registrador']);

        // 2. Fundo y Lotes
        $this->fundo = Fundo::firstOrCreate(
            ['code' => 'AGRITAC'],
            ['name' => 'AGRITAC', 'nombre_completo' => 'AGRICOLA TAMBO COLORADO', 'is_active' => true]
        );

        $this->loteA = Lote::firstOrCreate(['fundo_id' => $this->fundo->id, 'nombre' => 'A01']);
        $this->loteB = Lote::firstOrCreate(['fundo_id' => $this->fundo->id, 'nombre' => 'A02']);

        // 3. Usuarios
        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->garitaUser = User::create([
            'name' => 'Garita Test',
            'email' => 'garita@test.local',
            'password' => bcrypt('password'),
            'role_id' => $garitaRole->id,
            'is_active' => true,
        ]);
        $this->garitaUser->fundos()->attach($this->fundo->id);

        $this->registradorUser = User::create([
            'name' => 'Registrador Test',
            'email' => 'registrador@test.local',
            'password' => bcrypt('password'),
            'role_id' => $registradorRole->id,
            'is_active' => true,
        ]);
        $this->registradorUser->fundos()->attach($this->fundo->id);
    }

    public function test_borrador_no_se_envia_incompleto(): void
    {
        $venta = VentaDescarte::create([
            'fundo_id' => $this->fundo->id, 'lote_id' => $this->loteA->id,
            'fecha_produccion' => now()->subDays(3)->toDateString(),
            'motivo' => 'Campo', 'tipo_descarte' => 'Granos',
            'precio' => 2, 'kilogramos' => 100, 'valor_venta' => 200,
            'created_by' => $this->registradorUser->id,
        ]);
        $this->actingAs($this->registradorUser)->post(route('ventas.enviar-garita', $venta))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'conductor'));
        $this->assertSame('borrador', $venta->fresh()->estado_garita);
    }

    public function test_viaje_con_un_lote_incompleto_no_se_envia_parcialmente(): void
    {
        $datos = [
            'fundo_id' => $this->fundo->id, 'lote_id' => $this->loteA->id,
            'fecha_produccion' => now()->toDateString(), 'motivo' => 'Campo',
            'tipo_descarte' => 'Granos', 'precio' => 2, 'kilogramos' => 100,
            'valor_venta' => 200, 'jabas' => 5, 'cliente' => 'Cliente prueba',
            'conductor' => 'Juan Perez', 'placa' => 'ABC-123', 'viaje' => '1',
            'created_by' => $this->registradorUser->id,
        ];
        $primera = VentaDescarte::create($datos);
        $segunda = VentaDescarte::create(array_merge($datos, ['lote_id' => $this->loteB->id, 'cliente' => null]));

        $this->actingAs($this->registradorUser)->post(route('ventas.enviar-garita', $primera))
            ->assertSessionHas('error', fn ($mensaje) => str_contains($mensaje, 'cliente'));
        $this->assertSame('borrador', $primera->fresh()->estado_garita);
        $this->assertSame('borrador', $segunda->fresh()->estado_garita);
    }

    public function test_garita_user_is_redirected_to_garita_panel_from_home(): void
    {
        $response = $this->actingAs($this->garitaUser)->get('/');
        $response->assertRedirect(route('garita.index'));
    }

    public function test_registrador_without_garita_module_cannot_access_garita_index(): void
    {
        $response = $this->actingAs($this->registradorUser)->get(route('garita.index'));
        $response->assertStatus(403);
    }

    public function test_garita_user_can_view_garita_panel(): void
    {
        $response = $this->actingAs($this->garitaUser)->get(route('garita.index'));
        $response->assertStatus(200);
        $response->assertSee('Panel de Salidas de Camiones — Garita');
        $response->assertSee('Envíos pendientes');
        $response->assertSee('Ver historial de validaciones');
        $response->assertSee('Fecha de producción');
    }

    public function test_registrador_can_send_trip_to_garita_and_cancel_it(): void
    {
        // 1. Crear dos registros que comparten el mismo viaje y camión (2 lotes distintos)
        $venta1 = VentaDescarte::create([
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->loteA->id,
            'fecha_produccion' => now()->format('Y-m-d'),
            'motivo' => 'Campo',
            'tipo_descarte' => 'Descarte I',
            'precio' => 1.50,
            'kilogramos' => 1000,
            'valor_venta' => 1500,
            'jabas' => 50,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Perez',
            'viaje' => '1',
            'cliente' => 'AgroExport',
            'created_by' => $this->registradorUser->id,
        ]);

        $venta2 = VentaDescarte::create([
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->loteB->id,
            'fecha_produccion' => now()->format('Y-m-d'),
            'motivo' => 'Campo',
            'tipo_descarte' => 'Descarte II',
            'precio' => 1.20,
            'kilogramos' => 800,
            'valor_venta' => 960,
            'jabas' => 40,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Perez',
            'viaje' => '1',
            'cliente' => 'AgroExport',
            'created_by' => $this->registradorUser->id,
        ]);

        $ventaOtroNumero = VentaDescarte::create([
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->loteA->id,
            'fecha_produccion' => now()->format('Y-m-d'),
            'motivo' => 'Campo',
            'tipo_descarte' => 'Granos',
            'precio' => 1,
            'kilogramos' => 10,
            'valor_venta' => 10,
            'jabas' => 1,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Perez',
            'viaje' => '2',
            'cliente' => 'AgroExport',
            'created_by' => $this->registradorUser->id,
        ]);
        $ventaMismoNumeroOtraFecha = VentaDescarte::create([
            'fundo_id' => $this->fundo->id,
            'lote_id' => $this->loteA->id,
            'fecha_produccion' => now()->subDay()->format('Y-m-d'),
            'motivo' => 'Campo',
            'tipo_descarte' => 'Granos',
            'precio' => 1,
            'kilogramos' => 20,
            'valor_venta' => 20,
            'jabas' => 2,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Perez',
            'viaje' => '1',
            'cliente' => 'AgroExport',
            'created_by' => $this->registradorUser->id,
        ]);

        $this->assertEquals('borrador', $venta1->fresh()->estado_garita);
        $this->assertEquals('borrador', $venta2->fresh()->estado_garita);

        // 2. Registrador envía el viaje a Garita
        $response = $this->actingAs($this->registradorUser)
            ->post(route('ventas.enviar-garita', $venta1), [
                'enviar_viaje' => true,
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals('pendiente', $venta1->fresh()->estado_garita);
        $this->assertEquals('pendiente', $venta2->fresh()->estado_garita);
        $this->assertEquals('borrador', $ventaOtroNumero->fresh()->estado_garita);
        $this->assertEquals('borrador', $ventaMismoNumeroOtraFecha->fresh()->estado_garita);

        $ventaOtroNumero->update(['estado_garita' => 'pendiente']);
        $ventaMismoNumeroOtraFecha->update(['estado_garita' => 'pendiente']);
        $mixedTrips = $this->actingAs($this->garitaUser)->post(route('garita.confirmar-salida'), [
            'venta_ids' => [$venta1->id, $venta2->id, $ventaOtroNumero->id, $ventaMismoNumeroOtraFecha->id],
        ]);
        $mixedTrips->assertSessionHas('error');
        $this->assertEquals('pendiente', $venta1->fresh()->estado_garita);
        $this->assertEquals('pendiente', $ventaMismoNumeroOtraFecha->fresh()->estado_garita);
        $ventaOtroNumero->update(['estado_garita' => 'borrador']);
        $ventaMismoNumeroOtraFecha->update(['estado_garita' => 'borrador']);

        // 3. Verificar que Garita ve 1 camión pendiente con el total sumado (1800 kg, 90 jabas)
        $panel = $this->actingAs($this->garitaUser)->get(route('garita.index'));
        $panel->assertStatus(200);
        $panel->assertSee('ABC-123');
        $panel->assertSee('Juan Perez');
        $panel->assertSee('1,800.00 kg');
        $panel->assertSee('Pendiente de validación');
        $panel->assertSee('Fecha y hora de registro');
        $panel->assertSee('Lotes del envío');
        $panel->assertSee('data-viaje="1"', false);

        // 4. API de notificaciones cuenta 1 camión pendiente
        $api = $this->actingAs($this->garitaUser)->get(route('garita.pendientes-count'));
        $api->assertJson(['count' => 1]);

        // 5. Registrador cancela el envío para editar
        $cancel = $this->actingAs($this->registradorUser)
            ->post(route('ventas.cancelar-garita', $venta1), [
                'cancelar_viaje' => true,
            ]);

        $cancel->assertSessionHas('success');
        $this->assertEquals('borrador', $venta1->fresh()->estado_garita);
        $this->assertEquals('borrador', $venta2->fresh()->estado_garita);

        // 6. Volver a enviar a Garita
        $this->actingAs($this->registradorUser)
            ->post(route('ventas.enviar-garita', $venta1), ['enviar_viaje' => true]);

        // 7. Garita confirma la salida física
        $confirm = $this->actingAs($this->garitaUser)
            ->post(route('garita.confirmar-salida'), [
                'venta_ids' => [$venta1->id, $venta2->id],
                'observacion_garita' => 'Salida autorizada conforme con precinto 9988.',
            ]);

        $confirm->assertSessionHas('success');
        $this->assertEquals('validado', $venta1->fresh()->estado_garita);
        $this->assertEquals('validado', $venta2->fresh()->estado_garita);
        $this->assertNotNull($venta1->fresh()->salida_garita_at);
        $this->assertEquals($this->garitaUser->id, $venta1->fresh()->salida_garita_by);

        $history = $this->actingAs($this->garitaUser)->get(route('garita.historial'));
        $history->assertOk();
        $history->assertSee('Historial de Validaciones');
        $history->assertSee('Fecha y hora de validación');
        $history->assertSee('Fecha de producción');
        $history->assertSee($venta1->fresh()->salida_garita_at->format('d/m/Y H:i'));
        $history->assertSee('1,800.00 kg');
        $history->assertSee('90');
        $history->assertSee('Número de viaje');
        $history->assertSee('<td class="nowrap">1</td>', false);

        $salesHistory = $this->actingAs($this->adminUser)->get(route('ventas.index'));
        $salesHistory->assertOk();
        $salesHistory->assertSee('Fecha y hora validación');
        $salesHistory->assertSee($venta1->fresh()->salida_garita_at->format('d/m/Y H:i'));

        config(['app.powerbi_api_token' => 'garita-test-token']);
        $apiVentas = $this->getJson('/api/v1/ventas-descarte', [
            'Authorization' => 'Bearer garita-test-token',
        ]);
        $apiVentas->assertOk();
        $apiVenta = collect($apiVentas->json('data'))->firstWhere('id', $venta1->id);
        $this->assertSame($venta1->fresh()->salida_garita_at->toIso8601String(), $apiVenta['fecha_hora_validacion_garita']);

        // El mismo número de viaje en otra fecha es otro envío independiente.
        $sendOtherDate = $this->actingAs($this->registradorUser)
            ->post(route('ventas.enviar-garita', $ventaMismoNumeroOtraFecha));
        $sendOtherDate->assertSessionHas('success');
        $this->assertEquals('pendiente', $ventaMismoNumeroOtraFecha->fresh()->estado_garita);
        $this->actingAs($this->garitaUser)->post(route('garita.confirmar-salida'), [
            'venta_ids' => [$ventaMismoNumeroOtraFecha->id],
        ])->assertSessionHas('success');

        $historyWithRepeatedTripNumber = $this->actingAs($this->garitaUser)->get(route('garita.historial'));
        $historyHtml = $historyWithRepeatedTripNumber->getContent();
        $this->assertSame(2, substr_count($historyHtml, '<td class="nowrap">1</td>'));
        $this->assertStringContainsString($ventaMismoNumeroOtraFecha->fecha_produccion->format('d/m/Y'), $historyHtml);

        // 8. Intentar cancelar después de validado debe ser denegado
        $this->assertFalse($venta1->fresh()->puedeCancelarEnvioGarita($this->registradorUser));
        $this->assertFalse($venta1->fresh()->puedeSerEditadoPor($this->registradorUser));
    }

    public function test_garita_filtra_envios_completos_y_respeta_fundos_asignados(): void
    {
        $otroFundo = Fundo::create(['code' => 'OTRO', 'name' => 'OTRO', 'nombre_completo' => 'Otro Fundo', 'is_active' => true]);
        $otroLote = Lote::create(['fundo_id' => $otroFundo->id, 'nombre' => 'X01']);
        $crear = function (array $campos): VentaDescarte {
            return VentaDescarte::create(array_merge([
                'fundo_id' => $this->fundo->id, 'lote_id' => $this->loteA->id,
                'fecha_produccion' => '2026-10-30', 'motivo' => 'Campo', 'tipo_descarte' => 'Granos',
                'precio' => 1, 'kilogramos' => 10, 'valor_venta' => 10, 'jabas' => 2,
                'placa' => 'AAA-001', 'conductor' => 'Carlos Perez', 'viaje' => '1',
                'cliente' => 'Cliente Uno', 'created_by' => $this->registradorUser->id,
                'estado_garita' => 'pendiente',
            ], $campos));
        };

        $primero = $crear([]);
        $segundo = $crear(['lote_id' => $this->loteB->id, 'cliente' => 'Cliente Dos', 'kilogramos' => 20, 'valor_venta' => 20]);
        $ajeno = $crear(['fundo_id' => $otroFundo->id, 'lote_id' => $otroLote->id, 'cliente' => 'Cliente Restringido', 'placa' => 'ZZZ-999']);
        $crear(['viaje' => '2', 'cliente' => 'Validado Permitido', 'estado_garita' => 'validado', 'salida_garita_at' => now()]);
        $crear(['fundo_id' => $otroFundo->id, 'lote_id' => $otroLote->id, 'viaje' => '2', 'cliente' => 'Validado Restringido', 'estado_garita' => 'validado', 'salida_garita_at' => now()]);

        $panel = $this->actingAs($this->garitaUser)->get(route('garita.index', [
            'fecha' => '2026-10-30', 'viaje' => '1', 'cliente' => 'Dos', 'conductor' => 'perez',
        ]));
        $panel->assertOk()->assertSee('Cliente Uno')->assertSee('Cliente Dos')->assertSee('30.00 kg');
        $panel->assertDontSee('Cliente Restringido');
        $panel->assertSee($primero->id)->assertSee($segundo->id);
        $panel->assertDontSee('Total Kilos Despachados');

        $historial = $this->actingAs($this->garitaUser)->get(route('garita.historial', [
            'fecha' => '2026-10-30', 'viaje' => '2', 'cliente' => 'Permitido', 'conductor' => 'Carlos',
        ]));
        $historial->assertOk()->assertSee('Validado Permitido')->assertDontSee('Validado Restringido');
        $panelValidados = $this->actingAs($this->garitaUser)->get(route('garita.index', [
            'estado' => 'validado', 'fecha' => '2026-10-30', 'viaje' => '2',
        ]));
        $panelValidados->assertOk()->assertSee('Validado Permitido')->assertSee('Validado el')->assertDontSee('Validado Restringido');
        $panelValidados->assertDontSee('data-ids=', false);
        $this->actingAs($this->garitaUser)->get(route('garita.pendientes-count'))->assertJson(['count' => 1]);

        $this->actingAs($this->garitaUser)->post(route('garita.confirmar-salida'), ['venta_ids' => [$ajeno->id]])
            ->assertSessionHas('error');
        $this->assertSame('pendiente', VentaDescarte::withoutGlobalScopes()->findOrFail($ajeno->id)->estado_garita);
        $this->actingAs($this->garitaUser)->post(route('garita.confirmar-salida'), ['venta_ids' => [$primero->id, $ajeno->id]])
            ->assertSessionHas('error');
        $this->assertSame('pendiente', VentaDescarte::withoutGlobalScopes()->findOrFail($primero->id)->estado_garita);

        $this->garitaUser->fundos()->attach($otroFundo->id);
        $this->actingAs($this->garitaUser)->get(route('garita.index'))->assertSee('Cliente Restringido')->assertSee('Todos mis fundos');
        $this->actingAs($this->garitaUser)->get(route('garita.historial'))->assertSee('Validado Permitido')->assertSee('Validado Restringido')->assertSee('Fundo');
        $this->actingAs($this->garitaUser)->get(route('garita.index', ['fundo_id' => $otroFundo->id]))
            ->assertSee('Cliente Restringido')->assertDontSee('Cliente Uno');
        $this->actingAs($this->garitaUser)->post(route('garita.confirmar-salida'), ['venta_ids' => [$ajeno->id]])
            ->assertSessionHas('success');
        $this->assertSame('validado', VentaDescarte::withoutGlobalScopes()->findOrFail($ajeno->id)->estado_garita);

        $sinFundo = User::create([
            'name' => 'Garita sin asignación', 'email' => 'sin-fundo@test.local', 'password' => bcrypt('password'),
            'role_id' => $this->garitaUser->role_id, 'is_active' => true,
        ]);
        $this->actingAs($sinFundo)->get(route('garita.index'))->assertOk()->assertDontSee('Cliente Uno')->assertSee('No tienes fundos activos asignados');
        $this->actingAs($sinFundo)->get(route('garita.pendientes-count'))->assertJson(['count' => 0]);
    }
}
