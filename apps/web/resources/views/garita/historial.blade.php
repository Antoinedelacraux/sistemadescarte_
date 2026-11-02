@extends('layouts.app')

@section('title', 'Historial de Validaciones — Garita')
@section('page-title', 'Historial de Validaciones')

@section('content')
<main class="garita-history">
    <header class="history-heading">
        <div>
            <h1>Historial de Validaciones</h1>
            <p>Envíos confirmados por Garita con su fecha y hora de validación.</p>
        </div>
        <a href="{{ route('garita.index') }}">Volver a pendientes</a>
    </header>

    @include('garita._filtros', ['rutaFiltros' => 'garita.historial'])

    <div class="history-table-scroll">
        <table class="history-table" aria-label="Historial tabulado de validaciones de Garita">
            <thead>
                <tr>
                    @if($fundos->count() > 1)<th>Fundo</th>@endif
                    <th>Código del envío</th>
                    <th>Fecha de producción</th>
                    <th>Fecha y hora de registro</th>
                    <th>Número de viaje</th>
                    <th>Cliente</th>
                    <th>Nombre del conductor</th>
                    <th>Placa</th>
                    <th class="numeric">Cantidad de jabas</th>
                    <th class="numeric">Kilogramos</th>
                    <th>Lotes del envío</th>
                    <th>Fecha y hora de validación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($viajesAgrupados as $viaje)
                    <tr>
                        @if($fundos->count() > 1)<td>{{ $viaje['fundo_nombre'] }}</td>@endif
                        <td class="nowrap">{{ $viaje['envio_codigo'] ?? 'Histórico' }}</td>
                        <td class="nowrap">{{ $viaje['fecha_produccion']?->format('d/m/Y') ?? '—' }}</td>
                        <td class="nowrap">{{ $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="nowrap">{{ $viaje['viaje'] }}</td>
                        <td class="people">{{ $viaje['cliente'] }}</td>
                        <td class="people">{{ $viaje['conductor'] }}</td>
                        <td class="nowrap plate">{{ $viaje['placa'] }}</td>
                        <td class="numeric">{{ number_format($viaje['total_jabas']) }}</td>
                        <td class="numeric">{{ number_format($viaje['total_kilos'], 2) }} kg</td>
                        <td>{{ $viaje['lotes']->implode(', ') ?: '—' }}</td>
                        <td class="validation-time">{{ $viaje['fecha_validacion']?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $fundos->count() > 1 ? 12 : 11 }}" class="history-empty">No hay envíos validados para esta búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>

<style>
    .garita-history { max-width: 1500px; margin: 0 auto; padding: 1.5rem; }
    .history-heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; }
    .history-heading h1 { margin:0; color:#102319; font-size:clamp(1.5rem,3vw,2rem); }
    .history-heading p { color:#64748b; }
    .history-heading a { color:#15803d; font-weight:700; text-decoration:none; }
    .history-table-scroll { overflow-x:auto; background:#fff; border:1px solid #dce5df; border-radius:14px; box-shadow:0 2px 8px #0f172a0a; }
    .history-table { width:100%; min-width:1100px; border-collapse:collapse; table-layout:auto; }
    .history-table th,.history-table td { padding:1rem .9rem; border-bottom:1px solid #e2e8f0; text-align:left; vertical-align:middle; line-height:1.4; }
    .history-table th { color:#475569; background:#f8fafc; font-size:.72rem; font-weight:800; text-transform:uppercase; white-space:normal; }
    .history-table td { color:#102319; font-weight:500; font-size:.875rem; }
    .history-table td.nowrap { white-space:nowrap; }
    .history-table td.people { min-width:135px; }
    .history-table td.plate { min-width:85px; }
    .history-table tbody tr:last-child td { border-bottom:0; }
    .history-table .numeric { text-align:right; white-space:nowrap; }
    .history-table .validation-time { color:#15803d; font-weight:700; white-space:nowrap; }
    .history-table .history-empty { padding:3rem 1rem; text-align:center; color:#64748b; }
    @media(max-width:600px) { .garita-history { padding:1rem; } .history-heading { align-items:flex-start; flex-direction:column; } }
</style>
@endsection
