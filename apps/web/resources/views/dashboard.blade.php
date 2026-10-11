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

    {{-- Selector de vista de tablas (Control interactivo) --}}
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

    {{-- 3. Tabla 1: Segregación por origen y tipo de descarte --}}
    @include('dashboard._tabla_segregacion')

    {{-- 4. Tabla 2: Resumen por cliente --}}
    @include('dashboard._tabla_clientes')

    {{-- 5. Tabla 3: Envíos recientes --}}
    @include('dashboard._tabla_recientes')

    {{-- 6. Interactividad JavaScript --}}
    @include('dashboard._scripts')
@endsection
