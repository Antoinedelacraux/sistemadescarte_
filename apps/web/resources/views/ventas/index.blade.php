@extends('layouts.app')

@section('title', 'Historial de Ventas de Descarte')
@section('page-title', 'Historial de Ventas de Descarte')

@section('styles')
<style>
    .filter-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow-xs);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.875rem;
        align-items: flex-end;
    }

    .badge-motivo {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius-full);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .badge-campo    { background: #dcfce7; color: #166534; }
    .badge-packing  { background: #e0f2fe; color: #0369a1; }
    .badge-cosecha  { background: #fef3c7; color: #92400e; }

    .badge-tipo {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--clr-surface-100);
        color: var(--txt-secondary);
    }

    .monto-total {
        font-weight: 700;
        color: var(--clr-primary-900);
        font-variant-numeric: tabular-nums;
    }

    .table-container {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        overflow: hidden;
    }

    .table-header-bar {
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--brd-base);
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .sales-table {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--text-sm);
    }

    .sales-table th {
        background: var(--clr-surface-50);
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        border-bottom: 1px solid var(--brd-base);
        text-align: left;
        white-space: nowrap;
    }

    .sales-table th.text-right,
    .sales-table td.text-right {
        text-align: right;
    }

    .sales-table td {
        padding: 0.8125rem 1rem;
        border-bottom: 1px solid var(--clr-surface-100);
        vertical-align: middle;
        white-space: nowrap;
    }

    .sales-table tbody tr:hover td {
        background: var(--clr-primary-50);
    }

    .pagination-wrapper {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--clr-surface-50);
    }

    .index-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .index-top-bar h1 {
        font-size: clamp(1.2rem, 4.5vw, 1.5rem);
        font-weight: 700;
        color: var(--txt-primary);
        line-height: 1.2;
    }

    .index-top-bar p {
        font-size: clamp(0.75rem, 2.5vw, 0.8125rem);
        color: var(--txt-muted);
        margin-top: 0.125rem;
    }

    .index-actions-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .index-top-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .index-actions-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
            gap: 0.5rem;
        }
        .index-actions-group .btn {
            width: 100%;
            justify-content: center;
            font-size: clamp(0.72rem, 2.6vw, 0.8125rem);
            padding: 0.5rem 0.25rem;
            text-align: center;
        }
        .sales-table th {
            padding: 0.5rem 0.5rem;
            font-size: 0.625rem;
        }
        .sales-table td {
            padding: 0.5rem 0.5rem;
            font-size: 0.75rem;
        }
        .badge-motivo {
            font-size: 0.5625rem;
            padding: 0.125rem 0.35rem;
        }
        .badge-tipo {
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
        }
        .btn-row-action {
            padding: 0.25rem 0.45rem;
            font-size: 0.6875rem;
            gap: 0.2rem;
        }
        .modal-money-banner {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        .modal-money-total {
            text-align: left;
        }
        .modal-data-grid {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        .modal-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }
        .modal-footer > div {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .modal-footer .btn {
            justify-content: center;
        }
    }

    /* Acciones en fila de tabla - Iconos Minimalistas */
    .row-actions-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all var(--transition-fast);
        padding: 0;
        background: transparent;
        color: var(--txt-secondary);
        flex-shrink: 0;
    }

    .btn-action-icon:hover {
        transform: translateY(-1px);
    }

    .btn-action-icon svg {
        width: 15px;
        height: 15px;
        stroke-width: 2.2;
    }

    .btn-action-view {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }
    .btn-action-view:hover {
        background: #dcfce7;
        color: #14532d;
        border-color: #86efac;
        box-shadow: 0 1px 3px rgba(22, 101, 52, 0.15);
    }

    .btn-action-edit {
        background: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }
    .btn-action-edit:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
    }

    .btn-action-anular {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }
    .btn-action-anular:hover {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fdba74;
        box-shadow: 0 1px 3px rgba(194, 65, 12, 0.15);
    }

    .btn-action-reactivar {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .btn-action-reactivar:hover {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
        box-shadow: 0 1px 3px rgba(29, 78, 216, 0.15);
    }

    .btn-action-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .btn-action-delete:hover {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #f87171;
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.15);
    }

    .badge-anulado {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .row-anulado {
        opacity: 0.68;
        background: #fcfcfc;
    }
    .row-anulado td.monto-total {
        text-decoration: line-through;
        color: var(--txt-muted);
    }

    /* Modal de Inspección Rápida de Venta */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 31, 20, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-sheet {
        background: #ffffff;
        border-radius: var(--radius-xl);
        max-width: 680px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--brd-base);
        animation: modalScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes modalScaleIn {
        from { opacity: 0; transform: scale(0.96) translateY(6px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        background: var(--clr-surface-50);
    }

    .modal-title {
        font-size: clamp(1.05rem, 3.5vw, 1.25rem);
        font-weight: 700;
        color: var(--txt-primary);
        margin: 0;
        line-height: 1.2;
    }

    .modal-subtitle {
        font-size: 0.75rem;
        color: var(--txt-muted);
        margin-top: 0.2rem;
    }

    .modal-close-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
        background: white;
        color: var(--txt-muted);
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all var(--transition-fast);
        flex-shrink: 0;
    }

    .modal-close-btn:hover {
        background: var(--clr-surface-100);
        color: var(--txt-primary);
    }

    .modal-body {
        padding: 1.25rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-money-banner {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #86efac;
        border-radius: var(--radius-lg);
        padding: 0.875rem 1rem;
        display: grid;
        grid-template-columns: 1fr 1fr 1.3fr;
        gap: 0.75rem;
        align-items: center;
        margin-bottom: 1.125rem;
    }

    .modal-money-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .modal-money-label {
        font-size: 0.625rem;
        font-weight: 700;
        color: #166534;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .modal-money-val {
        font-size: clamp(0.95rem, 3vw, 1.15rem);
        font-weight: 800;
        color: #14532d;
        font-family: monospace;
    }

    .modal-money-total {
        background: #14532d;
        color: #f0fdf4;
        padding: 0.5rem 0.75rem;
        border-radius: var(--radius-md);
        text-align: right;
    }

    .modal-money-label-light {
        display: block;
        font-size: 0.5625rem;
        font-weight: 700;
        color: #86efac;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .modal-money-val-light {
        display: block;
        font-size: clamp(1rem, 3.5vw, 1.3rem);
        font-weight: 900;
        color: #ffffff;
        font-family: monospace;
        line-height: 1.1;
    }

    .modal-section-title {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        border-bottom: 1px solid var(--clr-surface-100);
        padding-bottom: 0.35rem;
        margin-bottom: 0.625rem;
    }

    .modal-data-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.625rem 1rem;
    }

    .modal-data-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .modal-data-label {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--txt-muted);
    }

    .modal-data-value {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--txt-primary);
        word-break: break-word;
    }

    .modal-obs-box {
        background: var(--clr-surface-50);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
        color: var(--txt-secondary);
        line-height: 1.4;
    }

    .modal-audit-bar {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px dashed var(--brd-base);
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.6875rem;
        color: var(--txt-muted);
        flex-wrap: wrap;
    }

    .modal-footer {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
</style>
@endsection

@section('content')
<div>
    {{-- Barra superior de acciones --}}
    <div class="index-top-bar">
        <div>
            <h1>Historial de Ventas de Descarte</h1>
            <p>Consulta de pesajes y descarte registrado en campo y packing</p>
        </div>

        <div class="index-actions-group">
            <a href="{{ route('reportes.index') }}" class="btn btn-secondary" title="Exportar datos a Excel">
                Exportar Excel
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
            <a href="{{ route('ventas.create') }}" class="btn btn-primary" id="btn-nueva-venta">
                Registrar Venta
            </a>
            @endif
        </div>
    </div>

    {{-- Filtros de búsqueda --}}
    <form method="GET" action="{{ route('ventas.index') }}" class="filter-card" role="search" aria-label="Filtros de historial">
        <div class="filter-grid">
            {{-- Filtro por Fundo --}}
            @if(Auth::user()->isAdmin() || Auth::user()->isAnalista() || $fundos->count() > 1)
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fundo_id" class="form-label text-xs">Fundo</label>
                <select name="fundo_id" id="fundo_id" class="form-control" style="height: 38px;">
                    <option value="">Todos los Fundos</option>
                    @foreach($fundos as $fundo)
                        <option value="{{ $fundo->id }}" {{ request('fundo_id') == $fundo->id ? 'selected' : '' }}>
                            {{ $fundo->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Filtro por Motivo --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label for="motivo" class="form-label text-xs">Motivo</label>
                <select name="motivo" id="motivo" class="form-control" style="height: 38px;">
                    <option value="">Todos los motivos</option>
                    <option value="Campo" {{ request('motivo') == 'Campo' ? 'selected' : '' }}>Campo</option>
                    <option value="Packing" {{ request('motivo') == 'Packing' ? 'selected' : '' }}>Packing</option>
                    <option value="Cosecha Nacional" {{ request('motivo') == 'Cosecha Nacional' ? 'selected' : '' }}>Cosecha Nacional</option>
                </select>
            </div>

            {{-- Filtro por Estado --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label for="estado" class="form-label text-xs">Estado</label>
                <select name="estado" id="estado" class="form-control" style="height: 38px;">
                    <option value="" {{ request('estado') === null || request('estado') === '' ? 'selected' : '' }}>Todos</option>
                    <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activos</option>
                    <option value="anulado" {{ request('estado') == 'anulado' ? 'selected' : '' }}>Anulados</option>
                </select>
            </div>

            {{-- Filtro por Fecha Desde --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fecha_desde" class="form-label text-xs">Desde</label>
                <input type="date" name="fecha_desde" id="fecha_desde" class="form-control" style="height: 38px;" value="{{ request('fecha_desde') }}">
            </div>

            {{-- Filtro por Fecha Hasta --}}
            <div class="form-group" style="margin-bottom: 0;">
                <label for="fecha_hasta" class="form-label text-xs">Hasta</label>
                <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control" style="height: 38px;" value="{{ request('fecha_hasta') }}">
            </div>

            {{-- Botones de filtro --}}
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-secondary" style="height: 38px; flex: 1;">
                    Filtrar
                </button>
                @if(request()->hasAny(['fundo_id', 'motivo', 'estado', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('ventas.index') }}" class="btn btn-ghost" style="height: 38px;" title="Limpiar filtros">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Tabla de Registros --}}
    <div class="table-container">
        <div class="table-header-bar">
            <div style="font-size: var(--text-sm); font-weight: 600; color: var(--txt-primary);">
                Registros encontrados: <span style="color: var(--clr-primary-700);">{{ $ventas->total() }}</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="sales-table" role="table" aria-label="Historial de ventas de descarte">
                <thead>
                    <tr>
                        <th scope="col">Fundo</th>
                        <th scope="col">Fecha Prod.</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Cuartel</th>
                        <th scope="col">Motivo</th>
                        <th scope="col">Tipo Descarte</th>
                        <th scope="col" class="text-right">Kilogramos</th>
                        <th scope="col" class="text-right">Precio/Kg</th>
                        <th scope="col" class="text-right">Total (S/)</th>
                        <th scope="col" style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        <tr class="{{ $venta->isAnulado() ? 'row-anulado' : '' }}">
                            <td>
                                <strong>{{ $venta->fundo?->name ?? 'N/A' }}</strong>
                                @if($venta->isAnulado())
                                    <div style="margin-top: 0.15rem;"><span class="badge-motivo badge-anulado">Anulado</span></div>
                                @endif
                            </td>
                            <td>
                                {{ $venta->fecha_produccion->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong style="color: var(--txt-primary);">{{ $venta->cliente ?: ($venta->ruc ? 'RUC: '.$venta->ruc : '—') }}</strong>
                            </td>
                            <td>
                                {{ $venta->lote?->nombre ?? 'Sin Lote' }}
                            </td>
                            <td>
                                {{ $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? '—') }}
                            </td>
                            <td>
                                @if($venta->motivo === 'Campo')
                                    <span class="badge-motivo badge-campo">Campo</span>
                                @elseif($venta->motivo === 'Packing')
                                    <span class="badge-motivo badge-packing">Packing</span>
                                @else
                                    <span class="badge-motivo badge-cosecha">Cosecha Nac.</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-tipo">{{ $venta->tipo_descarte }}</span>
                            </td>
                            <td class="text-right" style="font-weight: 600;">
                                {{ number_format($venta->kilogramos, 2) }} kg
                            </td>
                            <td class="text-right text-muted">
                                S/ {{ number_format($venta->precio, 2) }}
                            </td>
                            <td class="text-right monto-total">
                                S/ {{ number_format($venta->valor_venta, 2) }}
                            </td>
                            <td style="text-align: center;">
                                <div class="row-actions-btn-group">
                                    {{-- Ver (Modal de visualización) --}}
                                    <button type="button" 
                                            class="btn-action-icon btn-action-view" 
                                            title="Visualizar registro" 
                                            aria-label="Ver detalle del registro del {{ $venta->fecha_produccion->format('d/m/Y') }}"
                                            data-id="{{ $venta->id }}"
                                            data-estado="{{ $venta->estado ?? 'activo' }}"
                                            data-fundo="{{ $venta->fundo?->name ?? 'Fundo' }}"
                                            data-fecha="{{ $venta->fecha_produccion->format('d/m/Y') }}"
                                            data-cliente="{{ $venta->cliente ?: ($venta->ruc ? 'RUC: '.$venta->ruc : 'Sin cliente especificado') }}"
                                            data-ruc="{{ $venta->ruc ?: '—' }}"
                                            data-motivo="{{ $venta->motivo }}"
                                            data-tipo="{{ $venta->tipo_descarte }}"
                                            data-lote="{{ $venta->lote?->nombre ?? 'Sin Lote' }}"
                                            data-cuartel="{{ $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? '—') }}"
                                            data-kilos="{{ number_format($venta->kilogramos, 2) }}"
                                            data-precio="{{ number_format($venta->precio, 2) }}"
                                            data-total="{{ number_format($venta->valor_venta, 2) }}"
                                            data-jabas="{{ $venta->jabas ? number_format($venta->jabas) : '—' }}"
                                            data-peso-jaba="{{ $venta->peso_jaba ? number_format($venta->peso_jaba, 2) : '—' }}"
                                            data-placa="{{ $venta->placa ?: '—' }}"
                                            data-conductor="{{ $venta->conductor ?: '—' }}"
                                            data-brevete="{{ $venta->brevete ?: '—' }}"
                                            data-viaje="{{ $venta->viaje ?: '—' }}"
                                            data-observacion="{{ $venta->observacion ?: '' }}"
                                            data-creado-por="{{ $venta->creator?->name ?? 'Usuario' }}"
                                            data-creado-at="{{ $venta->created_at ? $venta->created_at->format('d/m/Y H:i') : '—' }}"
                                            data-actualizado-por="{{ $venta->updater?->name ?? '' }}"
                                            data-actualizado-at="{{ $venta->updated_at && $venta->updated_at != $venta->created_at ? $venta->updated_at->format('d/m/Y H:i') : '' }}"
                                            data-url-show="{{ route('ventas.show', $venta) }}"
                                            @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
                                            data-url-edit="{{ route('ventas.edit', $venta) }}"
                                            @endif
                                            onclick="abrirModalDetalle(this)">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
                                        @if($venta->isActivo())
                                            {{-- Editar (Solo activo) --}}
                                            <a href="{{ route('ventas.edit', $venta) }}" class="btn-action-icon btn-action-edit" title="Editar registro" aria-label="Editar venta del {{ $venta->fecha_produccion->format('d/m/Y') }}">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>

                                            {{-- Anular (Paso 1 del ciclo de eliminación) --}}
                                            <form method="POST" action="{{ route('ventas.anular', $venta) }}" style="display:inline;" onsubmit="return confirm('¿Deseas ANULAR este registro de venta? Pasará a estado anulado y se descontará de los totales.');">
                                                @csrf
                                                <button type="submit" class="btn-action-icon btn-action-anular" title="Anular registro (Paso 1 antes de eliminar)" aria-label="Anular venta">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            {{-- Si ya está anulado: Permitir Reactivar --}}
                                            <form method="POST" action="{{ route('ventas.reactivar', $venta) }}" style="display:inline;" onsubmit="return confirm('¿Deseas REACTIVAR este registro de venta?');">
                                                @csrf
                                                <button type="submit" class="btn-action-icon btn-action-reactivar" title="Reactivar registro" aria-label="Reactivar venta">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                                </button>
                                            </form>

                                            {{-- Eliminar definitivamente (Paso 2: Solo si ya está anulado) --}}
                                            <form method="POST" action="{{ route('ventas.destroy', $venta) }}" style="display:inline;" onsubmit="return confirm('¡ADVERTENCIA! Este registro ya está anulado.\n¿Deseas ELIMINARLO DEFINITIVAMENTE de la base de datos?\nEsta acción es irreversible.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-icon btn-action-delete" title="Eliminar definitivamente de la base de datos" aria-label="Eliminar venta permanentemente">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding: 3rem 1rem; text-align: center;">
                                <div class="empty-state">
                                    <div class="empty-icon">📋</div>
                                    <div class="empty-title">No hay ventas registradas</div>
                                    <div class="empty-desc">
                                        @if(request()->hasAny(['fundo_id', 'motivo', 'fecha_desde', 'fecha_hasta']))
                                            No se encontraron registros con los filtros seleccionados. Intenta cambiar los criterios.
                                        @else
                                            Aún no se han ingresado pesajes de descarte. Comienza haciendo clic en "Registrar Venta".
                                        @endif
                                    </div>
                                    @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
                                        <div style="margin-top: 1rem;">
                                            <a href="{{ route('ventas.create') }}" class="btn btn-primary btn-sm">
                                                ➕ Registrar Primera Venta
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($ventas->hasPages())
            <div class="pagination-wrapper">
                {{ $ventas->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para Visualizar Registro de Venta --}}
    <div id="modal-detalle-venta" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-venta-title" onclick="cerrarModalSiClickFondo(event)">
        <div class="modal-sheet" role="document" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <h2 id="modal-venta-title" class="modal-title">Detalle del Pesaje</h2>
                        <span id="modal-badge-motivo" class="badge-motivo"></span>
                    </div>
                    <p id="modal-subtitulo" class="modal-subtitle"></p>
                </div>
                <button type="button" class="modal-close-btn" onclick="cerrarModalDetalle()" aria-label="Cerrar ventana">&times;</button>
            </div>

            <div class="modal-body">
                {{-- Banner Económico --}}
                <div class="modal-money-banner">
                    <div class="modal-money-item">
                        <span class="modal-money-label">KILOGRAMOS</span>
                        <span id="modal-kilos" class="modal-money-val">0.00 kg</span>
                    </div>
                    <div class="modal-money-item">
                        <span class="modal-money-label">PRECIO / KG</span>
                        <span id="modal-precio" class="modal-money-val">S/ 0.00</span>
                    </div>
                    <div class="modal-money-total">
                        <span class="modal-money-label-light">TOTAL LIQUIDADO</span>
                        <span id="modal-total" class="modal-money-val-light">S/ 0.00</span>
                    </div>
                </div>

                {{-- Origen y Clasificación --}}
                <div class="modal-section-title">Origen y Clasificación</div>
                <div class="modal-data-grid">
                    <div class="modal-data-item">
                        <span class="modal-data-label">Fundo</span>
                        <span id="modal-fundo" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Fecha de Producción</span>
                        <span id="modal-fecha" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Lote</span>
                        <span id="modal-lote" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Cuartel</span>
                        <span id="modal-cuartel" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Motivo</span>
                        <span id="modal-motivo" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Tipo de Descarte</span>
                        <span id="modal-tipo" class="modal-data-value">—</span>
                    </div>
                </div>

                {{-- Cliente y Jabas --}}
                <div class="modal-section-title" style="margin-top: 1rem;">Cliente y Jabas</div>
                <div class="modal-data-grid">
                    <div class="modal-data-item">
                        <span class="modal-data-label">Cliente / Comprador</span>
                        <span id="modal-cliente" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">RUC</span>
                        <span id="modal-ruc" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Jabas Registradas</span>
                        <span id="modal-jabas" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Peso Promedio / Jaba</span>
                        <span id="modal-peso-jaba" class="modal-data-value">—</span>
                    </div>
                </div>

                {{-- Guía y Transporte --}}
                <div class="modal-section-title" style="margin-top: 1rem;">Guía y Transporte</div>
                <div class="modal-data-grid">
                    <div class="modal-data-item">
                        <span class="modal-data-label">Placa de Vehículo</span>
                        <span id="modal-placa" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Conductor</span>
                        <span id="modal-conductor" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">Brevete</span>
                        <span id="modal-brevete" class="modal-data-value">—</span>
                    </div>
                    <div class="modal-data-item">
                        <span class="modal-data-label">N° de Viaje</span>
                        <span id="modal-viaje" class="modal-data-value">—</span>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="modal-section-title" style="margin-top: 1rem;">Observaciones</div>
                <div class="modal-obs-box" id="modal-observacion">Sin observaciones registradas.</div>

                {{-- Auditoría --}}
                <div class="modal-audit-bar">
                    <div>Registrado por: <strong id="modal-creado-por">—</strong> (<span id="modal-creado-at">—</span>)</div>
                    <div id="modal-wrap-updated" style="display: none;">Editado por: <strong id="modal-actualizado-por">—</strong> (<span id="modal-actualizado-at">—</span>)</div>
                </div>
            </div>

            <div class="modal-footer">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a id="modal-btn-full" href="#" class="btn btn-secondary btn-sm" title="Abrir ficha técnica completa">
                        Ver Ficha Completa
                    </a>
                    <a id="modal-btn-edit" href="#" class="btn btn-primary btn-sm" title="Editar este pesaje">
                        Editar Registro
                    </a>
                </div>
                <button type="button" class="btn btn-ghost btn-sm" onclick="cerrarModalDetalle()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var modal = document.getElementById('modal-detalle-venta');

    window.abrirModalDetalle = function(btn) {
        var d = btn.dataset;

        document.getElementById('modal-fundo').textContent = d.fundo || '—';
        document.getElementById('modal-fecha').textContent = d.fecha || '—';
        document.getElementById('modal-subtitulo').textContent = (d.fundo || 'Fundo') + ' • ' + (d.fecha || '—');

        var badgeMotivo = document.getElementById('modal-badge-motivo');
        badgeMotivo.textContent = d.motivo || '';
        badgeMotivo.className = 'badge-motivo';
        if (d.motivo === 'Campo') {
            badgeMotivo.classList.add('badge-campo');
        } else if (d.motivo === 'Packing') {
            badgeMotivo.classList.add('badge-packing');
        } else {
            badgeMotivo.classList.add('badge-cosecha');
        }

        document.getElementById('modal-kilos').textContent = (d.kilos || '0.00') + ' kg';
        document.getElementById('modal-precio').textContent = 'S/ ' + (d.precio || '0.00');
        document.getElementById('modal-total').textContent = 'S/ ' + (d.total || '0.00');

        document.getElementById('modal-cliente').textContent = d.cliente || '—';
        document.getElementById('modal-ruc').textContent = d.ruc || '—';
        document.getElementById('modal-lote').textContent = d.lote || '—';
        document.getElementById('modal-cuartel').textContent = d.cuartel || '—';
        document.getElementById('modal-motivo').textContent = d.motivo || '—';
        document.getElementById('modal-tipo').textContent = d.tipo || '—';

        document.getElementById('modal-jabas').textContent = d.jabas || '—';
        document.getElementById('modal-peso-jaba').textContent = d.pesoJaba && d.pesoJaba !== '—' ? (d.pesoJaba + ' kg') : '—';

        document.getElementById('modal-placa').textContent = d.placa || '—';
        document.getElementById('modal-conductor').textContent = d.conductor || '—';
        document.getElementById('modal-brevete').textContent = d.brevete || '—';
        document.getElementById('modal-viaje').textContent = d.viaje || '—';

        var obsEl = document.getElementById('modal-observacion');
        if (d.observacion && d.observacion.trim().length > 0) {
            obsEl.textContent = d.observacion;
            obsEl.style.fontStyle = 'normal';
        } else {
            obsEl.textContent = 'Sin observaciones registradas.';
            obsEl.style.fontStyle = 'italic';
        }

        document.getElementById('modal-creado-por').textContent = d.creadoPor || '—';
        document.getElementById('modal-creado-at').textContent = d.creadoAt || '—';

        var wrapUpdated = document.getElementById('modal-wrap-updated');
        if (d.actualizadoPor && d.actualizadoAt) {
            document.getElementById('modal-actualizado-por').textContent = d.actualizadoPor;
            document.getElementById('modal-actualizado-at').textContent = d.actualizadoAt;
            wrapUpdated.style.display = 'block';
        } else {
            wrapUpdated.style.display = 'none';
        }

        var btnFull = document.getElementById('modal-btn-full');
        if (d.urlShow) {
            btnFull.href = d.urlShow;
            btnFull.style.display = 'inline-flex';
        } else {
            btnFull.style.display = 'none';
        }

        var btnEdit = document.getElementById('modal-btn-edit');
        if (d.urlEdit && d.estado !== 'anulado') {
            btnEdit.href = d.urlEdit;
            btnEdit.style.display = 'inline-flex';
        } else {
            btnEdit.style.display = 'none';
        }

        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.cerrarModalDetalle = function() {
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    };

    window.cerrarModalSiClickFondo = function(event) {
        if (event.target === modal) {
            window.cerrarModalDetalle();
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            window.cerrarModalDetalle();
        }
    });
})();
</script>
@endsection
