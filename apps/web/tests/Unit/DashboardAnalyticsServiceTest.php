<?php

namespace Tests\Unit;

use App\Services\DashboardAnalyticsService;
use PHPUnit\Framework\TestCase;

class DashboardAnalyticsServiceTest extends TestCase
{
    private DashboardAnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardAnalyticsService();
    }

    public function test_calcular_segregacion_motivos_devuelve_estructura_completa(): void
    {
        $ventas = collect([
            (object) [
                'motivo' => 'Campo',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 100.0,
                'valor_venta' => 200.0,
            ],
            (object) [
                'motivo' => 'Campo',
                'tipo_descarte' => 'Granos',
                'kilogramos' => 50.0,
                'valor_venta' => 75.0,
            ],
            (object) [
                'motivo' => 'Cosecha Nacional',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 200.0,
                'valor_venta' => 600.0,
            ],
        ]);

        $totalKg = 350.0;
        $segregacion = $this->service->calcularSegregacionMotivos($ventas, $totalKg);

        $this->assertArrayHasKey('Cosecha Nacional', $segregacion);
        $this->assertArrayHasKey('Campo', $segregacion);
        $this->assertArrayHasKey('Packing', $segregacion);

        $this->assertEquals(150.0, $segregacion['Campo']['total_kg']);
        $this->assertEquals(275.0, $segregacion['Campo']['total_venta']);
        $this->assertEquals(200.0, $segregacion['Cosecha Nacional']['total_kg']);
        $this->assertEquals(600.0, $segregacion['Cosecha Nacional']['total_venta']);
        $this->assertEquals(0.0, $segregacion['Packing']['total_kg']);
    }

    public function test_calcular_matriz_clientes_genera_matrices_cruzadas(): void
    {
        $ventas = collect([
            (object) [
                'cliente' => 'AGRICOLA SAN JOSE',
                'ruc' => '20123456789',
                'motivo' => 'Campo',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 500.0,
                'valor_venta' => 1000.0,
            ],
            (object) [
                'cliente' => 'AGRICOLA SAN JOSE',
                'ruc' => '20123456789',
                'motivo' => 'Packing',
                'tipo_descarte' => 'Granos',
                'kilogramos' => 200.0,
                'valor_venta' => 300.0,
            ],
            (object) [
                'cliente' => 'FRUTAS DEL NORTE',
                'ruc' => '20987654321',
                'motivo' => 'Cosecha Nacional',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 150.0,
                'valor_venta' => 450.0,
            ],
        ]);

        $resultado = $this->service->calcularMatrizClientes($ventas);

        $this->assertArrayHasKey('matrizClientes', $resultado);
        $this->assertArrayHasKey('totalesColumnasKg', $resultado);
        $this->assertArrayHasKey('totalesColumnasMonto', $resultado);
        $this->assertArrayHasKey('granTotalMatrizKg', $resultado);
        $this->assertArrayHasKey('granTotalMatrizMonto', $resultado);

        $this->assertEquals(850.0, $resultado['granTotalMatrizKg']);
        $this->assertEquals(1750.0, $resultado['granTotalMatrizMonto']);

        $matriz = $resultado['matrizClientes'];
        $this->assertArrayHasKey('AGRICOLA SAN JOSE', $matriz);
        $this->assertArrayHasKey('FRUTAS DEL NORTE', $matriz);

        $this->assertEquals(700.0, $matriz['AGRICOLA SAN JOSE']['total_kg']);
        $this->assertEquals(1300.0, $matriz['AGRICOLA SAN JOSE']['total_monto']);
        $this->assertEquals(500.0, $matriz['AGRICOLA SAN JOSE']['kg']['Campo']['Racimos']);
        $this->assertEquals(200.0, $matriz['AGRICOLA SAN JOSE']['kg']['Packing']['Granos']);
    }

    public function test_calcular_matriz_lotes_acumula_kilogramos_por_fundo_lote_motivo_y_tipo(): void
    {
        $ventas = collect([
            (object) [
                'fundo_id' => 1,
                'lote_id' => 10,
                'fundo' => (object) ['nombre_corto' => 'AGRITAC'],
                'lote' => (object) ['nombre' => 'A01'],
                'motivo' => 'Campo',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 500.0,
            ],
            (object) [
                'fundo_id' => 1,
                'lote_id' => 10,
                'fundo' => (object) ['nombre_corto' => 'AGRITAC'],
                'lote' => (object) ['nombre' => 'A01'],
                'motivo' => 'Packing',
                'tipo_descarte' => 'Granos',
                'kilogramos' => 200.0,
            ],
            (object) [
                'fundo_id' => 2,
                'lote_id' => 10,
                'fundo' => (object) ['nombre_corto' => 'PROCOM'],
                'lote' => (object) ['nombre' => 'A01'],
                'motivo' => 'Cosecha Nacional',
                'tipo_descarte' => 'Racimos',
                'kilogramos' => 150.0,
            ],
        ]);

        $resultado = $this->service->calcularMatrizLotes($ventas);

        $this->assertCount(2, $resultado['matrizLotes']);
        $this->assertEquals(850.0, $resultado['granTotalKg']);
        $this->assertEquals(500.0, $resultado['totalesColumnasKg']['Campo']['Racimos']);
        $this->assertEquals(200.0, $resultado['totalesColumnasKg']['Packing']['Granos']);
        $this->assertEquals(150.0, $resultado['totalesColumnasKg']['Cosecha Nacional']['Racimos']);
        $this->assertEquals(700.0, $resultado['matrizLotes']['1:10']['total_kg']);
        $this->assertEquals(150.0, $resultado['matrizLotes']['2:10']['total_kg']);
    }
}
