{{-- Filtros de búsqueda para historial de ventas --}}
@php
    $esRegistrador = Auth::user()->isRegistrador();
    $filtrosAvanzadosActivos = request()->filled('fundo_id')
        || request()->filled('motivo')
        || request()->filled('estado')
        || request()->filled('estado_registro');
@endphp
<form method="GET" action="{{ route('ventas.index') }}" class="filter-card {{ $esRegistrador ? 'filter-card--registrador' : '' }}" role="search" aria-label="Filtros de historial">
    <div class="filter-grid">
        <div class="filter-date-fields">
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
        </div>

        @if($esRegistrador)
        <button type="button"
                class="filter-more-toggle"
                aria-expanded="{{ $filtrosAvanzadosActivos ? 'true' : 'false' }}"
                aria-controls="ventas-filtros-avanzados">
            <span>Más filtros</span>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        @endif

        <div class="filter-advanced-fields {{ $filtrosAvanzadosActivos ? 'is-open' : '' }}" id="ventas-filtros-avanzados">
        {{-- Filtro por Fundo --}}
        @if(Auth::user()->hasAccessToAllFundos() || $fundos->count() > 1)
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

        {{-- Filtro por tipo de registro --}}
        <div class="form-group" style="margin-bottom: 0;">
            <label for="estado_registro" class="form-label text-xs">Tipo de registro</label>
            <select name="estado_registro" id="estado_registro" class="form-control" style="height: 38px;">
                <option value="">Todos</option>
                <option value="borrador" {{ request('estado_registro') == 'borrador' ? 'selected' : '' }}>Borradores</option>
                <option value="oficial" {{ request('estado_registro') == 'oficial' ? 'selected' : '' }}>Oficiales</option>
            </select>
        </div>
        </div>

        {{-- Botones de filtro --}}
        <div class="filter-actions">
            <button type="submit" class="btn btn-secondary" style="height: 38px; flex: 1;">
                Filtrar
            </button>
            @if(request()->hasAny(['fundo_id', 'motivo', 'estado', 'estado_registro', 'fecha_desde', 'fecha_hasta']))
                <a href="{{ route('ventas.index') }}" class="btn btn-ghost" style="height: 38px;" title="Limpiar filtros">
                    ✕
                </a>
            @endif
        </div>
    </div>
</form>

@if($esRegistrador)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.querySelector('.filter-card--registrador .filter-more-toggle');
        var advanced = document.getElementById('ventas-filtros-avanzados');
        if (!toggle || !advanced) return;

        toggle.addEventListener('click', function() {
            var open = !advanced.classList.contains('is-open');
            advanced.classList.toggle('is-open', open);
            toggle.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });
</script>
@endif
