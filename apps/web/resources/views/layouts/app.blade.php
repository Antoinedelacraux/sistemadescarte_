<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema Web de Gestión Agrícola para fundos. Módulo de venta de descarte y reportes.">
    <title>@yield('title', 'Sistema Fundo') — Gestión Agrícola</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ============================================================
         * SISTEMA DE DISEÑO — FUNDO AGRÍCOLA
         * Versión: 4.0 | Fase 4
         * ============================================================ */

        /* --- DESIGN TOKENS --- */
        :root {
            /* Paleta primaria: verde oliva-esmeralda agrícola */
            --clr-primary-50:  #f0fdf4;
            --clr-primary-100: #dcfce7;
            --clr-primary-200: #bbf7d0;
            --clr-primary-300: #86efac;
            --clr-primary-400: #4ade80;
            --clr-primary-500: #22c55e;
            --clr-primary-600: #16a34a;
            --clr-primary-700: #15803d;
            --clr-primary-800: #166534;
            --clr-primary-900: #14532d;

            /* Paleta secundaria: tierra cálida */
            --clr-earth-50:  #fefce8;
            --clr-earth-100: #fef9c3;
            --clr-earth-400: #facc15;
            --clr-earth-600: #ca8a04;
            --clr-earth-800: #713f12;

            /* Paleta de superficies */
            --clr-surface-0:   #ffffff;
            --clr-surface-50:  #f8faf9;
            --clr-surface-100: #f1f5f2;
            --clr-surface-200: #e4ebe6;
            --clr-surface-900: #0f1f14;

            /* Sidebar */
            --sidebar-bg:     #0f1f14;
            --sidebar-hover:  #1a3322;
            --sidebar-active: #166534;
            --sidebar-text:   rgba(255,255,255,0.75);
            --sidebar-text-active: #ffffff;
            --sidebar-border: rgba(255,255,255,0.07);
            --sidebar-width:  240px;
            --sidebar-collapsed: 64px;

            /* Header */
            --header-bg:      #ffffff;
            --header-height:  60px;
            --header-border:  #e4ebe6;
            --header-shadow:  0 1px 3px rgba(0,0,0,0.06);

            /* Textos */
            --txt-primary:   #0f1f14;
            --txt-secondary: #374151;
            --txt-muted:     #6b7280;
            --txt-disabled:  #9ca3af;
            --txt-inverse:   #ffffff;

            /* Bordes */
            --brd-base:   #e4ebe6;
            --brd-strong: #d1d9d4;

            /* Estados semánticos */
            --clr-danger:     #dc2626;
            --clr-danger-bg:  #fef2f2;
            --clr-danger-brd: #fecaca;
            --clr-success:    #16a34a;
            --clr-success-bg: #f0fdf4;
            --clr-success-brd:#bbf7d0;
            --clr-warning:    #d97706;
            --clr-warning-bg: #fffbeb;
            --clr-warning-brd:#fde68a;
            --clr-info:       #0369a1;
            --clr-info-bg:    #eff6ff;
            --clr-info-brd:   #bfdbfe;
            --clr-offline:    #9ca3af;
            --clr-sync:       #d97706;

            /* Sombras */
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.07), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.07), 0 4px 6px rgba(0,0,0,0.04);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.08), 0 10px 10px rgba(0,0,0,0.03);

            /* Radio */
            --radius-sm: 0.25rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-full: 9999px;

            /* Tipografía */
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --text-xs:   0.6875rem;
            --text-sm:   0.8125rem;
            --text-base: 0.9375rem;
            --text-lg:   1.0625rem;
            --text-xl:   1.25rem;
            --text-2xl:  1.5rem;
            --text-3xl:  1.875rem;

            /* Transiciones */
            --transition-fast: 120ms ease;
            --transition-base: 200ms ease;
            --transition-slow: 320ms ease;
        }

        /* --- RESET & BASE --- */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: var(--font-sans);
            font-size: var(--text-base);
            color: var(--txt-secondary);
            background: var(--clr-surface-50);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* --- LAYOUT SHELL --- */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* ============================================================
         * SIDEBAR
         * ============================================================ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            transition: width var(--transition-slow), transform var(--transition-slow);
            z-index: 50;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        /* Sidebar: logo/brand */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1rem;
            height: var(--header-height);
            border-bottom: 1px solid var(--sidebar-border);
            text-decoration: none;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            flex-shrink: 0;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, #22c55e 0%, #15803d 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(34,197,94,0.35);
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            transition: opacity var(--transition-base);
        }

        .sidebar-brand-text strong {
            font-size: var(--text-sm);
            font-weight: 700;
            color: white;
        }

        .sidebar-brand-text span {
            font-size: var(--text-xs);
            color: rgba(255,255,255,0.45);
            font-weight: 400;
        }

        .sidebar.collapsed .sidebar-brand-text {
            opacity: 0;
            pointer-events: none;
        }

        /* Sidebar: navegación */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.75rem 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.08) transparent;
        }

        .nav-section-label {
            font-size: 0.625rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.28);
            padding: 1rem 1rem 0.375rem;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity var(--transition-base);
        }

        .sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 0;
            font-size: var(--text-sm);
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            transition: background var(--transition-fast), color var(--transition-fast);
            position: relative;
        }

        .nav-item:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .nav-item.active {
            background: var(--sidebar-active);
            color: var(--sidebar-text-active);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--clr-primary-400);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }

        .nav-item-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .nav-item-label {
            transition: opacity var(--transition-base);
            flex: 1;
        }

        .sidebar.collapsed .nav-item-label {
            opacity: 0;
        }

        .nav-badge {
            font-size: 0.625rem;
            font-weight: 700;
            padding: 0.125rem 0.375rem;
            border-radius: var(--radius-full);
            background: var(--clr-primary-500);
            color: white;
            transition: opacity var(--transition-base);
        }

        .sidebar.collapsed .nav-badge {
            opacity: 0;
        }

        /* Sidebar: footer */
        .sidebar-footer {
            padding: 0.75rem;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.375rem;
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--clr-primary-600), var(--clr-primary-800));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8125rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            flex: 1;
            overflow: hidden;
            transition: opacity var(--transition-base);
        }

        .sidebar-user-info strong {
            display: block;
            font-size: var(--text-xs);
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-info span {
            display: block;
            font-size: 0.625rem;
            color: rgba(255,255,255,0.4);
        }

        .sidebar.collapsed .sidebar-user-info {
            opacity: 0;
        }

        /* ============================================================
         * MAIN CONTENT AREA
         * ============================================================ */
        .main-wrapper {
            flex: 1;
            min-width: 0;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left var(--transition-slow);
        }

        .main-wrapper.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed);
        }

        .main-wrapper.sidebar-hidden {
            margin-left: 0;
        }

        /* ============================================================
         * HEADER
         * ============================================================ */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 40;
            height: var(--header-height);
            background: var(--header-bg);
            border-bottom: 1px solid var(--header-border);
            box-shadow: var(--header-shadow);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
        }

        .header-toggle {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            color: var(--txt-muted);
            transition: background var(--transition-fast), color var(--transition-fast);
            flex-shrink: 0;
        }

        .header-toggle:hover {
            background: var(--clr-surface-100);
            color: var(--txt-primary);
        }

        .header-toggle svg {
            width: 20px;
            height: 20px;
        }

        .header-breadcrumb {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: var(--text-sm);
            color: var(--txt-muted);
        }

        .header-breadcrumb .page-title {
            font-size: var(--text-base);
            font-weight: 600;
            color: var(--txt-primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Indicador de red */
        .net-indicator {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: var(--text-xs);
            font-weight: 600;
            padding: 0.3125rem 0.625rem;
            border-radius: var(--radius-full);
            border: 1px solid transparent;
            transition: all var(--transition-base);
            cursor: default;
        }

        .net-indicator.online {
            background: var(--clr-success-bg);
            color: var(--clr-success);
            border-color: var(--clr-success-brd);
        }

        .net-indicator.offline {
            background: #f3f4f6;
            color: var(--txt-muted);
            border-color: var(--brd-base);
        }

        .net-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .net-indicator.online .net-dot {
            background: var(--clr-success);
            animation: pulse-dot 2s ease infinite;
        }

        .net-indicator.offline .net-dot {
            background: var(--clr-offline);
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.75); }
        }

        /* Chip de fundo */
        .fundo-chip {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: var(--text-xs);
            font-weight: 600;
            padding: 0.3125rem 0.625rem;
            border-radius: var(--radius-full);
            background: var(--clr-primary-50);
            color: var(--clr-primary-700);
            border: 1px solid var(--clr-primary-200);
            white-space: nowrap;
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* User menu header */
        .header-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-md);
            transition: background var(--transition-fast);
            position: relative;
        }

        .header-user:hover {
            background: var(--clr-surface-100);
        }

        .header-user-name {
            font-size: var(--text-sm);
            font-weight: 600;
            color: var(--txt-primary);
        }

        /* Badge de rol */
        .role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.1875rem 0.5rem;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-radius: var(--radius-full);
        }

        .role-badge-admin      { background: #fee2e2; color: #991b1b; }
        .role-badge-general    { background: #dbeafe; color: #1e40af; }
        .role-badge-individual { background: #d1fae5; color: #065f46; }
        .role-badge-analista   { background: #fef3c7; color: #92400e; }

        /* Dropdown menú usuario */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            min-width: 200px;
            background: white;
            border: 1px solid var(--brd-base);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 0.375rem 0;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-4px);
            transition: opacity var(--transition-fast), transform var(--transition-fast), visibility 0s var(--transition-fast);
        }

        .header-user.open .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: opacity var(--transition-fast), transform var(--transition-fast);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.875rem;
            font-size: var(--text-sm);
            color: var(--txt-secondary);
            text-decoration: none;
            transition: background var(--transition-fast);
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background: var(--clr-surface-100);
        }

        .dropdown-item.danger {
            color: var(--clr-danger);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--brd-base);
            margin: 0.375rem 0;
        }

        /* ============================================================
         * PAGE CONTENT
         * ============================================================ */
        .page-content {
            flex: 1;
            padding: 1.75rem 1.5rem;
            max-width: 100%;
            overflow-x: hidden;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--txt-primary);
            line-height: 1.2;
        }

        .page-header p {
            color: var(--txt-muted);
            font-size: var(--text-sm);
            margin-top: 0.25rem;
        }

        /* ============================================================
         * CARDS
         * ============================================================ */
        .card {
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-base);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
            padding: 1.5rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .card-title {
            font-size: var(--text-base);
            font-weight: 600;
            color: var(--txt-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-subtitle {
            font-size: var(--text-xs);
            color: var(--txt-muted);
            margin-top: 0.125rem;
        }

        /* Stat cards */
        .stat-card {
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-base);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: var(--shadow-xs);
            transition: box-shadow var(--transition-base), transform var(--transition-base);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-1px);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .stat-icon.green  { background: var(--clr-primary-50);  color: var(--clr-primary-700); }
        .stat-icon.blue   { background: #eff6ff;  color: #1d4ed8; }
        .stat-icon.yellow { background: var(--clr-earth-50);  color: var(--clr-earth-600); }
        .stat-icon.red    { background: var(--clr-danger-bg); color: var(--clr-danger); }

        .stat-value {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--txt-primary);
            line-height: 1;
        }

        .stat-label {
            font-size: var(--text-xs);
            color: var(--txt-muted);
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* ============================================================
         * BOTONES
         * ============================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.5625rem 1rem;
            font-size: var(--text-sm);
            font-weight: 600;
            border-radius: var(--radius-md);
            cursor: pointer;
            border: 1px solid transparent;
            transition: all var(--transition-fast);
            text-decoration: none;
            white-space: nowrap;
            font-family: var(--font-sans);
        }

        .btn:focus-visible {
            outline: 2px solid var(--clr-primary-600);
            outline-offset: 2px;
        }

        .btn-primary {
            background: var(--clr-primary-700);
            color: white;
            border-color: var(--clr-primary-700);
        }

        .btn-primary:hover {
            background: var(--clr-primary-800);
            border-color: var(--clr-primary-800);
            box-shadow: 0 1px 4px rgba(22,101,52,0.25);
        }

        .btn-secondary {
            background: var(--clr-surface-0);
            color: var(--txt-secondary);
            border-color: var(--brd-strong);
        }

        .btn-secondary:hover {
            background: var(--clr-surface-100);
        }

        .btn-danger {
            background: var(--clr-danger);
            color: white;
            border-color: var(--clr-danger);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-ghost {
            background: transparent;
            color: var(--txt-muted);
            border-color: transparent;
        }

        .btn-ghost:hover {
            background: var(--clr-surface-100);
            color: var(--txt-secondary);
        }

        .btn-sm {
            padding: 0.3125rem 0.625rem;
            font-size: var(--text-xs);
            border-radius: var(--radius-sm);
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: var(--text-base);
            border-radius: var(--radius-md);
        }

        .btn-full { width: 100%; }

        /* ============================================================
         * FORMULARIOS
         * ============================================================ */
        .form-group {
            margin-bottom: 1.125rem;
        }

        .form-label {
            display: block;
            font-size: var(--text-sm);
            font-weight: 500;
            color: var(--txt-secondary);
            margin-bottom: 0.375rem;
        }

        .form-label .required {
            color: var(--clr-danger);
            margin-left: 0.125rem;
        }

        .form-control {
            width: 100%;
            padding: 0.5625rem 0.75rem;
            font-size: var(--text-sm);
            font-family: var(--font-sans);
            color: var(--txt-primary);
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-strong);
            border-radius: var(--radius-md);
            outline: none;
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast);
            appearance: none;
        }

        .form-control:focus {
            border-color: var(--clr-primary-600);
            box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
        }

        .form-control:disabled {
            background: var(--clr-surface-100);
            color: var(--txt-disabled);
            cursor: not-allowed;
        }

        .form-control.is-invalid {
            border-color: var(--clr-danger);
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220,38,38,0.15);
        }

        .form-hint {
            font-size: var(--text-xs);
            color: var(--txt-muted);
            margin-top: 0.25rem;
        }

        .form-error {
            font-size: var(--text-xs);
            color: var(--clr-danger);
            margin-top: 0.25rem;
        }

        /* ============================================================
         * ALERTAS / MENSAJES
         * ============================================================ */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.625rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            border-width: 1px;
            border-style: solid;
            margin-bottom: 1rem;
        }

        .alert-danger  { background: var(--clr-danger-bg);  color: var(--clr-danger);  border-color: var(--clr-danger-brd); }
        .alert-success { background: var(--clr-success-bg); color: var(--clr-success); border-color: var(--clr-success-brd); }
        .alert-warning { background: var(--clr-warning-bg); color: var(--clr-warning); border-color: var(--clr-warning-brd); }
        .alert-info    { background: var(--clr-info-bg);    color: var(--clr-info);    border-color: var(--clr-info-brd); }

        /* ============================================================
         * TABLAS
         * ============================================================ */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--brd-base);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: var(--text-sm);
        }

        .data-table th {
            background: var(--clr-surface-50);
            padding: 0.625rem 0.875rem;
            font-size: var(--text-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--txt-muted);
            border-bottom: 1px solid var(--brd-base);
            white-space: nowrap;
            text-align: left;
        }

        .data-table th.text-right,
        .data-table td.text-right { text-align: right; }

        .data-table td {
            padding: 0.75rem 0.875rem;
            border-bottom: 1px solid var(--clr-surface-100);
            color: var(--txt-secondary);
            vertical-align: middle;
        }

        .data-table tbody tr:last-child td { border-bottom: none; }

        .data-table tbody tr:hover td {
            background: var(--clr-primary-50);
        }

        /* ============================================================
         * ESTADO VACÍO
         * ============================================================ */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 0.75rem;
            opacity: 0.6;
        }

        .empty-title {
            font-size: var(--text-base);
            font-weight: 600;
            color: var(--txt-secondary);
        }

        .empty-desc {
            font-size: var(--text-sm);
            color: var(--txt-muted);
            margin-top: 0.25rem;
            max-width: 320px;
        }

        /* ============================================================
         * UTILIDADES
         * ============================================================ */
        .grid-auto { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
        .grid-2    { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .grid-3    { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .mt-0 { margin-top: 0; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 0.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .gap-1 { gap: 0.5rem; }
        .gap-2 { gap: 1rem; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .flex-wrap { flex-wrap: wrap; }
        .text-muted { color: var(--txt-muted); }
        .text-sm { font-size: var(--text-sm); }
        .text-xs { font-size: var(--text-xs); }
        .fw-600 { font-weight: 600; }
        .fw-700 { font-weight: 700; }
        .text-success { color: var(--clr-success); }
        .text-danger  { color: var(--clr-danger); }
        .text-warning { color: var(--clr-warning); }

        /* ============================================================
         * OVERLAY SIDEBAR (MOBILE)
         * ============================================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 45;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active { display: block; }

        /* ============================================================
         * RESPONSIVE
         * ============================================================ */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 640px) {
            .page-content { padding: 1.25rem 1rem; }
            .app-header { padding: 0 1rem; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .header-user-name { display: none; }
            .fundo-chip { display: none; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="app-shell">

        @auth
        {{-- OVERLAY MÓVIL --}}
        <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true" onclick="toggleSidebar()"></div>

        {{-- SIDEBAR --}}
        <aside class="sidebar" id="main-sidebar" role="navigation" aria-label="Menú principal">
            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="sidebar-brand" aria-label="Inicio — Sistema Fundo">
                <div class="sidebar-brand-icon" aria-hidden="true">🌾</div>
                <div class="sidebar-brand-text">
                    <strong>Sistema Fundo</strong>
                    <span>Gestión Agrícola</span>
                </div>
            </a>

            {{-- Navegación --}}
            <nav class="sidebar-nav" aria-label="Navegación principal">
                <div class="nav-section-label" aria-hidden="true">Principal</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                   aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                    <span class="nav-item-icon" aria-hidden="true">📊</span>
                    <span class="nav-item-label">Panel de Control</span>
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isGeneral() || Auth::user()->isIndividual())
                <a href="{{ route('ventas.create') }}"
                   class="nav-item {{ request()->routeIs('ventas.create') ? 'active' : '' }}"
                   aria-current="{{ request()->routeIs('ventas.create') ? 'page' : 'false' }}">
                    <span class="nav-item-icon" aria-hidden="true">📝</span>
                    <span class="nav-item-label">Registrar Venta</span>
                </a>
                @endif

                <a href="{{ route('ventas.index') }}"
                   class="nav-item {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.edit') ? 'active' : '' }}"
                   aria-current="{{ request()->routeIs('ventas.index') ? 'page' : 'false' }}">
                    <span class="nav-item-icon" aria-hidden="true">📋</span>
                    <span class="nav-item-label">Historial de Ventas</span>
                </a>

                @if(Auth::user()->isAdmin() || Auth::user()->isAnalista())
                <div class="nav-section-label" aria-hidden="true">Análisis</div>
                <a href="#" class="nav-item">
                    <span class="nav-item-icon" aria-hidden="true">📈</span>
                    <span class="nav-item-label">Reportes</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-item-icon" aria-hidden="true">📥</span>
                    <span class="nav-item-label">Exportar Excel</span>
                </a>
                @endif

                @if(Auth::user()->isAdmin())
                <div class="nav-section-label" aria-hidden="true">Administración</div>
                <a href="#" class="nav-item">
                    <span class="nav-item-icon" aria-hidden="true">🏡</span>
                    <span class="nav-item-label">Fundos</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-item-icon" aria-hidden="true">👥</span>
                    <span class="nav-item-label">Usuarios</span>
                </a>
                <a href="#" class="nav-item">
                    <span class="nav-item-icon" aria-hidden="true">⚙️</span>
                    <span class="nav-item-label">Configuración</span>
                </a>
                @endif
            </nav>

            {{-- Usuario en sidebar footer --}}
            <div class="sidebar-footer">
                <div class="sidebar-user" aria-label="Usuario actual">
                    <div class="user-avatar" aria-hidden="true">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="sidebar-user-info">
                        <strong title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</strong>
                        <span>{{ Auth::user()->role?->display_name ?? 'Sin rol' }}</span>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ÁREA PRINCIPAL --}}
        <div class="main-wrapper" id="main-wrapper">

            {{-- HEADER --}}
            <header class="app-header" role="banner">
                {{-- Botón hamburgesa --}}
                <button class="header-toggle"
                        id="sidebar-toggle"
                        onclick="toggleSidebar()"
                        aria-label="Mostrar u ocultar menú lateral"
                        aria-expanded="true"
                        aria-controls="main-sidebar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                {{-- Título de página --}}
                <div class="header-breadcrumb">
                    <span class="page-title" id="page-title">@yield('page-title', 'Panel de Control')</span>
                </div>

                {{-- Acciones del header --}}
                <div class="header-actions">
                    {{-- Indicador de red --}}
                    <div class="net-indicator online" id="net-indicator" role="status" aria-live="polite" aria-label="Estado de conexión: en línea">
                        <div class="net-dot" aria-hidden="true"></div>
                        <span id="net-label">En línea</span>
                    </div>

                    {{-- Chip de fundo activo --}}
                    @php
                        $fundosHeader = Auth::user()->fundos;
                        $isGlobal = Auth::user()->isAdmin() || Auth::user()->isAnalista();
                    @endphp
                    @if($isGlobal)
                        <div class="fundo-chip" title="Acceso a todos los fundos">
                            🌐 Todos los Fundos
                        </div>
                    @elseif($fundosHeader->count() === 1)
                        <div class="fundo-chip" title="{{ $fundosHeader->first()->name }}">
                            🏡 {{ $fundosHeader->first()->name }}
                        </div>
                    @elseif($fundosHeader->count() > 1)
                        <div class="fundo-chip" title="Múltiples fundos asignados">
                            🏡 {{ $fundosHeader->count() }} fundos
                        </div>
                    @endif

                    {{-- Menú de usuario --}}
                    <div class="header-user" id="header-user-menu" onclick="toggleUserMenu()" aria-haspopup="true" aria-expanded="false">
                        <div class="user-avatar" style="width:32px;height:32px;font-size:0.75rem;" aria-hidden="true">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="header-user-name">{{ Auth::user()->name }}</span>
                        <span class="role-badge role-badge-{{ Auth::user()->role?->name ?? 'default' }}" aria-label="Rol: {{ Auth::user()->role?->display_name }}">
                            {{ Auth::user()->role?->display_name ?? 'Sin Rol' }}
                        </span>

                        {{-- Dropdown --}}
                        <div class="user-dropdown" role="menu" aria-label="Opciones de usuario">
                            <div style="padding: 0.625rem 0.875rem; border-bottom: 1px solid var(--brd-base); margin-bottom: 0.25rem;">
                                <div style="font-size: var(--text-xs); font-weight: 600; color: var(--txt-primary);">{{ Auth::user()->name }}</div>
                                <div style="font-size: var(--text-xs); color: var(--txt-muted);">{{ Auth::user()->email }}</div>
                            </div>
                            <a href="{{ route('dashboard') }}" class="dropdown-item" role="menuitem">
                                <span aria-hidden="true">👤</span> Mi Perfil
                            </a>
                            <div class="dropdown-divider" role="separator"></div>
                            <form action="{{ route('logout') }}" method="POST" style="display:block;">
                                @csrf
                                <button type="submit" class="dropdown-item danger" role="menuitem">
                                    <span aria-hidden="true">🔒</span> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- CONTENIDO PRINCIPAL --}}
            <main class="page-content" id="main-content" role="main" tabindex="-1">
                @if(session('success'))
                    <div class="alert alert-success" role="alert">
                        <span aria-hidden="true">✅</span>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" role="alert">
                        <span aria-hidden="true">⚠️</span>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- FOOTER --}}
            <footer style="padding: 0.75rem 1.5rem; font-size: var(--text-xs); color: var(--txt-muted); border-top: 1px solid var(--brd-base); background: var(--clr-surface-0); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;" role="contentinfo">
                <span>Sistema Web del Fundo &bull; Fase 4 — UI/UX &bull; {{ now()->year }}</span>
                <span>Entorno: {{ app()->environment() }}</span>
            </footer>
        </div>
        @else
            {{-- Sin autenticación: layout simple centrado --}}
            <main style="flex: 1; display: flex; flex-direction: column;" role="main">
                @yield('content')
            </main>
        @endauth

    </div><!-- /.app-shell -->

    <script>
    (function() {
        'use strict';

        /* --- Estado sidebar --- */
        var SIDEBAR_KEY   = 'fundo_sidebar_state';
        var sidebar       = document.getElementById('main-sidebar');
        var mainWrapper   = document.getElementById('main-wrapper');
        var overlay       = document.getElementById('sidebar-overlay');
        var toggleBtn     = document.getElementById('sidebar-toggle');
        var isMobile      = function() { return window.innerWidth <= 1024; };

        function getSavedState() {
            try { return localStorage.getItem(SIDEBAR_KEY); } catch(e) { return null; }
        }

        function saveState(s) {
            try { localStorage.setItem(SIDEBAR_KEY, s); } catch(e) {}
        }

        function applyDesktopState() {
            if (!sidebar) return;
            var state = getSavedState();
            if (state === 'hidden') {
                sidebar.classList.add('hidden');
                if (mainWrapper) mainWrapper.classList.add('sidebar-hidden');
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                sidebar.classList.remove('hidden');
                if (mainWrapper) mainWrapper.classList.remove('sidebar-hidden');
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
            }
        }

        function toggleSidebar() {
            if (!sidebar) return;
            if (isMobile()) {
                var isOpen = sidebar.classList.contains('mobile-open');
                sidebar.classList.toggle('mobile-open', !isOpen);
                if (overlay) overlay.classList.toggle('active', !isOpen);
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(!isOpen));
            } else {
                var isHidden = sidebar.classList.contains('hidden');
                sidebar.classList.toggle('hidden', !isHidden);
                if (mainWrapper) mainWrapper.classList.toggle('sidebar-hidden', !isHidden);
                if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(isHidden));
                saveState(!isHidden ? 'hidden' : 'visible');
            }
        }

        // Exponer globalmente
        window.toggleSidebar = toggleSidebar;

        // Inicializar estado
        if (sidebar && !isMobile()) {
            applyDesktopState();
        }

        // Cerrar sidebar móvil al redimensionar a desktop
        window.addEventListener('resize', function() {
            if (!isMobile() && sidebar) {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
                applyDesktopState();
            }
        });

        /* --- Indicador de red --- */
        var netIndicator = document.getElementById('net-indicator');
        var netLabel     = document.getElementById('net-label');

        function updateNetStatus() {
            if (!netIndicator || !netLabel) return;
            if (navigator.onLine) {
                netIndicator.className = 'net-indicator online';
                netIndicator.setAttribute('aria-label', 'Estado de conexión: en línea');
                netLabel.textContent = 'En línea';
            } else {
                netIndicator.className = 'net-indicator offline';
                netIndicator.setAttribute('aria-label', 'Estado de conexión: sin conexión');
                netLabel.textContent = 'Sin conexión';
            }
        }

        window.addEventListener('online',  updateNetStatus);
        window.addEventListener('offline', updateNetStatus);
        updateNetStatus();

        /* --- Menú de usuario (dropdown) --- */
        var userMenu = document.getElementById('header-user-menu');

        window.toggleUserMenu = function() {
            if (!userMenu) return;
            var isOpen = userMenu.classList.contains('open');
            userMenu.classList.toggle('open', !isOpen);
            userMenu.setAttribute('aria-expanded', String(!isOpen));
        };

        document.addEventListener('click', function(e) {
            if (userMenu && !userMenu.contains(e.target)) {
                userMenu.classList.remove('open');
                userMenu.setAttribute('aria-expanded', 'false');
            }
        });

        /* --- Cerrar con teclado (Escape) --- */
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (userMenu) {
                    userMenu.classList.remove('open');
                    userMenu.setAttribute('aria-expanded', 'false');
                }
                if (isMobile() && sidebar && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    if (overlay) overlay.classList.remove('active');
                }
            }
        });

    })();
    </script>

    @yield('scripts')
</body>
</html>
