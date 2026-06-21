<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) jsonResponse(['error' => 'ID requerido'], 400);

$stmt = $pdo->prepare("SELECT v.*, u.nombre as vendedor FROM ventas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.id = ?");
$stmt->execute([$id]);
$venta = $stmt->fetch();
if (!$venta) jsonResponse(['error' => 'Venta no encontrada'], 404);

$stmtDet = $pdo->prepare("SELECT dv.*, c.nombre as cat, c.color, c.icono FROM detalle_ventas dv JOIN categorias c ON c.id = dv.categoria_id WHERE dv.venta_id = ?");
$stmtDet->execute([$id]);
$venta['detalles'] = $stmtDet->fetchAll();

$stmtMixto = $pdo->prepare("SELECT * FROM ventas_pago_mixto WHERE venta_id = ?");
$stmtMixto->execute([$id]);
$venta['pago_mixto'] = $stmtMixto->fetchAll();

$stmt = $pdo->query("SELECT clave, valor FROM configuracion ORDER BY clave");
$rows = $stmt->fetchAll();
$config = [];
foreach ($rows as $r) $config[$r['clave']] = $r['valor'];

jsonResponse(['venta' => $venta, 'config' => $config]);
