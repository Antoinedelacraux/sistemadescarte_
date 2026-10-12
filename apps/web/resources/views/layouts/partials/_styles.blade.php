    <style>
        /* ============================================================
         * SISTEMA DE DISEÑO — FUNDO AGRÍCOLA
         * Versión: 1.0.0 | Producción
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

        html {
            scroll-behavior: smooth;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-sans);
            font-size: var(--text-base);
            color: var(--txt-secondary);
            background: var(--clr-surface-50);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            touch-action: manipulation;
        }

        a, button, input, select, textarea, .btn, .nav-item {
            touch-action: manipulation;
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
            transition: width 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
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
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }

        .sidebar-brand-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: transparent !important;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.45));
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
         * PIE DE PÁGINA INSTITUCIONAL (FOOTER)
         * ============================================================ */
        .app-footer {
            padding: 0.75rem 1.5rem;
            font-size: var(--text-xs);
            color: var(--txt-muted);
            border-top: 1px solid var(--brd-base);
            background: var(--clr-surface-0);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .footer-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .footer-env-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.55rem;
            border-radius: var(--radius-full);
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #b45309;
            font-weight: 600;
            font-size: 0.6875rem;
        }

        .footer-env-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #f59e0b;
            flex-shrink: 0;
        }

        .footer-prod-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.55rem;
            border-radius: var(--radius-full);
            background: var(--clr-success-bg);
            border: 1px solid var(--clr-success-brd);
            color: var(--clr-success);
            font-weight: 600;
            font-size: 0.6875rem;
        }

        .footer-prod-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--clr-success);
            flex-shrink: 0;
        }

        .footer-version {
            font-weight: 500;
            color: var(--txt-muted);
        }

        @media (max-width: 640px) {
            .app-footer {
                padding: 0.625rem 1rem;
                justify-content: center;
                text-align: center;
                flex-direction: column;
                gap: 0.375rem;
            }
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

        .role-badge-admin       { background: #fee2e2; color: #991b1b; }
        .role-badge-jefe_fundo   { background: #dbeafe; color: #1e40af; }
        .role-badge-registrador  { background: #d1fae5; color: #065f46; }
        .role-badge-analista    { background: #fef3c7; color: #92400e; }
        .role-badge-ventas      { background: #f3e8ff; color: #6b21a8; }
        .role-badge-general     { background: #dbeafe; color: #1e40af; }
        .role-badge-individual  { background: #d1fae5; color: #065f46; }

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
         * PAGINACIÓN PROFESIONAL Y RESPONSIVE (FUNDO AGRÍCOLA)
         * Soluciona desbordamientos de SVG y adapta a pantallas móviles
         * ============================================================ */
        .pagination-wrapper {
            margin-top: 0;
            width: 100%;
        }

        .fundo-pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: clamp(0.75rem, 2vw, 0.95rem) clamp(1rem, 3vw, 1.5rem);
            background: var(--clr-surface-0);
            border-top: 1px solid var(--brd-base);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            gap: 1rem;
            flex-wrap: wrap;
        }

        .fundo-pagination-summary {
            font-size: clamp(0.75rem, 2.5vw, 0.8125rem);
            color: var(--txt-muted);
            white-space: nowrap;
        }

        .fundo-pagination-summary strong {
            color: var(--txt-primary);
            font-weight: 600;
        }

        .fundo-pagination-nav {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            flex-wrap: wrap;
        }

        .pagination-pages-group {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            min-height: 34px;
            padding: 0.35rem 0.65rem;
            font-size: var(--text-xs);
            font-weight: 600;
            color: var(--txt-secondary);
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-strong);
            border-radius: var(--radius-md);
            text-decoration: none;
            cursor: pointer;
            user-select: none;
            transition: all var(--transition-fast);
        }

        .pagination-btn:hover:not(.pagination-btn-disabled):not(.pagination-btn-active) {
            background: var(--clr-primary-50);
            border-color: var(--clr-primary-400);
            color: var(--clr-primary-800);
            transform: translateY(-1px);
        }

        .pagination-btn-active {
            background: var(--clr-primary-700);
            border-color: var(--clr-primary-700);
            color: #ffffff;
            cursor: default;
            box-shadow: 0 1px 3px rgba(22, 101, 52, 0.3);
        }

        .pagination-btn-disabled {
            opacity: 0.45;
            cursor: not-allowed;
            background: var(--clr-surface-50);
            border-color: var(--brd-base);
            color: var(--txt-disabled);
            pointer-events: none;
        }

        .pagination-dots {
            padding: 0 0.25rem;
            font-size: var(--text-xs);
            color: var(--txt-muted);
            font-weight: 700;
        }

        /* Regla de salvaguarda global para SVGs en cualquier paginación */
        .fundo-pagination-container svg,
        .pagination-wrapper svg,
        nav[role="navigation"] svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            flex-shrink: 0 !important;
        }

        /* Adaptación en móviles para paginación */
        @media (max-width: 640px) {
            .fundo-pagination-container {
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                padding: 0.875rem 0.5rem;
                text-align: center;
            }

            .fundo-pagination-nav {
                justify-content: center;
                width: 100%;
            }

            .pagination-btn {
                min-height: 36px;
                padding: 0.4rem 0.55rem;
            }

            .pagination-btn-text {
                display: none;
            }
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
         * OVERLAY SIDEBAR (MOBILE - OPTIMIZADO FLUIDEZ 60 FPS)
         * ============================================================ */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 45;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* ==========================================        /* ============================================================
         * COMPONENTE: LISTAS DESPLEGABLES CONTROLADAS (CUSTOM SELECT)
         * Evita desbordamiento de pantalla y permite scroll fluido ("ir bajando")
         * Conforme a inspeccion-ui-bordes-tipografia y 05-ux-accesibilidad
         * ============================================================ */
        .custom-select-wrap {
            position: relative;
            width: 100%;
        }

        .custom-select-native {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .custom-select-trigger {
            width: 100%;
            min-height: 40px;
            padding: 0.5rem 0.75rem;
            font-size: var(--text-sm);
            font-family: var(--font-sans);
            color: var(--txt-primary);
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-strong);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            cursor: pointer;
            text-align: left;
            user-select: none;
            transition: border-color var(--transition-fast), box-shadow var(--transition-fast), background var(--transition-fast);
        }

        .custom-select-trigger:hover {
            border-color: var(--clr-primary-500);
            background: var(--clr-surface-50);
        }

        .custom-select-trigger:focus-visible,
        .custom-select-wrap.is-open .custom-select-trigger {
            border-color: var(--clr-primary-600);
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
            background: var(--clr-surface-0);
            outline: none;
        }

        .custom-select-label {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: var(--text-sm);
        }

        .custom-select-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--txt-muted);
            transition: transform var(--transition-fast);
            flex-shrink: 0;
        }

        .custom-select-wrap.is-open .custom-select-arrow {
            transform: rotate(180deg);
            color: var(--clr-primary-700);
        }

        .custom-select-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-strong);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 1050;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: customSelectFadeIn 0.16s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes customSelectFadeIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .custom-select-wrap.is-open .custom-select-dropdown {
            display: flex;
        }

        /* Si se abre hacia arriba porque está al borde inferior de la pantalla */
        .custom-select-wrap.open-up .custom-select-dropdown {
            top: auto;
            bottom: calc(100% + 4px);
            animation: customSelectFadeInUp 0.16s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes customSelectFadeInUp {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Buscador interno para listas con muchas opciones (>6 opciones, ej. 18 lotes) */
        .custom-select-search-wrap {
            padding: 0.5rem;
            border-bottom: 1px solid var(--brd-base);
            background: var(--clr-surface-50);
        }

        .custom-select-search-input {
            width: 100%;
            padding: 0.4rem 0.625rem;
            font-size: var(--text-xs);
            font-family: var(--font-sans);
            border: 1px solid var(--brd-strong);
            border-radius: var(--radius-sm);
            background: var(--clr-surface-0);
            outline: none;
            color: var(--txt-primary);
        }

        .custom-select-search-input:focus {
            border-color: var(--clr-primary-600);
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.15);
        }

        /* Lista deslizable con scroll contenido donde el usuario puede ir bajando sin tapar la pantalla */
        .custom-select-options-list {
            max-height: 200px;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            padding: 0.25rem 0;
            scrollbar-width: thin;
            scrollbar-color: var(--brd-strong) transparent;
        }

        .custom-select-options-list::-webkit-scrollbar {
            width: 6px;
        }
        .custom-select-options-list::-webkit-scrollbar-thumb {
            background-color: var(--brd-strong);
            border-radius: 4px;
        }

        .custom-select-option {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5625rem 0.875rem;
            font-size: var(--text-sm);
            color: var(--txt-secondary);
            cursor: pointer;
            user-select: none;
            transition: background var(--transition-fast), color var(--transition-fast);
        }

        .custom-select-option:hover,
        .custom-select-option.is-focused {
            background: var(--clr-primary-50);
            color: var(--clr-primary-800);
        }

        .custom-select-option.is-selected {
            background: var(--clr-primary-100);
            color: var(--clr-primary-900);
            font-weight: 600;
        }

        .custom-select-option-check {
            width: 16px;
            height: 16px;
            color: var(--clr-primary-700);
            display: none;
            flex-shrink: 0;
        }

        .custom-select-option.is-selected .custom-select-option-check {
            display: inline-block;
        }

        .custom-select-empty {
            padding: 0.875rem;
            font-size: var(--text-xs);
            color: var(--txt-muted);
            text-align: center;
            font-style: italic;
        }

        /* ============================================================
         * RESPONSIVE & ADAPTACIÓN MÓVIL PROFESIONAL (PROGRESIVO)
         * Adaptación dinámica de elementos del headbar y layout
         * ============================================================ */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translate3d(-100%, 0, 0);
                width: var(--sidebar-width) !important;
                will-change: transform;
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
            }

            .sidebar.mobile-open {
                transform: translate3d(0, 0, 0) !important;
            }

            .main-wrapper {
                margin-left: 0 !important;
            }

            .grid-3 { grid-template-columns: repeat(2, 1fr); }

            /* En tablet ocultar el badge de rol del headbar (se muestra dentro del menú de usuario) */
            .header-user .role-badge {
                display: none;
            }

            .fundo-chip {
                max-width: 135px;
            }
        }

        @media (max-width: 768px) {
            .app-header {
                height: 56px;
                padding: 0 0.875rem;
                gap: 0.5rem;
            }

            .header-actions {
                gap: 0.5rem;
            }

            /* Ocultar texto largo del usuario; mantener avatar táctil */
            .header-user-name {
                display: none;
            }

            /* Ocultar etiqueta 'En línea' y dejar punto de pulso compacto */
            #net-label {
                display: none;
            }

            .net-indicator {
                padding: 0.35rem;
                width: 30px;
                height: 30px;
                justify-content: center;
                border-radius: var(--radius-full);
            }

            /* Chip de fundo adaptativo */
            .fundo-chip {
                max-width: 115px;
                font-size: 0.72rem;
                padding: 0.25rem 0.5rem;
            }

            .page-content {
                padding: 1.125rem 0.875rem;
            }

            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }

            .header-breadcrumb .page-title {
                font-size: clamp(0.85rem, 3.4vw, 1rem);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .card {
                padding: 1rem 0.875rem;
                border-radius: var(--radius-md);
            }

            .card-header {
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
            }

            .card-title {
                font-size: clamp(0.95rem, 3.8vw, 1.15rem);
                word-break: break-word;
            }

            .card-subtitle {
                font-size: clamp(0.72rem, 2.6vw, 0.8125rem);
                word-break: break-word;
            }

            .page-header h1 {
                font-size: clamp(1.2rem, 5vw, 1.5rem);
            }

            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                max-width: 100%;
                border-radius: var(--radius-md);
            }

            .data-table {
                min-width: 520px;
            }

            .data-table th, .data-table td {
                padding: 0.5rem 0.625rem;
                font-size: clamp(0.7rem, 2.5vw, 0.8125rem);
                white-space: nowrap;
            }

            .form-control, .custom-select-trigger {
                font-size: 16px; /* Evita auto-zoom en navegadores móviles */
            }

            /* Touch targets optimizados para dedos y guantes agrícolas en campo */
            .btn-action-icon {
                width: 38px !important;
                height: 38px !important;
            }

            .btn-action-icon svg {
                width: 17px !important;
                height: 17px !important;
            }

            .row-actions-btn-group, .row-actions {
                gap: 0.45rem !important;
            }
        }

        /* Pantallas móviles estrechas (< 480px) */
        @media (max-width: 480px) {
            .app-header {
                height: 52px;
                padding: 0 0.5rem;
                gap: 0.375rem;
            }

            .header-toggle {
                width: 34px;
                height: 34px;
            }

            .header-actions {
                gap: 0.35rem;
            }

            .net-indicator {
                width: 26px;
                height: 26px;
                padding: 0;
            }

            .user-avatar {
                width: 30px !important;
                height: 30px !important;
                font-size: 0.72rem !important;
            }

            .fundo-chip {
                max-width: 90px;
                font-size: 0.68rem;
                padding: 0.2rem 0.4rem;
            }

            .header-breadcrumb .page-title {
                font-size: clamp(0.78rem, 3.6vw, 0.875rem);
            }

            .btn {
                font-size: clamp(0.75rem, 2.6vw, 0.8125rem);
                padding: 0.45rem 0.65rem;
                min-height: 40px;
            }

            .card-title {
                font-size: clamp(0.875rem, 3.8vw, 1rem);
            }

            .card-subtitle {
                font-size: clamp(0.6875rem, 2.7vw, 0.75rem);
            }
        }

        /* Pantallas ultra-estrechas (< 360px, ej. iPhone SE / Galaxy Mini) */
        @media (max-width: 360px) {
            .app-header {
                padding: 0 0.375rem;
                gap: 0.25rem;
            }

            /* Ocultar el chip de fundo para no asfixiar el título ni el avatar */
            .fundo-chip {
                display: none;
            }

            .header-toggle {
                width: 30px;
                height: 30px;
            }

            .user-avatar {
                width: 28px !important;
                height: 28px !important;
                font-size: 0.68rem !important;
            }
        }

        /* ============================================================
         * COMPONENTES PWA: BOTÓN INSTALAR Y MODAL GUÍA IOS
         * ============================================================ */
        .btn-pwa-install {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, var(--clr-primary-700) 0%, var(--clr-primary-900) 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: var(--text-xs);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(22, 101, 52, 0.25);
            white-space: nowrap;
        }

        .btn-pwa-install:hover {
            background: linear-gradient(135deg, var(--clr-primary-600) 0%, var(--clr-primary-800) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(22, 101, 52, 0.35);
        }

        .btn-pwa-install svg {
            color: var(--clr-primary-300);
            flex-shrink: 0;
        }

        @media (max-width: 480px) {
            .btn-pwa-install .btn-pwa-text {
                display: none;
            }
            .btn-pwa-install {
                padding: 0.35rem;
                width: 32px;
                height: 32px;
                justify-content: center;
            }
        }

        /* ============================================================
         * SINCRONIZACIÓN OFFLINE Y NOTIFICACIONES FLOTANTES (TOAST)
         * ============================================================ */
        .btn-sync-pending {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-full);
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-size: var(--text-xs);
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35);
            white-space: nowrap;
            animation: sync-badge-pulse 2s infinite ease-in-out;
        }

        .btn-sync-pending:hover {
            background: linear-gradient(135deg, #f59e0b 0%, #92400e 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.45);
        }

        .btn-sync-pending.syncing svg {
            animation: sync-spin 0.8s linear infinite;
        }

        @keyframes sync-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes sync-badge-pulse {
            0%, 100% { box-shadow: 0 2px 6px rgba(217, 119, 6, 0.35); transform: scale(1); }
            50% { box-shadow: 0 0 10px rgba(245, 158, 11, 0.65); transform: scale(1.02); }
        }

        @media (max-width: 480px) {
            .btn-sync-pending .sync-pending-label {
                display: none;
            }
        }

        /* Notificaciones Flotantes (Toast) */
        .fundo-toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
            max-width: 92vw;
            width: 400px;
        }

        .fundo-toast {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: var(--radius-lg);
            background: var(--clr-surface-0);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--brd-base);
            border-left: 5px solid var(--clr-primary-600);
            font-size: var(--text-sm);
            font-weight: 500;
            color: var(--txt-primary);
            line-height: 1.4;
            opacity: 0;
            transform: translateY(-12px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .fundo-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .fundo-toast-success {
            border-left-color: var(--clr-success);
        }

        .fundo-toast-warning {
            border-left-color: #d97706;
            background: #fffbeb;
        }

        .fundo-toast-danger {
            border-left-color: var(--clr-danger);
            background: #fef2f2;
        }

        /* Modal iOS Safari */
        .ios-install-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10000;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .ios-install-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .ios-install-sheet {
            position: relative;
            background: var(--clr-surface-0);
            border: 1px solid var(--brd-base);
            border-radius: 1.25rem 1.25rem 0 0;
            width: 100%;
            max-width: 500px;
            padding: 1.5rem;
            box-shadow: 0 -10px 25px rgba(0, 0, 0, 0.25);
            animation: sheetSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 10001;
        }

        @keyframes sheetSlideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        .ios-install-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.875rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--brd-base);
        }

        .ios-install-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            color: var(--txt-muted);
            cursor: pointer;
            padding: 0.25rem;
        }

        .ios-step-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0;
            border-bottom: 1px solid var(--brd-base);
            font-size: var(--text-sm);
            color: var(--txt-secondary);
        }

        .ios-step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--clr-primary-100);
            color: var(--clr-primary-800);
            font-weight: 700;
            font-size: var(--text-xs);
            flex-shrink: 0;
        }

        .ios-install-footer {
            margin-top: 1.25rem;
        }
    </style>
