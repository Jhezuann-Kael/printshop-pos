<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /historial.php'); exit; }

$pdo   = getDB();
$stmt  = $pdo->prepare("SELECT v.*, u.nombre as vendedor FROM ventas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch();
if (!$venta) { header('Location: /historial.php'); exit; }

$stmtDet = $pdo->prepare("SELECT dv.*, c.nombre as cat, c.color, c.icono FROM detalle_ventas dv JOIN categorias c ON c.id = dv.categoria_id WHERE dv.venta_id = ?");
$stmtDet->execute([$id]);
$detalles = $stmtDet->fetchAll();

$stmtMixto = $pdo->prepare("SELECT * FROM ventas_pago_mixto WHERE venta_id = ?");
$stmtMixto->execute([$id]);
$pagoMixto = $stmtMixto->fetchAll();

// Configuración del negocio
$nombre      = getConfig('negocio_nombre', 'PrintShop');
$rif         = getConfig('negocio_rif', '');
$direccion   = getConfig('negocio_direccion', '');
$telefono    = getConfig('negocio_telefono', '');
$email       = getConfig('negocio_email', '');
$pie         = getConfig('factura_pie', 'Gracias por su preferencia');
$color       = getConfig('factura_color_primario', '#7c3aed');
$nota        = getConfig('factura_nota', '');
$headerTexto = getConfig('factura_color_header_texto', '#ffffff');
$pieBg       = getConfig('factura_color_pie_bg', '#f8fafc');
$pieTxt      = getConfig('factura_color_pie_texto', '#94a3b8');
$logoPath    = getConfig('factura_logo', '');
$showLogo    = getConfig('factura_mostrar_logo', '0') === '1';
$titulo      = getConfig('factura_titulo', 'Nota de Entrega');
$subtitulo   = getConfig('factura_subtitulo', '');
$filaBg      = getConfig('factura_color_fila_bg',    '#ffffff');
$filaAlt     = getConfig('factura_color_fila_alt',   '#f8fafc');
$filaTxt     = getConfig('factura_color_fila_texto', '#334155');
$filaBorde   = getConfig('factura_color_fila_borde', '#f1f5f9');
$totalBg     = getConfig('factura_color_total_bg',   '#f8fafc');
$totalTxt    = getConfig('factura_color_total_texto','#7c3aed');

$payLabels = ['fisico_bs'=>'Físico (Bs)','fisico_usd'=>'Físico ($)','pago_movil'=>'Pago Móvil','mixto'=>'Mixto'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($venta['numero_venta']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary:      <?= htmlspecialchars($color) ?>;
            --header-texto: <?= htmlspecialchars($headerTexto) ?>;
            --pie-bg:       <?= htmlspecialchars($pieBg) ?>;
            --pie-txt:      <?= htmlspecialchars($pieTxt) ?>;
            --fila-bg:      <?= htmlspecialchars($filaBg) ?>;
            --fila-alt:     <?= htmlspecialchars($filaAlt) ?>;
            --fila-txt:     <?= htmlspecialchars($filaTxt) ?>;
            --fila-borde:   <?= htmlspecialchars($filaBorde) ?>;
            --total-bg:     <?= htmlspecialchars($totalBg) ?>;
            --total-txt:    <?= htmlspecialchars($totalTxt) ?>;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#f1f5f9; color:#1e293b; min-height:100vh; }

        .toolbar {
            background:#1e293b; color:white; padding:12px 24px;
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            position:sticky; top:0; z-index:50;
        }

        .toolbar-left { display:flex; align-items:center; gap:12px; }
        .toolbar-title { font-size:14px; font-weight:600; }
        .toolbar-sub { font-size:12px; color:#94a3b8; }

        .toolbar-btns { display:flex; gap:8px; flex-wrap:wrap; }

        .tbtn {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; border-radius:8px; font-size:12px; font-weight:600;
            cursor:pointer; border:none; font-family:'Inter',sans-serif; transition:all 0.2s;
        }
        .tbtn-primary { background:var(--primary); color:white; }
        .tbtn-primary:hover { filter:brightness(1.1); }
        .tbtn-secondary { background:rgba(255,255,255,0.1); color:white; }
        .tbtn-secondary:hover { background:rgba(255,255,255,0.2); }
        .tbtn-back { background:transparent; color:#94a3b8; }
        .tbtn-back:hover { color:white; }

        .page-wrap { padding:32px 16px; display:flex; justify-content:center; }

        .invoice {
            background:white; width:100%; max-width:760px;
            border-radius:8px; overflow:hidden;
            box-shadow:0 4px 24px rgba(0,0,0,0.12);
        }

        /* Header */
        .inv-header {
            background:var(--primary); color:var(--header-texto); padding:32px;
            display:flex; justify-content:space-between; align-items:flex-start; gap:24px;
        }
        .inv-brand { flex:1; }
        .inv-brand-name { font-size:26px; font-weight:800; letter-spacing:-0.5px; }
        .inv-brand-info { font-size:12px; opacity:0.8; margin-top:6px; line-height:1.7; }
        .inv-meta { text-align:right; }
        .inv-label { font-size:10px; text-transform:uppercase; letter-spacing:1px; opacity:0.7; }
        .inv-number { font-size:20px; font-weight:800; font-family:'Courier New',monospace; margin:4px 0; }
        .inv-date { font-size:12px; opacity:0.9; }

        /* Body */
        .inv-body { padding:28px 32px; }

        /* Client + payment */
        .inv-meta-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
        .inv-box { background:#f8fafc; border-radius:8px; padding:16px; border:1px solid #e2e8f0; }
        .inv-box-title { font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:700; margin-bottom:10px; }
        .inv-info-row { font-size:13px; color:#334155; margin-bottom:5px; display:flex; gap:6px; }
        .inv-info-row .lbl { color:#94a3b8; min-width:70px; font-size:12px; }
        .inv-info-row .val { font-weight:500; }

        /* Table */
        .inv-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .inv-table thead tr { background:var(--primary); color:white; }
        .inv-table thead th { padding:10px 14px; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; font-weight:600; text-align:left; }
        .inv-table thead th:last-child, .inv-table tbody td:last-child { text-align:right; }
        .inv-table thead th:nth-child(3), .inv-table tbody td:nth-child(3),
        .inv-table thead th:nth-child(4), .inv-table tbody td:nth-child(4) { text-align:center; }
        .inv-table tbody tr { border-bottom:1px solid var(--fila-borde); }
        .inv-table tbody tr:nth-child(odd)  { background:var(--fila-bg); }
        .inv-table tbody tr:nth-child(even) { background:var(--fila-alt); }
        .inv-table tbody tr:last-child { border-bottom:none; }
        .inv-table tbody td { padding:12px 14px; font-size:13px; color:var(--fila-txt); vertical-align:middle; }
        .inv-table tfoot td { padding:8px 14px; font-size:13px; }
        .inv-table tfoot .subtotal-row td { border-top:1px solid var(--fila-borde); color:#64748b; }
        .inv-table tfoot .total-row { background:var(--total-bg); }
        .inv-table tfoot .total-row td { font-size:16px; font-weight:800; color:var(--total-txt); border-top:2px solid var(--total-txt); }
        .inv-table tfoot .discount-row td { color:#ef4444; font-size:12px; }

        .cat-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:6px; vertical-align:middle; }

        /* Footer */
        .inv-footer { border-top:2px solid #f1f5f9; padding:20px 32px; display:flex; justify-content:space-between; align-items:center; gap:16px; background:var(--pie-bg); }
        .inv-pie { font-size:12px; color:var(--pie-txt); font-style:italic; }
        .inv-status { display:inline-block; padding:5px 14px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
        .status-completada { background:#dcfce7; color:#16a34a; }
        .status-cancelada { background:#fee2e2; color:#dc2626; }
        .status-pendiente { background:#fef9c3; color:#ca8a04; }

        .inv-mixto { background:#f8fafc; border-radius:8px; padding:12px 16px; margin-bottom:20px; border:1px solid #e2e8f0; }
        .inv-mixto-title { font-size:10px; text-transform:uppercase; letter-spacing:0.8px; color:#64748b; font-weight:700; margin-bottom:8px; }
        .inv-mixto-row { display:flex; justify-content:space-between; font-size:12px; color:#334155; padding:3px 0; }

        .inv-nota { background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 16px; margin-bottom:20px; }
        .inv-nota p { font-size:12px; color:#92400e; }

        /* Print styles */
        @media print {
            .toolbar { display:none !important; }
            .page-wrap { padding:0; }
            body { background:white; }
            .invoice { box-shadow:none; border-radius:0; max-width:100%; }
        }

        @media (max-width: 600px) {
            .inv-header { flex-direction:column; }
            .inv-meta { text-align:left; }
            .inv-meta-row { grid-template-columns:1fr; }
            .inv-body { padding:20px 16px; }
            .inv-footer { flex-direction:column; text-align:center; }
        }
    </style>
</head>
<body>
<!-- Toolbar -->
<div class="toolbar">
    <div class="toolbar-left">
        <button class="tbtn tbtn-back" onclick="window.close()"><i class="fas fa-arrow-left"></i></button>
        <div>
            <div class="toolbar-title">Factura</div>
            <div class="toolbar-sub"><?= htmlspecialchars($venta['numero_venta']) ?></div>
        </div>
    </div>
    <div class="toolbar-btns">
        <button class="tbtn tbtn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        <button class="tbtn tbtn-primary" onclick="downloadPDF()" id="btnPDF"><i class="fas fa-file-pdf"></i> Descargar PDF</button>
    </div>
</div>

<!-- Invoice -->
<div class="page-wrap">
<div class="invoice" id="invoiceEl">
    <!-- Header -->
    <div class="inv-header">
        <div class="inv-brand">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:4px">
                <?php if ($showLogo && $logoPath && file_exists(__DIR__ . '/' . $logoPath)): ?>
                <img src="/<?= htmlspecialchars($logoPath) ?>" alt="Logo" style="height:48px;border-radius:6px;background:rgba(255,255,255,0.15);padding:4px;flex-shrink:0">
                <?php endif; ?>
                <div>
                    <div class="inv-brand-name"><?= htmlspecialchars($nombre) ?></div>
                    <?php if ($subtitulo): ?><div style="font-size:11px;opacity:0.75;margin-top:2px"><?= htmlspecialchars($subtitulo) ?></div><?php endif; ?>
                </div>
            </div>
            <div class="inv-brand-info">
                <?php if ($rif): ?><div><i class="fas fa-building" style="width:14px"></i> <?= htmlspecialchars($rif) ?></div><?php endif; ?>
                <?php if ($direccion): ?><div><i class="fas fa-location-dot" style="width:14px"></i> <?= htmlspecialchars($direccion) ?></div><?php endif; ?>
                <?php if ($telefono): ?><div><i class="fas fa-phone" style="width:14px"></i> <?= htmlspecialchars($telefono) ?></div><?php endif; ?>
                <?php if ($email): ?><div><i class="fas fa-envelope" style="width:14px"></i> <?= htmlspecialchars($email) ?></div><?php endif; ?>
            </div>
        </div><!-- /inv-brand -->
        <div class="inv-meta">
            <div class="inv-label"><?= htmlspecialchars($titulo) ?></div>
            <div class="inv-number"><?= htmlspecialchars($venta['numero_venta']) ?></div>
            <div class="inv-date"><?= date('d/m/Y H:i', strtotime($venta['creado_en'])) ?></div>
            <div style="margin-top:8px"><span class="inv-status status-<?= $venta['estado'] ?>"><?= ucfirst($venta['estado']) ?></span></div>
        </div>
    </div><!-- /inv-header -->

    <!-- Body -->
    <div class="inv-body">
        <!-- Cliente + Pago -->
        <div class="inv-meta-row">
            <div class="inv-box">
                <div class="inv-box-title"><i class="fas fa-user"></i> &nbsp;Cliente</div>
                <div class="inv-info-row"><span class="lbl">Nombre:</span><span class="val"><?= htmlspecialchars($venta['cliente'] ?: '—') ?></span></div>
                <?php if ($venta['cliente_cedula']): ?>
                <div class="inv-info-row"><span class="lbl">Cédula:</span><span class="val"><?= htmlspecialchars($venta['cliente_cedula']) ?></span></div>
                <?php endif; ?>
                <?php if ($venta['cliente_telefono']): ?>
                <div class="inv-info-row"><span class="lbl">Teléfono:</span><span class="val"><?= htmlspecialchars($venta['cliente_telefono']) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="inv-box">
                <div class="inv-box-title"><i class="fas fa-wallet"></i> &nbsp;Pago</div>
                <div class="inv-info-row"><span class="lbl">Método:</span><span class="val"><?= $payLabels[$venta['metodo_pago']] ?? $venta['metodo_pago'] ?></span></div>
                <div class="inv-info-row"><span class="lbl">Vendedor:</span><span class="val"><?= htmlspecialchars($venta['vendedor']) ?></span></div>
                <div class="inv-info-row"><span class="lbl">Fecha:</span><span class="val"><?= date('d/m/Y', strtotime($venta['creado_en'])) ?></span></div>
                <?php if (!empty($venta['referencia_pm'])): ?>
                <div class="inv-info-row"><span class="lbl">Ref. PM:</span><span class="val" style="font-family:monospace"><?= htmlspecialchars($venta['referencia_pm']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($venta['tasa_bcv']) && $venta['tasa_bcv'] > 0): ?>
                <div class="inv-info-row"><span class="lbl">Tasa BCV:</span><span class="val">Bs <?= number_format((float)$venta['tasa_bcv'], 2) ?> / $</span></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($venta['metodo_pago'] === 'mixto' && $pagoMixto): ?>
        <div class="inv-mixto">
            <div class="inv-mixto-title"><i class="fas fa-layer-group"></i> &nbsp;Desglose Pago Mixto</div>
            <?php foreach ($pagoMixto as $pm): ?>
            <div class="inv-mixto-row">
                <span><?= $payLabels[$pm['metodo']] ?? $pm['metodo'] ?></span>
                <strong><?= number_format($pm['monto'],2) ?></strong>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($venta['comprobante_pm']) && file_exists(__DIR__ . '/' . $venta['comprobante_pm'])): ?>
        <div style="margin-bottom:20px">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:0.8px;color:#64748b;font-weight:700;margin-bottom:8px"><i class="fas fa-image"></i> &nbsp;Comprobante Pago Móvil</div>
            <img src="/<?= htmlspecialchars($venta['comprobante_pm']) ?>" alt="Comprobante" style="max-width:260px;border-radius:8px;border:1px solid #e2e8f0;display:block">
        </div>
        <?php endif; ?>

        <?php if ($nota): ?>
        <div class="inv-nota"><p><i class="fas fa-info-circle"></i> &nbsp;<?= htmlspecialchars($nota) ?></p></div>
        <?php endif; ?>

        <!-- Detalle -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:40%">Descripción</th>
                    <th>Categoría</th>
                    <th style="width:60px">Cant.</th>
                    <th style="width:100px">P. Unit.</th>
                    <th style="width:110px">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['descripcion']) ?></td>
                    <td>
                        <span class="cat-dot" style="background:<?= htmlspecialchars($d['color']) ?>"></span>
                        <?= htmlspecialchars($d['cat']) ?>
                    </td>
                    <td style="text-align:center"><?= $d['cantidad'] ?></td>
                    <td style="text-align:center"><?= number_format($d['precio_unitario'],2) ?></td>
                    <td style="text-align:right;font-weight:600"><?= number_format($d['subtotal'],2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="subtotal-row">
                    <td colspan="4" style="text-align:right">Subtotal</td>
                    <td style="text-align:right"><?= number_format($venta['total'],2) ?></td>
                </tr>
                <?php if ($venta['descuento'] > 0): ?>
                <tr class="discount-row">
                    <td colspan="4" style="text-align:right">Descuento</td>
                    <td style="text-align:right">- <?= number_format($venta['descuento'],2) ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align:right">TOTAL</td>
                    <td style="text-align:right">$ <?= number_format($venta['total_final'],2) ?></td>
                </tr>
                <?php if (!empty($venta['total_bs']) && $venta['total_bs'] > 0): ?>
                <tr>
                    <td colspan="4" style="text-align:right;padding:5px 14px;font-size:11px;color:#64748b">Equivalente en Bs</td>
                    <td style="text-align:right;padding:5px 14px;font-size:11px;color:#64748b;font-weight:600">Bs <?= number_format((float)$venta['total_bs'], 2) ?></td>
                </tr>
                <?php endif; ?>
            </tfoot>
        </table>

        <?php if ($venta['notas']): ?>
        <div style="background:#f8fafc;border-radius:8px;padding:12px 16px;margin-bottom:4px;border:1px solid #e2e8f0">
            <div style="font-size:10px;text-transform:uppercase;color:#64748b;font-weight:700;margin-bottom:6px">Notas</div>
            <div style="font-size:13px;color:#475569"><?= htmlspecialchars($venta['notas']) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="inv-footer">
        <div class="inv-pie"><?= htmlspecialchars($pie) ?></div>
        <div style="font-size:11px;color:#94a3b8;text-align:right">
            Generado: <?= date('d/m/Y H:i') ?><br>
            <?= htmlspecialchars($nombre) ?>
        </div>
    </div>
</div>
</div>

<script>
async function downloadPDF() {
    const btn = document.getElementById('btnPDF');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';

    try {
        const { jsPDF } = window.jspdf;
        const el        = document.getElementById('invoiceEl');

        const canvas = await html2canvas(el, {
            scale: 2,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });

        const imgData = canvas.toDataURL('image/jpeg', 0.95);
        const pdf     = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
        const pdfW    = pdf.internal.pageSize.getWidth();
        const pdfH    = pdf.internal.pageSize.getHeight();
        const imgW    = canvas.width;
        const imgH    = canvas.height;
        const ratio   = pdfW / imgW;
        const h       = imgH * ratio;

        if (h <= pdfH) {
            pdf.addImage(imgData, 'JPEG', 0, 0, pdfW, h);
        } else {
            let posY = 0;
            let remaining = h;
            while (remaining > 0) {
                pdf.addImage(imgData, 'JPEG', 0, -posY, pdfW, h);
                remaining -= pdfH;
                if (remaining > 0) { pdf.addPage(); posY += pdfH; }
            }
        }

        pdf.save(`Factura-<?= htmlspecialchars($venta['numero_venta']) ?>.pdf`);
    } catch (e) {
        alert('Error al generar PDF. Intenta usar "Imprimir" → Guardar como PDF.');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-file-pdf"></i> Descargar PDF';
}
</script>
</body>
</html>
