<?php

namespace Tests\Feature\Admin;

use App\Models\Fundo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $individualUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create([
            'name' => Role::ADMIN,
            'display_name' => 'Administrador',
        ]);

        $roleIndividual = Role::create([
            'name' => Role::INDIVIDUAL,
            'display_name' => 'Individual',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $roleAdmin->id,
            'is_active' => true,
        ]);

        $this->individualUser = User::create([
            'name' => 'Individual Test',
            'email' => 'individual@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $roleIndividual->id,
            'is_active' => true,
        ]);
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $response = $this->actingAs($this->individualUser)->get(route('admin.fundos'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->individualUser)->get(route('admin.usuarios'));
        $response2->assertStatus(403);
    }

    public function test_admin_can_view_and_create_fundo(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.fundos'));
        $response->assertStatus(200);
        $response->assertSee('Fundos Registrados');

        $payload = [
            'name' => 'Fundo Nuevo Horizonte',
            'code' => 'FNUEVO',
        ];

        $postResponse = $this->actingAs($this->adminUser)->post(route('admin.fundos.store'), $payload);
        $postResponse->assertRedirect(route('admin.fundos'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('fundos', [
            'name' => 'Fundo Nuevo Horizonte',
            'code' => 'FNUEVO',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_and_create_user_with_role_and_fundo(): void
    {
        $fundo = Fundo::create([
            'name' => 'Fundo Central',
            'code' => 'FCENTRAL',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.usuarios'));
        $response->assertStatus(200);
        $response->assertSee('Usuarios del Sistema');

        $payload = [
            'name' => 'Nuevo Operador',
            'email' => 'operador.nuevo@fundo.test',
            'password' => 'secret123',
            'role_id' => Role::where('name', Role::INDIVIDUAL)->first()->id,
            'fundos' => [$fundo->id],
        ];

        $postResponse = $this->actingAs($this->adminUser)->post(route('admin.usuarios.store'), $payload);
        $postResponse->assertRedirect(route('admin.usuarios'));
        $postResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'operador.nuevo@fundo.test',
            'name' => 'Nuevo Operador',
        ]);

        $createdUser = User::where('email', 'operador.nuevo@fundo.test')->first();
        $this->assertTrue($createdUser->fundos->contains($fundo->id));
    }
}
