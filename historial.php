<?php
$pageTitle = 'Historial de Ventas';
$activeMenu = 'historial';
require_once __DIR__ . '/includes/layout.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Historial de Ventas</div>
        <a href="/ventas.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Nueva Venta
        </a>
    </div>

    <div class="search-bar">
        <div class="search-input-wrapper">
            <i class="fas fa-magnifying-glass search-icon"></i>
            <input type="text" class="search-input" id="buscar" placeholder="Buscar por número o cliente..." oninput="debounceSearch()">
        </div>
        <input type="date" class="form-control" id="filtroFecha" style="width:auto" onchange="loadVentas(1)">
        <select class="form-control" id="filtroEstado" style="width:140px" onchange="loadVentas(1)">
            <option value="">Todos</option>
            <option value="completada">Completadas</option>
            <option value="cancelada">Canceladas</option>
            <option value="pendiente">Pendientes</option>
        </select>
        <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
            <i class="fas fa-x"></i> Limpiar
        </button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th># Venta</th>
                    <th>Cliente</th>
                    <th>Servicios</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="ventasTableBody">
                <tr><td colspan="8" style="text-align:center;padding:40px">
                    <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
                </td></tr>
            </tbody>
        </table>
    </div>

    <div class="pagination" id="pagination"></div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <div class="modal-title" id="detailTitle">Detalle de Venta</div>
            <button class="modal-close" onclick="closeDetail()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="detailBody"></div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let currentPage = 1;
let searchTimer;

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadVentas(1), 400);
}

function clearFilters() {
    document.getElementById('buscar').value = '';
    document.getElementById('filtroFecha').value = '';
    document.getElementById('filtroEstado').value = '';
    loadVentas(1);
}

async function loadVentas(page = 1) {
    currentPage = page;
    const buscar = document.getElementById('buscar').value;
    const fecha = document.getElementById('filtroFecha').value;
    const estado = document.getElementById('filtroEstado').value;

    const params = new URLSearchParams({ page, buscar, fecha, estado });
    const tbody = document.getElementById('ventasTableBody');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px">
        <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
    </td></tr>`;

    try {
        const data = await apiGet(`/api/ventas.php?${params}`);

        if (data.ventas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8">
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-receipt"></i></div>
                    <div class="empty-title">Sin ventas</div>
                    <div class="empty-desc">No se encontraron ventas con los filtros aplicados</div>
                </div>
            </td></tr>`;
        } else if (window.innerWidth < 720) {
            // ── Mobile card view ──
            tbody.innerHTML = `<tr><td colspan="8" style="padding:8px 0;border:none">
                <div style="display:flex;flex-direction:column;gap:10px">
                    ${data.ventas.map(v => {
                        const cats = [...new Set(v.detalles.map(d=>d.categoria_nombre))].slice(0,2);
                        return `<div style="background:rgba(255,255,255,0.03);border:1px solid var(--card-border);border-radius:12px;padding:14px;animation:fadeUp 0.3s ease">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px">
                                <div>
                                    <div style="font-family:monospace;font-size:12px;color:var(--primary-light);font-weight:700">${v.numero_venta}</div>
                                    <div style="font-size:14px;font-weight:600;margin-top:2px">${v.cliente||'—'}</div>
                                </div>
                                <div style="text-align:right;flex-shrink:0">
                                    <div style="font-size:15px;font-weight:800">$ ${formatMoney(v.total_final)}</div>
                                    ${v.total_bs>0?`<div style="font-size:10px;color:var(--text-dim);margin-top:1px">Bs ${formatMoney(v.total_bs)}</div>`:''}
                                    <div style="margin-top:3px">${statusBadge(v.estado)}</div>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px">
                                <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
                                    ${payBadge(v.metodo_pago)}
                                    <span style="font-size:11px;color:var(--text-dim)">${formatDateTime(v.creado_en)}</span>
                                </div>
                                <div style="display:flex;gap:5px">
                                    <button class="btn btn-secondary btn-sm" onclick="viewDetail(${v.id})"><i class="fas fa-eye"></i></button>
                                    <a href="/factura.php?id=${v.id}" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-file-invoice"></i></a>
                                    ${v.estado==='completada'?`<button class="btn btn-danger btn-sm" onclick="cancelVenta(${v.id})"><i class="fas fa-ban"></i></button>`:''}
                                </div>
                            </div>
                        </div>`;
                    }).join('')}
                </div>
            </td></tr>`;
        } else {
            tbody.innerHTML = data.ventas.map(v => {
                const cats = [...new Set(v.detalles.map(d => d.categoria_nombre))].slice(0, 3);
                const catsHtml = cats.map(c => {
                    const det = v.detalles.find(d => d.categoria_nombre === c);
                    return `<span class="cat-pill" style="background:${det?.categoria_color||'#7c3aed'}22;border-color:${det?.categoria_color||'#7c3aed'}44;color:${det?.categoria_color||'#a78bfa'};font-size:10px">
                        <i class="${det?.categoria_icono||'fas fa-tag'}"></i>${c}
                    </span>`;
                }).join('');
                return `<tr>
                    <td><span class="font-mono" style="color:var(--primary-light)">${v.numero_venta}</span></td>
                    <td>${v.cliente||'<span style="color:var(--text-dim)">—</span>'}</td>
                    <td><div style="display:flex;flex-wrap:wrap;gap:4px">${catsHtml||'<span style="color:var(--text-dim);font-size:12px">—</span>'}</div></td>
                    <td>
                        <strong>$ ${formatMoney(v.total_final)}</strong>
                        ${v.total_bs>0?`<div style="font-size:10px;color:var(--text-dim);margin-top:1px">Bs ${formatMoney(v.total_bs)}</div>`:''}
                    </td>
                    <td>${payBadge(v.metodo_pago)}</td>
                    <td>${statusBadge(v.estado)}</td>
                    <td><span style="font-size:12px;color:var(--text-muted)">${formatDateTime(v.creado_en)}</span></td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap">
                            <button class="btn btn-secondary btn-sm" onclick="viewDetail(${v.id})" title="Ver detalle"><i class="fas fa-eye"></i></button>
                            <a href="/factura.php?id=${v.id}" target="_blank" class="btn btn-secondary btn-sm" title="Ver factura"><i class="fas fa-file-invoice"></i></a>
                            ${v.estado==='completada'?`<button class="btn btn-danger btn-sm" onclick="cancelVenta(${v.id})" title="Cancelar"><i class="fas fa-ban"></i></button>`:''}
                        </div>
                    </td>
                </tr>`;
            }).join('');
        }

        // Pagination
        renderPagination(data.pagina, data.paginas);

    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--danger);padding:24px">Error al cargar ventas</td></tr>`;
    }
}

function renderPagination(current, total) {
    const el = document.getElementById('pagination');
    if (total <= 1) { el.innerHTML = ''; return; }

    let html = `<button class="page-btn" onclick="loadVentas(${current - 1})" ${current === 1 ? 'disabled' : ''}>
        <i class="fas fa-chevron-left"></i>
    </button>`;

    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || Math.abs(i - current) <= 2) {
            html += `<button class="page-btn ${i === current ? 'active' : ''}" onclick="loadVentas(${i})">${i}</button>`;
        } else if (Math.abs(i - current) === 3) {
            html += `<span style="color:var(--text-dim);padding:0 4px">…</span>`;
        }
    }

    html += `<button class="page-btn" onclick="loadVentas(${current + 1})" ${current === total ? 'disabled' : ''}>
        <i class="fas fa-chevron-right"></i>
    </button>`;

    el.innerHTML = html;
}

async function viewDetail(id) {
    try {
        const v = await apiGet(`/api/ventas.php?id=${id}`);
        document.getElementById('detailTitle').innerHTML = `<i class="fas fa-receipt" style="color:var(--primary-light)"></i> ${v.numero_venta}`;

        const detallesHtml = v.detalles.map(d => `
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <i class="${d.icono}" style="color:${d.color};font-size:13px"></i>
                        <div>
                            <div style="font-size:13px;font-weight:500">${d.descripcion}</div>
                            <div style="font-size:11px;color:var(--text-dim)">${d.categoria_nombre}</div>
                        </div>
                    </div>
                </td>
                <td style="text-align:center">${d.cantidad}</td>
                <td style="text-align:right">${formatMoney(d.precio_unitario)}</td>
                <td style="text-align:right;font-weight:600">${formatMoney(d.subtotal)}</td>
            </tr>
        `).join('');

        const mixtoHtml = v.pago_mixto?.length ? `
            <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:10px;padding:12px 14px;margin-bottom:14px">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.7px;color:var(--warning);font-weight:700;margin-bottom:8px"><i class="fas fa-layer-group"></i> Desglose Mixto</div>
                ${v.pago_mixto.map(pm=>{const ml={fisico_bs:'Físico (Bs)',fisico_usd:'Físico ($)',pago_movil:'Pago Móvil'};return`<div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0"><span style="color:var(--text-muted)">${ml[pm.metodo]||pm.metodo}</span><strong>${formatMoney(pm.monto)}</strong></div>`}).join('')}
            </div>` : '';

        document.getElementById('detailBody').innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
                <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:13px">
                    <div style="font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Cliente</div>
                    <div style="font-weight:600;font-size:13px">${v.cliente || '—'}</div>
                    ${v.cliente_cedula?`<div style="font-size:11px;color:var(--text-dim);margin-top:3px"><i class="fas fa-id-card"></i> ${v.cliente_cedula}</div>`:''}
                    ${v.cliente_telefono?`<div style="font-size:11px;color:var(--text-dim);margin-top:2px"><i class="fas fa-phone"></i> ${v.cliente_telefono}</div>`:''}
                </div>
                <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:13px">
                    <div style="font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Fecha</div>
                    <div style="font-weight:600;font-size:13px">${formatDateTime(v.creado_en)}</div>
                </div>
                <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:13px">
                    <div style="font-size:10px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Pago</div>
                    <div>${payBadge(v.metodo_pago)}</div>
                </div>
                <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:14px">
                    <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Estado</div>
                    <div>${statusBadge(v.estado)}</div>
                </div>
            </div>

            ${mixtoHtml}
            <table style="margin-bottom:16px">
                <thead>
                    <tr>
                        <th>Servicio</th>
                        <th style="text-align:center">Cant.</th>
                        <th style="text-align:right">Precio</th>
                        <th style="text-align:right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>${detallesHtml}</tbody>
            </table>

            <div style="background:rgba(124,58,237,0.08);border:1px solid rgba(124,58,237,0.15);border-radius:10px;padding:14px">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:13px">
                    <span style="color:var(--text-muted)">Subtotal</span>
                    <span>${formatMoney(v.total)}</span>
                </div>
                <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px">
                    <span style="color:var(--text-muted)">Descuento</span>
                    <span style="color:var(--danger)">- ${formatMoney(v.descuento)}</span>
                </div>
                <hr style="border:none;border-top:1px solid rgba(124,58,237,0.2);margin-bottom:10px">
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700">
                    <span>Total</span>
                    <span style="color:var(--primary-light)">${formatMoney(v.total_final)}</span>
                </div>
            </div>
            ${v.notas ? `<div style="margin-top:14px;padding:12px;background:rgba(255,255,255,0.03);border-radius:10px;font-size:13px;color:var(--text-muted)"><i class="fas fa-note-sticky" style="margin-right:6px"></i>${v.notas}</div>` : ''}
        `;

        document.getElementById('detailModal').classList.add('open');
    } catch {
        showToast('Error al cargar el detalle', 'error');
    }
}

function closeDetail() {
    document.getElementById('detailModal').classList.remove('open');
}

async function cancelVenta(id) {
    const ok = await confirmAction('¿Seguro que deseas cancelar esta venta? No se puede deshacer.');
    if (!ok) return;
    const data = await apiDelete(`/api/ventas.php?id=${id}`);
    if (data.success) {
        showToast('Venta cancelada', 'success');
        loadVentas(currentPage);
    } else {
        showToast(data.error || 'Error', 'error');
    }
}

// Close modal on overlay click
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeDetail();
});

loadVentas(1);
window.addEventListener('resize', () => { clearTimeout(window._resizeT); window._resizeT = setTimeout(()=>loadVentas(currentPage),200); });
</script>
