/* PrintShop — App Global JS | Venezuela */

// ── DateTime (Venezuela UTC-4) ──
function updateDateTime() {
    const el = document.getElementById('currentDateTime');
    if (!el) return;
    const now  = new Date();
    const opts = { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit', timeZone:'America/Caracas' };
    el.textContent = now.toLocaleString('es-VE', opts);
}
updateDateTime();
setInterval(updateDateTime, 30000);

// ── BCV Rate in topbar ──
async function loadBcvTopbar() {
    try {
        const d = await fetch('/api/bcv.php').then(r => r.json());
        if (d.ok && d.tasa) {
            const badge = document.getElementById('bcvBadge');
            const rate  = document.getElementById('bcvRate');
            if (badge && rate) {
                rate.textContent = 'Bs ' + parseFloat(d.tasa).toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2});
                badge.style.display = 'flex';
            }
        }
    } catch {}
}
loadBcvTopbar();

// ── Sidebar ──
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebarOverlay');
    sb.classList.toggle('open');
    ov.classList.toggle('open');
}

// ── Logout ──
async function logout() {
    await fetch('/api/logout.php', { method:'POST' });
    window.location.href = '/index.php';
}

// ── Toasts ──
const _toastContainer = (() => {
    let c = document.getElementById('toastContainer');
    if (!c) { c = document.createElement('div'); c.id='toastContainer'; c.className='toast-container'; document.body.appendChild(c); }
    return c;
})();

function showToast(message, type='info', duration=3500) {
    const icons = { success:'fa-circle-check', error:'fa-circle-xmark', info:'fa-circle-info', warning:'fa-triangle-exclamation' };
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `<i class="fas ${icons[type]||'fa-circle-info'} toast-icon"></i><span>${message}</span>`;
    _toastContainer.appendChild(t);
    setTimeout(() => {
        t.classList.add('hide');
        setTimeout(() => t.remove(), 350);
    }, duration);
}

// ── Format money ──
function formatMoney(amount) {
    return parseFloat(amount||0).toLocaleString('es-VE', { minimumFractionDigits:2, maximumFractionDigits:2 });
}

// ── Format date/time ──
function formatDate(s) {
    if (!s) return '—';
    return new Date(s).toLocaleDateString('es-VE',{day:'2-digit',month:'short',year:'numeric',timeZone:'America/Caracas'});
}
function formatDateTime(s) {
    if (!s) return '—';
    return new Date(s).toLocaleString('es-VE',{day:'2-digit',month:'short',hour:'2-digit',minute:'2-digit',timeZone:'America/Caracas'});
}

// ── API helpers ──
async function apiGet(url) {
    const r = await fetch(url, { headers:{'Accept':'application/json'} });
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
}
async function apiPost(url, data) {
    const r = await fetch(url, {
        method:'POST',
        headers:{'Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify(data)
    });
    return r.json();
}
async function apiDelete(url) {
    const r = await fetch(url, { method:'DELETE', headers:{'Accept':'application/json'} });
    return r.json();
}

// ── Payment badge ──
function payBadge(method) {
    const map = {
        fisico_bs:  ['fa-money-bill-wave','pay-fisico_bs','Físico (Bs)'],
        fisico_usd: ['fa-dollar-sign','pay-fisico_usd','Físico ($)'],
        pago_movil: ['fa-mobile-screen','pay-pago_movil','Pago Móvil'],
        mixto:      ['fa-layer-group','pay-mixto','Mixto']
    };
    const [icon, cls, label] = map[method] || ['fa-question','pay-fisico_bs', method];
    return `<span class="pay-badge ${cls}"><i class="fas ${icon}"></i>${label}</span>`;
}

// ── Status badge ──
function statusBadge(estado) {
    const cls = { completada:'badge-success', cancelada:'badge-danger', pendiente:'badge-warning' };
    const lbl = { completada:'Completada', cancelada:'Cancelada', pendiente:'Pendiente' };
    return `<span class="badge ${cls[estado]||'badge-gray'}">${lbl[estado]||estado}</span>`;
}

// ── Confirm dialog ──
function confirmAction(msg) {
    return new Promise(resolve => {
        const el = document.createElement('div');
        el.className = 'modal-overlay open';
        el.innerHTML = `
            <div class="modal" style="max-width:380px">
                <div class="modal-header">
                    <div class="modal-title"><i class="fas fa-exclamation-triangle" style="color:var(--warning)"></i> Confirmar</div>
                </div>
                <div class="modal-body">
                    <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:22px;line-height:1.6">${msg}</p>
                    <div style="display:flex;gap:10px;justify-content:flex-end">
                        <button class="btn btn-secondary" id="cNo">Cancelar</button>
                        <button class="btn btn-danger" id="cYes">Confirmar</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(el);
        el.querySelector('#cYes').onclick = () => { el.remove(); resolve(true); };
        el.querySelector('#cNo').onclick  = () => { el.remove(); resolve(false); };
        el.addEventListener('click', e => { if(e.target===el){ el.remove(); resolve(false); } });
    });
}

// ── Number counter animation ──
function animateCount(el, from, to, duration=600) {
    const start = performance.now();
    const isFloat = String(to).includes('.');
    function step(ts) {
        const pct = Math.min((ts-start)/duration, 1);
        const val = from + (to-from)*easeOut(pct);
        el.textContent = isFloat ? formatMoney(val) : Math.round(val);
        if (pct < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}
function easeOut(t) { return 1 - Math.pow(1-t, 3); }
