{{-- Modal para Visualizar Registro de Venta con Detalle Completo --}}
<div id="modal-detalle-venta" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-venta-title" onclick="cerrarModalSiClickFondo(event)">
    <div class="modal-sheet" role="document" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div style="min-width: 0;">
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <h2 id="modal-venta-title" class="modal-title">Detalle del Pesaje</h2>
                    <span id="modal-badge-motivo" class="badge-motivo"></span>
                </div>
                <p id="modal-subtitulo" class="modal-subtitle"></p>
            </div>
            <button type="button" class="modal-close-btn" onclick="cerrarModalDetalle()" aria-label="Cerrar ventana">&times;</button>
        </div>

        <div class="modal-body">
            {{-- Banner Económico --}}
            <div class="modal-money-banner">
                <div class="modal-money-item">
                    <span class="modal-money-label">KILOGRAMOS</span>
                    <span id="modal-kilos" class="modal-money-val">0.00 kg</span>
                </div>
                <div class="modal-money-item">
                    <span class="modal-money-label">PRECIO / KG</span>
                    <span id="modal-precio" class="modal-money-val">S/ 0.00</span>
                </div>
                <div class="modal-money-total">
                    <span class="modal-money-label-light">TOTAL LIQUIDADO</span>
                    <span id="modal-total" class="modal-money-val-light">S/ 0.00</span>
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
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a id="modal-btn-full" href="#" class="btn btn-secondary btn-sm" title="Abrir ficha técnica completa">
                    Ver Ficha Completa
                </a>
                <a id="modal-btn-edit" href="#" class="btn btn-primary btn-sm" title="Editar este pesaje">
                    Editar Registro
                </a>
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
        if (d.urlEdit && d.estado !== 'anulado') {
            btnEdit.href = d.urlEdit;
            btnEdit.style.display = 'inline-flex';
        } else {
            btnEdit.style.display = 'none';
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
