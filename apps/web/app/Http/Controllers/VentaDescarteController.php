<?php

namespace App\Http\Controllers;

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\VentaDescarte;
use App\Services\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VentaDescarteController extends Controller
{
    /**
     * Exporta el historial de ventas a Excel (.xlsx) nativo aplicando todos los filtros activos.
     */
    public function exportar(Request $request): Response
    {
        $user = Auth::user();

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'creator', 'updater', 'anulador'])
            ->latest('fecha_produccion');

        // Filtro por Estado (activo, anulado, o todos)
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'activo') {
                $query->activos();
            } elseif ($request->input('estado') === 'anulado') {
                $query->anulados();
            }
        }

        // Filtro por Fundo
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

        // Filtro por Rango de Fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_produccion', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_produccion', '<=', $request->input('fecha_hasta'));
        }

        // Filtro por Búsqueda (cliente o ruc)
        if ($request->filled('buscar')) {
            $term = '%' . $request->input('buscar') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('cliente', 'like', $term)
                  ->orWhere('ruc', 'like', $term);
            });
        }

        $ventas = $query->get();

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
        ];

        $columnTypes = [
            0 => 'string',   // FUNDO: AGRITAC, PROCOM, EL NEGRO (sin paréntesis)
            1 => 'date',     // FECHA
            2 => 'string',   // CLIENTE
            3 => 'string',   // RUC (tratado como texto explícito sin separador ni exponencial)
            4 => 'string',   // LOTE
            5 => 'string',   // CUARTEL
            6 => 'string',   // MOTIVO
            7 => 'string',   // TIPO DESCARTE
            8 => 'decimal',  // KILOS
            9 => 'decimal',  // PRECIO
            10 => 'decimal', // TOTAL
            11 => 'integer', // JABAS
            12 => 'decimal', // PESO JABA
            13 => 'string',  // PLACA
            14 => 'string',  // CONDUCTOR
            15 => 'string',  // BREVETE
            16 => 'string',  // VIAJE
            17 => 'string',  // ESTADO
            18 => 'string',  // OBSERVACIONES
            19 => 'string',  // REGISTRADO POR
            20 => 'date',    // FECHA REGISTRO
        ];

        $dataRows = [];
        foreach ($ventas as $v) {
            $fundoNombre = $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A';
            $dataRows[] = [
                $fundoNombre,
                $v->fecha_produccion ? $v->fecha_produccion->format('d/m/Y') : '',
                $v->cliente ?: 'Venta General',
                (string) ($v->ruc ?: ''),
                $v->lote?->nombre ?? '',
                $v->cuartel_manual ?? ($v->cuartel?->nombre ?? ''),
                $v->motivo,
                $v->tipo_descarte,
                (float) $v->kilogramos,
                (float) $v->precio,
                (float) $v->valor_venta,
                $v->jabas !== null ? (int) $v->jabas : '',
                $v->peso_jaba !== null ? (float) $v->peso_jaba : '',
                (string) ($v->placa ?? ''),
                (string) ($v->conductor ?? ''),
                (string) ($v->brevete ?? ''),
                (string) ($v->viaje ?? ''),
                $v->isAnulado() ? 'ANULADO' : 'ACTIVO',
                (string) ($v->observacion ?? ''),
                $v->creator?->name ?? 'Sistema',
                $v->created_at ? $v->created_at->format('d/m/Y H:i') : '',
            ];
        }

        $totalKilos = (float) $ventas->sum('kilogramos');
        $totalVentas = (float) $ventas->sum('valor_venta');
        $precioPromedio = $totalKilos > 0 ? (float) round($totalVentas / $totalKilos, 2) : 0.00;
        $totalJabas = (int) $ventas->sum('jabas');

        $totalRow = [
            'TOTAL GENERAL (' . $ventas->count() . ' registros)',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $totalKilos,
            $precioPromedio,
            $totalVentas,
            $totalJabas > 0 ? $totalJabas : '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        $filename = 'ventas_historial_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Historial Ventas', [
            'columnTypes' => $columnTypes,
            'totalRow' => $totalRow,
        ]);

        return response($xlsxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0, must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => strlen($xlsxBinary),
        ]);
    }
    /**
     * Listado con filtros y paginación.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'creator', 'updater', 'anulador'])
            ->latest('fecha_produccion');

        // Filtro por Estado (activo, anulado, o todos)
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'activo') {
                $query->activos();
            } elseif ($request->input('estado') === 'anulado') {
                $query->anulados();
            }
        }

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
     * Visualiza el contenido detallado de un registro de venta.
     */
    public function show(VentaDescarte $venta): View
    {
        $venta->load(['fundo', 'lote', 'cuartel', 'creator', 'updater', 'anulador']);
        return view('ventas.show', compact('venta'));
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

        // Reconocimiento y guardado automático de cuartel en el catálogo del lote
        $cuartelNombre = trim((string) ($request->input('cuartel_manual') ?? ''));
        if ($cuartelNombre === '' && !empty($validated['cuartel_id'])) {
            $cuartelObj = Cuartel::find($validated['cuartel_id']);
            $cuartelNombre = $cuartelObj?->nombre ?? '';
        }

        if ($cuartelNombre !== '' && !empty($validated['lote_id'])) {
            $cuartelRecord = Cuartel::where('lote_id', $validated['lote_id'])
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($cuartelNombre)])
                ->first();

            if (!$cuartelRecord) {
                $cuartelRecord = Cuartel::create([
                    'lote_id' => $validated['lote_id'],
                    'nombre' => $cuartelNombre,
                ]);
            }

            $validated['cuartel_id'] = $cuartelRecord->id;
            $validated['cuartel_manual'] = $cuartelRecord->nombre;
        } else {
            $validated['cuartel_id'] = null;
            $validated['cuartel_manual'] = null;
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

        // Reconocimiento y guardado automático de cuartel en el catálogo del lote
        $cuartelNombre = trim((string) ($request->input('cuartel_manual') ?? ''));
        if ($cuartelNombre === '' && !empty($validated['cuartel_id'])) {
            $cuartelObj = Cuartel::find($validated['cuartel_id']);
            $cuartelNombre = $cuartelObj?->nombre ?? '';
        }

        if ($cuartelNombre !== '' && !empty($validated['lote_id'])) {
            $cuartelRecord = Cuartel::where('lote_id', $validated['lote_id'])
                ->whereRaw('LOWER(TRIM(nombre)) = ?', [strtolower($cuartelNombre)])
                ->first();

            if (!$cuartelRecord) {
                $cuartelRecord = Cuartel::create([
                    'lote_id' => $validated['lote_id'],
                    'nombre' => $cuartelNombre,
                ]);
            }

            $validated['cuartel_id'] = $cuartelRecord->id;
            $validated['cuartel_manual'] = $cuartelRecord->nombre;
        } else {
            $validated['cuartel_id'] = null;
            $validated['cuartel_manual'] = null;
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

    /**
     * Anular un registro de venta (paso 1 para posterior eliminación o archivo).
     */
    public function anular(Request $request, VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isGeneral() && !$user->isIndividual()) {
            abort(403, 'No tienes permisos para anular registros de venta.');
        }

        $motivo = $request->input('motivo_anulacion', 'Anulación manual');

        $venta->update([
            'estado' => 'anulado',
            'anulado_at' => now(),
            'anulado_by' => $user->id,
            'motivo_anulacion' => $motivo,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('ventas.index')
            ->with('success', 'Registro anulado correctamente. Ahora puede ser eliminado definitivamente si lo requiere.');
    }

    /**
     * Reactivar un registro previamente anulado.
     */
    public function reactivar(VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isGeneral() && !$user->isIndividual()) {
            abort(403, 'No tienes permisos para reactivar registros.');
        }

        $venta->update([
            'estado' => 'activo',
            'anulado_at' => null,
            'anulado_by' => null,
            'motivo_anulacion' => null,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('ventas.index')
            ->with('success', 'Registro de venta reactivado con éxito.');
    }

    /**
     * Eliminar definitivamente de la base de datos (solo permitido si el registro ya está anulado).
     */
    public function destroy(VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isGeneral() && !$user->isIndividual()) {
            abort(403, 'No tienes permisos para eliminar registros.');
        }

        if (!$venta->isAnulado()) {
            return redirect()->route('ventas.index')
                ->with('error', 'El registro debe ser anulado antes de poder ser eliminado de la base de datos.');
        }

        $venta->delete();

        return redirect()->route('ventas.index')
            ->with('success', 'Registro eliminado definitivamente de la base de datos.');
    }
}
