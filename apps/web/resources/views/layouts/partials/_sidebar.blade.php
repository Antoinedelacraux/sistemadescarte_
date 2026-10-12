{{-- OVERLAY MÓVIL --}}
<div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true" onclick="toggleSidebar()"></div>

{{-- SIDEBAR --}}
<aside class="sidebar" id="main-sidebar" role="navigation" aria-label="Menú principal">
    {{-- Brand (Emblema transparente de la empresa) --}}
    <a href="{{ route('dashboard') }}" class="sidebar-brand" aria-label="Inicio — TALSA Grape Farms">
        <div class="sidebar-brand-icon" aria-hidden="true">
            <img src="/images/logo-emblem.webp" alt="TALSA" width="36" height="36" class="sidebar-brand-img">
        </div>
        <div class="sidebar-brand-text">
            <strong>TALSA Grape Farms</strong>
            <span>Gestión Agrícola</span>
        </div>
    </a>

    {{-- Navegación --}}
    <nav class="sidebar-nav" aria-label="Navegación principal">
        <div class="nav-section-label" aria-hidden="true">Operación</div>

        @if(Auth::user()->canAccessModule('panel'))
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
            </span>
            <span class="nav-item-label">Panel de Control</span>
        </a>
        @endif

        @if(Auth::user()->canAccessModule('registro'))
        <a href="{{ route('ventas.create') }}"
           class="nav-item {{ request()->routeIs('ventas.create') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('ventas.create') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            </span>
            <span class="nav-item-label">Registrar Venta</span>
        </a>
        @endif

        @if(Auth::user()->canAccessModule('historial'))
        <a href="{{ route('ventas.index') }}"
           class="nav-item {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.edit') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('ventas.index') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
            </span>
            <span class="nav-item-label">Historial de Ventas</span>
        </a>
        @endif

        @if(Auth::user()->canAccessModule('reportes'))
        <div class="nav-section-label" aria-hidden="true">Análisis</div>
        <a href="{{ route('reportes.index') }}"
           class="nav-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('reportes.*') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </span>
            <span class="nav-item-label">Reportes y Exportación</span>
        </a>
        @endif

        @if(Auth::user()->canAccessModule('admin'))
        <div class="nav-section-label" aria-hidden="true">Administración</div>
        <a href="{{ route('admin.fundos') }}"
           class="nav-item {{ request()->routeIs('admin.fundos*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('admin.fundos*') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </span>
            <span class="nav-item-label">Fundos</span>
        </a>
        <a href="{{ route('admin.usuarios') }}"
           class="nav-item {{ request()->routeIs('admin.usuarios*') ? 'active' : '' }}"
           aria-current="{{ request()->routeIs('admin.usuarios*') ? 'page' : 'false' }}">
            <span class="nav-item-icon" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <span class="nav-item-label">Usuarios</span>
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
