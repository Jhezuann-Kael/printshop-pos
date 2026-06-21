<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$cacheFile = sys_get_temp_dir() . '/printshop_bcv.json';
$cacheTTL  = 1800; // 30 min

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    echo file_get_contents($cacheFile);
    exit;
}

$apis = [
    'https://ve.dolarapi.com/v1/dolares/oficial',
    'https://pydolarve.org/api/v1/dollar?page=bcv&monitor=bcv',
];

$tasa = null; $fecha = date('Y-m-d'); $raw = null;

foreach ($apis as $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 6, 'ignore_errors' => true,
        'header' => "Accept: application/json\r\nUser-Agent: PrintShopVE/1.0\r\n"]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) continue;
    $j = json_decode($raw, true);
    if (!$j) continue;
    $tasa  = (float)($j['promedio'] ?? $j['promedioBCV'] ?? $j['price'] ?? 0);
    $fecha = $j['fechaActualizacion'] ?? $j['last_update'] ?? date('Y-m-d');
    if ($tasa > 0) break;
}

if ($tasa > 0) {
    $result = json_encode(['ok' => true, 'tasa' => $tasa, 'fecha' => $fecha, 'fuente' => 'BCV']);
    file_put_contents($cacheFile, $result);
    echo $result;
} elseif (file_exists($cacheFile)) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    $cached['stale'] = true;
    echo json_encode($cached);
} else {
    echo json_encode(['ok' => false, 'tasa' => null, 'error' => 'No se pudo obtener la tasa BCV']);
}
