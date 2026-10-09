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
            <span>➕</span> Registrar Nuevo Fundo
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
            <table class="data-table" style="min-width: 500px;">
                <thead>
                    <tr>
                        <th>Fundo</th>
                        <th>Código</th>
                        <th class="text-right">Lotes</th>
                        <th class="text-right">Usuarios Asignados</th>
                        <th class="text-right">Ventas Registradas</th>
                        <th style="text-align: center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fundos as $fundo)
                    <tr>
                        <td><strong>{{ $fundo->name }}</strong></td>
                        <td><code>{{ $fundo->code }}</code></td>
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
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
