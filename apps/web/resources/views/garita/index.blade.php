@extends('layouts.app')

@section('title', 'Control de Garita — Salidas de Camiones')
@section('page-title', 'Control de Garita')

@section('styles')
<style>
    .garita-header {
        margin-bottom: 1.5rem;
    }
    .garita-header h1 {
        font-size: clamp(1.25rem, 2.5vw, 1.625rem);
        font-weight: 800;
        color: var(--txt-primary);
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }
    .garita-header p {
        font-size: var(--text-sm);
        color: var(--txt-muted);
    }

    /* Tarjetas KPI Superiores */
    .garita-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .garita-kpi-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1.125rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .garita-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
    }
    .garita-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .icon-amber {
        background: #fef3c7;
        color: #b45309;
    }
    .icon-green {
        background: #dcfce7;
        color: #15803d;
    }
    .icon-blue {
        background: #e0f2fe;
        color: #0369a1;
    }
    .garita-kpi-info {
        display: flex;
        flex-direction: column;
    }
    .garita-kpi-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--txt-primary);
        line-height: 1.1;
    }
    .garita-kpi-lbl {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--txt-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-top: 0.25rem;
    }

    /* Barra de Filtros */
    .garita-filters-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }
    .garita-simple-nav { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0.9rem 1.1rem; margin:1.5rem 0; background:#fff; border:1px solid var(--brd-base); border-radius:var(--radius-lg); }
    .garita-simple-nav a { color:#15803d; font-weight:700; text-decoration:none; }
    .garita-tabs { display:flex; flex-wrap:wrap; gap:.4rem; }
    .garita-tabs a { padding:.5rem .8rem; border-radius:8px; color:#475569; }
    .garita-tabs a[aria-current="page"] { color:#166534; background:#dcfce7; }
    .viaje-title { font-size:1rem; font-weight:800; color:#173627; }
    .viaje-date { font-size:.78rem; color:#64748b; }
    .viaje-meta-item.is-carga { padding:.65rem .8rem; background:#f0fdf4; border:1px solid #dcfce7; border-radius:9px; }
    .viaje-meta-item.is-carga .viaje-meta-val { color:#166534; font-size:1.1rem; }
    .viaje-validado-fecha { color:#166534; font-weight:700; }
    .garita-current-view { color:var(--txt-primary); font-weight:700; }
    .garita-filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
        gap: 0.875rem;
        align-items: flex-end;
    }
    .form-group-filter {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }
    .form-group-filter label {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--txt-secondary);
    }
    .form-group-filter input,
    .form-group-filter select {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        background: var(--clr-surface-50);
        color: var(--txt-primary);
        outline: none;
    }
    .form-group-filter input:focus,
    .form-group-filter select:focus {
        border-color: var(--clr-primary-500);
        background: #ffffff;
    }
    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Tarjetas de Viaje de Camiones */
    .viajes-container {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .viaje-card {
        background: var(--clr-surface-0);
        border-radius: var(--radius-xl);
        border: 1.5px solid var(--brd-base);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        transition: border-color 0.15s ease;
    }

    /* Coloración por Estado */
    .viaje-card.is-pendiente {
        border-color: #f59e0b;
        border-left: 6px solid #d97706;
    }
    .viaje-card.is-validado {
        border-color: #10b981;
        border-left: 6px solid #059669;
    }
    .viaje-card.is-borrador {
        border-color: #cbd5e1;
        border-left: 6px solid #64748b;
    }

    .viaje-header {
        padding: 1rem 1.25rem;
        background: var(--clr-surface-50);
        border-bottom: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .viaje-title-group {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .viaje-placa-badge {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
        font-weight: 800;
        font-size: 1.05rem;
        padding: 0.35rem 0.65rem;
        background: #1e293b;
        color: #ffffff;
        border-radius: var(--radius-md);
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .badge-fundo-tag {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        background: #f1f5f9;
        color: #334155;
    }

    .badge-viaje-tag {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.25rem 0.5rem;
        border-radius: var(--radius-sm);
        background: #e0f2fe;
        color: #0369a1;
    }

    /* Badges de Estado */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .badge-status-pendiente {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-status-validado {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .badge-status-borrador {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .viaje-body {
        padding: 1.25rem;
    }

    /* Grid de Metadatos del Camión */
    .viaje-meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px dashed var(--brd-base);
    }
    .viaje-meta-item {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .viaje-meta-lbl {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--txt-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .viaje-meta-val {
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--txt-primary);
    }

    /* Detalle de Carga / Lotes */
    .viaje-lotes-desglose {
        background: var(--clr-surface-50);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .desglose-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }
    .desglose-table th {
        background: #f8fafc;
        padding: 0.5rem 0.75rem;
        text-align: left;
        font-size: 0.6875rem;
        font-weight: 700;
        color: var(--txt-muted);
        text-transform: uppercase;
        border-bottom: 1px solid var(--brd-base);
    }
    .desglose-table td {
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .desglose-table tr:last-child td {
        border-bottom: none;
    }

    /* Totales y Barra de Acción */
    .viaje-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding-top: 0.75rem;
    }
    .viaje-totals {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .viaje-total-item {
        display: flex;
        flex-direction: column;
    }
    .viaje-total-item .val {
        font-size: 1.125rem;
        font-weight: 800;
        color: #166534;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
    }
    .viaje-total-item .lbl {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--txt-muted);
        text-transform: uppercase;
    }

    .btn-confirmar-salida {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        background: #166534;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.875rem;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .btn-confirmar-salida:hover {
        background: #15803d;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(22, 101, 52, 0.25);
    }

    .salida-sellada-box {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.875rem;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: var(--radius-md);
        color: #166534;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    /* Modal de Confirmación */
    .modal-garita {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(3px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 1rem;
    }
    .modal-garita.is-open {
        display: flex;
    }
    .modal-garita-sheet {
        background: #ffffff;
        width: 100%;
        max-width: 500px;
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    }
    .modal-garita-header {
        padding: 1rem 1.25rem;
        background: #166534;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-garita-body {
        padding: 1.25rem;
    }
    .modal-garita-footer {
        padding: 0.875rem 1.25rem;
        background: #f8fafc;
        border-top: 1px solid var(--brd-base);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    /* Ajustes de jerarquía y comportamiento táctil del panel. */
    .viaje-card.is-pendiente { border-color:var(--brd-base); border-left-width:5px; }
    .viaje-title { font-size:1.05rem; }
    .viaje-title-group { flex-direction:column; align-items:flex-start; gap:.25rem; }
    .viaje-code { font-size:.72rem; color:#64748b; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; }
    .viaje-mobile-summary { display:none; }
    .viaje-identity { display:flex; align-items:center; flex-wrap:wrap; gap:.55rem; }
    .viajes-container { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:1rem; align-items:start; }
    .viaje-card { border-radius:14px; }
    .viaje-header { align-items:flex-start; padding:.85rem 1rem; }
    .viaje-body { padding:1rem; }
    .viaje-meta-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.75rem; padding-bottom:.8rem; margin-bottom:.8rem; }
    .viaje-meta-item.is-carga { padding:.55rem .65rem; }
    .viaje-meta-item.is-carga .viaje-meta-val { font-size:1rem; }
    .viaje-footer { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.6rem; padding-top:0; }
    .btn-confirmar-salida, .btn-ver-informacion { min-height:2.65rem; justify-content:center; padding:.55rem .7rem; font-size:.8rem; }
    .btn-ver-informacion { display:inline-flex; align-items:center; border:1px solid #86a394; border-radius:var(--radius-md); background:#fff; color:#155e3a; cursor:pointer; font-weight:700; }
    .btn-ver-informacion:hover { background:#f0fdf4; }
    .viaje-footer .viaje-validado-fecha { grid-column:1 / -1; }
    .modal-garita-info-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.9rem; }
    .modal-garita-info-grid div { min-width:0; }
    .modal-garita-info-grid dt { color:#64748b; font-size:.7rem; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
    .modal-garita-info-grid dd { margin:.2rem 0 0; color:#102319; font-size:.95rem; font-weight:700; overflow-wrap:anywhere; }
    @media (max-width: 1180px) { .viajes-container { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) {
        .garita-header { display:none; }
        .garita-kpis { grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.55rem; margin-bottom:.65rem; }
        .garita-kpi-card { min-width:0; min-height:3.3rem; padding:.55rem .7rem; gap:.5rem; justify-content:center; }
        .garita-kpi-card:hover { transform:none; }
        .garita-kpi-icon { width:32px; height:32px; }
        .garita-kpi-icon svg { width:18px; height:18px; }
        .garita-kpi-val { font-size:1.25rem; }
        .garita-kpi-lbl { display:block; font-size:.6rem; letter-spacing:.025em; line-height:1.2; }
        .garita-simple-nav { align-items:stretch; flex-direction:column; gap:.45rem; margin:.65rem 0; padding:.55rem; }
        .garita-tabs { display:grid; grid-template-columns:1fr 1fr; gap:.35rem; }
        .garita-tabs a { text-align:center; padding:.55rem .3rem; font-size:.78rem; }
        .viajes-container { grid-template-columns:1fr; gap:.7rem; }
        .viaje-card { border-left-width:4px !important; }
        .viaje-header { align-items:center; padding:.75rem; gap:.5rem; }
        .viaje-title-group { min-width:0; flex:1; }
        .viaje-identity { flex-wrap:nowrap; gap:.45rem; width:100%; }
        .viaje-title { font-size:.95rem; white-space:nowrap; }
        .viaje-placa-badge { padding:.28rem .48rem; font-size:.88rem; white-space:nowrap; }
        .viaje-identity .badge-fundo-tag, .viaje-date, .viaje-title-group > .viaje-code { display:none; }
        .badge-status { width:2rem; height:2rem; padding:0; flex:0 0 2rem; justify-content:center; border-radius:50%; }
        .badge-status-text { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0, 0, 0, 0); white-space:nowrap; }
        .viaje-mobile-summary { display:grid; grid-template-columns:1fr; gap:.22rem; width:100%; margin-top:.3rem; color:#64748b; font-size:.72rem; }
        .viaje-mobile-summary strong { color:#334155; font-weight:700; }
        .viaje-mobile-summary .viaje-code { overflow-wrap:anywhere; }
        .viaje-body { padding:.65rem .75rem .75rem; }
        .viaje-meta-grid { display:none; }
        .viaje-footer { display:flex; justify-content:flex-end; align-items:center; gap:.5rem; }
        .btn-confirmar-salida, .btn-ver-informacion { width:2.8rem; height:2.8rem; min-height:2.8rem; flex:0 0 2.8rem; justify-content:center; padding:0; }
        .btn-confirmar-salida svg, .btn-ver-informacion svg { width:18px; height:18px; }
        .viaje-validado-fecha { margin-right:auto; font-size:.75rem; }
        .btn-action-label { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0, 0, 0, 0); white-space:nowrap; border:0; }
        .modal-garita { align-items:flex-end; padding:0; }
        .modal-garita-sheet { max-height:92vh; border-radius:var(--radius-xl) var(--radius-xl) 0 0; overflow:auto; }
        .modal-garita-footer { position:sticky; bottom:0; justify-content:stretch; padding:.75rem 1rem; }
        .modal-garita-footer .btn { flex:1; min-height:2.75rem; }
        .modal-garita-info-grid { grid-template-columns:1fr; gap:.7rem; }
    }
</style>
@endsection

@section('content')
<div class="garita-wrapper">
    {{-- Encabezado --}}
    <div class="garita-header">
        <h1>Panel de Salidas de Camiones — Garita</h1>
        <p>Confirmación, control físico y registro de salida de camiones cargados con descarte</p>
    </div>

    {{-- KPIs Resumen Hoy --}}
    <div class="garita-kpis">
        <div class="garita-kpi-card">
            <div class="garita-kpi-icon icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="garita-kpi-info">
                <span class="garita-kpi-val">{{ $kpiPendientesHoy }}</span>
                <span class="garita-kpi-lbl">Camiones Pendientes Hoy</span>
            </div>
        </div>

        <div class="garita-kpi-card">
            <div class="garita-kpi-icon icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div class="garita-kpi-info">
                <span class="garita-kpi-val">{{ $kpiValidadosHoy }}</span>
                <span class="garita-kpi-lbl">Camiones Validados Hoy</span>
            </div>
        </div>

    </div>

    <div class="garita-simple-nav">
        <nav class="garita-tabs" aria-label="Estado de los envíos">
            <a href="{{ route('garita.index', array_merge(request()->except(['estado', 'viaje']), ['estado' => 'pendiente'])) }}" aria-current="{{ $estadoPanel === 'pendiente' ? 'page' : 'false' }}">Pendientes <span aria-label="{{ $kpiPendientesHoy }} pendientes hoy">({{ $kpiPendientesHoy }})</span></a>
            <a href="{{ route('garita.index', array_merge(request()->except(['estado', 'viaje']), ['estado' => 'validado'])) }}" aria-current="{{ $estadoPanel === 'validado' ? 'page' : 'false' }}">Validados <span aria-label="{{ $kpiValidadosHoy }} validados hoy">({{ $kpiValidadosHoy }})</span></a>
        </nav>
    </div>

    @include('garita._filtros', ['rutaFiltros' => 'garita.index'])

    {{-- Listado de envíos pendientes --}}
    <div class="viajes-container">
        @forelse($viajesAgrupados as $viaje)
            <article class="viaje-card is-{{ $viaje['estado_consolidado'] }}">
                <header class="viaje-header">
                    <div class="viaje-title-group">
                        <div class="viaje-identity">
                            <span class="viaje-title">Viaje N° {{ $viaje['viaje'] }}</span>
                            <span class="viaje-placa-badge">{{ $viaje['placa'] }}</span>
                            @if($fundos->count() > 1)<span class="badge-fundo-tag">{{ $viaje['fundo_nombre'] }}</span>@endif
                        </div>
                        <span class="viaje-date">Producción: {{ $viaje['fecha_produccion']?->format('d/m/Y') ?? '—' }} · Registro: {{ $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '—' }}</span>
                        @if($viaje['envio_codigo'])<span class="viaje-code">{{ $viaje['envio_codigo'] }}</span>@endif
                        <span class="viaje-mobile-summary">
                            <span><strong>Fundo:</strong> {{ $viaje['fundo_nombre'] }}</span>
                            <span><strong>Producción:</strong> {{ $viaje['fecha_produccion']?->format('d/m/Y') ?? '—' }} · <strong>Registro:</strong> {{ $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '—' }}</span>
                            <span class="viaje-code">{{ $viaje['envio_codigo'] ?? 'Histórico' }}</span>
                        </span>
                    </div>
                    <span class="badge-status {{ $estadoPanel === 'validado' ? 'badge-status-validado' : 'badge-status-pendiente' }}" title="{{ $estadoPanel === 'validado' ? 'Validado' : 'Pendiente de validación' }}">
                        <x-ui-icon :name="$estadoPanel === 'validado' ? 'check' : 'clock'" size="16" />
                        <span class="badge-status-text">{{ $estadoPanel === 'validado' ? 'Validado' : 'Pendiente de validación' }}</span>
                    </span>
                </header>
                <div class="viaje-body">
                    <div class="viaje-meta-grid">
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Cliente</span><span class="viaje-meta-val">{{ $viaje['cliente'] }}</span></div>
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Conductor</span><span class="viaje-meta-val">{{ $viaje['conductor'] }}</span></div>
                        <div class="viaje-meta-item is-carga"><span class="viaje-meta-lbl">Jabas</span><span class="viaje-meta-val">{{ number_format($viaje['total_jabas']) }}</span></div>
                        <div class="viaje-meta-item is-carga"><span class="viaje-meta-lbl">Kilogramos</span><span class="viaje-meta-val">{{ number_format($viaje['total_kilos'], 2) }} kg</span></div>
                    </div>
                    <div class="viaje-footer">
                        <button type="button" class="btn-ver-informacion"
                            title="Ver información del viaje"
                            aria-label="Ver información del Viaje N° {{ $viaje['viaje'] }}"
                            data-envio="{{ $viaje['envio_codigo'] ?? 'Histórico' }}"
                            data-viaje="{{ $viaje['viaje'] }}"
                            data-fecha="{{ $viaje['fecha_produccion']?->format('d/m/Y') ?? '—' }}"
                            data-registro="{{ $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '—' }}"
                            data-cliente="{{ $viaje['cliente'] }}"
                            data-conductor="{{ $viaje['conductor'] }}"
                            data-placa="{{ $viaje['placa'] }}"
                            data-fundo="{{ $viaje['fundo_nombre'] }}"
                            data-jabas="{{ number_format($viaje['total_jabas']) }}"
                            data-kilos="{{ number_format($viaje['total_kilos'], 2) }} kg"
                            data-lotes="{{ $viaje['lotes']->implode(', ') ?: '—' }}"
                            onclick="abrirModalInformacion(this)"><x-ui-icon name="note" size="16" /> <span class="btn-action-label">Ver información</span></button>
                        @if($estadoPanel === 'pendiente')
                        <button type="button" class="btn-confirmar-salida"
                            title="Revisar y validar salida"
                            aria-label="Revisar y validar salida del Viaje N° {{ $viaje['viaje'] }}"
                            data-ids="{{ implode(',', $viaje['ids']) }}"
                            onclick="abrirModalSalida(this)"><x-ui-icon name="check" size="16" /> <span class="btn-action-label">Revisar y validar salida</span></button>
                        @else
                        <span class="viaje-validado-fecha">Validado el {{ $viaje['fecha_validacion']?->format('d/m/Y H:i') ?? '—' }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div style="text-align:center;padding:3rem 1rem;background:var(--clr-surface-0);border-radius:var(--radius-lg);border:1px dashed var(--brd-base);">
                <h3>No hay envíos {{ $estadoPanel === 'validado' ? 'validados' : 'pendientes' }} para esta búsqueda</h3>
                <p>Prueba con otros filtros o revisa los envíos más tarde.</p>
                <a class="btn btn-secondary" href="{{ route('garita.index', ['estado' => $estadoPanel]) }}">Limpiar filtros</a>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal de información completa del viaje --}}
<div class="modal-garita" id="modal-informacion-viaje" role="dialog" aria-modal="true" aria-labelledby="modal-informacion-title">
    <div class="modal-garita-sheet">
        <div class="modal-garita-header">
            <h3 id="modal-informacion-title" style="margin:0; font-size:1.125rem; font-weight:800;">Información del viaje</h3>
            <button type="button" onclick="cerrarModalInformacion()" style="background:none; border:none; color:#ffffff; cursor:pointer; font-size:1.25rem;" aria-label="Cerrar información">&times;</button>
        </div>
        <div class="modal-garita-body">
            <dl class="modal-garita-info-grid">
                <div><dt>Viaje</dt><dd id="info-viaje"></dd></div>
                <div><dt>Placa</dt><dd id="info-placa"></dd></div>
                <div><dt>Fundo</dt><dd id="info-fundo"></dd></div>
                <div><dt>Fecha de producción</dt><dd id="info-fecha"></dd></div>
                <div><dt>Fecha y hora de registro</dt><dd id="info-registro"></dd></div>
                <div><dt>Código del envío</dt><dd id="info-envio"></dd></div>
                <div><dt>Cliente</dt><dd id="info-cliente"></dd></div>
                <div><dt>Conductor</dt><dd id="info-conductor"></dd></div>
                <div><dt>Jabas</dt><dd id="info-jabas"></dd></div>
                <div><dt>Kilogramos</dt><dd id="info-kilos"></dd></div>
                <div><dt>Lotes del envío</dt><dd id="info-lotes"></dd></div>
            </dl>
        </div>
        <div class="modal-garita-footer">
            <button type="button" class="btn btn-secondary" onclick="cerrarModalInformacion()">Cerrar</button>
        </div>
    </div>
</div>

{{-- Modal de Confirmación de Salida en Garita --}}
<div class="modal-garita" id="modal-confirmar-salida" role="dialog" aria-modal="true" aria-labelledby="modal-salida-title">
    <div class="modal-garita-sheet">
        <form method="POST" action="{{ route('garita.confirmar-salida') }}">
            @csrf
            <div id="modal-hidden-inputs"></div>

            <div class="modal-garita-header">
                <h3 id="modal-salida-title" style="margin: 0; font-size: 1.125rem; font-weight: 800;">Confirmar Salida de Camión</h3>
                <button type="button" onclick="cerrarModalSalida()" style="background:none; border:none; color:#ffffff; cursor:pointer; font-size:1.25rem;" aria-label="Cerrar">&times;</button>
            </div>

            <div class="modal-garita-body">
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <label for="observacion_garita" style="font-size: 0.8125rem; font-weight: 600; color: var(--txt-secondary);">Observación de Garita (Opcional):</label>
                    <textarea name="observacion_garita" id="observacion_garita" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid var(--brd-base); border-radius: var(--radius-md); font-size: 0.8125rem;" placeholder="Ej. Camión inspeccionado, precinto conforme, salida autorizada..."></textarea>
                </div>
            </div>

            <div class="modal-garita-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalSalida()">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="background: #166534;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="margin-right: 4px;"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirmar Salida Ahora
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalInformacion(button) {
        ['envio', 'viaje', 'fecha', 'registro', 'cliente', 'conductor', 'placa', 'fundo', 'jabas', 'kilos', 'lotes'].forEach(function(campo) {
            document.getElementById('info-' + campo).innerText = button.dataset[campo] || '—';
        });
        document.getElementById('modal-informacion-viaje').classList.add('is-open');
    }

    function cerrarModalInformacion() {
        document.getElementById('modal-informacion-viaje').classList.remove('is-open');
    }

    function abrirModalSalida(button) {
        var ids = button.dataset.ids.split(',').filter(Boolean);

        var hiddenContainer = document.getElementById('modal-hidden-inputs');
        hiddenContainer.innerHTML = '';
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'venta_ids[]';
            input.value = id;
            hiddenContainer.appendChild(input);
        });

        var observacion = document.getElementById('observacion_garita');
        observacion.value = '';
        document.getElementById('modal-confirmar-salida').classList.add('is-open');
        requestAnimationFrame(function() { observacion.focus(); });
    }

    function cerrarModalSalida() {
        document.getElementById('modal-confirmar-salida').classList.remove('is-open');
    }
</script>
@endsection


