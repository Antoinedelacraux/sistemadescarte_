@extends('layouts.app')

@section('title', 'Panel de Control')
@section('page-title', 'Panel de Control')

@section('styles')
<style>
    /* Tarjetas de bienvenida por rol */
    .welcome-banner {
        background: linear-gradient(135deg, var(--clr-primary-800) 0%, var(--clr-primary-900) 100%);
        border-radius: var(--radius-xl);
        padding: 1.75rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(34,197,94,0.10);
        pointer-events: none;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -60px;
        right: 60px;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: rgba(34,197,94,0.06);
        pointer-events: none;
    }

    .welcome-greeting {
        font-size: var(--text-xs);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--clr-primary-300);
        font-weight: 600;
        margin-bottom: 0.375rem;
    }

    .welcome-name {
        font-size: 1.625rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        position: relative;
        z-index: 1;
    }

    .welcome-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 1rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }

    .welcome-meta-item {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: var(--text-xs);
        color: rgba(255,255,255,0.6);
    }

    .welcome-meta-item strong {
        color: rgba(255,255,255,0.9);
    }

    /* Grid principal del dashboard */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 560px) { .dashboard-grid { grid-template-columns: 1fr; } }

    /* Grid de contenido */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }

    /* Tabla del dashboard */
    .dash-table th {
        padding: 0.5rem 0.875rem;
        background: var(--clr-surface-50);
    }

    .dash-table td {
        padding: 0.6875rem 0.875rem;
    }

    /* Estado de sync */
    .sync-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.125rem 0.4375rem;
        font-size: 0.625rem;
        font-weight: 700;
        border-radius: var(--radius-full);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .sync-synced  { background: var(--clr-success-bg);  color: var(--clr-success); }
    .sync-pending { background: var(--clr-warning-bg);  color: var(--clr-warning); }
    .sync-error   { background: var(--clr-danger-bg);   color: var(--clr-danger); }

    /* Panel de fundos asignados */
    .fundo-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--clr-surface-100);
    }

    .fundo-item:last-child { border-bottom: none; }

    .fundo-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--clr-primary-500);
        flex-shrink: 0;
    }

    /* Tag de rol destacado */
    .role-card {
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        border: 1px solid;
        margin-bottom: 1.25rem;
    }

    .role-card-admin      { background: #fff5f5; border-color: #fecaca; }
    .role-card-general    { background: #eff6ff; border-color: #bfdbfe; }
    .role-card-individual { background: #f0fdf4; border-color: #bbf7d0; }
    .role-card-analista   { background: #fffbeb; border-color: #fde68a; }

    /* Acceso global badge */
    .global-access {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        font-size: var(--text-xs);
        font-weight: 600;
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius-full);
        background: var(--clr-primary-100);
        color: var(--clr-primary-700);
    }
</style>
@endsection

@section('content')

{{-- Banner de bienvenida --}}
<div class="welcome-banner">
    <div class="welcome-greeting">👋 Bienvenido de regreso</div>
    <div class="welcome-name">{{ $user->name }}</div>
    <div class="welcome-meta">
        <div class="welcome-meta-item">
            <span aria-hidden="true">🗓️</span>
            <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
        </div>
        <div class="welcome-meta-item">
            <span aria-hidden="true">🕐</span>
            <span>{{ now()->format('H:i') }}</span>
        </div>
        @php
            $isGlobalRole = $user->isAdmin() || $user->isAnalista();
        @endphp
        @if($isGlobalRole)
            <div class="welcome-meta-item">
                <span aria-hidden="true">🌐</span>
                <strong>Acceso global — todos los fundos</strong>
            </div>
        @elseif($fundos->count())
            <div class="welcome-meta-item">
                <span aria-hidden="true">🏡</span>
                <strong>{{ $fundos->pluck('name')->join(', ') }}</strong>
            </div>
        @endif
    </div>
</div>

{{-- Tarjetas estadísticas --}}
<div class="dashboard-grid" role="region" aria-label="Resumen estadístico">
    <div class="stat-card">
        <div class="stat-icon green" aria-hidden="true">📝</div>
        <div>
            <div class="stat-value">{{ $ventas->count() }}</div>
            <div class="stat-label">Registros visibles</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow" aria-hidden="true">💰</div>
        <div>
            <div class="stat-value">S/ {{ number_format($ventas->sum('valor_venta'), 2) }}</div>
            <div class="stat-label">Total acumulado</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue" aria-hidden="true">⚖️</div>
        <div>
            <div class="stat-value">{{ number_format($ventas->sum('kilogramos'), 2) }} kg</div>
            <div class="stat-label">Kilogramos totales</div>
        </div>
    </div>
</div>

{{-- Grid de contenido principal --}}
<div class="content-grid">

    {{-- Columna principal: tabla de registros --}}
    <div>
        <div class="card" style="padding: 0; overflow: hidden;">
            <div class="card-header" style="padding: 1.25rem 1.5rem;">
                <div>
                    <div class="card-title">
                        <span aria-hidden="true">🛡️</span>
                        Registros recientes
                    </div>
                    <div class="card-subtitle">
                        Datos filtrados por la política de acceso (FundoScope)
                    </div>
                </div>
                <span style="font-size: var(--text-xs); font-weight: 600; background: var(--clr-info-bg); color: var(--clr-info); padding: 0.25rem 0.625rem; border-radius: var(--radius-full); border: 1px solid var(--clr-info-brd);">
                    {{ $ventas->count() }} registros
                </span>
            </div>

            @if($ventas->isEmpty())
                <div class="empty-state" role="status">
                    <div class="empty-icon" aria-hidden="true">📭</div>
                    <div class="empty-title">Sin registros visibles</div>
                    <p class="empty-desc">No existen ventas de descarte accesibles para su usuario bajo la política actual de fundo.</p>
                </div>
            @else
                <div class="table-wrapper" style="border: none; border-radius: 0;">
                    <table class="data-table dash-table" role="table" aria-label="Registros de venta de descarte">
                        <thead>
                            <tr>
                                <th scope="col">Fundo</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Motivo</th>
                                <th scope="col">Tipo</th>
                                <th scope="col" class="text-right">Precio (S/)</th>
                                <th scope="col" class="text-right">Kg</th>
                                <th scope="col" class="text-right">Total (S/)</th>
                                <th scope="col">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ventas as $v)
                            <tr>
                                <td>
                                    <span style="font-weight: 600; color: var(--txt-primary);">{{ $v->fundo->name ?? '—' }}</span>
                                    @if($v->fundo)
                                        <span style="display: block; font-size: var(--text-xs); color: var(--txt-muted);">{{ $v->fundo->code ?? '' }}</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap; color: var(--txt-muted);">
                                    {{ $v->fecha_produccion->format('d/m/Y') }}
                                </td>
                                <td>{{ $v->motivo }}</td>
                                <td>{{ $v->tipo_descarte }}</td>
                                <td class="text-right">{{ number_format($v->precio, 2) }}</td>
                                <td class="text-right" style="white-space: nowrap;">{{ number_format($v->kilogramos, 2) }}</td>
                                <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700);">
                                    {{ number_format($v->valor_venta, 2) }}
                                </td>
                                <td style="color: var(--txt-muted); font-size: var(--text-xs);">
                                    {{ $v->creator->name ?? 'Sistema' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Columna secundaria: info del usuario y fundos --}}
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">

        {{-- Tarjeta de rol --}}
        <div class="card role-card role-card-{{ $role?->name ?? 'default' }}">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span class="role-badge role-badge-{{ $role?->name ?? 'default' }}" aria-label="Rol asignado">
                    {{ $role?->display_name ?? 'Sin Rol' }}
                </span>
                @if($isGlobalRole)
                    <span class="global-access" aria-label="Acceso global a todos los fundos">🌐 Acceso global</span>
                @endif
            </div>
            <p style="font-size: var(--text-sm); color: var(--txt-secondary);">{{ $role?->description ?? 'No se ha asignado una descripción a este rol.' }}</p>
            <div style="margin-top: 0.75rem; font-size: var(--text-xs); color: var(--txt-muted); display: flex; gap: 1rem;">
                <span><strong style="color: var(--txt-secondary);">Correo:</strong> {{ $user->email }}</span>
            </div>
            <div style="margin-top: 0.25rem; font-size: var(--text-xs); color: var(--txt-muted);">
                <strong style="color: var(--txt-secondary);">Estado:</strong>
                @if($user->is_active)
                    <span class="text-success" aria-label="Cuenta activa">● Activo</span>
                @else
                    <span class="text-danger" aria-label="Cuenta inactiva">● Inactivo</span>
                @endif
            </div>
        </div>

        {{-- Tarjeta de fundos asignados --}}
        <div class="card">
            <div class="card-header" style="margin-bottom: 0.875rem;">
                <div class="card-title">
                    <span aria-hidden="true">🏡</span> Fundos Asignados
                </div>
            </div>
            @if($isGlobalRole)
                <div class="alert alert-success" style="margin-bottom: 0.5rem;" role="status">
                    <span aria-hidden="true">✅</span>
                    <div style="font-size: var(--text-xs);">Acceso transversal a todos los fundos del sistema.</div>
                </div>
            @endif
            @forelse($fundos as $fundo)
                <div class="fundo-item">
                    <div style="display: flex; align-items: center; gap: 0.625rem;">
                        <div class="fundo-dot" aria-hidden="true"></div>
                        <div>
                            <div style="font-size: var(--text-sm); font-weight: 600; color: var(--txt-primary);">{{ $fundo->name }}</div>
                            <div style="font-size: var(--text-xs); color: var(--txt-muted);">{{ $fundo->code }}</div>
                        </div>
                    </div>
                    <span style="font-size: var(--text-xs); color: var(--txt-muted);">ID {{ $fundo->id }}</span>
                </div>
            @empty
                <div class="empty-state" style="padding: 1.25rem 0;" role="status">
                    <div style="font-size: 1.5rem; margin-bottom: 0.375rem;" aria-hidden="true">🏡</div>
                    <div style="font-size: var(--text-xs); color: var(--txt-muted);">No hay fundos directamente asignados.</div>
                </div>
            @endforelse
        </div>

        {{-- Estado técnico (Fase 3 verificada) --}}
        <div class="card" style="background: var(--clr-primary-50); border-color: var(--clr-primary-200);">
            <div style="display: flex; align-items: center; gap: 0.375rem; margin-bottom: 0.375rem;">
                <span aria-hidden="true">✅</span>
                <span style="font-size: var(--text-xs); font-weight: 700; color: var(--clr-primary-700); text-transform: uppercase; letter-spacing: 0.06em;">Fase 3 Verificada</span>
            </div>
            <p style="font-size: var(--text-xs); color: var(--clr-primary-800); line-height: 1.6;">
                Login, RBAC (4 roles), <code style="background: rgba(22,101,52,0.1); padding: 0.125rem 0.25rem; border-radius: 3px;">FundoScope</code> multi-tenant y 15 pruebas PHPUnit exitosas (55 aserciones).
            </p>
        </div>
    </div>
</div>
@endsection
