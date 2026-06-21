<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$periodo = $_GET['periodo'] ?? 'mes';

$hoy = getVentasHoy();
$semana = getVentasSemana();
$porCategoria = getVentasPorCategoria($periodo);

// Ventas del mes actual
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total, COALESCE(SUM(total_final), 0) as monto
    FROM ventas
    WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE()) AND estado = 'completada'
");
$stmt->execute();
$mes = $stmt->fetch();

// Último cierre
$stmt = $pdo->prepare("
    SELECT fecha, monto_total, total_ventas
    FROM cierres_diarios
    ORDER BY cerrado_en DESC LIMIT 1
");
$stmt->execute();
$ultimoCierre = $stmt->fetch();

// Últimas 5 ventas
$stmt = $pdo->prepare("
    SELECT v.id, v.numero_venta, v.cliente, v.total_final, v.metodo_pago, v.creado_en, u.nombre as vendedor
    FROM ventas v
    JOIN usuarios u ON u.id = v.usuario_id
    WHERE v.estado = 'completada'
    ORDER BY v.creado_en DESC LIMIT 5
");
$stmt->execute();
$ultimasVentas = $stmt->fetchAll();

// Métodos de pago del mes
$stmt = $pdo->prepare("
    SELECT metodo_pago, COUNT(*) as cantidad, COALESCE(SUM(total_final), 0) as monto
    FROM ventas
    WHERE MONTH(creado_en) = MONTH(CURDATE()) AND YEAR(creado_en) = YEAR(CURDATE()) AND estado = 'completada'
    GROUP BY metodo_pago
");
$stmt->execute();
$metodosPago = $stmt->fetchAll();

// Verificar si ya hay cierre hoy
$stmt = $pdo->prepare("SELECT id FROM cierres_diarios WHERE fecha = CURDATE() LIMIT 1");
$stmt->execute();
$cierreHoy = $stmt->fetch();

jsonResponse([
    'hoy' => $hoy,
    'semana' => $semana,
    'mes' => $mes,
    'por_categoria' => $porCategoria,
    'ultimo_cierre' => $ultimoCierre,
    'ultimas_ventas' => $ultimasVentas,
    'metodos_pago' => $metodosPago,
    'cierre_hoy' => (bool)$cierreHoy
]);
