@if($fundos->isEmpty() && !Auth::user()->hasAccessToAllFundos())
    <p role="status" class="garita-no-fundos">No tienes fundos activos asignados. Solicita a Administración que revise tus asignaciones.</p>
@endif
@php($esHistorialGarita = $rutaFiltros === 'garita.historial')
<form method="GET" action="{{ route($rutaFiltros) }}" class="garita-filter-bar {{ $esHistorialGarita ? 'garita-filter-bar--historial' : 'garita-filter-bar--panel' }}" aria-label="Filtros de envíos">
    @if(isset($estadoPanel))
        <input type="hidden" name="estado" value="{{ $estadoPanel }}">
    @endif
    <div class="garita-filter-field">
        <label for="garita-fecha">Fecha de producción</label>
        <input id="garita-fecha" type="date" name="fecha" value="{{ $filtros['fecha'] }}" onchange="this.form.elements.viaje.value=''; this.form.requestSubmit()">
    </div>
    <div class="garita-filter-field">
        <label for="garita-viaje">Número de viaje</label>
        <select id="garita-viaje" name="viaje" @disabled(empty($filtros['fecha']))>
            <option value="">Todos los viajes</option>
            @foreach($opcionesViajes as $numero)
                <option value="{{ $numero }}" @selected((string) $filtros['viaje'] === (string) $numero)>{{ $numero }}</option>
            @endforeach
        </select>
    </div>
    @if($esHistorialGarita)
        @if($fundos->count() > 1)
        <div class="garita-filter-field">
            <label for="garita-fundo">Fundo</label>
            <select id="garita-fundo" name="fundo_id" onchange="this.form.elements.viaje.value=''; this.form.requestSubmit()">
                <option value="">Todos mis fundos</option>
                @foreach($fundos as $fundo)
                    <option value="{{ $fundo->id }}" @selected((string) $filtros['fundo_id'] === (string) $fundo->id)>{{ $fundo->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="garita-filter-field">
            <label for="garita-conductor">Conductor</label>
            <input id="garita-conductor" name="conductor" type="search" value="{{ $filtros['conductor'] }}" placeholder="Buscar conductor">
        </div>
        <div class="garita-filter-field">
            <label for="garita-cliente">Cliente</label>
            <input id="garita-cliente" name="cliente" type="search" value="{{ $filtros['cliente'] }}" placeholder="Buscar cliente">
        </div>
    @endif
    <div class="garita-filter-actions">
        <button type="submit" class="btn btn-primary">Buscar</button>
        <a href="{{ route($rutaFiltros, isset($estadoPanel) && $estadoPanel === 'validado' ? ['estado' => 'validado'] : []) }}" class="btn btn-secondary">Limpiar</a>
    </div>
</form>
<style>
    .garita-no-fundos { padding:.85rem 1rem; margin:0 0 1rem; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; }
    .garita-filter-bar { display:grid; align-items:end; gap:.8rem; padding:1.15rem 1.25rem; margin:0 0 1.25rem; background:#fff; border:1px solid #dce5df; border-radius:14px; }
    .garita-filter-bar--panel { grid-template-columns:minmax(220px, 1fr) minmax(220px, 1fr) auto; }
    .garita-filter-bar--historial { grid-template-columns:repeat(5, minmax(150px, 1fr)) auto; }
    .garita-filter-field { display:flex; flex:1 1 160px; min-width:145px; flex-direction:column; gap:.35rem; }
    .garita-filter-field label { font-size:.76rem; font-weight:700; color:#334155; }
    .garita-filter-field input,.garita-filter-field select { width:100%; height:2.65rem; padding:.5rem .65rem; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:#172c20; font:inherit; }
    .garita-filter-field select:disabled { background:#f1f5f9; color:#64748b; }
    .garita-filter-hint { font-size:.7rem; line-height:1.2; color:#64748b; }
    .garita-filter-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
    .garita-filter-actions .btn { min-height:2.65rem; display:inline-flex; align-items:center; justify-content:center; }
    @media(max-width:1100px) { .garita-filter-bar--historial { grid-template-columns:repeat(3, minmax(150px, 1fr)); } }
    @media(max-width:700px) { .garita-filter-bar { grid-template-columns:repeat(2, minmax(0, 1fr)); padding:1rem; gap:.75rem; } .garita-filter-field { min-width:0; } .garita-filter-actions { grid-column:1 / -1; width:100%; } .garita-filter-actions .btn { flex:1; } }
</style>
