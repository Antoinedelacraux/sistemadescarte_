<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Cuartel;
use App\Models\Envio;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\VentaDescarte;
use App\Services\ExcelExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VentaDescarteController extends Controller
{
    /**
     * Exporta el historial de ventas a Excel (.xlsx) nativo aplicando todos los filtros activos.
     */
    public function exportar(Request $request): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $user = Auth::user();

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'envio', 'creator', 'updater', 'anulador'])
            ->latest('fecha_produccion');

        // Filtro por Estado (activo, anulado, o todos)
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'activo') {
                $query->activos();
            } elseif ($request->input('estado') === 'anulado') {
                $query->anulados();
            }
        }

        // Filtro por Estado Garita
        if ($request->filled('estado_garita')) {
            $query->where('estado_garita', $request->input('estado_garita'));
        }
        if ($request->filled('estado_registro')) {
            $query->where('estado_registro', $request->input('estado_registro'));
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

        $headers = [
            'FUNDO',
            'CÓDIGO DEL ENVÍO',
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
            0 => 'string',   // FUNDO: AGRITAC, PROCOM, EL NEGRO (sin paréntesis)
            1 => 'string',   // CÓDIGO DEL ENVÍO
            2 => 'date',     // FECHA
            3 => 'string',   // CLIENTE
            4 => 'string',   // RUC
            5 => 'string',   // LOTE
            6 => 'string',   // CUARTEL
            7 => 'string',   // MOTIVO
            8 => 'string',   // TIPO DESCARTE
            9 => 'decimal',  // KILOS
            10 => 'decimal', // PRECIO
            11 => 'decimal', // TOTAL
            12 => 'integer', // JABAS
            13 => 'decimal', // PESO JABA
            14 => 'string',  // PLACA
            15 => 'string',  // CONDUCTOR
            16 => 'string',  // BREVETE
            17 => 'string',  // VIAJE
            18 => 'string',  // ESTADO
            19 => 'string',  // OBSERVACIONES
            20 => 'string',  // REGISTRADO POR
            21 => 'date',    // FECHA REGISTRO
            22 => 'date',    // FECHA Y HORA VALIDACION GARITA
        ];

        $dataRows = [];
        // Streaming en lotes pequeños (lazy chunks) para mantener la memoria RAM bajo control (< 25MB)
        $query->lazy(500)->each(function ($v) use (&$dataRows) {
            $fundoNombre = $v->fundo?->nombre_corto ?? $v->fundo?->name ?? 'N/A';
            $dataRows[] = [
                $fundoNombre,
                $v->envio?->codigo ?? '',
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
                $v->salida_garita_at ? $v->salida_garita_at->format('d/m/Y H:i') : '',
            ];
        });

        $filename = 'ventas_historial_' . date('Y-m-d_His') . '.xlsx';
        $xlsxBinary = ExcelExporter::generate($headers, $dataRows, 'Historial Ventas', [
            'columnTypes' => $columnTypes,
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

        $query = VentaDescarte::with(['fundo', 'lote', 'cuartel', 'envio', 'creator', 'updater', 'anulador'])
            ->latest('fecha_produccion');

        // Filtro por Estado (activo, anulado, o todos)
        if ($request->filled('estado')) {
            if ($request->input('estado') === 'activo') {
                $query->activos();
            } elseif ($request->input('estado') === 'anulado') {
                $query->anulados();
            }
        }

        // Filtro por Estado Garita
        if ($request->filled('estado_garita')) {
            $query->where('estado_garita', $request->input('estado_garita'));
        }
        if ($request->filled('estado_registro')) {
            $query->where('estado_registro', $request->input('estado_registro'));
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
        $fundos = $user->hasAccessToAllFundos()
            ? Fundo::where('is_active', true)->get()
            : $user->fundos()->where('is_active', true)->get();

        return view('ventas.index', compact('ventas', 'fundos'));
    }

    /**
     * Visualiza el contenido detallado de un registro de venta.
     */
    public function show(VentaDescarte $venta): View
    {
        $venta->load(['fundo', 'lote', 'cuartel', 'envio', 'creator', 'updater', 'anulador']);
        return view('ventas.show', compact('venta'));
    }

    /**
     * Formulario para registrar nueva venta de descarte.
     */
    public function create(): View
    {
        $user = Auth::user();

        $fundos = $user->hasAccessToAllFundos()
            ? Fundo::where('is_active', true)->with('lotes.cuarteles')->get()
            : $user->fundos()->where('is_active', true)->with('lotes.cuarteles')->get();

        // Lotes disponibles para el primer fundo (reutiliza la relación en memoria sin consultas redundantes)
        $lotes = $fundos->first()?->lotes ?? collect();
        $clientes = Cliente::orderBy('nombre')->get();

        return view('ventas.create', compact('fundos', 'lotes', 'clientes'));
    }

    /**
     * Almacena una nueva venta de descarte validando reglas de negocio.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $this->normalizarCuartelInput($request);
        $this->normalizarDatosTrazabilidad($request);

        $fundosAccesibles = $user->hasAccessToAllFundos()
            ? Fundo::pluck('id')->toArray()
            : $user->fundos()->pluck('fundos.id')->toArray();

        $validated = $request->validate(
            $this->getVentaValidationRules($request, $fundosAccesibles, true),
            $this->getVentaValidationMessages()
        );
        $modoGuardado = $validated['modo_guardado'] ?? 'borrador';
        unset($validated['modo_guardado']);

        $validated['valor_venta'] = round((float) $validated['precio'] * (float) $validated['kilogramos'], 2);
        $cuartelNombre = trim((string) ($request->input('cuartel_manual') ?? ''));
        $validated['cuartel_manual'] = $cuartelNombre !== '' ? $cuartelNombre : null;
        $validated['cuartel_id'] = null;
        $validated['created_by'] = $user->id;

        // Idempotencia: Si viene con UUID desde la cola offline del cliente, usamos firstOrCreate con captura de colisiones
        try {
            $venta = DB::transaction(function () use (&$validated, $modoGuardado) {
                $validated['envio_id'] = $this->sincronizarEnvio($validated);
                $validated['estado_registro'] = $modoGuardado === 'registro' ? 'oficial' : 'borrador';
                if (Schema::hasColumn('ventas_descarte', 'borrador_sin_vencimiento')) {
                    $validated['borrador_sin_vencimiento'] = $validated['estado_registro'] === 'borrador';
                }
                $this->syncClienteCatalogo($validated['cliente'] ?? null, $validated['ruc'] ?? null);

                return !empty($validated['id'])
                    ? VentaDescarte::withoutGlobalScopes()->firstOrCreate(['id' => $validated['id']], $validated)
                    : VentaDescarte::create($validated);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Manejo tolerante a fallos si dos llamadas concurrentes intentaron insertar el mismo UUID
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                $venta = !empty($validated['id'])
                    ? VentaDescarte::withoutGlobalScopes()->find($validated['id'])
                    : null;
                if (!$venta) {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $modoGuardado === 'registro'
                    ? 'Registro completo guardado y sincronizado exitosamente.'
                    : 'Borrador guardado y sincronizado exitosamente.',
                'data' => [
                    'id' => $venta->id,
                    'valor_venta' => $venta->valor_venta,
                    'cliente' => $venta->cliente,
                    'ruc' => $venta->ruc,
                ]
            ], 201);
        }

        return redirect()->route('ventas.index')
            ->with('success', $modoGuardado === 'registro'
                ? 'Registro completo guardado exitosamente.'
                : 'Borrador guardado exitosamente.');
    }

    /**
     * Formulario de edición.
     */
    public function edit(VentaDescarte $venta): View|RedirectResponse
    {
        $user = Auth::user();

        if (!$venta->puedeSerEditadoPor($user)) {
            $mensaje = $venta->isCerradoParaEdicion()
                ? 'Este registro tiene más de 24 horas y se encuentra cerrado para edición. Solicite los ajustes a un Administrador.'
                : 'No tienes permisos para editar este registro.';

            return redirect()->route('ventas.index')->with('error', $mensaje);
        }

        $fundos = $user->hasAccessToAllFundos()
            ? Fundo::where('is_active', true)->with('lotes.cuarteles')->get()
            : $user->fundos()->where('is_active', true)->with('lotes.cuarteles')->get();

        $lotes = Lote::where('fundo_id', $venta->fundo_id)->with('cuarteles')->get();
        $clientes = Cliente::orderBy('nombre')->get();

        return view('ventas.edit', compact('venta', 'fundos', 'lotes', 'clientes'));
    }

    /**
     * Actualiza una venta registrando quién la modificó.
     */
    public function update(Request $request, VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();

        if (!$venta->puedeSerEditadoPor($user)) {
            $mensaje = $venta->isCerradoParaEdicion()
                ? 'Este registro superó el límite de 24 horas y se encuentra cerrado para edición.'
                : 'No tienes permisos para actualizar este registro.';

            return redirect()->route('ventas.index')->with('error', $mensaje);
        }

        $this->normalizarCuartelInput($request);
        $this->normalizarDatosTrazabilidad($request);

        $fundosAccesibles = $user->hasAccessToAllFundos()
            ? Fundo::pluck('id')->toArray()
            : $user->fundos()->pluck('fundos.id')->toArray();

        $validated = $request->validate(
            $this->getVentaValidationRules($request, $fundosAccesibles, false),
            $this->getVentaValidationMessages()
        );
        $modoGuardado = $validated['modo_guardado'] ?? ($venta->isRegistroOficial() ? 'registro' : 'borrador');
        unset($validated['modo_guardado']);

        $validated['valor_venta'] = round((float) $validated['precio'] * (float) $validated['kilogramos'], 2);
        $cuartelNombre = trim((string) ($request->input('cuartel_manual') ?? ''));
        $validated['cuartel_manual'] = $cuartelNombre !== '' ? $cuartelNombre : null;
        $validated['cuartel_id'] = null;
        $validated['updated_by'] = $user->id;

        DB::transaction(function () use (&$validated, $venta, $modoGuardado) {
            $validated['envio_id'] = $this->sincronizarEnvio($validated, $venta->envio_id, $venta);
            $validated['estado_registro'] = $venta->isRegistroOficial() || $modoGuardado === 'registro'
                ? 'oficial'
                : 'borrador';
            if (Schema::hasColumn('ventas_descarte', 'borrador_sin_vencimiento')) {
                $validated['borrador_sin_vencimiento'] = $validated['estado_registro'] === 'borrador';
            }
            $this->syncClienteCatalogo($validated['cliente'] ?? null, $validated['ruc'] ?? null);
            $venta->update($validated);
        });

        return redirect()->route('ventas.index')
            ->with('success', $modoGuardado === 'registro'
                ? 'Registro completo actualizado correctamente.'
                : 'Borrador actualizado correctamente.');
    }

    /**
     * Normaliza la entrada de cuartel para asegurar compatibilidad si proviene de integraciones previas.
     */
    private function normalizarCuartelInput(Request $request): void
    {
        if (!$request->filled('cuartel_manual') && $request->filled('cuartel_id')) {
            $cuartelModel = Cuartel::find($request->input('cuartel_id'));
            if ($cuartelModel) {
                $request->merge(['cuartel_manual' => $cuartelModel->nombre]);
            }
        }
    }

    /** Normaliza texto operativo sin alterar campos vacíos de un borrador. */
    private function normalizarDatosTrazabilidad(Request $request): void
    {
        $normalizados = [];
        foreach (['cliente', 'conductor'] as $campo) {
            if ($request->filled($campo)) {
                $normalizados[$campo] = preg_replace('/\s+/u', ' ', trim((string) $request->input($campo)));
            }
        }
        if ($request->filled('placa')) {
            $normalizados['placa'] = mb_strtoupper(trim((string) $request->input('placa')), 'UTF-8');
        }
        foreach (['ruc', 'viaje', 'brevete', 'observacion'] as $campo) {
            if ($request->filled($campo)) {
                $normalizados[$campo] = trim((string) $request->input($campo));
            }
        }
        if ($normalizados) {
            $request->merge($normalizados);
        }
    }

    /** Crea o reutiliza un envío explícito para que los lotes compartan una relación real. */
    private function sincronizarEnvio(array $datos, ?string $envioActual = null, ?VentaDescarte $ventaActual = null): ?string
    {
        $numero = $datos['viaje'] ?? null;
        if ($numero === null || $numero === '') {
            return null;
        }

        $envio = Envio::withoutGlobalScopes()
            ->where('fundo_id', $datos['fundo_id'])
            ->whereDate('fecha_produccion', $datos['fecha_produccion'])
            ->where('numero_viaje', (int) $numero)
            ->first();

        if ($envio && $envioActual && $envio->id !== $envioActual) {
            throw ValidationException::withMessages(['viaje' => 'El número de viaje ya está asociado a otro envío para esa fecha y fundo.']);
        }

        if (!$envio) {
            $fundo = Fundo::withoutGlobalScopes()->findOrFail($datos['fundo_id']);
            $codigo = sprintf('ENV-%s-%s-%03d', date('Ymd', strtotime((string) $datos['fecha_produccion'])), $fundo->code, (int) $numero);
            $envio = Envio::withoutGlobalScopes()->create([
                'codigo' => $codigo,
                'fundo_id' => $datos['fundo_id'],
                'fecha_produccion' => $datos['fecha_produccion'],
                'numero_viaje' => (int) $numero,
                'cliente' => $datos['cliente'] ?? null,
                'placa' => $datos['placa'] ?? null,
                'conductor' => $datos['conductor'] ?? null,
            ]);
            return $envio->id;
        }

        foreach (['cliente', 'placa', 'conductor'] as $campo) {
            $nuevo = trim((string) ($datos[$campo] ?? ''));
            $actual = trim((string) ($envio->{$campo} ?? ''));
            if ($nuevo !== '' && $actual !== '' && mb_strtolower($nuevo, 'UTF-8') !== mb_strtolower($actual, 'UTF-8')) {
                // Compatibilidad con envíos históricos que ya tenían lotes inconsistentes:
                // si el usuario conserva el valor original de este registro, no se bloquea
                // la edición de otros campos. Una nueva discrepancia sí continúa bloqueada.
                $original = trim((string) ($ventaActual?->{$campo} ?? ''));
                if ($ventaActual && mb_strtolower($nuevo, 'UTF-8') === mb_strtolower($original, 'UTF-8')) {
                    continue;
                }
                throw ValidationException::withMessages([$campo => "No se puede guardar: el Viaje N° {$envio->numero_viaje} ({$envio->codigo}) ya tiene otro {$campo}. Un mismo viaje conserva el mismo cliente, conductor y vehículo para todos sus lotes. Conserva los datos del viaje o usa otro número de viaje."]);
            }
            if ($nuevo !== '' && $actual === '') {
                $envio->{$campo} = $nuevo;
            }
        }

        foreach (['ruc' => 'RUC', 'brevete' => 'número de brevete'] as $campo => $etiqueta) {
            $nuevo = trim((string) ($datos[$campo] ?? ''));
            if ($nuevo === '') {
                continue;
            }

            $referencia = VentaDescarte::withoutGlobalScopes()
                ->where('envio_id', $envio->id)
                ->when($ventaActual, fn ($query) => $query->where('id', '!=', $ventaActual->id))
                ->whereNotNull($campo)
                ->where($campo, '!=', '')
                ->orderBy('created_at')
                ->value($campo);

            if ($referencia !== null && mb_strtolower($nuevo, 'UTF-8') !== mb_strtolower(trim((string) $referencia), 'UTF-8')) {
                throw ValidationException::withMessages([$campo => "No se puede guardar: el Viaje N° {$envio->numero_viaje} ({$envio->codigo}) ya tiene otro {$etiqueta}. Un mismo viaje conserva el mismo cliente, conductor y vehículo para todos sus lotes. Conserva los datos del viaje o usa otro número de viaje."]);
            }
        }
        $envio->save();

        return $envio->id;
    }

    /**
     * Registra o actualiza el cliente en el catálogo para el autocompletado del sistema.
     */
    private function syncClienteCatalogo(?string $cliente, ?string $ruc): void
    {
        if (!empty($cliente)) {
            $clienteNombre = trim($cliente);
            if ($clienteNombre !== '') {
                $clienteRuc = !empty($ruc) ? trim($ruc) : null;
                $clienteExistente = Cliente::whereRaw('LOWER(nombre) = ?', [mb_strtolower($clienteNombre, 'UTF-8')])->first();
                if ($clienteExistente) {
                    if ($clienteRuc && $clienteExistente->ruc !== $clienteRuc) {
                        $clienteExistente->update(['ruc' => $clienteRuc]);
                    }
                    return;
                }
                Cliente::create(['nombre' => $clienteNombre, 'ruc' => $clienteRuc]);
            }
        }
    }

    /**
     * Reglas de validación centralizadas para registros y modificaciones de ventas.
     */
    private function getVentaValidationRules(Request $request, array $fundosAccesibles, bool $isCreate = false): array
    {
        $esRegistroCompleto = $request->input('modo_guardado', 'borrador') === 'registro';
        $rules = [
            'modo_guardado' => ['nullable', Rule::in(['borrador', 'registro'])],
            'fundo_id' => ['required', 'integer', Rule::in($fundosAccesibles)],
            'fecha_produccion' => ['required', 'date', 'before_or_equal:today'],
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
            'cuartel_id' => ['nullable'],
            'cuartel_manual' => [
                Rule::requiredIf(fn () => $request->input('motivo') === 'Cosecha Nacional'),
                'nullable',
                'string',
                'max:100',
            ],
            'precio' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'kilogramos' => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'jabas' => [Rule::requiredIf($esRegistroCompleto), 'nullable', 'integer', $esRegistroCompleto ? 'min:1' : 'min:0'],
            'peso_jaba' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'brevete' => ['nullable', 'string', 'max:50'],
            'ruc' => ['nullable', 'digits:11'],
            'cliente' => [Rule::requiredIf($esRegistroCompleto), 'nullable', 'string', 'max:150', function ($attribute, $value, $fail) {
                if (trim((string) $value) !== '' && !preg_match('/\p{L}/u', (string) $value)) {
                    $fail('El cliente debe contener al menos una letra.');
                }
            }],
            'placa' => [Rule::requiredIf($esRegistroCompleto), 'nullable', 'string', 'max:20'],
            'conductor' => [Rule::requiredIf($esRegistroCompleto), 'nullable', 'string', 'max:150', function ($attribute, $value, $fail) {
                if (trim((string) $value) !== '' && !preg_match("/^\\p{L}+(?:[ .'-]+\\p{L}+)+$/u", (string) $value)) {
                    $fail('El conductor debe incluir al menos nombre y apellido, usando solo letras y separadores válidos.');
                }
            }],
            'viaje' => [Rule::requiredIf($esRegistroCompleto), 'nullable', 'integer', 'min:1'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ];

        if ($isCreate) {
            $rules['id'] = ['nullable', 'string', 'max:50'];
        }

        return $rules;
    }

    /**
     * Mensajes descriptivos centralizados para validación de ventas.
     */
    private function getVentaValidationMessages(): array
    {
        return [
            'fundo_id.in' => 'No tienes permisos para registrar en el fundo seleccionado.',
            'lote_id.required' => 'El lote es obligatorio.',
            'lote_id.exists' => 'El lote seleccionado no pertenece al fundo indicado.',
            'motivo.required' => 'El motivo de descarte es obligatorio.',
            'tipo_descarte.required' => 'El tipo de descarte es obligatorio.',
            'cuartel_manual.required' => 'El cuartel es obligatorio cuando el motivo es Cosecha Nacional.',
            'precio.required' => 'El precio por kilogramo es obligatorio.',
            'kilogramos.required' => 'Los kilogramos totales son obligatorios.',
            'cliente.required' => 'El cliente es obligatorio para guardar el registro completo.',
            'jabas.required' => 'La cantidad de jabas es obligatoria para guardar el registro completo.',
            'placa.required' => 'La placa es obligatoria para guardar el registro completo.',
            'conductor.required' => 'El conductor es obligatorio para guardar el registro completo.',
            'viaje.required' => 'El número de viaje es obligatorio para guardar el registro completo.',
            'fecha_produccion.before_or_equal' => 'La fecha de producción no puede ser futura.',
            'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
            'viaje.integer' => 'El número de viaje debe ser un número entero positivo.',
            'viaje.min' => 'El número de viaje debe ser mayor que cero.',
        ];
    }


    /**
     * Endpoint API para obtener lotes y cuarteles al cambiar de fundo.
     */
    public function getLotesPorFundo(Request $request)
    {
        $fundoId = $request->query('fundo_id');
        $user = Auth::user();

        // Validar acceso al fundo
        if (!$user->hasAccessToAllFundos()) {
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

        if (!$venta->puedeSerAnuladoPor($user)) {
            $mensaje = $venta->isCerradoParaEdicion()
                ? 'Este registro superó el límite de 24 horas y se encuentra cerrado para anulación. Solo un Administrador puede gestionarlo.'
                : 'No tienes permisos para anular registros de venta.';

            return redirect()->route('ventas.index')->with('error', $mensaje);
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

        if (!$user->isAdmin()) {
            if (!$user->canAccessModule('registro') || $venta->isCerradoParaEdicion()) {
                return redirect()->route('ventas.index')
                    ->with('error', 'Solo un Administrador puede reactivar registros con más de 24 horas de antigüedad.');
            }
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

        if (!$user->isAdmin()) {
            if (!$user->canAccessModule('registro') || $venta->isCerradoParaEdicion()) {
                return redirect()->route('ventas.index')
                    ->with('error', 'Solo un Administrador puede eliminar definitivamente registros con más de 24 horas de antigüedad.');
            }
        }

        if (!$venta->isAnulado()) {
            return redirect()->route('ventas.index')
                ->with('error', 'El registro debe ser anulado antes de poder ser eliminado de la base de datos.');
        }

        $venta->delete();

        return redirect()->route('ventas.index')
            ->with('success', 'Registro eliminado definitivamente de la base de datos.');
    }

    /**
     * Enviar registro(s) a Garita para autorización de salida.
     * Si se pasa 'enviar_viaje=1', envía todos los registros que compartan viaje, fecha y placa.
     */
    public function enviarAGarita(Request $request, VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();

        if (!$venta->puedeEnviarAGarita($user)) {
            return redirect()->back()
                ->with('error', 'El registro no cumple con las condiciones para ser enviado a Garita.');
        }

        $enviarViajeCompleto = $request->boolean('enviar_viaje', true);

        if ($enviarViajeCompleto && !empty($venta->viaje)) {
            $resultado = DB::transaction(function () use ($venta, $user) {
                $registrosQuery = VentaDescarte::activos()->where('estado_garita', 'borrador');
                if ($venta->envio_id) {
                    $registrosQuery->where('envio_id', $venta->envio_id);
                } else {
                    $registrosQuery->where('fundo_id', $venta->fundo_id)
                        ->whereDate('fecha_produccion', $venta->fecha_produccion)
                        ->where('viaje', $venta->viaje)
                        ->where('placa', $venta->placa);
                }
                $registros = $registrosQuery->lockForUpdate()->get();
                if (!$registros->contains('id', $venta->id)) {
                    return 'El registro ya no está disponible para enviar. Actualiza la página.';
                }
                $faltantes = $registros->flatMap(fn ($registro) => $registro->camposFaltantesGarita())->unique()->values();
                if ($faltantes->isNotEmpty()) {
                    return 'No se puede enviar a Garita. Completa en todos los registros del viaje: '.$faltantes->implode(', ').'.';
                }
                VentaDescarte::whereIn('id', $registros->modelKeys())->update([
                    'estado_garita' => 'pendiente', 'enviado_garita_at' => now(),
                    'enviado_garita_by' => $user->id, 'updated_at' => now(),
                ]);
                if ($venta->envio_id) {
                    Envio::withoutGlobalScopes()->whereKey($venta->envio_id)->update([
                        'estado_garita' => 'pendiente', 'enviado_garita_at' => now(), 'enviado_garita_by' => $user->id,
                    ]);
                }
                return null;
            });
            if ($resultado !== null) {
                return redirect()->back()->with('error', $resultado);
            }

            return redirect()->back()
                ->with('success', "Viaje N° {$venta->viaje} enviado correctamente a Garita para control de salida.");
        }

        $faltantes = $venta->camposFaltantesGarita();
        if ($faltantes) {
            return redirect()->back()->with('error', 'No se puede enviar a Garita. Completa: '.implode(', ', $faltantes).'.');
        }

        $venta->update([
            'estado_garita' => 'pendiente',
            'enviado_garita_at' => now(),
            'enviado_garita_by' => $user->id,
        ]);

        return redirect()->back()
            ->with('success', 'Registro enviado correctamente a Garita.');
    }

    /**
     * Cancelar envío a Garita (retorna el registro a borrador para permitir edición antes de salida).
     */
    public function cancelarEnvioGarita(Request $request, VentaDescarte $venta): RedirectResponse
    {
        $user = Auth::user();

        if (!$venta->puedeCancelarEnvioGarita($user)) {
            return redirect()->back()
                ->with('error', 'No es posible cancelar el envío a Garita (el camión ya pudo haber sido validado o no tienes permisos).');
        }

        $cancelarViajeCompleto = $request->boolean('cancelar_viaje', true);

        if ($cancelarViajeCompleto && !empty($venta->viaje)) {
            $cancelQuery = VentaDescarte::activos()->where('estado_garita', 'pendiente');
            if ($venta->envio_id) {
                $cancelQuery->where('envio_id', $venta->envio_id);
            } else {
                $cancelQuery->where('fundo_id', $venta->fundo_id)
                    ->whereDate('fecha_produccion', $venta->fecha_produccion)
                    ->where('viaje', $venta->viaje)
                    ->where('placa', $venta->placa);
            }
            $cancelQuery
                ->update([
                    'estado_garita' => 'borrador',
                    'enviado_garita_at' => null,
                    'enviado_garita_by' => null,
                    'updated_at' => now(),
                ]);
            if ($venta->envio_id) {
                Envio::withoutGlobalScopes()->whereKey($venta->envio_id)->update([
                    'estado_garita' => 'borrador', 'enviado_garita_at' => null, 'enviado_garita_by' => null,
                ]);
            }

            return redirect()->back()
                ->with('success', "Envío a Garita del Viaje N° {$venta->viaje} cancelado. Ahora puedes editar los registros.");
        }

        $venta->update([
            'estado_garita' => 'borrador',
            'enviado_garita_at' => null,
            'enviado_garita_by' => null,
        ]);

        return redirect()->back()
            ->with('success', 'Envío a Garita cancelado con éxito.');
    }
}
