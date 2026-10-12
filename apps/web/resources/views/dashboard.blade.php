@extends('layouts.app')

@section('title', 'Panel de Control')
@section('page-title', 'Panel de Control')

@section('styles')
    @include('dashboard._styles')
@endsection

@section('content')
    {{-- 1. Hero banner de bienvenida y acciones --}}
    @include('dashboard._banner')

    {{-- 2. Resumen de KPIs y métricas clave --}}
    @include('dashboard._kpis')

    {{-- Selector de vista de tablas (Control interactivo) y Filtro por Fundo --}}
    <div class="table-view-control-bar" id="table-view-controller" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 0.875rem; flex-wrap: wrap;">
            <div class="table-view-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <span>Mostrar tabla:</span>
            </div>
            <div class="table-view-pills" role="tablist" aria-label="Seleccionar tablas a visualizar">
                <button type="button" class="view-pill active" data-view="motivos" onclick="cambiarVistaTabla('motivos', this)" role="tab" aria-selected="true">
                    <span>Por Tipo y Descarte</span>
                </button>
                <button type="button" class="view-pill" data-view="clientes" onclick="cambiarVistaTabla('clientes', this)" role="tab" aria-selected="false">
                    <span>Por Cliente</span>
                </button>
            </div>
        </div>

        @if(!empty($tieneAccesoATodos) && !empty($fundosFiltro) && $fundosFiltro->isNotEmpty())
        <div class="table-view-fundo-filter" style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
            <label for="fundo_filter_select" style="font-size: 0.8125rem; font-weight: 600; color: var(--txt-secondary, #475569); display: inline-flex; align-items: center; gap: 0.35rem; margin: 0; white-space: nowrap;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Fundo:</span>
            </label>
            <select id="fundo_filter_select" name="fundo_id" class="form-control" style="font-size: 0.8125rem; font-weight: 600; padding: 0.4rem 2rem 0.4rem 0.85rem; border-radius: 9999px; border: 1.5px solid var(--clr-primary-600, #16a34a); background-color: var(--clr-surface-0, #ffffff); color: var(--txt-primary, #0f172a); cursor: pointer; width: auto; min-width: 170px;" onchange="filtrarDashboardPorFundo(this.value)">
                <option value="" {{ empty($fundoSeleccionadoId) ? 'selected' : '' }}>Todos los Fundos</option>
                @foreach($fundosFiltro as $fundoItem)
                    <option value="{{ $fundoItem->id }}" {{ (string)$fundoSeleccionadoId === (string)$fundoItem->id ? 'selected' : '' }}>
                        {{ $fundoItem->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- 3. Tabla 1: Segregación por origen y tipo de descarte --}}
    @include('dashboard._tabla_segregacion')

    {{-- 4. Tabla 2: Resumen por cliente --}}
    @include('dashboard._tabla_clientes')

    {{-- 5. Tabla 3: Envíos recientes --}}
    @include('dashboard._tabla_recientes')

    {{-- 6. Interactividad JavaScript --}}
    @include('dashboard._scripts')
@endsection
