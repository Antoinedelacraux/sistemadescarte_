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
        <form action="{{ route('admin.fundos.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="name" class="form-label text-xs">Nombre del Fundo <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Ej. AGRICOLA PROCOM (PROCOM)" required value="{{ old('name') }}">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="code" class="form-label text-xs">Código Identificador <span class="required">*</span></label>
                    <input type="text" name="code" id="code" class="form-control" placeholder="Ej. PROCOM" required value="{{ old('code') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="lotes" class="form-label text-xs">
                    Lotes del Fundo (Opcional, separados por comas o líneas)
                </label>
                <input type="text" name="lotes" id="lotes" class="form-control" placeholder="Ej. H01, H02, H03, H04, H05, H06..." value="{{ old('lotes') }}">
                <small class="text-muted" style="font-size: 0.72rem; display: block; margin-top: 0.25rem;">
                    Configura los lotes iniciales del fundo de una vez para que estén disponibles inmediatamente al registrar pesajes.
                </small>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 1.5rem;">
                    Guardar Fundo y Lotes
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de fundos --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-wrapper" style="border: none; border-radius: 0;">
            <table class="data-table" style="min-width: 650px;">
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
                        <td>
                            <strong>{{ $fundo->name }}</strong>
                            @if($fundo->lotes->isNotEmpty())
                                <div style="margin-top: 0.35rem; display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    @foreach($fundo->lotes->take(8) as $l)
                                        <span class="badge-tipo" style="font-size: 0.625rem; font-family: monospace; background: var(--clr-surface-200); color: var(--txt-primary); padding: 0.1rem 0.35rem;">
                                            {{ $l->nombre }}
                                        </span>
                                    @endforeach
                                    @if($fundo->lotes->count() > 8)
                                        <span style="font-size: 0.625rem; color: var(--txt-muted); align-self: center;">
                                            +{{ $fundo->lotes->count() - 8 }} más
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td><code>{{ $fundo->code }}</code></td>
                        <td style="color: var(--txt-muted); font-size: var(--text-xs); font-variant-numeric: tabular-nums;">
                            {{ $fundo->created_at ? $fundo->created_at->format('d/m/Y H:i') : '—' }}
                        </td>
                        <td class="text-right">
                            <button type="button" class="btn btn-ghost btn-sm" style="font-size: 0.72rem; padding: 0.15rem 0.45rem; font-weight: 700; color: var(--clr-primary-700);" onclick="abrirModalGestionLotes({{ $fundo->id }})">
                                {{ $fundo->lotes->count() }} lote(s) &rsaquo;
                            </button>
                        </td>
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
                                {{-- Gestionar Lotes y Cuarteles --}}
                                <button type="button"
                                        class="btn-action-icon btn-action-view"
                                        title="Gestionar Lotes y Cuarteles"
                                        aria-label="Gestionar Lotes de {{ $fundo->name }}"
                                        onclick="abrirModalGestionLotes({{ $fundo->id }})">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                                </button>

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
                    <div class="form-group">
                        <label for="edit_fundo_code" class="form-label text-xs">Código Identificador <span class="required">*</span></label>
                        <input type="text" name="code" id="edit_fundo_code" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="edit_nuevos_lotes" class="form-label text-xs">Agregar Nuevos Lotes (separados por coma)</label>
                        <input type="text" name="nuevos_lotes" id="edit_nuevos_lotes" class="form-control" placeholder="Ej. L10, L11, L12">
                        <small class="text-muted" style="font-size: 0.7rem;">Los lotes existentes se mantendrán intactos.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="cerrarModalEditarFundo()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Fundo</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal para Gestionar Lotes y Cuarteles de un Fundo --}}
    <div id="modal-gestionar-lotes" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-lotes-titulo" onclick="cerrarModalLotesSiClickFondo(event)">
        <div class="modal-sheet" style="max-width: 680px; max-height: 85vh; display: flex; flex-direction: column;" role="document" onclick="event.stopPropagation()">
            <div class="modal-header">
                <div>
                    <h2 id="modal-lotes-titulo" style="font-size: 1.15rem; font-weight: 700; margin: 0; color: var(--txt-primary);">Catálogo de Lotes y Cuarteles</h2>
                    <div id="modal-lotes-subtitulo" style="font-size: 0.75rem; color: var(--txt-muted); margin-top: 0.15rem;">Fundo</div>
                </div>
                <button type="button" class="modal-close-btn" onclick="cerrarModalGestionLotes()" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1;">
                {{-- Formulario para agregar nuevo(s) lote(s) --}}
                <div style="background: var(--clr-surface-50); border: 1px solid var(--brd-base); border-radius: var(--radius-md); padding: 0.875rem; margin-bottom: 1.25rem;">
                    <strong style="font-size: var(--text-xs); color: var(--txt-primary); display: block; margin-bottom: 0.5rem;">
                        + Agregar Lote(s) a este Fundo
                    </strong>
                    <form id="form-agregar-lote" method="POST" action="" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        @csrf
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. L01 o varios separados por comas: L02, L03" required style="flex: 1; min-width: 200px; height: 38px;">
                        <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">
                            Agregar Lote
                        </button>
                    </form>
                </div>

                {{-- Listado de Lotes con sus Cuarteles --}}
                <div>
                    <div style="font-size: var(--text-xs); font-weight: 700; color: var(--txt-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">
                        Lotes Registrados en este Fundo (<span id="modal-lotes-count">0</span>):
                    </div>
                    <div id="modal-lotes-lista" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        {{-- Renderizado dinámico vía JavaScript --}}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModalGestionLotes()">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    var modalEditar = document.getElementById('modal-editar-fundo');
    var formEditar = document.getElementById('form-editar-fundo');
    var inputName = document.getElementById('edit_fundo_name');
    var inputCode = document.getElementById('edit_fundo_code');

    window.abrirModalEditarFundo = function(id, name, code) {
        formEditar.action = '{{ url("/administracion/fundos") }}/' + id;
        inputName.value = name;
        inputCode.value = code;
        modalEditar.classList.add('is-open');
    };

    window.cerrarModalEditarFundo = function() {
        modalEditar.classList.remove('is-open');
    };

    window.cerrarModalFundoSiClickFondo = function(e) {
        if (e.target === modalEditar) {
            cerrarModalEditarFundo();
        }
    };

    // Gestión de Lotes y Cuarteles
    var fundosData = @json($fundos);
    var modalLotes = document.getElementById('modal-gestionar-lotes');
    var subTituloLotes = document.getElementById('modal-lotes-subtitulo');
    var formAgregarLote = document.getElementById('form-agregar-lote');
    var countLotes = document.getElementById('modal-lotes-count');
    var listaLotes = document.getElementById('modal-lotes-lista');

    window.abrirModalGestionLotes = function(fundoId) {
        var fundo = fundosData.find(function(f) { return f.id === fundoId; });
        if (!fundo) return;

        subTituloLotes.textContent = fundo.name + ' (' + fundo.code + ')';
        formAgregarLote.action = '{{ url("/administracion/fundos") }}/' + fundo.id + '/lotes';
        countLotes.textContent = (fundo.lotes || []).length;

        listaLotes.innerHTML = '';
        if (!fundo.lotes || fundo.lotes.length === 0) {
            listaLotes.innerHTML = '<div style="padding: 1.5rem; text-align: center; color: var(--txt-muted); background: var(--clr-surface-50); border-radius: var(--radius-md); font-size: var(--text-sm);">Este fundo aún no tiene lotes registrados. Usa el formulario de arriba para agregar lotes de una vez.</div>';
        } else {
            fundo.lotes.forEach(function(lote) {
                var loteCard = document.createElement('div');
                loteCard.style.cssText = 'background: white; border: 1px solid var(--brd-base); border-radius: var(--radius-md); padding: 0.75rem 1rem;';

                // Encabezado del Lote
                var headerDiv = document.createElement('div');
                headerDiv.style.cssText = 'display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;';
                headerDiv.innerHTML = '<div><strong style="color: var(--txt-primary); font-size: 0.95rem;">Lote ' + escapeHtml(lote.nombre) + '</strong></div>';

                // Botón para eliminar lote (con form DELETE)
                var delForm = document.createElement('form');
                delForm.method = 'POST';
                delForm.action = '{{ url("/administracion/lotes") }}/' + lote.id;
                delForm.style.display = 'inline';
                delForm.onsubmit = function() { return confirm('¿Eliminar el lote ' + lote.nombre + ' y sus cuarteles?'); };
                delForm.innerHTML = '@csrf @method("DELETE")<button type="submit" class="btn btn-ghost btn-sm" style="color: var(--clr-danger); padding: 0.2rem 0.4rem; font-size: 0.6875rem;" title="Eliminar Lote">Eliminar Lote</button>';
                headerDiv.appendChild(delForm);
                loteCard.appendChild(headerDiv);

                // Cuarteles del Lote
                var cuartelesSection = document.createElement('div');
                cuartelesSection.style.cssText = 'padding: 0.5rem; background: var(--clr-surface-50); border-radius: var(--radius-sm); margin-top: 0.35rem;';

                var cuartelesTitle = document.createElement('div');
                cuartelesTitle.style.cssText = 'font-size: 0.6875rem; font-weight: 700; color: var(--txt-muted); text-transform: uppercase; margin-bottom: 0.35rem;';
                cuartelesTitle.textContent = 'Cuarteles registrados (' + ((lote.cuarteles || []).length) + '):';
                cuartelesSection.appendChild(cuartelesTitle);

                var cuartelesWrap = document.createElement('div');
                cuartelesWrap.style.cssText = 'display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; margin-bottom: 0.5rem;';

                if (!lote.cuarteles || lote.cuarteles.length === 0) {
                    cuartelesWrap.innerHTML = '<span style="font-size: 0.72rem; color: var(--txt-muted); font-style: italic;">Sin cuarteles registrados aún. (Se agregarán automáticamente cuando los pesadores los registren en ventas o puedes agregarlos aquí).</span>';
                } else {
                    lote.cuarteles.forEach(function(c) {
                        var cChip = document.createElement('span');
                        cChip.style.cssText = 'display: inline-flex; align-items: center; gap: 0.3rem; background: white; border: 1px solid var(--brd-base); padding: 0.15rem 0.45rem; border-radius: var(--radius-sm); font-size: 0.72rem; font-weight: 600; color: var(--txt-primary);';
                        cChip.textContent = c.nombre;

                        var cDelForm = document.createElement('form');
                        cDelForm.method = 'POST';
                        cDelForm.action = '{{ url("/administracion/cuarteles") }}/' + c.id;
                        cDelForm.style.display = 'inline';
                        cDelForm.onsubmit = function() { return confirm('¿Eliminar cuartel ' + c.nombre + '?'); };
                        cDelForm.innerHTML = '@csrf @method("DELETE")<button type="submit" style="background:none; border:none; color:var(--txt-muted); cursor:pointer; font-size:0.8rem; line-height:1; padding:0;" title="Quitar cuartel">&times;</button>';
                        cChip.appendChild(cDelForm);

                        cuartelesWrap.appendChild(cChip);
                    });
                }
                cuartelesSection.appendChild(cuartelesWrap);

                // Formulario inline para agregar cuartel al lote
                var addCuartelForm = document.createElement('form');
                addCuartelForm.method = 'POST';
                addCuartelForm.action = '{{ url("/administracion/lotes") }}/' + lote.id + '/cuarteles';
                addCuartelForm.style.cssText = 'display: flex; gap: 0.35rem; align-items: center;';
                addCuartelForm.innerHTML = '@csrf<input type="text" name="nombre" placeholder="+ Nuevo cuartel (ej. C01, Cuartel 1)" required style="height: 28px; font-size: 0.72rem; padding: 0 0.5rem; border: 1px solid var(--brd-base); border-radius: var(--radius-sm); flex: 1; max-width: 220px;"><button type="submit" class="btn btn-secondary btn-sm" style="height: 28px; padding: 0 0.5rem; font-size: 0.6875rem;">+ Agregar Cuartel</button>';
                cuartelesSection.appendChild(addCuartelForm);

                loteCard.appendChild(cuartelesSection);
                listaLotes.appendChild(loteCard);
            });
        }

        modalLotes.classList.add('is-open');
    };

    window.cerrarModalGestionLotes = function() {
        modalLotes.classList.remove('is-open');
    };

    window.cerrarModalLotesSiClickFondo = function(e) {
        if (e.target === modalLotes) {
            cerrarModalGestionLotes();
        }
    };

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>
@endsection
