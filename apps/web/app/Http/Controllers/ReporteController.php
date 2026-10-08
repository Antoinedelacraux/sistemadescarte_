<?php

namespace App\Http\Controllers;

use App\Models\Fundo;
use App\Models\VentaDescarte;
use Illuminate\Http\Request;
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
        'viaje' => 'Número de Viaje',
        'observacion' => 'Observaciones',
    ];

    /**
     * Pantalla de reportes y configuración de exportación.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Obtener datos respetando FundoScope
        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel'])->latest('fecha_produccion');

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
     * Descarga de datos en formato Excel compatible (.csv con BOM UTF-8 y delimitador punto y coma).
     */
    public function exportar(Request $request): StreamedResponse
    {
        $columnasSeleccionadas = $request->input('columnas', array_keys(self::COLUMNAS_DISPONIBLES));
        if (empty($columnasSeleccionadas) || !is_array($columnasSeleccionadas)) {
            $columnasSeleccionadas = array_keys(self::COLUMNAS_DISPONIBLES);
        }

        // Filtrar solo columnas válidas
        $columnasSeleccionadas = array_values(array_intersect($columnasSeleccionadas, array_keys(self::COLUMNAS_DISPONIBLES)));

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel'])->latest('fecha_produccion');

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

        $filename = 'ventas_descarte_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($ventas, $columnasSeleccionadas) {
            $handle = fopen('php://output', 'w');

            // BOM para compatibilidad con Microsoft Excel (reconocimiento UTF-8 de tildes y caracteres en español)
            fputs($handle, "\xEF\xBB\xBF");

            // Cabeceras de columnas seleccionadas
            $headerRow = [];
            foreach ($columnasSeleccionadas as $colKey) {
                $headerRow[] = self::COLUMNAS_DISPONIBLES[$colKey];
            }
            fputcsv($handle, $headerRow, ';');

            // Filas de datos
            foreach ($ventas as $v) {
                $row = [];
                foreach ($columnasSeleccionadas as $colKey) {
                    $row[] = match ($colKey) {
                        'fundo' => $v->fundo?->name ?? 'N/A',
                        'fecha_produccion' => $v->fecha_produccion->format('d/m/Y'),
                        'lote' => $v->lote?->nombre ?? '',
                        'cuartel' => $v->cuartel_manual ?? ($v->cuartel?->nombre ?? ''),
                        'motivo' => $v->motivo,
                        'tipo_descarte' => $v->tipo_descarte,
                        'kilogramos' => number_format($v->kilogramos, 2, '.', ''),
                        'precio' => number_format($v->precio, 2, '.', ''),
                        'valor_venta' => number_format($v->valor_venta, 2, '.', ''),
                        'jabas' => $v->jabas ?? '',
                        'peso_jaba' => $v->peso_jaba ? number_format($v->peso_jaba, 2, '.', '') : '',
                        'placa' => $v->placa ?? '',
                        'conductor' => $v->conductor ?? '',
                        'brevete' => $v->brevete ?? '',
                        'ruc' => $v->ruc ?? '',
                        'viaje' => $v->viaje ?? '',
                        'observacion' => $v->observacion ?? '',
                        default => '',
                    };
                }
                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
