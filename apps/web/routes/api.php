<?php

use App\Http\Controllers\Api\VentasApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Integración Power BI (solo lectura)
|--------------------------------------------------------------------------
|
| Estas rutas se autentican con token Bearer estático (POWERBI_API_TOKEN).
| No usan sesión de navegador ni Sanctum.
|
| Todas las respuestas son JSON. No hay rutas de escritura.
|
*/

Route::middleware('api.token')->prefix('v1')->group(function () {

    // Health-check: verifica que la API está activa y el token es válido
    Route::get('/estado', [VentasApiController::class, 'estado'])
        ->name('api.v1.estado');

    // Historial de ventas/descarte — endpoint principal para Power BI
    // Filtros opcionales: ?fecha_desde=Y-m-d &fecha_hasta=Y-m-d &fundo_id=1 &estado=activo &per_page=500
    Route::get('/ventas-descarte', [VentasApiController::class, 'index'])
        ->name('api.v1.ventas-descarte');

    // Catálogo de fundos activos (para tablas de referencia en Power BI)
    Route::get('/fundos', [VentasApiController::class, 'fundos'])
        ->name('api.v1.fundos');

});
