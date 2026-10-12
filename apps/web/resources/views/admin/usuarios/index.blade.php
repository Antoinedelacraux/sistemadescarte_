@extends('layouts.app')

@section('title', 'Administración de Usuarios')
@section('page-title', 'Administración de Usuarios, Roles y Permisos')

@section('styles')
<style>
    .fundos-checklist, .modulos-checklist {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--clr-surface-50);
        border-radius: var(--radius-md);
        border: 1px solid var(--brd-base);
    }

    .modulos-checklist {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        background: #ffffff;
    }

    .badge-modulo {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 600;
        border-radius: 4px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }

    .badge-excepcion {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.2rem 0.5rem;
        font-size: 0.68rem;
        font-weight: 700;
        border-radius: 4px;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
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
        max-width: 620px;
        width: 100%;
        max-height: min(90vh, 720px);
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
        flex-shrink: 0;
    }
    .modal-sheet form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        overflow: hidden;
        margin: 0;
    }
    .modal-body {
        padding: 1.25rem;
        overflow-y: auto;
        flex: 1 1 auto;
        min-height: 0;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
    .modal-footer {
        padding: 0.875rem 1.25rem;
        border-top: 1px solid var(--brd-base);
        background: var(--clr-surface-50);
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 0.65rem;
        flex-shrink: 0;
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
        <p style="font-size: clamp(0.75rem, 2.8vw, 0.875rem); color: var(--txt-muted);">Gestión de cuentas, roles oficiales (Jefe de fundo, Registrador, Analista, Ventas, Administrador) y habilitación/deshabilitación modular por usuario</p>
    </div>

    {{-- Formulario para registrar usuario --}}
    <details class="card" style="margin-bottom: 1.5rem;" {{ $errors->any() ? 'open' : '' }}>
        <summary style="font-size: var(--text-sm); font-weight: 700; color: var(--txt-primary); cursor: pointer; padding: 0.25rem 0;">
            ➕ Crear Nuevo Usuario
        </summary>

        <form action="{{ route('admin.usuarios.store') }}" method="POST" style="margin-top: 1.25rem; border-top: 1px solid var(--brd-base); padding-top: 1.25rem;">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                <div class="form-group">
                    <label for="create_name" class="form-label text-xs">Nombre Completo <span class="required">*</span></label>
                    <input type="text" name="name" id="create_name" class="form-control" placeholder="Ej. Pedro Morales" required value="{{ old('name') }}">
                </div>

                <div class="form-group">
                    <label for="create_email" class="form-label text-xs">Correo Electrónico <span class="required">*</span></label>
                    <input type="email" name="email" id="create_email" class="form-control" placeholder="pedro@fundo.test" required value="{{ old('email') }}">
                </div>

                <div class="form-group">
                    <label for="create_password" class="form-label text-xs">Contraseña Inicial <span class="required">*</span></label>
                    <input type="password" name="password" id="create_password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="create_role_id" class="form-label text-xs">Rol de Acceso <span class="required">*</span></label>
                    <select name="role_id" id="create_role_id" class="form-control" required onchange="onRoleChangedCreate()">
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" data-role-name="{{ $role->name }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Asignación de Fundos --}}
            <div style="margin-top: 0.5rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; margin-bottom: 0.35rem;">
                    <label class="form-label text-xs" style="font-weight: 700; margin: 0;">Fundos Asignados:</label>
                    <span style="font-size: 0.72rem; color: var(--txt-muted);">(Los roles Administrador, Jefe de fundo, Analista y Ventas tienen acceso total a todos los fundos automáticamente)</span>
                </div>
                <div class="fundos-checklist">
                    @foreach($fundos as $fundo)
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-sm); cursor: pointer;">
                            <input type="checkbox" name="fundos[]" value="{{ $fundo->id }}" style="width: 16px; height: 16px; accent-color: var(--clr-primary-600);">
                            <span>{{ $fundo->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Personalización de Módulos (Casos Excepcionales) --}}
            <div style="margin-top: 1rem; padding: 1rem; background: var(--clr-surface-50); border: 1px solid var(--brd-base); border-radius: var(--radius-md);">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <div>
                        <strong style="font-size: var(--text-sm); color: var(--txt-primary);">Módulos de Acceso Habilitados</strong>
                        <p style="font-size: 0.75rem; color: var(--txt-muted); margin: 0.2rem 0 0;">
                            Los módulos se configuran por defecto según el rol. Active la opción para habilitar o deshabilitar módulos en casos excepcionales.
                        </p>
                    </div>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-xs); font-weight: 600; cursor: pointer; background: white; padding: 0.35rem 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--brd-base);">
                        <input type="checkbox" name="usar_modulos_personalizados" id="create_usar_modulos_personalizados" value="1" onchange="togglePersonalizacionModulos('create')">
                        <span>Habilitar configuración excepcional</span>
                    </label>
                </div>

                <div id="create_modulos_container" class="modulos-checklist" style="opacity: 0.85;">
                    @foreach($modulos as $modKey => $modLabel)
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-sm); cursor: pointer;">
                            <input type="checkbox" name="modulos[]" value="{{ $modKey }}" class="create-modulo-cb" style="width: 16px; height: 16px; accent-color: var(--clr-primary-600);">
                            <span>{{ $modLabel }}</span>
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
            <table class="data-table" style="min-width: 640px;">
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
                            @if($user->hasCustomModules())
                                <span class="badge-excepcion" title="Usuario con permisos personalizados para casos excepcionales" style="margin-left: 0.25rem;">⚙️ Excepción</span>
                            @endif
                        </td>
                        <td>
                            @if($user->hasAccessToAllFundos())
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
                                <span class="role-badge role-badge-registrador">Activo</span>
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
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role-id="{{ $user->role_id }}"
                                        data-fundos='@json($user->fundos->pluck("id"))'
                                        data-custom="{{ $user->hasCustomModules() ? '1' : '0' }}"
                                        data-modulos='@json($user->getModulosHabilitados())'
                                        onclick="abrirModalEditarUsuarioDesdeBoton(this)">
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
            <div class="pagination-wrapper">
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
                        <select name="role_id" id="edit_user_role_id" class="form-control" required onchange="onRoleChangedEdit()">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_user_password" class="form-label text-xs">Nueva Contraseña (opcional)</label>
                        <input type="password" name="password" id="edit_user_password" class="form-control" placeholder="Dejar en blanco para conservar la actual" autocomplete="new-password">
                    </div>

                    {{-- Fundos asignados --}}
                    <div class="form-group" style="margin-bottom: 1rem;">
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

                    {{-- Personalización de Módulos (Casos Excepcionales) en Modal --}}
                    <div style="padding: 0.85rem; background: var(--clr-surface-50); border: 1px solid var(--brd-base); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.65rem;">
                            <div>
                                <strong style="font-size: var(--text-xs); color: var(--txt-primary);">Módulos Habilitados</strong>
                                <div style="font-size: 0.72rem; color: var(--txt-muted);">Excepciones específicas para este usuario</div>
                            </div>
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-xs); font-weight: 600; cursor: pointer; background: white; padding: 0.25rem 0.6rem; border-radius: var(--radius-sm); border: 1px solid var(--brd-base);">
                                <input type="checkbox" name="usar_modulos_personalizados" id="edit_usar_modulos_personalizados" value="1" onchange="togglePersonalizacionModulos('edit')">
                                <span>Personalizar (Excepción)</span>
                            </label>
                        </div>

                        <div id="edit_modulos_container" class="modulos-checklist">
                            @foreach($modulos as $modKey => $modLabel)
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: var(--text-xs); cursor: pointer;">
                                    <input type="checkbox" name="modulos[]" value="{{ $modKey }}" class="edit-modulo-cb" style="width: 16px; height: 16px; accent-color: var(--clr-primary-600);">
                                    <span>{{ $modLabel }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalEditarUsuario()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-editar-usuario" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                        Guardar Cambios
                    </button>
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
    var checkboxesFundos = document.querySelectorAll('.edit-fundo-checkbox');

    // Mapeo oficial de módulos predeterminados por rol
    var roleDefaultModulos = {
        'admin': ['panel', 'registro', 'historial', 'admin'],
        'jefe_fundo': ['panel', 'historial'],
        'registrador': ['registro', 'historial'],
        'analista': ['historial'],
        'ventas': ['historial'],
        'general': ['panel', 'historial'],
        'individual': ['registro', 'historial']
    };

    function getSelectedRoleName(selectElem) {
        if (!selectElem || selectElem.selectedIndex < 0) return '';
        var option = selectElem.options[selectElem.selectedIndex];
        return option ? (option.getAttribute('data-role-name') || '') : '';
    }

    function aplicarModulosPorDefecto(context, roleName) {
        var defaults = roleDefaultModulos[roleName] || [];
        var selector = context === 'create' ? '.create-modulo-cb' : '.edit-modulo-cb';
        var cbs = document.querySelectorAll(selector);
        cbs.forEach(function(cb) {
            cb.checked = defaults.indexOf(cb.value) !== -1;
        });
    }

    window.togglePersonalizacionModulos = function(context) {
        var toggleId = context === 'create' ? 'create_usar_modulos_personalizados' : 'edit_usar_modulos_personalizados';
        var containerId = context === 'create' ? 'create_modulos_container' : 'edit_modulos_container';
        var selectId = context === 'create' ? 'create_role_id' : 'edit_user_role_id';

        var toggle = document.getElementById(toggleId);
        var container = document.getElementById(containerId);
        var selectElem = document.getElementById(selectId);

        if (!toggle.checked) {
            // Si desactiva personalización, restablecer a los módulos por defecto del rol seleccionado
            var roleName = getSelectedRoleName(selectElem);
            aplicarModulosPorDefecto(context, roleName);
            container.style.pointerEvents = 'none';
            container.style.opacity = '0.7';
        } else {
            container.style.pointerEvents = 'auto';
            container.style.opacity = '1';
        }
    };

    window.onRoleChangedCreate = function() {
        var selectElem = document.getElementById('create_role_id');
        var toggle = document.getElementById('create_usar_modulos_personalizados');
        var roleName = getSelectedRoleName(selectElem);

        if (!toggle.checked) {
            aplicarModulosPorDefecto('create', roleName);
        }
    };

    window.onRoleChangedEdit = function() {
        var selectElem = document.getElementById('edit_user_role_id');
        var toggle = document.getElementById('edit_usar_modulos_personalizados');
        var roleName = getSelectedRoleName(selectElem);

        if (!toggle.checked) {
            aplicarModulosPorDefecto('edit', roleName);
        }
    };

    window.abrirModalEditarUsuarioDesdeBoton = function(btn) {
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        var name = btn.getAttribute('data-name') || '';
        var email = btn.getAttribute('data-email') || '';
        var roleId = btn.getAttribute('data-role-id');
        var fundosIds = [];
        try { fundosIds = JSON.parse(btn.getAttribute('data-fundos') || '[]'); } catch(e) {}
        var hasCustom = btn.getAttribute('data-custom') === '1';
        var modulosActivos = [];
        try { modulosActivos = JSON.parse(btn.getAttribute('data-modulos') || '[]'); } catch(e) {}

        window.abrirModalEditarUsuario(id, name, email, roleId, fundosIds, hasCustom, modulosActivos);
    };

    window.abrirModalEditarUsuario = function(id, name, email, roleId, fundosIds, hasCustom, modulosActivos) {
        form.action = '{{ url("/administracion/usuarios") }}/' + id;
        inputName.value = name;
        inputEmail.value = email;
        selectRole.value = roleId;
        inputPassword.value = '';

        // Sincronizar fundos
        var assigned = Array.isArray(fundosIds) ? fundosIds : [];
        checkboxesFundos.forEach(function(cb) {
            cb.checked = assigned.indexOf(parseInt(cb.value, 10)) !== -1;
        });

        // Sincronizar módulos
        var toggle = document.getElementById('edit_usar_modulos_personalizados');
        toggle.checked = !!hasCustom;

        var activeMods = Array.isArray(modulosActivos) ? modulosActivos : [];
        var cbsModulos = document.querySelectorAll('.edit-modulo-cb');
        cbsModulos.forEach(function(cb) {
            cb.checked = activeMods.indexOf(cb.value) !== -1;
        });

        window.togglePersonalizacionModulos('edit');

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

    // Inicializar estado create
    window.togglePersonalizacionModulos('create');
})();
</script>
@endsection
