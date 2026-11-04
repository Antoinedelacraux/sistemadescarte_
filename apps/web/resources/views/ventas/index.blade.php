@extends('layouts.app')

@section('title', 'Historial de Ventas de Descarte')
@section('page-title', 'Historial de Ventas de Descarte')

@section('styles')
<style>
    .filter-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow-xs);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.875rem;
        align-items: flex-end;
    }

    .filter-date-fields,
    .filter-advanced-fields {
        display: contents;
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }

    .filter-more-toggle {
        display: none;
    }

    .badge-motivo {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.5rem;
        border-radius: var(--radius-full);
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .badge-campo    { background: #dcfce7; color: #166534; }
    .badge-packing  { background: #e0f2fe; color: #0369a1; }
    .badge-cosecha  { background: #fef3c7; color: #92400e; }

    .badge-tipo {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        background: var(--clr-surface-100);
        color: var(--txt-secondary);
    }

    .monto-total {
        font-weight: 700;
        color: var(--clr-primary-900);
        font-variant-numeric: tabular-nums;
    }

    .table-container {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xs);
        overflow: hidden;
    }

    .table-header-bar {
        padding: 0.875rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--brd-base);
        flex-wrap: wrap;
        gap: 0.75rem;
        background: var(--clr-surface-0);
    }

    .table-header-summary {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: var(--text-sm);
        font-weight: 600;
        color: var(--txt-primary);
        flex-wrap: wrap;
    }

    .table-header-summary .summary-count {
        color: var(--clr-primary-700);
        font-weight: 700;
    }

    .table-header-summary .summary-range {
        font-size: var(--text-xs);
        color: var(--txt-muted);
        font-weight: 500;
        margin-left: 0.25rem;
    }

    .table-mini-pagination {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .mini-pag-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.775rem;
        font-weight: 600;
        color: var(--txt-secondary);
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all 0.15s ease;
        line-height: 1;
        cursor: pointer;
        user-select: none;
    }

    .mini-pag-btn:hover:not(.is-disabled) {
        color: #166534;
        background: #f0fdf4;
        border-color: #86efac;
        box-shadow: 0 1px 3px rgba(22, 101, 52, 0.12);
    }

    .mini-pag-btn:active:not(.is-disabled) {
        transform: translateY(1px);
    }

    .mini-pag-btn.is-disabled {
        opacity: 0.45;
        cursor: not-allowed;
        background: var(--clr-surface-50);
        color: var(--txt-muted);
        border-color: var(--brd-base);
    }

    .mini-pag-numbers {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .mini-pag-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 0.35rem;
        font-size: 0.775rem;
        font-weight: 600;
        color: var(--txt-secondary);
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .mini-pag-num:hover:not(.is-active) {
        color: #166534;
        background: #f0fdf4;
        border-color: #86efac;
    }

    .mini-pag-num.is-active {
        background: #166534;
        color: #ffffff;
        border-color: #166534;
        font-weight: 700;
    }

    .mini-pag-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 28px;
        font-size: 0.8125rem;
        color: var(--txt-muted);
        user-select: none;
    }

    .mini-pag-mobile-info {
        display: none;
        font-size: var(--text-xs);
        color: var(--txt-secondary);
        white-space: nowrap;
    }

    .sales-table {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--text-sm);
    }

    .sales-table th {
        background: var(--clr-surface-50);
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        border-bottom: 1px solid var(--brd-base);
        text-align: left;
        white-space: nowrap;
    }

    .sales-table th.text-right,
    .sales-table td.text-right {
        text-align: right;
    }

    .sales-table td {
        padding: 0.8125rem 1rem;
        border-bottom: 1px solid var(--clr-surface-100);
        vertical-align: middle;
        white-space: nowrap;
    }

    .sales-table tbody tr:hover td {
        background: var(--clr-primary-50);
    }

    .pagination-wrapper {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--clr-surface-50);
    }

    .index-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .index-top-bar h1 {
        font-size: clamp(1.2rem, 4.5vw, 1.5rem);
        font-weight: 700;
        color: var(--txt-primary);
        line-height: 1.2;
    }

    .index-top-bar p {
        font-size: clamp(0.75rem, 2.5vw, 0.8125rem);
        color: var(--txt-muted);
        margin-top: 0.125rem;
    }

    .index-actions-group {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .export-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        background: var(--clr-surface-0);
    }
    .export-icon-btn {
        display: inline-flex;
        width: 2.625rem;
        height: 2.625rem;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: var(--radius-md);
        transition: background var(--transition-fast), border-color var(--transition-fast), transform var(--transition-fast);
    }
    .export-icon-btn:hover { transform: translateY(-1px); }
    .export-icon-btn--excel { color:#166534; background:#f0fdf4; border-color:#bbf7d0; }
    .export-icon-btn--excel:hover { background:#dcfce7; border-color:#86efac; }

    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .filter-card--registrador {
            padding: 0.75rem;
        }
        .filter-card--registrador .filter-grid {
            display: block;
        }
        .filter-card--registrador .filter-date-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.625rem;
        }
        .filter-card--registrador .filter-date-fields .form-group,
        .filter-card--registrador .filter-date-fields .form-control {
            min-width: 0;
            width: 100%;
        }
        .filter-card--registrador .filter-advanced-fields {
            display: none;
            grid-template-columns: 1fr;
            gap: 0.625rem;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            border-top: 1px solid var(--brd-base);
        }
        .filter-card--registrador .filter-advanced-fields.is-open {
            display: grid;
        }
        .filter-card--registrador .filter-more-toggle {
            display: inline-flex;
            width: 100%;
            margin-top: 0.75rem;
            min-height: 2.375rem;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.45rem 0.65rem;
            border: 1px solid var(--brd-base);
            border-radius: var(--radius-md);
            background: var(--clr-surface-0);
            color: var(--txt-secondary);
            font: inherit;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
        }
        .filter-card--registrador .filter-more-toggle svg {
            transition: transform var(--transition-fast);
        }
        .filter-card--registrador .filter-more-toggle.is-open svg,
        .filter-card--registrador .filter-more-toggle[aria-expanded="true"] svg {
            transform: rotate(180deg);
        }
        .filter-card--registrador .filter-actions {
            display: flex;
            min-width: 0;
            margin-top: 0.75rem;
        }
        .filter-card--registrador .filter-actions .btn {
            min-width: 0;
            justify-content: center;
        }
        .index-top-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .index-actions-group {
            display: flex;
            width: 100%;
            gap: 0.5rem;
        }
        .export-actions { flex:0 0 auto; }
        .index-actions-group .btn {
            flex:1 1 auto;
            justify-content: center;
            font-size: clamp(0.72rem, 2.6vw, 0.8125rem);
            padding: 0.5rem 0.25rem;
            text-align: center;
        }
        .sales-table th {
            padding: 0.5rem 0.5rem;
            font-size: 0.625rem;
        }
        .sales-table td {
            padding: 0.5rem 0.5rem;
            font-size: 0.75rem;
        }
        .badge-motivo {
            font-size: 0.5625rem;
            padding: 0.125rem 0.35rem;
        }
        .badge-tipo {
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
        }
        .btn-row-action {
            padding: 0.25rem 0.45rem;
            font-size: 0.6875rem;
            gap: 0.2rem;
        }
        .modal-money-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.625rem;
            margin-bottom: 1rem;
        }
        .modal-stat-box {
            padding: 0.75rem 0.875rem;
        }
        .modal-stat-total {
            grid-column: 1 / -1;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .modal-data-grid {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        .modal-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }
        .modal-footer > div {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .modal-footer .btn {
            justify-content: center;
        }
        .table-header-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.625rem;
            padding: 0.75rem 0.875rem;
        }
        .table-mini-pagination {
            justify-content: space-between;
            width: 100%;
        }
        .mini-pag-numbers {
            display: none;
        }
        .mini-pag-mobile-info {
            display: inline-block;
        }
        .mini-pag-btn {
            padding: 0.45rem 0.75rem;
            font-size: 0.8125rem;
        }
        .row-actions-btn-group {
            gap: 0.45rem;
        }
        .btn-action-icon {
            width: 38px;
            height: 38px;
        }
        .btn-action-icon svg {
            width: 17px;
            height: 17px;
        }
    }

    /* Acciones en fila de tabla - Iconos Minimalistas */
    .row-actions-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        position: relative;
    }

    .row-actions-overflow {
        display: inline-flex;
    }

    .btn-action-more {
        background: var(--clr-surface-0);
        color: #475569;
        border-color: #cbd5e1;
    }

    .btn-action-more:hover,
    .btn-action-more[aria-expanded="true"] {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
    }

    .row-actions-menu {
        position: fixed;
        z-index: 1200;
        min-width: 205px;
        padding: 0.35rem;
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.18);
        text-align: left;
    }

    .row-actions-menu[hidden] {
        display: none !important;
    }

    .row-actions-menu form {
        display: block;
        margin: 0;
    }

    .row-action-menu-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        width: 100%;
        min-height: 40px;
        padding: 0.55rem 0.65rem;
        border: 0;
        border-radius: calc(var(--radius-md) - 2px);
        background: transparent;
        color: var(--txt-primary);
        font: inherit;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .row-action-menu-item:hover,
    .row-action-menu-item:focus-visible {
        background: #f1f5f9;
        outline: none;
    }

    .row-action-menu-item svg {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
    }

    .row-action-menu-item.is-warning {
        color: #9a3412;
    }

    .row-action-menu-item.is-danger {
        color: #b91c1c;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all var(--transition-fast);
        padding: 0;
        background: transparent;
        color: var(--txt-secondary);
        flex-shrink: 0;
    }

    .btn-action-icon:hover {
        transform: translateY(-1px);
    }

    .btn-action-icon svg {
        width: 15px;
        height: 15px;
        stroke-width: 2.2;
    }

    .btn-action-view {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }
    .btn-action-view:hover {
        background: #dcfce7;
        color: #14532d;
        border-color: #86efac;
        box-shadow: 0 1px 3px rgba(22, 101, 52, 0.15);
    }

    .btn-action-edit {
        background: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }
    .btn-action-edit:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
    }

    .btn-action-reactivar {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .btn-action-reactivar:hover {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
        box-shadow: 0 1px 3px rgba(29, 78, 216, 0.15);
    }

    .badge-garita {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.15rem 0.45rem;
        border-radius: var(--radius-sm);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }
    .badge-garita-pendiente {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-garita-validado {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
    .badge-garita-borrador {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .btn-action-garita-send {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }
    .btn-action-garita-send:hover {
        background: #fde68a;
        color: #92400e;
        border-color: #fcd34d;
        box-shadow: 0 1px 3px rgba(180, 83, 9, 0.2);
    }
    .badge-anulado {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .row-anulado {
        opacity: 0.68;
        background: #fcfcfc;
    }
    .row-anulado td.monto-total {
        text-decoration: line-through;
        color: var(--txt-muted);
    }

    /* Modal de Inspección Rápida de Venta */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 31, 20, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-backdrop.is-open {
        display: flex;
    }

    .modal-sheet {
        background: #ffffff;
        border-radius: var(--radius-xl);
        max-width: 680px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--brd-base);
        animation: modalScaleIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes modalScaleIn {
        from { opacity: 0; transform: scale(0.96) translateY(6px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        background: var(--clr-surface-50);
    }

    .modal-title {
        font-size: clamp(1.05rem, 3.5vw, 1.25rem);
        font-weight: 700;
        color: var(--txt-primary);
        margin: 0;
        line-height: 1.2;
    }

    .modal-subtitle {
        font-size: 0.75rem;
        color: var(--txt-muted);
        margin-top: 0.2rem;
    }

    .modal-close-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
        background: white;
        color: var(--txt-muted);
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
        transition: all var(--transition-fast);
        flex-shrink: 0;
    }

    .modal-close-btn:hover {
        background: var(--clr-surface-100);
        color: var(--txt-primary);
    }

    .modal-body {
        padding: 1.25rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-money-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.875rem;
        margin-bottom: 1.25rem;
    }

    .modal-stat-box {
        border-radius: var(--radius-lg);
        padding: 0.875rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 0.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .modal-stat-kilos {
        background: linear-gradient(145deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1.5px solid #86efac;
    }

    .modal-stat-precio {
        background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1.5px solid #e2e8f0;
    }

    .modal-stat-label {
        font-size: 0.625rem;
        font-weight: 700;
        color: #166534;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .modal-stat-precio .modal-stat-label {
        color: var(--txt-muted);
    }

    .modal-stat-val {
        font-size: clamp(1.05rem, 2.2vw, 1.25rem);
        font-weight: 800;
        color: #14532d;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
        white-space: nowrap;
        line-height: 1.2;
    }

    .modal-stat-precio .modal-stat-val {
        color: var(--txt-primary);
    }

    .modal-stat-total {
        background: linear-gradient(135deg, #14532d 0%, #166534 100%);
        border: 1px solid #14532d;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(20, 83, 45, 0.15);
    }

    .modal-stat-total-meta {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .modal-stat-label-light {
        font-size: 0.625rem;
        font-weight: 800;
        color: #86efac;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .modal-stat-sublabel-light {
        font-size: 0.5625rem;
        font-weight: 500;
        color: #d1fae5;
        opacity: 0.85;
        line-height: 1.1;
    }

    .modal-stat-val-light {
        font-size: clamp(1.15rem, 2.5vw, 1.4rem);
        font-weight: 900;
        color: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
        white-space: nowrap;
        line-height: 1.2;
        letter-spacing: -0.01em;
    }

    .modal-section-title {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        border-bottom: 1px solid var(--clr-surface-100);
        padding-bottom: 0.35rem;
        margin-bottom: 0.625rem;
    }

    .modal-data-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.625rem 1rem;
    }

    .modal-data-item {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .modal-data-label {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--txt-muted);
    }

    .modal-data-value {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--txt-primary);
        word-break: break-word;
    }

    .modal-obs-box {
        background: var(--clr-surface-50);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        padding: 0.625rem 0.75rem;
        font-size: 0.75rem;
        color: var(--txt-secondary);
        line-height: 1.4;
    }

    .modal-audit-bar {
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px dashed var(--brd-base);
        display: flex;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 0.6875rem;
        color: var(--txt-muted);
        flex-wrap: wrap;
    }

    .modal-footer {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    @media (max-width: 640px) {
        .modal-money-grid {
            grid-template-columns: 1fr 1fr;
            gap: 0.625rem;
            margin-bottom: 1rem;
        }

        .modal-stat-box {
            padding: 0.75rem 0.875rem;
        }

        .modal-stat-total {
            grid-column: 1 / -1;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
        }

        .modal-stat-val {
            font-size: 1.05rem;
        }

        .modal-stat-val-light {
            font-size: 1.25rem;
        }
    }
</style>
@endsection

@section('content')
<div>
    {{-- Barra superior de acciones --}}
    <div class="index-top-bar">
        <div>
            <h1>Historial de Ventas de Descarte</h1>
            <p>Consulta de pesajes y descarte registrado en campo y packing</p>
        </div>

        <div class="index-actions-group">
            <div class="export-actions" role="group" aria-label="Exportar historial filtrado">
                <a href="{{ route('ventas.exportar', request()->query()) }}" class="export-icon-btn export-icon-btn--excel" id="btn-exportar-ventas" title="Exportar historial filtrado a Excel" aria-label="Exportar historial filtrado a Excel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13l4 5m0-5-4 5m7-5v5"/></svg>
                </a>
            </div>
            @if(Auth::user()->canAccessModule('registro'))
            <a href="{{ route('ventas.create') }}" class="btn btn-primary" id="btn-nueva-venta">
                Registrar Venta
            </a>
            @endif
        </div>
    </div>

    {{-- Filtros de búsqueda --}}
    @include('ventas._filtros')

    {{-- Tabla de Registros --}}
    <div class="table-container">
        <div class="table-header-bar">
            <div class="table-header-summary">
                <span class="summary-label">Registros encontrados:</span>
                <span class="summary-count">{{ $ventas->total() }}</span>
                @if($ventas->total() > 0)
                    <span class="summary-range">(Mostrando {{ $ventas->firstItem() }}-{{ $ventas->lastItem() }})</span>
                @endif
            </div>

            @if($ventas->hasPages())
                <nav class="table-mini-pagination" aria-label="Paginación superior rápida">
                    {{-- Botón Anterior --}}
                    @if($ventas->onFirstPage())
                        <span class="mini-pag-btn is-disabled" aria-disabled="true" title="Primera página">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                            <span class="mini-pag-text">Anterior</span>
                        </span>
                    @else
                        <a href="{{ $ventas->previousPageUrl() }}" class="mini-pag-btn" aria-label="Ir a página anterior" title="Página anterior">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                            <span class="mini-pag-text">Anterior</span>
                        </a>
                    @endif

                    {{-- Indicador y números de página rápidos en desktop --}}
                    <div class="mini-pag-numbers">
                        @for($page = 1; $page <= $ventas->lastPage(); $page++)
                            @if($page == $ventas->currentPage())
                                <span class="mini-pag-num is-active" aria-current="page">{{ $page }}</span>
                            @elseif($ventas->lastPage() <= 6 || $page == 1 || $page == $ventas->lastPage() || abs($page - $ventas->currentPage()) <= 1)
                                <a href="{{ $ventas->url($page) }}" class="mini-pag-num" aria-label="Ir a página {{ $page }}">{{ $page }}</a>
                            @elseif($page == 2 || $page == $ventas->lastPage() - 1)
                                <span class="mini-pag-ellipsis">&hellip;</span>
                            @endif
                        @endfor
                    </div>

                    {{-- Info en móvil compacto --}}
                    <span class="mini-pag-mobile-info">
                        Pág. <strong>{{ $ventas->currentPage() }}</strong> / <strong>{{ $ventas->lastPage() }}</strong>
                    </span>

                    {{-- Botón Siguiente --}}
                    @if($ventas->hasMorePages())
                        <a href="{{ $ventas->nextPageUrl() }}" class="mini-pag-btn" aria-label="Ir a página siguiente" title="Página siguiente">
                            <span class="mini-pag-text">Siguiente</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    @else
                        <span class="mini-pag-btn is-disabled" aria-disabled="true" title="Última página">
                            <span class="mini-pag-text">Siguiente</span>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                        </span>
                    @endif
                </nav>
            @endif
        </div>

        <div style="overflow-x: auto;">
            <table class="sales-table" role="table" aria-label="Historial de ventas de descarte">
                <thead>
                    <tr>
                        <th scope="col">Fundo</th>
                        <th scope="col">Viaje</th>
                        <th scope="col">Fecha Prod.</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Cuartel</th>
                        <th scope="col">Motivo</th>
                        <th scope="col">Tipo Descarte</th>
                        <th scope="col" class="text-right">Kilogramos</th>
                        <th scope="col" class="text-right">Precio/Kg</th>
                        <th scope="col" class="text-right">Total (S/)</th>
                        <th scope="col" style="text-align: center;">Fecha y hora validación</th>
                        <th scope="col" style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        <tr class="{{ $venta->isAnulado() ? 'row-anulado' : '' }}">
                            <td>
                                <strong>{{ $venta->fundo?->name ?? 'N/A' }}</strong>
                                @if($venta->isAnulado())
                                    <div style="margin-top: 0.15rem;"><span class="badge-motivo badge-anulado">Anulado</span></div>
                                @elseif($venta->isBorradorRegistro())
                                    <div style="margin-top: 0.15rem; font-size: 0.75rem; color: #92400e;"><strong>Borrador</strong>
                                        @if($venta->camposFaltantesGarita())
                                            <span> · Falta información</span>
                                        @endif
                                    </div>
                                @elseif($venta->isBorradorGarita())
                                    <div style="margin-top: 0.15rem; font-size: 0.75rem; color: #166534;"><strong>Registro oficial</strong> · listo para enviar
                                        @if($venta->camposFaltantesGarita())
                                            <span title="{{ implode(', ', $venta->camposFaltantesGarita()) }}"> · Falta: {{ implode(', ', $venta->camposFaltantesGarita()) }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>{{ $venta->viaje ? 'Viaje '.$venta->viaje : '—' }}</td>
                            <td>
                                {{ $venta->fecha_produccion->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong style="color: var(--txt-primary);">{{ $venta->cliente ?: ($venta->ruc ? 'RUC: '.$venta->ruc : '—') }}</strong>
                            </td>
                            <td>
                                {{ $venta->lote?->nombre ?? 'Sin Lote' }}
                            </td>
                            <td>
                                {{ $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? '—') }}
                            </td>
                            <td>
                                @if($venta->motivo === 'Campo')
                                    <span class="badge-motivo badge-campo">Campo</span>
                                @elseif($venta->motivo === 'Packing')
                                    <span class="badge-motivo badge-packing">Packing</span>
                                @else
                                    <span class="badge-motivo badge-cosecha">Cosecha Nac.</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge-tipo">{{ $venta->tipo_descarte }}</span>
                            </td>
                            <td class="text-right" style="font-weight: 600;">
                                {{ number_format($venta->kilogramos, 2) }} kg
                            </td>
                            <td class="text-right text-muted">
                                S/ {{ number_format($venta->precio, 2) }}
                            </td>
                            <td class="text-right monto-total">
                                S/ {{ number_format($venta->valor_venta, 2) }}
                            </td>
                            <td style="text-align: center;">
                                {{ $venta->salida_garita_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td style="text-align: center;">
                                <div class="row-actions-btn-group">
                                    {{-- Ver (Modal de visualización) --}}
                                    <button type="button" 
                                            class="btn-action-icon btn-action-view btn-row-view" 
                                            title="Visualizar registro" 
                                            aria-label="Ver detalle del registro del {{ $venta->fecha_produccion->format('d/m/Y') }}"
                                            data-id="{{ $venta->id }}"
                                            data-estado="{{ $venta->estado ?? 'activo' }}"
                                            data-fundo="{{ $venta->fundo?->name ?? 'Fundo' }}"
                                            data-fecha="{{ $venta->fecha_produccion->format('d/m/Y') }}"
                                            data-cliente="{{ $venta->cliente ?: ($venta->ruc ? 'RUC: '.$venta->ruc : 'Sin cliente especificado') }}"
                                            data-ruc="{{ $venta->ruc ?: '—' }}"
                                            data-motivo="{{ $venta->motivo }}"
                                            data-tipo="{{ $venta->tipo_descarte }}"
                                            data-lote="{{ $venta->lote?->nombre ?? 'Sin Lote' }}"
                                            data-cuartel="{{ $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? '—') }}"
                                            data-kilos="{{ number_format($venta->kilogramos, 2) }}"
                                            data-precio="{{ number_format($venta->precio, 2) }}"
                                            data-total="{{ number_format($venta->valor_venta, 2) }}"
                                            data-jabas="{{ $venta->jabas ? number_format($venta->jabas) : '—' }}"
                                            data-peso-jaba="{{ $venta->peso_jaba ? number_format($venta->peso_jaba, 2) : '—' }}"
                                            data-placa="{{ $venta->placa ?: '—' }}"
                                            data-conductor="{{ $venta->conductor ?: '—' }}"
                                            data-brevete="{{ $venta->brevete ?: '—' }}"
                                            data-viaje="{{ $venta->viaje ?: '—' }}"
                                            data-estado-registro="{{ $venta->isBorradorRegistro() ? 'Borrador' : 'Registro oficial' }}"
                                            data-observacion="{{ $venta->observacion ?: '' }}"
                                            data-creado-por="{{ $venta->creator?->name ?? 'Usuario' }}"
                                            data-creado-at="{{ $venta->created_at ? $venta->created_at->format('d/m/Y H:i') : '—' }}"
                                            data-actualizado-por="{{ $venta->updater?->name ?? '' }}"
                                            data-actualizado-at="{{ $venta->updated_at && $venta->updated_at != $venta->created_at ? $venta->updated_at->format('d/m/Y H:i') : '' }}"
                                            data-url-show="{{ route('ventas.show', $venta) }}"
                                            @if($venta->puedeSerEditadoPor(Auth::user()))
                                            data-url-edit="{{ route('ventas.edit', $venta) }}"
                                            @endif
                                            data-cerrado="{{ $venta->isCerradoParaEdicion() && !$venta->tieneBorradorSinVencimiento() ? '1' : '0' }}"
                                            onclick="abrirModalDetalle(this)">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>

                                    @php
                                        $puedeEnviarGarita = $venta->puedeEnviarAGarita(Auth::user())
                                            && empty($venta->camposFaltantesGarita());
                                        $puedeCancelarGarita = $venta->puedeCancelarEnvioGarita(Auth::user());
                                        $puedeEditar = $venta->puedeSerEditadoPor(Auth::user());
                                        $puedeAnular = $venta->puedeSerAnuladoPor(Auth::user());
                                        $puedeReactivar = $venta->isAnulado()
                                            && (Auth::user()->isAdmin() || (Auth::user()->canAccessModule('registro') && !$venta->isCerradoParaEdicion()));
                                        $editarEsPrincipal = !$puedeEnviarGarita && !$puedeCancelarGarita && !$venta->isAnulado() && $puedeEditar;
                                        $hayAccionesSecundarias = $puedeCancelarGarita
                                            || ($puedeEditar && !$editarEsPrincipal)
                                            || $puedeAnular
                                            || $puedeReactivar;
                                        $menuId = 'acciones-venta-'.$venta->id;
                                    @endphp

                                    {{-- Acción principal según el estado del registro --}}
                                    @if($puedeEnviarGarita)
                                        <form method="POST" action="{{ route('ventas.enviar-garita', $venta) }}" style="display:inline;" onsubmit="return confirm('¿Ya verificaste el cliente, conductor, placa, número de viaje, jabas, kilogramos y lotes del envío? Confirma para enviarlo a Garita.');">
                                            @csrf
                                            <button type="submit" class="btn-action-icon btn-action-garita-send" title="Enviar camión/viaje a Garita" aria-label="Enviar a Garita">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                            </button>
                                        </form>
                                    @elseif($editarEsPrincipal)
                                        <a href="{{ route('ventas.edit', $venta) }}"
                                           class="btn-action-icon btn-action-edit btn-row-edit"
                                           title="{{ $venta->isBorradorRegistro() ? 'Completar borrador' : 'Editar registro oficial' }}"
                                           @if(!Auth::user()->isAdmin() && !$venta->isBorradorRegistro() && !$venta->tieneBorradorSinVencimiento() && $venta->created_at)
                                           data-expires-at="{{ ($venta->created_at->timestamp + 86400) * 1000 }}"
                                           data-venta-id="{{ $venta->id }}"
                                           @endif
                                           aria-label="{{ $venta->isBorradorRegistro() ? 'Completar borrador' : 'Editar venta' }} del {{ $venta->fecha_produccion->format('d/m/Y') }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </a>
                                    @elseif($puedeReactivar)
                                        <form method="POST" action="{{ route('ventas.reactivar', $venta) }}" style="display:inline;" onsubmit="return confirm('¿Deseas REACTIVAR este registro de venta?');">
                                            @csrf
                                            <button type="submit" class="btn-action-icon btn-action-reactivar" title="Reactivar registro" aria-label="Reactivar venta">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Acciones secundarias: se muestran solo al solicitar el menú --}}
                                    @if($hayAccionesSecundarias)
                                        <div class="row-actions-overflow">
                                            <button type="button"
                                                    class="btn-action-icon btn-action-more"
                                                    title="Más acciones"
                                                    aria-label="Más acciones para el registro del {{ $venta->fecha_produccion->format('d/m/Y') }}"
                                                    aria-haspopup="menu"
                                                    aria-expanded="false"
                                                    aria-controls="{{ $menuId }}"
                                                    data-actions-menu-button>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="5" cy="12" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="19" cy="12" r="1.7"/></svg>
                                            </button>

                                            <div id="{{ $menuId }}" class="row-actions-menu" role="menu" aria-label="Acciones secundarias" hidden>
                                                @if($puedeCancelarGarita)
                                                    <form method="POST" action="{{ route('ventas.cancelar-garita', $venta) }}" onsubmit="return confirm('¿Deseas CANCELAR el envío a Garita para poder editar este registro?');">
                                                        @csrf
                                                        <button type="submit" class="row-action-menu-item" role="menuitem">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                                            <span>Cancelar envío a Garita</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($puedeEditar && !$editarEsPrincipal)
                                                    <a href="{{ route('ventas.edit', $venta) }}"
                                                       class="row-action-menu-item btn-row-edit"
                                                       role="menuitem"
                                                       @if(!Auth::user()->isAdmin() && !$venta->isBorradorRegistro() && !$venta->tieneBorradorSinVencimiento() && $venta->created_at)
                                                       data-expires-at="{{ ($venta->created_at->timestamp + 86400) * 1000 }}"
                                                       data-venta-id="{{ $venta->id }}"
                                                       @endif>
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        <span>{{ $venta->isBorradorRegistro() ? 'Editar borrador' : 'Editar registro' }}</span>
                                                    </a>
                                                @endif

                                                @if($puedeAnular)
                                                    <form method="POST"
                                                          action="{{ route('ventas.anular', $venta) }}"
                                                          class="form-action-anular form-row-anular"
                                                          @if(!Auth::user()->isAdmin() && !$venta->isBorradorRegistro() && $venta->created_at)
                                                          data-expires-at="{{ ($venta->created_at->timestamp + 86400) * 1000 }}"
                                                          data-venta-id="{{ $venta->id }}"
                                                          @endif
                                                          onsubmit="return confirm('¿Deseas ANULAR este registro de venta? Pasará a estado anulado y se descontará de los totales.');">
                                                        @csrf
                                                        <button type="submit" class="row-action-menu-item is-warning" role="menuitem">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                                            <span>{{ $venta->isBorradorRegistro() ? 'Descartar borrador' : 'Anular registro' }}</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($puedeReactivar)
                                                    <form method="POST" action="{{ route('ventas.destroy', $venta) }}" onsubmit="return confirm('¡ADVERTENCIA! Este registro ya está anulado.\n¿Deseas ELIMINARLO DEFINITIVAMENTE de la base de datos?\nEsta acción es irreversible.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="row-action-menu-item is-danger" role="menuitem">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                            <span>Eliminar definitivamente</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="padding: 3rem 1rem; text-align: center;">
                                <div class="empty-state">
                                    <div class="empty-icon"><x-ui-icon name="clipboard" size="32" /></div>
                                    <div class="empty-title">No hay ventas registradas</div>
                                    <div class="empty-desc">
                                        @if(request()->hasAny(['fundo_id', 'motivo', 'fecha_desde', 'fecha_hasta']))
                                            No se encontraron registros con los filtros seleccionados. Intenta cambiar los criterios.
                                        @else
                                            Aún no se han ingresado pesajes de descarte. Comienza haciendo clic en "Registrar Venta".
                                        @endif
                                    </div>
                                    @if(Auth::user()->canAccessModule('registro'))
                                        <div style="margin-top: 1rem;">
                                            <a href="{{ route('ventas.create') }}" class="btn btn-primary btn-sm">
                                                <x-ui-icon name="plus" size="15" /> Registrar Primera Venta
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($ventas->hasPages())
            <div class="pagination-wrapper">
                {{ $ventas->links() }}
            </div>
        @endif
    </div>

    {{-- Modal para Visualizar Registro de Venta --}}
    @include('ventas._modal_detalle')
</div>

<script>
(function() {
    var openButton = null;

    function closeActionsMenu(returnFocus) {
        if (!openButton) return;

        var menuId = openButton.getAttribute('aria-controls');
        var menu = menuId ? document.getElementById(menuId) : null;
        openButton.setAttribute('aria-expanded', 'false');
        if (menu) menu.hidden = true;

        if (returnFocus) openButton.focus();
        openButton = null;
    }

    function positionActionsMenu(button, menu) {
        var buttonRect = button.getBoundingClientRect();
        var menuRect = menu.getBoundingClientRect();
        var margin = 8;
        var left = Math.min(
            window.innerWidth - menuRect.width - margin,
            Math.max(margin, buttonRect.right - menuRect.width)
        );
        var spaceBelow = window.innerHeight - buttonRect.bottom;
        var top = spaceBelow >= menuRect.height + margin
            ? buttonRect.bottom + 6
            : Math.max(margin, buttonRect.top - menuRect.height - 6);

        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
    }

    document.addEventListener('click', function(event) {
        var button = event.target.closest('[data-actions-menu-button]');

        if (button) {
            event.preventDefault();
            var wasOpen = button === openButton;
            closeActionsMenu(false);
            if (wasOpen) return;

            var menu = document.getElementById(button.getAttribute('aria-controls'));
            if (!menu) return;

            openButton = button;
            button.setAttribute('aria-expanded', 'true');
            menu.hidden = false;
            positionActionsMenu(button, menu);

            var firstItem = menu.querySelector('[role="menuitem"]');
            if (firstItem) firstItem.focus();
            return;
        }

        if (!event.target.closest('.row-actions-menu')) {
            closeActionsMenu(false);
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && openButton) {
            event.preventDefault();
            closeActionsMenu(true);
        }
    });

    window.addEventListener('resize', function() { closeActionsMenu(false); });
    window.addEventListener('scroll', function() { closeActionsMenu(false); }, true);
})();
</script>

@if(!Auth::user()->isAdmin())
<script>
(function() {
    function ocultarMenuVacio(group) {
        var menu = group.querySelector('.row-actions-menu');
        if (!menu || menu.querySelector('.row-action-menu-item')) return;

        var overflow = menu.closest('.row-actions-overflow');
        if (overflow) overflow.remove();
    }

    function verificarVencimientosEnTabla() {
        var now = Date.now();
        var expirables = document.querySelectorAll('[data-expires-at]');

        expirables.forEach(function(el) {
            var expiresAt = parseInt(el.getAttribute('data-expires-at'), 10);
            if (expiresAt && now >= expiresAt) {
                var group = el.closest('.row-actions-btn-group') || el.parentElement;

                if (group) {
                    // 1. Remover botón de Editar
                    var editBtn = group.querySelector('.btn-row-edit');
                    if (editBtn) editBtn.remove();

                    // 2. Remover formulario y botón de Anular
                    var anularForm = group.querySelector('.form-row-anular, .form-action-anular');
                    if (anularForm) anularForm.remove();

                    ocultarMenuVacio(group);

                    // 3. Actualizar botón "Ver" para que su dataset refleje cerrado=1 y no ofrezca editar
                    var viewBtn = group.querySelector('.btn-row-view');
                    if (viewBtn) {
                        viewBtn.removeAttribute('data-url-edit');
                        viewBtn.setAttribute('data-cerrado', '1');
                    }
                } else {
                    el.remove();
                }

                // 4. Si el modal de detalle está abierto para este registro, actualizarlo
                var modalEdit = document.getElementById('modal-btn-edit');
                var modalBadgeCerrado = document.getElementById('modal-badge-cerrado');
                if (modalEdit && modalEdit.style.display !== 'none') {
                    modalEdit.style.display = 'none';
                    if (modalBadgeCerrado) modalBadgeCerrado.style.display = 'inline-flex';
                }
            }
        });
    }

    // Interceptar si el usuario hace clic en Anular justo al expirar
    document.addEventListener('submit', function(e) {
        var form = e.target.closest('.form-row-anular, .form-action-anular');
        if (form && form.hasAttribute('data-expires-at')) {
            var expiresAt = parseInt(form.getAttribute('data-expires-at'), 10);
            if (Date.now() >= expiresAt) {
                e.preventDefault();
                e.stopPropagation();
                alert('Este registro superó el límite de 24 horas y se encuentra cerrado para anulación.');
                form.remove();
                return false;
            }
        }
    }, true);

    // Verificar cada 1 segundo en tiempo real
    setInterval(verificarVencimientosEnTabla, 1000);
    // Ejecutar una pasada inmediata al cargar
    verificarVencimientosEnTabla();
})();
</script>
@endif
@endsection

