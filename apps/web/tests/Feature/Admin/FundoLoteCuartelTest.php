<?php

namespace Tests\Feature\Admin;

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use App\Models\VentaDescarte;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FundoLoteCuartelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $registrador;
    private Fundo $fundoProcom;
    private Lote $loteH01;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            ['display_name' => 'Administrador']
        );

        $roleIndividual = Role::firstOrCreate(
            ['name' => Role::INDIVIDUAL],
            ['display_name' => 'Individual']
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Principal',
                'password' => Hash::make('password123'),
                'role_id' => $roleAdmin->id,
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

        $this->loteH01 = Lote::withoutGlobalScopes()->create([
            'fundo_id' => $this->fundoProcom->id,
            'nombre' => 'H01',
        ]);

        $this->registrador = User::create([
            'name' => 'Pesador Procom',
            'email' => 'pesador@test.com',
            'password' => Hash::make('password123'),
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
        $this->registrador->fundos()->attach([$this->fundoProcom->id]);
    }

    public function test_admin_can_create_fundo_with_initial_lotes(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fundos.store'), [
            'name' => 'FUNDO TEST',
            'nombre_completo' => 'AGRICOLA DE PRUEBA SAC',
            'code' => 'FDOTEST',
            'lotes' => 'T01, T02, T03, T04',
        ]);

        $response->assertRedirect(route('admin.fundos'));
        $response->assertSessionHas('success');

        $fundo = Fundo::where('code', 'FDOTEST')->first();
        $this->assertNotNull($fundo);
        $this->assertEquals(4, $fundo->lotes()->count());
        $this->assertTrue($fundo->lotes()->where('nombre', 'T01')->exists());
        $this->assertTrue($fundo->lotes()->where('nombre', 'T04')->exists());
    }

    public function test_admin_can_add_lotes_to_existing_fundo(): void
    {
        $lotesPrevios = $this->fundoProcom->lotes()->count();
        $response = $this->actingAs($this->admin)->post(route('admin.fundos.lotes.store', $this->fundoProcom), [
            'nombre' => 'H98, H99',
        ]);

        $response->assertRedirect(route('admin.fundos'));
        $this->assertEquals($lotesPrevios + 2, $this->fundoProcom->lotes()->count());
        $this->assertTrue($this->fundoProcom->lotes()->where('nombre', 'H98')->exists());
        $this->assertTrue($this->fundoProcom->lotes()->where('nombre', 'H99')->exists());
    }

    public function test_venta_store_saves_cuartel_as_free_text_without_creating_cuartel_model(): void
    {
        $this->assertEquals(0, $this->loteH01->cuarteles()->count());

        $payload = [
            'fundo_id' => $this->fundoProcom->id,
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteH01->id,
            'cuartel_manual' => 'Cuartel Libre 01',
            'precio' => 1.50,
            'kilogramos' => 100,
        ];

        $response = $this->actingAs($this->registrador)->post(route('ventas.store'), $payload);

        $response->assertRedirect(route('ventas.index'));
        $response->assertSessionHas('success');

        // Comprobar que NO se crean registros en la tabla cuarteles
        $this->assertEquals(0, Cuartel::count());

        // Y la venta guarda el texto en cuartel_manual
        $venta = VentaDescarte::withoutGlobalScopes()->latest('created_at')->first();
        $this->assertNull($venta->cuartel_id);
        $this->assertEquals('Cuartel Libre 01', $venta->cuartel_manual);
    }

    public function test_cuartel_is_optional_in_campo_and_packing_but_required_in_cosecha_nacional(): void
    {
        // 1. Campo sin cuartel: debe pasar con éxito
        $payloadCampo = [
            'fundo_id' => $this->fundoProcom->id,
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Campo',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteH01->id,
            'cuartel_manual' => '',
            'precio' => 1.50,
            'kilogramos' => 80,
        ];
        $resCampo = $this->actingAs($this->registrador)->post(route('ventas.store'), $payloadCampo);
        $resCampo->assertRedirect(route('ventas.index'));

        // 2. Cosecha Nacional sin cuartel: debe fallar validación
        $payloadNacionalSinCuartel = [
            'fundo_id' => $this->fundoProcom->id,
            'fecha_produccion' => '2026-10-09',
            'motivo' => 'Cosecha Nacional',
            'tipo_descarte' => 'Racimos',
            'lote_id' => $this->loteH01->id,
            'cuartel_manual' => '',
            'precio' => 1.80,
            'kilogramos' => 120,
        ];
        $resNacional = $this->actingAs($this->registrador)->post(route('ventas.store'), $payloadNacionalSinCuartel);
        $resNacional->assertSessionHasErrors('cuartel_manual');
    }
}
