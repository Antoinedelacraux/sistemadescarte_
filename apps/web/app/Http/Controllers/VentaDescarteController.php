<?php

namespace App\Http\Controllers;

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\VentaDescarte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VentaDescarteController extends Controller
{
    /**
     * Listado con filtros y paginación.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'creator', 'updater'])
            ->latest('fecha_produccion');

        // Filtro por Fundo (si tiene permisos)
        if ($request->filled('fundo_id')) {
            $query->where('fundo_id', $request->input('fundo_id'));
        }

        // Filtro por Motivo
        if ($request->filled('motivo')) {
            $query->where('motivo', $request->input('motivo'));
        }

        // Filtro por Tipo de Descarte
        if ($request->filled('tipo_descarte')) {
            $query->where('tipo_descarte', $request->input('tipo_descarte'));
        }

        // Filtro por Fecha
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->input('fecha_hasta'));
        }

        $ventas = $query->paginate(15)->withQueryString();

        // Fundos disponibles para el selector de filtros
        $fundos = $user->isAdmin() || $user->isAnalista()
            ? Fundo::where('is_active', true)->get()
            : $user->fundos()->where('is_active', true)->get();

        return view('ventas.index', compact('ventas', 'fundos'));
    }

    /**
     * Formulario para registrar nueva venta de descarte.
     */
    public function create(): View
    {
        $user = Auth::user();

        $fundos = $user->isAdmin() || $user->isAnalista()
            ? Fundo::where('is_active', true)->with('lotes.cuarteles')->get()
            : $user->fundos()->where('is_active', true)->with('lotes.cuarteles')->get();

        // Lotes disponibles para el primer fundo
        $primerFundoId = $fundos->first()?->id;
        $lotes = $primerFundoId
            ? Lote::where('fundo_id', $primerFundoId)->with('cuarteles')->get()
            : collect();

        return view('ventas.create', compact('fundos', 'lotes'));
    }

    /**
     * Almacena una nueva venta de descarte validando reglas de negocio.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Validar fondos accesibles
        $fundosAccesibles = ($user->isAdmin() || $user->isAnalista())
            ? Fundo::pluck('id')->toArray()
            : $user->fundos()->pluck('fundos.id')->toArray();

        $validated = $request->validate([
            'fundo_id' => ['required', 'integer', Rule::in($fundosAccesibles)],
            'fecha_produccion' => ['required', 'date'],
            'motivo' => ['required', 'string', Rule::in(['Campo', 'Packing', 'Cosecha Nacional'])],
            'tipo_descarte' => [
                'required',
                'string',
                Rule::in(['Racimos', 'Racimos con plaga', 'Granos']),
                function ($attribute, $value, $fail) use ($request) {
                    $motivo = $request->input('motivo');
                    if (in_array($motivo, ['Cosecha Nacional', 'Packing']) && !in_array($value, ['Racimos', 'Granos'])) {
                        $fail("Para {$motivo} únicamente se permiten dos tipos de descarte: Racimos y Granos.");
                    }
                },
            ],
            'lote_id' => [
                'required',
                'integer',
                Rule::exists('lotes', 'id')->where(function ($query) use ($request) {
                    return $query->where('fundo_id', $request->input('fundo_id'));
                }),
            ],
            'cuartel_id' => ['nullable', 'integer', 'exists:cuarteles,id'],
            'cuartel_manual' => [
                Rule::requiredIf(fn () => $request->input('motivo') === 'Cosecha Nacional' && empty($request->input('cuartel_id'))),
                'nullable',
                'string',
                'max:100',
            ],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'kilogramos' => ['required', 'numeric', 'min:0.01'],
            'jabas' => ['nullable', 'integer', 'min:0'],
            'peso_jaba' => ['nullable', 'numeric', 'min:0'],
            'brevete' => ['nullable', 'string', 'max:50'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'cliente' => ['nullable', 'string', 'max:150'],
            'placa' => ['nullable', 'string', 'max:20'],
            'conductor' => ['nullable', 'string', 'max:150'],
            'viaje' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ], [
            'fundo_id.in' => 'No tienes permisos para registrar en el fundo seleccionado.',
            'lote_id.required' => 'El lote es obligatorio.',
            'lote_id.exists' => 'El lote seleccionado no pertenece al fundo indicado.',
            'cuartel_manual.required' => 'El cuartel es obligatorio cuando el motivo es Cosecha Nacional.',
            'precio.required' => 'El precio por kilogramo es obligatorio.',
            'kilogramos.required' => 'Los kilogramos totales son obligatorios.',
        ]);

        // Cálculo exacto del valor total (precio * kilogramos)
        $valorVenta = round((float) $validated['precio'] * (float) $validated['kilogramos'], 2);

        // Si se seleccionó cuartel_id, extraer su nombre si cuartel_manual está vacío
        if (!empty($validated['cuartel_id']) && empty($validated['cuartel_manual'])) {
            $cuartelObj = Cuartel::find($validated['cuartel_id']);
            $validated['cuartel_manual'] = $cuartelObj?->nombre;
        }

        $validated['valor_venta'] = $valorVenta;
        $validated['created_by'] = $user->id;

        VentaDescarte::create($validated);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta de descarte registrada exitosamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(VentaDescarte $venta): View
    {
        $user = Auth::user();

        $fundos = $user->isAdmin() || $user->isAnalista()
            ? Fundo::where('is_active', true)->with('lotes.cuarteles')->get()
            : $user->fundos()->where('is_active', true)->with('lotes.cuarteles')->get();

        $lotes = Lote::where('fundo_id', $venta->fundo_id)->with('cuarteles')->get();

        return view('ventas.edit', compact('venta', 'fundos', 'lotes'));
    }

    /**
     * Actualiza una venta registrando quién la modificó.
     */
    public function update(Request $request, VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();

        $fundosAccesibles = ($user->isAdmin() || $user->isAnalista())
            ? Fundo::pluck('id')->toArray()
            : $user->fundos()->pluck('fundos.id')->toArray();

        $validated = $request->validate([
            'fundo_id' => ['required', 'integer', Rule::in($fundosAccesibles)],
            'fecha_produccion' => ['required', 'date'],
            'motivo' => ['required', 'string', Rule::in(['Campo', 'Packing', 'Cosecha Nacional'])],
            'tipo_descarte' => [
                'required',
                'string',
                Rule::in(['Racimos', 'Racimos con plaga', 'Granos']),
                function ($attribute, $value, $fail) use ($request) {
                    $motivo = $request->input('motivo');
                    if (in_array($motivo, ['Cosecha Nacional', 'Packing']) && !in_array($value, ['Racimos', 'Granos'])) {
                        $fail("Para {$motivo} únicamente se permiten dos tipos de descarte: Racimos y Granos.");
                    }
                },
            ],
            'lote_id' => [
                'required',
                'integer',
                Rule::exists('lotes', 'id')->where(function ($query) use ($request) {
                    return $query->where('fundo_id', $request->input('fundo_id'));
                }),
            ],
            'cuartel_id' => ['nullable', 'integer', 'exists:cuarteles,id'],
            'cuartel_manual' => [
                Rule::requiredIf(fn () => $request->input('motivo') === 'Cosecha Nacional' && empty($request->input('cuartel_id'))),
                'nullable',
                'string',
                'max:100',
            ],
            'precio' => ['required', 'numeric', 'min:0.01'],
            'kilogramos' => ['required', 'numeric', 'min:0.01'],
            'jabas' => ['nullable', 'integer', 'min:0'],
            'peso_jaba' => ['nullable', 'numeric', 'min:0'],
            'brevete' => ['nullable', 'string', 'max:50'],
            'ruc' => ['nullable', 'string', 'max:20'],
            'cliente' => ['nullable', 'string', 'max:150'],
            'placa' => ['nullable', 'string', 'max:20'],
            'conductor' => ['nullable', 'string', 'max:150'],
            'viaje' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $valorVenta = round((float) $validated['precio'] * (float) $validated['kilogramos'], 2);

        if (!empty($validated['cuartel_id']) && empty($validated['cuartel_manual'])) {
            $cuartelObj = Cuartel::find($validated['cuartel_id']);
            $validated['cuartel_manual'] = $cuartelObj?->nombre;
        }

        $validated['valor_venta'] = $valorVenta;
        $validated['updated_by'] = $user->id;

        $venta->update($validated);

        return redirect()->route('ventas.index')
            ->with('success', 'Registro de venta actualizado correctamente.');
    }

    /**
     * Endpoint API para obtener lotes y cuarteles al cambiar de fundo.
     */
    public function getLotesPorFundo(Request $request)
    {
        $fundoId = $request->query('fundo_id');
        $user = Auth::user();

        // Validar acceso al fundo
        if (!$user->isAdmin() && !$user->isAnalista()) {
            if (!$user->fundos()->where('fundos.id', $fundoId)->exists()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        $lotes = Lote::where('fundo_id', $fundoId)
            ->with('cuarteles')
            ->orderBy('nombre')
            ->get();

        return response()->json($lotes);
    }
}
