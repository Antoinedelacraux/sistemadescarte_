@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('styles')
<style>
    .login-container {
        max-width: 440px;
        margin: 2rem auto;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-label {
        display: block;
        margin-bottom: 0.375rem;
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border: 1px solid var(--border);
        border-radius: 0.375rem;
        font-size: 0.875rem;
        outline: none;
        transition: border-color 0.15s ease-in-out;
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(45, 106, 79, 0.15);
    }
    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .demo-accounts {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px dashed var(--border);
        font-size: 0.8125rem;
    }
    .demo-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0.5rem;
        font-size: 0.75rem;
    }
    .demo-table th, .demo-table td {
        padding: 0.375rem 0.5rem;
        text-align: left;
        border-bottom: 1px solid var(--border);
    }
    .demo-table tr:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="login-container">
    <div class="card">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; line-height: 1;">🍇</div>
            <h1 style="font-size: 1.375rem; font-weight: 700; margin-top: 0.5rem; color: var(--primary-dark);">
                Sistema Web del Fundo
            </h1>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">
                Ingreso al Módulo de Venta de Descarte
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Error de autenticación:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="usuario@fundo.test">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label class="form-check">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.625rem;">
                Iniciar Sesión
            </button>
        </form>

        <div class="demo-accounts">
            <p style="font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">
                📋 Usuarios ficticios para pruebas (Clic para autocompletar):
            </p>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                Contraseña común para todos: <code>password123</code>
            </p>
            <table class="demo-table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Correo</th>
                        <th>Fundo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr onclick="fillLogin('admin@fundo.test', 'password123')">
                        <td><span class="badge badge-admin">Admin</span></td>
                        <td>admin@fundo.test</td>
                        <td>Todos</td>
                    </tr>
                    <tr onclick="fillLogin('general.sofia@fundo.test', 'password123')">
                        <td><span class="badge badge-general">General</span></td>
                        <td>general.sofia@fundo.test</td>
                        <td>Santa Sofía</td>
                    </tr>
                    <tr onclick="fillLogin('general.elena@fundo.test', 'password123')">
                        <td><span class="badge badge-general">General</span></td>
                        <td>general.elena@fundo.test</td>
                        <td>Santa Elena</td>
                    </tr>
                    <tr onclick="fillLogin('individual.sofia@fundo.test', 'password123')">
                        <td><span class="badge badge-individual">Individual</span></td>
                        <td>individual.sofia@fundo.test</td>
                        <td>Santa Sofía</td>
                    </tr>
                    <tr onclick="fillLogin('analista@fundo.test', 'password123')">
                        <td><span class="badge badge-analista">Analista</span></td>
                        <td>analista@fundo.test</td>
                        <td>Todos</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function fillLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
