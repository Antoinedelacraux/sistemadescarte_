<?php

namespace App\Http\Controllers;

use App\Models\VentaDescarte;
use App\Services\ExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard with segregated tables by motive and by client.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'fundos']);

        // FundoScope aplica automáticamente el aislamiento de fundos para roles 'general' e 'individual'
        $ventas = VentaDescarte::with(['fundo', 'creator'])
            ->activos()
            ->latest('fecha_produccion')
            ->get();

        $totalGeneralKg = (float) $ventas->sum('kilogramos');
        $totalGeneralVenta = (float) $ventas->sum('valor_venta');

        // 1. SEGREGACIÓN POR MOTIVO Y TIPO DE DESCARTE
        // Venta Nacional: Racimos, Granos (solo dos tipos)
        // Venta Campo: Racimos, Racimos con plaga, Granos
        // Venta Packing: Racimos, Granos
        $motivosDefinicion = [
            'Cosecha Nacional' => [
                'titulo' => 'Venta Nacional',
                'badge' => 'amber',
                'badge_bg' => '#fef3c7',
                'badge_color' => '#92400e',
                'icon' => '🌾',
                'tipos_permitidos' => ['Racimos', 'Granos'],
            ],
            'Campo' => [
                'titulo' => 'Venta Campo',
                'badge' => 'green',
                'badge_bg' => '#d1fae5',
                'badge_color' => '#065f46',
                'icon' => '🌿',
                'tipos_permitidos' => ['Racimos', 'Racimos con plaga', 'Granos'],
            ],
            'Packing' => [
                'titulo' => 'Venta Packing',
                'badge' => 'blue',
                'badge_bg' => '#dbeafe',
                'badge_color' => '#1e40af',
                'icon' => '📦',
                'tipos_permitidos' => ['Racimos', 'Granos'],
            ],
        ];

        $segregacionMotivos = [];
        foreach ($motivosDefinicion as $motivoKey => $meta) {
            $ventasMotivo = $ventas->where('motivo', $motivoKey);
            $totalKgMotivo = (float) $ventasMotivo->sum('kilogramos');
            $totalVentaMotivo = (float) $ventasMotivo->sum('valor_venta');
            $precioPromMotivo = $totalKgMotivo > 0 ? $totalVentaMotivo / $totalKgMotivo : 0;

            $filasTipos = [];
            foreach ($meta['tipos_permitidos'] as $tipo) {
                $ventasTipo = $ventasMotivo->where('tipo_descarte', $tipo);
                $kgTipo = (float) $ventasTipo->sum('kilogramos');
                $ventaTipo = (float) $ventasTipo->sum('valor_venta');
                $precioPromTipo = $kgTipo > 0 ? $ventaTipo / $kgTipo : 0;
                $countTipo = $ventasTipo->count();
                $porcentajeKg = $totalGeneralKg > 0 ? ($kgTipo / $totalGeneralKg) * 100 : 0;

                $filasTipos[] = [
                    'tipo_descarte' => $tipo,
                    'count' => $countTipo,
                    'kilogramos' => $kgTipo,
                    'precio_promedio' => $precioPromTipo,
                    'valor_venta' => $ventaTipo,
                    'porcentaje_kg' => round($porcentajeKg, 1),
                ];
            }

            $segregacionMotivos[$motivoKey] = [
                'meta' => $meta,
                'total_kg' => $totalKgMotivo,
                'total_venta' => $totalVentaMotivo,
                'precio_promedio' => $precioPromMotivo,
                'count' => $ventasMotivo->count(),
                'porcentaje_kg' => $totalGeneralKg > 0 ? round(($totalKgMotivo / $totalGeneralKg) * 100, 1) : 0,
                'tipos' => $filasTipos,
            ];
        }

        // 2. SEGREGACIÓN POR CLIENTE
        // La tabla segmenta: cliente - cosecha nacional, packing y campo - tipos de descartes - kilogramos total - precio venta
        $clientesAgrupados = [];
        foreach ($ventas as $v) {
            $nombreCliente = trim((string) ($v->cliente ?? ''));
            if ($nombreCliente === '') {
                $nombreCliente = trim((string) ($v->ruc ?? '')) !== '' ? 'RUC: ' . trim($v->ruc) : 'Sin Cliente Registrado';
            }
            $motivo = $v->motivo;
            $tipo = $v->tipo_descarte;
            $kg = (float) $v->kilogramos;
            $ventaTotal = (float) $v->valor_venta;

            if (!isset($clientesAgrupados[$nombreCliente])) {
                $clientesAgrupados[$nombreCliente] = [
                    'cliente' => $nombreCliente,
                    'ruc' => $v->ruc,
                    'total_kg' => 0,
                    'total_venta' => 0,
                    'motivos' => [],
                    'desglose' => [],
                ];
            }

            $clientesAgrupados[$nombreCliente]['total_kg'] += $kg;
            $clientesAgrupados[$nombreCliente]['total_venta'] += $ventaTotal;
            if (!in_array($motivo, $clientesAgrupados[$nombreCliente]['motivos'])) {
                $clientesAgrupados[$nombreCliente]['motivos'][] = $motivo;
            }

            $desgloseKey = "{$motivo}|{$tipo}";
            if (!isset($clientesAgrupados[$nombreCliente]['desglose'][$desgloseKey])) {
                $clientesAgrupados[$nombreCliente]['desglose'][$desgloseKey] = [
                    'motivo' => $motivo,
                    'tipo_descarte' => $tipo,
                    'kilogramos' => 0,
                    'valor_venta' => 0,
                    'count' => 0,
                ];
            }

            $clientesAgrupados[$nombreCliente]['desglose'][$desgloseKey]['kilogramos'] += $kg;
            $clientesAgrupados[$nombreCliente]['desglose'][$desgloseKey]['valor_venta'] += $ventaTotal;
            $clientesAgrupados[$nombreCliente]['desglose'][$desgloseKey]['count'] += 1;
        }

        foreach ($clientesAgrupados as &$cData) {
            $cData['precio_promedio'] = $cData['total_kg'] > 0 ? $cData['total_venta'] / $cData['total_kg'] : 0;
            foreach ($cData['desglose'] as &$dData) {
                $dData['precio_promedio'] = $dData['kilogramos'] > 0 ? $dData['valor_venta'] / $dData['kilogramos'] : 0;
            }
            unset($dData);
        }
        unset($cData);

        uasort($clientesAgrupados, fn ($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        // Lista plana para vista matricial
        $filasClientesPlanas = [];
        foreach ($clientesAgrupados as $clienteName => $cData) {
            foreach ($cData['desglose'] as $dData) {
                $filasClientesPlanas[] = [
                    'cliente' => $clienteName,
                    'ruc' => $cData['ruc'],
                    'motivo' => $dData['motivo'],
                    'tipo_descarte' => $dData['tipo_descarte'],
                    'kilogramos' => $dData['kilogramos'],
                    'precio_promedio' => $dData['precio_promedio'],
                    'valor_venta' => $dData['valor_venta'],
                    'count' => $dData['count'],
                ];
            }
        }

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'fundos' => $user->fundos,
            'ventas' => $ventas,
            'segregacionMotivos' => $segregacionMotivos,
            'clientesAgrupados' => $clientesAgrupados,
            'filasClientesPlanas' => $filasClientesPlanas,
            'totalGeneralKg' => $totalGeneralKg,
            'totalGeneralVenta' => $totalGeneralVenta,
        ]);
    }

    /**
     * Exporta a Excel (.xlsx) la información del Panel de Control según la tabla y filtros seleccionados.
     */
    public function exportar(Request $request): Response
    {
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
                  ->orWhere('tipo_descarte', 'like', $term);
            });
        }

        $ventas = $query->get();

        if ($tabla === 'motivos') {
            return $this->exportarMotivos($ventas);
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
     * Exporta el Resumen de Ventas por Cliente a Excel.
     */
    private function exportarClientes($ventas): Response
    {
        $headers = [
            'CLIENTE / RAZÓN SOCIAL',
            'RUC',
            'ORIGEN / MOTIVO',
            'TIPO DE DESCARTE',
            'CANTIDAD ENVÍOS',
            'KILOS TOTALES (KG)',
            'PRECIO PROMEDIO (S/)',
            'TOTAL VENTA (S/)',
        ];

        $columnTypes = [
            0 => 'string',
            1 => 'string', // RUC tratado como string explícito
            2 => 'string',
            3 => 'string',
            4 => 'integer',
            5 => 'decimal',
            6 => 'decimal',
            7 => 'decimal',
        ];

        $grupos = [];
        foreach ($ventas as $v) {
            $cliente = trim($v->cliente ?: ($v->ruc ? 'RUC: ' . $v->ruc : 'Venta General'));
            $ruc = (string) ($v->ruc ?: '');
            $motivo = $v->motivo;
            $tipo = $v->tipo_descarte;
            $key = "{$cliente}|{$ruc}|{$motivo}|{$tipo}";

            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'cliente' => $cliente,
                    'ruc' => $ruc,
                    'motivo' => $motivo,
                    'tipo' => $tipo,
                    'count' => 0,
                    'kilos' => 0.0,
                    'total' => 0.0,
                ];
            }
            $grupos[$key]['count'] += 1;
            $grupos[$key]['kilos'] += (float) $v->kilogramos;
            $grupos[$key]['total'] += (float) $v->valor_venta;
        }

        $dataRows = [];
        $totalKilos = 0.0;
        $totalVenta = 0.0;
        $totalCount = 0;

        foreach ($grupos as $g) {
            $precioProm = $g['kilos'] > 0 ? round($g['total'] / $g['kilos'], 2) : 0.0;
            $dataRows[] = [
                $g['cliente'],
                $g['ruc'],
                $g['motivo'],
                $g['tipo'],
                $g['count'],
                $g['kilos'],
                $precioProm,
                $g['total'],
            ];
            $totalKilos += $g['kilos'];
            $totalVenta += $g['total'];
            $totalCount += $g['count'];
        }

        $precioPromGeneral = $totalKilos > 0 ? round($totalVenta / $totalKilos, 2) : 0.0;
        $totalRow = [
            'TOTAL GENERAL (' . count($grupos) . ' combinaciones)',
            '',
            '',
            '',
            $totalCount,
            $totalKilos,
            $precioPromGeneral,
            $totalVenta,
        ];

        $filename = 'resumen_ventas_clientes_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Ventas por Cliente', [
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
     * Exporta el Resumen por Tipo y Descarte a Excel.
     */
    private function exportarMotivos($ventas): Response
    {
        $headers = [
            'ORIGEN / MODALIDAD',
            'TIPO DE DESCARTE',
            'CANTIDAD ENVÍOS',
            'KILOGRAMOS TOTALES (KG)',
            '% VOLUMEN DESCARTE',
            'PRECIO PROMEDIO (S/)',
            'TOTAL VENTA (S/)',
        ];

        $columnTypes = [
            0 => 'string',
            1 => 'string',
            2 => 'integer',
            3 => 'decimal',
            4 => 'decimal',
            5 => 'decimal',
            6 => 'decimal',
        ];

        $motivosDefinicion = [
            'Cosecha Nacional' => [
                'titulo' => 'Venta Nacional',
                'tipos' => ['Racimos', 'Granos'],
            ],
            'Campo' => [
                'titulo' => 'Venta Campo',
                'tipos' => ['Racimos', 'Racimos con plaga', 'Granos'],
            ],
            'Packing' => [
                'titulo' => 'Venta Packing',
                'tipos' => ['Racimos', 'Granos'],
            ],
        ];

        $totalGeneralKg = (float) $ventas->sum('kilogramos');
        $totalGeneralVenta = (float) $ventas->sum('valor_venta');

        $dataRows = [];
        foreach ($motivosDefinicion as $motivoKey => $meta) {
            $ventasMotivo = $ventas->where('motivo', $motivoKey);
            foreach ($meta['tipos'] as $tipo) {
                $ventasTipo = $ventasMotivo->where('tipo_descarte', $tipo);
                $kgTipo = (float) $ventasTipo->sum('kilogramos');
                $ventaTipo = (float) $ventasTipo->sum('valor_venta');
                $precioPromTipo = $kgTipo > 0 ? round($ventaTipo / $kgTipo, 2) : 0.0;
                $countTipo = $ventasTipo->count();
                $porcentajeKg = $totalGeneralKg > 0 ? round(($kgTipo / $totalGeneralKg) * 100, 1) : 0.0;

                $dataRows[] = [
                    $meta['titulo'],
                    $tipo,
                    $countTipo,
                    $kgTipo,
                    $porcentajeKg,
                    $precioPromTipo,
                    $ventaTipo,
                ];
            }
        }

        $precioPromGeneral = $totalGeneralKg > 0 ? round($totalGeneralVenta / $totalGeneralKg, 2) : 0.0;
        $totalRow = [
            'TOTAL GENERAL',
            '',
            $ventas->count(),
            $totalGeneralKg,
            100.0,
            $precioPromGeneral,
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
            0 => 'string',   // AGRITAC, PROCOM, EL NEGRO
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

        $totalKg = (float) $ventas->sum('kilogramos');
        $totalVenta = (float) $ventas->sum('valor_venta');
        $precioProm = $totalKg > 0 ? round($totalVenta / $totalKg, 2) : 0.0;

        $totalRow = [
            'TOTAL GENERAL (' . $ventas->count() . ' envíos)',
            '',
            '',
            '',
            '',
            '',
            $precioProm,
            $totalKg,
            $totalVenta,
            '',
        ];

        $filename = 'ultimos_envios_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Envíos Registrados', [
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

        $totalKg = (float) $ventas->sum('kilogramos');
        $totalVenta = (float) $ventas->sum('valor_venta');
        $precioProm = $totalKg > 0 ? round($totalVenta / $totalKg, 2) : 0.0;

        $totalRow = [
            'TOTAL GENERAL (' . $ventas->count() . ' registros)',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalKg,
            $precioProm,
            $totalVenta,
            (int) $ventas->sum('jabas'),
            '',
        ];

        $filename = 'panel_control_completo_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Resumen General', [
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
}
