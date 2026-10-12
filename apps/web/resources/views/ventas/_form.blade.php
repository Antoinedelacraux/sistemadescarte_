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
        <select name="lote_id" id="lote_id" class="form-control" required data-custom-select="true">
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
        <input
            type="text"
            name="cuartel_manual"
            id="cuartel_manual"
            class="form-control"
            placeholder="Escribe el cuartel (Ej. C-01, Cuartel 4)"
            value="{{ old('cuartel_manual', $venta?->cuartel_manual) }}"
        >
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
            <option value="">-- Seleccionar Motivo --</option>
            <option value="Campo" {{ old('motivo', $venta?->motivo) == 'Campo' ? 'selected' : '' }}>Campo</option>
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
            <option value="">-- Seleccionar Tipo de Descarte --</option>
            <option value="Racimos" {{ old('tipo_descarte', $venta?->tipo_descarte) == 'Racimos' ? 'selected' : '' }}>Racimos</option>
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
        <div class="form-group" style="position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                <label for="cliente" class="form-label" style="margin-bottom: 0;">Cliente / Razón Social</label>
                <span id="cliente_guardado_badge" style="font-size: 0.72rem; color: #16a34a; display: none; font-weight: 600; background: #dcfce7; padding: 2px 8px; border-radius: 9999px;">
                    ✓ Registrado
                </span>
            </div>
            <div class="cliente-input-container" style="position: relative; display: flex; align-items: center;">
                <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Escriba o elija un cliente..." value="{{ old('cliente', $venta?->cliente) }}" autocomplete="off" style="padding-right: 2.25rem;">
                <button type="button" id="btn_toggle_clientes" title="Ver clientes registrados" style="position: absolute; right: 0.5rem; background: none; border: none; cursor: pointer; color: #64748b; padding: 0.25rem; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
            </div>
            {{-- Menú flotante de clientes registrados con búsqueda en tiempo real --}}
            <div id="clientes_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 50; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); max-height: 220px; overflow-y: auto; margin-top: 4px;">
                <div id="clientes_dropdown_list">
                    {{-- Elementos renderizados dinámicamente --}}
                </div>
            </div>
            <small class="form-hint" style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: block;">
                Los clientes se van guardando solos. Puede elegir uno existente o escribir uno nuevo.
            </small>
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
// Almacén de lotes, fundos y cuarteles en memoria para interactividad offline instantánea
var lotesCache = @json($lotes);
var fundosData = @json($fundos);

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

    selectTipo.innerHTML = '<option value="">-- Seleccionar Tipo de Descarte --</option>';
    var opciones = [];
    if (motivo === 'Packing' || motivo === 'Cosecha Nacional') {
        opciones = ['Racimos', 'Granos'];
    } else if (motivo === 'Campo') {
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

function renderLotesEnSelect(lotesList) {
    lotesCache = lotesList;
    var selectLote = document.getElementById('lote_id');
    selectLote.innerHTML = '<option value="">-- Seleccionar Lote --</option>';
    lotesList.forEach(function(l) {
        var opt = document.createElement('option');
        opt.value = l.id;
        opt.textContent = l.nombre;
        selectLote.appendChild(opt);
    });

    if (selectLote.refreshCustomSelect) {
        selectLote.refreshCustomSelect();
    }
}

function cargarLotesPorFundo(fundoId) {
    if (!fundoId) return;

    // 1. Intento inmediato en memoria (100% offline)
    if (typeof fundosData !== 'undefined' && Array.isArray(fundosData)) {
        var fundoEncontrado = fundosData.find(function(f) { return f.id == fundoId; });
        if (fundoEncontrado && fundoEncontrado.lotes) {
            renderLotesEnSelect(fundoEncontrado.lotes);
            return;
        }
    }

    // 2. Si no estuviera en memoria, fallback por red
    fetch('/api/catalogo/lotes?fundo_id=' + encodeURIComponent(fundoId), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        renderLotesEnSelect(data);
    })
    .catch(function(err) {
        console.error('Error cargando lotes:', err);
    });
}

// Catálogo de clientes precargado
var clientesData = @json(($clientes ?? collect())->map(function($c) {
    return ['nombre' => $c->nombre, 'ruc' => $c->ruc];
}));

function inicializarSelectorClientes() {
    var clienteInput = document.getElementById('cliente');
    var rucInput = document.getElementById('ruc');
    var btnToggle = document.getElementById('btn_toggle_clientes');
    var dropdown = document.getElementById('clientes_dropdown');
    var dropdownList = document.getElementById('clientes_dropdown_list');
    var badge = document.getElementById('cliente_guardado_badge');

    if (!clienteInput || !dropdown || !dropdownList) return;

    function renderDropdown(filtro) {
        var query = (filtro || '').trim().toLowerCase();
        dropdownList.innerHTML = '';
        var valorActual = clienteInput.value.trim().toLowerCase();

        var items = clientesData.filter(function(item) {
            if (!query) return true;
            return item.nombre.toLowerCase().includes(query) || (item.ruc && item.ruc.includes(query));
        });

        if (items.length === 0) {
            var empty = document.createElement('div');
            empty.style.padding = '0.75rem 1rem';
            empty.style.fontSize = '0.8125rem';
            empty.style.color = '#64748b';
            empty.textContent = query ? 'Sin coincidencias (se guardará como cliente nuevo)' : 'No hay clientes registrados aún';
            dropdownList.appendChild(empty);
            return;
        }

        items.forEach(function(item) {
            var row = document.createElement('div');
            row.className = 'cliente-option-item';
            row.style.padding = '0.625rem 1rem';
            row.style.cursor = 'pointer';
            row.style.display = 'flex';
            row.style.justifyContent = 'space-between';
            row.style.alignItems = 'center';
            row.style.borderBottom = '1px solid #f1f5f9';
            row.style.transition = 'background-color 0.12s ease';

            var esSeleccionado = (item.nombre.trim().toLowerCase() === valorActual);
            if (esSeleccionado) {
                row.style.backgroundColor = '#f0fdf4';
            }

            row.addEventListener('mouseenter', function() {
                row.style.backgroundColor = '#f0fdf4';
            });
            row.addEventListener('mouseleave', function() {
                row.style.backgroundColor = esSeleccionado ? '#f0fdf4' : 'transparent';
            });

            var nameSpan = document.createElement('span');
            nameSpan.style.fontWeight = esSeleccionado ? '600' : '500';
            nameSpan.style.color = '#0f172a';
            nameSpan.style.fontSize = '0.875rem';
            nameSpan.textContent = item.nombre;
            row.appendChild(nameSpan);

            if (esSeleccionado) {
                var checkSpan = document.createElement('span');
                checkSpan.style.fontSize = '0.8125rem';
                checkSpan.style.color = '#16a34a';
                checkSpan.style.fontWeight = 'bold';
                checkSpan.textContent = '✓';
                row.appendChild(checkSpan);
            }

            row.addEventListener('mousedown', function(e) {
                e.preventDefault();
                seleccionarCliente(item);
            });

            dropdownList.appendChild(row);
        });
    }

    function seleccionarCliente(item) {
        clienteInput.value = item.nombre;
        if (item.ruc && rucInput) {
            rucInput.value = item.ruc;
        }
        cerrarDropdown();
        actualizarBadge();
    }

    function abrirDropdown(mostrarTodos) {
        if (mostrarTodos) {
            renderDropdown('');
        } else {
            renderDropdown(clienteInput.value);
        }
        dropdown.style.display = 'block';
    }

    function cerrarDropdown() {
        dropdown.style.display = 'none';
    }

    function actualizarBadge() {
        if (!badge) return;
        var actual = clienteInput.value.trim().toLowerCase();
        var match = clientesData.find(function(c) {
            return c.nombre.toLowerCase() === actual;
        });
        if (match) {
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }

    clienteInput.addEventListener('focus', function() {
        abrirDropdown(true);
    });

    clienteInput.addEventListener('click', function() {
        abrirDropdown(true);
    });

    clienteInput.addEventListener('input', function() {
        renderDropdown(clienteInput.value);
        dropdown.style.display = 'block';
        actualizarBadge();
    });

    if (btnToggle) {
        btnToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (dropdown.style.display === 'block') {
                cerrarDropdown();
            } else {
                clienteInput.focus();
                abrirDropdown(true);
            }
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#clientes_dropdown') && !e.target.closest('.cliente-input-container')) {
            cerrarDropdown();
        }
    });

    actualizarBadge();

    window.agregarClienteALista = function(nombre, ruc) {
        if (!nombre || !nombre.trim()) return;
        var cleanNombre = nombre.trim();
        var cleanRuc = ruc ? ruc.trim() : null;
        var idx = clientesData.findIndex(function(c) {
            return c.nombre.toLowerCase() === cleanNombre.toLowerCase();
        });
        if (idx >= 0) {
            if (cleanRuc) clientesData[idx].ruc = cleanRuc;
        } else {
            clientesData.unshift({ nombre: cleanNombre, ruc: cleanRuc });
        }
    };

    window.resetClienteState = function() {
        cerrarDropdown();
        if (badge) badge.style.display = 'none';
    };
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    calcularTotal();
    actualizarOpcionesTipoDescarte();
    inicializarSelectorClientes();

    // Inicializar listas desplegables controladas solo en elementos SELECT
    ['fundo_id', 'lote_id', 'motivo', 'tipo_descarte'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.tagName === 'SELECT' && window.initCustomSelect) {
            window.initCustomSelect(el);
        }
    });
});
</script>
