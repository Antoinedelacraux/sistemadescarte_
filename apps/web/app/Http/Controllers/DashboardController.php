<?php

namespace App\Http\Controllers;

use App\Models\VentaDescarte;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load(['role', 'fundos']);

        // FundoScope aplica automáticamente el aislamiento de fundos para roles 'general' e 'individual'
        $ventas = VentaDescarte::with(['fundo', 'creator'])
            ->latest('fecha_produccion')
            ->get();

        return view('dashboard', [
            'user' => $user,
            'role' => $user->role,
            'fundos' => $user->fundos,
            'ventas' => $ventas,
        ]);
    }
}
