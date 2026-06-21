<?php
require_once __DIR__ . '/../config/database.php';

function generateNumeroVenta(): string {
    return 'VTA-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getJsonInput(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function formatMoney(float $amount): string {
    return number_format($amount, 2);
}

function getPayLabel(string $method): string {
    return match($method) {
        'fisico_bs'  => 'Físico (Bs)',
        'fisico_usd' => 'Físico ($)',
        'pago_movil' => 'Pago Móvil',
        'mixto'      => 'Mixto',
        default      => $method
    };
}

function getVentasHoy(): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_ventas, COALESCE(SUM(total_final),0) as monto_total FROM ventas WHERE DATE(creado_en)=CURDATE() AND estado='completada'");
    $stmt->execute();
    return $stmt->fetch();
}

function getVentasSemana(): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT DATE(creado_en) as fecha, COUNT(*) as cantidad, COALESCE(SUM(total_final),0) as monto FROM ventas WHERE creado_en >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND estado='completada' GROUP BY DATE(creado_en) ORDER BY fecha ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getVentasPorCategoria(string $periodo = 'mes'): array {
    $pdo = getDB();
    $filtro = match($periodo) {
        'hoy'    => "DATE(v.creado_en) = CURDATE()",
        'semana' => "v.creado_en >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
        default  => "MONTH(v.creado_en) = MONTH(CURDATE()) AND YEAR(v.creado_en) = YEAR(CURDATE())"
    };
    $stmt = $pdo->prepare("
        SELECT c.nombre, c.color, COUNT(DISTINCT v.id) as total_ventas, COALESCE(SUM(dv.subtotal),0) as monto_total
        FROM categorias c
        LEFT JOIN detalle_ventas dv ON dv.categoria_id = c.id
        LEFT JOIN ventas v ON v.id = dv.venta_id AND v.estado='completada' AND $filtro
        WHERE c.activo = 1
        GROUP BY c.id, c.nombre, c.color
        ORDER BY monto_total DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getBcvRate(): float {
    $cacheFile = sys_get_temp_dir() . '/printshop_bcv.json';
    $cacheTTL  = 1800;
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!empty($cached['promedio'])) return (float)$cached['promedio'];
    }
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true,
        'header' => "Accept: application/json\r\nUser-Agent: PrintShop/1.0\r\n"]]);
    $raw = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial', false, $ctx);
    if ($raw) {
        $d = json_decode($raw, true);
        if (!empty($d['promedio'])) {
            file_put_contents($cacheFile, json_encode($d));
            return (float)$d['promedio'];
        }
    }
    // fallback: stale cache
    if (file_exists($cacheFile)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!empty($cached['promedio'])) return (float)$cached['promedio'];
    }
    return 0.0;
}

function getConfig(string $clave, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$clave])) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ? LIMIT 1");
        $stmt->execute([$clave]);
        $row = $stmt->fetch();
        $cache[$clave] = $row ? $row['valor'] : $default;
    }
    return $cache[$clave] ?? $default;
}
