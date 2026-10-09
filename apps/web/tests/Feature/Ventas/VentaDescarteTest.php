<?php

namespace Tests\Feature\Ventas;

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class VentaDescarteTest extends TestCase
{
    use RefreshDatabase;

    private User $individualUser;
    private User $generalElenaUser;
    private Fundo $fundoSofia;
    private Fundo $fundoElena;
    private Lote $loteSofia;
    private Cuartel $cuartelSofia;

    protected function setUp(): void
    {
        parent::setUp();

        $roleIndividual = Role::create([
            'name' => Role::INDIVIDUAL,
            'display_name' => 'Individual',
        ]);

        $roleGeneral = Role::create([
            'name' => Role::GENERAL,
            'display_name' => 'General',
        ]);

        $this->fundoSofia = Fundo::create([
            'name' => 'Fundo Santa Sofía',
            'code' => 'FSOFIA',
            'is_active' => true,
        ]);

        $this->fundoElena = Fundo::create([
            'name' => 'Fundo Santa Elena',
            'code' => 'FELENA',
            'is_active' => true,
        ]);

        $this->loteSofia = Lote::withoutGlobalScopes()->create([
            'fundo_id' => $this->fundoSofia->id,
            'nombre' => 'Lote 01 Norte',
        ]);

        $this->cuartelSofia = Cuartel::create([
            'lote_id' => $this->loteSofia->id,
            'nombre' => 'Cuartel A',
        ]);

        $this->individualUser = User::create([
            'name' => 'Registrador Sofía',
            'email' => 'registrador@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
        $this->individualUser->fundos()->attach([$this->fundoSofia->id]);

        $this->generalElenaUser = User::create([
            'name' => 'Jefe Elena',
            'email' => 'jefe.elena@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $roleGeneral->id,
            'is_active' => true,
        ]);
        $this->generalElenaUser->fundos()->attach([$this->fundoElena->id]);
    }

    public function test_authenticated_user_can_view_ventas_index(): void
    {
        $response = $this->actingAs($this->individualUser)->get(route('ventas.index'));

        $response->assertStatus(200);
        $response->assertSee('Historial de Ventas de Descarte');
    }

    public function test_user_can_view_create_form(): void
    {
        $response = $this->actingAs($this->individualUser)->get(route('ventas.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrar Venta de Descarte');
        $response->assertSee('Fundo Santa Sofía');
    }

    public function test_user_can_store_venta_descarte_with_automatic_total_calculation(): void
    {
        $payload = [
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => $this->cuartelSofia->id,
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'jabas' => 5,
            'peso_jaba' => 20.00,
            'placa' => 'ABC-123',
            'conductor' => 'Juan Pérez',
        ];

        $response = $this->actingAs($this->individualUser)->post(route('ventas.store'), $payload);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        // Comprobar persistencia y cálculo automático: 1.50 * 100.00 = 150.00
        $this->assertDatabaseHas('ventas_descarte', [
            'fundo_id' => $this->fundoSofia->id,
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->individualUser->id,
        ]);
    }

    public function test_cuartel_is_mandatory_when_motivo_is_cosecha_nacional(): void
    {
        $payload = [
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Granos',
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => null,
            'cuartel_manual' => null, // Dejar vacío deliberadamente
            'precio' => 2.00,
            'kilogramos' => 50.00,
        ];

        $response = $this->actingAs($this->individualUser)
            ->from(route('ventas.create'))
            ->post(route('ventas.store'), $payload);

        $response->assertRedirect(route('ventas.create'));
        $response->assertSessionHasErrors('cuartel_manual');
    }

    public function test_individual_user_cannot_register_sale_in_unauthorized_fundo(): void
    {
        // Intento de registrar en Santa Elena teniendo asignado solo Santa Sofía
        $payload = [
            'fundo_id' => $this->fundoElena->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteSofia->id,
            'precio' => 1.50,
            'kilogramos' => 100.00,
        ];

        $response = $this->actingAs($this->individualUser)
            ->from(route('ventas.create'))
            ->post(route('ventas.store'), $payload);

        $response->assertRedirect(route('ventas.create'));
        $response->assertSessionHasErrors('fundo_id');
    }

    public function test_fundoscope_isolates_records_between_fundos(): void
    {
        // Venta en Santa Sofía
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->individualUser->id,
        ]);

        // Venta en Santa Elena
        VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoElena->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Granos',
            'precio' => 2.00,
            'kilogramos' => 80.00,
            'valor_venta' => 160.00,
            'created_by' => $this->generalElenaUser->id,
        ]);

        // El usuario de Santa Sofía solo ve 1 venta
        $this->actingAs($this->individualUser);
        $this->assertCount(1, VentaDescarte::all());

        // El usuario de Santa Elena solo ve su venta
        $this->actingAs($this->generalElenaUser);
        $this->assertCount(1, VentaDescarte::all());
        $this->assertEquals($this->fundoElena->id, VentaDescarte::first()->fundo_id);
    }

    public function test_user_can_update_existing_sale_with_audit_trail(): void
    {
        $venta = VentaDescarte::withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoSofia->id,
            'lote_id' => $this->loteSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'created_by' => $this->individualUser->id,
        ]);

        $updatePayload = [
            'fundo_id' => $this->fundoSofia->id,
            'lote_id' => $this->loteSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'precio' => 2.00, // Cambio de precio
            'kilogramos' => 100.00,
        ];

        $response = $this->actingAs($this->individualUser)
            ->put(route('ventas.update', $venta), $updatePayload);

        $response->assertRedirect(route('ventas.index'));

        $venta->refresh();
        $this->assertEquals(2.00, $venta->precio);
        $this->assertEquals(200.00, $venta->valor_venta);
        $this->assertEquals($this->individualUser->id, $venta->updated_by);
    }

    public function test_api_returns_lotes_for_authorized_fundo(): void
    {
        $response = $this->actingAs($this->individualUser)
            ->getJson(route('api.lotes', ['fundo_id' => $this->fundoSofia->id]));

        $response->assertStatus(200);
        $response->assertJsonFragment(['nombre' => 'Lote 01 Norte']);
    }

    public function test_cosecha_nacional_rejects_invalid_tipo_descarte(): void
    {
        $payload = [
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos con plaga', // No permitido en Cosecha Nacional
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => $this->cuartelSofia->id,
            'precio' => 2.00,
            'kilogramos' => 50.00,
        ];

        $response = $this->actingAs($this->individualUser)
            ->post(route('ventas.store'), $payload);

        $response->assertSessionHasErrors('tipo_descarte');
    }

    public function test_cosecha_nacional_accepts_racimos_and_granos(): void
    {
        $payload = [
            'fundo_id' => $this->fundoSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => $this->cuartelSofia->id,
            'precio' => 2.00,
            'kilogramos' => 50.00,
            'cliente' => 'Agro Frutas SAC',
        ];

        $response = $this->actingAs($this->individualUser)
            ->post(route('ventas.store'), $payload);

        $response->assertRedirect(route('ventas.index'));
        $this->assertDatabaseHas('ventas_descarte', [
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos',
            'cliente' => 'Agro Frutas SAC',
        ]);
    }

    public function test_ventas_index_renders_view_button_and_modal(): void
    {
        $venta = VentaDescarte::create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoSofia->id,
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => $this->cuartelSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Descarte Campo',
            'precio' => 1.50,
            'kilogramos' => 100.00,
            'valor_venta' => 150.00,
            'cliente' => 'Comercializadora Frutas',
            'created_by' => $this->individualUser->id,
        ]);

        $response = $this->actingAs($this->individualUser)->get(route('ventas.index'));

        $response->assertStatus(200);
        $response->assertSee('btn-row-view');
        $response->assertSee('abrirModalDetalle');
        $response->assertSee('modal-detalle-venta');
        $response->assertSee('Comercializadora Frutas');
    }

    public function test_user_can_view_single_venta_show_page(): void
    {
        $venta = VentaDescarte::create([
            'id' => (string) Str::uuid(),
            'fundo_id' => $this->fundoSofia->id,
            'lote_id' => $this->loteSofia->id,
            'cuartel_id' => $this->cuartelSofia->id,
            'fecha_produccion' => '2026-10-08',
            'motivo' => 'Packing',
            'tipo_descarte' => 'Descarte Packing',
            'precio' => 1.80,
            'kilogramos' => 200.00,
            'valor_venta' => 360.00,
            'cliente' => 'Distribuidora del Sur',
            'created_by' => $this->individualUser->id,
        ]);

        $response = $this->actingAs($this->individualUser)->get(route('ventas.show', $venta));

        $response->assertStatus(200);
        $response->assertSee('Ficha de Pesaje de Descarte');
        $response->assertSee('Distribuidora del Sur');
        $response->assertSee('360.00');
    }
}
