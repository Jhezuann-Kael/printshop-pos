<?php
$pageTitle = 'Historial de Cierres';
$activeMenu = 'cierres';
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
require_once __DIR__ . '/includes/layout.php';
?>

<div class="card" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-book"></i> Historial de Cierres Diarios</div>
        <a href="/cierre.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Cierre</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ventas</th>
                    <th>Total</th>
                    <th>Efectivo</th>
                    <th>Transfer.</th>
                    <th>Tarjeta</th>
                    <th>Mixto</th>
                    <th>Cerrado por</th>
                    <th>Hora</th>
                </tr>
            </thead>
            <tbody id="cierresBody">
                <tr><td colspan="9" style="text-align:center;padding:40px">
                    <div class="loading-dots" style="justify-content:center"><span></span><span></span><span></span></div>
                </td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-chart-area"></i> Tendencia — Últimos 30 días</div>
    </div>
    <canvas id="chartTendencia" height="80"></canvas>
</div>

<?php require_once __DIR__ . '/includes/layout_end.php'; ?>
<script>
async function loadCierres() {
    try {
        const data = await apiGet('/api/cierre.php?historial=1');
        const tbody = document.getElementById('cierresBody');

        if (data.cierres.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><div class="empty-icon"><i class="fas fa-book"></i></div><div class="empty-title">Sin cierres aún</div></div></td></tr>`;
        } else {
            tbody.innerHTML = data.cierres.map(c => `
                <tr>
                    <td><strong>${new Date(c.fecha+'T12:00:00').toLocaleDateString('es-GT',{weekday:'short',day:'numeric',month:'short',year:'numeric'})}</strong></td>
                    <td><span class="badge badge-primary">${c.total_ventas}</span></td>
                    <td><strong style="color:var(--primary-light)">${formatMoney(c.monto_total)}</strong></td>
                    <td>${parseFloat(c.monto_efectivo) > 0 ? formatMoney(c.monto_efectivo) : '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td>${parseFloat(c.monto_transferencia) > 0 ? formatMoney(c.monto_transferencia) : '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td>${parseFloat(c.monto_tarjeta) > 0 ? formatMoney(c.monto_tarjeta) : '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td>${parseFloat(c.monto_mixto) > 0 ? formatMoney(c.monto_mixto) : '<span style="color:var(--text-dim)">—</span>'}</td>
                    <td><span style="font-size:12px;color:var(--text-muted)">${c.cerrado_por || '—'}</span></td>
                    <td><span style="font-size:11px;color:var(--text-dim)">${formatDateTime(c.cerrado_en)}</span></td>
                </tr>
            `).join('');

            // Chart
            const labels = [...data.cierres].reverse().map(c =>
                new Date(c.fecha+'T12:00:00').toLocaleDateString('es-GT',{day:'numeric',month:'short'})
            );
            const montos = [...data.cierres].reverse().map(c => parseFloat(c.monto_total));

            new Chart(document.getElementById('chartTendencia'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Cierre diario (Q)',
                        data: montos,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#7c3aed',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: { label: ctx => 'Q ' + ctx.raw.toLocaleString('es-GT',{minimumFractionDigits:2}) }
                        }
                    },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b', font: { size: 10 } } },
                        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#64748b', font: { size: 11 }, callback: v => 'Q ' + v } }
                    }
                }
            });
        }
    } catch {
        showToast('Error al cargar el historial', 'error');
    }
}

loadCierres();
</script>
