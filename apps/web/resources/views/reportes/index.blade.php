@extends('layouts.app')

@section('title', 'Reportes y Exportación')
@section('page-title', 'Reportes y Exportación Excel')

@section('styles')
<style>
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: var(--clr-surface-0);
        border: 1px solid var(--brd-base);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-xs);
    }

    .kpi-label {
        font-size: var(--text-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--txt-muted);
        margin-bottom: 0.25rem;
    }

    .kpi-value {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--clr-primary-900);
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .export-box {
        background: var(--clr-surface-0);
        border: 1.5px solid var(--clr-primary-300);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        margin-bottom: 1.75rem;
        box-shadow: var(--shadow-sm);
    }

    .columns-selector-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
        margin: 1.25rem 0;
        padding: 1rem;
        background: var(--clr-surface-50);
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
    }

    .col-checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: var(--text-sm);
        color: var(--txt-secondary);
        cursor: pointer;
        user-select: none;
    }

    .col-checkbox-label input[type="checkbox"] {
        width: 17px;
        height: 17px;
        accent-color: var(--clr-primary-600);
        cursor: pointer;
    }

    .btn-export {
        height: 48px;
        padding: 0 1.5rem;
        font-size: 1rem;
        font-weight: 700;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        color: white;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(22,163,74,0.25);
        transition: all var(--transition-base);
    }

    .btn-export:hover {
        background: linear-gradient(135deg, #15803d 0%, #166534 100%);
        transform: translateY(-1px);
    }
</style>
@endsection

@section('content')
<div>
    {{-- Encabezado --}}
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: var(--text-2xl); font-weight: 700; color: var(--txt-primary);">Reportes y Análisis de Descarte</h1>
        <p style="font-size: var(--text-sm); color: var(--txt-muted);">Consolidado analítico y herramienta de exportación a Excel con columnas personalizadas</p>
    </div>

    {{-- KPIs Resumen --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Kilogramos Totales</div>
            <div class="kpi-value">{{ number_format($totalKg, 2) }} <span style="font-size: 1rem; font-weight: 500; color: var(--txt-muted);">kg</span></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Valor de Venta Total</div>
            <div class="kpi-value" style="color: var(--clr-primary-700);">S/ {{ number_format($totalMonto, 2) }}</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Pesajes Registrados</div>
            <div class="kpi-value">{{ $totalRegistros }}</div>
        </div>
    </div>

    {{-- CAJA DE EXPORTACIÓN A EXCEL CON SELECCIÓN DE COLUMNAS --}}
    <div class="export-box">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; border-bottom: 1px solid var(--brd-base); padding-bottom: 1rem;">
            <div>
                <h2 style="font-size: var(--text-lg); font-weight: 700; color: var(--txt-primary); display: flex; align-items: center; gap: 0.5rem;">
                    <span>📥</span> Exportar a Excel (.csv compatible)
                </h2>
                <p style="font-size: var(--text-xs); color: var(--txt-muted);">
                    Selecciona con precisión las columnas que deseas incluir en el archivo descargable.
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="seleccionarTodas(true)">Marcar todas</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="seleccionarTodas(false)">Desmarcar todas</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="seleccionarEsenciales()">Solo esenciales</button>
            </div>
        </div>

        <form method="GET" action="{{ route('reportes.exportar') }}" id="form-exportar">
            {{-- Filtros del exportador --}}
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
                @if(Auth::user()->isAdmin() || Auth::user()->isAnalista() || $fundos->count() > 1)
                <div>
                    <label class="form-label text-xs">Fundo</label>
                    <select name="fundo_id" class="form-control" style="height: 38px;">
                        <option value="">Todos los Fundos</option>
                        @foreach($fundos as $fundo)
                            <option value="{{ $fundo->id }}">{{ $fundo->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="form-label text-xs">Motivo</label>
                    <select name="motivo" class="form-control" style="height: 38px;">
                        <option value="">Todos los motivos</option>
                        <option value="Campo">Campo</option>
                        <option value="Packing">Packing</option>
                        <option value="Cosecha Nacional">Cosecha Nacional</option>
                    </select>
                </div>

                <div>
                    <label class="form-label text-xs">Fecha Desde</label>
                    <input type="date" name="fecha_desde" class="form-control" style="height: 38px;">
                </div>

                <div>
                    <label class="form-label text-xs">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" class="form-control" style="height: 38px;">
                </div>
            </div>

            {{-- Selector de columnas --}}
            <div style="margin-top: 1rem;">
                <label class="form-label text-xs" style="font-weight: 700;">Columnas a incluir en el archivo:</label>
                <div class="columns-selector-grid">
                    @foreach($columnasDisponibles as $key => $label)
                        <label class="col-checkbox-label">
                            <input type="checkbox" name="columnas[]" value="{{ $key }}" class="col-check" checked>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                <button type="submit" class="btn-export" id="btn-descargar-excel">
                    <span>📊</span> Descargar Archivo Excel
                </button>
            </div>
        </form>
    </div>

    {{-- TABLAS DE DISTRIBUCIÓN --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
        {{-- Distribución por Motivo --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>🏷️</span> Resumen por Motivo
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Motivo</th>
                        <th class="text-right">Registros</th>
                        <th class="text-right">Kg Totales</th>
                        <th class="text-right">Total (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porMotivo as $item)
                        <tr>
                            <td><strong>{{ $item['motivo'] }}</strong></td>
                            <td class="text-right">{{ $item['cantidad'] }}</td>
                            <td class="text-right">{{ number_format($item['kg'], 2) }}</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700);">
                                S/ {{ number_format($item['monto'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">Sin registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Distribución por Fundo --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <span>🏡</span> Resumen por Fundo
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fundo</th>
                        <th class="text-right">Registros</th>
                        <th class="text-right">Kg Totales</th>
                        <th class="text-right">Total (S/)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porFundo as $item)
                        <tr>
                            <td><strong>{{ $item['fundo'] }}</strong></td>
                            <td class="text-right">{{ $item['cantidad'] }}</td>
                            <td class="text-right">{{ number_format($item['kg'], 2) }}</td>
                            <td class="text-right" style="font-weight: 700; color: var(--clr-primary-700);">
                                S/ {{ number_format($item['monto'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted" style="padding: 1.5rem;">Sin registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function seleccionarTodas(estado) {
    document.querySelectorAll('.col-check').forEach(function(c) {
        c.checked = estado;
    });
}

function seleccionarEsenciales() {
    var esenciales = ['fundo', 'fecha_produccion', 'lote', 'cuartel', 'motivo', 'tipo_descarte', 'kilogramos', 'precio', 'valor_venta'];
    document.querySelectorAll('.col-check').forEach(function(c) {
        c.checked = esenciales.includes(c.value);
    });
}
</script>
@endsection
