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
