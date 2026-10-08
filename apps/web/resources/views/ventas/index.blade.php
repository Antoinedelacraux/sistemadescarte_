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

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div>
    {{-- Barra superior de acciones --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: var(--text-2xl); font-weight: 700; color: var(--txt-primary);">Historial de Ventas de Descarte</h1>
            <p style="font-size: var(--text-sm); color: var(--txt-muted);">Consulta de pesajes y descarte registrado en campo y packing</p>
        </div>

        @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
        <div>
            <a href="{{ route('ventas.create') }}" class="btn btn-primary" id="btn-nueva-venta">
                <span>➕</span> Registrar Venta
            </a>
        </div>
        @endif
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
                    🔍 Filtrar
                </button>
                @if(request()->hasAny(['fundo_id', 'motivo', 'fecha_desde', 'fecha_hasta']))
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
            <div class="text-xs text-muted">
                Tip: Desliza el sidebar con el botón superior si deseas ver la tabla a pantalla completa.
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="sales-table" role="table" aria-label="Historial de ventas de descarte">
                <thead>
                    <tr>
                        <th scope="col">Fundo</th>
                        <th scope="col">Fecha Prod.</th>
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
                        <tr>
                            <td>
                                <strong>{{ $venta->fundo?->name ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                {{ $venta->fecha_produccion->format('d/m/Y') }}
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
                                <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-secondary btn-sm" title="Editar registro" aria-label="Editar venta del {{ $venta->fecha_produccion->format('d/m/Y') }}">
                                    ✏️ Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="padding: 3rem 1rem; text-align: center;">
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
</div>
@endsection
