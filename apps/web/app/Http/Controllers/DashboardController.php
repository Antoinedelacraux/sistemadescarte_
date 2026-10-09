<?php

namespace App\Http\Controllers;

use App\Models\VentaDescarte;
use Illuminate\Http\Request;
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
}
