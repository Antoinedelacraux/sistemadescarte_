<script>
(function() {
    'use strict';

    /* --- Estado sidebar --- */
    var SIDEBAR_KEY   = 'fundo_sidebar_state';
    var sidebar       = document.getElementById('main-sidebar');
    var mainWrapper   = document.getElementById('main-wrapper');
    var overlay       = document.getElementById('sidebar-overlay');
    var toggleBtn     = document.getElementById('sidebar-toggle');
    var isMobile      = function() { return window.innerWidth <= 1024; };

    function getSavedState() {
        try { return localStorage.getItem(SIDEBAR_KEY); } catch(e) { return null; }
    }

    function saveState(s) {
        try { localStorage.setItem(SIDEBAR_KEY, s); } catch(e) {}
    }

    function applyDesktopState() {
        if (!sidebar) return;
        var state = getSavedState();
        if (state === 'hidden') {
            sidebar.classList.add('hidden');
            if (mainWrapper) mainWrapper.classList.add('sidebar-hidden');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
        } else {
            sidebar.classList.remove('hidden');
            if (mainWrapper) mainWrapper.classList.remove('sidebar-hidden');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
        }
    }

    function toggleSidebar() {
        if (!sidebar) return;
        if (isMobile()) {
            var isOpen = sidebar.classList.contains('mobile-open');
            sidebar.classList.toggle('mobile-open', !isOpen);
            if (overlay) overlay.classList.toggle('active', !isOpen);
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(!isOpen));
        } else {
            var isHidden = sidebar.classList.contains('hidden');
            sidebar.classList.toggle('hidden', !isHidden);
            if (mainWrapper) mainWrapper.classList.toggle('sidebar-hidden', !isHidden);
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', String(isHidden));
            saveState(!isHidden ? 'hidden' : 'visible');
        }
    }

    // Exponer globalmente
    window.toggleSidebar = toggleSidebar;

    // Inicializar estado
    if (sidebar && !isMobile()) {
        applyDesktopState();
    }

    // Cerrar sidebar móvil al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            applyDesktopState();
        }
    });

    /* --- Indicador de red --- */
    var netIndicator = document.getElementById('net-indicator');
    var netLabel     = document.getElementById('net-label');

    function updateNetStatus() {
        if (!netIndicator || !netLabel) return;
        if (navigator.onLine) {
            netIndicator.className = 'net-indicator online';
            netIndicator.setAttribute('aria-label', 'Estado de conexión: en línea');
            netLabel.textContent = 'En línea';
        } else {
            netIndicator.className = 'net-indicator offline';
            netIndicator.setAttribute('aria-label', 'Estado de conexión: sin conexión');
            netLabel.textContent = 'Sin conexión';
        }
    }

    window.addEventListener('online',  updateNetStatus);
    window.addEventListener('offline', updateNetStatus);
    updateNetStatus();

    /* --- Menú de usuario (dropdown) --- */
    var userMenu = document.getElementById('header-user-menu');

    window.toggleUserMenu = function() {
        if (!userMenu) return;
        var isOpen = userMenu.classList.contains('open');
        userMenu.classList.toggle('open', !isOpen);
        userMenu.setAttribute('aria-expanded', String(!isOpen));
    };

    document.addEventListener('click', function(e) {
        if (userMenu && !userMenu.contains(e.target)) {
            userMenu.classList.remove('open');
            userMenu.setAttribute('aria-expanded', 'false');
        }
    });

    /* --- Cerrar con teclado (Escape) --- */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (userMenu) {
                userMenu.classList.remove('open');
                userMenu.setAttribute('aria-expanded', 'false');
            }
            if (isMobile() && sidebar && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('active');
            }
        }
    });

    /* --- Registro de Service Worker (PWA) --- */
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').catch(function(err) {
                console.log('SW registration notice:', err);
            });
        });
    }

    /* ============================================================
     * MOTOR DE LISTAS DESPLEGABLES CONTROLADAS (CUSTOM SELECT)
     * Control estricto de altura (max-height: 200px) y scroll fluido
     * ============================================================ */
    window.initCustomSelect = function(nativeSelect) {
        if (!nativeSelect || nativeSelect.tagName !== 'SELECT' || nativeSelect._customSelectInitialized) return;

        // Contenedor principal
        var wrap = document.createElement('div');
        wrap.className = 'custom-select-wrap';
        nativeSelect.parentNode.insertBefore(wrap, nativeSelect);
        wrap.appendChild(nativeSelect);
        nativeSelect.classList.add('custom-select-native');

        // Botón disparador (Trigger)
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        var label = document.createElement('span');
        label.className = 'custom-select-label';

        var arrow = document.createElement('span');
        arrow.className = 'custom-select-arrow';
        arrow.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';

        trigger.appendChild(label);
        trigger.appendChild(arrow);
        wrap.appendChild(trigger);

        // Panel desplegable
        var dropdown = document.createElement('div');
        dropdown.className = 'custom-select-dropdown';
        dropdown.setAttribute('role', 'listbox');

        // Buscador interno para listas con más de 6 opciones (ej. lotes)
        var searchWrap = document.createElement('div');
        searchWrap.className = 'custom-select-search-wrap';
        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = 'custom-select-search-input';
        searchInput.placeholder = 'Buscar opción...';
        searchInput.autocomplete = 'off';
        searchInput.setAttribute('aria-label', 'Filtrar opciones');
        searchWrap.appendChild(searchInput);
        dropdown.appendChild(searchWrap);

        // Contenedor deslizable de opciones
        var optionsList = document.createElement('div');
        optionsList.className = 'custom-select-options-list';
        dropdown.appendChild(optionsList);
        wrap.appendChild(dropdown);

        function syncLabel() {
            var selectedOpt = nativeSelect.options[nativeSelect.selectedIndex];
            if (selectedOpt) {
                label.textContent = selectedOpt.textContent.trim();
                if (nativeSelect.value === '' && selectedOpt.textContent.trim().startsWith('--')) {
                    label.style.color = 'var(--txt-muted)';
                } else {
                    label.style.color = 'var(--txt-primary)';
                }
            } else {
                label.textContent = '-- Seleccionar --';
                label.style.color = 'var(--txt-muted)';
            }
        }

        function renderOptions() {
            optionsList.innerHTML = '';
            var count = nativeSelect.options.length;

            // Mostrar buscador si hay más de 6 opciones
            if (count > 6) {
                searchWrap.style.display = 'block';
                searchInput.value = '';
            } else {
                searchWrap.style.display = 'none';
            }

            Array.from(nativeSelect.options).forEach(function(opt, index) {
                var item = document.createElement('div');
                item.className = 'custom-select-option';
                item.setAttribute('role', 'option');
                item.setAttribute('data-value', opt.value);
                item.setAttribute('data-index', String(index));

                if (opt.selected) {
                    item.classList.add('is-selected');
                    item.setAttribute('aria-selected', 'true');
                } else {
                    item.setAttribute('aria-selected', 'false');
                }

                var textSpan = document.createElement('span');
                textSpan.textContent = opt.textContent.trim();

                var checkIcon = document.createElement('span');
                checkIcon.className = 'custom-select-option-check';
                checkIcon.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';

                item.appendChild(textSpan);
                item.appendChild(checkIcon);

                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    nativeSelect.selectedIndex = index;
                    nativeSelect.value = opt.value;
                    nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    syncLabel();
                    closeDropdown();
                    trigger.focus();
                });

                optionsList.appendChild(item);
            });

            syncLabel();
        }

        function openDropdown() {
            // Cerrar cualquier otro dropdown abierto en la página
            document.querySelectorAll('.custom-select-wrap.is-open').forEach(function(other) {
                if (other !== wrap) other.classList.remove('is-open');
            });

            var rect = wrap.getBoundingClientRect();
            var spaceBelow = window.innerHeight - rect.bottom;
            var spaceAbove = rect.top;

            // Solo si no hay espacio abajo (menos de 220px) y arriba hay más espacio, abrir hacia arriba con max-height controlado
            if (spaceBelow < 220 && spaceAbove > spaceBelow) {
                wrap.classList.add('open-up');
            } else {
                wrap.classList.remove('open-up');
            }

            wrap.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');

            if (nativeSelect.options.length > 6) {
                setTimeout(function() { searchInput.focus(); }, 60);
            }

            // Desplazarse al elemento seleccionado para fácil localización
            var selectedItem = optionsList.querySelector('.is-selected');
            if (selectedItem) {
                optionsList.scrollTop = selectedItem.offsetTop - optionsList.offsetTop - 30;
            }
        }

        function closeDropdown() {
            wrap.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (wrap.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        // Filtrado dinámico en tiempo real
        searchInput.addEventListener('input', function() {
            var query = searchInput.value.toLowerCase().trim();
            var items = optionsList.querySelectorAll('.custom-select-option');
            var anyVisible = false;

            items.forEach(function(item) {
                var match = item.textContent.toLowerCase().includes(query);
                item.style.display = match ? 'flex' : 'none';
                if (match) anyVisible = true;
            });

            var existingEmpty = optionsList.querySelector('.custom-select-empty');
            if (!anyVisible) {
                if (!existingEmpty) {
                    var empty = document.createElement('div');
                    empty.className = 'custom-select-empty';
                    empty.textContent = 'No se encontraron resultados';
                    optionsList.appendChild(empty);
                }
            } else if (existingEmpty) {
                existingEmpty.remove();
            }
        });

        // Teclas de accesibilidad
        wrap.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            } else if (e.key === 'ArrowDown' && !wrap.classList.contains('is-open')) {
                e.preventDefault();
                openDropdown();
            }
        });

        // Cerrar al hacer clic fuera del componente
        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) {
                closeDropdown();
            }
        });

        // Método expuesto para refrescar opciones dinámicas (AJAX / DOM)
        nativeSelect.refreshCustomSelect = function() {
            renderOptions();
        };

        renderOptions();
        nativeSelect._customSelectInitialized = true;
    };

    // Auto-inicializar selects declarados con atributo data-custom-select
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('select[data-custom-select]').forEach(function(sel) {
            window.initCustomSelect(sel);
        });
    });

})();
</script>
