@extends('layouts.app')

@section('title', 'Administración de Usuarios')
@section('page-title', 'Administración de Usuarios y Roles')

@section('styles')
<style>
    .fundos-checklist {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--clr-surface-50);
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
    }

    .user-submit-btn {
        height: 44px;
        padding: 0 1.5rem;
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .user-submit-btn {
            width: 100%;
            height: 48px;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div>
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: clamp(1.2rem, 5vw, 1.5rem); font-weight: 700; color: var(--txt-primary);">Usuarios del Sistema</h1>
        <p style="font-size: clamp(0.75rem, 2.8vw, 0.875rem); color: var(--txt-muted);">Creación de cuentas, asignación de roles (Administrador, General, Individual, Analista) y control de fundos</p>
    </div>

    {{-- Formulario para registrar usuario --}}
    <details class="card" style="margin-bottom: 1.5rem;" {{ $errors->any() ? 'open' : '' }}>
        <summary style="font-size: var(--text-sm); font-weight: 700; color: var(--txt-primary); cursor: pointer; padding: 0.25rem 0;">
            <span>➕</span> Crear Nuevo Usuario
        </summary>

        <form action="{{ route('admin.usuarios.store') }}" method="POST" style="margin-top: 1.25rem; border-top: 1px solid var(--brd-base); padding-top: 1.25rem;">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="name" class="form-label text-xs">Nombre Completo <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Ej. Pedro Morales" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label text-xs">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="pedro@fundo.test" required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label text-xs">Contraseña Inicial <span class="required">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="role_id" class="form-label text-xs">Rol de Acceso <span class="required">*</span></label>
                    <select name="role_id" id="role_id" class="form-control" required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Asignación de Fundos --}}
            <div style="margin-top: 0.5rem;">
                <label class="form-label text-xs" style="font-weight: 700;">Fundos asignados a este usuario:</label>
                <div class="fundos-checklist">
                    @foreach($fundos as $fundo)
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-sm); cursor: pointer;">
                            <input type="checkbox" name="fundos[]" value="{{ $fundo->id }}" style="width: 16px; height: 16px; accent-color: var(--clr-primary-600);">
                            <span>{{ $fundo->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="margin-top: 1.25rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary user-submit-btn">
                    Crear y Asignar Usuario
                </button>
            </div>
        </form>
    </details>

    {{-- Tabla de usuarios --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="data-table" style="min-width: 620px;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Fundos Asignados</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-badge-{{ $user->role?->name ?? 'default' }}">
                                {{ $user->role?->display_name ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td>
                            @if($user->isAdmin() || $user->isAnalista())
                                <span class="text-xs" style="color: var(--clr-primary-700); font-weight: 600;">🌐 Todos los fundos</span>
                            @elseif($user->fundos->isEmpty())
                                <span class="text-xs text-muted">Ningún fundo asignado</span>
                            @else
                                @foreach($user->fundos as $f)
                                    <span class="badge-tipo" style="margin-right: 0.25rem;">{{ $f->name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($user->is_active)
                                <span class="role-badge role-badge-individual">Activo</span>
                            @else
                                <span class="role-badge role-badge-admin">Inactivo</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($user->id !== Auth::id())
                            <form action="{{ route('admin.usuarios.toggle', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-sm" style="color: {{ $user->is_active ? 'var(--clr-danger)' : 'var(--clr-success)' }};">
                                    {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                            @else
                                <span class="text-xs text-muted">Tú</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
            <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--brd-base);">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
