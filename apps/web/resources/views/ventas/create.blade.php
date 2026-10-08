@extends('layouts.app')

@section('title', 'Registrar Venta de Descarte')
@section('page-title', 'Registrar Venta de Descarte')

@section('styles')
<style>
    .form-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .form-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-sm);
        padding: 1.75rem;
    }

    .form-section-title {
        font-size: var(--text-base);
        font-weight: 700;
        color: var(--txt-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.625rem;
        border-bottom: 1px solid var(--brd-base);
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }

    .total-display-card {
        background: linear-gradient(135deg, var(--clr-primary-50), #ecfdf5);
        border: 1.5px solid var(--clr-primary-300);
        border-radius: var(--radius-lg);
        padding: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1.5rem 0;
        box-shadow: var(--shadow-xs);
    }

    .total-title {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--clr-primary-800);
    }

    .total-hint {
        font-size: var(--text-xs);
        color: var(--clr-primary-700);
    }

    .total-amount {
        font-size: 2rem;
        font-weight: 800;
        color: var(--clr-primary-900);
        font-variant-numeric: tabular-nums;
    }

    .optional-details {
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        margin-top: 1.5rem;
        background: var(--clr-surface-50);
    }

    .optional-details summary {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--txt-secondary);
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .optional-details[open] summary {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--brd-base);
    }

    .btn-submit-sale {
        height: 50px;
        font-size: 1.05rem;
        font-weight: 700;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, var(--clr-primary-700), var(--clr-primary-800));
        color: white;
        border: none;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(22,101,52,0.25);
        transition: all var(--transition-base);
    }

    .btn-submit-sale:hover {
        background: linear-gradient(135deg, var(--clr-primary-800), var(--clr-primary-900));
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(22,101,52,0.35);
    }

    @media (max-width: 768px) {
        .form-grid-2, .form-grid-3 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .form-card {
            padding: 1.25rem 1rem;
        }
        .total-display-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="form-container">

    {{-- Encabezado con retorno --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
        <div>
            <h1 style="font-size: var(--text-2xl); font-weight: 700; color: var(--txt-primary);">Registrar Venta de Descarte</h1>
            <p style="font-size: var(--text-sm); color: var(--txt-muted);">Ingresa los datos del pesaje y clasificación del descarte</p>
        </div>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-sm" aria-label="Volver al historial">
            ← Volver al historial
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <span aria-hidden="true">⚠️</span>
            <div>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('ventas.store') }}" method="POST" id="form-venta" class="form-card" novalidate>
        @csrf

        {{-- 1. UBICACIÓN Y FECHA --}}
        <div class="form-section-title">
            <span>📍</span> Ubicación y Fecha de Producción
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
                    <select name="fundo_id" id="fundo_id" class="form-control" required onchange="cargarLotesPorFundo(this.value)">
                        @foreach($fundos as $fundo)
                            <option value="{{ $fundo->id }}" {{ old('fundo_id') == $fundo->id ? 'selected' : '' }}>
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
                    value="{{ old('fecha_produccion', date('Y-m-d')) }}"
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
                <select name="lote_id" id="lote_id" class="form-control" required onchange="actualizarCuarteles(this.value)">
                    <option value="">-- Seleccionar Lote --</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}" {{ old('lote_id') == $lote->id ? 'selected' : '' }}>
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
                <div style="display: flex; gap: 0.5rem;">
                    <select id="cuartel_select" class="form-control" onchange="seleccionarCuartel(this.value)">
                        <option value="">-- Cuartel de catálogo --</option>
                    </select>
                    <input
                        type="text"
                        name="cuartel_manual"
                        id="cuartel_manual"
                        class="form-control"
                        placeholder="O escribe el cuartel"
                        value="{{ old('cuartel_manual') }}"
                    >
                </div>
                <input type="hidden" name="cuartel_id" id="cuartel_id" value="{{ old('cuartel_id') }}">
            </div>
        </div>

        {{-- 2. CLASIFICACIÓN DE DESCARTE --}}
        <div class="form-section-title" style="margin-top: 1.5rem;">
            <span>🏷️</span> Clasificación del Descarte
        </div>

        <div class="form-grid-2">
            {{-- Motivo --}}
            <div class="form-group">
                <label for="motivo" class="form-label">
                    Motivo <span class="required">*</span>
                </label>
                <select name="motivo" id="motivo" class="form-control" required onchange="actualizarOpcionesTipoDescarte()">
                    <option value="Campo" {{ old('motivo') == 'Campo' ? 'selected' : '' }}>Campo</option>
                    <option value="Packing" {{ old('motivo') == 'Packing' ? 'selected' : '' }}>Packing</option>
                    <option value="Cosecha Nacional" {{ old('motivo') == 'Cosecha Nacional' ? 'selected' : '' }}>Cosecha Nacional</option>
                </select>
            </div>

            {{-- Tipo de Descarte --}}
            <div class="form-group">
                <label for="tipo_descarte" class="form-label">
                    Tipo de Descarte <span class="required">*</span>
                </label>
                <select name="tipo_descarte" id="tipo_descarte" class="form-control" required>
                    <option value="Racimos" {{ old('tipo_descarte') == 'Racimos' ? 'selected' : '' }}>Racimos</option>
                    <option value="Racimos con plaga" {{ old('tipo_descarte') == 'Racimos con plaga' ? 'selected' : '' }}>Racimos con plaga</option>
                    <option value="Granos" {{ old('tipo_descarte') == 'Granos' ? 'selected' : '' }}>Granos</option>
                </select>
            </div>
        </div>

        {{-- 3. CÁLCULO DE VENTA --}}
        <div class="form-section-title" style="margin-top: 1.5rem;">
            <span>⚖️</span> Pesaje y Precios
        </div>

        <div class="form-grid-2">
            {{-- Kilogramos --}}
            <div class="form-group">
                <label for="kilogramos" class="form-label">
                    Kilogramos Totales <span class="required">*</span>
                </label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="kilogramos"
                    id="kilogramos"
                    class="form-control"
                    placeholder="0.00"
                    value="{{ old('kilogramos') }}"
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
                    value="{{ old('precio') }}"
                    required
                    oninput="calcularTotal()"
                >
            </div>
        </div>

        {{-- TARJETA DE TOTAL CALCULADO AUTOMÁTICAMENTE --}}
        <div class="total-display-card">
            <div>
                <div class="total-title">VALOR TOTAL DE LA VENTA (AUTOMÁTICO)</div>
                <div class="total-hint">Cálculo exacto: Kilogramos Totales &times; Precio por Kg</div>
            </div>
            <div class="total-amount" id="total-display">
                S/ 0.00
            </div>
        </div>

        {{-- 4. DATOS ADICIONALES (OPCIONALES) --}}
        <details class="optional-details">
            <summary>
                <span>🚚</span> Datos adicionales de transporte y jabas (opcionales)
            </summary>

            <div class="form-grid-2" style="margin-top: 0.75rem;">
                <div class="form-group">
                    <label for="jabas" class="form-label">Cantidad de Jabas</label>
                    <input type="number" min="0" name="jabas" id="jabas" class="form-control" placeholder="Ej. 10" value="{{ old('jabas') }}">
                </div>
                <div class="form-group">
                    <label for="peso_jaba" class="form-label">Peso por Jaba (kg)</label>
                    <input type="number" step="0.01" min="0" name="peso_jaba" id="peso_jaba" class="form-control" placeholder="Ej. 20.5" value="{{ old('peso_jaba') }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label for="placa" class="form-label">Placa del Vehículo</label>
                    <input type="text" name="placa" id="placa" class="form-control" placeholder="Ej. ABC-123" value="{{ old('placa') }}">
                </div>
                <div class="form-group">
                    <label for="conductor" class="form-label">Nombre del Conductor</label>
                    <input type="text" name="conductor" id="conductor" class="form-control" placeholder="Ej. Juan Pérez" value="{{ old('conductor') }}">
                </div>
                <div class="form-group">
                    <label for="brevete" class="form-label">Brevete</label>
                    <input type="text" name="brevete" id="brevete" class="form-control" placeholder="Ej. Q12345678" value="{{ old('brevete') }}">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="ruc" class="form-label">RUC Comprador</label>
                    <input type="text" name="ruc" id="ruc" class="form-control" placeholder="Ej. 20123456789" value="{{ old('ruc') }}">
                </div>
                <div class="form-group">
                    <label for="viaje" class="form-label">Número de Viaje</label>
                    <input type="text" name="viaje" id="viaje" class="form-control" placeholder="Ej. Viaje 1" value="{{ old('viaje') }}">
                </div>
            </div>

            <div class="form-group">
                <label for="observacion" class="form-label">Observaciones</label>
                <textarea name="observacion" id="observacion" rows="2" class="form-control" placeholder="Anotaciones adicionales del pesaje o lote...">{{ old('observacion') }}</textarea>
            </div>
        </details>

        {{-- BOTÓN SUBMIT --}}
        <div style="margin-top: 1.75rem;">
            <button type="submit" class="btn-submit-sale" id="btn-submit">
                <span>💾</span> Guardar Venta de Descarte
            </button>
        </div>
    </form>
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

    // Opciones de tipo según motivo:
    // Campo: Racimos, Racimos con plaga, Granos
    // Packing: Granos, Racimos
    // Cosecha Nacional: Racimos, Racimos con plaga, Granos
    selectTipo.innerHTML = '';
    var opciones = [];
    if (motivo === 'Packing') {
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
}

function actualizarCuarteles(loteId) {
    var selectCuartel = document.getElementById('cuartel_select');
    selectCuartel.innerHTML = '<option value="">-- Cuartel de catálogo --</option>';

    if (!loteId) return;

    var lote = lotesCache.find(function(l) { return l.id == loteId; });
    if (lote && lote.cuarteles) {
        lote.cuarteles.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.nombre;
            selectCuartel.appendChild(opt);
        });
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
});
</script>
@endsection
