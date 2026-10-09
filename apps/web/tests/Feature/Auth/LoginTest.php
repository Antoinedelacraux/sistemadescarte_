<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create([
            'name' => Role::INDIVIDUAL,
            'display_name' => 'Individual',
        ]);

        $this->user = User::create([
            'name' => 'Usuario Prueba',
            'email' => 'operador@fundo.test',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sistema para registro de venta de descarte');
        $response->assertSee('Gestión Agrícola');
        $response->assertSee('Iniciar Sesión');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $response = $this->post('/login', [
            'email' => 'operador@fundo.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($this->user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'operador@fundo.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $this->user->update(['is_active' => false]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'operador@fundo.test',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_users_can_logout(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    public function test_rate_limiter_blocks_too_many_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'operador@fundo.test',
                'password' => 'wrong-password',
            ]);
        }

        // El 6to intento debe ser bloqueado por rate limit
        $response = $this->post('/login', [
            'email' => 'operador@fundo.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertTrue(session()->get('errors')->first('email') !== 'Las credenciales proporcionadas no son válidas o la cuenta está desactivada.');
    }
}
