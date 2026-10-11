<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema Web de Gestión Agrícola para fundos. Módulo de venta de descarte y reportes.">
    <title>@yield('title', 'Sistema Fundo') — Gestión Agrícola</title>

    <!-- PWA & Mobile Web App Settings (Android / iPhone) -->
    <meta name="theme-color" content="#166534">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Sistema Fundo">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/images/logo-emblem.png">
    <link rel="icon" type="image/webp" href="/images/logo-emblem.webp">
    <link rel="apple-touch-icon" href="/images/logo-emblem.png">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Sistema de diseño y estilos globales --}}
    @include('layouts.partials._styles')
    @yield('styles')
</head>
<body>
    <div class="app-shell">

        @auth
            {{-- Navegación lateral y overlay móvil --}}
            @include('layouts.partials._sidebar')

            {{-- Área principal de trabajo --}}
            <div class="main-wrapper" id="main-wrapper">
                {{-- Barra superior con estado de red, fundo y perfil --}}
                @include('layouts.partials._header')

                {{-- Contenido de cada vista --}}
                <main class="page-content" id="main-content" role="main" tabindex="-1">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            <span aria-hidden="true">✅</span>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            <span aria-hidden="true">⚠️</span>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    @yield('content')
                </main>

                {{-- Pie de página institucional --}}
                <footer style="padding: 0.75rem 1.5rem; font-size: var(--text-xs); color: var(--txt-muted); border-top: 1px solid var(--brd-base); background: var(--clr-surface-0); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;" role="contentinfo">
                    <span>Sistema Web del Fundo &bull; Fase 4 — UI/UX &bull; {{ now()->year }}</span>
                    <span>Entorno: {{ app()->environment() }}</span>
                </footer>
            </div>
        @else
            {{-- Sin autenticación: layout simple centrado --}}
            <main style="flex: 1; display: flex; flex-direction: column;" role="main">
                @yield('content')
            </main>
        @endauth

    </div><!-- /.app-shell -->

    {{-- Scripts del layout principal (sidebar, dropdowns, PWA, custom selects) --}}
    @include('layouts.partials._scripts')
    @yield('scripts')
</body>
</html>
