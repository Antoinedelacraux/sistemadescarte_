<style>
    /* ============================================================
     * PANEL DE CONTROL — SISTEMA FUNDO (DECLUTTERED & OPTIMIZADO)
     * Diseñado para máxima claridad y facilidad de uso operativo
     * ============================================================ */

    /* Banner principal de bienvenida y acceso rápido */
    .welcome-banner {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        border-radius: var(--radius-xl);
        padding: clamp(1.25rem, 4vw, 1.75rem);
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 16px rgba(5,46,22,0.18);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -40px;
        right: 180px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(74,222,128,0.10);
        pointer-events: none;
    }

    .welcome-banner-info {
        flex: 1;
        min-width: 240px;
        position: relative;
        z-index: 1;
    }

    .welcome-greeting {
        font-size: var(--text-xs);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #86efac;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .welcome-name {
        font-size: clamp(1.25rem, 5vw, 1.75rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin-bottom: 0.5rem;
        word-break: break-word;
    }

    .welcome-meta {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        flex-wrap: wrap;
        font-size: var(--text-xs);
        color: rgba(255,255,255,0.75);
    }

    .welcome-meta-item {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .welcome-actions {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .btn-hero-primary {
        background: #22c55e;
        color: #052e16;
        font-weight: 700;
        padding: 0.625rem 1.125rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: var(--text-sm);
        transition: all var(--transition-fast);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .btn-hero-primary:hover {
        background: #4ade80;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    }

    .btn-hero-secondary {
        background: rgba(255,255,255,0.12);
        color: white;
        border: 1px solid rgba(255,255,255,0.25);
        font-weight: 600;
        padding: 0.625rem 1rem;
        border-radius: var(--radius-md);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-size: var(--text-sm);
        transition: all var(--transition-fast);
    }
    .btn-hero-secondary:hover {
        background: rgba(255,255,255,0.22);
    }

    @media (max-width: 640px) {
        .welcome-actions {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .btn-hero-primary, .btn-hero-secondary {
            justify-content: center;
            padding: 0.5625rem 0.5rem;
            font-size: clamp(0.75rem, 2.8vw, 0.8125rem);
            text-align: center;
        }
    }

    /* Grid de métricas clave (KPIs) */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1.125rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.375rem;
        flex-shrink: 0;
    }
    .stat-icon.green  { background: #dcfce7; color: #166534; }
    .stat-icon.yellow { background: #fef3c7; color: #92400e; }
    .stat-icon.blue   { background: #dbeafe; color: #1e40af; }

    .stat-value {
        font-size: clamp(1.2rem, 4.5vw, 1.5rem);
        font-weight: 800;
        line-height: 1.1;
        color: var(--txt-primary);
        font-family: monospace;
    }

    .stat-label {
        font-size: var(--text-xs);
        color: var(--txt-muted);
        margin-top: 0.2rem;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        .stat-card {
            padding: 0.875rem 1rem;
        }
    }

    /* Secciones del panel y tarjetas de tabla pulidas */
    .dash-section {
        margin-bottom: 1.75rem;
        animation: fadeInTab 0.18s ease-in-out;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .dash-table-card {
        background: #ffffff;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-xl);
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        margin-bottom: 1.5rem;
    }

    .dash-table-header {
        padding: clamp(1.125rem, 3.5vw, 1.375rem) clamp(1.25rem, 4vw, 1.625rem);
        border-bottom: 1px solid var(--brd-base);
        background: #ffffff;
    }

    /* Selector de Vista de Tablas (Tabs / Switcher) */
    .table-view-control-bar {
        background: #ffffff;
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 0.625rem 0.875rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.875rem;
        box-shadow: var(--shadow-xs);
        flex-wrap: wrap;
    }

    .table-view-label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: var(--text-xs);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        white-space: nowrap;
    }

    .table-view-pills {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
        flex: 1;
    }
    .table-view-pills::-webkit-scrollbar { display: none; }

    .view-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4375rem 0.875rem;
        font-size: var(--text-xs);
        font-weight: 600;
        border-radius: var(--radius-full);
        border: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        color: var(--txt-secondary);
        cursor: pointer;
        transition: all var(--transition-fast);
        white-space: nowrap;
        font-family: inherit;
        line-height: 1.2;
    }
    .view-pill:hover {
        background: var(--clr-surface-100);
        color: var(--txt-primary);
        border-color: var(--brd-strong);
    }
    .view-pill.active {
        background: #14532d;
        color: #ffffff;
        border-color: #14532d;
        box-shadow: 0 2px 6px rgba(20, 83, 45, 0.25);
    }
    .view-pill.active svg {
        stroke: #ffffff;
    }
    .view-pill-count {
        font-size: 0.625rem;
        font-weight: 700;
        padding: 0.1rem 0.35rem;
        border-radius: var(--radius-full);
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }
    .view-pill:not(.active) .view-pill-count {
        background: var(--clr-surface-200);
        color: var(--txt-secondary);
    }

    .table-view-fundo-filter {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    @media (max-width: 640px) {
        .table-view-control-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .table-view-fundo-filter {
            margin-left: 0 !important;
            width: 100%;
            justify-content: space-between;
        }
        .table-view-fundo-filter select {
            flex: 1;
        }
    }

    .section-header-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .section-title {
        font-size: clamp(1rem, 3.2vw, 1.22rem);
        font-weight: 700;
        color: var(--txt-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        line-height: 1.3;
        letter-spacing: -0.01em;
    }

    .section-subtitle {
        font-size: clamp(0.72rem, 2.4vw, 0.8125rem);
        color: var(--txt-muted);
        margin-top: 0.25rem;
        line-height: 1.4;
    }

    /* Tablas operativas */
    .table-clean {
        min-width: 660px;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-clean th {
        background: #f8fafc;
        padding: 0.625rem 0.875rem;
        font-size: 0.6875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-secondary);
        border-bottom: 2px solid var(--brd-base);
        font-weight: 700;
    }

    .table-clean td {
        padding: 0.75rem 0.875rem;
        font-size: var(--text-sm);
        border-bottom: 1px solid var(--clr-surface-100);
        vertical-align: middle;
    }

    /* Pastillas de motivos / origen con respiro adecuado */
    .motivo-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.65rem;
        border-radius: var(--radius-full);
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: 0.02em;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }
    .motivo-pill-amber { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .motivo-pill-green { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .motivo-pill-blue  { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

    /* Contenedor apilador con espacio generoso para columna Origen */
    .origen-pills-stack {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        align-items: flex-start;
        padding: 0.25rem 0;
    }

    /* Barra visual de porcentaje */
    .progress-bar-wrap {
        width: 75px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
        margin-right: 0.375rem;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
    }
    .progress-fill-amber { background: #f59e0b; }
    .progress-fill-green { background: #16a34a; }
    .progress-fill-blue  { background: #2563eb; }

    /* Subtotales y Totales */
    .row-subtotal {
        background: #f8fafc;
        font-weight: 600;
        font-size: var(--text-xs);
    }
    .row-subtotal td {
        border-bottom: 2px solid var(--brd-base);
    }

    .row-grand-total {
        background: #0f2b1f;
        color: white !important;
        font-weight: 800;
    }
    .row-grand-total td {
        color: white !important;
        padding: 0.75rem 0.875rem;
        border: none;
        font-size: var(--text-sm);
    }
    .row-grand-total .text-highlight {
        color: #4ade80 !important;
    }

    /* Barra de filtros de clientes */
    .client-controls-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--brd-base);
    }

    .search-box-client {
        position: relative;
        flex: 1;
        min-width: 200px;
        max-width: 340px;
    }

    .search-box-client input {
        width: 100%;
        padding: 0.4375rem 0.75rem;
        font-size: var(--text-xs);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-md);
        background: white;
        color: var(--txt-primary);
        font-family: inherit;
    }
    .search-box-client input:focus {
        outline: none;
        border-color: var(--clr-primary-500);
        box-shadow: 0 0 0 3px rgba(34,197,94,0.15);
    }

    .search-box-client .search-icon {
        position: absolute;
        left: 0.625rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.875rem;
        color: var(--txt-muted);
        pointer-events: none;
    }

    .filter-chips-wrap {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .chip-btn {
        padding: 0.28rem 0.625rem;
        font-size: 0.6875rem;
        font-weight: 600;
        border-radius: var(--radius-full);
        border: 1px solid var(--brd-base);
        background: white;
        color: var(--txt-secondary);
        cursor: pointer;
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .chip-btn:hover {
        background: var(--clr-primary-50);
        border-color: var(--clr-primary-300);
        color: var(--clr-primary-800);
    }
    .chip-btn.active {
        background: var(--clr-primary-700);
        border-color: var(--clr-primary-700);
        color: white;
    }

    .view-toggle-btns {
        display: inline-flex;
        border-radius: var(--radius-md);
        overflow: hidden;
        border: 1px solid var(--brd-base);
        background: white;
    }
    .view-toggle-btn {
        padding: 0.28rem 0.625rem;
        font-size: 0.6875rem;
        font-weight: 600;
        border: none;
        background: none;
        cursor: pointer;
        color: var(--txt-secondary);
        transition: background var(--transition-fast);
    }
    .view-toggle-btn.active {
        background: var(--clr-primary-100);
        color: var(--clr-primary-800);
    }

    /* Filas de cliente */
    .client-parent-row {
        cursor: pointer;
        transition: background var(--transition-fast);
    }
    .client-parent-row:hover {
        background: #f8fafc;
    }
    .client-parent-row.expanded {
        background: #f8fafc;
    }

    .client-toggle-caret {
        display: inline-block;
        width: 18px;
        height: 18px;
        text-align: center;
        line-height: 18px;
        border-radius: 4px;
        background: var(--clr-surface-200);
        color: var(--txt-secondary);
        font-size: 0.6875rem;
        margin-right: 0.5rem;
        transition: transform 0.2s ease;
    }
    .client-parent-row.expanded .client-toggle-caret {
        transform: rotate(90deg);
        background: var(--clr-primary-600);
        color: white;
    }

    .client-subrow {
        background: #fafafa;
        font-size: var(--text-xs);
    }
    .client-subrow td {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed var(--brd-base);
    }

    .client-subrow-bullet {
        margin-left: 1.625rem;
        color: var(--txt-muted);
        font-size: 0.6875rem;
    }

    /* ==========================================================================
       TABLAS MATRICIALES POR CLIENTE (KILOGRAMOS Y MONTOS)
       ========================================================================== */
    .matrix-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
        font-size: var(--text-xs);
        border: 1px solid var(--brd-base);
    }

    /* Fila 1 de encabezados: Motivos */
    .matrix-header-row-1 th {
        padding: 0.65rem 0.5rem;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        border: 1px solid rgba(0, 0, 0, 0.12);
    }

    .matrix-th-client {
        background: #bae6fd !important;
        color: #0369a1 !important;
        text-align: left !important;
        padding-left: 1rem !important;
        min-width: 200px;
        width: 220px;
        vertical-align: middle;
        font-size: 0.75rem;
    }

    .matrix-th-motivo {
        background: #ea580c !important;
        color: #ffffff !important;
        font-size: 0.75rem;
    }
    .matrix-th-campo {
        background: #ea580c !important;
    }
    .matrix-th-packing {
        background: #ea580c !important;
    }
    .matrix-th-nacional {
        background: #ea580c !important;
    }

    .matrix-th-total {
        background: #bae6fd !important;
        color: #0369a1 !important;
        text-align: right !important;
        padding-right: 1rem !important;
        min-width: 130px;
        vertical-align: middle;
        font-size: 0.75rem;
    }

    /* Fila 2 de encabezados: Tipos */
    .matrix-header-row-2 th {
        background: #e0f2fe;
        color: #0369a1;
        font-weight: 700;
        font-size: 0.7rem;
        padding: 0.5rem 0.4rem;
        text-align: center;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }

    /* Celdas del cuerpo */
    .matrix-client-row {
        transition: background 0.12s ease;
    }
    .matrix-client-row:nth-child(even) {
        background: #fcfcfc;
    }
    .matrix-client-row:hover {
        background: #f0fdf4 !important;
    }

    .matrix-td-client {
        padding: 0.625rem 1rem;
        border-right: 1px solid var(--brd-base);
        border-bottom: 1px solid var(--brd-light);
        vertical-align: middle;
    }
    .matrix-client-ruc {
        display: block;
        font-size: 0.6875rem;
        color: var(--txt-muted);
        font-weight: normal;
        margin-top: 0.15rem;
    }

    .matrix-td-val {
        padding: 0.625rem 0.6rem;
        text-align: right;
        font-family: var(--font-mono, monospace);
        border-right: 1px solid var(--brd-light);
        border-bottom: 1px solid var(--brd-light);
        font-size: 0.8125rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    .matrix-td-val.has-val {
        color: var(--txt-primary);
        font-weight: 600;
    }
    .matrix-td-val.empty-val {
        color: #94a3b8;
        font-weight: 400;
    }

    .matrix-td-total {
        padding: 0.625rem 1rem;
        text-align: right;
        font-family: var(--font-mono, monospace);
        border-bottom: 1px solid var(--brd-light);
        font-size: 0.825rem;
        background: #f8fafc;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Fila de Totales Generales en pie */
    .matrix-tfoot-row td {
        background: #f1f5f9;
        font-weight: 800;
        border-top: 2px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
        border-right: 1px solid #cbd5e1;
        vertical-align: middle;
        padding: 0.75rem 0.6rem;
    }
    .matrix-tf-client {
        padding-left: 1rem !important;
        color: var(--txt-primary);
        font-size: 0.75rem;
        letter-spacing: 0.03em;
    }
    .matrix-tf-val {
        text-align: right;
        font-family: var(--font-mono, monospace);
        font-size: 0.8125rem;
        color: var(--txt-primary);
    }
    .matrix-tf-total {
        padding-right: 1rem !important;
        text-align: right;
        font-family: var(--font-mono, monospace);
        font-size: 0.85rem;
        background: #e2e8f0 !important;
        color: #0f172a;
    }

    @media (max-width: 640px) {
        #container-matriz-kg,
        #container-matriz-monto {
            padding: 0.75rem 0.5rem 1rem 0.5rem !important;
        }
        .matrix-table {
            min-width: 800px;
        }
        .matrix-header-row-1 th,
        .matrix-header-row-2 th {
            padding: 0.45rem 0.35rem;
            font-size: 0.65rem;
        }
        .matrix-td-val,
        .matrix-tf-val {
            padding: 0.5rem 0.45rem;
            font-size: 0.75rem;
        }
        .matrix-td-client,
        .matrix-tf-client {
            padding: 0.5rem 0.65rem !important;
            font-size: 0.72rem;
            min-width: 160px;
            width: 170px;
        }
        .matrix-td-total,
        .matrix-tf-total {
            padding: 0.5rem 0.65rem !important;
            font-size: 0.78rem;
            min-width: 100px;
        }
        .view-toggle-btns {
            width: 100%;
        }
        .view-toggle-btn {
            flex: 1;
            text-align: center;
            padding: 0.4rem 0.25rem;
            font-size: 0.68rem;
        }
    }
</style>
