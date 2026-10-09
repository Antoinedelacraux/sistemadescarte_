@extends('layouts.app')

@section('title', 'Panel de Control')
@section('page-title', 'Panel de Control')

@section('styles')
<style>
    /* ============================================================
     * PANEL DE CONTROL — SISTEMA FUNDO (DECLUTTERED & OPTIMIZADO)
     * Diseñado para máxima claridad y facilidad de uso operativo
     * ============================================================ */

    /* Banner principal de bienvenida y acceso rápido */
    .welcome-banner {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 4vw, 1.75rem);
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 16px rgba(5,46,22,0.18);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: 180px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(74,222,128,0.10);
        pointer-events: none;
    }

    .welcome-banner-info {
        flex: 1;
        min-width: 240px;
        position: relative;
        z-index: 1;
    }

    .welcome-greeting {
        font-size: var(--text-xs);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #86efac;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .welcome-name {
        font-size: clamp(1.25rem, 5vw, 1.75rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .welcome-meta {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex-wrap: wrap;
        font-size: var(--text-xs);
        color: rgba(255,255,255,0.75);
    }

    .welcome-meta-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .welcome-actions {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .btn-hero-primary {
        background: #22c55e;
        color: #052e16;
        font-weight: 700;
        padding: 0.625rem 1.125rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: var(--text-sm);
        transition: all var(--transition-fast);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .btn-hero-primary:hover {
        background: #4ade80;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }

    .btn-hero-secondary {
        background: rgba(255,255,255,0.12);
        color: white;
        border: 1px solid rgba(255,255,255,0.25);
        font-weight: 600;
        padding: 0.625rem 1rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: var(--text-sm);
        transition: all var(--transition-fast);
    }
    .btn-hero-secondary:hover {
        background: rgba(255,255,255,0.22);
    }

    @media (max-width: 640px) {
        .welcome-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .btn-hero-primary, .btn-hero-secondary {
            justify-content: center;
            padding: 0.5625rem 0.5rem;
            font-size: clamp(0.75rem, 2.8vw, 0.8125rem);
            text-align: center;
        }
    }

    /* Grid de métricas clave (KPIs) */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1.125rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.375rem;
        flex-shrink: 0;
    }
    .stat-icon.green  { background: #dcfce7; color: #166534; }
    .stat-icon.yellow { background: #fef3c7; color: #92400e; }
    .stat-icon.blue   { background: #dbeafe; color: #1e40af; }

    .stat-value {
        font-size: clamp(1.2rem, 4.5vw, 1.5rem);
        font-weight: 800;
        line-height: 1.1;
        color: var(--txt-primary);
        font-family: monospace;
    }

    .stat-label {
        font-size: var(--text-xs);
        color: var(--txt-muted);
        margin-top: 0.2rem;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        .stat-card {
            padding: 0.875rem 1rem;
        }
    }

    /* Secciones del panel y tarjetas de tabla pulidas */
    .dash-section {
        margin-bottom: 1.75rem;
        animation: fadeInTab 0.18s ease-in-out;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dash-table-card {
        background: #ffffff;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 1.5rem;
    }

    .dash-table-header {
        padding: clamp(1.125rem, 3.5vw, 1.375rem) clamp(1.25rem, 4vw, 1.625rem);
        border-bottom: 1px solid var(--brd-base);
        background: #ffffff;
    }

    /* Selector de Vista de Tablas (Tabs / Switcher) */
    .table-view-control-bar {
        background: #ffffff;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 0.625rem 0.875rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        box-shadow: var(--shadow-xs);
        flex-wrap: wrap;
    }

    .table-view-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: var(--text-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        white-space: nowrap;
    }

    .table-view-pills {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
        flex: 1;
    }
    .table-view-pills::-webkit-scrollbar { display: none; }

    .view-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4375rem 0.875rem;
        font-size: var(--text-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        border: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        color: var(--txt-secondary);
        cursor: pointer;
        transition: all var(--transition-fast);
        white-space: nowrap;
        font-family: inherit;
        line-height: 1.2;
    }
    .view-pill:hover {
        background: var(--clr-surface-100);
        color: var(--txt-primary);
        border-color: var(--brd-strong);
    }
    .view-pill.active {
        background: #14532d;
        color: #ffffff;
        border-color: #14532d;
        box-shadow: 0 2px 6px rgba(20, 83, 45, 0.25);
    }
    .view-pill.active svg {
        stroke: #ffffff;
    }
    .view-pill-count {
        font-size: 0.625rem;
        font-weight: 700;
        padding: 0.1rem 0.35rem;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }
    .view-pill:not(.active) .view-pill-count {
        background: var(--clr-surface-200);
        color: var(--txt-secondary);
    }

    .section-header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .section-title {
        font-size: clamp(1rem, 3.2vw, 1.22rem);
        font-weight: 700;
        color: var(--txt-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }

    .section-subtitle {
        font-size: clamp(0.72rem, 2.4vw, 0.8125rem);
        color: var(--txt-muted);
        margin-top: 0.25rem;
        line-height: 1.4;
    }

    /* Tablas operativas */
    .table-clean {
        min-width: 660px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-clean th {
        background: #f8fafc;
        padding: 0.625rem 0.875rem;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-secondary);
        border-bottom: 2px solid var(--brd-base);
        font-weight: 700;
    }

    .table-clean td {
        padding: 0.625rem 0.875rem;
        font-size: var(--text-sm);
        border-bottom: 1px solid var(--clr-surface-100);
        vertical-align: middle;
    }

    /* Pastillas de motivos */
    .motivo-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.5625rem;
        border-radius: var(--radius-full);
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .motivo-pill-amber { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .motivo-pill-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .motivo-pill-blue  { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

    /* Barra visual de porcentaje */
    .progress-bar-wrap {
        width: 75px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 0.375rem;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
    }
    .progress-fill-amber { background: #f59e0b; }
    .progress-fill-green { background: #16a34a; }
    .progress-fill-blue  { background: #2563eb; }

    /* Subtotales y Totales */
    .row-subtotal {
        background: #f8fafc;
        font-weight: 600;
        font-size: var(--text-xs);
    }
    .row-subtotal td {
        border-bottom: 2px solid var(--brd-base);
    }

    .row-grand-total {
        background: #0f2b1f;
        color: white !important;
        font-weight: 800;
    }
    .row-grand-total td {
        color: white !important;
        padding: 0.75rem 0.875rem;
        border: none;
        font-size: var(--text-sm);
    }
    .row-grand-total .text-highlight {
        color: #4ade80 !important;
    }

    /* Barra de filtros de clientes */
    .client-controls-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--brd-base);
    }

    .search-box-client {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 340px;
    }

    .search-box-client input {
        width: 100%;
        padding: 0.4375rem 0.75rem 0.4375rem 2.1rem;
        font-size: var(--text-xs);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        background: white;
        color: var(--txt-primary);
        font-family: inherit;
    }
    .search-box-client input:focus {
        outline: none;
        border-color: var(--clr-primary-500);
        box-shadow: 0 0 0 3px rgba(34,197,94,0.15);
    }

    .search-box-client .search-icon {
        position: absolute;
        left: 0.625rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.875rem;
        color: var(--txt-muted);
        pointer-events: none;
    }

    .filter-chips-wrap {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .chip-btn {
        padding: 0.28rem 0.625rem;
        font-size: 0.6875rem;
        font-weight: 600;
        border-radius: var(--radius-full);
        border: 1px solid var(--brd-base);
        background: white;
        color: var(--txt-secondary);
        cursor: pointer;
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .chip-btn:hover {
        background: var(--clr-primary-50);
        border-color: var(--clr-primary-300);
        color: var(--clr-primary-800);
    }
    .chip-btn.active {
        background: var(--clr-primary-700);
        border-color: var(--clr-primary-700);
        color: white;
    }

    .view-toggle-btns {
        display: inline-flex;
        border-radius: var(--radius-md);
        overflow: hidden;
        border: 1px solid var(--brd-base);
        background: white;
    }
    .view-toggle-btn {
        padding: 0.28rem 0.625rem;
        font-size: 0.6875rem;
        font-weight: 600;
        border: none;
        background: none;
        cursor: pointer;
        color: var(--txt-secondary);
        transition: background var(--transition-fast);
    }
    .view-toggle-btn.active {
        background: var(--clr-primary-100);
        color: var(--clr-primary-800);
    }

    /* Filas de cliente */
    .client-parent-row {
        cursor: pointer;
        transition: background var(--transition-fast);
    }
    .client-parent-row:hover {
        background: #f8fafc;
    }
    .client-parent-row.expanded {
        background: #f8fafc;
    }

    .client-toggle-caret {
        display: inline-block;
        width: 18px;
        height: 18px;
        text-align: center;
        line-height: 18px;
        border-radius: 4px;
        background: var(--clr-surface-200);
        color: var(--txt-secondary);
        font-size: 0.6875rem;
        margin-right: 0.5rem;
        transition: transform 0.2s ease;
    }
    .client-parent-row.expanded .client-toggle-caret {
        transform: rotate(90deg);
        background: var(--clr-primary-600);
        color: white;
    }

    .client-subrow {
        background: #fafafa;
        font-size: var(--text-xs);
    }
    .client-subrow td {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        border-bottom: 1px dashed var(--brd-base);
    }

    .client-subrow-bullet {
        margin-left: 1.625rem;
        color: var(--txt-muted);
        font-size: 0.6875rem;
    }
</style>
@endsection

@section('content')

{{-- 1. HERO BANNER DE BIENVENIDA Y ACCESO RÁPIDO --}}
<div class="welcome-banner">
    <div class="welcome-banner-info">
        <div class="welcome-greeting">🌾 Sistema Fundo &bull; Gestión Agrícola</div>
        <div class="welcome-name">Hola, {{ $user->name }}</div>
        <div class="welcome-meta">
            <div class="welcome-meta-item">
                <span aria-hidden="true">🗓️</span>
                <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
            @if(Auth::user()->isAdmin() || Auth::user()->isAnalista())
                <div class="welcome-meta-item" style="color: #bbf7d0;">
                    <span aria-hidden="true">🌐</span>
                    <span>Acceso a todos los fundos</span>
                </div>
            @elseif($fundos->count())
                <div class="welcome-meta-item" style="color: #bbf7d0;">
                    <span aria-hidden="true">🏡</span>
                    <span>{{ $fundos->pluck('name')->join(', ') }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Botones de acción directa para trabajadores --}}
    <div class="welcome-actions">
        @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
            <a href="{{ route('ventas.create') }}" class="btn-hero-primary" id="btn-quick-create">
                <span aria-hidden="true">➕</span>
                <span>Registrar Venta</span>
            </a>
        @endif
        <a href="{{ route('ventas.index') }}" class="btn-hero-secondary">
            <span aria-hidden="true">📋</span>
            <span>Ver Historial</span>
        </a>
    </div>
</div>

{{-- 2. RESUMEN DE NÚMEROS CLAVE (KPIS) --}}
<div class="dashboard-grid" role="region" aria-label="Resumen de producción">
    <div class="stat-card">
        <div class="stat-icon green" aria-hidden="true">⚖️</div>
        <div>
            <div class="stat-value">{{ number_format($totalGeneralKg, 2) }} kg</div>
            <div class="stat-label">Kilogramos Totales de Descarte</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow" aria-hidden="true">💰</div>
        <div>
            <div class="stat-value">S/ {{ number_format($totalGeneralVenta, 2) }}</div>
            <div class="stat-label">Valor Total en Ventas</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue" aria-hidden="true">📝</div>
        <div>
            <div class="stat-value">{{ $ventas->count() }}</div>
            <div class="stat-label">Envíos / Pesajes Registrados</div>
        </div>
    </div>
</div>

{{-- SELECTOR DE VISTA DE TABLAS (CONTROL INTERACTIVO) --}}
<div class="table-view-control-bar" id="table-view-controller">
    <div class="table-view-label">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        <span>Mostrar tabla:</span>
    </div>
    <div class="table-view-pills" role="tablist" aria-label="Seleccionar tablas a visualizar">
        <button type="button" class="view-pill active" data-view="all" onclick="cambiarVistaTabla('all', this)" role="tab" aria-selected="true">
            <span>Todas las Tablas</span>
        </button>
        <button type="button" class="view-pill" data-view="motivos" onclick="cambiarVistaTabla('motivos', this)" role="tab" aria-selected="false">
            <span>Por Tipo y Descarte</span>
        </button>
        <button type="button" class="view-pill" data-view="clientes" onclick="cambiarVistaTabla('clientes', this)" role="tab" aria-selected="false">
            <span>Por Cliente</span>
        </button>
        <button type="button" class="view-pill" data-view="recientes" onclick="cambiarVistaTabla('recientes', this)" role="tab" aria-selected="false">
            <span>Últimos Envíos</span>
            <span class="view-pill-count">{{ $ventas->take(8)->count() }}</span>
        </button>
    </div>
</div>

{{-- 3. TABLA 1: RESUMEN POR ORIGEN Y TIPO DE DESCARTE --}}
<section class="dash-section" id="sec-motivos-container" aria-labelledby="sec-title-motivos">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-motivos">
                        Resumen por Tipo de Venta y Descarte
                    </h2>
                    <div class="section-subtitle">
                        Totales acumulados en Venta Nacional (Racimos y Granos), Venta Campo y Venta Packing
                    </div>
                </div>
            </div>
        </div>

        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="table-clean" role="table" aria-label="Resumen por Tipo de Venta y Descarte">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 180px;">Origen / Tipo</th>
                        <th scope="col" class="text-center" style="width: 90px;">Envíos</th>
                        <th scope="col" class="text-right" style="width: 140px;">Kilogramos (kg)</th>
                        <th scope="col" style="width: 130px;">% Volumen</th>
                        <th scope="col" class="text-right" style="width: 130px;">Precio Prom.</th>
                        <th scope="col" class="text-right" style="width: 140px;">Total (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segregacionMotivos as $motivoKey => $mInfo)
                        @php $meta = $mInfo['meta']; @endphp

                        {{-- Fila agrupada de cabecera --}}
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="6" style="padding-top: 0.75rem; padding-bottom: 0.4rem;">
                                <span class="motivo-pill motivo-pill-{{ $meta['badge'] }}">
                                    <span>{{ $meta['icon'] }}</span>
                                    <span>{{ $meta['titulo'] }}</span>
                                </span>
                            </td>
                        </tr>

                        {{-- Filas de cada tipo permitido --}}
                        @foreach($mInfo['tipos'] as $tipoRow)
                        <tr>
                            <td style="padding-left: 2rem;">
                                <span style="color: var(--txt-primary); font-weight: 500;">
                                    &bull; {{ $tipoRow['tipo_descarte'] }}
                                </span>
                            </td>
                            <td class="text-center" style="color: var(--txt-muted);">
                                {{ $tipoRow['count'] }}
                            </td>
                            <td class="text-right" style="font-weight: 600; font-family: monospace;">
                                {{ number_format($tipoRow['kilogramos'], 2) }}
                            </td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill progress-fill-{{ $meta['badge'] }}" style="width: {{ min(100, $tipoRow['porcentaje_kg']) }}%;"></div>
                                </div>
                                <span style="font-size: 0.6875rem; color: var(--txt-secondary);">{{ $tipoRow['porcentaje_kg'] }}%</span>
                            </td>
                            <td class="text-right" style="color: var(--txt-secondary); font-family: monospace;">
                                S/ {{ number_format($tipoRow['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 600; color: var(--txt-primary); font-family: monospace;">
                                S/ {{ number_format($tipoRow['valor_venta'], 2) }}
                            </td>
                        </tr>
                        @endforeach

                        {{-- Subtotal --}}
                        <tr class="row-subtotal">
                            <td style="padding-left: 1.5rem;">
                                <strong>Subtotal {{ $meta['titulo'] }}</strong>
                            </td>
                            <td class="text-center"><strong>{{ $mInfo['count'] }}</strong></td>
                            <td class="text-right" style="font-family: monospace;">
                                <strong>{{ number_format($mInfo['total_kg'], 2) }}</strong>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--txt-secondary);">{{ $mInfo['porcentaje_kg'] }}%</span>
                            </td>
                            <td class="text-right" style="font-family: monospace;">
                                S/ {{ number_format($mInfo['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-family: monospace; color: var(--clr-primary-700);">
                                <strong>S/ {{ number_format($mInfo['total_venta'], 2) }}</strong>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Total General --}}
                    <tr class="row-grand-total">
                        <td>TOTAL GENERAL</td>
                        <td class="text-center">{{ $ventas->count() }}</td>
                        <td class="text-right" style="font-family: monospace;">{{ number_format($totalGeneralKg, 2) }} kg</td>
                        <td>100.0%</td>
                        <td class="text-right" style="font-family: monospace;">
                            S/ {{ $totalGeneralKg > 0 ? number_format($totalGeneralVenta / $totalGeneralKg, 2) : '0.00' }}
                        </td>
                        <td class="text-right text-highlight" style="font-family: monospace;">
                            S/ {{ number_format($totalGeneralVenta, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- 4. TABLA 2: RESUMEN POR CLIENTE --}}
<section class="dash-section" id="sec-clientes-container" aria-labelledby="sec-title-clientes">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-clientes">
                        Resumen de Ventas por Cliente
                    </h2>
                    <div class="section-subtitle">
                        Detalle de compras por cliente con desglose de Cosecha Nacional, Campo y Packing
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="expandAllClients(true)" title="Ver detalle de todos los clientes">
                        Abrir Todos
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="expandAllClients(false)" title="Ocultar detalles">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

        {{-- Filtros y buscador --}}
        <div class="client-controls-bar">
            <div class="search-box-client">
                <span class="search-icon" aria-hidden="true">🔍</span>
                <input
                    type="search"
                    id="client-search-input"
                    placeholder="Buscar cliente o tipo..."
                    oninput="filtrarTablaClientes()"
                    aria-label="Buscar cliente"
                >
            </div>

            <div class="filter-chips-wrap" role="group" aria-label="Filtro de motivo">
                <button type="button" class="chip-btn active" data-filter="todos" onclick="setMotivoFilter('todos', this)">Todos</button>
                <button type="button" class="chip-btn" data-filter="Cosecha Nacional" onclick="setMotivoFilter('Cosecha Nacional', this)">🌾 Nacional</button>
                <button type="button" class="chip-btn" data-filter="Campo" onclick="setMotivoFilter('Campo', this)">🌿 Campo</button>
                <button type="button" class="chip-btn" data-filter="Packing" onclick="setMotivoFilter('Packing', this)">📦 Packing</button>
            </div>

            <div class="view-toggle-btns" role="radiogroup" aria-label="Modo de vista">
                <button type="button" id="btn-view-grouped" class="view-toggle-btn active" onclick="switchClientView('grouped')">
                    📁 Por Cliente
                </button>
                <button type="button" id="btn-view-flat" class="view-toggle-btn" onclick="switchClientView('flat')">
                    📋 Lista Plana
                </button>
            </div>
        </div>

        <div class="table-wrapper" style="border: none; border-radius: 0;">
            {{-- MODO A: AGRUPADO POR CLIENTE --}}
            <table class="table-clean" id="table-clients-grouped" role="table" aria-label="Ventas por Cliente">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 220px;">Cliente</th>
                        <th scope="col" style="width: 160px;">Origen</th>
                        <th scope="col" style="width: 170px;">Tipo de Descarte</th>
                        <th scope="col" class="text-right" style="width: 130px;">Kilos (kg)</th>
                        <th scope="col" class="text-right" style="width: 130px;">Precio Prom.</th>
                        <th scope="col" class="text-right" style="width: 140px;">Total (S/)</th>
                    </tr>
                </thead>
                <tbody id="clients-grouped-tbody">
                    @forelse($clientesAgrupados as $clienteNombre => $cData)
                        {{-- Fila de Cliente --}}
                        <tr class="client-parent-row expanded"
                            id="row-client-{{ Str::slug($clienteNombre) }}"
                            data-client-name="{{ strtolower($clienteNombre) }}"
                            data-motivos="{{ implode(' ', $cData['motivos']) }}"
                            onclick="toggleClientRows('{{ Str::slug($clienteNombre) }}')">
                            <td>
                                <span class="client-toggle-caret" id="caret-{{ Str::slug($clienteNombre) }}" aria-hidden="true">&#9658;</span>
                                <strong style="color: var(--txt-primary); font-size: 0.875rem;">{{ $clienteNombre }}</strong>
                                @if(!empty($cData['ruc']) && !str_contains($clienteNombre, $cData['ruc']))
                                    <span style="display: block; font-size: 0.6875rem; color: var(--txt-muted); margin-left: 1.625rem;">
                                        RUC: {{ $cData['ruc'] }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @foreach($cData['motivos'] as $mName)
                                    @php $bClass = $mName === 'Cosecha Nacional' ? 'amber' : ($mName === 'Packing' ? 'blue' : 'green'); @endphp
                                    <span class="motivo-pill motivo-pill-{{ $bClass }}" style="font-size: 0.625rem; padding: 0.125rem 0.375rem;">
                                        {{ $mName }}
                                    </span>
                                @endforeach
                            </td>
                            <td style="color: var(--txt-muted); font-size: var(--text-xs);">
                                {{ count($cData['desglose']) }} variante(s)
                            </td>
                            <td class="text-right" style="font-weight: 700; font-family: monospace;">
                                {{ number_format($cData['total_kg'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 600; color: var(--txt-secondary); font-family: monospace;">
                                S/ {{ number_format($cData['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">
                                S/ {{ number_format($cData['total_venta'], 2) }}
                            </td>
                        </tr>

                        {{-- Desglose por tipo --}}
                        @foreach($cData['desglose'] as $dKey => $dData)
                            @php $bClass = $dData['motivo'] === 'Cosecha Nacional' ? 'amber' : ($dData['motivo'] === 'Packing' ? 'blue' : 'green'); @endphp
                            <tr class="client-subrow subrow-{{ Str::slug($clienteNombre) }}"
                                data-client-name="{{ strtolower($clienteNombre) }}"
                                data-motivo="{{ $dData['motivo'] }}"
                                data-tipo="{{ strtolower($dData['tipo_descarte']) }}">
                                <td>
                                    <span class="client-subrow-bullet" aria-hidden="true">&#8627;</span>
                                    <span style="color: var(--txt-secondary);">Detalle</span>
                                </td>
                                <td>
                                    <span class="motivo-pill motivo-pill-{{ $bClass }}">
                                        {{ $dData['motivo'] }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--txt-primary);">
                                        {{ $dData['tipo_descarte'] }}
                                    </span>
                                    <span style="font-size: 0.6875rem; color: var(--txt-muted);">({{ $dData['count'] }})</span>
                                </td>
                                <td class="text-right" style="font-family: monospace;">
                                    {{ number_format($dData['kilogramos'], 2) }}
                                </td>
                                <td class="text-right" style="font-family: monospace; color: var(--txt-secondary);">
                                    S/ {{ number_format($dData['precio_promedio'], 2) }}
                                </td>
                                <td class="text-right" style="font-family: monospace; font-weight: 600; color: var(--txt-primary);">
                                    S/ {{ number_format($dData['valor_venta'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--txt-muted);">
                                No hay registros de ventas para clasificar por cliente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- MODO B: MATRIZ PLANA --}}
            <table class="table-clean" id="table-clients-flat" style="display: none;" role="table" aria-label="Lista Plana de Clientes">
                <thead>
                    <tr>
                        <th scope="col">Cliente</th>
                        <th scope="col">RUC</th>
                        <th scope="col">Origen</th>
                        <th scope="col">Tipo de Descarte</th>
                        <th scope="col" class="text-right">Kilos (kg)</th>
                        <th scope="col" class="text-right">Precio Prom.</th>
                        <th scope="col" class="text-right">Total (S/)</th>
                    </tr>
                </thead>
                <tbody id="clients-flat-tbody">
                    @forelse($filasClientesPlanas as $fila)
                        @php $bClass = $fila['motivo'] === 'Cosecha Nacional' ? 'amber' : ($fila['motivo'] === 'Packing' ? 'blue' : 'green'); @endphp
                        <tr class="flat-client-row"
                            data-client-name="{{ strtolower($fila['cliente']) }}"
                            data-motivo="{{ $fila['motivo'] }}"
                            data-tipo="{{ strtolower($fila['tipo_descarte']) }}">
                            <td><strong style="color: var(--txt-primary);">{{ $fila['cliente'] }}</strong></td>
                            <td style="color: var(--txt-muted); font-size: var(--text-xs);">{{ $fila['ruc'] ?? '—' }}</td>
                            <td><span class="motivo-pill motivo-pill-{{ $bClass }}">{{ $fila['motivo'] }}</span></td>
                            <td><span style="font-weight: 600;">{{ $fila['tipo_descarte'] }}</span></td>
                            <td class="text-right" style="font-weight: 600; font-family: monospace;">{{ number_format($fila['kilogramos'], 2) }}</td>
                            <td class="text-right" style="color: var(--txt-secondary); font-family: monospace;">S/ {{ number_format($fila['precio_promedio'], 2) }}</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">S/ {{ number_format($fila['valor_venta'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--txt-muted);">
                                No hay registros disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- 5. ÚLTIMOS REGISTROS REALIZADOS --}}
<section class="dash-section" id="sec-recientes-container" aria-labelledby="sec-title-recientes">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-recientes">
                        Últimos Envíos Registrados
                    </h2>
                    <div class="section-subtitle">
                        Movimientos recientes en el fundo
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-sm">
                        Ver Historial Completo &rarr;
                    </a>
                </div>
            </div>
        </div>

        @if($ventas->isEmpty())
            <div class="empty-state" role="status">
                <div class="empty-icon" aria-hidden="true">📭</div>
                <div class="empty-title">Sin registros visibles</div>
                <p class="empty-desc">No existen ventas de descarte registradas aún.</p>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table class="table-clean" role="table" aria-label="Envíos recientes">
                    <thead>
                        <tr>
                            <th scope="col">Fundo</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Origen</th>
                            <th scope="col">Tipo de Descarte</th>
                            <th scope="col" class="text-right">Precio/Kg</th>
                            <th scope="col" class="text-right">Kilos</th>
                            <th scope="col" class="text-right">Total (S/)</th>
                            <th scope="col">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas->take(8) as $v)
                        <tr>
                            <td>
                                <strong style="color: var(--txt-primary);">{{ $v->fundo->name ?? '—' }}</strong>
                            </td>
                            <td style="white-space: nowrap; color: var(--txt-secondary); font-size: var(--text-xs);">
                                {{ $v->fecha_produccion->format('d/m/Y') }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--txt-primary);">
                                    {{ $v->cliente ?: ($v->ruc ? 'RUC: '.$v->ruc : 'Venta General') }}
                                </span>
                            </td>
                            <td>
                                @php $bClass = $v->motivo === 'Cosecha Nacional' ? 'amber' : ($v->motivo === 'Packing' ? 'blue' : 'green'); @endphp
                                <span class="motivo-pill motivo-pill-{{ $bClass }}">
                                    {{ $v->motivo }}
                                </span>
                            </td>
                            <td>{{ $v->tipo_descarte }}</td>
                            <td class="text-right" style="font-family: monospace;">S/ {{ number_format($v->precio, 2) }}</td>
                            <td class="text-right" style="white-space: nowrap; font-family: monospace; font-weight: 600;">{{ number_format($v->kilogramos, 2) }} kg</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">
                                S/ {{ number_format($v->valor_venta, 2) }}
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
</section>

{{-- INTERACTIVIDAD JAVASCRIPT LIGERA Y RÁPIDA --}}
<script>
var filtroMotivoActual = 'todos';

function toggleClientRows(slug) {
    var parentRow = document.getElementById('row-client-' + slug);
    var subrows = document.querySelectorAll('.subrow-' + slug);
    if (!parentRow) return;

    var isExpanded = parentRow.classList.contains('expanded');
    if (isExpanded) {
        parentRow.classList.remove('expanded');
        subrows.forEach(function(row) { row.style.display = 'none'; });
    } else {
        parentRow.classList.add('expanded');
        subrows.forEach(function(row) {
            var rowMotivo = row.getAttribute('data-motivo');
            if (filtroMotivoActual === 'todos' || filtroMotivoActual === rowMotivo) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

function expandAllClients(expand) {
    var parentRows = document.querySelectorAll('.client-parent-row');
    parentRows.forEach(function(pRow) {
        var id = pRow.id.replace('row-client-', '');
        var subrows = document.querySelectorAll('.subrow-' + id);
        if (expand) {
            pRow.classList.add('expanded');
            subrows.forEach(function(row) {
                var rowMotivo = row.getAttribute('data-motivo');
                if (filtroMotivoActual === 'todos' || filtroMotivoActual === rowMotivo) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        } else {
            pRow.classList.remove('expanded');
            subrows.forEach(function(row) { row.style.display = 'none'; });
        }
    });
}

function switchClientView(view) {
    var tableGrouped = document.getElementById('table-clients-grouped');
    var tableFlat = document.getElementById('table-clients-flat');
    var btnGrouped = document.getElementById('btn-view-grouped');
    var btnFlat = document.getElementById('btn-view-flat');

    if (view === 'grouped') {
        tableGrouped.style.display = '';
        tableFlat.style.display = 'none';
        btnGrouped.classList.add('active');
        btnFlat.classList.remove('active');
    } else {
        tableGrouped.style.display = 'none';
        tableFlat.style.display = '';
        btnGrouped.classList.remove('active');
        btnFlat.classList.add('active');
    }
    filtrarTablaClientes();
}

function setMotivoFilter(motivo, btn) {
    filtroMotivoActual = motivo;
    document.querySelectorAll('.filter-chips-wrap .chip-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');
    filtrarTablaClientes();
}

function filtrarTablaClientes() {
    var q = (document.getElementById('client-search-input').value || '').trim().toLowerCase();
    var isGrouped = document.getElementById('table-clients-grouped').style.display !== 'none';

    if (isGrouped) {
        var parentRows = document.querySelectorAll('.client-parent-row');
        parentRows.forEach(function(pRow) {
            var clientName = pRow.getAttribute('data-client-name') || '';
            var slug = pRow.id.replace('row-client-', '');
            var subrows = document.querySelectorAll('.subrow-' + slug);
            var isExpanded = pRow.classList.contains('expanded');

            var clientMatchesQuery = q === '' || clientName.indexOf(q) !== -1;
            var anySubrowMatches = false;

            subrows.forEach(function(sRow) {
                var sMotivo = sRow.getAttribute('data-motivo') || '';
                var sTipo = (sRow.getAttribute('data-tipo') || '').toLowerCase();

                var matchesMotivo = filtroMotivoActual === 'todos' || filtroMotivoActual === sMotivo;
                var matchesQuery = q === '' || clientName.indexOf(q) !== -1 || sTipo.indexOf(q) !== -1 || sMotivo.toLowerCase().indexOf(q) !== -1;

                if (matchesMotivo && matchesQuery) {
                    anySubrowMatches = true;
                    if (isExpanded) {
                        sRow.style.display = '';
                    } else {
                        sRow.style.display = 'none';
                    }
                } else {
                    sRow.style.display = 'none';
                }
            });

            if (anySubrowMatches || (clientMatchesQuery && (filtroMotivoActual === 'todos' || (pRow.getAttribute('data-motivos') || '').indexOf(filtroMotivoActual) !== -1))) {
                pRow.style.display = '';
            } else {
                pRow.style.display = 'none';
            }
        });
    } else {
        var flatRows = document.querySelectorAll('.flat-client-row');
        flatRows.forEach(function(row) {
            var clientName = row.getAttribute('data-client-name') || '';
            var motivo = row.getAttribute('data-motivo') || '';
            var tipo = row.getAttribute('data-tipo') || '';

            var matchesMotivo = filtroMotivoActual === 'todos' || filtroMotivoActual === motivo;
            var matchesQuery = q === '' || clientName.indexOf(q) !== -1 || tipo.indexOf(q) !== -1 || motivo.toLowerCase().indexOf(q) !== -1;

            if (matchesMotivo && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

function cambiarVistaTabla(vista, btn) {
    document.querySelectorAll('.view-pill').forEach(function(b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
    });
    if (btn) {
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
    }

    var secMotivos = document.getElementById('sec-motivos-container');
    var secClientes = document.getElementById('sec-clientes-container');
    var secRecientes = document.getElementById('sec-recientes-container');

    if (vista === 'all') {
        if (secMotivos) secMotivos.style.display = '';
        if (secClientes) secClientes.style.display = '';
        if (secRecientes) secRecientes.style.display = '';
    } else if (vista === 'motivos') {
        if (secMotivos) secMotivos.style.display = '';
        if (secClientes) secClientes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = 'none';
    } else if (vista === 'clientes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = '';
        if (secRecientes) secRecientes.style.display = 'none';
    } else if (vista === 'recientes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = '';
    }

    try {
        localStorage.setItem('fundo_dashboard_tab_view', vista);
    } catch(e) {}
}

// Restaurar preferencia previa de visualización
(function() {
    try {
        var guardada = localStorage.getItem('fundo_dashboard_tab_view') || 'all';
        var targetBtn = document.querySelector('.view-pill[data-view="' + guardada + '"]');
        if (targetBtn) {
            cambiarVistaTabla(guardada, targetBtn);
        }
    } catch(e) {}
})();
</script>
@endsection
