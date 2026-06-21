<?php
$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
$extraHead  = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
require_once __DIR__ . '/includes/layout.php';
$isAdmin = $user['rol'] === 'admin';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card purple" style="animation-delay:.04s">
        <div class="stat-icon"><i class="fas fa-cart-shopping"></i></div>
        <div class="stat-value" id="sVentasHoy">—</div>
        <div class="stat-label">Ventas Hoy</div>
    </div>
    <div class="stat-card cyan" style="animation-delay:.09s">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-value" id="sMontoHoy">—</div>
        <div class="stat-label">Ingresos Hoy</div>
    </div>
    <div class="stat-card amber" style="animation-delay:.14s">
        <div class="stat-icon"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-value" id="sVentasMes">—</div>
        <div class="stat-label">Ventas del Mes</div>
    </div>
    <div class="stat-card green" style="animation-delay:.19s">
        <div class="stat-icon"><i class="fas fa-sack-dollar"></i></div>
        <div class="stat-value" id="sMontoMes">—</div>
        <div class="stat-label">Ingresos del Mes</div>
    </div>
</div>

<!-- Charts row -->
<div class="grid-col-2-1" style="margin-bottom:18px">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line"></i> Ventas — Últimos 7 días</div>
            <?php if ($isAdmin): ?>
            <a href="/reportes.php" class="btn btn-secondary btn-sm"><i class="fas fa-expand"></i> Ver más</a>
            <?php endif; ?>
        </div>
        <canvas id="chartSemana" height="100"></canvas>
    </div>
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-donut"></i> Por Categoría</div>
        </div>
        <div style="position:relative;height:160px;display:flex;align-items:center;justify-content:center">
            <canvas id="chartCat"></canvas>
            <div id="chartCatEmpty" style="display:none;color:var(--text-dim);font-size:13px">Sin datos</div>
        </div>
        <div id="catLeyenda" style="margin-top:10px;display:flex;flex-direction:column;gap:5px"></div>
    </div>
</div>

<!-- Bottom row -->
<div class="grid-2">
    <!-- Métodos de pago -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-wallet"></i> Métodos de Pago (Mes)</div>
        </div>
        <div id="metodosGrid" style="display:grid;grid-template-columns:1fr 1fr;gap:10px"></div>
    </div>

    <!-- Últimas ventas -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-receipt"></i> Últimas Ventas</div>
            <a href="/historial.php" class="btn btn-secondary btn-sm">Ver todas</a>
        </div>
        <div id="ultimasVentas">
            <div class="loading-dots" style="justify-content:center;padding:20px"><span></span><span></span><span></span></div>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Admin extra: cierre alert -->
<div id="cierreAlert" style="display:none;margin-top:18px">
    <div style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.2);border-radius:14px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:12px">
            <i class="fas fa-triangle-exclamation" style="color:var(--warning);font-size:20px;flex-shrink:0"></i>
            <div>
                <div style="font-weight:600">No has realizado el cierre del día</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px">Hay ventas registradas pendientes de cierre</div>
            </div>
        </div>
        <a href="/cierre.php" class="btn btn-primary btn-sm"><i class="fas fa-calendar-check"></i> Hacer Cierre</a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let semChart = null, catChart = null;

async function loadDashboard() {
    try {
        const d = await apiGet('/api/dashboard.php');

        // Stats with animation
        document.getElementById('sVentasHoy').textContent = d.hoy.total_ventas;
        document.getElementById('sMontoHoy').textContent  = formatMoney(d.hoy.monto_total);
        document.getElementById('sVentasMes').textContent = d.mes.total;
        document.getElementById('sMontoMes').textContent  = formatMoney(d.mes.monto);

        <?php if ($isAdmin): ?>
        if (!d.cierre_hoy && d.hoy.total_ventas > 0) {
            document.getElementById('cierreAlert').style.display = 'block';
        }
        <?php endif; ?>

        // Semana chart
        const semlabels = d.semana.map(r => {
            const dt = new Date(r.fecha+'T12:00:00');
            return dt.toLocaleDateString('es-VE',{weekday:'short',day:'numeric'});
        });
        const semMontos = d.semana.map(r => parseFloat(r.monto));

        if (semChart) semChart.destroy();
        semChart = new Chart(document.getElementById('chartSemana'), {
            type: 'bar',
            data: {
                labels: semlabels,
                datasets: [{
                    data: semMontos,
                    backgroundColor: ctx => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                        g.addColorStop(0, 'rgba(139,92,246,0.85)');
                        g.addColorStop(1, 'rgba(124,58,237,0.15)');
                        return g;
                    },
                    borderColor: 'rgba(167,139,250,0.8)',
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false,
                    hoverBackgroundColor: ctx => {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                        g.addColorStop(0, 'rgba(167,139,250,0.95)');
                        g.addColorStop(1, 'rgba(124,58,237,0.3)');
                        return g;
                    }
                }]
            },
            options: {
                responsive: true,
                animation: { duration: 900, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15,15,30,0.95)',
                        borderColor: 'rgba(124,58,237,0.4)',
                        borderWidth: 1,
                        titleColor: '#a78bfa',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: { label: ctx => ' $ ' + ctx.raw.toLocaleString('es-VE',{minimumFractionDigits:2}) }
                    }
                },
                scales: {
                    x: { grid:{color:'rgba(255,255,255,0.03)'}, ticks:{color:'#475569',font:{size:10,weight:'600'}} },
                    y: { grid:{color:'rgba(255,255,255,0.04)',dash:[4,4]}, ticks:{color:'#475569',font:{size:10}, callback:v=>'$'+v} }
                }
            }
        });

        // Categorías chart
        const cats = d.por_categoria.filter(c => parseFloat(c.monto_total) > 0);
        if (cats.length) {
            // Center text plugin for doughnut
            const totalCat = cats.reduce((a,c)=>a+parseFloat(c.monto_total),0);
            const centerPlugin = {
                id:'centerText',
                afterDraw(chart) {
                    const {ctx,chartArea:{top,bottom,left,right}} = chart;
                    const cx=(left+right)/2, cy=(top+bottom)/2;
                    ctx.save();
                    ctx.textAlign='center'; ctx.textBaseline='middle';
                    ctx.fillStyle='#a78bfa'; ctx.font='bold 11px Inter,sans-serif';
                    ctx.fillText('TOTAL', cx, cy-10);
                    ctx.fillStyle='#e2e8f0'; ctx.font='bold 13px Inter,sans-serif';
                    ctx.fillText('$'+totalCat.toLocaleString('es-VE',{minimumFractionDigits:0,maximumFractionDigits:0}), cx, cy+8);
                    ctx.restore();
                }
            };
            if (catChart) catChart.destroy();
            catChart = new Chart(document.getElementById('chartCat'), {
                type: 'doughnut',
                plugins: [centerPlugin],
                data: {
                    labels: cats.map(c=>c.nombre),
                    datasets: [{ data: cats.map(c=>parseFloat(c.monto_total)), backgroundColor: cats.map(c=>c.color+'bb'), borderColor: cats.map(c=>c.color), borderWidth: 2, hoverOffset: 10, hoverBorderWidth: 3 }]
                },
                options: {
                    responsive: true,
                    cutout: '74%',
                    animation: { animateRotate: true, duration: 900 },
                    plugins: {
                        legend: {display:false},
                        tooltip: {
                            backgroundColor: 'rgba(15,15,30,0.95)',
                            borderColor: 'rgba(124,58,237,0.4)',
                            borderWidth: 1,
                            titleColor: '#a78bfa',
                            bodyColor: '#e2e8f0',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: { label: ctx=>ctx.label+': $'+ctx.raw.toLocaleString('es-VE',{minimumFractionDigits:2}) }
                        }
                    }
                }
            });
            document.getElementById('catLeyenda').innerHTML = cats.map(c=>`
                <div style="display:flex;justify-content:space-between;font-size:12px;align-items:center">
                    <span style="display:flex;align-items:center;gap:6px">
                        <span style="width:8px;height:8px;border-radius:3px;background:${c.color};display:inline-block;flex-shrink:0"></span>
                        <span style="color:var(--text-muted)">${c.nombre}</span>
                    </span>
                    <span style="font-weight:600;color:var(--text)">${formatMoney(c.monto_total)}</span>
                </div>
            `).join('');
        } else {
            document.getElementById('chartCatEmpty').style.display='block';
        }

        // Métodos de pago
        const payCfg = {
            fisico_bs:  { icon:'fa-money-bill-wave', color:'#10b981', label:'Físico (Bs)' },
            fisico_usd: { icon:'fa-dollar-sign',     color:'#f59e0b', label:'Físico ($)' },
            pago_movil: { icon:'fa-mobile-screen',   color:'#06b6d4', label:'Pago Móvil' },
            mixto:      { icon:'fa-layer-group',     color:'#7c3aed', label:'Mixto' }
        };
        const mg = document.getElementById('metodosGrid');
        if (!d.metodos_pago.length) {
            mg.innerHTML = '<div class="empty-state" style="padding:16px;grid-column:1/-1"><div class="empty-desc">Sin ventas este mes</div></div>';
        } else {
            mg.innerHTML = d.metodos_pago.map(m => {
                const cfg = payCfg[m.metodo_pago] || {icon:'fa-question',color:'#fff',label:m.metodo_pago};
                return `<div style="background:rgba(255,255,255,0.03);border:1px solid var(--card-border);border-radius:12px;padding:14px;transition:all 0.2s" onmouseover="this.style.borderColor='${cfg.color}44'" onmouseout="this.style.borderColor='var(--card-border)'">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <i class="fas ${cfg.icon}" style="color:${cfg.color};font-size:14px"></i>
                        <span style="font-size:11px;color:var(--text-muted);font-weight:600">${cfg.label}</span>
                    </div>
                    <div style="font-size:17px;font-weight:800;color:var(--text)">$ ${formatMoney(m.monto)}</div>
                    <div style="font-size:10px;color:var(--text-dim);margin-top:2px">${m.cantidad} venta${m.cantidad!=1?'s':''}</div>
                </div>`;
            }).join('');
        }

        // Últimas ventas
        const uv = document.getElementById('ultimasVentas');
        if (!d.ultimas_ventas.length) {
            uv.innerHTML = `<div class="empty-state"><div class="empty-icon"><i class="fas fa-receipt"></i></div><div class="empty-title">Sin ventas aún</div></div>`;
        } else {
            uv.innerHTML = `<div style="display:flex;flex-direction:column;gap:7px">` +
                d.ultimas_ventas.map(v=>`
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:rgba(255,255,255,0.02);border-radius:10px;border:1px solid var(--card-border);gap:12px;transition:border-color 0.2s" onmouseover="this.style.borderColor='rgba(124,58,237,0.25)'" onmouseout="this.style.borderColor='var(--card-border)'">
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${v.cliente||'Cliente'}</div>
                            <div style="font-size:10px;color:var(--text-dim);margin-top:2px">${v.numero_venta} · ${formatDateTime(v.creado_en)}</div>
                        </div>
                        <div style="text-align:right;flex-shrink:0">
                            <div style="font-size:14px;font-weight:700">${formatMoney(v.total_final)}</div>
                            ${payBadge(v.metodo_pago)}
                        </div>
                    </div>
                `).join('') + '</div>';
        }

    } catch { showToast('Error al cargar el dashboard','error'); }
}

loadDashboard();
</script>
