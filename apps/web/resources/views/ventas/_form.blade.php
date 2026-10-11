{{-- Formulario Compartido para Venta de Descarte (Create / Edit) --}}
@php
    $isEdit = isset($venta) && $venta;
@endphp

{{-- 1. UBICACIÓN Y FECHA --}}
<div class="form-section-title">
    Ubicación y Fecha de Producción
</div>

<div class="form-grid-2">
    {{-- Fundo --}}
    <div class="form-group">
        <label for="fundo_id" class="form-label">
            Fundo <span class="required">*</span>
        </label>
        @if($fundos->count() === 1)
            <input type="text" class="form-control" value="{{ $fundos->first()->name }}" readonly style="background: var(--clr-surface-100); font-weight: 600;">
            <input type="hidden" name="fundo_id" id="fundo_id" value="{{ $fundos->first()->id }}">
        @else
            <select name="fundo_id" id="fundo_id" class="form-control" required data-custom-select="true" onchange="cargarLotesPorFundo(this.value)">
                @foreach($fundos as $fundo)
                    <option value="{{ $fundo->id }}" {{ old('fundo_id', $venta?->fundo_id) == $fundo->id ? 'selected' : '' }}>
                        {{ $fundo->name }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Fecha de Producción --}}
    <div class="form-group">
        <label for="fecha_produccion" class="form-label">
            Fecha de Producción <span class="required">*</span>
        </label>
        <input
            type="date"
            id="fecha_produccion"
            name="fecha_produccion"
            class="form-control"
            value="{{ old('fecha_produccion', $isEdit && $venta->fecha_produccion ? $venta->fecha_produccion->format('Y-m-d') : date('Y-m-d')) }}"
            required
        >
    </div>
</div>

<div class="form-grid-2">
    {{-- Lote --}}
    <div class="form-group">
        <label for="lote_id" class="form-label">
            Lote <span class="required">*</span>
        </label>
        <select name="lote_id" id="lote_id" class="form-control" required data-custom-select="true" onchange="actualizarCuarteles(this.value)">
            <option value="">-- Seleccionar Lote --</option>
            @foreach($lotes as $lote)
                <option value="{{ $lote->id }}" {{ old('lote_id', $venta?->lote_id) == $lote->id ? 'selected' : '' }}>
                    {{ $lote->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Cuartel --}}
    <div class="form-group">
        <label for="cuartel_manual" class="form-label">
            Cuartel <span id="cuartel-required-star" class="required" style="display: none;">*</span>
            <small id="cuartel-hint" class="text-muted">(Obligatorio en Cosecha Nacional)</small>
        </label>
        <div class="cuartel-input-group">
            <select id="cuartel_select" class="form-control" data-custom-select="true" onchange="seleccionarCuartel(this.value)">
                <option value="">-- Cuartel de catálogo --</option>
            </select>
            <input
                type="text"
                name="cuartel_manual"
                id="cuartel_manual"
                class="form-control"
                placeholder="O escribe el cuartel"
                value="{{ old('cuartel_manual', $venta?->cuartel_manual) }}"
            >
        </div>
        <input type="hidden" name="cuartel_id" id="cuartel_id" value="{{ old('cuartel_id', $venta?->cuartel_id) }}">
    </div>
</div>

{{-- 2. CLASIFICACIÓN DE DESCARTE --}}
<div class="form-section-title" style="margin-top: 1.5rem;">
    Clasificación del Descarte
</div>

<div class="form-grid-2">
    {{-- Motivo --}}
    <div class="form-group">
        <label for="motivo" class="form-label">
            Motivo <span class="required">*</span>
        </label>
        <select name="motivo" id="motivo" class="form-control" required data-custom-select="true" onchange="actualizarOpcionesTipoDescarte()">
            <option value="Campo" {{ old('motivo', $venta?->motivo ?? 'Campo') == 'Campo' ? 'selected' : '' }}>Campo</option>
            <option value="Packing" {{ old('motivo', $venta?->motivo) == 'Packing' ? 'selected' : '' }}>Packing</option>
            <option value="Cosecha Nacional" {{ old('motivo', $venta?->motivo) == 'Cosecha Nacional' ? 'selected' : '' }}>Cosecha Nacional</option>
        </select>
    </div>

    {{-- Tipo de Descarte --}}
    <div class="form-group">
        <label for="tipo_descarte" class="form-label">
            Tipo de Descarte <span class="required">*</span>
        </label>
        <select name="tipo_descarte" id="tipo_descarte" class="form-control" required data-custom-select="true">
            <option value="Racimos" {{ old('tipo_descarte', $venta?->tipo_descarte ?? 'Racimos') == 'Racimos' ? 'selected' : '' }}>Racimos</option>
            <option value="Racimos con plaga" {{ old('tipo_descarte', $venta?->tipo_descarte) == 'Racimos con plaga' ? 'selected' : '' }}>Racimos con plaga</option>
            <option value="Granos" {{ old('tipo_descarte', $venta?->tipo_descarte) == 'Granos' ? 'selected' : '' }}>Granos</option>
        </select>
    </div>
</div>

{{-- 3. CÁLCULO DE VENTA --}}
<div class="form-section-title" style="margin-top: 1.5rem;">
    Pesaje y Precios
</div>

<div class="form-grid-2">
    {{-- Kilogramos --}}
    <div class="form-group">
        <label for="kilogramos" class="form-label">
            Kilogramos <span class="required">*</span>
        </label>
        <input
            type="number"
            step="0.01"
            min="0.01"
            name="kilogramos"
            id="kilogramos"
            class="form-control"
            placeholder="0.00"
            value="{{ old('kilogramos', $venta?->kilogramos) }}"
            required
            oninput="calcularTotal()"
        >
    </div>

    {{-- Precio --}}
    <div class="form-group">
        <label for="precio" class="form-label">
            Precio por Kg (S/) <span class="required">*</span>
        </label>
        <input
            type="number"
            step="0.01"
            min="0.01"
            name="precio"
            id="precio"
            class="form-control"
            placeholder="0.00"
            value="{{ old('precio', $venta?->precio) }}"
            required
            oninput="calcularTotal()"
        >
    </div>
</div>

{{-- Visualizador de Total Calculado --}}
<div class="total-display-card">
    <div>
        <div class="total-title">Total Estimado de Venta</div>
        <div class="total-hint">Cálculo automático: Kilogramos &times; Precio</div>
    </div>
    <div class="total-amount" id="total-display">
        S/ {{ number_format($venta?->valor_venta ?? 0, 2) }}
    </div>
</div>

{{-- 4. CAMPOS COMPLEMENTARIOS (OPCIONALES) --}}
<details class="optional-details" {{ $isEdit ? 'open' : '' }}>
    <summary>
        Datos de Trazabilidad, Cliente y Transporte (Opcional)
    </summary>

    <div class="form-grid-2" style="margin-top: 1rem;">
        <div class="form-group">
            <label for="cliente" class="form-label">Cliente / Razón Social</label>
            <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Agroexport del Sur SAC" value="{{ old('cliente', $venta?->cliente) }}">
        </div>
        <div class="form-group">
            <label for="ruc" class="form-label">RUC del Cliente</label>
            <input type="text" name="ruc" id="ruc" class="form-control" placeholder="Ej. 20123456789" maxlength="20" value="{{ old('ruc', $venta?->ruc) }}">
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label for="jabas" class="form-label">Cantidad de Jabas</label>
            <input type="number" name="jabas" id="jabas" class="form-control" placeholder="Ej. 50" min="0" value="{{ old('jabas', $venta?->jabas) }}">
        </div>
        <div class="form-group">
            <label for="peso_jaba" class="form-label">Peso Promedio por Jaba (kg)</label>
            <input type="number" step="0.01" name="peso_jaba" id="peso_jaba" class="form-control" placeholder="Ej. 18.50" min="0" value="{{ old('peso_jaba', $venta?->peso_jaba) }}">
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label for="placa" class="form-label">Placa del Vehículo</label>
            <input type="text" name="placa" id="placa" class="form-control" placeholder="Ej. ABC-123" value="{{ old('placa', $venta?->placa) }}">
        </div>
        <div class="form-group">
            <label for="conductor" class="form-label">Nombre del Conductor</label>
            <input type="text" name="conductor" id="conductor" class="form-control" placeholder="Ej. Juan Pérez" value="{{ old('conductor', $venta?->conductor) }}">
        </div>
    </div>

    <div class="form-grid-2">
        <div class="form-group">
            <label for="brevete" class="form-label">Número de Brevete</label>
            <input type="text" name="brevete" id="brevete" class="form-control" placeholder="Ej. Q12345678" value="{{ old('brevete', $venta?->brevete) }}">
        </div>
        <div class="form-group">
            <label for="viaje" class="form-label">Número de Viaje</label>
            <input type="text" name="viaje" id="viaje" class="form-control" placeholder="Ej. Viaje 1" value="{{ old('viaje', $venta?->viaje) }}">
        </div>
    </div>

    <div class="form-group">
        <label for="observacion" class="form-label">Observaciones</label>
        <textarea name="observacion" id="observacion" rows="2" class="form-control" placeholder="Anotaciones adicionales del pesaje o lote...">{{ old('observacion', $venta?->observacion) }}</textarea>
    </div>
</details>

{{-- BOTÓN SUBMIT --}}
<div style="margin-top: 1.75rem;">
    <button type="submit" class="btn-submit-sale" id="btn-submit">
        {{ $isEdit ? 'Guardar Cambios de la Venta' : 'Guardar Venta de Descarte' }}
    </button>
</div>

<script>
// Almacén de lotes y cuarteles en memoria para interactividad rápida
var lotesCache = @json($lotes);

function calcularTotal() {
    var kg = parseFloat(document.getElementById('kilogramos').value) || 0;
    var precio = parseFloat(document.getElementById('precio').value) || 0;
    var total = kg * precio;
    document.getElementById('total-display').textContent = 'S/ ' + total.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function actualizarOpcionesTipoDescarte() {
    var motivo = document.getElementById('motivo').value;
    var selectTipo = document.getElementById('tipo_descarte');
    var valorActual = selectTipo.value;
    var cuartelStar = document.getElementById('cuartel-required-star');
    var cuartelHint = document.getElementById('cuartel-hint');
    var cuartelInput = document.getElementById('cuartel_manual');

    // Regla de negocio: Cuartel obligatorio en Cosecha Nacional
    if (motivo === 'Cosecha Nacional') {
        cuartelStar.style.display = 'inline';
        cuartelHint.textContent = '(Obligatorio)';
        cuartelHint.style.color = 'var(--clr-danger)';
        cuartelInput.setAttribute('required', 'required');
    } else {
        cuartelStar.style.display = 'none';
        cuartelHint.textContent = '(Opcional)';
        cuartelHint.style.color = 'var(--txt-muted)';
        cuartelInput.removeAttribute('required');
    }

    selectTipo.innerHTML = '';
    var opciones = [];
    if (motivo === 'Packing' || motivo === 'Cosecha Nacional') {
        opciones = ['Racimos', 'Granos'];
    } else {
        opciones = ['Racimos', 'Racimos con plaga', 'Granos'];
    }

    opciones.forEach(function(opc) {
        var opt = document.createElement('option');
        opt.value = opc;
        opt.textContent = opc;
        if (opc === valorActual) opt.selected = true;
        selectTipo.appendChild(opt);
    });

    if (selectTipo.refreshCustomSelect) {
        selectTipo.refreshCustomSelect();
    }
}

function actualizarCuarteles(loteId) {
    var selectCuartel = document.getElementById('cuartel_select');
    selectCuartel.innerHTML = '<option value="">-- Cuartel de catálogo --</option>';

    if (!loteId) {
        if (selectCuartel.refreshCustomSelect) selectCuartel.refreshCustomSelect();
        return;
    }

    var lote = lotesCache.find(function(l) { return l.id == loteId; });
    if (lote && lote.cuarteles) {
        lote.cuarteles.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre;
            selectCuartel.appendChild(opt);
        });
    }

    if (selectCuartel.refreshCustomSelect) {
        selectCuartel.refreshCustomSelect();
    }
}

function seleccionarCuartel(cuartelId) {
    var cuartelInput = document.getElementById('cuartel_manual');
    var cuartelHidden = document.getElementById('cuartel_id');
    var selectCuartel = document.getElementById('cuartel_select');

    if (cuartelId) {
        var texto = selectCuartel.options[selectCuartel.selectedIndex].text;
        cuartelInput.value = texto;
        cuartelHidden.value = cuartelId;
    } else {
        cuartelHidden.value = '';
    }
}

function cargarLotesPorFundo(fundoId) {
    if (!fundoId) return;

    fetch('/api/catalogo/lotes?fundo_id=' + encodeURIComponent(fundoId), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        lotesCache = data;
        var selectLote = document.getElementById('lote_id');
        selectLote.innerHTML = '<option value="">-- Seleccionar Lote --</option>';
        data.forEach(function(l) {
            var opt = document.createElement('option');
            opt.value = l.id;
            opt.textContent = l.nombre;
            selectLote.appendChild(opt);
        });

        if (selectLote.refreshCustomSelect) {
            selectLote.refreshCustomSelect();
        }
        actualizarCuarteles('');
    })
    .catch(function(err) {
        console.error('Error cargando lotes:', err);
    });
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    calcularTotal();
    actualizarOpcionesTipoDescarte();
    var loteInicial = document.getElementById('lote_id').value;
    if (loteInicial) {
        actualizarCuarteles(loteInicial);
    }

    // Inicializar listas desplegables controladas solo en elementos SELECT
    ['fundo_id', 'lote_id', 'cuartel_select', 'motivo', 'tipo_descarte'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.tagName === 'SELECT' && window.initCustomSelect) {
            window.initCustomSelect(el);
        }
    });
});
</script>
