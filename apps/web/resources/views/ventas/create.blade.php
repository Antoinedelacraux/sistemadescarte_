@extends('layouts.app')

@section('title', 'Registrar Venta de Descarte')
@section('page-title', 'Registrar Venta de Descarte')

@section('styles')
<style>
    /* Estilos del formulario de venta */
    .form-container {
        max-width: 860px;
        margin: 0 auto;
    }

    .form-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 4vw, 2rem);
        box-shadow: var(--shadow-sm);
    }

    .form-section-title {
        font-size: var(--text-sm);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--clr-surface-200);
        margin-bottom: 1.25rem;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .total-display-card {
        background: linear-gradient(135deg, var(--clr-primary-50) 0%, var(--clr-primary-100) 100%);
        border: 1.5px solid var(--clr-primary-200);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .total-title {
        font-size: var(--text-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--clr-primary-800);
    }

    .total-hint {
        font-size: var(--text-xs);
        color: var(--clr-primary-700);
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
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(22,101,52,0.25);
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
        .total-title {
            font-size: 0.75rem;
        }
    }
</style>
@endsection

@section('content')
<div class="form-container">

    {{-- Encabezado con retorno --}}
    <div class="form-top-bar">
        <div>
            <h1>Registrar Venta de Descarte</h1>
            <p>Ingresa los datos del pesaje y clasificación del descarte</p>
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
        @include('ventas._form', ['venta' => null, 'isEdit' => false])
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var formVenta = document.getElementById('form-venta');
    if (!formVenta) return;

    formVenta.addEventListener('submit', async function(e) {
        e.preventDefault();

        // 1. Obtención y validación de campos obligatorios
        var fundoId = document.getElementById('fundo_id')?.value;
        var fechaProduccion = document.getElementById('fecha_produccion')?.value;
        var loteId = document.getElementById('lote_id')?.value;
        var motivo = document.getElementById('motivo')?.value;
        var tipoDescarte = document.getElementById('tipo_descarte')?.value;
        var kgInput = document.getElementById('kilogramos');
        var precioInput = document.getElementById('precio');
        var cuartelInput = document.getElementById('cuartel_manual');

        var kg = parseFloat(kgInput?.value);
        var precio = parseFloat(precioInput?.value);

        if (!fundoId || !fechaProduccion || !loteId || !motivo || !tipoDescarte) {
            window.FundoOffline.showToast('Por favor completa todos los campos requeridos (*).', 'danger');
            return;
        }

        if (isNaN(kg) || kg <= 0) {
            window.FundoOffline.showToast('Ingresa un peso en kilogramos válido mayor a 0.', 'danger');
            kgInput?.focus();
            return;
        }

        if (isNaN(precio) || precio <= 0) {
            window.FundoOffline.showToast('Ingresa un precio por kilogramo válido mayor a 0.', 'danger');
            precioInput?.focus();
            return;
        }

        if (motivo === 'Cosecha Nacional' && (!cuartelInput?.value || cuartelInput.value.trim() === '')) {
            window.FundoOffline.showToast('El cuartel es obligatorio para el motivo Cosecha Nacional.', 'danger');
            cuartelInput?.focus();
            return;
        }

        var btnSubmit = document.getElementById('btn-submit');
        var originalBtnText = btnSubmit ? btnSubmit.textContent : 'Guardar Venta de Descarte';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando registro...';
        }

        // 2. Construir objeto de datos con UUID v4 permanente
        var saleUuid = window.FundoOffline.generateUUID();
        var formData = new FormData(formVenta);
        var payload = { id: saleUuid };
        formData.forEach(function(val, key) {
            payload[key] = val;
        });

        // Asegurar que campos vacíos no viajen como strings vacíos que rompan validaciones
        if (!payload.cuartel_id) payload.cuartel_id = null;
        if (!payload.cuartel_manual) payload.cuartel_manual = null;
        if (!payload.jabas) payload.jabas = null;
        if (!payload.peso_jaba) payload.peso_jaba = null;
        if (!payload.cliente) payload.cliente = null;
        if (!payload.ruc) payload.ruc = null;
        if (!payload.placa) payload.placa = null;
        if (!payload.conductor) payload.conductor = null;
        if (!payload.brevete) payload.brevete = null;
        if (!payload.viaje) payload.viaje = null;
        if (!payload.observacion) payload.observacion = null;

        function prepararSiguientePesaje() {
            kgInput.value = '';
            precioInput.value = '';
            document.getElementById('total-display').textContent = 'S/ 0.00';
            kgInput.focus();
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = originalBtnText;
            }
        }

        // 3. Caso: Sin conexión a internet (Modo Cuartel / Campo Desconectado)
        if (!navigator.onLine) {
            window.FundoOffline.enqueue(payload);
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
            window.FundoOffline.showToast('✅ Venta guardada LOCALMENTE en tu teléfono. Se sincronizará automáticamente apenas tengas señal.', 'warning', 6000);
            prepararSiguientePesaje();
            return;
        }

        // 4. Caso: Con conexión (Envío directo a servidor con fallback resiliente)
        try {
            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || payload._token;

            var res = await fetch(formVenta.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: JSON.stringify(payload)
            });

            if (res.status === 201 || res.status === 200) {
                if (navigator.vibrate) navigator.vibrate(80);
                window.FundoOffline.showToast('✅ Venta registrada en el servidor con éxito.', 'success', 4000);
                prepararSiguientePesaje();
            } else if (res.status === 422) {
                var errorData = await res.json();
                var msg = 'Errores de validación: ';
                if (errorData.errors) {
                    msg += Object.values(errorData.errors).flat().join(', ');
                } else if (errorData.message) {
                    msg += errorData.message;
                }
                window.FundoOffline.showToast(msg, 'danger', 6000);
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = originalBtnText;
                }
            } else {
                throw new Error('Servidor retornó estado ' + res.status);
            }
        } catch (error) {
            console.warn('[Offline Engine] Fallo de red, encolando localmente:', error);
            window.FundoOffline.enqueue(payload);
            window.FundoOffline.showToast('⚠️ Señal débil. Venta guardada de forma segura en tu teléfono; se subirá automáticamente.', 'warning', 6000);
            prepararSiguientePesaje();
        }
    });
});
</script>
@endsection
