<?php
$pageTitle  = 'Trabajadores';
$activeMenu = 'trabajadores';
require_once __DIR__ . '/includes/layout.php';
if ($user['rol'] !== 'admin') { header('Location: /dashboard.php'); exit; }
?>

<div class="grid-col-2-1" style="align-items:start;gap:20px">
    <!-- Lista trabajadores -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-users"></i> Equipo de Trabajo</div>
            <button class="btn btn-primary btn-sm" onclick="openAddModal()"><i class="fas fa-user-plus"></i> Nuevo</button>
        </div>
        <div id="listaTrabajadores">
            <div class="loading-dots" style="justify-content:center;padding:32px"><span></span><span></span><span></span></div>
        </div>
    </div>

    <!-- Pagos panel -->
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card">
            <div class="card-title mb-16"><i class="fas fa-hand-holding-dollar"></i> Registrar Pago</div>
            <div class="form-group">
                <label class="form-label">Trabajador</label>
                <select class="form-control" id="pagoUid"></select>
            </div>

            <!-- Monto USD -->
            <div class="form-group">
                <label class="form-label">Monto <span style="color:var(--success);font-weight:700">USD ($)</span></label>
                <div class="input-icon-wrap">
                    <i class="fas fa-dollar-sign form-icon"></i>
                    <input type="number" class="form-control has-icon" id="pagoMonto" placeholder="0.00" min="0" step="0.01" oninput="calcBs()">
                </div>
            </div>

            <!-- BCV rate display -->
            <div id="bcvDisplay" style="margin-bottom:14px;padding:12px 14px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.18);border-radius:10px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                    <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--success)"><i class="fas fa-landmark"></i> Tasa BCV</span>
                    <button onclick="fetchBcv()" style="background:none;border:none;color:var(--text-dim);cursor:pointer;font-size:11px;padding:2px 6px;border-radius:5px;transition:color 0.2s" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--text-dim)'"><i class="fas fa-rotate"></i> Actualizar</button>
                </div>
                <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap">
                    <div>
                        <span style="font-size:10px;color:var(--text-dim)">1 USD =</span>
                        <span id="bcvTasa" style="font-size:16px;font-weight:800;color:var(--success)">—</span>
                        <span style="font-size:11px;color:var(--text-dim)">Bs</span>
                    </div>
                    <div style="flex:1;text-align:right">
                        <span style="font-size:10px;color:var(--text-dim)">Equivale a</span>
                        <span id="bcvMontoBs" style="font-size:15px;font-weight:700;color:var(--text)">—</span>
                        <span style="font-size:11px;color:var(--text-dim)">Bs</span>
                    </div>
                </div>
                <div id="bcvFecha" style="font-size:10px;color:var(--text-dim);margin-top:4px"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Descripción</label>
                <input type="text" class="form-control" id="pagoDesc" placeholder="Quincena, bono, etc.">
            </div>
            <div class="form-group">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control" id="pagoFecha" value="<?= date('Y-m-d') ?>">
            </div>

            <!-- Image upload -->
            <div class="form-group mb-0">
                <label class="form-label">Comprobante <span style="color:var(--text-dim);font-weight:400">(imagen opcional)</span></label>
                <div id="uploadZone" onclick="document.getElementById('pagoImagen').click()"
                     style="border:2px dashed var(--card-border);border-radius:10px;padding:16px;text-align:center;cursor:pointer;transition:all 0.2s"
                     onmouseover="this.style.borderColor='var(--primary-light)'" onmouseout="this.style.borderColor='var(--card-border)'">
                    <i class="fas fa-image" style="font-size:22px;color:var(--text-dim);display:block;margin-bottom:6px"></i>
                    <div id="uploadLabel" style="font-size:12px;color:var(--text-dim)">Clic para seleccionar imagen</div>
                    <input type="file" id="pagoImagen" accept="image/*" style="display:none" onchange="previewImg(event)">
                </div>
                <div id="imgPreview" style="display:none;margin-top:10px;position:relative">
                    <img id="imgPreviewEl" style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid var(--card-border)" src="">
                    <button onclick="clearImg()" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.7);border:none;color:white;width:26px;height:26px;border-radius:50%;cursor:pointer;font-size:11px">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <button class="btn btn-success w-full mt-16" onclick="registrarPago()"><i class="fas fa-check"></i> Registrar Pago</button>
        </div>

        <div class="card">
            <div class="card-title mb-16"><i class="fas fa-clock-rotate-left"></i> Historial de Pagos</div>
            <div id="historialPagos">
                <div style="color:var(--text-dim);font-size:13px;text-align:center;padding:16px">Selecciona un trabajador</div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Worker Modal -->
<div class="modal-overlay" id="wModal">
    <div class="modal" style="max-width:580px;max-height:90vh;overflow-y:auto">
        <div class="modal-header" style="position:sticky;top:0;z-index:1;background:var(--card-bg)">
            <div class="modal-title" id="wModalTitle"><i class="fas fa-user-plus"></i> Nuevo Trabajador</div>
            <button class="modal-close" onclick="closeWModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">

            <!-- Section: Datos personales -->
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.8px;color:var(--primary-light);font-weight:700;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid rgba(124,58,237,0.2)">
                <i class="fas fa-id-card"></i> Datos Personales
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nombre <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="wNombre" placeholder="Nombre(s)">
                </div>
                <div class="form-group">
                    <label class="form-label">Apellido <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="wApellido" placeholder="Apellido(s)">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Cédula</label>
                    <input type="text" class="form-control" id="wCedula" placeholder="V-12345678">
                </div>
                <div class="form-group">
                    <label class="form-label">Teléfono personal</label>
                    <input type="text" class="form-control" id="wTelefono" placeholder="0414-1234567">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Lugar de residencia</label>
                    <input type="text" class="form-control" id="wLugar" placeholder="Ciudad, Estado">
                </div>
                <div class="form-group">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" class="form-control" id="wFechaNac" onchange="calcularEdad()">
                </div>
            </div>
            <div id="edadDisplay" style="display:none;margin-bottom:14px;padding:9px 13px;background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.2);border-radius:9px;font-size:13px;color:var(--text-muted)">
                <i class="fas fa-birthday-cake" style="color:#06b6d4;margin-right:6px"></i>
                <span id="edadTexto"></span>
            </div>

            <!-- Section: Cuenta del sistema -->
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.8px;color:var(--primary-light);font-weight:700;margin:18px 0 12px;padding-bottom:6px;border-bottom:1px solid rgba(124,58,237,0.2)">
                <i class="fas fa-lock"></i> Cuenta del Sistema
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Usuario <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="wUsuario" placeholder="usuario_login" autocomplete="off">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="wEmail" placeholder="correo@ejemplo.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Rol</label>
                    <select class="form-control" id="wRol">
                        <option value="vendedor">Vendedor</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Contraseña <span id="passHint" style="color:var(--text-dim);font-weight:400">(requerida)</span></label>
                    <input type="password" class="form-control" id="wPassword" placeholder="Contraseña segura" autocomplete="new-password">
                </div>
            </div>

            <!-- Section: Pago Móvil -->
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.8px;color:var(--primary-light);font-weight:700;margin:18px 0 12px;padding-bottom:6px;border-bottom:1px solid rgba(124,58,237,0.2)">
                <i class="fas fa-mobile-screen"></i> Datos de Pago Móvil
            </div>
            <div class="form-group">
                <label class="form-label">Banco</label>
                <select class="form-control" id="wBanco">
                    <option value="">— Seleccionar banco —</option>
                    <option>Banco de Venezuela</option>
                    <option>Banesco</option>
                    <option>Banco Mercantil</option>
                    <option>BBVA Provincial</option>
                    <option>Banco Exterior</option>
                    <option>Bancaribe</option>
                    <option>Banco Nacional de Crédito (BNC)</option>
                    <option>Banco del Tesoro</option>
                    <option>Banco Bicentenario</option>
                    <option>Bancrecer</option>
                    <option>Sofitasa</option>
                    <option>Fondo Común</option>
                    <option>BOD</option>
                    <option>Otro</option>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Teléfono afiliado</label>
                    <input type="text" class="form-control" id="wTelPM" placeholder="0414-1234567">
                </div>
                <div class="form-group">
                    <label class="form-label">Cédula del pago móvil</label>
                    <input type="text" class="form-control" id="wCedPM" placeholder="V-12345678">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:8px">
                <button class="btn btn-secondary" style="flex:1" onclick="closeWModal()">Cancelar</button>
                <button class="btn btn-primary" style="flex:1" onclick="saveTrabajador()"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- View Worker Detail Modal -->
<div class="modal-overlay" id="viewModal">
    <div class="modal" style="max-width:480px;max-height:90vh;overflow-y:auto">
        <div class="modal-header" style="position:sticky;top:0;z-index:1;background:var(--card-bg)">
            <div class="modal-title"><i class="fas fa-user-circle"></i> Perfil del Trabajador</div>
            <button class="modal-close" onclick="document.getElementById('viewModal').classList.remove('open')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="viewBody"></div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let trabajadores = [];

async function loadTrabajadores() {
    const d = await apiGet('/api/trabajadores.php');
    trabajadores = d.trabajadores;

    const sel = document.getElementById('pagoUid');
    sel.innerHTML = trabajadores.filter(t => t.activo).map(t =>
        `<option value="${t.id}">${t.nombre} ${t.apellido || ''}</option>`
    ).join('');

    document.getElementById('listaTrabajadores').innerHTML = `
        <div style="display:flex;flex-direction:column;gap:10px">
            ${trabajadores.map(t => {
                const fullName = [t.nombre, t.apellido].filter(Boolean).join(' ');
                const initials = (t.nombre.charAt(0) + (t.apellido?.charAt(0) || '')).toUpperCase();
                return `
                <div style="display:flex;align-items:center;gap:12px;padding:14px;background:rgba(255,255,255,0.03);border:1px solid var(--card-border);border-radius:12px;transition:all 0.2s" onmouseover="this.style.borderColor='rgba(124,58,237,0.3)'" onmouseout="this.style.borderColor='var(--card-border)'">
                    <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,${t.rol==='admin'?'#7c3aed,#5b21b6':'#06b6d4,#0891b2'});display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:white;flex-shrink:0">
                        ${initials}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:600;font-size:14px">${fullName}</div>
                        <div style="font-size:12px;color:var(--text-dim)">@${t.usuario}${t.cedula?' · '+t.cedula:''}${t.telefono?' · '+t.telefono:''}</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end">
                        <span class="badge ${t.rol==='admin'?'badge-primary':'badge-gray'}">${t.rol==='admin'?'Admin':'Vendedor'}</span>
                        ${t.activo ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>'}
                    </div>
                    <div style="display:flex;gap:6px;flex-shrink:0">
                        <button class="btn btn-secondary btn-sm" onclick="viewTrabajador(${t.id})" title="Ver perfil"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-secondary btn-sm" onclick="viewPagos(${t.id})" title="Historial pagos"><i class="fas fa-coins"></i></button>
                        <button class="btn btn-secondary btn-sm" onclick="editTrabajador(${t.id})" title="Editar"><i class="fas fa-pen"></i></button>
                        <button class="btn ${t.activo?'btn-danger':'btn-secondary'} btn-sm" onclick="toggleActivo(${t.id},${t.activo?1:0})" title="${t.activo?'Desactivar':'Activar'}">
                            <i class="fas fa-${t.activo?'ban':'check'}"></i>
                        </button>
                    </div>
                </div>`;
            }).join('')}
        </div>
    `;
}

async function viewTrabajador(id) {
    const t = await apiGet(`/api/trabajadores.php?id=${id}`);
    const fullName = [t.nombre, t.apellido].filter(Boolean).join(' ');
    const edad = t.fecha_nacimiento ? calcEdad(t.fecha_nacimiento) : null;

    document.getElementById('viewBody').innerHTML = `
        <div style="text-align:center;padding-bottom:20px;border-bottom:1px solid var(--card-border);margin-bottom:18px">
            <div style="width:68px;height:68px;border-radius:18px;background:linear-gradient(135deg,${t.rol==='admin'?'#7c3aed,#5b21b6':'#06b6d4,#0891b2'});display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;color:white;margin:0 auto 12px">
                ${(t.nombre.charAt(0)+(t.apellido?.charAt(0)||'')).toUpperCase()}
            </div>
            <div style="font-size:18px;font-weight:700">${fullName}</div>
            <div style="font-size:12px;color:var(--text-dim);margin-top:4px">@${t.usuario}</div>
            <div style="display:flex;gap:8px;justify-content:center;margin-top:10px">
                <span class="badge ${t.rol==='admin'?'badge-primary':'badge-gray'}">${t.rol==='admin'?'Administrador':'Vendedor'}</span>
                ${t.activo?'<span class="badge badge-success">Activo</span>':'<span class="badge badge-danger">Inactivo</span>'}
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
            ${infoBox('fa-id-card','Cédula',t.cedula||'—')}
            ${infoBox('fa-phone','Teléfono',t.telefono||'—')}
            ${infoBox('fa-location-dot','Residencia',t.lugar_residencia||'—')}
            ${infoBox('fa-birthday-cake','Edad',edad!==null?`${edad} años (${new Date(t.fecha_nacimiento+'T12:00:00').toLocaleDateString('es-VE')})`:'—')}
            ${t.email?infoBox('fa-envelope','Email',t.email):''}
        </div>

        ${(t.banco_pago_movil || t.telefono_pago_movil || t.cedula_pago_movil) ? `
        <div style="background:rgba(6,182,212,0.05);border:1px solid rgba(6,182,212,0.2);border-radius:12px;padding:14px;margin-bottom:16px">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.7px;color:#06b6d4;font-weight:700;margin-bottom:10px"><i class="fas fa-mobile-screen"></i> Pago Móvil</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                ${infoBox('fa-building-columns','Banco',t.banco_pago_movil||'—')}
                ${infoBox('fa-phone','Teléfono afiliado',t.telefono_pago_movil||'—')}
                ${infoBox('fa-id-card','Cédula PM',t.cedula_pago_movil||'—')}
            </div>
        </div>` : ''}

        <div style="display:flex;gap:10px">
            <button class="btn btn-secondary" style="flex:1" onclick="document.getElementById('viewModal').classList.remove('open')">Cerrar</button>
            <button class="btn btn-primary" style="flex:1" onclick="editTrabajador(${t.id});document.getElementById('viewModal').classList.remove('open')"><i class="fas fa-pen"></i> Editar</button>
        </div>
    `;
    document.getElementById('viewModal').classList.add('open');
}

function infoBox(icon, label, value) {
    return `<div style="background:rgba(255,255,255,0.03);border-radius:9px;padding:10px 12px">
        <div style="font-size:10px;color:var(--text-dim);margin-bottom:4px"><i class="fas ${icon}" style="margin-right:4px;opacity:.7"></i>${label}</div>
        <div style="font-size:13px;font-weight:500;word-break:break-word">${value}</div>
    </div>`;
}

function calcEdad(fechaNac) {
    const hoy = new Date();
    const nac = new Date(fechaNac + 'T12:00:00');
    let edad = hoy.getFullYear() - nac.getFullYear();
    const m = hoy.getMonth() - nac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;
    return edad;
}

function calcularEdad() {
    const val = document.getElementById('wFechaNac').value;
    const el  = document.getElementById('edadDisplay');
    if (!val) { el.style.display='none'; return; }
    const edad = calcEdad(val);
    document.getElementById('edadTexto').textContent = `${edad} años`;
    el.style.display = 'block';
}

let bcvTasa = 0;

async function fetchBcv() {
    document.getElementById('bcvTasa').textContent = '…';
    try {
        const d = await apiGet('/api/bcv.php');
        if (d.ok && d.tasa) {
            bcvTasa = d.tasa;
            document.getElementById('bcvTasa').textContent = parseFloat(d.tasa).toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
            document.getElementById('bcvFecha').textContent = d.stale ? 'Última tasa guardada · ' + d.fecha : 'Actualizado: ' + d.fecha;
            calcBs();
        } else {
            document.getElementById('bcvTasa').textContent = 'N/D';
            document.getElementById('bcvFecha').textContent = d.error || 'No disponible';
        }
    } catch {
        document.getElementById('bcvTasa').textContent = 'Error';
    }
}

function calcBs() {
    const monto = parseFloat(document.getElementById('pagoMonto').value || 0);
    const el = document.getElementById('bcvMontoBs');
    if (!bcvTasa || !monto) { el.textContent = '—'; return; }
    el.textContent = (monto * bcvTasa).toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function previewImg(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('imgPreviewEl').src = ev.target.result;
        document.getElementById('imgPreview').style.display = 'block';
        document.getElementById('uploadLabel').textContent = file.name;
    };
    reader.readAsDataURL(file);
}

function clearImg() {
    document.getElementById('pagoImagen').value = '';
    document.getElementById('imgPreview').style.display = 'none';
    document.getElementById('uploadLabel').textContent = 'Clic para seleccionar imagen';
}

async function viewPagos(uid) {
    document.getElementById('pagoUid').value = uid;
    const d = await apiGet(`/api/trabajadores.php?pagos=${uid}`);
    const t = trabajadores.find(x => x.id == uid);
    const el = document.getElementById('historialPagos');

    if (!d.pagos.length) {
        el.innerHTML = `<div class="empty-state" style="padding:20px"><div class="empty-icon"><i class="fas fa-coins"></i></div><div class="empty-desc">Sin pagos registrados</div></div>`;
        return;
    }

    el.innerHTML = `
        <div style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:10px">${t?.nombre || ''} ${t?.apellido||''}</div>
        <div style="display:flex;flex-direction:column;gap:8px">
            ${d.pagos.map(p => `
                <div style="padding:10px 12px;background:rgba(255,255,255,0.02);border-radius:8px;border:1px solid var(--card-border)">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:4px">
                        <div>
                            <span style="font-size:13px;font-weight:700;color:var(--success)">$ ${parseFloat(p.monto).toLocaleString('es-VE',{minimumFractionDigits:2})}</span>
                            ${p.monto_bs?`<div style="font-size:11px;color:var(--text-dim);margin-top:1px">Bs ${parseFloat(p.monto_bs).toLocaleString('es-VE',{minimumFractionDigits:2})}${p.tasa_bcv?' · Tasa: '+parseFloat(p.tasa_bcv).toLocaleString('es-VE',{minimumFractionDigits:2}):''}</div>`:''}
                        </div>
                        <span style="font-size:11px;color:var(--text-dim)">${new Date(p.fecha+'T12:00:00').toLocaleDateString('es-VE')}</span>
                    </div>
                    ${p.descripcion?`<div style="font-size:12px;color:var(--text-muted)">${p.descripcion}</div>`:''}
                    ${p.imagen_comprobante?`<a href="/${p.imagen_comprobante}" target="_blank" style="display:inline-flex;align-items:center;gap:5px;font-size:11px;color:var(--primary-light);text-decoration:none;margin-top:6px"><i class="fas fa-image"></i> Ver comprobante</a>`:''}
                </div>
            `).join('')}
        </div>
    `;
}

async function registrarPago() {
    const uid   = document.getElementById('pagoUid').value;
    const monto = parseFloat(document.getElementById('pagoMonto').value || 0);
    if (!uid || monto <= 0) return showToast('Selecciona trabajador y monto válido','warning');

    const montoBs = bcvTasa ? (monto * bcvTasa).toFixed(2) : 0;
    const fd = new FormData();
    fd.append('usuario_id',  uid);
    fd.append('monto',       monto);
    fd.append('descripcion', document.getElementById('pagoDesc').value);
    fd.append('fecha',       document.getElementById('pagoFecha').value);
    fd.append('tasa_bcv',    bcvTasa || 0);
    fd.append('monto_bs',    montoBs);
    const imgFile = document.getElementById('pagoImagen').files[0];
    if (imgFile) fd.append('imagen', imgFile);

    const r = await fetch('/api/trabajadores.php?pago=1', { method: 'POST', body: fd });
    const d = await r.json();

    if (d.success) {
        showToast('Pago registrado','success');
        document.getElementById('pagoMonto').value = '';
        document.getElementById('pagoDesc').value  = '';
        clearImg();
        viewPagos(uid);
    } else {
        showToast(d.error || 'Error','error');
    }
}

async function editTrabajador(id) {
    const t = await apiGet(`/api/trabajadores.php?id=${id}`);
    document.getElementById('editId').value    = t.id;
    document.getElementById('wNombre').value   = t.nombre   || '';
    document.getElementById('wApellido').value = t.apellido || '';
    document.getElementById('wCedula').value   = t.cedula   || '';
    document.getElementById('wTelefono').value = t.telefono || '';
    document.getElementById('wLugar').value    = t.lugar_residencia || '';
    document.getElementById('wFechaNac').value = t.fecha_nacimiento || '';
    document.getElementById('wUsuario').value  = t.usuario  || '';
    document.getElementById('wEmail').value    = t.email    || '';
    document.getElementById('wRol').value      = t.rol      || 'vendedor';
    document.getElementById('wPassword').value = '';
    document.getElementById('wBanco').value    = t.banco_pago_movil    || '';
    document.getElementById('wTelPM').value    = t.telefono_pago_movil || '';
    document.getElementById('wCedPM').value    = t.cedula_pago_movil   || '';
    calcularEdad();
    document.getElementById('wModalTitle').innerHTML = '<i class="fas fa-pen"></i> Editar Trabajador';
    document.getElementById('passHint').textContent  = '(dejar vacío para mantener)';
    document.getElementById('wUsuario').disabled = true;
    document.getElementById('wModal').classList.add('open');
}

async function toggleActivo(id, activo) {
    const ok = await confirmAction(`¿${activo ? 'Desactivar' : 'Activar'} este usuario?`);
    if (!ok) return;
    const d = await fetch('/api/trabajadores.php', {
        method:'PUT',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({id, activo: !activo})
    }).then(r=>r.json());
    if (d.success) { showToast('Actualizado','success'); loadTrabajadores(); }
}

async function saveTrabajador() {
    const id       = document.getElementById('editId').value;
    const nombre   = document.getElementById('wNombre').value.trim();
    const apellido = document.getElementById('wApellido').value.trim();
    const usuario  = document.getElementById('wUsuario').value.trim();
    const email    = document.getElementById('wEmail').value.trim();
    const rol      = document.getElementById('wRol').value;
    const pass     = document.getElementById('wPassword').value;
    const cedula   = document.getElementById('wCedula').value.trim();
    const telefono = document.getElementById('wTelefono').value.trim();
    const lugar    = document.getElementById('wLugar').value.trim();
    const fnac     = document.getElementById('wFechaNac').value;
    const banco    = document.getElementById('wBanco').value;
    const telPM    = document.getElementById('wTelPM').value.trim();
    const cedPM    = document.getElementById('wCedPM').value.trim();

    if (!nombre) return showToast('El nombre es requerido','warning');

    const payload = { nombre, apellido, email, rol, cedula, telefono,
                      lugar_residencia: lugar, fecha_nacimiento: fnac || null,
                      banco_pago_movil: banco, telefono_pago_movil: telPM, cedula_pago_movil: cedPM,
                      password: pass };

    let res;
    if (id) {
        payload.id = parseInt(id);
        res = await fetch('/api/trabajadores.php', {
            method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json'},
            body: JSON.stringify(payload)
        }).then(r=>r.json());
    } else {
        if (!usuario || !pass) return showToast('Usuario y contraseña son requeridos','warning');
        payload.usuario = usuario;
        res = await apiPost('/api/trabajadores.php', payload);
    }

    if (res.success) {
        showToast(id ? 'Trabajador actualizado' : 'Trabajador creado','success');
        closeWModal();
        loadTrabajadores();
    } else {
        showToast(res.error || 'Error al guardar','error');
    }
}

function openAddModal() {
    document.getElementById('editId').value    = '';
    document.getElementById('wNombre').value   = '';
    document.getElementById('wApellido').value = '';
    document.getElementById('wCedula').value   = '';
    document.getElementById('wTelefono').value = '';
    document.getElementById('wLugar').value    = '';
    document.getElementById('wFechaNac').value = '';
    document.getElementById('wUsuario').value  = '';
    document.getElementById('wEmail').value    = '';
    document.getElementById('wRol').value      = 'vendedor';
    document.getElementById('wPassword').value = '';
    document.getElementById('wBanco').value    = '';
    document.getElementById('wTelPM').value    = '';
    document.getElementById('wCedPM').value    = '';
    document.getElementById('edadDisplay').style.display = 'none';
    document.getElementById('wModalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Trabajador';
    document.getElementById('passHint').textContent  = '(requerida)';
    document.getElementById('wUsuario').disabled = false;
    document.getElementById('wModal').classList.add('open');
}

function closeWModal() {
    document.getElementById('wModal').classList.remove('open');
}

document.getElementById('wModal').addEventListener('click', function(e) { if(e.target===this) closeWModal(); });
document.getElementById('viewModal').addEventListener('click', function(e) { if(e.target===this) this.classList.remove('open'); });

loadTrabajadores();
fetchBcv();
</script>
