<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VentaDescarteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Exportación directa a Excel
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportar'])->name('dashboard.exportar');
    Route::get('/ventas/exportar', [VentaDescarteController::class, 'exportar'])->name('ventas.exportar');

    // Módulo de Venta de Descarte
    Route::get('/ventas', [VentaDescarteController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/registrar', [VentaDescarteController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaDescarteController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaDescarteController::class, 'show'])->name('ventas.show');
    Route::get('/ventas/{venta}/editar', [VentaDescarteController::class, 'edit'])->name('ventas.edit');
    Route::put('/ventas/{venta}', [VentaDescarteController::class, 'update'])->name('ventas.update');
    Route::post('/ventas/{venta}/anular', [VentaDescarteController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/{venta}/reactivar', [VentaDescarteController::class, 'reactivar'])->name('ventas.reactivar');
    Route::delete('/ventas/{venta}', [VentaDescarteController::class, 'destroy'])->name('ventas.destroy');

    // Endpoint dinámico para carga de catálogos
    Route::get('/api/catalogo/lotes', [VentaDescarteController::class, 'getLotesPorFundo'])->name('api.lotes');

    // Módulo de Reportes y Exportación Excel
    Route::get('/reportes', [\App\Http\Controllers\ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/exportar', [\App\Http\Controllers\ReporteController::class, 'exportar'])->name('reportes.exportar');

    // Módulo de Administración (Solo Administrador)
    Route::get('/administracion/fundos', [\App\Http\Controllers\AdminController::class, 'fundos'])->name('admin.fundos');
    Route::post('/administracion/fundos', [\App\Http\Controllers\AdminController::class, 'storeFundo'])->name('admin.fundos.store');
    Route::put('/administracion/fundos/{fundo}', [\App\Http\Controllers\AdminController::class, 'updateFundo'])->name('admin.fundos.update');
    Route::post('/administracion/fundos/{fundo}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleFundo'])->name('admin.fundos.toggle');
    Route::delete('/administracion/fundos/{fundo}', [\App\Http\Controllers\AdminController::class, 'destroyFundo'])->name('admin.fundos.destroy');

    // Gestión de Lotes y Cuarteles dentro de Fundos
    Route::post('/administracion/fundos/{fundo}/lotes', [\App\Http\Controllers\AdminController::class, 'storeLote'])->name('admin.fundos.lotes.store');
    Route::delete('/administracion/lotes/{lote}', [\App\Http\Controllers\AdminController::class, 'destroyLote'])->name('admin.lotes.destroy');
    Route::post('/administracion/lotes/{lote}/cuarteles', [\App\Http\Controllers\AdminController::class, 'storeCuartel'])->name('admin.lotes.cuarteles.store');
    Route::delete('/administracion/cuarteles/{cuartel}', [\App\Http\Controllers\AdminController::class, 'destroyCuartel'])->name('admin.cuarteles.destroy');

    Route::get('/administracion/usuarios', [\App\Http\Controllers\AdminController::class, 'usuarios'])->name('admin.usuarios');
    Route::post('/administracion/usuarios', [\App\Http\Controllers\AdminController::class, 'storeUsuario'])->name('admin.usuarios.store');
    Route::put('/administracion/usuarios/{user}', [\App\Http\Controllers\AdminController::class, 'updateUsuario'])->name('admin.usuarios.update');
    Route::post('/administracion/usuarios/{user}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleUsuario'])->name('admin.usuarios.toggle');
    Route::delete('/administracion/usuarios/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');
});
