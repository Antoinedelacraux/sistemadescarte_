<?php

namespace App\Http\Controllers;

use App\Models\Fundo;
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
     * Listado de fundos para el Administrador.
     */
    public function fundos(): View
    {
        $fundos = Fundo::withCount(['lotes', 'ventas', 'users'])->get();

        return view('admin.fundos.index', compact('fundos'));
    }

    /**
     * Almacenar un nuevo fundo.
     */
    public function storeFundo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:fundos,name'],
            'code' => ['required', 'string', 'max:20', 'unique:fundos,code'],
        ], [
            'name.required' => 'El nombre del fundo es obligatorio.',
            'name.unique' => 'Ya existe un fundo con este nombre.',
            'code.required' => 'El código de fundo es obligatorio.',
            'code.unique' => 'Ya existe un fundo con este código.',
        ]);

        $validated['is_active'] = true;
        Fundo::create($validated);

        return redirect()->route('admin.fundos')->with('success', 'Fundo creado exitosamente.');
    }

    /**
     * Listado de usuarios del sistema.
     */
    public function usuarios(): View
    {
        $usuarios = User::with(['role', 'fundos'])->orderBy('name')->paginate(15);
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

        $estado = $user->is_active ? 'activado' : 'desactivado';
        return redirect()->route('admin.usuarios')->with('success', "Usuario {$estado} correctamente.");
    }
}
