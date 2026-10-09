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

    /* Acciones en fila de tabla - Iconos Minimalistas */
    .row-actions-btn-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all var(--transition-fast);
        padding: 0;
        background: transparent;
        color: var(--txt-secondary);
        flex-shrink: 0;
    }

    .btn-action-icon:hover {
        transform: translateY(-1px);
    }

    .btn-action-icon svg {
        width: 15px;
        height: 15px;
        stroke-width: 2.2;
    }

    .btn-action-edit {
        background: #f8fafc;
        color: #334155;
        border-color: #cbd5e1;
    }
    .btn-action-edit:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
    }

    .btn-action-anular {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
    }
    .btn-action-anular:hover {
        background: #ffedd5;
        color: #9a3412;
        border-color: #fdba74;
        box-shadow: 0 1px 3px rgba(194, 65, 12, 0.15);
    }

    .btn-action-reactivar {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .btn-action-reactivar:hover {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
        box-shadow: 0 1px 3px rgba(29, 78, 216, 0.15);
    }

    .btn-action-delete {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .btn-action-delete:hover {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #f87171;
        box-shadow: 0 1px 3px rgba(220, 38, 38, 0.15);
    }

    /* Modal de Edición */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 31, 20, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop.is-open {
        display: flex;
    }
    .modal-sheet {
        background: #ffffff;
        border-radius: var(--radius-xl);
        max-width: 560px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
        border: 1px solid var(--brd-base);
        overflow: hidden;
    }
    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--brd-base);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--clr-surface-50);
    }
    .modal-body {
        padding: 1.25rem;
        overflow-y: auto;
    }
    .modal-footer {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    .modal-close-btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
        background: white;
        color: var(--txt-muted);
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        line-height: 1;
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
            Crear Nuevo Usuario
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
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $user)
                    <tr style="{{ !$user->is_active ? 'opacity: 0.65; background: #fafafa;' : '' }}">
                        <td><strong>{{ $user->name }}</strong></td>
                        <td class="text-muted">{{ $user->email }}</td>
                        <td>
                            <span class="role-badge role-badge-{{ $user->role?->name ?? 'default' }}">
                                {{ $user->role?->display_name ?? 'Sin Rol' }}
                            </span>
                        </td>
                        <td>
                            @if($user->isAdmin() || $user->isAnalista())
                                <span class="text-xs" style="color: var(--clr-primary-700); font-weight: 600;">Todos los fundos</span>
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
                            <div class="row-actions-btn-group">
                                {{-- Editar (Modal) --}}
                                <button type="button" 
                                        class="btn-action-icon btn-action-edit" 
                                        title="Editar Usuario" 
                                        aria-label="Editar {{ $user->name }}"
                                        onclick='abrirModalEditarUsuario({{ $user->id }}, @json($user->name), @json($user->email), {{ $user->role_id }}, @json($user->fundos->pluck("id")) )'>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>

                                @if($user->id !== Auth::id())
                                    @if($user->is_active)
                                        {{-- Anular / Desactivar (Paso 1) --}}
                                        <form action="{{ route('admin.usuarios.toggle', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Deseas ANULAR / DESACTIVAR el acceso de este usuario?');">
                                            @csrf
                                            <button type="submit" class="btn-action-icon btn-action-anular" title="Desactivar / Anular Usuario" aria-label="Desactivar {{ $user->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Reactivar si está inactivo --}}
                                        <form action="{{ route('admin.usuarios.toggle', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Deseas REACTIVAR el acceso de este usuario?');">
                                            @csrf
                                            <button type="submit" class="btn-action-icon btn-action-reactivar" title="Reactivar Usuario" aria-label="Reactivar {{ $user->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                            </button>
                                        </form>

                                        {{-- Eliminar definitivamente (Paso 2: Solo si está previamente inactivo/anulado) --}}
                                        <form action="{{ route('admin.usuarios.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('¡ADVERTENCIA! Este usuario ya está desactivado.\n¿Deseas ELIMINARLO DEFINITIVAMENTE de la base de datos?\nEsta acción es irreversible.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-icon btn-action-delete" title="Eliminar definitivamente de la base de datos" aria-label="Eliminar {{ $user->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
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

    {{-- Modal para Editar Usuario --}}
    <div id="modal-editar-usuario" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-usuario-titulo" onclick="cerrarModalUsuarioSiClickFondo(event)">
        <div class="modal-sheet" role="document" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modal-usuario-titulo" style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--txt-primary);">Editar Usuario</h2>
                <button type="button" class="modal-close-btn" onclick="cerrarModalEditarUsuario()" aria-label="Cerrar">&times;</button>
            </div>
            <form id="form-editar-usuario" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_user_name" class="form-label text-xs">Nombre Completo <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_user_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_user_email" class="form-label text-xs">Correo Electrónico <span class="required">*</span></label>
                        <input type="email" name="email" id="edit_user_email" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_user_role_id" class="form-label text-xs">Rol de Acceso <span class="required">*</span></label>
                        <select name="role_id" id="edit_user_role_id" class="form-control" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_user_password" class="form-label text-xs">Nueva Contraseña (opcional)</label>
                        <input type="password" name="password" id="edit_user_password" class="form-control" placeholder="Dejar en blanco para conservar la actual">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label text-xs" style="font-weight: 700;">Fundos Asignados:</label>
                        <div class="fundos-checklist">
                            @foreach($fundos as $fundo)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-sm); cursor: pointer;">
                                    <input type="checkbox" name="fundos[]" value="{{ $fundo->id }}" class="edit-fundo-checkbox" style="width: 16px; height: 16px; accent-color: var(--clr-primary-600);">
                                    <span>{{ $fundo->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="cerrarModalEditarUsuario()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var modal = document.getElementById('modal-editar-usuario');
    var form = document.getElementById('form-editar-usuario');
    var inputName = document.getElementById('edit_user_name');
    var inputEmail = document.getElementById('edit_user_email');
    var selectRole = document.getElementById('edit_user_role_id');
    var inputPassword = document.getElementById('edit_user_password');
    var checkboxes = document.querySelectorAll('.edit-fundo-checkbox');

    window.abrirModalEditarUsuario = function(id, name, email, roleId, fundosIds) {
        form.action = '{{ url("/administracion/usuarios") }}/' + id;
        inputName.value = name;
        inputEmail.value = email;
        selectRole.value = roleId;
        inputPassword.value = '';

        var assigned = Array.isArray(fundosIds) ? fundosIds : [];
        checkboxes.forEach(function(cb) {
            cb.checked = assigned.indexOf(parseInt(cb.value, 10)) !== -1;
        });

        modal.classList.add('is-open');
    };

    window.cerrarModalEditarUsuario = function() {
        modal.classList.remove('is-open');
    };

    window.cerrarModalUsuarioSiClickFondo = function(e) {
        if (e.target === modal) {
            cerrarModalEditarUsuario();
        }
    };
})();
</script>
@endsection
