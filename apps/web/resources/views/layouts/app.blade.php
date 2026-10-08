<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistema Fundo') - Sistema de Gestión Agrícola</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2d6a4f;
            --primary-dark: #1b4332;
            --primary-light: #40916c;
            --accent: #52b788;
            --bg-body: #f8faf9;
            --surface: #ffffff;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        header.navbar {
            background-color: var(--primary-dark);
            color: white;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            text-decoration: none;
        }

        .navbar-brand span {
            color: var(--accent);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        .badge-admin { background: #fee2e2; color: #991b1b; }
        .badge-general { background: #dbeafe; color: #1e40af; }
        .badge-individual { background: #d1fae5; color: #065f46; }
        .badge-analista { background: #fef3c7; color: #92400e; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.375rem;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            background-color: transparent;
            border-color: rgba(255,255,255,0.4);
            color: white;
        }
        .btn-outline:hover {
            background-color: rgba(255,255,255,0.1);
        }

        main.container {
            flex: 1;
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-danger {
            background-color: var(--danger-bg);
            color: var(--danger);
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: var(--success-bg);
            color: var(--success);
            border: 1px solid #a7f3d0;
        }

        footer {
            text-align: center;
            padding: 1rem;
            font-size: 0.875rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            background: var(--surface);
        }
    </style>
    @yield('styles')
</head>
<body>
    @auth
    <header class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            🌾 Sistema <span>Fundo</span>
        </a>
        <div class="navbar-user">
            <span style="font-size: 0.875rem;">
                {{ Auth::user()->name }}
                <span class="badge badge-{{ Auth::user()->role?->name ?? 'default' }}">
                    {{ Auth::user()->role?->display_name ?? 'Sin Rol' }}
                </span>
            </span>
            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline" style="padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </header>
    @endauth

    <main class="container">
        @yield('content')
    </main>

    <footer>
        Sistema Web del Fundo &bull; Fase 3 — Fundación Técnica (Local) &bull; Entorno: {{ app()->environment() }}
    </footer>
</body>
</html>
