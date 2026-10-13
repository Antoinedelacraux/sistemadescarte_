{{-- HERO UNIFICADO: SALUDO Y KPIS EN 1 SOLA FILA (PROPUESTA 2) --}}
{{-- Diseñado para eliminar el scroll vertical y ubicar los totales en primer plano --}}
<div class="fusion-hero-grid" role="region" aria-label="Resumen ejecutivo del fundo">
    {{-- Columna 1: Tarjeta de Bienvenida y Contexto --}}
    <div class="fusion-welcome-card">
        <div class="fusion-header-tag">
            <img src="/images/logo-emblem-white.webp" alt="TALSA" width="16" height="16" style="object-fit: contain;">
            <span>TALSA GRAPE FARMS</span>
        </div>
        <div class="fusion-name">Hola, {{ $user->name }}</div>
        <div class="fusion-meta">
            <span>{{ now()->translatedFormat('l, d \d\e F') }}</span>
            <span class="fusion-meta-sep">•</span>
            @if(Auth::user()->hasAccessToAllFundos())
                <span class="fusion-meta-fundo">Todos los fundos</span>
            @elseif($fundos->count())
                <span class="fusion-meta-fundo">{{ $fundos->map(fn($f) => $f->nombre_completo ?: $f->name)->join(', ') }}</span>
            @endif
        </div>
    </div>

    {{-- Columna 2: KPI Kilogramos Totales --}}
    <div class="fusion-kpi-card">
        <div class="stat-icon green" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="m3 7 9-4 9 4"/><path d="m6 13-3-6h6l-3 6a3 3 0 0 1-3-3"/><path d="m18 13-3-6h6l-3 6a3 3 0 0 1-3-3"/></svg>
        </div>
        <div style="min-width: 0;">
            <div class="stat-value">{{ number_format($totalGeneralKg, 2) }} kg</div>
            <div class="stat-label">Kilogramos Totales</div>
        </div>
    </div>

    {{-- Columna 3: KPI Valor Total en Ventas --}}
    <div class="fusion-kpi-card">
        <div class="stat-icon yellow" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="9.5" stroke-width="1.8"/>
                <text x="12" y="12.5" text-anchor="middle" dominant-baseline="central" font-size="9.5" font-weight="800" font-family="Inter, -apple-system, BlinkMacSystemFont, sans-serif" fill="currentColor" stroke="none">S/</text>
            </svg>
        </div>
        <div style="min-width: 0;">
            <div class="stat-value">S/ {{ number_format($totalGeneralVenta, 2) }}</div>
            <div class="stat-label">Valor Total en Ventas</div>
        </div>
    </div>
</div>
