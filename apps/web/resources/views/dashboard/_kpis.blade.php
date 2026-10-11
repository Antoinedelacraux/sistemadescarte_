{{-- 2. RESUMEN DE NÚMEROS CLAVE (KPIS) --}}
<div class="dashboard-grid" role="region" aria-label="Resumen de producción">
    <div class="stat-card">
        <div class="stat-icon green" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v18"/><path d="m3 7 9-4 9 4"/><path d="m6 13-3-6h6l-3 6a3 3 0 0 1-3-3"/><path d="m18 13-3-6h6l-3 6a3 3 0 0 1-3-3"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ number_format($totalGeneralKg, 2) }} kg</div>
            <div class="stat-label">Kilogramos Totales de Descarte</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div class="stat-value">S/ {{ number_format($totalGeneralVenta, 2) }}</div>
            <div class="stat-label">Valor Total en Ventas</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
        </div>
        <div>
            <div class="stat-value">{{ $ventas->count() }}</div>
            <div class="stat-label">Envíos / Pesajes Registrados</div>
        </div>
    </div>
</div>
