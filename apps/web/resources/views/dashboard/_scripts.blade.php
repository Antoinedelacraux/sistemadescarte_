{{-- Interactividad JavaScript para el Panel de Control --}}
<script>
var filtroMotivoActual = 'todos';

function toggleClientRows(slug) {
    var parentRow = document.getElementById('row-client-' + slug);
    var subrows = document.querySelectorAll('.subrow-' + slug);
    if (!parentRow) return;

    var isExpanded = parentRow.classList.contains('expanded');
    if (isExpanded) {
        parentRow.classList.remove('expanded');
        subrows.forEach(function(row) { row.style.display = 'none'; });
    } else {
        parentRow.classList.add('expanded');
        subrows.forEach(function(row) {
            var rowMotivo = row.getAttribute('data-motivo');
            if (filtroMotivoActual === 'todos' || filtroMotivoActual === rowMotivo) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

function expandAllClients(expand) {
    var parentRows = document.querySelectorAll('.client-parent-row');
    parentRows.forEach(function(pRow) {
        var id = pRow.id.replace('row-client-', '');
        var subrows = document.querySelectorAll('.subrow-' + id);
        if (expand) {
            pRow.classList.add('expanded');
            subrows.forEach(function(row) {
                var rowMotivo = row.getAttribute('data-motivo');
                if (filtroMotivoActual === 'todos' || filtroMotivoActual === rowMotivo) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        } else {
            pRow.classList.remove('expanded');
            subrows.forEach(function(row) { row.style.display = 'none'; });
        }
    });
}

function switchClientView(view) {
    var tableGrouped = document.getElementById('table-clients-grouped');
    var tableFlat = document.getElementById('table-clients-flat');
    var btnGrouped = document.getElementById('btn-view-grouped');
    var btnFlat = document.getElementById('btn-view-flat');

    if (view === 'grouped') {
        if (tableGrouped) tableGrouped.style.display = '';
        if (tableFlat) tableFlat.style.display = 'none';
        if (btnGrouped) btnGrouped.classList.add('active');
        if (btnFlat) btnFlat.classList.remove('active');
    } else {
        if (tableGrouped) tableGrouped.style.display = 'none';
        if (tableFlat) tableFlat.style.display = '';
        if (btnGrouped) btnGrouped.classList.remove('active');
        if (btnFlat) btnFlat.classList.add('active');
    }
    filtrarTablaClientes();
}

function setMotivoFilter(motivo, btn) {
    filtroMotivoActual = motivo;
    document.querySelectorAll('.chip-btn').forEach(function(b) { b.classList.remove('active'); });
    if (btn) btn.classList.add('active');
    filtrarTablaClientes();
}

function filtrarTablaClientes() {
    var q = (document.getElementById('client-search-input')?.value || '').toLowerCase().trim();

    var isGroupedActive = document.getElementById('table-clients-grouped')?.style.display !== 'none';

    if (isGroupedActive) {
        var parentRows = document.querySelectorAll('.client-parent-row');
        parentRows.forEach(function(pRow) {
            var slug = pRow.id.replace('row-client-', '');
            var clientName = pRow.getAttribute('data-client-name') || '';
            var clientMotivos = pRow.getAttribute('data-motivos') || '';
            var subrows = document.querySelectorAll('.subrow-' + slug);

            var matchesMotivo = filtroMotivoActual === 'todos' || clientMotivos.indexOf(filtroMotivoActual) !== -1;
            var matchesQuery = q === '' || clientName.indexOf(q) !== -1;

            var matchingSubrows = 0;
            subrows.forEach(function(sRow) {
                var sMotivo = sRow.getAttribute('data-motivo') || '';
                var sTipo = sRow.getAttribute('data-tipo') || '';
                var subMatchesMotivo = filtroMotivoActual === 'todos' || filtroMotivoActual === sMotivo;
                var subMatchesQuery = q === '' || clientName.indexOf(q) !== -1 || sTipo.indexOf(q) !== -1;

                if (subMatchesMotivo && subMatchesQuery) {
                    matchingSubrows++;
                    if (pRow.classList.contains('expanded')) {
                        sRow.style.display = '';
                    } else {
                        sRow.style.display = 'none';
                    }
                } else {
                    sRow.style.display = 'none';
                }
            });

            if ((matchesMotivo && matchesQuery) || matchingSubrows > 0) {
                pRow.style.display = '';
            } else {
                pRow.style.display = 'none';
            }
        });
    } else {
        var flatRows = document.querySelectorAll('.flat-client-row');
        flatRows.forEach(function(row) {
            var clientName = row.getAttribute('data-client-name') || '';
            var motivo = row.getAttribute('data-motivo') || '';
            var tipo = row.getAttribute('data-tipo') || '';

            var matchesMotivo = filtroMotivoActual === 'todos' || filtroMotivoActual === motivo;
            var matchesQuery = q === '' || clientName.indexOf(q) !== -1 || tipo.indexOf(q) !== -1 || motivo.toLowerCase().indexOf(q) !== -1;

            if (matchesMotivo && matchesQuery) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}

function exportarClientesExcel() {
    var q = (document.getElementById('client-search-input')?.value || '').trim();
    var motivo = typeof filtroMotivoActual !== 'undefined' ? filtroMotivoActual : 'todos';
    var url = "{{ route('dashboard.exportar') }}?tabla=clientes";
    if (motivo && motivo !== 'todos') {
        url += '&motivo=' + encodeURIComponent(motivo);
    }
    if (q !== '') {
        url += '&buscar=' + encodeURIComponent(q);
    }
    window.location.href = url;
}

function cambiarVistaTabla(vista, btn) {
    document.querySelectorAll('.view-pill').forEach(function(b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
    });
    if (btn) {
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
    }

    var secMotivos = document.getElementById('sec-motivos-container');
    var secClientes = document.getElementById('sec-clientes-container');
    var secRecientes = document.getElementById('sec-recientes-container');

    if (vista === 'all') {
        if (secMotivos) secMotivos.style.display = '';
        if (secClientes) secClientes.style.display = '';
        if (secRecientes) secRecientes.style.display = '';
    } else if (vista === 'motivos') {
        if (secMotivos) secMotivos.style.display = '';
        if (secClientes) secClientes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = 'none';
    } else if (vista === 'clientes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = '';
        if (secRecientes) secRecientes.style.display = 'none';
    } else if (vista === 'recientes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = '';
    }

    try {
        localStorage.setItem('fundo_dashboard_tab_view', vista);
    } catch(e) {}
}

// Restaurar preferencia previa de visualización
(function() {
    try {
        var guardada = localStorage.getItem('fundo_dashboard_tab_view') || 'all';
        var targetBtn = document.querySelector('.view-pill[data-view="' + guardada + '"]');
        if (targetBtn) {
            cambiarVistaTabla(guardada, targetBtn);
        }
    } catch(e) {}
})();
</script>
