@if($fundos->isEmpty() && !Auth::user()->hasAccessToAllFundos())
    <p role="status" class="garita-no-fundos">No tienes fundos activos asignados. Solicita a Administración que revise tus asignaciones.</p>
@endif
@php($esHistorialGarita = $rutaFiltros === 'garita.historial')
@php($historialConFundo = $esHistorialGarita && $fundos->count() > 1)
<form method="GET" action="{{ route($rutaFiltros) }}" class="garita-filter-bar {{ $esHistorialGarita ? 'garita-filter-bar--historial' : 'garita-filter-bar--panel' }} {{ $historialConFundo ? 'garita-filter-bar--con-fundo' : 'garita-filter-bar--sin-fundo' }}" aria-label="Filtros de envíos">
    @if(isset($estadoPanel))
        <input type="hidden" name="estado" value="{{ $estadoPanel }}">
    @endif
    <div class="garita-filter-field garita-filter-field--fecha">
        <label for="garita-fecha">Fecha de producción</label>
        <input id="garita-fecha" type="date" name="fecha" value="{{ $filtros['fecha'] }}" onchange="this.form.elements.viaje.value=''; this.form.requestSubmit()">
    </div>
    <div class="garita-filter-field garita-filter-field--viaje">
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
        <div class="garita-filter-field garita-filter-field--fundo">
            <label for="garita-fundo">Fundo</label>
            <select id="garita-fundo" name="fundo_id" onchange="this.form.elements.viaje.value=''; this.form.requestSubmit()">
                <option value="">Todos mis fundos</option>
                @foreach($fundos as $fundo)
                    <option value="{{ $fundo->id }}" @selected((string) $filtros['fundo_id'] === (string) $fundo->id)>{{ $fundo->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="garita-filter-field garita-filter-field--conductor">
            <label for="garita-conductor">Conductor</label>
            <input id="garita-conductor" name="conductor" type="search" value="{{ $filtros['conductor'] }}" placeholder="Buscar conductor">
        </div>
        <div class="garita-filter-field garita-filter-field--cliente">
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
    .garita-filter-bar { display:grid; align-items:end; gap:.9rem; padding:1.25rem; margin:0 0 1.25rem; background:#fff; border:1px solid #dce5df; border-radius:14px; box-shadow:0 2px 8px rgba(15,23,42,.035); }
    .garita-filter-bar--panel { grid-template-columns:minmax(220px, 1fr) minmax(220px, 1fr) auto; }
    .garita-filter-bar--historial.garita-filter-bar--con-fundo { grid-template-columns:minmax(155px,.78fr) minmax(155px,.78fr) minmax(170px,.85fr) minmax(205px,1.15fr) minmax(205px,1.15fr) auto; grid-template-areas:"fecha viaje fundo conductor cliente acciones"; }
    .garita-filter-bar--historial.garita-filter-bar--sin-fundo { grid-template-columns:minmax(165px,.8fr) minmax(165px,.8fr) minmax(220px,1.15fr) minmax(220px,1.15fr) auto; grid-template-areas:"fecha viaje conductor cliente acciones"; }
    .garita-filter-field { display:flex; min-width:0; flex-direction:column; gap:.4rem; }
    .garita-filter-bar--historial .garita-filter-field--fecha { grid-area:fecha; }
    .garita-filter-bar--historial .garita-filter-field--viaje { grid-area:viaje; }
    .garita-filter-bar--historial .garita-filter-field--fundo { grid-area:fundo; }
    .garita-filter-bar--historial .garita-filter-field--conductor { grid-area:conductor; }
    .garita-filter-bar--historial .garita-filter-field--cliente { grid-area:cliente; }
    .garita-filter-field label { min-height:1rem; font-size:.76rem; line-height:1.25; font-weight:700; color:#334155; }
    .garita-filter-field input,.garita-filter-field select { width:100%; min-width:0; height:2.75rem; padding:.55rem .75rem; border:1px solid #cbd5e1; border-radius:9px; background:#fff; color:#172c20; font:inherit; transition:border-color .15s ease,box-shadow .15s ease,background .15s ease; }
    .garita-filter-field input:focus,.garita-filter-field select:focus { outline:0; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.12); }
    .garita-filter-field select:disabled { background:#f1f5f9; color:#64748b; }
    .garita-filter-hint { font-size:.7rem; line-height:1.2; color:#64748b; }
    .garita-filter-actions { display:flex; align-items:center; justify-content:flex-end; gap:.55rem; flex-wrap:nowrap; }
    .garita-filter-bar--historial .garita-filter-actions { grid-area:actions; }
    .garita-filter-actions .btn { min-width:6.25rem; min-height:2.75rem; display:inline-flex; align-items:center; justify-content:center; padding-inline:1rem; }
    @media(max-width:1480px) {
        .garita-filter-bar--historial.garita-filter-bar--con-fundo { grid-template-columns:repeat(3,minmax(0,1fr)); grid-template-areas:"fecha viaje fundo" "conductor cliente acciones"; }
    }
    @media(max-width:1280px) {
        .garita-filter-bar--historial.garita-filter-bar--sin-fundo { grid-template-columns:repeat(4,minmax(0,1fr)); grid-template-areas:"fecha viaje conductor cliente" ". . acciones acciones"; }
    }
    @media(max-width:820px) {
        .garita-filter-bar--historial.garita-filter-bar--con-fundo { grid-template-columns:repeat(2,minmax(0,1fr)); grid-template-areas:"fecha viaje" "fundo fundo" "conductor cliente" "acciones acciones"; }
        .garita-filter-bar--historial.garita-filter-bar--sin-fundo { grid-template-columns:repeat(2,minmax(0,1fr)); grid-template-areas:"fecha viaje" "conductor cliente" "acciones acciones"; }
        .garita-filter-actions { width:100%; }
    }
    @media(max-width:700px) {
        .garita-filter-bar { padding:1rem; gap:.8rem; }
        .garita-filter-bar--panel { display:none; }
        .garita-filter-bar--historial.garita-filter-bar--con-fundo { grid-template-areas:"fecha viaje" "fundo fundo" "conductor conductor" "cliente cliente" "acciones acciones"; }
        .garita-filter-bar--historial.garita-filter-bar--sin-fundo { grid-template-areas:"fecha viaje" "conductor conductor" "cliente cliente" "acciones acciones"; }
        .garita-filter-actions .btn { flex:1 1 0; min-width:0; }
    }
    @media(max-width:420px) {
        .garita-filter-bar--historial.garita-filter-bar--con-fundo { grid-template-columns:minmax(0,1fr); grid-template-areas:"fecha" "viaje" "fundo" "conductor" "cliente" "acciones"; }
        .garita-filter-bar--historial.garita-filter-bar--sin-fundo { grid-template-columns:minmax(0,1fr); grid-template-areas:"fecha" "viaje" "conductor" "cliente" "acciones"; }
    }
</style>
