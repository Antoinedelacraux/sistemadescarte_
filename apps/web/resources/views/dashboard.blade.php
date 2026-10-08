@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--primary-dark);">
        Bienvenido, {{ $user->name }}
    </h1>
    <p style="color: var(--text-muted); font-size: 0.875rem;">
        Fase 3: Fundación técnica, autenticación y verificación de aislamiento multi-tenant por fundo.
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- Tarjeta Perfil y Rol -->
    <div class="card" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--primary-dark);">
            👤 Perfil de Usuario
        </h2>
        <div style="font-size: 0.875rem; line-height: 1.8;">
            <p><strong>Correo:</strong> {{ $user->email }}</p>
            <p><strong>Rol:</strong>
                <span class="badge badge-{{ $role?->name }}">
                    {{ $role?->display_name }}
                </span>
            </p>
            <p><strong>Descripción:</strong> {{ $role?->description }}</p>
            <p><strong>Estado:</strong>
                @if ($user->is_active)
                    <span style="color: var(--success); font-weight: 600;">● Activo</span>
                @else
                    <span style="color: var(--danger); font-weight: 600;">● Inactivo</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Tarjeta Fundos Asignados -->
    <div class="card" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--primary-dark);">
            📍 Fundos Asignados
        </h2>
        @if ($user->isAdmin() || $user->isAnalista())
            <div class="alert alert-success" style="margin-bottom: 0.5rem; font-size: 0.8125rem;">
                <strong>Acceso Global:</strong> Por su rol ({{ $role?->display_name }}), este usuario tiene visibilidad transversal sobre todos los fundos.
            </div>
        @endif
        <ul style="list-style: none; padding-left: 0; font-size: 0.875rem;">
            @forelse ($fundos as $fundo)
                <li style="padding: 0.375rem 0; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between;">
                    <span><strong>{{ $fundo->name }}</strong> ({{ $fundo->code }})</span>
                    <span style="color: var(--text-muted); font-size: 0.75rem;">ID: {{ $fundo->id }}</span>
                </li>
            @empty
                <li style="color: var(--text-muted); padding: 0.5rem 0;">No tiene fundos directamente asignados.</li>
            @endforelse
        </ul>
    </div>
</div>

<!-- Tarjeta de Demostración de Aislamiento de Registros (FundoScope) -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: gap;">
        <div>
            <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--primary-dark);">
                🛡️ Registros Visibles según Política de Acceso (FundoScope)
            </h2>
            <p style="color: var(--text-muted); font-size: 0.8125rem;">
                Prueba en tiempo real del Global Scope de Laravel. Los usuarios solo pueden ver registros de su propio fundo.
            </p>
        </div>
        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.8125rem;">
            Total Visibles: {{ $ventas->count() }}
        </span>
    </div>

    @if ($ventas->isEmpty())
        <div class="alert alert-danger" style="margin-bottom: 0;">
            No hay registros visibles para este usuario bajo la política actual.
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid var(--border); text-align: left;">
                        <th style="padding: 0.75rem;">Fundo</th>
                        <th style="padding: 0.75rem;">Fecha</th>
                        <th style="padding: 0.75rem;">Motivo</th>
                        <th style="padding: 0.75rem;">Tipo</th>
                        <th style="padding: 0.75rem; text-align: right;">Precio (S/)</th>
                        <th style="padding: 0.75rem; text-align: right;">Kg</th>
                        <th style="padding: 0.75rem; text-align: right;">Total (S/)</th>
                        <th style="padding: 0.75rem;">Registrado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ventas as $v)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 0.75rem;">
                                <strong>{{ $v->fundo->name ?? 'N/A' }}</strong>
                            </td>
                            <td style="padding: 0.75rem;">{{ $v->fecha_produccion->format('d/m/Y') }}</td>
                            <td style="padding: 0.75rem;">{{ $v->motivo }}</td>
                            <td style="padding: 0.75rem;">{{ $v->tipo_descarte }}</td>
                            <td style="padding: 0.75rem; text-align: right;">S/ {{ number_format($v->precio, 2) }}</td>
                            <td style="padding: 0.75rem; text-align: right;">{{ number_format($v->kilogramos, 2) }} kg</td>
                            <td style="padding: 0.75rem; text-align: right; font-weight: 600; color: var(--primary-dark);">
                                S/ {{ number_format($v->valor_venta, 2) }}
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-muted); font-size: 0.8125rem;">
                                {{ $v->creator->name ?? 'Usuario' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card" style="background-color: #f8fafc; border-left: 4px solid var(--primary);">
    <h3 style="font-size: 0.9375rem; font-weight: 600; color: var(--primary-dark); margin-bottom: 0.25rem;">
        📌 Estado de la Verificación Técnica (Fase 3)
    </h3>
    <p style="font-size: 0.8125rem; color: var(--text-muted); line-height: 1.6;">
        El login, regeneración de sesión, protección contra ataques de fuerza bruta (rate limiter), estructura de roles, fundos y el mecanismo <code>FundoScope</code> han sido implementados con éxito y están respaldados por pruebas automatizadas en PHPUnit. El siguiente paso tras su aprobación será la Fase 4 (Catálogos y Formulario).
    </p>
</div>
@endsection
