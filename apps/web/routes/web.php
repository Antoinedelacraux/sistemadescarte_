<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VentaDescarteController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::redirect('/dashboard', '/');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Módulo Panel: Dashboard (DashboardController implementa redirección inteligente si el usuario no tiene panel)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportar'])
        ->middleware('modulo:panel')
        ->name('dashboard.exportar');

    // Módulo Registro: Formulario y guardado de venta
    Route::middleware('modulo:registro')->group(function () {
        Route::get('/ventas/registrar', [VentaDescarteController::class, 'create'])->name('ventas.create');
        Route::post('/ventas', [VentaDescarteController::class, 'store'])->name('ventas.store');
        Route::get('/ventas/{venta}/editar', [VentaDescarteController::class, 'edit'])->name('ventas.edit');
        Route::put('/ventas/{venta}', [VentaDescarteController::class, 'update'])->name('ventas.update');
        Route::post('/ventas/{venta}/anular', [VentaDescarteController::class, 'anular'])->name('ventas.anular');
        Route::post('/ventas/{venta}/reactivar', [VentaDescarteController::class, 'reactivar'])->name('ventas.reactivar');
        Route::delete('/ventas/{venta}', [VentaDescarteController::class, 'destroy'])->name('ventas.destroy');
    });

    // Módulo Historial: Visualización y exportación de ventas
    Route::middleware('modulo:historial')->group(function () {
        Route::get('/ventas', [VentaDescarteController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/exportar', [VentaDescarteController::class, 'exportar'])->name('ventas.exportar');
        Route::get('/ventas/{venta}', [VentaDescarteController::class, 'show'])->name('ventas.show');
    });

    // Endpoint dinámico para carga de catálogos
    Route::get('/api/catalogo/lotes', [VentaDescarteController::class, 'getLotesPorFundo'])->name('api.lotes');

    // Módulo Administración: Solo usuarios con permiso de módulo admin
    Route::middleware('modulo:admin')->group(function () {
        Route::get('/administracion/fundos', [\App\Http\Controllers\AdminController::class, 'fundos'])->name('admin.fundos');
        Route::post('/administracion/fundos', [\App\Http\Controllers\AdminController::class, 'storeFundo'])->name('admin.fundos.store');
        Route::put('/administracion/fundos/{fundo}', [\App\Http\Controllers\AdminController::class, 'updateFundo'])->name('admin.fundos.update');
        Route::post('/administracion/fundos/{fundo}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleFundo'])->name('admin.fundos.toggle');
        Route::delete('/administracion/fundos/{fundo}', [\App\Http\Controllers\AdminController::class, 'destroyFundo'])->name('admin.fundos.destroy');

        // Gestión de Lotes dentro de Fundos
        Route::post('/administracion/fundos/{fundo}/lotes', [\App\Http\Controllers\AdminController::class, 'storeLote'])->name('admin.fundos.lotes.store');
        Route::delete('/administracion/lotes/{lote}', [\App\Http\Controllers\AdminController::class, 'destroyLote'])->name('admin.lotes.destroy');

        Route::get('/administracion/usuarios', [\App\Http\Controllers\AdminController::class, 'usuarios'])->name('admin.usuarios');
        Route::post('/administracion/usuarios', [\App\Http\Controllers\AdminController::class, 'storeUsuario'])->name('admin.usuarios.store');
        Route::put('/administracion/usuarios/{user}', [\App\Http\Controllers\AdminController::class, 'updateUsuario'])->name('admin.usuarios.update');
        Route::post('/administracion/usuarios/{user}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleUsuario'])->name('admin.usuarios.toggle');
        Route::delete('/administracion/usuarios/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUsuario'])->name('admin.usuarios.destroy');
    });
});

if (!app()->environment('production')) {
    Route::get('/dev/seed-test-vencimiento-5min', function () {
        // 1. Borrar todas las ventas de prueba anteriores
        \App\Models\VentaDescarte::withoutGlobalScopes()->forceDelete();

        // 2. Fundo AGRITAC y lotes
        $fundo = \App\Models\Fundo::where('code', 'AGRITAC')->first() ?? \App\Models\Fundo::first();
        $lotes = \App\Models\Lote::withoutGlobalScopes()->where('fundo_id', $fundo->id)->get();

        $registrador = \App\Models\User::where('email', 'registrador@fundo.test')->first()
            ?? \App\Models\User::first();

        $now = \Carbon\Carbon::now();

        // Configuración de los 5 registros en cadena escalonada de 2 minutos
        $datos = [
            [
                'minutos' => 2,
                'cliente' => '1. AGRO EXPORT CHINCHA S.A.C.',
                'ruc' => '20549812341',
                'lote_idx' => 0,
                'cuartel' => 'Cuartel 01',
                'motivo' => 'Campo',
                'tipo' => 'Granos',
                'kg' => 750.00,
                'precio' => 1.80,
            ],
            [
                'minutos' => 4,
                'cliente' => '2. FRUTAS DEL VALLE S.A.C.',
                'ruc' => '20549812342',
                'lote_idx' => 1,
                'cuartel' => 'Cuartel 02',
                'motivo' => 'Packing',
                'tipo' => 'Racimos',
                'kg' => 1240.00,
                'precio' => 2.10,
            ],
            [
                'minutos' => 6,
                'cliente' => '3. AGROINDUSTRIAL ICA S.A.',
                'ruc' => '20549812343',
                'lote_idx' => 2,
                'cuartel' => 'Cuartel 03',
                'motivo' => 'Campo',
                'tipo' => 'Granos',
                'kg' => 890.50,
                'precio' => 1.75,
            ],
            [
                'minutos' => 8,
                'cliente' => '4. COMERCIALIZADORA DEL SUR E.I.R.L.',
                'ruc' => '20549812344',
                'lote_idx' => 3,
                'cuartel' => 'Cuartel 04',
                'motivo' => 'Cosecha Nac.',
                'tipo' => 'Segunda',
                'kg' => 620.00,
                'precio' => 1.50,
            ],
            [
                'minutos' => 10,
                'cliente' => '5. EXPORTADORA SANTA RITA S.A.C.',
                'ruc' => '20549812345',
                'lote_idx' => 4,
                'cuartel' => 'Cuartel 05',
                'motivo' => 'Packing',
                'tipo' => 'Racimos',
                'kg' => 1500.00,
                'precio' => 2.20,
            ],
        ];

        $ventasCreadas = [];

        foreach ($datos as $item) {
            $created = $now->copy()->subHours(24)->addMinutes($item['minutos']);
            $lote = $lotes->skip($item['lote_idx'])->first() ?? $lotes->first();

            $venta = \App\Models\VentaDescarte::withoutGlobalScopes()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'fundo_id' => $fundo->id,
                'lote_id' => $lote?->id,
                'cuartel_manual' => $item['cuartel'],
                'fecha_produccion' => $now->copy()->subDay()->format('Y-m-d'),
                'motivo' => $item['motivo'],
                'tipo_descarte' => $item['tipo'],
                'precio' => $item['precio'],
                'kilogramos' => $item['kg'],
                'valor_venta' => round($item['precio'] * $item['kg'], 2),
                'cliente' => $item['cliente'],
                'ruc' => $item['ruc'],
                'jabas' => ceil($item['kg'] / 20),
                'peso_jaba' => 20.00,
                'created_by' => $registrador->id,
                'estado' => 'activo',
            ]);

            \Illuminate\Support\Facades\DB::table('ventas_descarte')->where('id', $venta->id)->update([
                'created_at' => $created->format('Y-m-d H:i:s'),
                'updated_at' => $created->format('Y-m-d H:i:s'),
            ]);

            $ventasCreadas[] = [
                'id' => $venta->id,
                'cliente' => $item['cliente'],
                'minutos_para_vencer' => $item['minutos'],
                'created_at' => $created->toDateTimeString(),
                'vence_a' => $created->copy()->addHours(24)->toDateTimeString(),
                'url_edit' => route('ventas.edit', $venta),
            ];
        }

        return response()->json([
            'success' => true,
            'now' => $now->toDateTimeString(),
            'total_registros' => count($ventasCreadas),
            'ventas_creadas' => $ventasCreadas
        ]);
    });

    Route::get('/dev/status-ventas', function () {
        $now = \Carbon\Carbon::now();
        $ventas = \App\Models\VentaDescarte::withoutGlobalScopes()->get()->map(function($v) use ($now) {
            $limite = $v->created_at->copy()->addHours(24);
            $diffSeconds = $limite->timestamp - $now->timestamp;
            return [
                'id' => $v->id,
                'cliente' => $v->cliente,
                'created_at' => $v->created_at->toDateTimeString(),
                'vence_a' => $limite->toDateTimeString(),
                'segundos_restantes' => $diffSeconds,
                'minutos_restantes' => round($diffSeconds / 60, 2),
                'esta_cerrado' => $v->isCerradoParaEdicion(),
            ];
        });

        return response()->json([
            'now' => $now->toDateTimeString(),
            'ventas' => $ventas,
        ]);
    });
}



