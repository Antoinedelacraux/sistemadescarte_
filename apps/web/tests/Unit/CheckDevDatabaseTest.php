<?php

namespace Tests\Unit;

use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserModelModulesTest extends TestCase
{
    public function test_modulos_constant_contains_expected_keys(): void
    {
        $expected = ['panel', 'registro', 'historial', 'admin'];
        $this->assertEquals($expected, array_keys(User::MODULOS));
    }

    public function test_user_roles_constants_exist(): void
    {
        $this->assertEquals('admin', Role::ADMIN);
        $this->assertEquals('jefe_fundo', Role::JEFE_FUNDO);
        $this->assertEquals('registrador', Role::REGISTRADOR);
        $this->assertEquals('analista', Role::ANALISTA);
        $this->assertEquals('ventas', Role::VENTAS);
    }
}
