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

    /* ============================================================
     * MOTOR OFFLINE-FIRST: COLA LOCAL Y SINCRONIZACIÓN AUTOMÁTICA
     * ============================================================ */
    window.FundoOffline = (function() {
        var STORAGE_KEY = 'fundo_offline_ventas';
        var isSyncing = false;

        function getQueue() {
            try {
                var raw = localStorage.getItem(STORAGE_KEY);
                return raw ? JSON.parse(raw) : [];
            } catch(e) {
                console.error('[Offline] Error al leer cola:', e);
                return [];
            }
        }

        function saveQueue(items) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            } catch(e) {
                console.error('[Offline] Error al guardar cola:', e);
            }
            updateBadge();
        }

        function enqueue(ventaData) {
            var queue = getQueue();
            if (!ventaData.id) {
                ventaData.id = generateUUID();
            }
            if (!ventaData.created_at_local) {
                ventaData.created_at_local = new Date().toISOString();
            }
            queue.push(ventaData);
            saveQueue(queue);
            return ventaData;
        }

        function updateBadge() {
            var count = getQueue().length;
            var btn = document.getElementById('btn-sync-pending');
            var countEl = document.getElementById('sync-pending-count');
            if (btn && countEl) {
                countEl.textContent = count;
                btn.style.display = count > 0 ? 'inline-flex' : 'none';
            }
        }

        function generateUUID() {
            if (typeof crypto !== 'undefined' && crypto.randomUUID) {
                return crypto.randomUUID();
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        function showToast(message, type, duration) {
            type = type || 'success';
            duration = duration || 5000;
            var container = document.getElementById('fundo-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'fundo-toast-container';
                container.className = 'fundo-toast-container';
                document.body.appendChild(container);
            }

            var toast = document.createElement('div');
            toast.className = 'fundo-toast fundo-toast-' + type;
            var icon = type === 'success' ? '✅' : (type === 'warning' ? '⚠️' : (type === 'danger' ? '❌' : 'ℹ️'));
            toast.innerHTML = '<span aria-hidden="true" style="font-size:1.1rem;flex-shrink:0;">' + icon + '</span><span>' + message + '</span>';
            container.appendChild(toast);

            requestAnimationFrame(function() {
                toast.classList.add('show');
            });

            setTimeout(function() {
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }, duration);
        }

        async function syncNow() {
            if (isSyncing) return;
            var queue = getQueue();
            if (queue.length === 0) {
                return;
            }

            if (!navigator.onLine) {
                showToast('Aún sin conexión para sincronizar los ' + queue.length + ' registros pendientes.', 'warning', 4000);
                return;
            }

            isSyncing = true;
            var btn = document.getElementById('btn-sync-pending');
            if (btn) btn.classList.add('syncing');

            var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_token"]')?.value;

            var syncedCount = 0;
            var remainingQueue = [];

            for (var i = 0; i < queue.length; i++) {
                var item = queue[i];
                try {
                    var response = await fetch('/ventas', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        body: JSON.stringify(item)
                    });

                    if (response.status === 201 || response.status === 200) {
                        syncedCount++;
                    } else if (response.status === 422) {
                        var errData = await response.json();
                        console.error('[Offline] Error validando venta:', errData);
                        remainingQueue.push(item);
                    } else {
                        remainingQueue.push(item);
                        break;
                    }
                } catch (err) {
                    console.error('[Offline] Error de red durante sync:', err);
                    remainingQueue.push(item);
                    break;
                }
            }

            saveQueue(remainingQueue);
            isSyncing = false;
            if (btn) btn.classList.remove('syncing');

            if (syncedCount > 0) {
                showToast('¡Se sincronizaron ' + syncedCount + ' venta(s) correctamente con el servidor!', 'success', 5000);
                if (window.location.pathname === '/ventas' || window.location.pathname === '/') {
                    setTimeout(function() { window.location.reload(); }, 1200);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateBadge();
            if (navigator.onLine && getQueue().length > 0) {
                setTimeout(syncNow, 1200);
            }
        });

        window.addEventListener('online', function() {
            setTimeout(syncNow, 600);
        });

        setInterval(function() {
            if (navigator.onLine && getQueue().length > 0) {
                syncNow();
            }
        }, 60000);

        return {
            getQueue: getQueue,
            saveQueue: saveQueue,
            enqueue: enqueue,
            updateBadge: updateBadge,
            generateUUID: generateUUID,
            showToast: showToast,
            syncNow: syncNow
        };
    })();

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

    /* ============================================================
     * GESTOR PWA: SERVICE WORKER & INSTALACIÓN DESCARGABLE
     * ============================================================ */
    var deferredPrompt = null;
    var pwaInstallBtn  = document.getElementById('btn-pwa-install');
    var iosModal       = document.getElementById('ios-install-modal');

    var isRunningStandalone = (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) ||
                              (window.navigator.standalone === true);

    var isIosDevice = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

    // Registro del Service Worker v3
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').then(function(reg) {
                // Verificar actualizaciones disponibles
                reg.onupdatefound = function() {
                    var installingWorker = reg.installing;
                    if (installingWorker) {
                        installingWorker.onstatechange = function() {
                            if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                console.log('[PWA] Nueva versión disponible en fondo.');
                            }
                        };
                    }
                };
            }).catch(function(err) {
                console.warn('[PWA] Service Worker registration notice:', err);
            });
        });
    }

    // Captura del evento estándar de instalación (Android / Chrome / Edge / Desktop)
    window.addEventListener('beforeinstallprompt', function(e) {
        // Prevenir el banner automático intrusivo por defecto
        e.preventDefault();
        deferredPrompt = e;

        // Mostrar botón amigable si no está en modo standalone ya instalada
        if (!isRunningStandalone && pwaInstallBtn) {
            pwaInstallBtn.style.display = 'inline-flex';
        }
    });

    // En iOS Safari no existe beforeinstallprompt; mostrar botón si no está en standalone
    if (isIosDevice && !isRunningStandalone && pwaInstallBtn) {
        pwaInstallBtn.style.display = 'inline-flex';
    }

    // Disparador de instalación
    window.triggerPwaInstall = function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'accepted') {
                    if (pwaInstallBtn) pwaInstallBtn.style.display = 'none';
                }
                deferredPrompt = null;
            });
        } else if (isIosDevice) {
            // Mostrar modal con pasos para iOS
            if (iosModal) {
                iosModal.style.display = 'flex';
            }
        } else {
            // Fallback para otros navegadores
            alert('Para instalar esta aplicación, utiliza el menú de tu navegador (los tres puntos o botón de configuración) y selecciona "Instalar aplicación" o "Añadir a pantalla de inicio".');
        }
    };

    window.closeIosInstallModal = function() {
        if (iosModal) {
            iosModal.style.display = 'none';
        }
    };

    // Confirmación al completarse la instalación
    window.addEventListener('appinstalled', function() {
        console.log('[PWA] TALSA Fundo instalada exitosamente.');
        if (pwaInstallBtn) {
            pwaInstallBtn.style.display = 'none';
        }
        deferredPrompt = null;
    });

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

    /* ============================================================
     * PREFETCH UNIVERSAL DE MÓDULOS (INSTANT NAVIGATION)
     * Anticipa la navegación al contacto táctil o hover del cursor
     * ============================================================ */
    var prefetchedUrls = new Set();
    function prefetchUrl(url) {
        if (!url || prefetchedUrls.has(url)) return;
        if (!url.startsWith('/') || url.startsWith('//') || url.includes('/logout') || url.includes('/exportar') || url.includes('/anular') || url.includes('/toggle')) return;
        prefetchedUrls.add(url);

        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    document.addEventListener('pointerover', function(e) {
        var anchor = e.target.closest('a[href]');
        if (anchor && anchor.origin === window.location.origin) {
            prefetchUrl(anchor.getAttribute('href'));
        }
    }, { passive: true });

    document.addEventListener('touchstart', function(e) {
        var anchor = e.target.closest('a[href]');
        if (anchor && anchor.origin === window.location.origin) {
            prefetchUrl(anchor.getAttribute('href'));
        }
    }, { passive: true });

})();
</script>
