<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/telegram.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user   = getCurrentUser();

// Historial de cierres
if ($method === 'GET' && isset($_GET['historial'])) {
    $stmt = $pdo->prepare("
        SELECT cd.*, u.nombre as cerrado_por
        FROM cierres_diarios cd JOIN usuarios u ON u.id = cd.usuario_id
        ORDER BY cd.fecha DESC LIMIT 30
    ");
    $stmt->execute();
    jsonResponse(['cierres' => $stmt->fetchAll()]);
}

if ($method === 'GET') {
    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    $stmtCierre = $pdo->prepare("SELECT * FROM cierres_diarios WHERE fecha = ? LIMIT 1");
    $stmtCierre->execute([$fecha]);
    $cierreExistente = $stmtCierre->fetch();

    $stmt = $pdo->prepare("SELECT v.*, u.nombre as vendedor FROM ventas v JOIN usuarios u ON u.id = v.usuario_id WHERE DATE(v.creado_en) = ? AND v.estado = 'completada' ORDER BY v.creado_en DESC");
    $stmt->execute([$fecha]);
    $ventas = $stmt->fetchAll();

    foreach ($ventas as &$venta) {
        $stmtDet = $pdo->prepare("SELECT dv.*, c.nombre as categoria_nombre, c.color, c.icono FROM detalle_ventas dv JOIN categorias c ON c.id = dv.categoria_id WHERE dv.venta_id = ?");
        $stmtDet->execute([$venta['id']]);
        $venta['detalles'] = $stmtDet->fetchAll();
    }

    $stmt = $pdo->prepare("SELECT metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(total_final),0) as monto FROM ventas WHERE DATE(creado_en) = ? AND estado = 'completada' GROUP BY metodo_pago");
    $stmt->execute([$fecha]);
    $resumenPago = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT c.nombre, c.color, c.icono, COUNT(DISTINCT v.id) as ventas_count, COALESCE(SUM(dv.subtotal),0) as monto
        FROM categorias c
        LEFT JOIN detalle_ventas dv ON dv.categoria_id = c.id
        LEFT JOIN ventas v ON v.id = dv.venta_id AND DATE(v.creado_en) = ? AND v.estado = 'completada'
        WHERE c.activo = 1 GROUP BY c.id, c.nombre, c.color, c.icono HAVING monto > 0
    ");
    $stmt->execute([$fecha]);
    $resumenCategoria = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_ventas, COALESCE(SUM(total_final),0) as monto_total FROM ventas WHERE DATE(creado_en) = ? AND estado = 'completada'");
    $stmt->execute([$fecha]);
    $totales = $stmt->fetch();

    jsonResponse(['fecha' => $fecha, 'cierre_existente' => $cierreExistente, 'ventas' => $ventas, 'resumen_pago' => $resumenPago, 'resumen_categoria' => $resumenCategoria, 'totales' => $totales]);
}

if ($method === 'POST') {
    $data  = getJsonInput();
    $fecha = $data['fecha'] ?? date('Y-m-d');
    $notas = sanitize($data['notas'] ?? '');

    $stmt = $pdo->prepare("SELECT id FROM cierres_diarios WHERE fecha = ? LIMIT 1");
    $stmt->execute([$fecha]);
    if ($stmt->fetch()) jsonResponse(['error' => 'Ya existe un cierre para esta fecha'], 409);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_ventas, COALESCE(SUM(total_final),0) as monto_total,
               COALESCE(SUM(CASE WHEN metodo_pago='fisico_bs'  THEN total_final ELSE 0 END),0) as monto_fisico_bs,
               COALESCE(SUM(CASE WHEN metodo_pago='fisico_usd' THEN total_final ELSE 0 END),0) as monto_fisico_usd,
               COALESCE(SUM(CASE WHEN metodo_pago='pago_movil' THEN total_final ELSE 0 END),0) as monto_pago_movil,
               COALESCE(SUM(CASE WHEN metodo_pago='mixto'      THEN total_final ELSE 0 END),0) as monto_mixto
        FROM ventas WHERE DATE(creado_en) = ? AND estado = 'completada'
    ");
    $stmt->execute([$fecha]);
    $totales = $stmt->fetch();

    $stmt = $pdo->prepare("
        INSERT INTO cierres_diarios
        (usuario_id, fecha, total_ventas, monto_total, monto_efectivo, monto_transferencia, monto_tarjeta, monto_mixto, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['id'], $fecha,
        $totales['total_ventas'], $totales['monto_total'],
        $totales['monto_fisico_bs'] + $totales['monto_fisico_usd'],
        $totales['monto_pago_movil'], 0,
        $totales['monto_mixto'], $notas
    ]);

    tgCierre($fecha, $totales, getBcvRate());

    jsonResponse(['success' => true, 'totales' => $totales]);
}
