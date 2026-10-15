<?php

namespace App\Services;

class DashboardAnalyticsService
{
    /**
     * Definición oficial de motivos y sus tipos permitidos en el sistema.
     */
    public const MOTIVOS_DEFINICION = [
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

    /**
     * Estructura jerárquica de columnas para las matrices cruzadas por cliente.
     */
    public const COLUMNAS_MATRIZ = [
        'Campo' => [
            'titulo' => 'DESCARTE CAMPO',
            'badge' => 'campo',
            'tipos' => ['Granos', 'Racimos', 'Racimos con Plaga'],
        ],
        'Packing' => [
            'titulo' => 'DESCARTE PACKING',
            'badge' => 'packing',
            'tipos' => ['Granos', 'Racimos'],
        ],
        'Cosecha Nacional' => [
            'titulo' => 'COSECHA NACIONAL',
            'badge' => 'nacional',
            'tipos' => ['Granos', 'Racimos'],
        ],
    ];

    /**
     * Calcula la segregación operativa por motivo y tipo de descarte.
     *
     * @param \Illuminate\Support\Collection $ventas Colección de ventas filtradas
     * @param float $totalGeneralKg Sumatoria total de kilogramos
     * @return array
     */
    public function calcularSegregacionMotivos($ventas, float $totalGeneralKg): array
    {
        $segregacion = [];

        foreach (self::MOTIVOS_DEFINICION as $motivoKey => $meta) {
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
                    'porcentaje_kg' => round($porcentajeKg, 2),
                ];
            }

            $segregacion[$motivoKey] = [
                'meta' => $meta,
                'total_kg' => $totalKgMotivo,
                'total_venta' => $totalVentaMotivo,
                'precio_promedio' => $precioPromMotivo,
                'count' => $ventasMotivo->count(),
                'porcentaje_kg' => $totalGeneralKg > 0 ? round(($totalKgMotivo / $totalGeneralKg) * 100, 2) : 0,
                'tipos' => $filasTipos,
            ];
        }

        return $segregacion;
    }

    /**
     * Calcula la matriz cruzada de clientes para Kilogramos (KG) y Montos (S/.).
     *
     * @param \Illuminate\Support\Collection $ventas Colección de ventas filtradas
     * @return array
     */
    public function calcularMatrizClientes($ventas): array
    {
        $columnasMatriz = self::COLUMNAS_MATRIZ;
        $matrizClientes = [];
        $totalesColumnasKg = [];
        $totalesColumnasMonto = [];
        $granTotalMatrizKg = 0.0;
        $granTotalMatrizMonto = 0.0;

        foreach ($columnasMatriz as $motivoKey => $colDef) {
            foreach ($colDef['tipos'] as $tipoKey) {
                $totalesColumnasKg[$motivoKey][$tipoKey] = 0.0;
                $totalesColumnasMonto[$motivoKey][$tipoKey] = 0.0;
            }
        }

        foreach ($ventas as $v) {
            $clienteNombre = trim((string) ($v->cliente ?? ''));
            if ($clienteNombre === '') {
                $clienteNombre = trim((string) ($v->ruc ?? '')) !== '' ? 'RUC: ' . trim($v->ruc) : 'Sin Cliente Registrado';
            }

            $motivoRaw = strtolower(trim((string) $v->motivo));
            $motivoNorm = null;
            if (str_contains($motivoRaw, 'campo')) {
                $motivoNorm = 'Campo';
            } elseif (str_contains($motivoRaw, 'packing')) {
                $motivoNorm = 'Packing';
            } elseif (str_contains($motivoRaw, 'nacional') || str_contains($motivoRaw, 'cosecha')) {
                $motivoNorm = 'Cosecha Nacional';
            }

            $tipoRaw = strtolower(trim((string) $v->tipo_descarte));
            $tipoNorm = null;
            if ($tipoRaw === 'granos') {
                $tipoNorm = 'Granos';
            } elseif (str_contains($tipoRaw, 'plaga')) {
                $tipoNorm = 'Racimos con Plaga';
            } elseif ($tipoRaw === 'racimos') {
                $tipoNorm = 'Racimos';
            }

            $kg = (float) $v->kilogramos;
            $monto = (float) $v->valor_venta;

            if (!isset($matrizClientes[$clienteNombre])) {
                $matrizClientes[$clienteNombre] = [
                    'cliente' => $clienteNombre,
                    'ruc' => $v->ruc,
                    'kg' => [],
                    'monto' => [],
                    'total_kg' => 0.0,
                    'total_monto' => 0.0,
                ];
                foreach ($columnasMatriz as $mK => $cDef) {
                    foreach ($cDef['tipos'] as $tK) {
                        $matrizClientes[$clienteNombre]['kg'][$mK][$tK] = 0.0;
                        $matrizClientes[$clienteNombre]['monto'][$mK][$tK] = 0.0;
                    }
                }
            }

            if ($motivoNorm && $tipoNorm && isset($matrizClientes[$clienteNombre]['kg'][$motivoNorm][$tipoNorm])) {
                $matrizClientes[$clienteNombre]['kg'][$motivoNorm][$tipoNorm] += $kg;
                $matrizClientes[$clienteNombre]['monto'][$motivoNorm][$tipoNorm] += $monto;
                $matrizClientes[$clienteNombre]['total_kg'] += $kg;
                $matrizClientes[$clienteNombre]['total_monto'] += $monto;

                $totalesColumnasKg[$motivoNorm][$tipoNorm] += $kg;
                $totalesColumnasMonto[$motivoNorm][$tipoNorm] += $monto;
                $granTotalMatrizKg += $kg;
                $granTotalMatrizMonto += $monto;
            }
        }

        uasort($matrizClientes, fn ($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        return [
            'matrizClientes' => $matrizClientes,
            'totalesColumnasKg' => $totalesColumnasKg,
            'totalesColumnasMonto' => $totalesColumnasMonto,
            'granTotalMatrizKg' => $granTotalMatrizKg,
            'granTotalMatrizMonto' => $granTotalMatrizMonto,
            'columnasMatriz' => $columnasMatriz,
        ];
    }

    /**
     * Calcula la matriz de kilogramos por fundo, lote, motivo y tipo de descarte.
     * Mantiene la misma estructura de columnas que la matriz por cliente para
     * que ambas lecturas sean comparables en el dashboard.
     *
     * @param \Illuminate\Support\Collection $ventas Coleccion de ventas filtradas
     * @return array
     */
    public function calcularMatrizLotes($ventas): array
    {
        $columnasMatriz = self::COLUMNAS_MATRIZ;
        $matrizLotes = [];
        $totalesColumnasKg = [];
        $granTotalKg = 0.0;

        foreach ($columnasMatriz as $motivoKey => $colDef) {
            foreach ($colDef['tipos'] as $tipoKey) {
                $totalesColumnasKg[$motivoKey][$tipoKey] = 0.0;
            }
        }

        foreach ($ventas as $venta) {
            $loteId = $venta->lote_id ?: 'sin-lote';
            $fundoId = $venta->fundo_id ?: 'sin-fundo';
            $key = $fundoId . ':' . $loteId;
            $loteNombre = $venta->lote?->nombre ?: 'Sin lote registrado';
            $fundoNombre = $venta->fundo?->nombre_corto ?: ($venta->fundo?->name ?: 'Sin fundo');

            if (!isset($matrizLotes[$key])) {
                $matrizLotes[$key] = [
                    'fundo' => $fundoNombre,
                    'lote' => $loteNombre,
                    'kg' => [],
                    'total_kg' => 0.0,
                ];

                foreach ($columnasMatriz as $motivoKey => $colDef) {
                    foreach ($colDef['tipos'] as $tipoKey) {
                        $matrizLotes[$key]['kg'][$motivoKey][$tipoKey] = 0.0;
                    }
                }
            }

            $motivoNorm = $this->normalizarMotivo($venta->motivo);
            $tipoNorm = $this->normalizarTipoDescarte($venta->tipo_descarte);
            $kg = (float) $venta->kilogramos;

            if ($motivoNorm && $tipoNorm && isset($matrizLotes[$key]['kg'][$motivoNorm][$tipoNorm])) {
                $matrizLotes[$key]['kg'][$motivoNorm][$tipoNorm] += $kg;
                $matrizLotes[$key]['total_kg'] += $kg;
                $totalesColumnasKg[$motivoNorm][$tipoNorm] += $kg;
                $granTotalKg += $kg;
            }
        }

        uasort($matrizLotes, function (array $a, array $b): int {
            $totalCompare = $b['total_kg'] <=> $a['total_kg'];
            if ($totalCompare !== 0) {
                return $totalCompare;
            }

            return strcmp($a['fundo'] . ' ' . $a['lote'], $b['fundo'] . ' ' . $b['lote']);
        });

        return [
            'matrizLotes' => $matrizLotes,
            'totalesColumnasKg' => $totalesColumnasKg,
            'granTotalKg' => $granTotalKg,
            'columnasMatriz' => $columnasMatriz,
        ];
    }

    private function normalizarMotivo(?string $motivo): ?string
    {
        $motivoRaw = strtolower(trim((string) $motivo));

        if (str_contains($motivoRaw, 'campo')) {
            return 'Campo';
        }
        if (str_contains($motivoRaw, 'packing')) {
            return 'Packing';
        }
        if (str_contains($motivoRaw, 'nacional') || str_contains($motivoRaw, 'cosecha')) {
            return 'Cosecha Nacional';
        }

        return null;
    }

    private function normalizarTipoDescarte(?string $tipo): ?string
    {
        $tipoRaw = strtolower(trim((string) $tipo));

        if ($tipoRaw === 'granos') {
            return 'Granos';
        }
        if (str_contains($tipoRaw, 'plaga')) {
            return 'Racimos con Plaga';
        }
        if ($tipoRaw === 'racimos') {
            return 'Racimos';
        }

        return null;
    }
}
