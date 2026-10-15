{{-- 5. TABLA 3: RESUMEN DE KILOGRAMOS POR LOTE --}}
<section class="dash-section" id="sec-lotes-container" aria-labelledby="sec-title-lotes" style="display: none;">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-lotes">Resumen de Ventas por Lote</h2>
                    <div class="section-subtitle">
                        Kilogramos vendidos por fundo, lote, motivo y tipo de descarte
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="exportarLotesExcel()" title="Descargar resumen por lote a Excel (.xlsx)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="client-controls-bar">
            <div class="search-box-client" style="max-width: 380px;">
                <input type="search" id="lote-matrix-search" placeholder="Buscar lote o fundo..." oninput="filtrarMatrizLotes()" aria-label="Buscar lote o fundo">
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                <span class="badge-tipo" id="matrix-lotes-count">{{ count($matrizLotes) }} lote(s) registrados</span>
            </div>
        </div>

        <div style="padding: 1.25rem 1.25rem 1.5rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; gap: 0.75rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-weight: 800; font-size: 0.875rem; letter-spacing: 0.04em; color: #ea580c; background: #fff7ed; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid #ffedd5;">KG</span>
                    <strong style="color: var(--txt-primary); font-size: 0.95rem;">Kilogramos por lote y descarte</strong>
                </div>
                <span class="text-xs text-muted">Ordenado de mayor a menor cantidad</span>
            </div>

            <div class="table-wrapper" style="border: 1px solid var(--brd-base); border-radius: var(--radius-md); overflow-x: auto;">
                <table class="matrix-table matrix-lotes-table" id="table-matrix-lotes" role="table" aria-label="Matriz de kilogramos por lote">
                    <thead>
                        <tr class="matrix-header-row-1">
                            <th rowspan="2" class="matrix-th-client" style="min-width: 145px; width: 145px;">FUNDO</th>
                            <th rowspan="2" class="matrix-th-client" style="min-width: 115px; width: 115px;">LOTE</th>
                            @foreach($columnasMatriz as $motivoKey => $colDef)
                                <th colspan="{{ count($colDef['tipos']) }}" class="matrix-th-motivo matrix-th-{{ $colDef['badge'] }}">{{ $colDef['titulo'] }}</th>
                            @endforeach
                            <th rowspan="2" class="matrix-th-total">TOTAL KG</th>
                        </tr>
                        <tr class="matrix-header-row-2">
                            @foreach($columnasMatriz as $colDef)
                                @foreach($colDef['tipos'] as $tipo)
                                    <th class="matrix-th-sub">{{ strtoupper($tipo) }}</th>
                                @endforeach
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="matrix-lotes-tbody">
                        @forelse($matrizLotes as $lote)
                            <tr class="matrix-lote-row" data-lote-name="{{ strtolower($lote['lote']) }}" data-fundo-name="{{ strtolower($lote['fundo']) }}">
                                <td class="matrix-td-client"><strong style="color: var(--txt-primary);">{{ $lote['fundo'] }}</strong></td>
                                <td class="matrix-td-client"><strong style="color: var(--txt-primary);">{{ $lote['lote'] }}</strong></td>
                                @foreach($columnasMatriz as $motivoKey => $colDef)
                                    @foreach($colDef['tipos'] as $tipo)
                                        @php $kg = (float) ($lote['kg'][$motivoKey][$tipo] ?? 0); @endphp
                                        <td class="matrix-td-val {{ $kg > 0 ? 'has-val' : 'empty-val' }}">{{ $kg > 0 ? number_format($kg, 2) : '—' }}</td>
                                    @endforeach
                                @endforeach
                                <td class="matrix-td-total"><strong>{{ number_format($lote['total_kg'], 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr class="matrix-empty-row">
                                <td colspan="10" style="text-align: center; padding: 2.5rem; color: var(--txt-muted);">No hay registros de ventas para clasificar por lote.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($matrizLotes) > 0)
                    <tfoot>
                        <tr class="matrix-tfoot-row">
                            <td colspan="2" class="matrix-tf-client"><strong>TOTAL GENERAL</strong></td>
                            @foreach($columnasMatriz as $motivoKey => $colDef)
                                @foreach($colDef['tipos'] as $tipo)
                                    @php $totalKg = (float) ($totalesColumnasLotesKg[$motivoKey][$tipo] ?? 0); @endphp
                                    <td class="matrix-tf-val"><strong>{{ $totalKg > 0 ? number_format($totalKg, 2) : '—' }}</strong></td>
                                @endforeach
                            @endforeach
                            <td class="matrix-tf-total"><strong>{{ number_format($granTotalLotesKg, 2) }} kg</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            <div id="matrix-lotes-empty-filter" style="display: none; text-align: center; padding: 2rem; color: var(--txt-muted);">No se encontró ningún lote o fundo.</div>
        </div>
    </div>
</section>
