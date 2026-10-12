{{-- 4. TABLA 2: RESUMEN POR CLIENTE (MATRICES CRUZADAS DE VOLUMEN Y MONTOS) --}}
<section class="dash-section" id="sec-clientes-container" aria-labelledby="sec-title-clientes">
    <div class="dash-table-card">
        {{-- Cabecera de la sección --}}
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-clientes">
                        Resumen de Ventas por Cliente
                    </h2>
                    <div class="section-subtitle">
                        Matriz comparativa de volumen (Kilogramos) e ingresos económicos (Soles) por motivo y tipo de descarte
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    {{-- Selector de vista de matrices --}}
                    <div class="view-toggle-btns" role="group" aria-label="Visualización de matrices">
                        <button type="button" id="btn-matriz-all" class="view-toggle-btn active" onclick="cambiarSubvistaMatriz('todas')" title="Ver ambas tablas">
                            Todas
                        </button>
                        <button type="button" id="btn-matriz-kg" class="view-toggle-btn" onclick="cambiarSubvistaMatriz('kg')" title="Ver solo tabla de Kilogramos">
                            Kilogramos (KG)
                        </button>
                        <button type="button" id="btn-matriz-monto" class="view-toggle-btn" onclick="cambiarSubvistaMatriz('monto')" title="Ver solo tabla de Montos">
                            Montos (S/.)
                        </button>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" onclick="exportarClientesExcel()" title="Descargar resumen de clientes filtrado a Excel (.xlsx)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </button>
                </div>
            </div>
        </div>

        {{-- Barra de búsqueda y controles --}}
        <div class="client-controls-bar">
            <div class="search-box-client" style="max-width: 380px;">
                <input
                    type="search"
                    id="client-matrix-search"
                    placeholder="Buscar cliente por nombre..."
                    oninput="filtrarMatricesClientes()"
                    aria-label="Buscar cliente"
                >
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                <span class="badge-tipo" id="matrix-clients-count">
                    {{ count($matrizClientes) }} cliente(s) registrados
                </span>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TABLA 1: MATRIZ DE KILOGRAMOS (KG)                                       --}}
        {{-- ========================================================================= --}}
        <div id="container-matriz-kg" style="padding: 1.25rem 1.25rem 1.5rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-weight: 800; font-size: 0.875rem; letter-spacing: 0.04em; color: #ea580c; background: #fff7ed; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid #ffedd5;">KG</span>
                    <strong style="color: var(--txt-primary); font-size: 0.95rem;">Distribución por Volumen — Kilogramos</strong>
                </div>
                <span class="text-xs text-muted">Valores expresados en Kilogramos (kg)</span>
            </div>

            <div class="table-wrapper" style="border: 1px solid var(--brd-base); border-radius: var(--radius-md); overflow-x: auto;">
                <table class="matrix-table" id="table-matrix-kg" role="table" aria-label="Matriz de Clientes en Kilogramos">
                    <thead>
                        {{-- Fila 1 de Encabezados (Motivos de Descarte) --}}
                        <tr class="matrix-header-row-1">
                            <th rowspan="2" class="matrix-th-client">CLIENTE</th>
                            <th colspan="3" class="matrix-th-motivo matrix-th-campo">DESCARTE CAMPO</th>
                            <th colspan="2" class="matrix-th-motivo matrix-th-packing">DESCARTE PACKING</th>
                            <th colspan="2" class="matrix-th-motivo matrix-th-nacional">COSECHA NACIONAL</th>
                            <th rowspan="2" class="matrix-th-total">TOTAL KG</th>
                        </tr>
                        {{-- Fila 2 de Encabezados (Tipos de Descarte) --}}
                        <tr class="matrix-header-row-2">
                            {{-- Campo --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                            <th class="matrix-th-sub">RACIMOS CON PLAGA</th>
                            {{-- Packing --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                            {{-- Nacional --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                        </tr>
                    </thead>
                    <tbody id="matrix-kg-tbody">
                        @forelse($matrizClientes as $c)
                            <tr class="matrix-client-row" data-client-name="{{ strtolower($c['cliente']) }}" data-ruc="{{ $c['ruc'] ?? '' }}">
                                <td class="matrix-td-client">
                                    <strong style="color: var(--txt-primary);">{{ $c['cliente'] }}</strong>
                                </td>
                                {{-- Campo --}}
                                @php $kgCampoGranos = $c['kg']['Campo']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgCampoGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgCampoGranos > 0 ? number_format($kgCampoGranos, 2) : '—' }}</td>
                                @php $kgCampoRacimos = $c['kg']['Campo']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgCampoRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgCampoRacimos > 0 ? number_format($kgCampoRacimos, 2) : '—' }}</td>
                                @php $kgCampoPlaga = $c['kg']['Campo']['Racimos con Plaga'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgCampoPlaga > 0 ? 'has-val' : 'empty-val' }}">{{ $kgCampoPlaga > 0 ? number_format($kgCampoPlaga, 2) : '—' }}</td>

                                {{-- Packing --}}
                                @php $kgPackGranos = $c['kg']['Packing']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgPackGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgPackGranos > 0 ? number_format($kgPackGranos, 2) : '—' }}</td>
                                @php $kgPackRacimos = $c['kg']['Packing']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgPackRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgPackRacimos > 0 ? number_format($kgPackRacimos, 2) : '—' }}</td>

                                {{-- Cosecha Nacional --}}
                                @php $kgNacGranos = $c['kg']['Cosecha Nacional']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgNacGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgNacGranos > 0 ? number_format($kgNacGranos, 2) : '—' }}</td>
                                @php $kgNacRacimos = $c['kg']['Cosecha Nacional']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $kgNacRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $kgNacRacimos > 0 ? number_format($kgNacRacimos, 2) : '—' }}</td>

                                {{-- Total Cliente --}}
                                <td class="matrix-td-total">
                                    <strong>{{ number_format($c['total_kg'], 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr class="matrix-empty-row">
                                <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--txt-muted);">
                                    No hay registros de ventas para clasificar por cliente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($matrizClientes) > 0)
                    <tfoot>
                        <tr class="matrix-tfoot-row">
                            <td class="matrix-tf-client"><strong>TOTAL GENERAL</strong></td>
                            {{-- Campo --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Campo']['Granos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Campo']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Campo']['Racimos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Campo']['Racimos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Campo']['Racimos con Plaga'] ?? 0) > 0 ? number_format($totalesColumnasKg['Campo']['Racimos con Plaga'], 2) : '—' }}</strong></td>
                            {{-- Packing --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Packing']['Granos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Packing']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Packing']['Racimos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Packing']['Racimos'], 2) : '—' }}</strong></td>
                            {{-- Nacional --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Cosecha Nacional']['Granos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Cosecha Nacional']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasKg['Cosecha Nacional']['Racimos'] ?? 0) > 0 ? number_format($totalesColumnasKg['Cosecha Nacional']['Racimos'], 2) : '—' }}</strong></td>
                            {{-- Gran Total --}}
                            <td class="matrix-tf-total"><strong>{{ number_format($granTotalMatrizKg, 2) }} kg</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Separador sutil si se muestran ambas --}}
        <div id="matrix-separator" style="border-top: 1px dashed var(--brd-base); margin: 0 1.25rem;"></div>

        {{-- ========================================================================= --}}
        {{-- TABLA 2: MATRIZ DE MONTOS (S/. SOLES)                                     --}}
        {{-- ========================================================================= --}}
        <div id="container-matriz-monto" style="padding: 1.5rem 1.25rem 1.75rem 1.25rem;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="font-weight: 800; font-size: 0.875rem; letter-spacing: 0.04em; color: #166534; background: #f0fdf4; padding: 0.25rem 0.6rem; border-radius: 6px; border: 1px solid #dcfce7;">S/</span>
                    <strong style="color: var(--txt-primary); font-size: 0.95rem;">Distribución Económica — Montos (Soles)</strong>
                </div>
                <span class="text-xs text-muted">Valores expresados en Moneda Nacional (PEN / S/)</span>
            </div>

            <div class="table-wrapper" style="border: 1px solid var(--brd-base); border-radius: var(--radius-md); overflow-x: auto;">
                <table class="matrix-table" id="table-matrix-monto" role="table" aria-label="Matriz de Clientes en Montos">
                    <thead>
                        {{-- Fila 1 de Encabezados (Motivos de Descarte) --}}
                        <tr class="matrix-header-row-1">
                            <th rowspan="2" class="matrix-th-client">CLIENTE</th>
                            <th colspan="3" class="matrix-th-motivo matrix-th-campo">DESCARTE CAMPO</th>
                            <th colspan="2" class="matrix-th-motivo matrix-th-packing">DESCARTE PACKING</th>
                            <th colspan="2" class="matrix-th-motivo matrix-th-nacional">COSECHA NACIONAL</th>
                            <th rowspan="2" class="matrix-th-total">TOTAL S/</th>
                        </tr>
                        {{-- Fila 2 de Encabezados (Tipos de Descarte) --}}
                        <tr class="matrix-header-row-2">
                            {{-- Campo --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                            <th class="matrix-th-sub">RACIMOS CON PLAGA</th>
                            {{-- Packing --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                            {{-- Nacional --}}
                            <th class="matrix-th-sub">GRANOS</th>
                            <th class="matrix-th-sub">RACIMOS</th>
                        </tr>
                    </thead>
                    <tbody id="matrix-monto-tbody">
                        @forelse($matrizClientes as $c)
                            <tr class="matrix-client-row" data-client-name="{{ strtolower($c['cliente']) }}" data-ruc="{{ $c['ruc'] ?? '' }}">
                                <td class="matrix-td-client">
                                    <strong style="color: var(--txt-primary);">{{ $c['cliente'] }}</strong>
                                </td>
                                {{-- Campo --}}
                                @php $montoCampoGranos = $c['monto']['Campo']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoCampoGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoCampoGranos > 0 ? 'S/ ' . number_format($montoCampoGranos, 2) : '—' }}</td>
                                @php $montoCampoRacimos = $c['monto']['Campo']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoCampoRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoCampoRacimos > 0 ? 'S/ ' . number_format($montoCampoRacimos, 2) : '—' }}</td>
                                @php $montoCampoPlaga = $c['monto']['Campo']['Racimos con Plaga'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoCampoPlaga > 0 ? 'has-val' : 'empty-val' }}">{{ $montoCampoPlaga > 0 ? 'S/ ' . number_format($montoCampoPlaga, 2) : '—' }}</td>

                                {{-- Packing --}}
                                @php $montoPackGranos = $c['monto']['Packing']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoPackGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoPackGranos > 0 ? 'S/ ' . number_format($montoPackGranos, 2) : '—' }}</td>
                                @php $montoPackRacimos = $c['monto']['Packing']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoPackRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoPackRacimos > 0 ? 'S/ ' . number_format($montoPackRacimos, 2) : '—' }}</td>

                                {{-- Cosecha Nacional --}}
                                @php $montoNacGranos = $c['monto']['Cosecha Nacional']['Granos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoNacGranos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoNacGranos > 0 ? 'S/ ' . number_format($montoNacGranos, 2) : '—' }}</td>
                                @php $montoNacRacimos = $c['monto']['Cosecha Nacional']['Racimos'] ?? 0; @endphp
                                <td class="matrix-td-val {{ $montoNacRacimos > 0 ? 'has-val' : 'empty-val' }}">{{ $montoNacRacimos > 0 ? 'S/ ' . number_format($montoNacRacimos, 2) : '—' }}</td>

                                {{-- Total Cliente --}}
                                <td class="matrix-td-total" style="color: var(--clr-primary-700);">
                                    <strong>S/ {{ number_format($c['total_monto'], 2) }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr class="matrix-empty-row">
                                <td colspan="9" style="text-align: center; padding: 2.5rem; color: var(--txt-muted);">
                                    No hay registros de ventas para clasificar por cliente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($matrizClientes) > 0)
                    <tfoot>
                        <tr class="matrix-tfoot-row">
                            <td class="matrix-tf-client"><strong>TOTAL GENERAL</strong></td>
                            {{-- Campo --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Campo']['Granos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Campo']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Campo']['Racimos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Campo']['Racimos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Campo']['Racimos con Plaga'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Campo']['Racimos con Plaga'], 2) : '—' }}</strong></td>
                            {{-- Packing --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Packing']['Granos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Packing']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Packing']['Racimos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Packing']['Racimos'], 2) : '—' }}</strong></td>
                            {{-- Nacional --}}
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Cosecha Nacional']['Granos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Cosecha Nacional']['Granos'], 2) : '—' }}</strong></td>
                            <td class="matrix-tf-val"><strong>{{ ($totalesColumnasMonto['Cosecha Nacional']['Racimos'] ?? 0) > 0 ? 'S/ ' . number_format($totalesColumnasMonto['Cosecha Nacional']['Racimos'], 2) : '—' }}</strong></td>
                            {{-- Gran Total --}}
                            <td class="matrix-tf-total" style="color: var(--clr-primary-700);"><strong>S/ {{ number_format($granTotalMatrizMonto, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</section>
