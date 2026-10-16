<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema Web de Gestión Agrícola para fundos. Módulo de venta de descarte y reportes.">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <title>@yield('title', 'Sistema Fundo') — Gestión Agrícola</title>

    <!-- Open Graph / Meta Compartir Redes -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Sistema Fundo') — Gestión Agrícola">
    <meta property="og:description" content="Sistema Web de Gestión Agrícola para fundos. Módulo de venta de descarte y reportes.">
    <meta property="og:image" content="{{ asset('icons/icon-512.png') }}">
    <meta property="og:site_name" content="TALSA Fundo">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'Sistema Fundo') — Gestión Agrícola">
    <meta name="twitter:description" content="Sistema Web de Gestión Agrícola para fundos. Módulo de venta de descarte y reportes.">
    <meta name="twitter:image" content="{{ asset('icons/icon-512.png') }}">

    <!-- PWA & Mobile Web App Settings (Android / iPhone) -->
    <meta name="theme-color" content="#166534">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TALSA Fundo">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @auth
    <!-- Reglas de Prerenderizado Especulativo (Instant Navigation en PC y Smartphones Chromium) -->
    <script type="speculationrules">
    {
      "prerender": [
        {
          "where": {
            "and": [
              { "href_matches": "/*" },
              { "not": { "href_matches": "/logout" } },
              { "not": { "href_matches": "*/exportar*" } },
              { "not": { "href_matches": "*anular*" } },
              { "not": { "href_matches": "*reactivar*" } },
              { "not": { "href_matches": "*toggle*" } }
            ]
          },
          "eagerness": "moderate"
        }
      ]
    }
    </script>
    @endauth

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

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert" style="align-items: flex-start;">
                            <span aria-hidden="true" style="margin-top: 0.15rem;">⚠️</span>
                            <div>
                                <strong style="display: block; margin-bottom: 0.35rem; font-size: var(--text-sm);">Por favor corrige los siguientes inconvenientes:</strong>
                                <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.85rem; line-height: 1.5;">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </main>

                {{-- Pie de página institucional listo para producción --}}
                <footer class="app-footer" role="contentinfo">
                    <div class="footer-left">
                        <span>Sistema Web del Fundo &bull; TALSA Grape Farms &bull; &copy; {{ now()->year }}</span>
                    </div>
                    <div class="footer-right">
                        @if(app()->isProduction())
                            <span class="footer-prod-tag" title="Sistema oficial en producción">
                                <span class="footer-prod-dot" aria-hidden="true"></span>
                                Producción &bull; v1.0.0
                            </span>
                        @else
                            <span class="footer-env-pill" title="Ambiente de ejecución">
                                <span class="footer-env-dot" aria-hidden="true"></span>
                                Entorno: {{ app()->environment() }}
                            </span>
                            <span class="footer-version">v1.0.0</span>
                        @endif
                    </div>
                </footer>
            </div>
        @else
            {{-- Sin autenticación: layout simple centrado --}}
            <main style="flex: 1; display: flex; flex-direction: column;" role="main">
                @yield('content')
            </main>
        @endauth

        {{-- Modal de guía para instalación PWA en iOS Safari --}}
        <div id="ios-install-modal" class="ios-install-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="ios-modal-title">
            <div class="ios-install-backdrop" onclick="closeIosInstallModal()"></div>
            <div class="ios-install-sheet">
                <div class="ios-install-header">
                    <h3 id="ios-modal-title" style="margin:0;font-size:var(--text-base);font-weight:700;color:var(--txt-primary);">📲 Instalar TALSA Fundo</h3>
                    <button type="button" class="ios-install-close" onclick="closeIosInstallModal()" aria-label="Cerrar">&times;</button>
                </div>
                <div class="ios-install-body">
                    <p style="font-size:var(--text-sm);color:var(--txt-secondary);margin-bottom:1rem;line-height:1.5;">
                        Instala la app en tu iPhone o iPad para abrirla en pantalla completa y acceder rápidamente sin conexión en los cuarteles:
                    </p>
                    <div class="ios-step-item">
                        <span class="ios-step-badge">1</span>
                        <span>Toca el botón <strong>Compartir</strong> <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:text-bottom;display:inline-block;"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path><polyline points="16 6 12 2 8 6"></polyline><line x1="12" y1="2" x2="12" y2="15"></line></svg> en la barra inferior de Safari.</span>
                    </div>
                    <div class="ios-step-item">
                        <span class="ios-step-badge">2</span>
                        <span>Desplaza hacia abajo y pulsa en <strong>Añadir a pantalla de inicio</strong> ➕.</span>
                    </div>
                    <div class="ios-step-item">
                        <span class="ios-step-badge">3</span>
                        <span>Toca <strong>Añadir</strong> en la esquina superior derecha.</span>
                    </div>
                </div>
                <div class="ios-install-footer">
                    <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;" onclick="closeIosInstallModal()">
                        Entendido
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /.app-shell -->

    {{-- Scripts del layout principal (sidebar, dropdowns, PWA, custom selects) --}}
    @include('layouts.partials._scripts')
    @yield('scripts')
</body>
</html>
