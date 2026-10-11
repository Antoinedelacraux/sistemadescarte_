# Capa Frontend — Sistema Fundo

La arquitectura aprobada para el proyecto consolidó la interfaz web en **`apps/web/`** bajo un enfoque monolítico modular con Laravel 12, plantillas Blade modulares y CSS nativo con Design Tokens (sin Tailwind ni frameworks pesados).

- **Plantilla Maestra y Parciales:** `apps/web/resources/views/layouts/` (`partials/_header.blade.php`, `_sidebar.blade.php`, `_styles.blade.php`, `_scripts.blade.php`)
- **Vistas y Componentes UI:** `apps/web/resources/views/ventas/` (`_form.blade.php`, `_filtros.blade.php`, `_modal_detalle.blade.php`) y `apps/web/resources/views/dashboard/`
- **Activos Públicos y PWA:** `apps/web/public/` (`manifest.json`, `sw.js`, `icons/`, `images/`)
- **Estilos y Tokens:** `apps/web/resources/css/app.css` y `apps/web/resources/views/layouts/partials/_styles.blade.php`
- **Documentación de Diseño:** [docs/03-diseno/GUIA_UI_UX.md](file:///docs/03-diseno/GUIA_UI_UX.md) y [docs/03-diseno/WIREFRAMES.md](file:///docs/03-diseno/WIREFRAMES.md)
