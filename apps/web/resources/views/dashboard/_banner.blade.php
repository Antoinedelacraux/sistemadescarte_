{{-- 1. HERO BANNER DE BIENVENIDA Y ACCESO RÁPIDO --}}
<div class="welcome-banner">
    <div class="welcome-banner-info">
        <div class="welcome-greeting" style="display: flex; align-items: center; gap: 0.5rem;">
            <img src="/images/logo-emblem.webp" alt="TALSA" width="20" height="20" style="object-fit: contain; background: transparent; filter: drop-shadow(0 1px 3px rgba(0,0,0,0.4)) drop-shadow(0 0 6px rgba(255,255,255,0.4));">
            <span>TALSA Grape Farms &bull; Gestión Agrícola</span>
        </div>
        <div class="welcome-name">Hola, {{ $user->name }}</div>
        <div class="welcome-meta">
            <div class="welcome-meta-item">
                <span>{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</span>
            </div>
            @if(Auth::user()->hasAccessToAllFundos())
                <div class="welcome-meta-item" style="color: #bbf7d0;">
                    <span>Acceso a todos los fundos</span>
                </div>
            @elseif($fundos->count())
                <div class="welcome-meta-item" style="color: #bbf7d0;">
                    <span>{{ $fundos->map(fn($f) => $f->nombre_completo ?: $f->name)->join(', ') }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Botones de acción directa para trabajadores --}}
    <div class="welcome-actions">
        @if(Auth::user()->canAccessModule('registro'))
            <a href="{{ route('ventas.create') }}" class="btn-hero-primary" id="btn-quick-create">
                <span>Registrar Venta</span>
            </a>
        @endif
        @if(Auth::user()->canAccessModule('historial'))
        <a href="{{ route('ventas.index') }}" class="btn-hero-secondary">
            <span>Ver Historial</span>
        </a>
        @endif
        <a href="{{ route('dashboard.exportar', array_filter(['tabla' => 'completo', 'fundo_id' => $fundoSeleccionadoId ?? null])) }}" class="btn-hero-secondary" title="Descargar reporte consolidado del panel en Excel (.xlsx)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Exportar Excel</span>
        </a>
    </div>
</div>
