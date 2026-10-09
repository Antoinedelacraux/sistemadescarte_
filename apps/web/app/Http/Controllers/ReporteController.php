<?php

namespace App\Http\Controllers;

use App\Models\Fundo;
use App\Models\VentaDescarte;
use App\Services\ExcelExporter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    /**
     * Definición de columnas exportables con sus etiquetas legibles.
     */
    public const COLUMNAS_DISPONIBLES = [
        'fundo' => 'Fundo',
        'fecha_produccion' => 'Fecha de Producción',
        'lote' => 'Lote',
        'cuartel' => 'Cuartel',
        'motivo' => 'Motivo',
        'tipo_descarte' => 'Tipo de Descarte',
        'kilogramos' => 'Kilogramos Totales',
        'precio' => 'Precio por Kg (S/)',
        'valor_venta' => 'Valor Total (S/)',
        'jabas' => 'Cantidad de Jabas',
        'peso_jaba' => 'Peso de Jaba (kg)',
        'placa' => 'Placa de Vehículo',
        'conductor' => 'Nombre Conductor',
        'brevete' => 'Brevete',
        'ruc' => 'RUC Comprador',
        'cliente' => 'Cliente / Razón Social',
        'viaje' => 'Número de Viaje',
        'observacion' => 'Observaciones',
    ];

    /**
     * Pantalla de reportes y configuración de exportación.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Obtener datos respetando FundoScope (ventas activas)
        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel'])->activos()->latest('fecha_produccion');

        if ($request->filled('fundo_id')) {
            $query->where('fundo_id', $request->input('fundo_id'));
        }
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->input('motivo'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->input('fecha_hasta'));
        }

        $ventas = $query->get();

        // Métricas analíticas
        $totalKg = $ventas->sum('kilogramos');
        $totalMonto = $ventas->sum('valor_venta');
        $totalRegistros = $ventas->count();

        // Agrupación por Motivo
        $porMotivo = $ventas->groupBy('motivo')->map(function ($items, $motivo) {
            return [
                'motivo' => $motivo,
                'cantidad' => $items->count(),
                'kg' => $items->sum('kilogramos'),
                'monto' => $items->sum('valor_venta'),
            ];
        });

        // Agrupación por Fundo
        $porFundo = $ventas->groupBy(fn ($v) => $v->fundo?->name ?? 'Sin Fundo')->map(function ($items, $nombre) {
            return [
                'fundo' => $nombre,
                'cantidad' => $items->count(),
                'kg' => $items->sum('kilogramos'),
                'monto' => $items->sum('valor_venta'),
            ];
        });

        $fundos = $user->isAdmin() || $user->isAnalista()
            ? Fundo::where('is_active', true)->get()
            : $user->fundos()->where('is_active', true)->get();

        return view('reportes.index', [
            'ventas' => $ventas,
            'totalKg' => $totalKg,
            'totalMonto' => $totalMonto,
            'totalRegistros' => $totalRegistros,
            'porMotivo' => $porMotivo,
            'porFundo' => $porFundo,
            'fundos' => $fundos,
            'columnasDisponibles' => self::COLUMNAS_DISPONIBLES,
        ]);
    }

    /**
     * Descarga de datos en formato Excel nativo (.xlsx).
     */
    public function exportar(Request $request): Response
    {
        $columnasSeleccionadas = $request->input('columnas', array_keys(self::COLUMNAS_DISPONIBLES));
        if (empty($columnasSeleccionadas) || !is_array($columnasSeleccionadas)) {
            $columnasSeleccionadas = array_keys(self::COLUMNAS_DISPONIBLES);
        }

        // Filtrar solo columnas válidas
        $columnasSeleccionadas = array_values(array_intersect($columnasSeleccionadas, array_keys(self::COLUMNAS_DISPONIBLES)));

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel'])->activos()->latest('fecha_produccion');

        if ($request->filled('fundo_id')) {
            $query->where('fundo_id', $request->input('fundo_id'));
        }
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->input('motivo'));
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->input('fecha_hasta'));
        }

        $ventas = $query->get();

        $filename = 'ventas_descarte_' . date('Y-m-d_His') . '.xlsx';

        // Cabeceras seleccionadas
        $headers = [];
        foreach ($columnasSeleccionadas as $colKey) {
            $headers[] = self::COLUMNAS_DISPONIBLES[$colKey];
        }

        // Filas formateadas con valores tipados
        $dataRows = [];
        foreach ($ventas as $v) {
            $row = [];
            foreach ($columnasSeleccionadas as $colKey) {
                $row[] = match ($colKey) {
                    'fundo' => $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A',
                    'fecha_produccion' => $v->fecha_produccion ? $v->fecha_produccion->format('d/m/Y') : '',
                    'lote' => $v->lote?->nombre ?? '',
                    'cuartel' => $v->cuartel_manual ?? ($v->cuartel?->nombre ?? ''),
                    'motivo' => $v->motivo,
                    'tipo_descarte' => $v->tipo_descarte,
                    'kilogramos' => (float) $v->kilogramos,
                    'precio' => (float) $v->precio,
                    'valor_venta' => (float) $v->valor_venta,
                    'jabas' => $v->jabas !== null ? (int) $v->jabas : '',
                    'peso_jaba' => $v->peso_jaba !== null ? (float) $v->peso_jaba : '',
                    'placa' => (string) ($v->placa ?? ''),
                    'conductor' => (string) ($v->conductor ?? ''),
                    'brevete' => (string) ($v->brevete ?? ''),
                    'ruc' => (string) ($v->ruc ?? ''),
                    'cliente' => $v->cliente ?? '',
                    'viaje' => (string) ($v->viaje ?? ''),
                    'observacion' => (string) ($v->observacion ?? ''),
                    default => '',
                };
            }
            $dataRows[] = $row;
        }

        $columnTypes = [];
        foreach ($columnasSeleccionadas as $idx => $colKey) {
            $columnTypes[$idx] = match ($colKey) {
                'kilogramos', 'precio', 'valor_venta', 'peso_jaba' => 'decimal',
                'jabas' => 'integer',
                'fecha_produccion' => 'date',
                default => 'string',
            };
        }

        $totalRow = [];
        $isFirst = true;
        foreach ($columnasSeleccionadas as $colKey) {
            if ($isFirst) {
                $totalRow[] = 'TOTAL GENERAL (' . $ventas->count() . ' reg.)';
                $isFirst = false;
            } else {
                $totalRow[] = match ($colKey) {
                    'kilogramos' => (float) $ventas->sum('kilogramos'),
                    'valor_venta' => (float) $ventas->sum('valor_venta'),
                    'jabas' => (int) $ventas->sum('jabas'),
                    default => '',
                };
            }
        }

        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Ventas Descarte', [
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
