<?php

namespace App\Http\Controllers;

use App\Models\VentaDescarte;
use App\Models\Envio;
use App\Models\Fundo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->whereDate('fecha_produccion', now()->toDateString())
            ->where('estado_garita', 'pendiente'));
        $kpiValidadosHoy = $this->contarViajes(VentaDescarte::activos()
            ->whereDate('fecha_produccion', now()->toDateString())
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
                'envio_codigo' => $first->envio?->codigo,
                'fecha_produccion' => $first->fecha_produccion,
                'fundo_nombre' => $first->fundo?->name ?? '—',
                'fecha_hora_registro' => $items->min('created_at'),
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
        $placa = strtoupper(trim($venta->placa ?: 'SIN-PLACA'));

        return json_encode([$fecha, $venta->fundo_id, $numeroViaje, $placa], JSON_THROW_ON_ERROR);
    }

    private function contarViajes($query): int
    {
        return $query->get(['fecha_produccion', 'fundo_id', 'viaje', 'placa'])
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

        $items = VentaDescarte::query()
            ->whereIn('id', $ids)
            ->where('estado', 'activo')
            ->where('estado_garita', 'pendiente')
            ->get(['id', 'envio_id', 'fecha_produccion', 'fundo_id', 'viaje', 'placa']);

        if ($items->count() !== count(array_unique($ids))) {
            return redirect()->back()->with('error', 'El envío cambió o ya fue validado. Actualiza la pantalla e inténtalo nuevamente.');
        }

        $clavesViaje = $items->map(fn (VentaDescarte $item) => $this->claveViaje($item))->unique();

        if ($clavesViaje->count() !== 1) {
            return redirect()->back()->with('error', 'Solo se pueden validar juntos los registros del mismo fundo, fecha, número de viaje y placa.');
        }

        DB::transaction(function () use ($ids, $user, $observacion, $items) {
            VentaDescarte::query()
                ->whereIn('id', $ids)
                ->where('estado', 'activo')
                ->where('estado_garita', 'pendiente')
                ->update([
                    'estado_garita' => 'validado',
                    'salida_garita_at' => now(),
                    'salida_garita_by' => $user->id,
                    'observacion_garita' => $observacion,
                    'updated_at' => now(),
                ]);
            $envioIds = $items->pluck('envio_id')->filter()->unique()->values();
            if ($envioIds->isNotEmpty()) {
                Envio::withoutGlobalScopes()->whereIn('id', $envioIds)->update([
                    'estado_garita' => 'validado', 'salida_garita_at' => now(), 'salida_garita_by' => $user->id,
                    'observacion_garita' => $observacion,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Salida del camión confirmada con éxito.');
    }

    public function pendientesCount(): JsonResponse
    {
        $user = Auth::user();
        if (!$user || !$user->canAccessModule('garita')) {
            return response()->json(['count' => 0]);
        }

        $query = VentaDescarte::activos()->where('estado_garita', 'pendiente');
        if (!$user->hasAccessToAllFundos()) {
            $query->whereIn('fundo_id', $user->fundos()->pluck('fundos.id'));
        }

        return response()->json([
            'count' => $this->contarViajes($query),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
