<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fundo;
use App\Models\VentaDescarte;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador de API de solo lectura para integración con Power BI.
 *
 * Expone el historial de ventas/descarte en formato JSON paginado.
 * No requiere sesión de usuario; se autentica mediante token Bearer (ApiTokenAuth).
 *
 * Endpoints:
 *   GET /api/v1/ventas-descarte    → Historial paginado con filtros opcionales
 *   GET /api/v1/fundos             → Catálogo de fundos activos
 *   GET /api/v1/estado             → Health-check de la conexión
 */
class VentasApiController extends Controller
{
    /**
     * Devuelve el historial de ventas/descarte en formato JSON para Power BI.
     *
     * Parámetros de query opcionales:
     *   - fecha_desde  (Y-m-d)   Filtrar desde esta fecha de producción
     *   - fecha_hasta  (Y-m-d)   Filtrar hasta esta fecha de producción
     *   - fundo_id     (int)     Filtrar por fundo específico
     *   - estado       (string)  'activo', 'anulado' o vacío para todos
     *   - per_page     (int)     Registros por página, máx 2000 (defecto: 500)
     *   - page         (int)     Página actual (defecto: 1)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 500), 2000);

        $query = VentaDescarte::withoutGlobalScopes()
            ->with(['fundo', 'lote', 'creator'])
            ->orderBy('fecha_produccion', 'desc')
            ->orderBy('created_at', 'desc');

        // ── Filtros opcionales ────────────────────────────────────────────
        if ($request->filled('fundo_id')) {
            $query->where('fundo_id', (int) $request->query('fundo_id'));
        }

        if ($request->filled('estado')) {
            $estado = $request->query('estado');
            if ($estado === 'activo') {
                $query->where(function ($q) {
                    $q->where('estado', 'activo')->orWhereNull('estado');
                });
            } elseif ($estado === 'anulado') {
                $query->where('estado', 'anulado');
            }
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->query('fecha_desde'));
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->query('fecha_hasta'));
        }
        // ─────────────────────────────────────────────────────────────────

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(function (VentaDescarte $v): array {
            $fundoNombre = $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A';

            return [
                // Identificadores
                'id'               => $v->id,

                // Fundo / Ubicación
                'fundo'            => $fundoNombre,
                'lote'             => $v->lote?->nombre ?? '',
                'cuartel'          => $v->cuartel_manual ?? '',

                // Datos de producción
                'fecha_produccion' => $v->fecha_produccion?->format('Y-m-d'),
                'motivo'           => $v->motivo ?? '',
                'tipo_descarte'    => $v->tipo_descarte ?? '',

                // Volumen y valor
                'kilogramos'       => (float) ($v->kilogramos ?? 0),
                'precio_kg'        => (float) ($v->precio ?? 0),
                'valor_venta'      => (float) ($v->valor_venta ?? 0),
                'jabas'            => $v->jabas !== null ? (int) $v->jabas : null,
                'peso_jaba_kg'     => $v->peso_jaba !== null ? (float) $v->peso_jaba : null,

                // Cliente / Transporte
                'cliente'          => $v->cliente ?? '',
                'ruc'              => (string) ($v->ruc ?? ''),
                'placa'            => $v->placa ?? '',
                'conductor'        => $v->conductor ?? '',
                'brevete'          => $v->brevete ?? '',
                'viaje'            => $v->viaje ?? '',

                // Estado y auditoría
                'estado'           => $v->estado ?? 'activo',
                'observacion'      => $v->observacion ?? '',
                'registrado_por'   => $v->creator?->name ?? 'Sistema',
                'fecha_registro'   => $v->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }

    /**
     * Catálogo de fundos activos (útil para filtros en Power BI).
     */
    public function fundos(): JsonResponse
    {
        $fundos = Fundo::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Fundo $f) => [
                'id'             => $f->id,
                'codigo'         => $f->code,
                'nombre_corto'   => $f->nombre_corto,
                'nombre_completo' => $f->nombre_completo,
            ]);

        return response()->json(['data' => $fundos]);
    }

    /**
     * Health-check: confirma que la API está activa y autenticada correctamente.
     * Power BI puede usarlo para verificar conectividad antes de cargar datos.
     */
    public function estado(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'sistema'   => 'Sistema de Ventas y Descarte — Fundo',
            'timestamp' => now()->toIso8601String(),
            'version'   => 'v1',
        ]);
    }
}
