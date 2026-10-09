@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('styles')
<style>
    /* Layout: pantalla completa split */
    body {
        background: var(--sidebar-bg);
        min-height: 100vh;
        align-items: stretch;
    }

    .login-shell {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    /* Panel izquierdo: identidad visual */
    .login-hero {
        flex: 1;
        display: none; /* visible en desktop */
        flex-direction: column;
        justify-content: space-between;
        padding: 2.5rem;
        background: linear-gradient(160deg, #0f1f14 0%, #1a3322 40%, #0f2b1f 100%);
        position: relative;
        overflow: hidden;
    }

    .login-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 50% at 30% 60%, rgba(34,197,94,0.10) 0%, transparent 65%),
            radial-gradient(ellipse 50% 40% at 80% 20%, rgba(22,163,74,0.07) 0%, transparent 60%);
    }

    .hero-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        z-index: 1;
    }

    .hero-logo-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #22c55e, #15803d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.375rem;
        box-shadow: 0 4px 16px rgba(34,197,94,0.35);
    }

    .hero-logo-text strong {
        display: block;
        font-size: 1.125rem;
        font-weight: 700;
        color: white;
        letter-spacing: -0.01em;
    }

    .hero-logo-text span {
        font-size: 0.8125rem;
        color: rgba(255,255,255,0.45);
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        line-height: 1.15;
        letter-spacing: -0.025em;
        margin-bottom: 1.25rem;
    }

    .hero-title em {
        font-style: normal;
        color: var(--clr-primary-400);
    }

    .hero-desc {
        font-size: 1rem;
        color: rgba(255,255,255,0.55);
        line-height: 1.7;
        max-width: 380px;
    }

    .hero-stats {
        display: flex;
        gap: 2rem;
        margin-top: 2.5rem;
        position: relative;
        z-index: 1;
    }

    .hero-stat-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
        line-height: 1;
    }

    .hero-stat-label {
        font-size: 0.75rem;
        color: rgba(255,255,255,0.4);
        margin-top: 0.25rem;
    }

    /* Ornamentos decorativos */
    .hero-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(60px);
        opacity: 0.25;
        pointer-events: none;
    }

    .hero-orb-1 {
        width: 300px;
        height: 300px;
        background: var(--clr-primary-500);
        bottom: -80px;
        right: -80px;
    }

    .hero-orb-2 {
        width: 200px;
        height: 200px;
        background: var(--clr-earth-400);
        top: 120px;
        right: 20px;
        opacity: 0.12;
    }

    /* Panel derecho: formulario */
    .login-panel {
        width: 100%;
        max-width: 480px;
        background: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2.5rem;
        position: relative;
        overflow-y: auto;
    }

    /* Mobile: logo arriba */
    .login-mobile-brand {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin-bottom: 2rem;
    }

    .login-mobile-brand-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #22c55e, #15803d);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        box-shadow: 0 2px 8px rgba(34,197,94,0.25);
    }

    .login-mobile-brand-text strong {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: var(--txt-primary);
    }

    .login-mobile-brand-text span {
        font-size: var(--text-xs);
        color: var(--txt-muted);
    }

    .login-heading {
        font-size: clamp(1.4rem, 5vw, 1.75rem);
        font-weight: 800;
        color: var(--txt-primary);
        letter-spacing: -0.02em;
        margin-bottom: 0.375rem;
    }

    .login-subheading {
        font-size: clamp(0.75rem, 2.8vw, 0.875rem);
        color: var(--txt-muted);
        margin-bottom: 2rem;
    }

    /* Formulario */
    .login-form .form-control {
        height: 44px;
        font-size: 16px; /* Evita auto-zoom en iOS Safari */
        border-radius: var(--radius-md);
        border-color: #d1d9d4;
        transition: all var(--transition-fast);
    }

    .login-form .form-control:focus {
        border-color: var(--clr-primary-600);
        box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
    }

    .remember-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .form-check-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: var(--text-sm);
        color: var(--txt-muted);
        cursor: pointer;
        user-select: none;
    }

    .form-check-label input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--clr-primary-600);
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-login {
        height: 48px;
        font-size: clamp(0.875rem, 3.5vw, 1rem);
        border-radius: var(--radius-md);
        width: 100%;
        background: linear-gradient(135deg, var(--clr-primary-700), var(--clr-primary-800));
        border: none;
        color: white;
        font-weight: 600;
        font-family: var(--font-sans);
        cursor: pointer;
        transition: all var(--transition-fast);
        box-shadow: 0 2px 8px rgba(22,101,52,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-login:hover {
        background: linear-gradient(135deg, var(--clr-primary-800), var(--clr-primary-900));
        box-shadow: 0 4px 12px rgba(22,101,52,0.35);
        transform: translateY(-1px);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login:focus-visible {
        outline: 2px solid var(--clr-primary-600);
        outline-offset: 2px;
    }

    /* Cuentas demo */
    .demo-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px dashed var(--brd-base);
    }

    .demo-header {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: var(--text-xs);
        font-weight: 600;
        color: var(--txt-muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 0.75rem;
    }

    .demo-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
    }

    .demo-table {
        width: 100%;
        border-collapse: collapse;
        font-size: var(--text-xs);
    }

    .demo-table th {
        padding: 0.375rem 0.5rem;
        text-align: left;
        color: var(--txt-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.625rem;
        border-bottom: 1px solid var(--brd-base);
        white-space: nowrap;
    }

    .demo-table td {
        padding: 0.4375rem 0.5rem;
        border-bottom: 1px solid var(--clr-surface-100);
        color: var(--txt-secondary);
        vertical-align: middle;
        white-space: nowrap;
    }

    .demo-table tbody tr {
        cursor: pointer;
        transition: background var(--transition-fast);
        border-radius: var(--radius-sm);
    }

    .demo-table tbody tr:hover td {
        background: var(--clr-primary-50);
    }

    .demo-table tbody tr:last-child td { border-bottom: none; }

    .demo-password-hint {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: clamp(0.625rem, 2.2vw, 0.6875rem);
        color: var(--txt-muted);
        background: var(--clr-surface-100);
        padding: 0.1875rem 0.4375rem;
        border-radius: var(--radius-sm);
        margin-bottom: 0.625rem;
        font-family: 'Courier New', monospace;
    }

    /* Pill roles en tabla demo */
    .rpill {
        display: inline-block;
        padding: 0.125rem 0.4375rem;
        font-size: 0.625rem;
        font-weight: 700;
        border-radius: 9999px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .rpill-admin      { background: #fee2e2; color: #991b1b; }
    .rpill-general    { background: #dbeafe; color: #1e40af; }
    .rpill-individual { background: #d1fae5; color: #065f46; }
    .rpill-analista   { background: #fef3c7; color: #92400e; }

    /* Responsive */
    @media (min-width: 768px) {
        .login-hero { display: flex; }
        .login-mobile-brand { display: none; }
        .login-panel { border-left: 1px solid #1a3322; }
    }

    @media (max-width: 500px) {
        .login-panel { padding: clamp(1.25rem, 5vw, 1.75rem) clamp(0.875rem, 4vw, 1.25rem); }
        .demo-table {
            font-size: clamp(0.65rem, 2.3vw, 0.75rem);
            min-width: 290px;
        }
        .demo-table th, .demo-table td {
            padding: 0.35rem 0.35rem;
        }
        .rpill {
            font-size: 0.5625rem;
            padding: 0.1rem 0.35rem;
        }
    }
</style>
@endsection

@section('content')
<div class="login-shell">

    {{-- PANEL IZQUIERDO (DESKTOP) --}}
    <div class="login-hero" aria-hidden="true">
        {{-- Orbes decorativos --}}
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>

        {{-- Logo --}}
        <div class="hero-logo">
            <div class="hero-logo-icon" style="overflow:hidden;padding:0;">
                <img src="/icons/icon-192.webp" alt="Sistema Fundo" width="44" height="44" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;">
            </div>
            <div class="hero-logo-text">
                <strong>Sistema Web del Fundo</strong>
                <span>Gestión Agrícola</span>
            </div>
        </div>

        {{-- Mensaje central --}}
        <div class="hero-content">
            <h1 class="hero-title">
                Control total<br>
                de tu <em>producción</em><br>
                agrícola.
            </h1>
            <p class="hero-desc">
                Registra, sincroniza y analiza las ventas de descarte de tus fundos con precisión y en tiempo real, incluso sin conexión a internet.
            </p>
            <div class="hero-stats">
                <div>
                    <div class="hero-stat-value">3</div>
                    <div class="hero-stat-label">Fundos activos</div>
                </div>
                <div>
                    <div class="hero-stat-value">4</div>
                    <div class="hero-stat-label">Roles de acceso</div>
                </div>
                <div>
                    <div class="hero-stat-value">PWA</div>
                    <div class="hero-stat-label">Modo offline</div>
                </div>
            </div>
        </div>

        {{-- Footer hero --}}
        <div style="font-size: 0.6875rem; color: rgba(255,255,255,0.25); position: relative; z-index: 1;">
            TAL S.A. &bull; Uso interno &bull; {{ now()->year }}
        </div>
    </div>

    {{-- PANEL DERECHO: FORMULARIO --}}
    <div class="login-panel">

        {{-- Logo móvil --}}
        <div class="login-mobile-brand" aria-hidden="true">
            <div class="login-mobile-brand-icon" style="overflow:hidden;padding:0;">
                <img src="/icons/icon-192.webp" alt="Sistema Fundo" width="36" height="36" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;display:block;">
            </div>
            <div class="login-mobile-brand-text">
                <strong>Sistema Web del Fundo</strong>
                <span>Gestión Agrícola</span>
            </div>
        </div>

        <h1 class="login-heading">Iniciar Sesión</h1>
        <p class="login-subheading">Ingresa con tus credenciales autorizadas para continuar</p>

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <span aria-hidden="true">⚠️</span>
                <div>
                    <strong>Error de autenticación:</strong>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Formulario --}}
        <form action="/login" method="POST" class="login-form" novalidate>
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">
                    Correo Electrónico <span class="required" aria-label="campo obligatorio">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="usuario@fundo.test"
                    aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                >
                @error('email')
                    <div class="form-error" id="email-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Contraseña <span class="required" aria-label="campo obligatorio">*</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                >
                @error('password')
                    <div class="form-error" id="password-error" role="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="remember-row">
                <label class="form-check-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} aria-label="Recordar sesión">
                    <span>Recordar sesión</span>
                </label>
            </div>

            <button type="submit" class="btn-login" id="btn-login">
                <span>🔐</span> Iniciar Sesión
            </button>
        </form>

        {{-- Cuentas demo --}}
        <div class="demo-section" aria-label="Cuentas de prueba para demostración">
            <div class="demo-header">
                <span aria-hidden="true">🧪</span>
                Cuentas de demostración (clic para llenar)
            </div>

            <div class="demo-password-hint">
                🔑 Contraseña común: <strong>password123</strong>
            </div>

            <div class="demo-table-wrapper">
                <table class="demo-table" role="table" aria-label="Lista de usuarios de prueba">
                    <thead>
                        <tr>
                            <th scope="col">Rol</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Alcance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr onclick="fillLogin('admin@fundo.test')" tabindex="0" role="row"
                            onkeydown="if(event.key==='Enter')fillLogin('admin@fundo.test')"
                            aria-label="Autenticarse como Administrador">
                            <td><span class="rpill rpill-admin">Admin</span></td>
                            <td>admin@fundo.test</td>
                            <td>Todos</td>
                        </tr>
                        <tr onclick="fillLogin('general.sofia@fundo.test')" tabindex="0" role="row"
                            onkeydown="if(event.key==='Enter')fillLogin('general.sofia@fundo.test')"
                            aria-label="Autenticarse como General - Santa Sofía">
                            <td><span class="rpill rpill-general">General</span></td>
                            <td>general.sofia@fundo.test</td>
                            <td>Santa Sofía</td>
                        </tr>
                        <tr onclick="fillLogin('general.elena@fundo.test')" tabindex="0" role="row"
                            onkeydown="if(event.key==='Enter')fillLogin('general.elena@fundo.test')"
                            aria-label="Autenticarse como General - Santa Elena">
                            <td><span class="rpill rpill-general">General</span></td>
                            <td>general.elena@fundo.test</td>
                            <td>Santa Elena</td>
                        </tr>
                        <tr onclick="fillLogin('individual.sofia@fundo.test')" tabindex="0" role="row"
                            onkeydown="if(event.key==='Enter')fillLogin('individual.sofia@fundo.test')"
                            aria-label="Autenticarse como Individual - Santa Sofía">
                            <td><span class="rpill rpill-individual">Individual</span></td>
                            <td>individual.sofia@fundo.test</td>
                            <td>Santa Sofía</td>
                        </tr>
                        <tr onclick="fillLogin('analista@fundo.test')" tabindex="0" role="row"
                            onkeydown="if(event.key==='Enter')fillLogin('analista@fundo.test')"
                            aria-label="Autenticarse como Analista">
                            <td><span class="rpill rpill-analista">Analista</span></td>
                            <td>analista@fundo.test</td>
                            <td>Todos</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function fillLogin(email) {
    var emailField    = document.getElementById('email');
    var passwordField = document.getElementById('password');
    if (emailField)    { emailField.value    = email; }
    if (passwordField) { passwordField.value = 'password123'; }
    if (emailField)    { emailField.focus(); }
}
</script>
@endsection
