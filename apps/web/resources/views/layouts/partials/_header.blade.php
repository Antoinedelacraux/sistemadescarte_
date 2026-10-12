{{-- HEADER DE LA APLICACIÓN --}}
<header class="app-header" role="banner">
    {{-- Botón hamburguesa --}}
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
        {{-- Botón de instalación PWA (visible al dispararse prompt o en móviles compatibles) --}}
        <button type="button"
                id="btn-pwa-install"
                class="btn-pwa-install"
                style="display:none;"
                onclick="triggerPwaInstall()"
                title="Instalar aplicación en dispositivo"
                aria-label="Descargar e instalar TALSA Fundo como aplicación en tu pantalla">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            <span class="btn-pwa-text">Instalar App</span>
        </button>

        {{-- Botón / Contador de sincronización offline --}}
        <button type="button"
                id="btn-sync-pending"
                class="btn-sync-pending"
                style="display:none;"
                onclick="window.FundoOffline && window.FundoOffline.syncNow()"
                title="Sincronizar ventas guardadas en memoria local"
                aria-label="Sincronizar registros locales">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
            </svg>
            <span id="sync-pending-count">0</span>
            <span class="sync-pending-label">pendientes</span>
        </button>

        {{-- Indicador de red --}}
        <div class="net-indicator online" id="net-indicator" role="status" aria-live="polite" aria-label="Estado de conexión: en línea">
            <div class="net-dot" aria-hidden="true"></div>
            <span id="net-label">En línea</span>
        </div>

        {{-- Chip de fundo activo --}}
        @php
            $fundosHeader = Auth::user()->fundos;
            $isGlobal = Auth::user()->hasAccessToAllFundos();
        @endphp
        @if($isGlobal)
            <div class="fundo-chip" title="Acceso a todos los fundos">
                🌐 Todos los Fundos
            </div>
        @elseif($fundosHeader->count() === 1)
            <div class="fundo-chip" title="{{ $fundosHeader->first()->nombre_completo }}">
                🏡 {{ $fundosHeader->first()->nombre_corto }}
            </div>
        @elseif($fundosHeader->count() > 1)
            <div class="fundo-chip" title="Fundos asignados: {{ $fundosHeader->pluck('name')->join(', ') }}">
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
                    <div style="font-size: var(--text-xs); color: var(--txt-muted); margin-bottom: 0.375rem;">{{ Auth::user()->email }}</div>
                    <span class="role-badge role-badge-{{ Auth::user()->role?->name ?? 'default' }}">
                        {{ Auth::user()->role?->display_name ?? 'Sin Rol' }}
                    </span>
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
