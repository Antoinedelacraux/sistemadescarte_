@extends('layouts.app')

@section('title', 'Detalle de Venta — ' . ($venta->fundo?->name ?? 'Fundo'))
@section('page-title', 'Detalle del Pesaje')

@section('styles')
<style>
    .detail-container {
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 2.5rem;
    }

    .detail-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .detail-heading h1 {
        font-size: clamp(1.25rem, 4vw, 1.625rem);
        font-weight: 800;
        color: var(--txt-primary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-heading p {
        font-size: var(--text-xs);
        color: var(--txt-muted);
        margin-top: 0.25rem;
    }

    .detail-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Tarjetas de sección */
    .detail-card {
        background: white;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 4vw, 1.75rem);
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .detail-card-title {
        font-size: var(--text-sm);
        font-weight: 700;
        color: var(--txt-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.625rem;
        border-bottom: 1px solid var(--clr-surface-100);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* Grid de datos (Label + Valor) */
    .detail-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem 1.5rem;
    }

    .detail-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem 1.5rem;
    }

    @media (max-width: 640px) {
        .detail-grid-2, .detail-grid-3 {
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }
        .detail-top-bar {
            flex-direction: column;
            align-items: stretch;
        }
        .detail-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .detail-actions .btn {
            justify-content: center;
        }
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-label {
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--txt-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .detail-value {
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--txt-primary);
        word-break: break-word;
    }

    /* Tarjeta destacada de valor económico */
    .highlight-money-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1.5px solid #86efac;
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 4vw, 1.75rem);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        flex-wrap: wrap;
    }

    .money-stat {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .money-stat-label {
        font-size: var(--text-xs);
        font-weight: 700;
        color: #166534;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .money-stat-value {
        font-size: clamp(1.25rem, 4vw, 1.625rem);
        font-weight: 800;
        color: #14532d;
        font-family: monospace;
    }

    .money-total-pill {
        background: #14532d;
        color: #f0fdf4;
        padding: 0.875rem 1.5rem;
        border-radius: var(--radius-lg);
        text-align: right;
    }

    .money-total-label {
        font-size: 0.6875rem;
        font-weight: 700;
        color: #86efac;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .money-total-value {
        font-size: clamp(1.5rem, 5vw, 2rem);
        font-weight: 900;
        line-height: 1.1;
        font-family: monospace;
        color: #ffffff;
    }

    @media (max-width: 640px) {
        .highlight-money-card {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }
        .money-total-pill {
            text-align: center;
            padding: 0.75rem 1rem;
        }
    }

    /* Badges de motivo */
    .motivo-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.625rem;
        border-radius: var(--radius-full);
        font-size: var(--text-xs);
        font-weight: 700;
        letter-spacing: 0.02em;
        width: fit-content;
    }
    .motivo-pill-amber { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .motivo-pill-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .motivo-pill-blue  { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
</style>
@endsection

@section('content')
<div class="detail-container">

    {{-- BARRA SUPERIOR DE NAVEGACIÓN Y ACCIÓN --}}
    <div class="detail-top-bar">
        <div class="detail-heading">
            <h1>
                <span aria-hidden="true">📋</span> Ficha de Pesaje de Descarte
            </h1>
            <p>
                {{ $venta->fundo?->name ?? 'Fundo' }} &bull; Producción del {{ $venta->fecha_produccion->format('d/m/Y') }}
            </p>
        </div>
        <div class="detail-actions">
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-sm">
                &larr; Volver al Historial
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
                <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-primary btn-sm">
                    ✏️ Editar Registro
                </a>
            @endif
        </div>
    </div>

    {{-- 1. VALORIZACIÓN ECONÓMICA DESTACADA --}}
    <div class="highlight-money-card">
        <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
            <div class="money-stat">
                <span class="money-stat-label">Kilogramos Totales</span>
                <span class="money-stat-value">{{ number_format($venta->kilogramos, 2) }} kg</span>
            </div>
            <div class="money-stat">
                <span class="money-stat-label">Precio por Kg</span>
                <span class="money-stat-value">S/ {{ number_format($venta->precio, 2) }}</span>
            </div>
        </div>
        <div class="money-total-pill">
            <div class="money-total-label">Total Liquidado</div>
            <div class="money-total-value">S/ {{ number_format($venta->valor_venta, 2) }}</div>
        </div>
    </div>

    {{-- 2. ORIGEN Y CLASIFICACIÓN DEL DESCARTE --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span aria-hidden="true">🏷️</span> Clasificación y Ubicación en Fundo
        </div>
        <div class="detail-grid-3">
            <div class="detail-item">
                <span class="detail-label">Fundo</span>
                <span class="detail-value">{{ $venta->fundo?->name ?? 'No especificado' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Fecha de Producción</span>
                <span class="detail-value">{{ $venta->fecha_produccion->format('d/m/Y') }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Motivo de Venta</span>
                <div>
                    @php
                        $bClass = $venta->motivo === 'Cosecha Nacional' ? 'amber' : ($venta->motivo === 'Packing' ? 'blue' : 'green');
                    @endphp
                    <span class="motivo-pill motivo-pill-{{ $bClass }}">
                        {{ $venta->motivo }}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tipo de Descarte</span>
                <span class="detail-value" style="color: var(--clr-primary-800); font-size: 0.9375rem;">
                    {{ $venta->tipo_descarte }}
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Lote</span>
                <span class="detail-value">{{ $venta->lote?->nombre ?? 'Sin lote especificado' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Cuartel</span>
                <span class="detail-value">{{ $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? '—') }}</span>
            </div>
        </div>
    </div>

    {{-- 3. DATOS COMERCIALES (CLIENTE Y COMPRADOR) --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span aria-hidden="true">👥</span> Datos del Comprador
        </div>
        <div class="detail-grid-2">
            <div class="detail-item">
                <span class="detail-label">Cliente / Razón Social</span>
                <span class="detail-value" style="font-size: 1rem;">
                    {{ $venta->cliente ?: ($venta->ruc ? 'RUC: ' . $venta->ruc : 'Venta General / Sin registrar') }}
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">RUC Comprador</span>
                <span class="detail-value" style="font-family: monospace;">
                    {{ $venta->ruc ?? '—' }}
                </span>
            </div>
        </div>
    </div>

    {{-- 4. DESPACHO, TRANSPORTE Y JABAS --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span aria-hidden="true">🚚</span> Despacho, Transporte y Jabas
        </div>
        <div class="detail-grid-3">
            <div class="detail-item">
                <span class="detail-label">Cantidad de Jabas</span>
                <span class="detail-value">{{ $venta->jabas ? $venta->jabas . ' jabas' : '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Peso Promedio por Jaba</span>
                <span class="detail-value">{{ $venta->peso_jaba ? number_format($venta->peso_jaba, 2) . ' kg' : '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Placa del Vehículo</span>
                <span class="detail-value" style="font-family: monospace;">{{ $venta->placa ?? '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Nombre del Conductor</span>
                <span class="detail-value">{{ $venta->conductor ?? '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Brevete</span>
                <span class="detail-value">{{ $venta->brevete ?? '—' }}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Número de Viaje</span>
                <span class="detail-value">{{ $venta->viaje ?? '—' }}</span>
            </div>
        </div>
    </div>

    {{-- 5. OBSERVACIONES Y AUDITORÍA --}}
    <div class="detail-card">
        <div class="detail-card-title">
            <span aria-hidden="true">📝</span> Observaciones y Registro
        </div>
        <div style="margin-bottom: 1.25rem;">
            <span class="detail-label">Observaciones del Pesaje</span>
            <div style="margin-top: 0.35rem; font-size: var(--text-sm); color: var(--txt-secondary); background: #f8fafc; padding: 0.75rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--brd-base);">
                {{ $venta->observacion ?: 'Sin observaciones adicionales registradas.' }}
            </div>
        </div>
        <div class="detail-grid-2" style="border-top: 1px solid var(--clr-surface-100); padding-top: 0.875rem;">
            <div class="detail-item">
                <span class="detail-label">Registrado por</span>
                <span class="detail-value" style="font-size: var(--text-xs); color: var(--txt-muted);">
                    {{ $venta->creator?->name ?? 'Sistema' }} &bull; {{ $venta->created_at?->format('d/m/Y H:i') ?? '—' }}
                </span>
            </div>
            @if($venta->updated_by)
            <div class="detail-item">
                <span class="detail-label">Última Modificación</span>
                <span class="detail-value" style="font-size: var(--text-xs); color: var(--txt-muted);">
                    {{ $venta->updater?->name ?? 'Usuario' }} &bull; {{ $venta->updated_at?->format('d/m/Y H:i') ?? '—' }}
                </span>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
