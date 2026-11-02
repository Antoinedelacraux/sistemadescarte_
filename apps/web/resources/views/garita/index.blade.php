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
            <a href="{{ route('garita.index', array_merge(request()->except(['estado', 'viaje']), ['estado' => 'pendiente'])) }}" aria-current="{{ $estadoPanel === 'pendiente' ? 'page' : 'false' }}">Envíos pendientes</a>
            <a href="{{ route('garita.index', array_merge(request()->except(['estado', 'viaje']), ['estado' => 'validado'])) }}" aria-current="{{ $estadoPanel === 'validado' ? 'page' : 'false' }}">Envíos validados</a>
        </nav>
        <a href="{{ route('garita.historial') }}">Ver historial de validaciones</a>
    </div>

    @include('garita._filtros', ['rutaFiltros' => 'garita.index'])

    {{-- Listado de envíos pendientes --}}
    <div class="viajes-container">
        @forelse($viajesAgrupados as $viaje)
            <article class="viaje-card is-{{ $viaje['estado_consolidado'] }}">
                <header class="viaje-header">
                    <div class="viaje-title-group"><span class="viaje-title">{{ $viaje['envio_codigo'] ?? 'Viaje N° '.$viaje['viaje'] }}</span><span class="viaje-date">Viaje N° {{ $viaje['viaje'] }} · Producción: {{ $viaje['fecha_produccion']?->format('d/m/Y') ?? '—' }}</span>@if($fundos->count() > 1)<span class="badge-fundo-tag">{{ $viaje['fundo_nombre'] }}</span>@endif</div>
                    <span class="badge-status {{ $estadoPanel === 'validado' ? 'badge-status-validado' : 'badge-status-pendiente' }}">{{ $estadoPanel === 'validado' ? 'Validado' : 'Pendiente de validación' }}</span>
                </header>
                <div class="viaje-body">
                    <div class="viaje-meta-grid">
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Fecha y hora de registro</span><span class="viaje-meta-val">{{ $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '—' }}</span></div>
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Cliente</span><span class="viaje-meta-val">{{ $viaje['cliente'] }}</span></div>
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Conductor</span><span class="viaje-meta-val">{{ $viaje['conductor'] }}</span></div>
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Placa</span><span class="viaje-meta-val">{{ $viaje['placa'] }}</span></div>
                        <div class="viaje-meta-item is-carga"><span class="viaje-meta-lbl">Jabas</span><span class="viaje-meta-val">{{ number_format($viaje['total_jabas']) }}</span></div>
                        <div class="viaje-meta-item is-carga"><span class="viaje-meta-lbl">Kilogramos</span><span class="viaje-meta-val">{{ number_format($viaje['total_kilos'], 2) }} kg</span></div>
                        @if($viaje['lotes']->isNotEmpty())
                        <div class="viaje-meta-item"><span class="viaje-meta-lbl">Lotes del envío</span><span class="viaje-meta-val">{{ $viaje['lotes']->implode(', ') }}</span></div>
                        @endif
                    </div>
                    <div class="viaje-footer">
                        @if($estadoPanel === 'pendiente')
                        <button type="button" class="btn-confirmar-salida" data-envio="{{ $viaje['envio_codigo'] ?? '' }}" data-placa="{{ $viaje['placa'] }}" data-viaje="{{ $viaje['viaje'] }}" data-kilos="{{ number_format($viaje['total_kilos'], 2) }}" data-ids="{{ implode(',', $viaje['ids']) }}" onclick="abrirModalSalida(this)">Confirmar salida — Viaje N° {{ $viaje['viaje'] }}</button>
                        @else
                        <span class="viaje-validado-fecha">Validado el {{ $viaje['fecha_validacion']?->format('d/m/Y H:i') ?? '—' }}</span>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div style="text-align:center;padding:3rem 1rem;background:var(--clr-surface-0);border-radius:var(--radius-lg);border:1px dashed var(--brd-base);"><h3>No hay envíos {{ $estadoPanel === 'validado' ? 'validados' : 'pendientes' }} para esta búsqueda</h3><p>Prueba con otros filtros o revisa los envíos más tarde.</p></div>
        @endforelse
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
                <p style="margin-bottom: 1rem; font-size: 0.875rem; color: var(--txt-secondary);">
                    ¿Confirmas la salida física del camión con placa <strong id="modal-salida-placa" style="color: #1e293b;"></strong> para el <strong>Viaje N° <span id="modal-salida-viaje"></span></strong> (<span id="modal-salida-envio"></span>)?
                </p>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                    <span style="font-size: 0.75rem; color: #166534; font-weight: 600; text-transform: uppercase;">Total de Carga a Despachar</span>
                    <div id="modal-salida-kilos" style="font-size: 1.35rem; font-weight: 800; color: #15803d;"></div>
                </div>

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
    function abrirModalSalida(button) {
        var envio = button.dataset.envio;
        var placa = button.dataset.placa;
        var viaje = button.dataset.viaje;
        var kilos = button.dataset.kilos;
        var ids = button.dataset.ids.split(',').filter(Boolean);
        document.getElementById('modal-salida-placa').innerText = placa;
        document.getElementById('modal-salida-viaje').innerText = viaje;
        document.getElementById('modal-salida-envio').innerText = envio || 'relación histórica';
        document.getElementById('modal-salida-kilos').innerText = kilos + ' kg';
        
        var hiddenContainer = document.getElementById('modal-hidden-inputs');
        hiddenContainer.innerHTML = '';
        ids.forEach(function(id) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'venta_ids[]';
            input.value = id;
            hiddenContainer.appendChild(input);
        });

        document.getElementById('modal-confirmar-salida').classList.add('is-open');
    }

    function cerrarModalSalida() {
        document.getElementById('modal-confirmar-salida').classList.remove('is-open');
    }
</script>
@endsection


