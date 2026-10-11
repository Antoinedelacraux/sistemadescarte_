{{-- 5. ÚLTIMOS REGISTROS REALIZADOS --}}
<section class="dash-section" id="sec-recientes-container" aria-labelledby="sec-title-recientes">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-recientes">
                        Últimos Envíos Registrados
                    </h2>
                    <div class="section-subtitle">
                        Movimientos recientes en el fundo
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('dashboard.exportar', ['tabla' => 'recientes']) }}" class="btn btn-secondary btn-sm" title="Descargar envíos recientes a Excel (.xlsx)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </a>
                    <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-sm">
                        Ver Historial Completo &rarr;
                    </a>
                </div>
            </div>
        </div>

        @if($ventas->isEmpty())
            <div class="empty-state" role="status">
                <div class="empty-icon" aria-hidden="true">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                <div class="empty-title">Sin registros visibles</div>
                <p class="empty-desc">No existen ventas de descarte registradas aún.</p>
            </div>
        @else
            <div class="table-wrapper" style="border: none; border-radius: 0;">
                <table class="table-clean" role="table" aria-label="Envíos recientes">
                    <thead>
                        <tr>
                            <th scope="col">Fundo</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Cliente</th>
                            <th scope="col" style="min-width: 140px;">Origen</th>
                            <th scope="col">Tipo de Descarte</th>
                            <th scope="col" class="text-right">Precio/Kg</th>
                            <th scope="col" class="text-right">Kilos</th>
                            <th scope="col" class="text-right">Total (S/)</th>
                            <th scope="col">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ventas->take(8) as $v)
                        <tr>
                            <td>
                                <strong style="color: var(--txt-primary);">{{ $v->fundo?->nombre_corto ?? $v->fundo?->name ?? '—' }}</strong>
                            </td>
                            <td style="white-space: nowrap; color: var(--txt-secondary); font-size: var(--text-xs);">
                                {{ $v->fecha_produccion->format('d/m/Y') }}
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--txt-primary);">
                                    {{ $v->cliente ?: ($v->ruc ? 'RUC: '.$v->ruc : 'Venta General') }}
                                </span>
                            </td>
                            <td>
                                @php $bClass = $v->motivo === 'Cosecha Nacional' ? 'amber' : ($v->motivo === 'Packing' ? 'blue' : 'green'); @endphp
                                <span class="motivo-pill motivo-pill-{{ $bClass }}">
                                    {{ $v->motivo }}
                                </span>
                            </td>
                            <td>{{ $v->tipo_descarte }}</td>
                            <td class="text-right" style="font-family: monospace;">S/ {{ number_format($v->precio, 2) }}</td>
                            <td class="text-right" style="white-space: nowrap; font-family: monospace; font-weight: 600;">{{ number_format($v->kilogramos, 2) }} kg</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">
                                S/ {{ number_format($v->valor_venta, 2) }}
                            </td>
                            <td style="color: var(--txt-muted); font-size: var(--text-xs);">
                                {{ $v->creator->name ?? 'Sistema' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>
