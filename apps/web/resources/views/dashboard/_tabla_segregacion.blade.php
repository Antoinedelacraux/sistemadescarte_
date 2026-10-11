{{-- 3. TABLA 1: RESUMEN POR ORIGEN Y TIPO DE DESCARTE --}}
<section class="dash-section" id="sec-motivos-container" aria-labelledby="sec-title-motivos">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-motivos">
                        Resumen por Tipo de Venta y Descarte
                    </h2>
                    <div class="section-subtitle">
                        Totales acumulados en Venta Nacional (Racimos y Granos), Venta Campo y Venta Packing
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('dashboard.exportar', ['tabla' => 'motivos']) }}" class="btn btn-secondary btn-sm" title="Descargar este resumen por tipo y descarte a Excel (.xlsx)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </a>
                </div>
            </div>
        </div>

        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="table-clean" role="table" aria-label="Resumen por Tipo de Venta y Descarte">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 180px;">Origen / Tipo</th>
                        <th scope="col" class="text-center" style="width: 90px;">Envíos</th>
                        <th scope="col" class="text-right" style="width: 140px;">Kilogramos (kg)</th>
                        <th scope="col" style="width: 130px;">% Volumen</th>
                        <th scope="col" class="text-right" style="width: 130px;">Precio Prom.</th>
                        <th scope="col" class="text-right" style="width: 140px;">Total (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($segregacionMotivos as $motivoKey => $mInfo)
                        @php $meta = $mInfo['meta']; @endphp

                        {{-- Fila agrupada de cabecera --}}
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="6" style="padding-top: 0.75rem; padding-bottom: 0.4rem;">
                                <span class="motivo-pill motivo-pill-{{ $meta['badge'] }}">
                                    <span>{{ $meta['titulo'] }}</span>
                                </span>
                            </td>
                        </tr>

                        {{-- Filas de cada tipo permitido --}}
                        @foreach($mInfo['tipos'] as $tipoRow)
                        <tr>
                            <td style="padding-left: 2rem;">
                                <span style="color: var(--txt-primary); font-weight: 500;">
                                    &bull; {{ $tipoRow['tipo_descarte'] }}
                                </span>
                            </td>
                            <td class="text-center" style="color: var(--txt-muted);">
                                {{ $tipoRow['count'] }}
                            </td>
                            <td class="text-right" style="font-weight: 600; font-family: monospace;">
                                {{ number_format($tipoRow['kilogramos'], 2) }}
                            </td>
                            <td>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-fill progress-fill-{{ $meta['badge'] }}" style="width: {{ min(100, $tipoRow['porcentaje_kg']) }}%;"></div>
                                </div>
                                <span style="font-size: 0.6875rem; color: var(--txt-secondary);">{{ $tipoRow['porcentaje_kg'] }}%</span>
                            </td>
                            <td class="text-right" style="color: var(--txt-secondary); font-family: monospace;">
                                S/ {{ number_format($tipoRow['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 600; color: var(--txt-primary); font-family: monospace;">
                                S/ {{ number_format($tipoRow['valor_venta'], 2) }}
                            </td>
                        </tr>
                        @endforeach

                        {{-- Subtotal --}}
                        <tr class="row-subtotal">
                            <td style="padding-left: 1.5rem;">
                                <strong>Subtotal {{ $meta['titulo'] }}</strong>
                            </td>
                            <td class="text-center"><strong>{{ $mInfo['count'] }}</strong></td>
                            <td class="text-right" style="font-family: monospace;">
                                <strong>{{ number_format($mInfo['total_kg'], 2) }}</strong>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--txt-secondary);">{{ $mInfo['porcentaje_kg'] }}%</span>
                            </td>
                            <td class="text-right" style="font-family: monospace;">
                                S/ {{ number_format($mInfo['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-family: monospace; color: var(--clr-primary-700);">
                                <strong>S/ {{ number_format($mInfo['total_venta'], 2) }}</strong>
                            </td>
                        </tr>
                    @endforeach

                    {{-- Total General --}}
                    <tr class="row-grand-total">
                        <td>TOTAL GENERAL</td>
                        <td class="text-center">{{ $ventas->count() }}</td>
                        <td class="text-right" style="font-family: monospace;">{{ number_format($totalGeneralKg, 2) }} kg</td>
                        <td>100.0%</td>
                        <td class="text-right" style="font-family: monospace;">
                            S/ {{ $totalGeneralKg > 0 ? number_format($totalGeneralVenta / $totalGeneralKg, 2) : '0.00' }}
                        </td>
                        <td class="text-right text-highlight" style="font-family: monospace;">
                            S/ {{ number_format($totalGeneralVenta, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
