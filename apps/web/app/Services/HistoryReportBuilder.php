<?php

namespace App\Services;

use App\Models\VentaDescarte;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class HistoryReportBuilder
{
    /**
     * @param  iterable<int, VentaDescarte>  $ventas
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>, columnTypes: array<int, string>, columnAlignments: array<int, string>, columnWidths: array<int, int>}
     */
    public static function ventas(iterable $ventas): array
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
            'PESO / JABA (KG)',
            'PLACA VEHÍCULO',
            'CONDUCTOR',
            'BREVETE',
            'N° VIAJE',
            'ESTADO',
            'OBSERVACIONES',
            'REGISTRADO POR',
            'FECHA Y HORA REGISTRO',
            'FECHA Y HORA VALIDACIÓN GARITA',
        ];

        $columnTypes = [
            'string', 'date', 'string', 'string', 'string', 'string', 'string', 'string',
            'decimal', 'decimal', 'decimal', 'integer', 'decimal', 'string', 'string', 'string',
            'string', 'string', 'string', 'string', 'date', 'date',
        ];
        $columnAlignments = [
            'center', 'center', 'center', 'center', 'center', 'center', 'center', 'center',
            'left', 'left', 'left', 'left', 'left', 'center', 'center', 'center',
            'left', 'center', 'center', 'center', 'center', 'center',
        ];

        $rows = [];
        foreach ($ventas as $venta) {
            $rows[] = [
                $venta->fundo?->nombre_corto ?? $venta->fundo?->name ?? 'N/A',
                $venta->fecha_produccion?->format('d/m/Y') ?? '',
                $venta->cliente ?: 'Venta General',
                (string) ($venta->ruc ?: ''),
                $venta->lote?->nombre ?? '',
                $venta->cuartel_manual ?? ($venta->cuartel?->nombre ?? ''),
                $venta->motivo,
                $venta->tipo_descarte,
                (float) $venta->kilogramos,
                (float) $venta->precio,
                (float) $venta->valor_venta,
                $venta->jabas !== null ? (int) $venta->jabas : '',
                $venta->peso_jaba !== null ? (float) $venta->peso_jaba : '',
                (string) ($venta->placa ?? ''),
                (string) ($venta->conductor ?? ''),
                (string) ($venta->brevete ?? ''),
                (string) ($venta->viaje ?? ''),
                $venta->isAnulado() ? 'ANULADO' : 'ACTIVO',
                (string) ($venta->observacion ?? ''),
                $venta->creator?->name ?? 'Sistema',
                $venta->created_at?->format('d/m/Y H:i') ?? '',
                $venta->salida_garita_at?->format('d/m/Y H:i') ?? '',
            ];
        }

        return self::validatedReport(
            $headers,
            $rows,
            $columnTypes,
            $columnAlignments,
            [16, 17, 30, 17, 15, 15, 22, 22, 18, 18, 19, 18, 18, 18, 25, 18, 14, 15, 38, 24, 24, 28],
        );
    }

    /**
     * @param  iterable<int|string, array<string, mixed>>  $viajes
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>, columnTypes: array<int, string>, columnAlignments: array<int, string>, columnWidths: array<int, int>}
     */
    public static function garita(iterable $viajes, bool $includeFundo): array
    {
        $headers = [
            'FECHA DE PRODUCCIÓN',
            'FECHA Y HORA DE REGISTRO',
            'NÚMERO DE VIAJE',
            'CLIENTE',
            'NOMBRE DEL CONDUCTOR',
            'PLACA',
            'CANTIDAD DE JABAS',
            'KILOGRAMOS',
            'LOTES DEL ENVÍO',
            'FECHA Y HORA DE VALIDACIÓN',
        ];
        $columnTypes = ['date', 'date', 'string', 'string', 'string', 'string', 'integer', 'decimal', 'string', 'date'];
        $columnAlignments = ['center', 'center', 'left', 'center', 'center', 'center', 'left', 'left', 'center', 'center'];
        $columnWidths = [20, 25, 18, 28, 28, 16, 20, 18, 25, 28];

        if ($includeFundo) {
            array_unshift($headers, 'FUNDO');
            array_unshift($columnTypes, 'string');
            array_unshift($columnAlignments, 'center');
            array_unshift($columnWidths, 20);
        }

        $rows = [];
        foreach ($viajes as $viaje) {
            $row = [
                $viaje['fecha_produccion']?->format('d/m/Y') ?? '',
                $viaje['fecha_hora_registro']?->format('d/m/Y H:i') ?? '',
                $viaje['viaje'],
                $viaje['cliente'],
                $viaje['conductor'],
                $viaje['placa'],
                (int) $viaje['total_jabas'],
                (float) $viaje['total_kilos'],
                $viaje['lotes'] instanceof Collection ? $viaje['lotes']->implode(', ') : implode(', ', (array) $viaje['lotes']),
                $viaje['fecha_validacion']?->format('d/m/Y H:i') ?? '',
            ];
            if ($includeFundo) {
                array_unshift($row, $viaje['fundo_nombre']);
            }
            $rows[] = $row;
        }

        return self::validatedReport($headers, $rows, $columnTypes, $columnAlignments, $columnWidths);
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>, columnTypes: array<int, string>, columnAlignments: array<int, string>, columnWidths: array<int, int>}
     */
    private static function validatedReport(
        array $headers,
        array $rows,
        array $columnTypes,
        array $columnAlignments,
        array $columnWidths,
    ): array {
        $expected = count($headers);
        if ($expected === 0 || count($columnTypes) !== $expected || count($columnAlignments) !== $expected || count($columnWidths) !== $expected) {
            throw new InvalidArgumentException('La definición del reporte no mantiene el mismo número de columnas.');
        }
        foreach ($columnAlignments as $alignment) {
            if (! in_array($alignment, ['left', 'center', 'right'], true)) {
                throw new InvalidArgumentException('La definición del reporte contiene una alineación no válida.');
            }
        }
        foreach ($rows as $index => $row) {
            if (count($row) !== $expected) {
                throw new InvalidArgumentException("La fila {$index} no coincide con las columnas del reporte.");
            }
        }

        return compact('headers', 'rows', 'columnTypes', 'columnAlignments', 'columnWidths');
    }
}
