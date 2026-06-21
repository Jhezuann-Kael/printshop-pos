<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();
header('Content-Type: application/json; charset=utf-8');

$pdo    = getDB();
$tipo   = $_GET['tipo'] ?? 'resumen';
$anio   = (int)($_GET['anio'] ?? date('Y'));
$mes    = (int)($_GET['mes'] ?? date('n'));

if ($tipo === 'anual') {
    $stmt = $pdo->prepare("
        SELECT MONTH(creado_en) as mes, COUNT(*) as ventas, COALESCE(SUM(total_final),0) as monto
        FROM ventas WHERE YEAR(creado_en) = ? AND estado = 'completada'
        GROUP BY MONTH(creado_en) ORDER BY mes
    ");
    $stmt->execute([$anio]);
    $porMes = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_final),0) as monto FROM ventas WHERE YEAR(creado_en) = ? AND estado = 'completada'");
    $stmt->execute([$anio]);
    $totalAnual = $stmt->fetch();

    jsonResponse(['por_mes' => $porMes, 'total' => $totalAnual]);
}

if ($tipo === 'mensual') {
    $stmt = $pdo->prepare("
        SELECT DAY(creado_en) as dia, COUNT(*) as ventas, COALESCE(SUM(total_final),0) as monto
        FROM ventas WHERE YEAR(creado_en)=? AND MONTH(creado_en)=? AND estado='completada'
        GROUP BY DAY(creado_en) ORDER BY dia
    ");
    $stmt->execute([$anio, $mes]);
    $porDia = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(SUM(total_final),0) as monto FROM ventas WHERE YEAR(creado_en)=? AND MONTH(creado_en)=? AND estado='completada'");
    $stmt->execute([$anio, $mes]);
    $totalMes = $stmt->fetch();

    jsonResponse(['por_dia' => $porDia, 'total' => $totalMes]);
}

if ($tipo === 'semanal') {
    $stmt = $pdo->prepare("
        SELECT DATE(creado_en) as fecha, DAYOFWEEK(creado_en) as dow,
               COUNT(*) as ventas, COALESCE(SUM(total_final),0) as monto
        FROM ventas WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado='completada'
        GROUP BY DATE(creado_en) ORDER BY fecha
    ");
    $stmt->execute();
    $semana = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT COUNT(*) as total, COALESCE(SUM(total_final),0) as monto FROM ventas WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado='completada'");
    $totalSemana = $stmt->fetch();

    jsonResponse(['semana' => $semana, 'total' => $totalSemana]);
}

if ($tipo === 'trabajadores') {
    $stmt = $pdo->prepare("
        SELECT u.id, u.nombre, u.usuario, u.rol,
               COUNT(DISTINCT DATE(v.creado_en)) as dias_trabajados,
               COUNT(v.id) as total_ventas,
               COALESCE(SUM(v.total_final),0) as monto_total,
               COALESCE(SUM(CASE WHEN MONTH(v.creado_en)=MONTH(CURDATE()) AND YEAR(v.creado_en)=YEAR(CURDATE()) THEN v.total_final ELSE 0 END),0) as monto_mes
        FROM usuarios u
        LEFT JOIN ventas v ON v.usuario_id = u.id AND v.estado = 'completada'
        WHERE u.activo = 1
        GROUP BY u.id, u.nombre, u.usuario, u.rol
        ORDER BY monto_total DESC
    ");
    $stmt->execute();
    $trabajadores = $stmt->fetchAll();

    foreach ($trabajadores as &$t) {
        $stmt = $pdo->prepare("SELECT SUM(monto) as total_pagado, COUNT(*) as num_pagos FROM pagos_trabajadores WHERE usuario_id = ?");
        $stmt->execute([$t['id']]);
        $pagos = $stmt->fetch();
        $t['total_pagado'] = $pagos['total_pagado'] ?? 0;
        $t['num_pagos']    = $pagos['num_pagos'] ?? 0;
    }

    jsonResponse(['trabajadores' => $trabajadores]);
}

if ($tipo === 'resumen') {
    $hoy   = date('Y-m-d');
    $stmt  = $pdo->prepare("SELECT COUNT(*) as t, COALESCE(SUM(total_final),0) as m FROM ventas WHERE DATE(creado_en)=? AND estado='completada'");
    $stmt->execute([$hoy]);
    $dia = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as t, COALESCE(SUM(total_final),0) as m FROM ventas WHERE creado_en>=DATE_SUB(NOW(),INTERVAL 7 DAY) AND estado='completada'");
    $stmt->execute();
    $semana = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as t, COALESCE(SUM(total_final),0) as m FROM ventas WHERE MONTH(creado_en)=MONTH(CURDATE()) AND YEAR(creado_en)=YEAR(CURDATE()) AND estado='completada'");
    $stmt->execute();
    $mes = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) as t, COALESCE(SUM(total_final),0) as m FROM ventas WHERE YEAR(creado_en)=YEAR(CURDATE()) AND estado='completada'");
    $stmt->execute();
    $anio_actual = $stmt->fetch();

    jsonResponse(['dia' => $dia, 'semana' => $semana, 'mes' => $mes, 'anio' => $anio_actual]);
}
