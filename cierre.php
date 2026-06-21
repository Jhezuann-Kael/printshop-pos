<?php
$pageTitle = 'Cierre del Día';
$activeMenu = 'cierre';
require_once __DIR__ . '/includes/layout.php';
?>

<div class="grid-col-2-1" style="align-items:start">
    <!-- Left: Day summary -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-calendar-check"></i> Cierre del Día</div>
            <div style="display:flex;align-items:center;gap:10px">
                <input type="date" id="fechaCierre" class="form-control" style="width:auto"
                    value="<?= date('Y-m-d') ?>" onchange="loadCierre()">
            </div>
        </div>

        <!-- Stats row -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px" id="cierreStats">
            <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:16px;text-align:center">
                <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
            </div>
            <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:16px;text-align:center">
                <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
            </div>
            <div style="background:rgba(255,255,255,0.03);border-radius:12px;padding:16px;text-align:center">
                <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
            </div>
        </div>

        <!-- By payment method -->
        <div style="margin-bottom:24px">
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:12px">
                <i class="fas fa-wallet" style="color:var(--primary-light)"></i> &nbsp;Desglose por Pago
            </div>
            <div id="desglosePago"></div>
        </div>

        <!-- By category -->
        <div style="margin-bottom:24px">
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:12px">
                <i class="fas fa-tags" style="color:var(--primary-light)"></i> &nbsp;Desglose por Categoría
            </div>
            <div id="desgloseCat"></div>
        </div>

        <!-- Ventas del día -->
        <div>
            <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:12px">
                <i class="fas fa-list" style="color:var(--primary-light)"></i> &nbsp;Ventas del Día
            </div>
            <div id="ventasDiaList"></div>
        </div>
    </div>

    <!-- Right: Close action -->
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card" id="cierreActionCard">
            <div class="card-title mb-16"><i class="fas fa-lock"></i> Cerrar Jornada</div>

            <div id="cierreStatus">
                <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-title mb-16"><i class="fas fa-book"></i> Último Resumen</div>
            <div id="ultimoCierre">
                <div style="color:var(--text-dim);font-size:13px;text-align:center;padding:16px">Cargando...</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let cierreData = null;

async function loadCierre() {
    const fecha = document.getElementById('fechaCierre').value;

    try {
        const data = await apiGet(`/api/cierre.php?fecha=${fecha}`);
        cierreData = data;

        // Stats
        const tot = data.totales;
        document.getElementById('cierreStats').innerHTML = `
            <div style="background:rgba(124,58,237,0.08);border:1px solid rgba(124,58,237,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:26px;font-weight:800;color:var(--primary-light)">${tot.total_ventas}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Ventas</div>
            </div>
            <div style="background:rgba(6,182,212,0.08);border:1px solid rgba(6,182,212,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:22px;font-weight:800;color:#67e8f9">${formatMoney(tot.monto_total)}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Total</div>
            </div>
            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:14px;font-weight:700;color:var(--success)">${data.cierre_existente ? '✓ CERRADO' : '⏳ ABIERTO'}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px;margin-top:4px">Estado</div>
            </div>
        `;

        // Desglose pago
        const pagoMap = { efectivo:['#10b981','fa-money-bill-wave','Efectivo'], transferencia:['#06b6d4','fa-building-columns','Transferencia'], tarjeta:['#7c3aed','fa-credit-card','Tarjeta'], mixto:['#f59e0b','fa-layer-group','Mixto'] };
        if (data.resumen_pago.length === 0) {
            document.getElementById('desglosePago').innerHTML = `<div style="color:var(--text-dim);font-size:13px;text-align:center;padding:16px">Sin ventas este día</div>`;
        } else {
            document.getElementById('desglosePago').innerHTML = `<div style="display:flex;flex-direction:column;gap:8px">` +
                data.resumen_pago.map(r => {
                    const [color, icon, label] = pagoMap[r.metodo_pago] || ['#fff','fa-question',r.metodo_pago];
                    const pct = tot.monto_total > 0 ? (r.monto / tot.monto_total * 100).toFixed(1) : 0;
                    return `<div style="display:flex;align-items:center;gap:12px;padding:12px;background:rgba(255,255,255,0.03);border-radius:10px">
                        <i class="fas ${icon}" style="color:${color};font-size:16px;width:20px;text-align:center"></i>
                        <div style="flex:1">
                            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                                <span style="font-size:13px;font-weight:500">${label}</span>
                                <span style="font-size:13px;font-weight:700">${formatMoney(r.monto)}</span>
                            </div>
                            <div style="background:rgba(255,255,255,0.06);border-radius:4px;height:4px;overflow:hidden">
                                <div style="height:100%;border-radius:4px;background:${color};width:${pct}%;transition:width 0.5s"></div>
                            </div>
                        </div>
                    </div>`;
                }).join('') + `</div>`;
        }

        // Desglose categoría
        if (data.resumen_categoria.length === 0) {
            document.getElementById('desgloseCat').innerHTML = `<div style="color:var(--text-dim);font-size:13px;text-align:center;padding:16px">Sin ventas</div>`;
        } else {
            document.getElementById('desgloseCat').innerHTML = `<div style="display:flex;flex-direction:column;gap:6px">` +
                data.resumen_categoria.map(c => `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,0.02);border-radius:8px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="${c.icono}" style="color:${c.color};font-size:13px;width:16px;text-align:center"></i>
                            <span style="font-size:13px;color:var(--text-muted)">${c.nombre}</span>
                            <span style="font-size:11px;color:var(--text-dim)">(${c.ventas_count} venta${c.ventas_count != 1 ? 's' : ''})</span>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:var(--text)">${formatMoney(c.monto)}</span>
                    </div>
                `).join('') + `</div>`;
        }

        // Ventas del día
        if (data.ventas.length === 0) {
            document.getElementById('ventasDiaList').innerHTML = `<div class="empty-state" style="padding:20px"><div class="empty-icon"><i class="fas fa-receipt"></i></div><div class="empty-desc">Sin ventas este día</div></div>`;
        } else {
            document.getElementById('ventasDiaList').innerHTML = `<div class="table-wrapper">
                <table>
                    <thead><tr><th># Venta</th><th>Cliente</th><th>Total</th><th>Pago</th><th>Hora</th></tr></thead>
                    <tbody>${data.ventas.map(v => `<tr>
                        <td><span class="font-mono" style="color:var(--primary-light)">${v.numero_venta}</span></td>
                        <td>${v.cliente || '<span style="color:var(--text-dim)">—</span>'}</td>
                        <td><strong>${formatMoney(v.total_final)}</strong></td>
                        <td>${payBadge(v.metodo_pago)}</td>
                        <td><span style="font-size:11px;color:var(--text-dim)">${new Date(v.creado_en).toLocaleTimeString('es-GT',{hour:'2-digit',minute:'2-digit'})}</span></td>
                    </tr>`).join('')}</tbody>
                </table>
            </div>`;
        }

        // Cierre action
        renderCierreAction(data);

    } catch (e) {
        showToast('Error al cargar el cierre', 'error');
    }
}

function renderCierreAction(data) {
    const status = document.getElementById('cierreStatus');
    const hoy = document.getElementById('fechaCierre').value === new Date().toISOString().split('T')[0];

    if (data.cierre_existente) {
        const c = data.cierre_existente;
        status.innerHTML = `
            <div style="text-align:center;padding:16px 0">
                <div style="width:56px;height:56px;background:rgba(16,185,129,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;color:var(--success)">
                    <i class="fas fa-check-double"></i>
                </div>
                <div style="font-size:15px;font-weight:700;color:var(--success);margin-bottom:4px">Cierre Realizado</div>
                <div style="font-size:12px;color:var(--text-dim)">Cerrado por ${c.cerrado_por || '—'}</div>
                <div style="margin-top:16px;padding:14px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.15);border-radius:10px">
                    <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;letter-spacing:0.5px">Total del Día</div>
                    <div style="font-size:22px;font-weight:800;color:var(--success);margin-top:4px">${formatMoney(c.monto_total)}</div>
                    <div style="font-size:12px;color:var(--text-dim);margin-top:2px">${c.total_ventas} venta${c.total_ventas != 1 ? 's' : ''}</div>
                </div>
            </div>`;
    } else if (data.totales.total_ventas == 0) {
        status.innerHTML = `
            <div style="text-align:center;padding:16px 0">
                <div style="font-size:36px;opacity:.3;margin-bottom:12px"><i class="fas fa-store-slash"></i></div>
                <div style="font-size:14px;color:var(--text-muted)">Sin ventas para cerrar en esta fecha</div>
            </div>`;
    } else {
        status.innerHTML = `
            <div>
                <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.15);border-radius:10px;padding:14px;margin-bottom:16px">
                    <div style="font-size:12px;color:var(--warning);font-weight:600;margin-bottom:8px"><i class="fas fa-triangle-exclamation"></i> &nbsp;Resumen antes de cerrar</div>
                    <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px">Total ventas: <strong style="color:var(--text)">${data.totales.total_ventas}</strong></div>
                    <div style="font-size:13px;color:var(--text-muted)">Monto total: <strong style="color:var(--text);font-size:15px">${formatMoney(data.totales.monto_total)}</strong></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notas del cierre</label>
                    <textarea class="form-control" id="notasCierre" rows="3" placeholder="Observaciones opcionales..."></textarea>
                </div>
                <button class="btn btn-success w-full btn-lg" onclick="hacerCierre()" id="btnCierre">
                    <i class="fas fa-lock"></i> Realizar Cierre
                </button>
            </div>`;
    }
}

async function hacerCierre() {
    const ok = await confirmAction(`¿Confirmar el cierre del día ${document.getElementById('fechaCierre').value}? Esta acción no se puede deshacer.`);
    if (!ok) return;

    const btn = document.getElementById('btnCierre');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cerrando...';

    try {
        const data = await apiPost('/api/cierre.php', {
            fecha: document.getElementById('fechaCierre').value,
            notas: document.getElementById('notasCierre')?.value || ''
        });

        if (data.success) {
            showToast('¡Cierre realizado exitosamente!', 'success');
            loadCierre();
        } else {
            showToast(data.error || 'Error al realizar el cierre', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-lock"></i> Realizar Cierre';
        }
    } catch {
        showToast('Error de conexión', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Realizar Cierre';
    }
}

loadCierre();
</script>
