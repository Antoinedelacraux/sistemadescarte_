{{-- Interactividad JavaScript para el Panel de Control --}}
<script>

function cambiarSubvistaMatriz(modo) {
    var contKg = document.getElementById('container-matriz-kg');
    var contMonto = document.getElementById('container-matriz-monto');
    var sep = document.getElementById('matrix-separator');
    var btnAll = document.getElementById('btn-matriz-all');
    var btnKg = document.getElementById('btn-matriz-kg');
    var btnMonto = document.getElementById('btn-matriz-monto');

    [btnAll, btnKg, btnMonto].forEach(function(b) { if(b) b.classList.remove('active'); });

    if (modo === 'kg') {
        if (contKg) contKg.style.display = '';
        if (contMonto) contMonto.style.display = 'none';
        if (sep) sep.style.display = 'none';
        if (btnKg) btnKg.classList.add('active');
    } else if (modo === 'monto') {
        if (contKg) contKg.style.display = 'none';
        if (contMonto) contMonto.style.display = '';
        if (sep) sep.style.display = 'none';
        if (btnMonto) btnMonto.classList.add('active');
    } else {
        if (contKg) contKg.style.display = '';
        if (contMonto) contMonto.style.display = '';
        if (sep) sep.style.display = '';
        if (btnAll) btnAll.classList.add('active');
    }
}

function filtrarMatricesClientes() {
    var input = document.getElementById('client-matrix-search');
    var q = (input ? input.value : '').toLowerCase().trim();
    var rowsKg = document.querySelectorAll('#matrix-kg-tbody .matrix-client-row');
    var rowsMonto = document.querySelectorAll('#matrix-monto-tbody .matrix-client-row');
    var visibleCount = 0;

    rowsKg.forEach(function(r) {
        var name = (r.getAttribute('data-client-name') || '').toLowerCase();
        var ruc = (r.getAttribute('data-ruc') || '').toLowerCase();
        var match = q === '' || name.indexOf(q) !== -1 || ruc.indexOf(q) !== -1;
        r.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    rowsMonto.forEach(function(r) {
        var name = (r.getAttribute('data-client-name') || '').toLowerCase();
        var ruc = (r.getAttribute('data-ruc') || '').toLowerCase();
        var match = q === '' || name.indexOf(q) !== -1 || ruc.indexOf(q) !== -1;
        r.style.display = match ? '' : 'none';
    });

    var badge = document.getElementById('matrix-clients-count');
    if (badge) {
        badge.textContent = visibleCount + ' cliente(s) ' + (q !== '' ? 'encontrado(s)' : 'registrados');
    }
}

function filtrarDashboardPorFundo(fundoId) {
    var url = new URL(window.location.href);
    if (fundoId) {
        url.searchParams.set('fundo_id', fundoId);
    } else {
        url.searchParams.delete('fundo_id');
    }
    window.location.href = url.toString();
}

function exportarClientesExcel() {
    var q = (document.getElementById('client-matrix-search')?.value || document.getElementById('client-search-input')?.value || '').trim();
    var fundoSelect = document.getElementById('fundo_filter_select');
    var fundoId = fundoSelect ? fundoSelect.value : '{{ $fundoSeleccionadoId ?? "" }}';
    var url = "{{ route('dashboard.exportar') }}?tabla=clientes";
    if (fundoId) {
        url += '&fundo_id=' + encodeURIComponent(fundoId);
    }
    if (q !== '') {
        url += '&buscar=' + encodeURIComponent(q);
    }
    window.location.href = url;
}

function exportarLotesExcel() {
    var fundoSelect = document.getElementById('fundo_filter_select');
    var fundoId = fundoSelect ? fundoSelect.value : '{{ $fundoSeleccionadoId ?? "" }}';
    var url = "{{ route('dashboard.exportar') }}?tabla=lotes";
    if (fundoId) {
        url += '&fundo_id=' + encodeURIComponent(fundoId);
    }
    window.location.href = url;
}

function filtrarMatrizLotes() {
    var input = document.getElementById('lote-matrix-search');
    var q = (input ? input.value : '').toLowerCase().trim();
    var rows = document.querySelectorAll('#matrix-lotes-tbody .matrix-lote-row');
    var visibleCount = 0;

    rows.forEach(function(row) {
        var lote = (row.getAttribute('data-lote-name') || '').toLowerCase();
        var fundo = (row.getAttribute('data-fundo-name') || '').toLowerCase();
        var match = q === '' || lote.indexOf(q) !== -1 || fundo.indexOf(q) !== -1;
        row.style.display = match ? '' : 'none';
        if (match) visibleCount++;
    });

    var badge = document.getElementById('matrix-lotes-count');
    if (badge) badge.textContent = visibleCount + ' lote(s) ' + (q !== '' ? 'encontrado(s)' : 'registrados');

    var empty = document.getElementById('matrix-lotes-empty-filter');
    if (empty) empty.style.display = visibleCount > 0 ? 'none' : 'block';
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
    var secLotes = document.getElementById('sec-lotes-container');
    var secRecientes = document.getElementById('sec-recientes-container');

    if (vista === 'clientes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = '';
        if (secLotes) secLotes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = 'none';
    } else if (vista === 'lotes') {
        if (secMotivos) secMotivos.style.display = 'none';
        if (secClientes) secClientes.style.display = 'none';
        if (secLotes) secLotes.style.display = '';
        if (secRecientes) secRecientes.style.display = 'none';
    } else {
        // Por defecto: 'motivos' (Por Tipo y Descarte)
        if (secMotivos) secMotivos.style.display = '';
        if (secClientes) secClientes.style.display = 'none';
        if (secLotes) secLotes.style.display = 'none';
        if (secRecientes) secRecientes.style.display = 'none';
    }

    try {
        localStorage.setItem('fundo_dashboard_tab_view', vista);
    } catch(e) {}
}

// Restaurar preferencia previa de visualización (solo 'motivos' o 'clientes')
(function() {
    try {
        var guardada = localStorage.getItem('fundo_dashboard_tab_view') || 'motivos';
        if (guardada !== 'motivos' && guardada !== 'clientes' && guardada !== 'lotes') {
            guardada = 'motivos';
        }
        var targetBtn = document.querySelector('.view-pill[data-view="' + guardada + '"]');
        if (targetBtn) {
            cambiarVistaTabla(guardada, targetBtn);
        } else {
            var defaultBtn = document.querySelector('.view-pill[data-view="motivos"]');
            if (defaultBtn) cambiarVistaTabla('motivos', defaultBtn);
        }
    } catch(e) {}
})();
</script>
