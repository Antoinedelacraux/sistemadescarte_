<?php

namespace App\Http\Controllers;

use App\Models\Fundo;
use App\Models\VentaDescarte;
use App\Services\DashboardAnalyticsService;
use App\Services\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Inyección de dependencias: Servicio desacoplado para cálculos y matrices analíticas.
     */
    public function __construct(
        protected DashboardAnalyticsService $analyticsService
    ) {}

    /**
     * Muestra el Panel de Control con tablas segregadas por motivo y matrices cruzadas por cliente.
     */
    public function index(Request $request): View|RedirectResponse
    {
        @ini_set('memory_limit', '256M');

        $user = $request->user()->load(['role', 'fundos']);

        // Si el usuario no tiene acceso al módulo panel, redirigir a su primer módulo habilitado
        if (!$user->canAccessModule('panel')) {
            if ($user->canAccessModule('registro')) {
                return redirect()->route('ventas.create');
            }
            if ($user->canAccessModule('historial')) {
                return redirect()->route('ventas.index');
            }
            if ($user->canAccessModule('admin')) {
                return redirect()->route('admin.fundos');
            }
            abort(403, 'No tienes autorización para acceder a los módulos del sistema.');
        }

        $totalFundosActivos = Fundo::where('is_active', true)->count();
        $tieneAccesoATodos = $user->hasAccessToAllFundos() || ($totalFundosActivos > 0 && $user->fundos->count() >= $totalFundosActivos);
        $fundosFiltro = $tieneAccesoATodos ? Fundo::where('is_active', true)->orderBy('name')->get() : collect();
        $fundoSeleccionadoId = $request->input('fundo_id', '');

        // FundoScope aplica automáticamente el aislamiento de fundos para roles 'general' e 'individual'
        $ventasQuery = VentaDescarte::with(['fundo', 'lote', 'creator'])
            ->activos()
            ->latest('fecha_produccion');

        if ($tieneAccesoATodos && !empty($fundoSeleccionadoId) && $fundoSeleccionadoId !== 'todos') {
            $ventasQuery->where('fundo_id', $fundoSeleccionadoId);
        }

        $ventas = $ventasQuery->get();

        $totalGeneralKg = (float) $ventas->sum('kilogramos');
        $totalGeneralVenta = (float) $ventas->sum('valor_venta');

        // 1. Segregación operativa por motivo y tipo de descarte delegada al servicio
        $segregacionMotivos = $this->analyticsService->calcularSegregacionMotivos($ventas, $totalGeneralKg);

        // 2. Matrices cruzadas de clientes (Kilogramos y Montos) delegadas al servicio
        $matrizData = $this->analyticsService->calcularMatrizClientes($ventas);
        $matrizLotesData = $this->analyticsService->calcularMatrizLotes($ventas);

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'fundos' => $user->fundos,
            'tieneAccesoATodos' => $tieneAccesoATodos,
            'fundosFiltro' => $fundosFiltro,
            'fundoSeleccionadoId' => $fundoSeleccionadoId,
            'ventas' => $ventas,
            'segregacionMotivos' => $segregacionMotivos,
            'columnasMatriz' => $matrizData['columnasMatriz'],
            'matrizClientes' => $matrizData['matrizClientes'],
            'totalesColumnasKg' => $matrizData['totalesColumnasKg'],
            'totalesColumnasMonto' => $matrizData['totalesColumnasMonto'],
            'granTotalMatrizKg' => $matrizData['granTotalMatrizKg'],
            'granTotalMatrizMonto' => $matrizData['granTotalMatrizMonto'],
            'matrizLotes' => $matrizLotesData['matrizLotes'],
            'totalesColumnasLotesKg' => $matrizLotesData['totalesColumnasKg'],
            'granTotalLotesKg' => $matrizLotesData['granTotalKg'],
            'totalGeneralKg' => $totalGeneralKg,
            'totalGeneralVenta' => $totalGeneralVenta,
        ]);
    }

    /**
     * Exporta a Excel (.xlsx) la información del Panel de Control según la tabla y filtros seleccionados.
     */
    public function exportar(Request $request): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $tabla = $request->input('tabla', 'clientes');

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'creator'])
            ->activos()
            ->latest('fecha_produccion');

        if ($request->filled('fundo_id')) {
            $query->where('fundo_id', $request->input('fundo_id'));
        }

        if ($request->filled('motivo') && $request->input('motivo') !== 'todos') {
            $query->where('motivo', $request->input('motivo'));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->input('fecha_hasta'));
        }

        if ($request->filled('buscar')) {
            $term = '%' . trim($request->input('buscar')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cliente', 'like', $term)
                  ->orWhere('ruc', 'like', $term)
                  ->orWhere('placa', 'like', $term);
            });
        }

        $ventas = $query->get();

        if ($tabla === 'motivos') {
            return $this->exportarMotivos($ventas);
        }

        if ($tabla === 'lotes') {
            return $this->exportarLotes($ventas);
        }

        if ($tabla === 'recientes') {
            return $this->exportarRecientes($ventas);
        }

        if ($tabla === 'completo') {
            return $this->exportarCompleto($ventas);
        }

        return $this->exportarClientes($ventas);
    }

    /**
     * Exporta las Matrices de Ventas por Cliente (Kilogramos y Montos) a Excel,
     * organizadas secuencialmente una debajo de la otra.
     */
    private function exportarClientes($ventas): Response
    {
        $matrizData = $this->analyticsService->calcularMatrizClientes($ventas);
        $matrizClientes = $matrizData['matrizClientes'];

        $filename = 'resumen_ventas_clientes_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generateMatricesClientes($matrizClientes, 'Ventas por Cliente');

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }

    /**
     * Exporta el Resumen por Tipo y Descarte a Excel.
     */
    private function exportarMotivos($ventas): Response
    {
        $headers = [
            'ORIGEN / MODALIDAD',
            'TIPO DE DESCARTE',
            'CANTIDAD ENVÍOS',
            'KILOGRAMOS TOTALES (KG)',
            'TOTAL VENTA (S/)',
        ];

        $columnTypes = [
            0 => 'string',
            1 => 'string',
            2 => 'integer',
            3 => 'decimal',
            4 => 'decimal',
        ];

        $totalGeneralVenta = (float) $ventas->sum('valor_venta');

        $dataRows = [];
        foreach (DashboardAnalyticsService::MOTIVOS_DEFINICION as $motivoKey => $meta) {
            $ventasMotivo = $ventas->where('motivo', $motivoKey);
            foreach ($meta['tipos_permitidos'] as $tipo) {
                $ventasTipo = $ventasMotivo->where('tipo_descarte', $tipo);
                $kgTipo = (float) $ventasTipo->sum('kilogramos');
                $ventaTipo = (float) $ventasTipo->sum('valor_venta');
                $countTipo = $ventasTipo->count();

                $dataRows[] = [
                    $meta['titulo'],
                    $tipo,
                    $countTipo,
                    $kgTipo,
                    $ventaTipo,
                ];
            }
        }

        $totalRow = [
            'TOTAL GENERAL',
            '',
            $ventas->count(),
            (float) $ventas->sum('kilogramos'),
            $totalGeneralVenta,
        ];

        $filename = 'resumen_tipo_descarte_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Por Tipo y Descarte', [
            'columnTypes' => $columnTypes,
            'totalRow' => $totalRow,
        ]);

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }

    /**
     * Exporta la matriz de kilogramos por fundo, lote, motivo y tipo de descarte.
     */
    private function exportarLotes($ventas): Response
    {
        $matrizData = $this->analyticsService->calcularMatrizLotes($ventas);
        $columnas = $matrizData['columnasMatriz'];
        $headers = ['FUNDO', 'LOTE'];
        $columnTypes = ['string', 'string'];

        foreach ($columnas as $colDef) {
            foreach ($colDef['tipos'] as $tipo) {
                $headers[] = $colDef['titulo'] . ' - ' . $tipo . ' (KG)';
                $columnTypes[] = 'decimal';
            }
        }
        $headers[] = 'TOTAL KG';
        $columnTypes[] = 'decimal';

        $dataRows = [];
        foreach ($matrizData['matrizLotes'] as $lote) {
            $row = [$lote['fundo'], $lote['lote']];
            foreach ($columnas as $motivoKey => $colDef) {
                foreach ($colDef['tipos'] as $tipo) {
                    $row[] = (float) ($lote['kg'][$motivoKey][$tipo] ?? 0);
                }
            }
            $row[] = (float) $lote['total_kg'];
            $dataRows[] = $row;
        }

        $totalRow = ['TOTAL GENERAL', ''];
        foreach ($columnas as $motivoKey => $colDef) {
            foreach ($colDef['tipos'] as $tipo) {
                $totalRow[] = (float) ($matrizData['totalesColumnasKg'][$motivoKey][$tipo] ?? 0);
            }
        }
        $totalRow[] = (float) $matrizData['granTotalKg'];

        $filename = 'resumen_ventas_lotes_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Ventas por Lote', [
            'columnTypes' => $columnTypes,
            'totalRow' => $totalRow,
        ]);

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }

    /**
     * Exporta los Últimos Envíos Registrados a Excel.
     */
    private function exportarRecientes($ventas): Response
    {
        $headers = [
            'FUNDO',
            'FECHA PRODUCCIÓN',
            'CLIENTE',
            'RUC',
            'ORIGEN / MOTIVO',
            'TIPO DE DESCARTE',
            'PRECIO / KG (S/)',
            'KILOS (KG)',
            'TOTAL VENTA (S/)',
            'REGISTRADO POR',
        ];

        $columnTypes = [
            0 => 'string',
            1 => 'date',
            2 => 'string',
            3 => 'string',
            4 => 'string',
            5 => 'string',
            6 => 'decimal',
            7 => 'decimal',
            8 => 'decimal',
            9 => 'string',
        ];

        $dataRows = [];
        foreach ($ventas as $v) {
            $fundoNombre = $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A';
            $dataRows[] = [
                $fundoNombre,
                $v->fecha_produccion ? $v->fecha_produccion->format('d/m/Y') : '',
                $v->cliente ?: 'Venta General',
                (string) ($v->ruc ?: ''),
                $v->motivo,
                $v->tipo_descarte,
                (float) $v->precio,
                (float) $v->kilogramos,
                (float) $v->valor_venta,
                $v->creator?->name ?? 'Sistema',
            ];
        }

        $filename = 'ultimos_envios_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Envíos Registrados', [
            'columnTypes' => $columnTypes,
        ]);

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }

    /**
     * Exporta el reporte completo del Panel a Excel con todos los campos.
     */
    private function exportarCompleto($ventas): Response
    {
        $headers = [
            'FUNDO',
            'FECHA PRODUCCIÓN',
            'CLIENTE / RAZÓN SOCIAL',
            'RUC',
            'LOTE',
            'CUARTEL',
            'ORIGEN / MOTIVO',
            'TIPO DE DESCARTE',
            'KILOGRAMOS (KG)',
            'PRECIO / KG (S/)',
            'TOTAL VENTA (S/)',
            'CANTIDAD JABAS',
            'REGISTRADO POR',
        ];

        $columnTypes = [
            0 => 'string',
            1 => 'date',
            2 => 'string',
            3 => 'string',
            4 => 'string',
            5 => 'string',
            6 => 'string',
            7 => 'string',
            8 => 'decimal',
            9 => 'decimal',
            10 => 'decimal',
            11 => 'integer',
            12 => 'string',
        ];

        $dataRows = [];
        foreach ($ventas as $v) {
            $fundoNombre = $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A';
            $dataRows[] = [
                $fundoNombre,
                $v->fecha_produccion ? $v->fecha_produccion->format('d/m/Y') : '',
                $v->cliente ?: 'Venta General',
                (string) ($v->ruc ?: ''),
                $v->lote?->nombre ?? '',
                $v->cuartel_manual ?? ($v->cuartel?->nombre ?? ''),
                $v->motivo,
                $v->tipo_descarte,
                (float) $v->kilogramos,
                (float) $v->precio,
                (float) $v->valor_venta,
                $v->jabas !== null ? (int) $v->jabas : '',
                $v->creator?->name ?? 'Sistema',
            ];
        }

        $filename = 'panel_control_completo_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Resumen General', [
            'columnTypes' => $columnTypes,
        ]);

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }
}
