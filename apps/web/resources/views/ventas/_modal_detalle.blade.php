{{-- Modal para Visualizar Registro de Venta con Detalle Completo --}}
<div id="modal-detalle-venta" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-venta-title" onclick="cerrarModalSiClickFondo(event)">
    <div class="modal-sheet" role="document" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="min-width: 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <h2 id="modal-venta-title" class="modal-title">Detalle de Venta</h2>
                    <span id="modal-badge-motivo" class="badge-motivo"></span>
                </div>
                <p id="modal-subtitulo" class="modal-subtitle"></p>
            </div>
            <button type="button" class="modal-close-btn" onclick="cerrarModalDetalle()" aria-label="Cerrar ventana">&times;</button>
        </div>

        <div class="modal-body">
            {{-- Resumen Económico: 3 Tarjetas Inteligentes (3 columnas en Desktop, 2+1 en Móvil) --}}
            <div class="modal-money-grid">
                <div class="modal-stat-box modal-stat-kilos">
                    <span class="modal-stat-label">KILOGRAMOS</span>
                    <span id="modal-kilos" class="modal-stat-val">0.00 kg</span>
                </div>

                <div class="modal-stat-box modal-stat-precio">
                    <span class="modal-stat-label">PRECIO / KG</span>
                    <span id="modal-precio" class="modal-stat-val">S/ 0.00</span>
                </div>

                <div class="modal-stat-box modal-stat-total">
                    <div class="modal-stat-total-meta">
                        <span class="modal-stat-label-light">TOTAL LIQUIDADO</span>
                        <span class="modal-stat-sublabel-light">Importe calculado</span>
                    </div>
                    <span id="modal-total" class="modal-stat-val-light">S/ 0.00</span>
                </div>
            </div>

            {{-- Origen y Clasificación --}}
            <div class="modal-section-title">Origen y Clasificación</div>
            <div class="modal-data-grid">
                <div class="modal-data-item">
                    <span class="modal-data-label">Fundo</span>
                    <span id="modal-fundo" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Fecha de Producción</span>
                    <span id="modal-fecha" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Lote</span>
                    <span id="modal-lote" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Cuartel</span>
                    <span id="modal-cuartel" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Motivo</span>
                    <span id="modal-motivo" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Tipo de Descarte</span>
                    <span id="modal-tipo" class="modal-data-value">—</span>
                </div>
            </div>

            {{-- Cliente y Jabas --}}
            <div class="modal-section-title" style="margin-top: 1rem;">Cliente y Jabas</div>
            <div class="modal-data-grid">
                <div class="modal-data-item">
                    <span class="modal-data-label">Cliente / Comprador</span>
                    <span id="modal-cliente" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">RUC</span>
                    <span id="modal-ruc" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Jabas Registradas</span>
                    <span id="modal-jabas" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Peso Promedio / Jaba</span>
                    <span id="modal-peso-jaba" class="modal-data-value">—</span>
                </div>
            </div>

            {{-- Guía y Transporte --}}
            <div class="modal-section-title" style="margin-top: 1rem;">Guía y Transporte</div>
            <div class="modal-data-grid">
                <div class="modal-data-item">
                    <span class="modal-data-label">Placa de Vehículo</span>
                    <span id="modal-placa" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Conductor</span>
                    <span id="modal-conductor" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">Brevete</span>
                    <span id="modal-brevete" class="modal-data-value">—</span>
                </div>
                <div class="modal-data-item">
                    <span class="modal-data-label">N° de Viaje</span>
                    <span id="modal-viaje" class="modal-data-value">—</span>
                </div>
            </div>

            {{-- Observaciones --}}
            <div class="modal-section-title" style="margin-top: 1rem;">Observaciones</div>
            <div class="modal-obs-box" id="modal-observacion">Sin observaciones registradas.</div>

            {{-- Auditoría --}}
            <div class="modal-audit-bar">
                <div>Registrado por: <strong id="modal-creado-por">—</strong> (<span id="modal-creado-at">—</span>)</div>
                <div id="modal-wrap-updated" style="display: none;">Editado por: <strong id="modal-actualizado-por">—</strong> (<span id="modal-actualizado-at">—</span>)</div>
            </div>
        </div>

        <div class="modal-footer">
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <a id="modal-btn-full" href="#" class="btn btn-secondary btn-sm" title="Abrir ficha técnica completa">
                    Ver Ficha Completa
                </a>
                <a id="modal-btn-edit" href="#" class="btn btn-primary btn-sm" title="Editar este pesaje">
                    Editar Registro
                </a>
                <span id="modal-badge-cerrado" style="display: none; font-size: 0.75rem; color: #64748b; font-weight: 700; align-items: center; gap: 0.35rem; padding: 0.35rem 0.65rem; background: #f1f5f9; border-radius: var(--radius-full); border: 1px solid #cbd5e1;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Cerrado (&gt;24h)</span>
                </span>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" onclick="cerrarModalDetalle()">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('modal-detalle-venta');

    window.abrirModalDetalle = function(btn) {
        var d = btn.dataset;

        document.getElementById('modal-fundo').textContent = d.fundo || '—';
        document.getElementById('modal-fecha').textContent = d.fecha || '—';
        document.getElementById('modal-subtitulo').textContent = (d.fundo || 'Fundo') + ' • ' + (d.fecha || '—');

        var badgeMotivo = document.getElementById('modal-badge-motivo');
        badgeMotivo.textContent = d.motivo || '';
        badgeMotivo.className = 'badge-motivo';
        if (d.motivo === 'Campo') {
            badgeMotivo.classList.add('badge-campo');
        } else if (d.motivo === 'Packing') {
            badgeMotivo.classList.add('badge-packing');
        } else {
            badgeMotivo.classList.add('badge-cosecha');
        }

        document.getElementById('modal-kilos').textContent = (d.kilos || '0.00') + ' kg';
        document.getElementById('modal-precio').textContent = 'S/ ' + (d.precio || '0.00');
        document.getElementById('modal-total').textContent = 'S/ ' + (d.total || '0.00');

        document.getElementById('modal-cliente').textContent = d.cliente || '—';
        document.getElementById('modal-ruc').textContent = d.ruc || '—';
        document.getElementById('modal-lote').textContent = d.lote || '—';
        document.getElementById('modal-cuartel').textContent = d.cuartel || '—';
        document.getElementById('modal-motivo').textContent = d.motivo || '—';
        document.getElementById('modal-tipo').textContent = d.tipo || '—';

        document.getElementById('modal-jabas').textContent = d.jabas || '—';
        document.getElementById('modal-peso-jaba').textContent = d.pesoJaba && d.pesoJaba !== '—' ? (d.pesoJaba + ' kg') : '—';

        document.getElementById('modal-placa').textContent = d.placa || '—';
        document.getElementById('modal-conductor').textContent = d.conductor || '—';
        document.getElementById('modal-brevete').textContent = d.brevete || '—';
        document.getElementById('modal-viaje').textContent = d.viaje || '—';

        var obsEl = document.getElementById('modal-observacion');
        if (d.observacion && d.observacion.trim().length > 0) {
            obsEl.textContent = d.observacion;
            obsEl.style.fontStyle = 'normal';
        } else {
            obsEl.textContent = 'Sin observaciones registradas.';
            obsEl.style.fontStyle = 'italic';
        }

        document.getElementById('modal-creado-por').textContent = d.creadoPor || '—';
        document.getElementById('modal-creado-at').textContent = d.creadoAt || '—';

        var wrapUpdated = document.getElementById('modal-wrap-updated');
        if (d.actualizadoPor && d.actualizadoAt) {
            document.getElementById('modal-actualizado-por').textContent = d.actualizadoPor;
            document.getElementById('modal-actualizado-at').textContent = d.actualizadoAt;
            wrapUpdated.style.display = 'block';
        } else {
            wrapUpdated.style.display = 'none';
        }

        var btnFull = document.getElementById('modal-btn-full');
        if (d.urlShow) {
            btnFull.href = d.urlShow;
            btnFull.style.display = 'inline-flex';
        } else {
            btnFull.style.display = 'none';
        }

        var btnEdit = document.getElementById('modal-btn-edit');
        var badgeCerrado = document.getElementById('modal-badge-cerrado');
        if (d.urlEdit && d.estado !== 'anulado') {
            btnEdit.href = d.urlEdit;
            btnEdit.style.display = 'inline-flex';
            if (badgeCerrado) badgeCerrado.style.display = 'none';
        } else {
            btnEdit.style.display = 'none';
            if (badgeCerrado) {
                badgeCerrado.style.display = (d.cerrado === '1' && d.estado !== 'anulado') ? 'inline-flex' : 'none';
            }
        }

        if (modal) {
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }
    };

    window.cerrarModalDetalle = function() {
        if (modal) {
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    };

    window.cerrarModalSiClickFondo = function(event) {
        if (event.target === modal) {
            window.cerrarModalDetalle();
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            window.cerrarModalDetalle();
        }
    });
})();
</script>

<style>
    .modal-money-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 0.875rem !important;
        margin-bottom: 1.25rem !important;
    }

    .modal-stat-box {
        border-radius: var(--radius-lg, 0.75rem) !important;
        padding: 0.875rem 1rem !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        gap: 0.25rem !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .modal-stat-kilos {
        background: linear-gradient(145deg, #f0fdf4 0%, #ecfdf5 100%) !important;
        border: 1.5px solid #86efac !important;
    }

    .modal-stat-precio {
        background: linear-gradient(145deg, #f8fafc 0%, #f1f5f9 100%) !important;
        border: 1.5px solid #e2e8f0 !important;
    }

    .modal-stat-label {
        font-size: 0.625rem !important;
        font-weight: 700 !important;
        color: #166534 !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        line-height: 1.1 !important;
    }

    .modal-stat-precio .modal-stat-label {
        color: var(--txt-muted, #64748b) !important;
    }

    .modal-stat-val {
        font-size: clamp(1.05rem, 2.2vw, 1.25rem) !important;
        font-weight: 800 !important;
        color: #14532d !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
    }

    .modal-stat-precio .modal-stat-val {
        color: var(--txt-primary, #0f172a) !important;
    }

    /* Tarjeta destacada de Total Liquidado */
    .modal-stat-total {
        background: linear-gradient(135deg, #14532d 0%, #166534 100%) !important;
        border: 1px solid #14532d !important;
        color: #ffffff !important;
        box-shadow: 0 2px 6px rgba(20, 83, 45, 0.15) !important;
    }

    .modal-stat-total-meta {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.1rem !important;
    }

    .modal-stat-label-light {
        font-size: 0.625rem !important;
        font-weight: 800 !important;
        color: #86efac !important;
        letter-spacing: 0.06em !important;
        text-transform: uppercase !important;
        line-height: 1.1 !important;
    }

    .modal-stat-sublabel-light {
        font-size: 0.5625rem !important;
        font-weight: 500 !important;
        color: #d1fae5 !important;
        opacity: 0.85 !important;
        line-height: 1.1 !important;
    }

    .modal-stat-val-light {
        font-size: clamp(1.15rem, 2.5vw, 1.4rem) !important;
        font-weight: 900 !important;
        color: #ffffff !important;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace !important;
        white-space: nowrap !important;
        line-height: 1.2 !important;
        letter-spacing: -0.01em !important;
    }

    /* En Móviles (< 640px): 2 columnas arriba + Total ancho completo abajo */
    @media (max-width: 640px) {
        .modal-money-grid {
            grid-template-columns: 1fr 1fr !important;
            gap: 0.625rem !important;
            margin-bottom: 1rem !important;
        }

        .modal-stat-box {
            padding: 0.75rem 0.875rem !important;
        }

        .modal-stat-total {
            grid-column: 1 / -1 !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 0.75rem !important;
            padding: 0.75rem 1rem !important;
        }

        .modal-stat-val {
            font-size: 1.05rem !important;
        }

        .modal-stat-val-light {
            font-size: 1.25rem !important;
        }
    }
</style>
