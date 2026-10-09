@extends('layouts.app')

@section('title', 'Editar Venta de Descarte')
@section('page-title', 'Editar Venta de Descarte')

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

    .audit-box {
        background: var(--clr-surface-50);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        padding: 0.875rem 1rem;
        font-size: var(--text-xs);
        color: var(--txt-muted);
        margin-bottom: 1.25rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .form-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        gap: 0.75rem;
    }

    .form-top-bar h1 {
        font-size: clamp(1.2rem, 4.5vw, 1.5rem);
        font-weight: 700;
        color: var(--txt-primary);
        line-height: 1.2;
    }

    .form-top-bar p {
        font-size: clamp(0.75rem, 2.5vw, 0.8125rem);
        color: var(--txt-muted);
        margin-top: 0.125rem;
    }

    .cuartel-input-group {
        display: flex;
        gap: 0.5rem;
    }

    .total-amount {
        font-size: clamp(1.4rem, 6vw, 2rem);
        font-weight: 800;
        color: var(--clr-primary-900);
        font-variant-numeric: tabular-nums;
    }

    .btn-submit-sale {
        height: 48px;
        font-size: clamp(0.875rem, 3vw, 1.05rem);
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
    }

    @media (max-width: 768px) {
        .form-grid-2, .form-grid-3 {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        .form-card {
            padding: 1rem 0.875rem;
            border-radius: var(--radius-lg);
        }
        .form-top-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.625rem;
        }
        .form-top-bar .btn {
            width: 100%;
            justify-content: center;
        }
        .cuartel-input-group {
            flex-direction: column;
            gap: 0.5rem;
        }
        .total-display-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.375rem;
            padding: 1rem 0.875rem;
        }
        .audit-box {
            flex-direction: column;
            gap: 0.375rem;
        }
    }
</style>
@endsection

@section('content')
<div class="form-container">

    {{-- Encabezado con retorno --}}
    <div class="form-top-bar">
        <div>
            <h1>Editar Venta de Descarte</h1>
            <p>Modifica los datos del pesaje y guarda los cambios con auditoría</p>
        </div>
        <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-sm" aria-label="Volver al historial">
            ← Volver al historial
        </a>
    </div>

    {{-- Cuadro de auditoría --}}
    <div class="audit-box">
        <div>
            <strong>Creado por:</strong> {{ $venta->creator?->name ?? 'Desconocido' }} &bull; {{ $venta->created_at->format('d/m/Y H:i') }}
        </div>
        @if($venta->updated_by)
            <div>
                <strong>Última modificación:</strong> {{ $venta->updater?->name ?? 'Desconocido' }} &bull; {{ $venta->updated_at->format('d/m/Y H:i') }}
            </div>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <span aria-hidden="true">⚠️</span>
            <div>
                <strong>Corrige los siguientes errores:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('ventas.update', $venta) }}" method="POST" id="form-venta" class="form-card" novalidate>
        @csrf
        @method('PUT')

        {{-- 1. UBICACIÓN Y FECHA --}}
        <div class="form-section-title">
            Ubicación y Fecha de Producción
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="fundo_id" class="form-label">Fundo <span class="required">*</span></label>
                <select name="fundo_id" id="fundo_id" class="form-control" required onchange="cargarLotesPorFundo(this.value)">
                    @foreach($fundos as $fundo)
                        <option value="{{ $fundo->id }}" {{ old('fundo_id', $venta->fundo_id) == $fundo->id ? 'selected' : '' }}>
                            {{ $fundo->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_produccion" class="form-label">Fecha de Producción <span class="required">*</span></label>
                <input
                    type="date"
                    id="fecha_produccion"
                    name="fecha_produccion"
                    class="form-control"
                    value="{{ old('fecha_produccion', $venta->fecha_produccion->format('Y-m-d')) }}"
                    required
                >
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="lote_id" class="form-label">Lote <span class="required">*</span></label>
                <select name="lote_id" id="lote_id" class="form-control" required onchange="actualizarCuarteles(this.value)">
                    <option value="">-- Seleccionar Lote --</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->id }}" {{ old('lote_id', $venta->lote_id) == $lote->id ? 'selected' : '' }}>
                            {{ $lote->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="cuartel_manual" class="form-label">
                    Cuartel <span id="cuartel-required-star" class="required" style="display: none;">*</span>
                </label>
                <div class="cuartel-input-group">
                    <select id="cuartel_select" class="form-control" onchange="seleccionarCuartel(this.value)">
                        <option value="">-- Cuartel de catálogo --</option>
                    </select>
                    <input
                        type="text"
                        name="cuartel_manual"
                        id="cuartel_manual"
                        class="form-control"
                        placeholder="O escribe el cuartel"
                        value="{{ old('cuartel_manual', $venta->cuartel_manual) }}"
                    >
                </div>
                <input type="hidden" name="cuartel_id" id="cuartel_id" value="{{ old('cuartel_id', $venta->cuartel_id) }}">
            </div>
        </div>

        {{-- 2. CLASIFICACIÓN DE DESCARTE --}}
        <div class="form-section-title" style="margin-top: 1.5rem;">
            Clasificación del Descarte
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="motivo" class="form-label">Motivo <span class="required">*</span></label>
                <select name="motivo" id="motivo" class="form-control" required onchange="actualizarOpcionesTipoDescarte()">
                    <option value="Campo" {{ old('motivo', $venta->motivo) == 'Campo' ? 'selected' : '' }}>Campo</option>
                    <option value="Packing" {{ old('motivo', $venta->motivo) == 'Packing' ? 'selected' : '' }}>Packing</option>
                    <option value="Cosecha Nacional" {{ old('motivo', $venta->motivo) == 'Cosecha Nacional' ? 'selected' : '' }}>Cosecha Nacional</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tipo_descarte" class="form-label">Tipo de Descarte <span class="required">*</span></label>
                <select name="tipo_descarte" id="tipo_descarte" class="form-control" required>
                    <option value="Racimos" {{ old('tipo_descarte', $venta->tipo_descarte) == 'Racimos' ? 'selected' : '' }}>Racimos</option>
                    <option value="Racimos con plaga" {{ old('tipo_descarte', $venta->tipo_descarte) == 'Racimos con plaga' ? 'selected' : '' }}>Racimos con plaga</option>
                    <option value="Granos" {{ old('tipo_descarte', $venta->tipo_descarte) == 'Granos' ? 'selected' : '' }}>Granos</option>
                </select>
            </div>
        </div>

        {{-- 3. CÁLCULO DE VENTA --}}
        <div class="form-section-title" style="margin-top: 1.5rem;">
            Pesaje y Precios
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="kilogramos" class="form-label">Kilogramos Totales <span class="required">*</span></label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="kilogramos"
                    id="kilogramos"
                    class="form-control"
                    value="{{ old('kilogramos', $venta->kilogramos) }}"
                    required
                    oninput="calcularTotal()"
                >
            </div>

            <div class="form-group">
                <label for="precio" class="form-label">Precio por Kg (S/) <span class="required">*</span></label>
                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="precio"
                    id="precio"
                    class="form-control"
                    value="{{ old('precio', $venta->precio) }}"
                    required
                    oninput="calcularTotal()"
                >
            </div>
        </div>

        {{-- TARJETA TOTAL --}}
        <div class="total-display-card">
            <div>
                <div class="total-title">VALOR TOTAL DE LA VENTA (AUTOMÁTICO)</div>
                <div class="total-hint">Cálculo exacto: Kilogramos Totales &times; Precio por Kg</div>
            </div>
            <div class="total-amount" id="total-display">
                S/ {{ number_format($venta->valor_venta, 2) }}
            </div>
        </div>

        {{-- 4. DATOS ADICIONALES --}}
        <details class="optional-details" open>
            <summary>
                Datos adicionales de transporte y jabas
            </summary>

            <div class="form-grid-2" style="margin-top: 0.75rem;">
                <div class="form-group">
                    <label for="jabas" class="form-label">Cantidad de Jabas</label>
                    <input type="number" min="0" name="jabas" id="jabas" class="form-control" value="{{ old('jabas', $venta->jabas) }}">
                </div>
                <div class="form-group">
                    <label for="peso_jaba" class="form-label">Peso por Jaba (kg)</label>
                    <input type="number" step="0.01" min="0" name="peso_jaba" id="peso_jaba" class="form-control" value="{{ old('peso_jaba', $venta->peso_jaba) }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label for="placa" class="form-label">Placa</label>
                    <input type="text" name="placa" id="placa" class="form-control" value="{{ old('placa', $venta->placa) }}">
                </div>
                <div class="form-group">
                    <label for="conductor" class="form-label">Conductor</label>
                    <input type="text" name="conductor" id="conductor" class="form-control" value="{{ old('conductor', $venta->conductor) }}">
                </div>
                <div class="form-group">
                    <label for="brevete" class="form-label">Brevete</label>
                    <input type="text" name="brevete" id="brevete" class="form-control" value="{{ old('brevete', $venta->brevete) }}">
                </div>
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label for="cliente" class="form-label">Cliente / Razón Social</label>
                    <input type="text" name="cliente" id="cliente" class="form-control" placeholder="Ej. Frutas del Norte SAC" value="{{ old('cliente', $venta->cliente) }}">
                </div>
                <div class="form-group">
                    <label for="ruc" class="form-label">RUC Comprador</label>
                    <input type="text" name="ruc" id="ruc" class="form-control" value="{{ old('ruc', $venta->ruc) }}">
                </div>
                <div class="form-group">
                    <label for="viaje" class="form-label">Número de Viaje</label>
                    <input type="text" name="viaje" id="viaje" class="form-control" value="{{ old('viaje', $venta->viaje) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="observacion" class="form-label">Observaciones</label>
                <textarea name="observacion" id="observacion" rows="2" class="form-control">{{ old('observacion', $venta->observacion) }}</textarea>
            </div>
        </details>

        <div style="margin-top: 1.75rem;">
            <button type="submit" class="btn-submit-sale" id="btn-submit">
                Actualizar Registro de Venta
            </button>
        </div>
    </form>
</div>

<script>
var lotesCache = @json($lotes);
var tipoActual = @json(old('tipo_descarte', $venta->tipo_descarte));

function calcularTotal() {
    var kg = parseFloat(document.getElementById('kilogramos').value) || 0;
    var precio = parseFloat(document.getElementById('precio').value) || 0;
    var total = kg * precio;
    document.getElementById('total-display').textContent = 'S/ ' + total.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function actualizarOpcionesTipoDescarte() {
    var motivo = document.getElementById('motivo').value;
    var cuartelStar = document.getElementById('cuartel-required-star');
    var selectTipo = document.getElementById('tipo_descarte');
    var valorActual = selectTipo.value || tipoActual;

    if (motivo === 'Cosecha Nacional') {
        cuartelStar.style.display = 'inline';
    } else {
        cuartelStar.style.display = 'none';
    }

    // Regla de negocio:
    // Cosecha Nacional: Racimos, Granos (solo dos tipos)
    // Packing: Racimos, Granos
    // Campo: Racimos, Racimos con plaga, Granos
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
        cuartelInput.value = selectCuartel.options[selectCuartel.selectedIndex].text;
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

document.addEventListener('DOMContentLoaded', function() {
    actualizarOpcionesTipoDescarte();
    var loteInicial = document.getElementById('lote_id').value;
    if (loteInicial) {
        actualizarCuarteles(loteInicial);
    }
});
</script>
@endsection
