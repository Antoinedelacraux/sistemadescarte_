{{-- 4. TABLA 2: RESUMEN POR CLIENTE --}}
<section class="dash-section" id="sec-clientes-container" aria-labelledby="sec-title-clientes">
    <div class="dash-table-card">
        <div class="dash-table-header">
            <div class="section-header-box">
                <div>
                    <h2 class="section-title" id="sec-title-clientes">
                        Resumen de Ventas por Cliente
                    </h2>
                    <div class="section-subtitle">
                        Detalle de compras por cliente con desglose de Cosecha Nacional, Campo y Packing
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="expandAllClients(true)" title="Ver detalle de todos los clientes">
                        Abrir Todos
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="expandAllClients(false)" title="Ocultar detalles">
                        Cerrar
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="exportarClientesExcel()" title="Descargar resumen de clientes filtrado a Excel (.xlsx)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar Excel
                    </button>
                </div>
            </div>
        </div>

        {{-- Filtros y buscador --}}
        <div class="client-controls-bar">
            <div class="search-box-client">
                <input
                    type="search"
                    id="client-search-input"
                    placeholder="Buscar cliente o tipo..."
                    oninput="filtrarTablaClientes()"
                    aria-label="Buscar cliente"
                >
            </div>

            <div class="filter-chips-wrap" role="group" aria-label="Filtro de motivo">
                <button type="button" class="chip-btn active" data-filter="todos" onclick="setMotivoFilter('todos', this)">Todos</button>
                <button type="button" class="chip-btn" data-filter="Cosecha Nacional" onclick="setMotivoFilter('Cosecha Nacional', this)">Nacional</button>
                <button type="button" class="chip-btn" data-filter="Campo" onclick="setMotivoFilter('Campo', this)">Campo</button>
                <button type="button" class="chip-btn" data-filter="Packing" onclick="setMotivoFilter('Packing', this)">Packing</button>
            </div>

            <div class="view-toggle-btns" role="radiogroup" aria-label="Modo de vista">
                <button type="button" id="btn-view-grouped" class="view-toggle-btn active" onclick="switchClientView('grouped')">
                    Por Cliente
                </button>
                <button type="button" id="btn-view-flat" class="view-toggle-btn" onclick="switchClientView('flat')">
                    Lista Plana
                </button>
            </div>
        </div>

        <div class="table-wrapper" style="border: none; border-radius: 0;">
            {{-- MODO A: AGRUPADO POR CLIENTE --}}
            <table class="table-clean" id="table-clients-grouped" role="table" aria-label="Ventas por Cliente">
                <thead>
                    <tr>
                        <th scope="col" style="min-width: 220px;">Cliente</th>
                        <th scope="col" style="min-width: 175px; width: 180px;">Origen</th>
                        <th scope="col" style="width: 170px;">Tipo de Descarte</th>
                        <th scope="col" class="text-right" style="width: 130px;">Kilos (kg)</th>
                        <th scope="col" class="text-right" style="width: 130px;">Precio Prom.</th>
                        <th scope="col" class="text-right" style="width: 140px;">Total (S/)</th>
                    </tr>
                </thead>
                <tbody id="clients-grouped-tbody">
                    @forelse($clientesAgrupados as $clienteNombre => $cData)
                        {{-- Fila de Cliente --}}
                        <tr class="client-parent-row expanded"
                            id="row-client-{{ Str::slug($clienteNombre) }}"
                            data-client-name="{{ strtolower($clienteNombre) }}"
                            data-motivos="{{ implode(' ', $cData['motivos']) }}"
                            onclick="toggleClientRows('{{ Str::slug($clienteNombre) }}')">
                            <td>
                                <span class="client-toggle-caret" id="caret-{{ Str::slug($clienteNombre) }}" aria-hidden="true">&#9658;</span>
                                <strong style="color: var(--txt-primary); font-size: 0.875rem;">{{ $clienteNombre }}</strong>
                                @if(!empty($cData['ruc']) && !str_contains($clienteNombre, $cData['ruc']))
                                    <span style="display: block; font-size: 0.6875rem; color: var(--txt-muted); margin-left: 1.625rem;">
                                        RUC: {{ $cData['ruc'] }}
                                    </span>
                                @endif
                            </td>
                            <td style="vertical-align: middle;">
                                <div class="origen-pills-stack">
                                    @foreach($cData['motivos'] as $mName)
                                        @php $bClass = $mName === 'Cosecha Nacional' ? 'amber' : ($mName === 'Packing' ? 'blue' : 'green'); @endphp
                                        <span class="motivo-pill motivo-pill-{{ $bClass }}">
                                            {{ $mName }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td style="color: var(--txt-muted); font-size: var(--text-xs);">
                                {{ count($cData['desglose']) }} variante(s)
                            </td>
                            <td class="text-right" style="font-weight: 700; font-family: monospace;">
                                {{ number_format($cData['total_kg'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 600; color: var(--txt-secondary); font-family: monospace;">
                                S/ {{ number_format($cData['precio_promedio'], 2) }}
                            </td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">
                                S/ {{ number_format($cData['total_venta'], 2) }}
                            </td>
                        </tr>

                        {{-- Desglose por tipo --}}
                        @foreach($cData['desglose'] as $dKey => $dData)
                            @php $bClass = $dData['motivo'] === 'Cosecha Nacional' ? 'amber' : ($dData['motivo'] === 'Packing' ? 'blue' : 'green'); @endphp
                            <tr class="client-subrow subrow-{{ Str::slug($clienteNombre) }}"
                                data-client-name="{{ strtolower($clienteNombre) }}"
                                data-motivo="{{ $dData['motivo'] }}"
                                data-tipo="{{ strtolower($dData['tipo_descarte']) }}">
                                <td>
                                    <span class="client-subrow-bullet" aria-hidden="true">&#8627;</span>
                                    <span style="color: var(--txt-secondary);">Detalle</span>
                                </td>
                                <td>
                                    <span class="motivo-pill motivo-pill-{{ $bClass }}">
                                        {{ $dData['motivo'] }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--txt-primary);">
                                        {{ $dData['tipo_descarte'] }}
                                    </span>
                                    <span style="font-size: 0.6875rem; color: var(--txt-muted);">({{ $dData['count'] }})</span>
                                </td>
                                <td class="text-right" style="font-family: monospace;">
                                    {{ number_format($dData['kilogramos'], 2) }}
                                </td>
                                <td class="text-right" style="font-family: monospace; color: var(--txt-secondary);">
                                    S/ {{ number_format($dData['precio_promedio'], 2) }}
                                </td>
                                <td class="text-right" style="font-family: monospace; font-weight: 600; color: var(--txt-primary);">
                                    S/ {{ number_format($dData['valor_venta'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--txt-muted);">
                                No hay registros de ventas para clasificar por cliente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- MODO B: MATRIZ PLANA --}}
            <table class="table-clean" id="table-clients-flat" style="display: none;" role="table" aria-label="Lista Plana de Clientes">
                <thead>
                    <tr>
                        <th scope="col">Cliente</th>
                        <th scope="col">RUC</th>
                        <th scope="col">Origen</th>
                        <th scope="col">Tipo de Descarte</th>
                        <th scope="col" class="text-right">Kilos (kg)</th>
                        <th scope="col" class="text-right">Precio Prom.</th>
                        <th scope="col" class="text-right">Total (S/)</th>
                    </tr>
                </thead>
                <tbody id="clients-flat-tbody">
                    @forelse($filasClientesPlanas as $fila)
                        @php $bClass = $fila['motivo'] === 'Cosecha Nacional' ? 'amber' : ($fila['motivo'] === 'Packing' ? 'blue' : 'green'); @endphp
                        <tr class="flat-client-row"
                            data-client-name="{{ strtolower($fila['cliente']) }}"
                            data-motivo="{{ $fila['motivo'] }}"
                            data-tipo="{{ strtolower($fila['tipo_descarte']) }}">
                            <td><strong style="color: var(--txt-primary);">{{ $fila['cliente'] }}</strong></td>
                            <td style="color: var(--txt-muted); font-size: var(--text-xs);">{{ $fila['ruc'] ?? '—' }}</td>
                            <td><span class="motivo-pill motivo-pill-{{ $bClass }}">{{ $fila['motivo'] }}</span></td>
                            <td><span style="font-weight: 600;">{{ $fila['tipo_descarte'] }}</span></td>
                            <td class="text-right" style="font-weight: 600; font-family: monospace;">{{ number_format($fila['kilogramos'], 2) }}</td>
                            <td class="text-right" style="color: var(--txt-secondary); font-family: monospace;">S/ {{ number_format($fila['precio_promedio'], 2) }}</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700); font-family: monospace;">S/ {{ number_format($fila['valor_venta'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--txt-muted);">
                                No hay registros disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
