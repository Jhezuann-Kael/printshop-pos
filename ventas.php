<?php
$pageTitle = 'Nueva Venta';
$activeMenu = 'ventas';
require_once __DIR__ . '/includes/layout.php';
?>

<div class="grid-col-2-1 gap-20" style="align-items:start">
    <!-- ── Formulario ── -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-plus-circle"></i> Registrar Venta</div>
        </div>

        <!-- Cliente -->
        <div class="section-label" style="display:flex;align-items:center;justify-content:space-between">
            <span>Datos del Cliente</span>
            <a href="/clientes.php" target="_blank" style="font-size:11px;color:var(--primary-light);text-decoration:none;font-weight:500"><i class="fas fa-address-book"></i> Ver clientes</a>
        </div>
        <div class="form-group mb-12" style="position:relative">
            <label class="form-label">Buscar cliente registrado</label>
            <div class="input-icon-wrap">
                <i class="fas fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);color:white;font-size:13px;z-index:1;pointer-events:none"></i>
                <input type="text" id="clienteBuscar" placeholder="Buscar por nombre, cédula o teléfono..." autocomplete="off" oninput="buscarCliente()" onfocus="buscarCliente()"
                       style="width:100%;padding:10px 14px 10px 38px;border-radius:10px;border:none;outline:none;background:var(--primary);color:white;font-size:13px;font-family:inherit;font-weight:500"
                       onfocus="this.style.background='#6d28d9';buscarCliente()" onblur="this.style.background='var(--primary)'">
            </div>
            <div id="clienteSugerencias" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:200;background:var(--card-bg);border:1px solid var(--card-border);border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.4);max-height:220px;overflow-y:auto;margin-top:4px"></div>
        </div>
        <div class="form-row mb-16">
            <div class="form-group mb-0">
                <label class="form-label">Nombre del Cliente</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" class="form-control has-icon" id="cliente" placeholder="Nombre completo">
                </div>
            </div>
            <div class="form-group mb-0">
                <label class="form-label">Cédula</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-id-card form-icon"></i>
                    <input type="text" class="form-control has-icon" id="clienteCedula" placeholder="V-00000000">
                </div>
            </div>
        </div>
        <div class="form-group mb-16">
            <label class="form-label">Teléfono</label>
            <div class="input-icon-wrap">
                <i class="fas fa-phone form-icon"></i>
                <input type="text" class="form-control has-icon" id="clienteTelefono" placeholder="0414-0000000" style="max-width:280px">
            </div>
        </div>

        <hr class="separator">

        <!-- Método de pago -->
        <div class="section-label">Método de Pago</div>
        <div class="pay-selector" id="paySelector">
            <label class="pay-option" data-method="fisico_bs">
                <input type="radio" name="metodoPago" value="fisico_bs" checked>
                <span class="pay-opt-inner">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Físico (Bs)</span>
                </span>
            </label>
            <label class="pay-option" data-method="fisico_usd">
                <input type="radio" name="metodoPago" value="fisico_usd">
                <span class="pay-opt-inner">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Físico ($)</span>
                </span>
            </label>
            <label class="pay-option" data-method="pago_movil">
                <input type="radio" name="metodoPago" value="pago_movil">
                <span class="pay-opt-inner">
                    <i class="fas fa-mobile-screen"></i>
                    <span>Pago Móvil</span>
                </span>
            </label>
            <label class="pay-option" data-method="mixto">
                <input type="radio" name="metodoPago" value="mixto">
                <span class="pay-opt-inner">
                    <i class="fas fa-layer-group"></i>
                    <span>Mixto</span>
                </span>
            </label>
        </div>

        <!-- Pago Móvil: referencia + comprobante -->
        <div id="pagoMovilSection" style="display:none;margin-top:14px">
            <div style="background:rgba(6,182,212,0.06);border:1px solid rgba(6,182,212,0.2);border-radius:12px;padding:16px">
                <div style="font-size:12px;font-weight:700;color:#06b6d4;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px">
                    <i class="fas fa-mobile-screen"></i> &nbsp;Datos del Pago Móvil
                </div>
                <div class="form-group mb-12">
                    <label class="form-label">Número de Referencia <span style="color:var(--danger)">*</span></label>
                    <input type="text" class="form-control" id="pmReferencia" placeholder="Ej: 00123456789" maxlength="100">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Captura / Comprobante</label>
                    <div id="pmUploadZone" onclick="document.getElementById('pmFile').click()"
                         style="border:2px dashed rgba(6,182,212,0.3);border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:all 0.2s;background:rgba(6,182,212,0.03)"
                         onmouseover="this.style.borderColor='#06b6d4'" onmouseout="this.style.borderColor='rgba(6,182,212,0.3)'">
                        <div id="pmUploadPlaceholder">
                            <i class="fas fa-image" style="font-size:22px;color:#06b6d4;margin-bottom:6px;display:block"></i>
                            <div style="font-size:12px;color:var(--text-muted)">Click para subir captura</div>
                            <div style="font-size:10px;color:var(--text-dim);margin-top:3px">PNG, JPG, WebP</div>
                        </div>
                        <img id="pmPreview" style="display:none;max-height:100px;border-radius:7px;margin:0 auto" src="" alt="">
                    </div>
                    <input type="file" id="pmFile" accept="image/png,image/jpeg,image/webp" style="display:none" onchange="previewPm(event)">
                    <button type="button" id="pmClearBtn" onclick="clearPm()" style="display:none;margin-top:6px;font-size:11px;color:var(--danger);background:none;border:none;cursor:pointer"><i class="fas fa-times"></i> Quitar imagen</button>
                </div>
            </div>
        </div>

        <!-- Mixto breakdown -->
        <div id="mixtoSection" style="display:none;margin-top:14px">
            <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:12px;padding:16px">
                <div style="font-size:12px;font-weight:700;color:var(--warning);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px">
                    <i class="fas fa-layer-group"></i> &nbsp;Desglose del pago mixto
                </div>
                <div id="mixtoItems"></div>
                <button type="button" class="btn btn-secondary btn-sm" onclick="addMixtoItem()" style="margin-top:10px">
                    <i class="fas fa-plus"></i> Agregar método
                </button>
                <div id="mixtoRestante" style="font-size:12px;color:var(--text-muted);margin-top:10px"></div>
            </div>
        </div>

        <hr class="separator">

        <!-- Productos/Servicios -->
        <div class="section-label d-flex justify-between align-center">
            <span>Productos / Servicios</span>
            <button class="btn btn-secondary btn-sm" onclick="addItem()"><i class="fas fa-plus"></i> Agregar</button>
        </div>
        <div id="itemsList" style="margin-top:12px"></div>

        <hr class="separator">

        <!-- Totales -->
        <div class="totals-box">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="displaySubtotal">0,00</span>
            </div>
            <div class="total-row">
                <span>Descuento</span>
                <div style="display:flex;align-items:center;gap:6px">
                    <input type="number" id="descuento" value="0" min="0" step="0.01"
                        class="discount-input" oninput="updateTotals()">
                </div>
            </div>
            <hr class="separator mb-0" style="margin:10px 0">
            <div class="total-row total-final">
                <span>Total</span>
                <span id="displayTotal">0,00</span>
            </div>
        </div>

        <div class="form-group mt-16 mb-0">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="notas" placeholder="Observaciones..." rows="2"></textarea>
        </div>

        <div style="display:flex;gap:10px;margin-top:18px">
            <button class="btn btn-secondary" onclick="clearForm()"><i class="fas fa-rotate-left"></i> Limpiar</button>
            <button class="btn btn-success btn-lg" style="flex:1" onclick="submitVenta()" id="btnSubmit">
                <i class="fas fa-check"></i> Registrar Venta
            </button>
        </div>
    </div>

    <!-- ── Resumen ── -->
    <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:80px">
        <div class="card">
            <div class="card-title mb-12"><i class="fas fa-receipt"></i> Resumen</div>
            <div id="resumenItems">
                <div class="empty-state" style="padding:20px">
                    <div class="empty-icon"><i class="fas fa-shopping-basket"></i></div>
                    <div class="empty-desc">Agrega productos</div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-title mb-12"><i class="fas fa-tags"></i> Categorías</div>
            <div id="categoriasRef">
                <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal éxito -->
<div class="modal-overlay" id="successModal">
    <div class="modal" style="max-width:420px;text-align:center">
        <div style="padding:36px 24px">
            <div class="success-icon-wrap"><i class="fas fa-check"></i></div>
            <div style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:6px">¡Venta Registrada!</div>
            <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px">Número</div>
            <div class="numero-venta-display" id="successNumero"></div>
            <div style="font-size:30px;font-weight:800;color:var(--success);margin:12px 0 24px" id="successTotal"></div>
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
                <button class="btn btn-secondary" onclick="closeSuccessModal()"><i class="fas fa-plus"></i> Nueva</button>
                <button class="btn btn-primary" onclick="openFactura()"><i class="fas fa-file-invoice"></i> Ver Factura</button>
                <a href="/historial.php" class="btn btn-secondary"><i class="fas fa-list"></i> Historial</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let categorias  = [];
let itemCount   = 0;
let mixtoCount  = 0;
let lastVentaId = null;

// ── Método de pago ──
document.querySelectorAll('.pay-option input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('active'));
        radio.closest('.pay-option').classList.add('active');
        const isMixto  = radio.value === 'mixto';
        const isPMovil = radio.value === 'pago_movil';
        document.getElementById('mixtoSection').style.display     = isMixto  ? 'block' : 'none';
        document.getElementById('pagoMovilSection').style.display = isPMovil ? 'block' : 'none';
        if (isMixto && document.getElementById('mixtoItems').children.length === 0) {
            addMixtoItem(); addMixtoItem();
        }
        updateMixtoRestante();
    });
});
// default activo
document.querySelector('.pay-option[data-method="fisico_bs"]').classList.add('active');

function previewPm(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('pmPreview').src = ev.target.result;
        document.getElementById('pmPreview').style.display = 'block';
        document.getElementById('pmUploadPlaceholder').style.display = 'none';
        document.getElementById('pmClearBtn').style.display = 'block';
    };
    reader.readAsDataURL(file);
}

function clearPm() {
    document.getElementById('pmFile').value = '';
    document.getElementById('pmPreview').src = '';
    document.getElementById('pmPreview').style.display = 'none';
    document.getElementById('pmUploadPlaceholder').style.display = 'block';
    document.getElementById('pmClearBtn').style.display = 'none';
}

function getMetodoPago() {
    return document.querySelector('.pay-option input:checked')?.value || 'fisico_bs';
}

// ── Mixto ──
const mixtoOpts = [
    {value:'fisico_bs', label:'Físico (Bs)'},
    {value:'fisico_usd', label:'Físico ($)'},
    {value:'pago_movil', label:'Pago Móvil'}
];

function addMixtoItem() {
    mixtoCount++;
    const id = mixtoCount;
    const opts = mixtoOpts.map(o => `<option value="${o.value}">${o.label}</option>`).join('');
    const div = document.createElement('div');
    div.id = `mixto-${id}`;
    div.style.cssText = 'display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:center;margin-bottom:8px';
    div.innerHTML = `
        <select class="form-control" id="mmetodo-${id}" onchange="updateMixtoRestante()">${opts}</select>
        <input type="number" class="form-control" id="mmonto-${id}" placeholder="Monto" min="0" step="0.01" oninput="updateMixtoRestante()">
        <button onclick="document.getElementById('mixto-${id}').remove();updateMixtoRestante()" style="background:rgba(239,68,68,0.1);border:none;color:#fca5a5;width:34px;height:34px;border-radius:8px;cursor:pointer;flex-shrink:0"><i class="fas fa-times"></i></button>
    `;
    document.getElementById('mixtoItems').appendChild(div);
}

function getMixtoData() {
    const items = [];
    document.querySelectorAll('[id^="mixto-"]').forEach(el => {
        const id = el.id.replace('mixto-','');
        const metodo = document.getElementById(`mmetodo-${id}`)?.value;
        const monto  = parseFloat(document.getElementById(`mmonto-${id}`)?.value || 0);
        if (metodo && monto > 0) items.push({metodo, monto});
    });
    return items;
}

function updateMixtoRestante() {
    const total  = parseFloat(document.getElementById('displayTotal').dataset.raw || 0);
    const mixtos = getMixtoData();
    const asignado = mixtos.reduce((a, m) => a + m.monto, 0);
    const resta  = total - asignado;
    const el     = document.getElementById('mixtoRestante');
    if (!el) return;
    if (Math.abs(resta) < 0.01) {
        el.innerHTML = `<span style="color:var(--success)"><i class="fas fa-check-circle"></i> Monto completo asignado</span>`;
    } else if (resta > 0) {
        el.innerHTML = `<span style="color:var(--warning)"><i class="fas fa-exclamation-triangle"></i> Faltan: <strong>${formatMoney(resta)}</strong></span>`;
    } else {
        el.innerHTML = `<span style="color:var(--danger)"><i class="fas fa-times-circle"></i> Excede en: <strong>${formatMoney(Math.abs(resta))}</strong></span>`;
    }
}

// ── Items ──
async function loadCategorias() {
    try {
        const data = await apiGet('/api/categorias.php');
        categorias = data.categorias;
        const ref = document.getElementById('categoriasRef');
        ref.innerHTML = categorias.map(c => `
            <div style="display:flex;align-items:center;gap:10px;padding:7px 10px;background:rgba(255,255,255,0.02);border-radius:8px;cursor:pointer" onclick="addItemCat(${c.id})">
                <i class="${c.icono}" style="color:${c.color};font-size:14px;width:16px;text-align:center;flex-shrink:0"></i>
                <span style="font-size:13px;color:var(--text-muted)">${c.nombre}</span>
                <i class="fas fa-plus-circle" style="margin-left:auto;color:var(--text-dim);font-size:11px"></i>
            </div>
        `).join('');
        addItem();
    } catch { showToast('Error al cargar categorías','error'); }
}

function addItemCat(catId) {
    addItem(catId);
}

function addItem(presetCat = null) {
    itemCount++;
    const id = itemCount;
    const catOptions = categorias.map(c => `<option value="${c.id}" ${c.id == presetCat ? 'selected' : ''}>${c.nombre}</option>`).join('');
    const div = document.createElement('div');
    div.id = `item-${id}`;
    div.className = 'item-card';
    div.innerHTML = `
        <div class="item-header">
            <span class="item-num">Ítem #${id}</span>
            <button onclick="removeItem(${id})" class="item-remove"><i class="fas fa-times"></i></button>
        </div>
        <div class="form-row-3" style="gap:10px">
            <div style="grid-column:span 2">
                <select class="form-control" id="cat-${id}" onchange="updateTotals()">
                    ${catOptions}
                </select>
            </div>
            <div></div>
            <div style="grid-column:1/-1">
                <input type="text" class="form-control" id="desc-${id}" placeholder="Descripción del trabajo...">
            </div>
            <div>
                <label class="form-label-mini">Cantidad</label>
                <input type="number" class="form-control" id="qty-${id}" value="1" min="1" oninput="updateTotals()">
            </div>
            <div>
                <label class="form-label-mini">Precio Unit.</label>
                <input type="number" class="form-control" id="price-${id}" value="0" min="0" step="0.01" oninput="updateTotals()">
            </div>
            <div>
                <label class="form-label-mini">Subtotal</label>
                <div class="subtotal-display" id="sub-${id}">0,00</div>
            </div>
        </div>
    `;
    document.getElementById('itemsList').appendChild(div);
    updateTotals();
    div.querySelector(`#desc-${id}`)?.focus();
}

function removeItem(id) {
    document.getElementById(`item-${id}`)?.remove();
    updateTotals();
}

function getItems() {
    const items = [];
    document.querySelectorAll('[id^="item-"]').forEach(el => {
        const id    = el.id.replace('item-','');
        const qty   = parseInt(document.getElementById(`qty-${id}`)?.value   || 0);
        const price = parseFloat(document.getElementById(`price-${id}`)?.value || 0);
        const catId = document.getElementById(`cat-${id}`)?.value;
        const desc  = document.getElementById(`desc-${id}`)?.value || '';
        if (qty > 0 && price > 0) items.push({categoria_id: catId, descripcion: desc, cantidad: qty, precio_unitario: price});
    });
    return items;
}

function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('[id^="item-"]').forEach(el => {
        const id  = el.id.replace('item-','');
        const qty = parseInt(document.getElementById(`qty-${id}`)?.value   || 0);
        const pr  = parseFloat(document.getElementById(`price-${id}`)?.value || 0);
        const sub = qty * pr;
        subtotal += sub;
        const subEl = document.getElementById(`sub-${id}`);
        if (subEl) subEl.textContent = formatMoney(sub);
    });
    const desc  = parseFloat(document.getElementById('descuento').value || 0);
    const total = Math.max(0, subtotal - desc);
    document.getElementById('displaySubtotal').textContent = formatMoney(subtotal);
    const totalEl = document.getElementById('displayTotal');
    totalEl.textContent    = formatMoney(total);
    totalEl.dataset.raw    = total;
    updateResumen();
    updateMixtoRestante();
}

function updateResumen() {
    const items = [];
    document.querySelectorAll('[id^="item-"]').forEach(el => {
        const id   = el.id.replace('item-','');
        const qty  = parseInt(document.getElementById(`qty-${id}`)?.value   || 0);
        const pr   = parseFloat(document.getElementById(`price-${id}`)?.value || 0);
        const catId= document.getElementById(`cat-${id}`)?.value;
        const desc = document.getElementById(`desc-${id}`)?.value || '';
        const cat  = categorias.find(c => c.id == catId);
        if (qty > 0 && pr > 0) items.push({desc: desc || cat?.nombre || 'Servicio', qty, sub: qty*pr, color: cat?.color||'#7c3aed', icono: cat?.icono||'fas fa-tag'});
    });
    const el = document.getElementById('resumenItems');
    if (!items.length) {
        el.innerHTML = `<div class="empty-state" style="padding:20px"><div class="empty-icon"><i class="fas fa-shopping-basket"></i></div><div class="empty-desc">Agrega productos</div></div>`;
        return;
    }
    el.innerHTML = `<div style="display:flex;flex-direction:column;gap:8px">` +
        items.map(it => `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:13px">
                <div style="display:flex;align-items:center;gap:7px;flex:1;min-width:0">
                    <i class="${it.icono}" style="color:${it.color};font-size:12px;flex-shrink:0"></i>
                    <span style="color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${it.desc} ×${it.qty}</span>
                </div>
                <span style="font-weight:700;flex-shrink:0">${formatMoney(it.sub)}</span>
            </div>
        `).join('') + `</div>`;
}

async function submitVenta() {
    const items = getItems();
    if (!items.length) return showToast('Agrega al menos un producto con precio mayor a 0','warning');

    const metodoPago = getMetodoPago();
    const pagoMixto  = metodoPago === 'mixto' ? getMixtoData() : [];

    if (metodoPago === 'mixto') {
        const total     = parseFloat(document.getElementById('displayTotal').dataset.raw || 0);
        const asignado  = pagoMixto.reduce((a,m) => a+m.monto, 0);
        if (Math.abs(total - asignado) > 0.01) return showToast('El desglose mixto no coincide con el total','warning');
        if (!pagoMixto.length) return showToast('Especifica al menos un método en el pago mixto','warning');
    }

    if (metodoPago === 'pago_movil') {
        const ref = document.getElementById('pmReferencia').value.trim();
        if (!ref) return showToast('Ingresa el número de referencia del pago móvil','warning');
    }

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    try {
        let data;
        if (metodoPago === 'pago_movil') {
            const fd = new FormData();
            fd.append('cliente',          document.getElementById('cliente').value);
            fd.append('cliente_cedula',   document.getElementById('clienteCedula').value);
            fd.append('cliente_telefono', document.getElementById('clienteTelefono').value);
            fd.append('metodo_pago',      metodoPago);
            fd.append('descuento',        document.getElementById('descuento').value || '0');
            fd.append('notas',            document.getElementById('notas').value);
            fd.append('referencia_pm',    document.getElementById('pmReferencia').value.trim());
            fd.append('items',            JSON.stringify(items));
            const pmImg = document.getElementById('pmFile').files[0];
            if (pmImg) fd.append('comprobante_pm', pmImg);
            const r = await fetch('/api/ventas.php', { method: 'POST', body: fd });
            data = await r.json();
        } else {
            data = await apiPost('/api/ventas.php', {
                cliente:          document.getElementById('cliente').value,
                cliente_cedula:   document.getElementById('clienteCedula').value,
                cliente_telefono: document.getElementById('clienteTelefono').value,
                metodo_pago:      metodoPago,
                descuento:        parseFloat(document.getElementById('descuento').value || 0),
                notas:            document.getElementById('notas').value,
                items, pago_mixto: pagoMixto
            });
        }
        if (data.success) {
            lastVentaId = data.id;
            document.getElementById('successNumero').textContent = data.numero_venta;
            document.getElementById('successTotal').textContent  = formatMoney(data.total_final);
            document.getElementById('successModal').classList.add('open');
        } else {
            showToast(data.error || 'Error al registrar','error');
        }
    } catch { showToast('Error de conexión','error'); }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Registrar Venta';
}

function openFactura() {
    if (lastVentaId) window.open(`/factura.php?id=${lastVentaId}`, '_blank');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.remove('open');
    clearForm();
}

function clearForm() {
    document.getElementById('clienteBuscar').value = '';
    document.getElementById('clienteSugerencias').style.display = 'none';
    document.getElementById('cliente').value = '';
    document.getElementById('clienteCedula').value = '';
    document.getElementById('clienteTelefono').value = '';
    document.getElementById('notas').value = '';
    document.getElementById('descuento').value = 0;
    document.querySelector('.pay-option input[value="fisico_bs"]').click();
    document.getElementById('itemsList').innerHTML = '';
    document.getElementById('mixtoItems').innerHTML = '';
    mixtoCount = 0; itemCount = 0;
    addItem();
    updateTotals();
}

// ── Client autocomplete ──
let clienteTimer;
async function buscarCliente() {
    clearTimeout(clienteTimer);
    const q = document.getElementById('clienteBuscar').value.trim();
    const box = document.getElementById('clienteSugerencias');
    if (q.length < 1) { box.style.display='none'; return; }
    clienteTimer = setTimeout(async () => {
        try {
            const d = await apiGet(`/api/clientes.php?buscar=${encodeURIComponent(q)}`);
            if (!d.clientes.length) { box.style.display='none'; return; }
            const tipoIcon = t => t==='empresa'?'fa-building':'fa-user';
            const tipoColor = t => t==='empresa'?'#f59e0b':'var(--primary-light)';
            box.innerHTML = d.clientes.map(c => `
                <div onclick="seleccionarCliente(${JSON.stringify(c).replace(/"/g,'&quot;')})"
                     style="padding:10px 14px;cursor:pointer;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--card-border);transition:background 0.15s"
                     onmouseover="this.style.background='rgba(124,58,237,0.1)'" onmouseout="this.style.background='transparent'">
                    <i class="fas ${tipoIcon(c.tipo)}" style="color:${tipoColor(c.tipo)};font-size:13px;flex-shrink:0"></i>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.nombre}</div>
                        <div style="font-size:11px;color:var(--text-dim)">${[c.cedula_rif,c.telefono].filter(Boolean).join(' · ') || '—'}</div>
                    </div>
                </div>
            `).join('');
            box.style.display = 'block';
        } catch {}
    }, 250);
}

function seleccionarCliente(c) {
    document.getElementById('cliente').value          = c.nombre       || '';
    document.getElementById('clienteCedula').value    = c.cedula_rif   || '';
    document.getElementById('clienteTelefono').value  = c.telefono     || '';
    document.getElementById('clienteBuscar').value    = '';
    document.getElementById('clienteSugerencias').style.display = 'none';
}

document.addEventListener('click', e => {
    if (!e.target.closest('#clienteBuscar') && !e.target.closest('#clienteSugerencias')) {
        document.getElementById('clienteSugerencias').style.display = 'none';
    }
});

loadCategorias();
</script>
