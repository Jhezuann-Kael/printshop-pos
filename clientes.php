<?php
$pageTitle  = 'Clientes';
$activeMenu = 'clientes';
require_once __DIR__ . '/includes/layout.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-address-book"></i> Clientes Registrados</div>
        <button class="btn btn-primary btn-sm" onclick="openAdd()"><i class="fas fa-user-plus"></i> Nuevo Cliente</button>
    </div>

    <div class="search-bar">
        <div class="search-input-wrapper">
            <i class="fas fa-magnifying-glass search-icon"></i>
            <input type="text" class="search-input" id="buscar" placeholder="Buscar por nombre, cédula o teléfono..." oninput="debounceSearch()">
        </div>
        <select class="form-control" id="filtroTipo" style="width:140px" onchange="loadClientes(1)">
            <option value="">Todos</option>
            <option value="persona">Persona</option>
            <option value="empresa">Empresa</option>
        </select>
        <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
            <i class="fas fa-x"></i> Limpiar
        </button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Nombre / Empresa</th>
                    <th>Cédula / RIF</th>
                    <th>Teléfono</th>
                    <th>Registrado por</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="clientesBody">
                <tr><td colspan="6" style="text-align:center;padding:40px">
                    <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
                </td></tr>
            </tbody>
        </table>
    </div>

    <div class="pagination" id="pagination"></div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="cModal">
    <div class="modal" style="max-width:460px">
        <div class="modal-header">
            <div class="modal-title" id="cModalTitle"><i class="fas fa-user-plus"></i> Nuevo Cliente</div>
            <button class="modal-close" onclick="closeCModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cEditId">

            <!-- Tipo -->
            <div class="form-group">
                <label class="form-label">Tipo</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(255,255,255,0.03);border:2px solid var(--card-border);border-radius:10px;cursor:pointer;transition:all 0.2s" id="labelPersona">
                        <input type="radio" name="cTipo" value="persona" checked onchange="updateTipoUI()" style="accent-color:var(--primary)">
                        <div>
                            <div style="font-size:13px;font-weight:600"><i class="fas fa-user" style="color:var(--primary-light);margin-right:5px"></i>Persona</div>
                            <div style="font-size:11px;color:var(--text-dim)">Natural</div>
                        </div>
                    </label>
                    <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:rgba(255,255,255,0.03);border:2px solid var(--card-border);border-radius:10px;cursor:pointer;transition:all 0.2s" id="labelEmpresa">
                        <input type="radio" name="cTipo" value="empresa" onchange="updateTipoUI()" style="accent-color:var(--primary)">
                        <div>
                            <div style="font-size:13px;font-weight:600"><i class="fas fa-building" style="color:#f59e0b;margin-right:5px"></i>Empresa</div>
                            <div style="font-size:11px;color:var(--text-dim)">Jurídica</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" id="nombreLabel">Nombre completo <span style="color:var(--danger)">*</span></label>
                <input type="text" class="form-control" id="cNombre" placeholder="Nombre(s) Apellido(s)">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" id="cedLabel">Cédula</label>
                    <input type="text" class="form-control" id="cCedula" placeholder="V-12345678">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="cTelefono" placeholder="0414-1234567">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notas (opcional)</label>
                <input type="text" class="form-control" id="cNotas" placeholder="Observaciones del cliente">
            </div>

            <div style="display:flex;gap:10px;margin-top:6px">
                <button class="btn btn-secondary" style="flex:1" onclick="closeCModal()">Cancelar</button>
                <button class="btn btn-primary" style="flex:1" onclick="saveCliente()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let currentPage = 1;
let searchTimer;

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadClientes(1), 350);
}

function clearFilters() {
    document.getElementById('buscar').value = '';
    document.getElementById('filtroTipo').value = '';
    loadClientes(1);
}

async function loadClientes(page = 1) {
    currentPage = page;
    const q    = document.getElementById('buscar').value;
    const tipo = document.getElementById('filtroTipo').value;
    const params = new URLSearchParams({ page, q, tipo });

    const tbody = document.getElementById('clientesBody');
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;padding:40px">
        <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
    </td></tr>`;

    try {
        const data = await apiGet(`/api/clientes.php?${params}`);

        if (!data.clientes.length) {
            tbody.innerHTML = `<tr><td colspan="6">
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-address-book"></i></div>
                    <div class="empty-title">Sin clientes</div>
                    <div class="empty-desc">No se encontraron clientes con los filtros aplicados</div>
                </div>
            </td></tr>`;
        } else if (window.innerWidth < 720) {
            // ── Mobile card view ──
            tbody.innerHTML = `<tr><td colspan="6" style="padding:8px 0;border:none">
                <div style="display:flex;flex-direction:column;gap:10px">
                    ${data.clientes.map(c => `
                        <div style="background:rgba(255,255,255,0.03);border:1px solid var(--card-border);border-radius:12px;padding:14px;animation:fadeUp 0.3s ease">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px">
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:14px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.nombre}</div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:5px">
                                        ${c.tipo==='empresa'
                                            ? '<span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border-color:rgba(245,158,11,0.3);font-size:9px"><i class="fas fa-building"></i> Empresa</span>'
                                            : '<span class="badge badge-primary" style="font-size:9px"><i class="fas fa-user"></i> Persona</span>'}
                                        ${c.cedula_rif?`<span style="font-size:11px;color:var(--text-dim)"><i class="fas fa-id-card" style="margin-right:3px"></i>${c.cedula_rif}</span>`:''}
                                        ${c.telefono?`<span style="font-size:11px;color:var(--text-dim)"><i class="fas fa-phone" style="margin-right:3px"></i>${c.telefono}</span>`:''}
                                    </div>
                                </div>
                                <div style="display:flex;gap:5px;flex-shrink:0">
                                    <button class="btn btn-secondary btn-sm" onclick="editCliente(${c.id})"><i class="fas fa-pen"></i></button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteCliente(${c.id},'${c.nombre.replace(/'/g,"\\'").replace(/"/g,'&quot;')}')"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>`).join('')}
                </div>
            </td></tr>`;
        } else {
            tbody.innerHTML = data.clientes.map(c => `
                <tr>
                    <td>${c.tipo === 'empresa'
                        ? '<span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border-color:rgba(245,158,11,0.3)"><i class="fas fa-building" style="margin-right:4px"></i>Empresa</span>'
                        : '<span class="badge badge-primary" style="background:rgba(124,58,237,0.12)"><i class="fas fa-user" style="margin-right:4px"></i>Persona</span>'
                    }</td>
                    <td><strong>${c.nombre}</strong></td>
                    <td>${c.cedula_rif || '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td>${c.telefono   || '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td><span style="font-size:12px;color:var(--text-dim)">${c.registrado_por_nombre || '—'}</span></td>
                    <td>
                        <div style="display:flex;gap:5px">
                            <button class="btn btn-secondary btn-sm" onclick="editCliente(${c.id})" title="Editar"><i class="fas fa-pen"></i></button>
                            <button class="btn btn-danger btn-sm" onclick="deleteCliente(${c.id},'${c.nombre.replace(/'/g,"\\'").replace(/"/g,'&quot;')}')" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        renderPagination(data.pagina, data.paginas);
    } catch {
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:var(--danger);padding:24px">Error al cargar clientes</td></tr>`;
    }
}

function renderPagination(current, total) {
    const el = document.getElementById('pagination');
    if (total <= 1) { el.innerHTML = ''; return; }
    let html = `<button class="page-btn" onclick="loadClientes(${current-1})" ${current===1?'disabled':''}><i class="fas fa-chevron-left"></i></button>`;
    for (let i = 1; i <= total; i++) {
        if (i===1||i===total||Math.abs(i-current)<=2) html += `<button class="page-btn ${i===current?'active':''}" onclick="loadClientes(${i})">${i}</button>`;
        else if (Math.abs(i-current)===3) html += `<span style="color:var(--text-dim);padding:0 4px">…</span>`;
    }
    html += `<button class="page-btn" onclick="loadClientes(${current+1})" ${current===total?'disabled':''}><i class="fas fa-chevron-right"></i></button>`;
    el.innerHTML = html;
}

function updateTipoUI() {
    const tipo = document.querySelector('input[name="cTipo"]:checked').value;
    document.getElementById('nombreLabel').innerHTML = tipo === 'empresa'
        ? 'Nombre de la empresa <span style="color:var(--danger)">*</span>'
        : 'Nombre completo <span style="color:var(--danger)">*</span>';
    document.getElementById('cedLabel').textContent = tipo === 'empresa' ? 'RIF' : 'Cédula';
    document.getElementById('cCedula').placeholder  = tipo === 'empresa' ? 'J-12345678-9' : 'V-12345678';
    document.getElementById('cNombre').placeholder  = tipo === 'empresa' ? 'Empresa, C.A.' : 'Nombre(s) Apellido(s)';
    document.getElementById('labelPersona').style.borderColor = tipo==='persona'?'var(--primary)':'var(--card-border)';
    document.getElementById('labelEmpresa').style.borderColor = tipo==='empresa'?'#f59e0b':'var(--card-border)';
}

function openAdd() {
    document.getElementById('cEditId').value = '';
    document.querySelectorAll('input[name="cTipo"]')[0].checked = true;
    document.getElementById('cNombre').value   = '';
    document.getElementById('cCedula').value   = '';
    document.getElementById('cTelefono').value = '';
    document.getElementById('cNotas').value    = '';
    document.getElementById('cModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Cliente';
    updateTipoUI();
    document.getElementById('cModal').classList.add('open');
}

async function editCliente(id) {
    const c = await apiGet(`/api/clientes.php?id=${id}`);
    document.getElementById('cEditId').value = c.id;
    document.querySelector(`input[name="cTipo"][value="${c.tipo}"]`).checked = true;
    document.getElementById('cNombre').value   = c.nombre    || '';
    document.getElementById('cCedula').value   = c.cedula_rif || '';
    document.getElementById('cTelefono').value = c.telefono  || '';
    document.getElementById('cNotas').value    = c.notas     || '';
    document.getElementById('cModalTitle').innerHTML = '<i class="fas fa-pen"></i> Editar Cliente';
    updateTipoUI();
    document.getElementById('cModal').classList.add('open');
}

async function saveCliente() {
    const id     = document.getElementById('cEditId').value;
    const tipo   = document.querySelector('input[name="cTipo"]:checked').value;
    const nombre = document.getElementById('cNombre').value.trim();
    const ced    = document.getElementById('cCedula').value.trim();
    const tel    = document.getElementById('cTelefono').value.trim();
    const notas  = document.getElementById('cNotas').value.trim();

    if (!nombre) return showToast('El nombre es requerido','warning');

    const payload = { tipo, nombre, cedula_rif: ced, telefono: tel, notas };
    let res;
    if (id) {
        payload.id = parseInt(id);
        res = await fetch('/api/clientes.php', {
            method: 'PUT',
            headers: {'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify(payload)
        }).then(r => r.json());
    } else {
        res = await apiPost('/api/clientes.php', payload);
    }

    if (res.success) {
        showToast(id ? 'Cliente actualizado' : 'Cliente registrado','success');
        closeCModal();
        loadClientes(currentPage);
    } else {
        showToast(res.error || 'Error al guardar','error');
    }
}

async function deleteCliente(id, nombre) {
    const ok = await confirmAction(`¿Eliminar el cliente "<strong>${nombre}</strong>"?`);
    if (!ok) return;
    const res = await apiDelete(`/api/clientes.php?id=${id}`);
    if (res.success) {
        showToast('Cliente eliminado','success');
        loadClientes(currentPage);
    } else {
        showToast(res.error || 'Error','error');
    }
}

function closeCModal() {
    document.getElementById('cModal').classList.remove('open');
}

document.getElementById('cModal').addEventListener('click', function(e) { if(e.target===this) closeCModal(); });

loadClientes(1);
window.addEventListener('resize', () => { clearTimeout(window._cResT); window._cResT = setTimeout(()=>loadClientes(currentPage),200); });
</script>
