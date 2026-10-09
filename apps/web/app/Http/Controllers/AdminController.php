<?php

namespace App\Http\Controllers;

use App\Models\Cuartel;
use App\Models\Fundo;
use App\Models\Lote;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (!$request->user() || !$request->user()->isAdmin()) {
                    abort(403, 'Acceso restringido únicamente al rol Administrador.');
                }
                return $next($request);
            }
        ];
    }

    /**
     * Listado de fundos para el Administrador con sus lotes y cuarteles.
     */
    public function fundos(): View
    {
        $fundos = Fundo::with(['lotes.cuarteles'])
            ->withCount(['lotes', 'ventas', 'users'])
            ->latest('created_at')
            ->get();

        return view('admin.fundos.index', compact('fundos'));
    }

    /**
     * Almacenar un nuevo fundo con sus lotes iniciales.
     */
    public function storeFundo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:fundos,name'],
            'nombre_completo' => ['nullable', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:20', 'unique:fundos,code'],
            'lotes' => ['nullable', 'string'],
        ], [
            'name.required' => 'El nombre del fundo es obligatorio (ej. AGRITAC, PROCOM).',
            'name.unique' => 'Ya existe un fundo con este nombre.',
            'code.required' => 'El código de fundo es obligatorio.',
            'code.unique' => 'Ya existe un fundo con este código.',
        ]);

        $fundo = Fundo::create([
            'name' => $validated['name'],
            'nombre_completo' => $validated['nombre_completo'] ?? null,
            'code' => $validated['code'],
            'is_active' => true,
        ]);

        // Registrar los lotes ingresados de una vez
        $lotesCreados = 0;
        if ($request->filled('lotes')) {
            $rawLotes = preg_split('/[\r\n,]+/', (string) $request->input('lotes'));
            foreach ($rawLotes as $loteNombre) {
                $loteNombre = trim($loteNombre);
                if ($loteNombre !== '') {
                    $fundo->lotes()->firstOrCreate(['nombre' => $loteNombre]);
                    $lotesCreados++;
                }
            }
        }

        $mensaje = $lotesCreados > 0
            ? "Fundo '{$fundo->name}' creado exitosamente con {$lotesCreados} lote(s)."
            : "Fundo '{$fundo->name}' creado exitosamente.";

        return redirect()->route('admin.fundos')->with('success', $mensaje);
    }

    /**
     * Actualizar datos de un fundo existente y agregar nuevos lotes si se especifican.
     */
    public function updateFundo(Request $request, Fundo $fundo): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('fundos', 'name')->ignore($fundo->id)],
            'nombre_completo' => ['nullable', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:20', Rule::unique('fundos', 'code')->ignore($fundo->id)],
            'nuevos_lotes' => ['nullable', 'string'],
        ], [
            'name.required' => 'El nombre del fundo es obligatorio.',
            'name.unique' => 'Ya existe otro fundo con este nombre.',
            'code.required' => 'El código de fundo es obligatorio.',
            'code.unique' => 'Ya existe otro fundo con este código.',
        ]);

        $fundo->update([
            'name' => $validated['name'],
            'nombre_completo' => $validated['nombre_completo'] ?? $fundo->nombre_completo,
            'code' => $validated['code'],
        ]);

        if ($request->filled('nuevos_lotes')) {
            $rawLotes = preg_split('/[\r\n,]+/', (string) $request->input('nuevos_lotes'));
            foreach ($rawLotes as $loteNombre) {
                $loteNombre = trim($loteNombre);
                if ($loteNombre !== '') {
                    $fundo->lotes()->firstOrCreate(['nombre' => $loteNombre]);
                }
            }
        }

        return redirect()->route('admin.fundos')->with('success', 'Fundo actualizado correctamente.');
    }

    /**
     * Agregar lote(s) a un fundo específico.
     */
    public function storeLote(Request $request, Fundo $fundo): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string'],
        ], [
            'nombre.required' => 'Ingresa el nombre del lote.',
        ]);

        $rawLotes = preg_split('/[\r\n,]+/', (string) $request->input('nombre'));
        $creados = 0;
        foreach ($rawLotes as $lName) {
            $lName = trim($lName);
            if ($lName !== '') {
                $fundo->lotes()->firstOrCreate(['nombre' => $lName]);
                $creados++;
            }
        }

        return redirect()->route('admin.fundos')
            ->with('success', "Se agregaron {$creados} lote(s) al fundo '{$fundo->name}'.");
    }

    /**
     * Eliminar un lote (solo si no tiene ventas asociadas).
     */
    public function destroyLote(Lote $lote): RedirectResponse
    {
        if ($lote->ventas()->exists()) {
            return redirect()->route('admin.fundos')
                ->with('error', "No se puede eliminar el lote '{$lote->nombre}' porque tiene registros de ventas asociados.");
        }

        $fundoNombre = $lote->fundo?->name ?? 'Fundo';
        $nombreLote = $lote->nombre;
        $lote->cuarteles()->delete();
        $lote->delete();

        return redirect()->route('admin.fundos')
            ->with('success', "Lote '{$nombreLote}' eliminado de {$fundoNombre}.");
    }

    /**
     * Agregar cuartel(es) a un lote específico.
     */
    public function storeCuartel(Request $request, Lote $lote): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string'],
        ], [
            'nombre.required' => 'Ingresa el nombre del cuartel.',
        ]);

        $rawCuarteles = preg_split('/[\r\n,]+/', (string) $request->input('nombre'));
        $creados = 0;
        foreach ($rawCuarteles as $cName) {
            $cName = trim($cName);
            if ($cName !== '') {
                $lote->cuarteles()->firstOrCreate(['nombre' => $cName]);
                $creados++;
            }
        }

        return redirect()->route('admin.fundos')
            ->with('success', "Se agregaron {$creados} cuartel(es) al lote '{$lote->nombre}'.");
    }

    /**
     * Eliminar un cuartel.
     */
    public function destroyCuartel(Cuartel $cuartel): RedirectResponse
    {
        $nombre = $cuartel->nombre;
        $cuartel->delete();

        return redirect()->route('admin.fundos')
            ->with('success', "Cuartel '{$nombre}' eliminado.");
    }

    /**
     * Cambiar estado activo/inactivo (anular o reactivar) de un fundo.
     */
    public function toggleFundo(Fundo $fundo): RedirectResponse
    {
        $fundo->update(['is_active' => !$fundo->is_active]);

        $estado = $fundo->is_active ? 'reactivado' : 'anulado/inactivado';
        return redirect()->route('admin.fundos')->with('success', "Fundo {$estado} correctamente.");
    }

    /**
     * Eliminar definitivamente un fundo (solo permitido si está previamente anulado/inactivo).
     */
    public function destroyFundo(Fundo $fundo): RedirectResponse
    {
        if ($fundo->is_active) {
            return redirect()->route('admin.fundos')
                ->with('error', 'El fundo debe ser anulado/inactivado antes de poder ser eliminado definitivamente.');
        }

        if ($fundo->ventas()->count() > 0) {
            return redirect()->route('admin.fundos')
                ->with('error', 'No se puede eliminar un fundo que tiene ventas de descarte registradas por trazabilidad histórica.');
        }

        $fundo->lotes()->delete();
        $fundo->users()->detach();
        $fundo->delete();

        return redirect()->route('admin.fundos')->with('success', 'Fundo eliminado definitivamente de la base de datos.');
    }

    /**
     * Listado de usuarios del sistema.
     */
    public function usuarios(): View
    {
        $usuarios = User::with(['role', 'fundos'])->orderBy('name')->paginate(15)->withQueryString();
        $roles = Role::all();
        $fundos = Fundo::where('is_active', true)->get();

        return view('admin.usuarios.index', compact('usuarios', 'roles', 'fundos'));
    }

    /**
     * Crear un nuevo usuario asignándole rol y fundos.
     */
    public function storeUsuario(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'fundos' => ['nullable', 'array'],
            'fundos.*' => ['exists:fundos,id'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya se encuentra registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'role_id.required' => 'Debes asignar un rol al usuario.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'is_active' => true,
        ]);

        if (!empty($validated['fundos'])) {
            $user->fundos()->attach($validated['fundos']);
        }

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo de un usuario.
     */
    public function toggleUsuario(User $user): RedirectResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        $estado = $user->is_active ? 'activado' : 'anulado/desactivado';
        return redirect()->route('admin.usuarios')->with('success', "Usuario {$estado} correctamente.");
    }

    /**
     * Actualizar datos de un usuario.
     */
    public function updateUsuario(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'fundos' => ['nullable', 'array'],
            'fundos.*' => ['exists:fundos,id'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya pertenece a otro usuario.',
            'role_id.required' => 'Debes asignar un rol.',
            'password.min' => 'La nueva contraseña debe tener al menos 6 caracteres.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->fundos()->sync($validated['fundos'] ?? []);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar definitivamente un usuario (solo permitido si está previamente anulado/desactivado).
     */
    public function destroyUsuario(User $user): RedirectResponse
    {
        if ($user->is_active) {
            return redirect()->route('admin.usuarios')
                ->with('error', 'El usuario debe ser anulado/desactivado antes de poder ser eliminado definitivamente.');
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('admin.usuarios')
                ->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->fundos()->detach();
        $user->delete();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado definitivamente de la base de datos.');
    }
}
