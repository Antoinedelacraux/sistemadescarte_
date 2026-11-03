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
                if (e.name === 'QuotaExceededError' || e.name === 'NS_ERROR_DOM_QUOTA_REACHED' || e.code === 22) {
                    showToast('El almacenamiento local del navegador está lleno. Conéctate a internet para sincronizar los registros pendientes y liberar memoria.', 'danger', 9000, 'Memoria Saturada');
                }
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

        function showToast(message, type, duration, title) {
            type = type || 'success';
            duration = duration || 5000;
            var container = document.getElementById('fundo-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'fundo-toast-container';
                container.className = 'fundo-toast-container';
                document.body.appendChild(container);
            }

            // Sanitizar y limpiar cualquier emoji repetido que venga al inicio del texto
            var cleanMessage = (message || '').replace(/^([\uD800-\uDBFF][\uDC00-\uDFFF]|[\u2600-\u27BF]|[\uE000-\uF8FF]|✅|⚠️|❌|ℹ️|\s)+/gu, '').trim();
            if (!cleanMessage) cleanMessage = message || '';

            // Título amigable por defecto según el tipo si no se especificó
            if (!title) {
                if (type === 'success') title = '¡Operación Exitosa!';
                else if (type === 'warning') title = 'Aviso del Sistema';
                else if (type === 'danger') title = 'Atención';
                else title = 'Información';
            }

            // SVGs vectoriales nítidos y modernos
            var svgIcon = '';
            if (type === 'success') {
                svgIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
            } else if (type === 'warning') {
                svgIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            } else if (type === 'danger') {
                svgIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
            } else {
                svgIcon = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
            }

            var toast = document.createElement('div');
            toast.className = 'fundo-toast fundo-toast-' + type;
            toast.setAttribute('role', 'alert');
            toast.innerHTML = 
                '<div class="fundo-toast-icon-wrap fundo-toast-icon-' + type + '">' + svgIcon + '</div>' +
                '<div class="fundo-toast-body">' +
                    '<div class="fundo-toast-title">' + title + '</div>' +
                    '<div class="fundo-toast-message">' + cleanMessage + '</div>' +
                '</div>' +
                '<button type="button" class="fundo-toast-close" title="Cerrar notificación" aria-label="Cerrar">' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                '</button>' +
                '<div class="fundo-toast-progress" style="animation-duration:' + duration + 'ms;"></div>';

            // Cierre manual instantáneo con animación
            var closeBtn = toast.querySelector('.fundo-toast-close');
            var dismissTimeout = null;
            function dismissToast() {
                if (dismissTimeout) clearTimeout(dismissTimeout);
                toast.classList.remove('show');
                toast.classList.add('hiding');
                setTimeout(function() {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 260);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dismissToast();
                });
            }

            container.appendChild(toast);

            requestAnimationFrame(function() {
                toast.classList.add('show');
            });

            dismissTimeout = setTimeout(dismissToast, duration);
        }

        async function syncNow() {
            if (isSyncing) return;
            var queue = getQueue();
            if (queue.length === 0) {
                return;
            }

            if (!navigator.onLine) {
                showToast('Aún sin conexión para sincronizar los ' + queue.length + ' registros pendientes.', 'warning', 4000, 'Sin Conexión');
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
                    } else if (response.status === 409) {
                        // El registro ya existía en el servidor (confirmación idempotente)
                        syncedCount++;
                    } else if (response.status === 419) {
                        console.warn('[Offline] Token CSRF caducado.');
                        showToast('Tu sesión de seguridad expiró mientras estabas sin conexión. Recarga la página con internet para renovar el acceso y sincronizar.', 'warning', 8000, 'Sesión Expirada');
                        remainingQueue.push(item);
                        break;
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
                showToast('Se sincronizaron ' + syncedCount + ' venta(s) correctamente con el servidor.', 'success', 5000, '¡Sincronización Exitosa!');
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

    function showPwaNotificationTestMessage(message, type, title) {
        if (window.FundoOffline && typeof window.FundoOffline.showToast === 'function') {
            window.FundoOffline.showToast(message, type, 5000, title);
            return;
        }
        window.alert(message);
    }

    // Prueba manual disponible solo en desarrollo: no registra suscripciones ni envía datos al servidor.
    window.triggerPwaNotificationTest = async function() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            showPwaNotificationTestMessage('Este navegador no permite notificaciones para la aplicación.', 'warning', 'Notificación no disponible');
            return;
        }

        var permission = Notification.permission;
        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }

        if (permission !== 'granted') {
            showPwaNotificationTestMessage('No se concedió el permiso. Actívalo en los permisos del sitio para volver a probar.', 'warning', 'Permiso pendiente');
            return;
        }

        try {
            var registration = await navigator.serviceWorker.ready;
            await registration.showNotification('TALSA Fundo', {
                body: 'Notificación de prueba recibida correctamente.',
                icon: '/icons/icon-192.png',
                badge: '/icons/icon-192.png',
                tag: 'fundo-pwa-notification-test',
                renotify: true,
                data: { url: '/' }
            });
            showPwaNotificationTestMessage('La notificación fue enviada a este dispositivo.', 'success', 'Prueba enviada');
        } catch (error) {
            console.warn('[PWA] No se pudo mostrar la notificación de prueba:', error);
            showPwaNotificationTestMessage('No se pudo mostrar la notificación. Verifica que la PWA esté instalada y que Chrome tenga permiso.', 'danger', 'Prueba no enviada');
        }
    };

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

    // Limpieza de páginas HTML en caché al cerrar sesión para garantizar que el siguiente usuario
    // vea su rol y módulos de forma instantánea y real sin arrastrar vistas de la sesión anterior
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form && form.action && form.action.includes('/logout')) {
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    keys.forEach(function(key) {
                        caches.open(key).then(function(c) {
                            c.delete('/');
                            c.delete('/ventas/registrar');
                            c.delete('/ventas');
                        });
                    });
                });
            }
        }
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
     * AVISOS DE GARITA: CONTADOR Y NOTIFICACIONES MIENTRAS LA PWA ESTÁ ACTIVA
     * ============================================================ */
    var garitaBadge = document.getElementById('garita-bell-count');
    var garitaNotificationsButton = document.getElementById('btn-garita-notifications');
    if (garitaNotificationsButton && garitaBadge) {
        // Cada cuenta Garita conserva sus propios viajes vistos aunque varias
        // personas utilicen el mismo dispositivo.
        var garitaNotificationUserId = garitaNotificationsButton
            ? garitaNotificationsButton.dataset.userId
            : 'unknown';
        var GARITA_PENDING_SEEN_KEY = 'fundo_garita_pending_seen_v1_' + garitaNotificationUserId;
        var GARITA_PENDING_READY_KEY = 'fundo_garita_pending_ready_v1_' + garitaNotificationUserId;
        var announceCurrentGaritaPendientes = false;

        function showGaritaToast(message, type, title) {
            if (window.FundoOffline && typeof window.FundoOffline.showToast === 'function') {
                window.FundoOffline.showToast(message, type, 5000, title);
                return;
            }
            window.alert(message);
        }

        function getSeenGaritaPendientes() {
            try {
                return JSON.parse(localStorage.getItem(GARITA_PENDING_SEEN_KEY) || '[]');
            } catch (error) {
                return [];
            }
        }

        function saveSeenGaritaPendientes(fingerprints) {
            try {
                localStorage.setItem(GARITA_PENDING_SEEN_KEY, JSON.stringify(fingerprints));
                localStorage.setItem(GARITA_PENDING_READY_KEY, '1');
            } catch (error) {
                // La aplicación conserva el contador si el navegador restringe almacenamiento local.
            }
        }

        function updateGaritaNotificationButton() {
            if (!garitaNotificationsButton || !('Notification' in window)) return;

            var active = Notification.permission === 'granted';
            garitaNotificationsButton.classList.toggle('is-active', active);
            garitaNotificationsButton.title = active
                ? 'Avisos de Garita activados'
                : 'Activar avisos de salidas pendientes';
            garitaNotificationsButton.setAttribute('aria-label', garitaNotificationsButton.title);
            var label = garitaNotificationsButton.querySelector('.garita-notifications-text');
            if (label) label.textContent = active ? 'Avisos activos' : 'Activar avisos';
        }

        async function showGaritaNotification(title, body) {
            if (!('Notification' in window) || Notification.permission !== 'granted' || !('serviceWorker' in navigator)) {
                return;
            }

            try {
                var registration = await navigator.serviceWorker.ready;
                await registration.showNotification(title, {
                    body: body,
                    icon: '/icons/icon-192.png',
                    badge: '/icons/icon-192.png',
                    tag: 'garita-pendientes',
                    renotify: true,
                    data: { url: '/garita' }
                });
            } catch (error) {
                console.warn('[Garita] No se pudo mostrar la notificación:', error);
            }
        }

        function processGaritaPendientes(data) {
            var pendientes = Array.isArray(data.pendientes) ? data.pendientes : [];
            var fingerprints = pendientes.map(function(pendiente) { return pendiente.fingerprint; });
            var initialized = localStorage.getItem(GARITA_PENDING_READY_KEY) === '1';
            var seen = getSeenGaritaPendientes();

            if (!initialized) {
                saveSeenGaritaPendientes(fingerprints);
                if (announceCurrentGaritaPendientes && pendientes.length > 0) {
                    showGaritaNotification('Garita: salidas pendientes', 'Tienes ' + pendientes.length + ' salida(s) pendiente(s) de validación.');
                }
                announceCurrentGaritaPendientes = false;
                return;
            }

            var nuevos = pendientes.filter(function(pendiente) {
                return !seen.includes(pendiente.fingerprint);
            });
            saveSeenGaritaPendientes(fingerprints);

            if (nuevos.length === 1) {
                var pendiente = nuevos[0];
                showGaritaNotification('Nueva salida pendiente', 'Viaje N° ' + pendiente.viaje + ' · ' + pendiente.placa + ' · ' + pendiente.fundo);
            } else if (nuevos.length > 1) {
                showGaritaNotification('Nuevas salidas pendientes', 'Tienes ' + nuevos.length + ' nuevas salidas por validar en Garita.');
            }
        }

        function checkGaritaPendientes() {
            if (!navigator.onLine) return;
            fetch('/api/garita/pendientes-count', {
                headers: { 'Accept': 'application/json' }
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && typeof data.count === 'number') {
                    if (data.count > 0) {
                        garitaBadge.textContent = data.count > 99 ? '99+' : data.count;
                        garitaBadge.style.display = 'flex';
                    } else {
                        garitaBadge.style.display = 'none';
                    }
                    processGaritaPendientes(data);
                }
            })
            .catch(function(err) {
                // silencioso
            });
        }

        // Consultar de inmediato y luego cada 30 segundos
        checkGaritaPendientes();
        setInterval(checkGaritaPendientes, 30000);
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') checkGaritaPendientes();
        });

        window.activarAvisosGarita = async function() {
            if (!('Notification' in window) || !('serviceWorker' in navigator)) {
                showGaritaToast('Este navegador no permite avisos para la PWA.', 'warning', 'Avisos no disponibles');
                return;
            }

            var permission = Notification.permission;
            if (permission === 'default') permission = await Notification.requestPermission();

            updateGaritaNotificationButton();
            if (permission !== 'granted') {
                showGaritaToast('No se concedió el permiso. Actívalo en los permisos del sitio para recibir avisos.', 'warning', 'Permiso pendiente');
                return;
            }

            announceCurrentGaritaPendientes = true;
            try {
                localStorage.removeItem(GARITA_PENDING_READY_KEY);
                checkGaritaPendientes();
                showGaritaToast('Los avisos de nuevas salidas pendientes están activos mientras uses la PWA.', 'success', 'Avisos activados');
            } catch (error) {
                showGaritaToast('No se pudieron activar los avisos.', 'danger', 'Error de avisos');
            }
        };
        updateGaritaNotificationButton();
    }

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
