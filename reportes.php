<?php
$pageTitle = 'Reportes';
$activeMenu = 'reportes';
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
require_once __DIR__ . '/includes/layout.php';
if ($user['rol'] !== 'admin') { header('Location: /dashboard.php'); exit; }
?>

<!-- Tabs -->
<div class="tab-bar mb-20">
    <button class="tab-btn active" data-tab="resumen" onclick="switchTab('resumen', this)">
        <i class="fas fa-chart-pie"></i> Resumen
    </button>
    <button class="tab-btn" data-tab="semanal" onclick="switchTab('semanal', this)">
        <i class="fas fa-calendar-week"></i> Semanal
    </button>
    <button class="tab-btn" data-tab="mensual" onclick="switchTab('mensual', this)">
        <i class="fas fa-calendar"></i> Mensual
    </button>
    <button class="tab-btn" data-tab="anual" onclick="switchTab('anual', this)">
        <i class="fas fa-chart-bar"></i> Anual
    </button>
    <button class="tab-btn" data-tab="trabajadores" onclick="switchTab('trabajadores', this)">
        <i class="fas fa-users"></i> Trabajadores
    </button>
</div>

<!-- Resumen -->
<div id="tab-resumen">
    <div class="stats-grid mb-20">
        <div class="stat-card purple" style="animation-delay:.05s">
            <div class="stat-icon"><i class="fas fa-sun"></i></div>
            <div class="stat-value" id="r-dia-ventas">—</div>
            <div class="stat-label">Ventas Hoy</div>
            <div class="stat-change up" id="r-dia-monto"></div>
        </div>
        <div class="stat-card cyan" style="animation-delay:.1s">
            <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
            <div class="stat-value" id="r-sem-ventas">—</div>
            <div class="stat-label">Esta Semana</div>
            <div class="stat-change up" id="r-sem-monto"></div>
        </div>
        <div class="stat-card amber" style="animation-delay:.15s">
            <div class="stat-icon"><i class="fas fa-calendar"></i></div>
            <div class="stat-value" id="r-mes-ventas">—</div>
            <div class="stat-label">Este Mes</div>
            <div class="stat-change up" id="r-mes-monto"></div>
        </div>
        <div class="stat-card green" style="animation-delay:.2s">
            <div class="stat-icon"><i class="fas fa-trophy"></i></div>
            <div class="stat-value" id="r-ano-ventas">—</div>
            <div class="stat-label">Este Año</div>
            <div class="stat-change up" id="r-ano-monto"></div>
        </div>
    </div>
</div>

<!-- Semanal -->
<div id="tab-semanal" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-calendar-week"></i> Últimos 7 días</div>
        </div>
        <canvas id="chartSemanal" height="80"></canvas>
        <div id="tablaSemanal" style="margin-top:20px"></div>
    </div>
</div>

<!-- Mensual -->
<div id="tab-mensual" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-calendar"></i> Reporte Mensual</div>
            <div style="display:flex;gap:8px">
                <select id="selectMes" class="form-control" style="width:130px">
                    <?php
                    $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    $mesActual = (int)date('n');
                    foreach ($meses as $i => $m) {
                        $sel = ($i+1 === $mesActual) ? 'selected' : '';
                        echo "<option value='".($i+1)."' $sel>$m</option>";
                    }
                    ?>
                </select>
                <select id="selectAnio" class="form-control" style="width:90px">
                    <?php for ($y = date('Y'); $y >= date('Y')-3; $y--) echo "<option value='$y'>$y</option>"; ?>
                </select>
                <button class="btn btn-primary btn-sm" onclick="loadMensual()"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <canvas id="chartMensual" height="80"></canvas>
        <div id="tablaMensual" style="margin-top:20px"></div>
    </div>
</div>

<!-- Anual -->
<div id="tab-anual" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar"></i> Reporte Anual</div>
            <select id="selectAnioAnual" class="form-control" style="width:90px" onchange="loadAnual()">
                <?php for ($y = date('Y'); $y >= date('Y')-4; $y--) echo "<option value='$y'>$y</option>"; ?>
            </select>
        </div>
        <canvas id="chartAnual" height="80"></canvas>
        <div id="tablaAnual" style="margin-top:20px"></div>
    </div>
</div>

<!-- Trabajadores -->
<div id="tab-trabajadores" style="display:none">
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-users"></i> Rendimiento por Trabajador</div>
        </div>
        <div id="tablaTrabajadores">
            <div class="loading-dots" style="justify-content:center;padding:32px"><span></span><span></span><span></span></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
let charts = {};

function switchTab(tab, el) {
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(`tab-${tab}`).style.display = 'block';
    el.classList.add('active');

    if (tab === 'resumen')      loadResumen();
    if (tab === 'semanal')      loadSemanal();
    if (tab === 'mensual')      loadMensual();
    if (tab === 'anual')        loadAnual();
    if (tab === 'trabajadores') loadTrabajadores();
}

async function loadResumen() {
    try {
        const d = await apiGet('/api/reportes.php?tipo=resumen');
        document.getElementById('r-dia-ventas').textContent = d.dia.t;
        document.getElementById('r-dia-monto').textContent = formatMoney(d.dia.m);
        document.getElementById('r-sem-ventas').textContent = d.semana.t;
        document.getElementById('r-sem-monto').textContent = formatMoney(d.semana.m);
        document.getElementById('r-mes-ventas').textContent = d.mes.t;
        document.getElementById('r-mes-monto').textContent = formatMoney(d.mes.m);
        document.getElementById('r-ano-ventas').textContent = d.anio.t;
        document.getElementById('r-ano-monto').textContent = formatMoney(d.anio.m);
    } catch { showToast('Error al cargar resumen','error'); }
}

async function loadSemanal() {
    const d = await apiGet('/api/reportes.php?tipo=semanal');
    const labels = d.semana.map(r => new Date(r.fecha+'T12:00:00').toLocaleDateString('es-VE',{weekday:'short',day:'numeric',month:'short'}));
    const montos = d.semana.map(r => parseFloat(r.monto));

    if (charts.semanal) charts.semanal.destroy();
    charts.semanal = new Chart(document.getElementById('chartSemanal'), {
        type: 'bar',
        data: { labels, datasets: [{ label:'Ventas', data: montos, backgroundColor:'rgba(124,58,237,0.6)', borderColor:'#7c3aed', borderWidth:2, borderRadius:8, borderSkipped:false }] },
        options: chartOpts()
    });

    document.getElementById('tablaSemanal').innerHTML = tableHTML(
        ['Fecha','Ventas','Monto'],
        d.semana.map(r => [new Date(r.fecha+'T12:00:00').toLocaleDateString('es-VE',{weekday:'long',day:'numeric',month:'long'}), `<span class="badge badge-primary">${r.ventas}</span>`, `<strong>${formatMoney(r.monto)}</strong>`])
    );
}

async function loadMensual() {
    const mes  = document.getElementById('selectMes').value;
    const anio = document.getElementById('selectAnio').value;
    const d    = await apiGet(`/api/reportes.php?tipo=mensual&mes=${mes}&anio=${anio}`);

    const labels = d.por_dia.map(r => `Día ${r.dia}`);
    const montos = d.por_dia.map(r => parseFloat(r.monto));

    if (charts.mensual) charts.mensual.destroy();
    charts.mensual = new Chart(document.getElementById('chartMensual'), {
        type: 'line',
        data: { labels, datasets: [{ label:'Ventas', data: montos, borderColor:'#06b6d4', backgroundColor:'rgba(6,182,212,0.1)', borderWidth:2, fill:true, tension:0.4, pointRadius:4 }] },
        options: chartOpts()
    });

    document.getElementById('tablaMensual').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div style="background:rgba(124,58,237,0.08);border:1px solid rgba(124,58,237,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:24px;font-weight:800;color:var(--primary-light)">${d.total.total}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;margin-top:4px">Total Ventas</div>
            </div>
            <div style="background:rgba(6,182,212,0.08);border:1px solid rgba(6,182,212,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:22px;font-weight:800;color:#67e8f9">${formatMoney(d.total.monto)}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;margin-top:4px">Monto Total</div>
            </div>
        </div>` +
        tableHTML(['Día','Ventas','Monto'], d.por_dia.map(r => [`Día ${r.dia}`, r.ventas, `<strong>${formatMoney(r.monto)}</strong>`]));
}

async function loadAnual() {
    const anio = document.getElementById('selectAnioAnual').value;
    const d    = await apiGet(`/api/reportes.php?tipo=anual&anio=${anio}`);
    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const full  = Array.from({length:12}, (_,i) => {
        const r = d.por_mes.find(x => x.mes == i+1);
        return { mes: meses[i], ventas: r?.ventas||0, monto: parseFloat(r?.monto||0) };
    });

    if (charts.anual) charts.anual.destroy();
    charts.anual = new Chart(document.getElementById('chartAnual'), {
        type: 'bar',
        data: {
            labels: full.map(x=>x.mes),
            datasets: [
                { label:'Ventas', data: full.map(x=>x.ventas), backgroundColor:'rgba(124,58,237,0.6)', borderColor:'#7c3aed', borderWidth:2, borderRadius:6, yAxisID:'y1' },
                { label:'Monto', data: full.map(x=>x.monto), type:'line', borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,0.1)', borderWidth:2, fill:true, tension:0.4, pointRadius:4, yAxisID:'y2' }
            ]
        },
        options: {
            responsive:true,
            plugins: { legend: { labels: { color:'#94a3b8', font:{size:12} } }, tooltip: { callbacks: { label: ctx => ctx.datasetIndex===1 ? 'Q '+ctx.raw.toLocaleString('es-VE',{minimumFractionDigits:2}) : ctx.raw+' ventas' } } },
            scales: {
                x: { grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#64748b'} },
                y1: { position:'left', grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#64748b'} },
                y2: { position:'right', grid:{display:false}, ticks:{color:'#f59e0b', callback: v=>'Q'+v} }
            }
        }
    });

    document.getElementById('tablaAnual').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div style="background:rgba(124,58,237,0.08);border:1px solid rgba(124,58,237,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:24px;font-weight:800;color:var(--primary-light)">${d.total.total}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;margin-top:4px">Ventas ${anio}</div>
            </div>
            <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.15);border-radius:12px;padding:16px;text-align:center">
                <div style="font-size:22px;font-weight:800;color:var(--accent)">${formatMoney(d.total.monto)}</div>
                <div style="font-size:11px;color:var(--text-dim);text-transform:uppercase;margin-top:4px">Total ${anio}</div>
            </div>
        </div>` +
        tableHTML(['Mes','Ventas','Monto'], full.map(r => [r.mes, `<span class="badge badge-primary">${r.ventas}</span>`, `<strong>${formatMoney(r.monto)}</strong>`]));
}

async function loadTrabajadores() {
    const d = await apiGet('/api/reportes.php?tipo=trabajadores');
    document.getElementById('tablaTrabajadores').innerHTML = tableHTML(
        ['Trabajador','Rol','Ventas (mes)','Monto (mes)','Total Ventas','Monto Total','Pagado'],
        d.trabajadores.map(t => [
            `<div><div style="font-weight:600">${t.nombre}</div><div style="font-size:11px;color:var(--text-dim)">@${t.usuario}</div></div>`,
            t.rol === 'admin' ? '<span class="badge badge-primary">Admin</span>' : '<span class="badge badge-gray">Vendedor</span>',
            t.total_ventas > 0 ? t.total_ventas : '<span style="color:var(--text-dim)">0</span>',
            `<strong>${formatMoney(t.monto_mes)}</strong>`,
            t.total_ventas,
            `<strong style="color:var(--primary-light)">${formatMoney(t.monto_total)}</strong>`,
            `<span style="color:var(--success)">${formatMoney(t.total_pagado)}</span>`
        ])
    );
}

function tableHTML(headers, rows) {
    if (!rows.length) return `<div class="empty-state"><div class="empty-icon"><i class="fas fa-chart-bar"></i></div><div class="empty-title">Sin datos</div></div>`;
    return `<div class="table-wrapper"><table>
        <thead><tr>${headers.map(h=>`<th>${h}</th>`).join('')}</tr></thead>
        <tbody>${rows.map(r=>`<tr>${r.map(c=>`<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>
    </table></div>`;
}

function chartOpts() {
    return {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => 'Q ' + ctx.raw.toLocaleString('es-VE',{minimumFractionDigits:2}) } }
        },
        scales: {
            x: { grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#64748b',font:{size:11}} },
            y: { grid:{color:'rgba(255,255,255,0.04)'}, ticks:{color:'#64748b',font:{size:11}, callback: v=>'Q '+v} }
        }
    };
}

loadResumen();
</script>
