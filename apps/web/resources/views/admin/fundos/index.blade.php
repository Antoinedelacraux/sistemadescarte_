@extends('layouts.app')

@section('title', 'Administración de Fundos')
@section('page-title', 'Administración de Fundos')

@section('styles')
<style>
    .fundo-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) 160px;
        gap: 1rem;
        align-items: flex-end;
    }

    @media (max-width: 640px) {
        .fundo-form-grid {
            grid-template-columns: 1fr;
        }

        .fundo-form-grid .btn {
            height: 46px;
            width: 100%;
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
        max-width: 480px;
        width: 100%;
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
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: clamp(1.2rem, 5vw, 1.5rem); font-weight: 700; color: var(--txt-primary);">Fundos Registrados</h1>
            <p style="font-size: clamp(0.75rem, 2.8vw, 0.875rem); color: var(--txt-muted);">Gestión de sedes agrícolas y control de acceso multi-tenant</p>
        </div>
    </div>

    {{-- Formulario para crear nuevo fundo --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-title" style="margin-bottom: 1rem;">
            Registrar Nuevo Fundo
        </div>
        <form action="{{ route('admin.fundos.store') }}" method="POST" class="fundo-form-grid">
            @csrf
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name" class="form-label text-xs">Nombre del Fundo <span class="required">*</span></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Ej. Fundo El Carmen" required value="{{ old('name') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="code" class="form-label text-xs">Código Identificador <span class="required">*</span></label>
                <input type="text" name="code" id="code" class="form-control" placeholder="Ej. FCARMEN" required value="{{ old('code') }}">
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-full" style="height: 42px;">
                    Guardar Fundo
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de fundos --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="data-table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Fundo</th>
                        <th>Código</th>
                        <th>Fecha y Hora Registro</th>
                        <th class="text-right">Lotes</th>
                        <th class="text-right">Usuarios</th>
                        <th class="text-right">Ventas</th>
                        <th style="text-align: center;">Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fundos as $fundo)
                    <tr style="{{ !$fundo->is_active ? 'opacity: 0.65; background: #fafafa;' : '' }}">
                        <td><strong>{{ $fundo->name }}</strong></td>
                        <td><code>{{ $fundo->code }}</code></td>
                        <td style="color: var(--txt-muted); font-size: var(--text-xs); font-variant-numeric: tabular-nums;">
                            {{ $fundo->created_at ? $fundo->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="text-right">{{ $fundo->lotes_count }}</td>
                        <td class="text-right">{{ $fundo->users_count }}</td>
                        <td class="text-right">{{ $fundo->ventas_count }}</td>
                        <td style="text-align: center;">
                            @if($fundo->is_active)
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
                                        title="Editar Fundo" 
                                        aria-label="Editar {{ $fundo->name }}"
                                        onclick="abrirModalEditarFundo({{ $fundo->id }}, '{{ addslashes($fundo->name) }}', '{{ addslashes($fundo->code) }}')">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>

                                @if($fundo->is_active)
                                    {{-- Anular / Inactivar (Paso 1) --}}
                                    <form method="POST" action="{{ route('admin.fundos.toggle', $fundo) }}" style="display:inline;" onsubmit="return confirm('¿Deseas ANULAR / INACTIVAR este fundo? Los usuarios no podrán seleccionarlo para nuevas ventas.');">
                                        @csrf
                                        <button type="submit" class="btn-action-icon btn-action-anular" title="Anular / Inactivar fundo" aria-label="Inactivar {{ $fundo->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                        </button>
                                    </form>
                                @else
                                    {{-- Reactivar si está inactivo --}}
                                    <form method="POST" action="{{ route('admin.fundos.toggle', $fundo) }}" style="display:inline;" onsubmit="return confirm('¿Deseas REACTIVAR este fundo?');">
                                        @csrf
                                        <button type="submit" class="btn-action-icon btn-action-reactivar" title="Reactivar fundo" aria-label="Reactivar {{ $fundo->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        </button>
                                    </form>

                                    {{-- Eliminar definitivamente (Paso 2: Solo si está inactivo/anulado) --}}
                                    <form method="POST" action="{{ route('admin.fundos.destroy', $fundo) }}" style="display:inline;" onsubmit="return confirm('¡ADVERTENCIA! Este fundo ya está anulado.\n¿Deseas ELIMINARLO DEFINITIVAMENTE de la base de datos?\nEsta acción es irreversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-icon btn-action-delete" title="Eliminar definitivamente de la base de datos" aria-label="Eliminar {{ $fundo->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal para Editar Fundo --}}
    <div id="modal-editar-fundo" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-fundo-titulo" onclick="cerrarModalFundoSiClickFondo(event)">
        <div class="modal-sheet" role="document" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modal-fundo-titulo" style="font-size: 1.1rem; font-weight: 700; margin: 0; color: var(--txt-primary);">Editar Fundo</h2>
                <button type="button" class="modal-close-btn" onclick="cerrarModalEditarFundo()" aria-label="Cerrar">&times;</button>
            </div>
            <form id="form-editar-fundo" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_fundo_name" class="form-label text-xs">Nombre del Fundo <span class="required">*</span></label>
                        <input type="text" name="name" id="edit_fundo_name" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_fundo_code" class="form-label text-xs">Código Identificador <span class="required">*</span></label>
                        <input type="text" name="code" id="edit_fundo_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="cerrarModalEditarFundo()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Fundo</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var modal = document.getElementById('modal-editar-fundo');
    var form = document.getElementById('form-editar-fundo');
    var inputName = document.getElementById('edit_fundo_name');
    var inputCode = document.getElementById('edit_fundo_code');

    window.abrirModalEditarFundo = function(id, name, code) {
        form.action = '{{ url("/administracion/fundos") }}/' + id;
        inputName.value = name;
        inputCode.value = code;
        modal.classList.add('is-open');
    };

    window.cerrarModalEditarFundo = function() {
        modal.classList.remove('is-open');
    };

    window.cerrarModalFundoSiClickFondo = function(e) {
        if (e.target === modal) {
            cerrarModalEditarFundo();
        }
    };
})();
</script>
@endsection
