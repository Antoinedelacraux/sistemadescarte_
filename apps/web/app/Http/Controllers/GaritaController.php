<?php

namespace App\Http\Controllers;

use App\Models\VentaDescarte;
use App\Models\Envio;
use App\Models\Fundo;
use App\Services\ExcelExporter;
use App\Services\HistoryReportBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GaritaController extends Controller
{
    public function index(Request $request): View
    {
        $estadoPanel = $request->query('estado') === 'validado' ? 'validado' : 'pendiente';
        $datos = $this->consultarEnvios($request, $estadoPanel);

        $kpiPendientesHoy = $this->contarViajes(VentaDescarte::activos()
            ->whereDate('enviado_garita_at', now()->toDateString())
            ->where('estado_garita', 'pendiente'));
        $kpiValidadosHoy = $this->contarViajes(VentaDescarte::activos()
            ->whereDate('salida_garita_at', now()->toDateString())
            ->where('estado_garita', 'validado'));
        return view('garita.index', $datos + [
            'estadoPanel' => $estadoPanel,
            'kpiPendientesHoy' => $kpiPendientesHoy,
            'kpiValidadosHoy' => $kpiValidadosHoy,
        ]);
    }

    public function historial(Request $request): View
    {
        return view('garita.historial', $this->consultarEnvios($request, 'validado'));
    }

    public function exportarHistorialExcel(Request $request): Response
    {
        $datos = $this->consultarEnvios($request, 'validado');
        $report = HistoryReportBuilder::garita($datos['viajesAgrupados'], $datos['fundos']->count() > 1);
        $binary = ExcelExporter::generate($report['headers'], $report['rows'], 'Validaciones Garita', [
            'columnTypes' => $report['columnTypes'],
            'columnAlignments' => $report['columnAlignments'],
            'columnWidths' => $report['columnWidths'],
        ]);
        $filename = 'validaciones_garita_'.date('Y-m-d_His').'.xlsx';

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($binary),
        ]);
    }

    private function consultarEnvios(Request $request, string $estado): array
    {
        $filtros = $request->validate([
            'fecha' => ['nullable', 'date_format:Y-m-d'],
            'fundo_id' => ['nullable', 'integer'],
            'viaje' => ['nullable', 'string', 'max:50'],
            'conductor' => ['nullable', 'string', 'max:150'],
            'cliente' => ['nullable', 'string', 'max:150'],
        ]);
        $filtros = array_merge(['fecha' => '', 'fundo_id' => '', 'viaje' => '', 'conductor' => '', 'cliente' => ''], $filtros);
        if (empty($filtros['fecha'])) {
            $filtros['viaje'] = '';
        }

        $query = VentaDescarte::with(['lote', 'fundo', 'envio'])->activos()->where('estado_garita', $estado);
        if (!empty($filtros['fecha'])) {
            $query->whereDate('fecha_produccion', $filtros['fecha']);
        }
        if (!empty($filtros['fundo_id'])) {
            $query->where('fundo_id', $filtros['fundo_id']);
        }

        // Las búsquedas se aplican al envío agrupado para conservar todos sus lotes y totales.
        $agrupados = $this->agruparViajes($query->get());
        $opcionesViajes = !empty($filtros['fecha'])
            ? $agrupados->pluck('viaje')->filter(fn ($viaje) => $viaje !== '—')->unique()->sort(SORT_NATURAL)->values()
            : collect();
        $viajesAgrupados = $agrupados->filter(function ($envio) use ($filtros) {
            return (($filtros['viaje'] === null || $filtros['viaje'] === '') || $envio['viaje'] === $filtros['viaje'])
                && (empty($filtros['conductor']) || mb_stripos($envio['conductores'], trim($filtros['conductor'])) !== false)
                && (empty($filtros['cliente']) || mb_stripos($envio['cliente'], trim($filtros['cliente'])) !== false);
        });
        $user = Auth::user();
        $fundos = $user->hasAccessToAllFundos()
            ? Fundo::where('is_active', true)->orderBy('name')->get()
            : $user->fundos()->where('is_active', true)->orderBy('name')->get();

        return compact('viajesAgrupados', 'filtros', 'opcionesViajes', 'fundos');
    }

    private function agruparViajes($ventas)
    {
        return $ventas->groupBy(fn ($venta) => $this->claveViaje($venta))->map(function ($items, $key) {
            $first = $items->first();
            $numeroViaje = trim((string) $first->viaje);
            $conductores = $items->pluck('conductor')->filter()->unique()->implode(', ');
            return [
                'clave' => $key,
                'envio_id' => $first->envio_id,
                'fecha_produccion' => $first->fecha_produccion,
                'fundo_nombre' => $first->fundo?->name ?? '—',
                'fecha_hora_registro' => $items->min('created_at'),
                'enviado_garita_at' => $items->max('enviado_garita_at'),
                'fecha_validacion' => $items->max('salida_garita_at'),
                'viaje' => $numeroViaje !== '' ? $numeroViaje : '—',
                'placa' => $first->placa ?: '—',
                'conductor' => $conductores ?: '—',
                'conductores' => $conductores,
                'cliente' => $items->pluck('cliente')->filter()->unique()->implode(', ') ?: '—',
                'total_kilos' => $items->sum('kilogramos'),
                'total_jabas' => $items->sum('jabas'),
                'lotes' => $items->pluck('lote.nombre')->filter()->unique()->values(),
                'estado_consolidado' => $first->estado_garita,
                'ids' => $items->pluck('id')->all(),
            ];
        })->sortByDesc(fn ($viaje) => $viaje['fecha_validacion'] ?? $viaje['fecha_hora_registro']);
    }

    private function claveViaje(VentaDescarte $venta): string
    {
        if ($venta->envio_id) {
            return 'envio:'.$venta->envio_id;
        }
        $fecha = $venta->fecha_produccion?->format('Y-m-d') ?? 'sin-fecha';
        $numeroViaje = trim((string) $venta->viaje);
        $numeroViaje = $numeroViaje !== '' ? $numeroViaje : 'sin-viaje';
        // La identidad del viaje no depende de la placa: la placa es un dato
        // que debe coincidir entre lotes, no una forma de separar el envío.
        return json_encode([$fecha, $venta->fundo_id, $numeroViaje], JSON_THROW_ON_ERROR);
    }

    private function contarViajes($query): int
    {
        return $query->get(['envio_id', 'fecha_produccion', 'fundo_id', 'viaje'])
            ->map(fn (VentaDescarte $venta) => $this->claveViaje($venta))
            ->unique()
            ->count();
    }

    public function confirmarSalida(Request $request): RedirectResponse
    {
        $request->validate([
            'venta_ids' => 'required|array|min:1',
            'venta_ids.*' => 'required|string',
            'observacion_garita' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        abort_unless($user && $user->canAccessModule('garita'), 403, 'No tienes autorización para validar salidas en garita.');

        $ids = $request->input('venta_ids');
        $observacion = $request->input('observacion_garita');

        $resultado = DB::transaction(function () use ($ids, $user, $observacion) {
            $items = VentaDescarte::query()
                ->activos()
                ->whereIn('id', $ids)
                ->where('estado_garita', 'pendiente')
                ->lockForUpdate()
                ->get(['id', 'envio_id', 'fecha_produccion', 'fundo_id', 'viaje', 'placa']);

            if ($items->count() !== count(array_unique($ids))) {
                return 'El envío cambió o ya fue validado. Actualiza la pantalla e inténtalo nuevamente.';
            }

            $clavesViaje = $items->map(fn (VentaDescarte $item) => $this->claveViaje($item))->unique();
            if ($clavesViaje->count() !== 1) {
                return 'Solo se pueden validar juntos los registros del mismo fundo, fecha y número de viaje.';
            }

            $primero = $items->first();
            $envioQuery = VentaDescarte::query()->activos();
            if ($primero->envio_id) {
                $envioQuery->where('envio_id', $primero->envio_id);
            } else {
                $envioQuery->where('fundo_id', $primero->fundo_id)
                    ->whereDate('fecha_produccion', $primero->fecha_produccion)
                    ->where('viaje', $primero->viaje);
            }
            $registrosEnvio = $envioQuery->lockForUpdate()->get([
                'id', 'estado_registro', 'cliente', 'conductor', 'placa', 'ruc', 'brevete',
            ]);
            if ($registrosEnvio->contains(fn (VentaDescarte $registro) => $registro->isBorradorRegistro())) {
                return 'No se puede validar: este viaje aún tiene registros con información faltante.';
            }
            foreach (['cliente' => 'cliente', 'conductor' => 'conductor', 'placa' => 'placa', 'ruc' => 'RUC', 'brevete' => 'brevete'] as $campo => $etiqueta) {
                $valores = $registrosEnvio->pluck($campo)
                    ->map(fn ($valor) => mb_strtolower(trim((string) $valor), 'UTF-8'))
                    ->filter(fn ($valor) => $valor !== '')
                    ->unique();
                if ($valores->count() > 1) {
                    return "No se puede validar: los registros del viaje tienen datos distintos de {$etiqueta}.";
                }
            }

            $idsSolicitados = collect($ids)->unique()->sort()->values()->all();
            $idsDelEnvio = $registrosEnvio->modelKeys();
            sort($idsDelEnvio);
            if ($idsSolicitados !== $idsDelEnvio) {
                return 'No se puede validar el viaje por partes. Actualiza la tarjeta y confirma todos los lotes pendientes del envío.';
            }

            $ahora = now();
            VentaDescarte::query()
                ->whereIn('id', $idsSolicitados)
                ->where('estado_garita', 'pendiente')
                ->update([
                    'estado_garita' => 'validado',
                    'salida_garita_at' => $ahora,
                    'salida_garita_by' => $user->id,
                    'observacion_garita' => $observacion,
                    'updated_at' => $ahora,
                ]);
            $envioIds = $items->pluck('envio_id')->filter()->unique()->values();
            if ($envioIds->isNotEmpty()) {
                Envio::withoutGlobalScopes()->whereIn('id', $envioIds)->update([
                    'estado_garita' => 'validado', 'salida_garita_at' => $ahora, 'salida_garita_by' => $user->id,
                    'observacion_garita' => $observacion,
                ]);
            }

            return null;
        });

        if ($resultado !== null) {
            return redirect()->back()->with('error', $resultado);
        }

        return redirect()->back()->with('success', 'Salida del camión confirmada con éxito.');
    }

    public function pendientesCount(): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user && $user->isGarita(), 403, 'Solo el rol Garita puede consultar avisos de viajes pendientes.');

        $query = VentaDescarte::activos()->where('estado_garita', 'pendiente');
        if (!$user->hasAccessToAllFundos()) {
            $query->whereIn('fundo_id', $user->fundos()->pluck('fundos.id'));
        }

        $pendientes = $this->agruparViajes($query->with(['fundo', 'envio'])->get())->values();
        $fechaHoy = now()->toDateString();
        $kpiPendientesHoy = $this->contarViajes((clone $query)->whereDate('enviado_garita_at', $fechaHoy));

        $validadosHoyQuery = VentaDescarte::activos()
            ->where('estado_garita', 'validado')
            ->whereDate('salida_garita_at', $fechaHoy);
        if (!$user->hasAccessToAllFundos()) {
            $validadosHoyQuery->whereIn('fundo_id', $user->fundos()->pluck('fundos.id'));
        }
        $kpiValidadosHoy = $this->contarViajes($validadosHoyQuery);

        $actividadQuery = VentaDescarte::activos()->whereIn('estado_garita', ['pendiente', 'validado']);
        if (!$user->hasAccessToAllFundos()) {
            $actividadQuery->whereIn('fundo_id', $user->fundos()->pluck('fundos.id'));
        }
        $ultimaActualizacion = (clone $actividadQuery)->max('updated_at');
        $revision = hash('sha256', implode('|', [
            $pendientes->map(fn (array $envio) => $envio['clave'].'@'.($envio['enviado_garita_at']?->toIso8601String() ?? ''))->implode(','),
            $kpiPendientesHoy,
            $kpiValidadosHoy,
            (string) $ultimaActualizacion,
        ]));

        return response()->json([
            'count' => $pendientes->count(),
            'kpis' => [
                'pendientes_hoy' => $kpiPendientesHoy,
                'validados_hoy' => $kpiValidadosHoy,
            ],
            'revision' => $revision,
            'timestamp' => now()->toISOString(),
            'pendientes' => $pendientes->map(function (array $envio): array {
                $enviadoAt = $envio['enviado_garita_at'];

                return [
                    // La huella evita volver a avisar el mismo envío y cambia si se reenvía a Garita.
                    'fingerprint' => hash('sha256', $envio['clave'].'|'.($enviadoAt?->toIso8601String() ?? '')),
                    'viaje' => $envio['viaje'],
                    'placa' => $envio['placa'],
                    'fundo' => $envio['fundo_nombre'],
                    'enviado_at' => $enviadoAt?->toIso8601String(),
                ];
            })->values(),
        ]);
    }
}
