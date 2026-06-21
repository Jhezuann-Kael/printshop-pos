<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();
$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];

/* ── Logo upload ── */
if ($method === 'POST' && isset($_GET['logo'])) {
    header('Content-Type: application/json; charset=utf-8');
    if (empty($_FILES['logo']['tmp_name'])) jsonResponse(['error' => 'Sin archivo'], 400);
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    if (!in_array($_FILES['logo']['type'], $allowed)) jsonResponse(['error' => 'Tipo no permitido'], 400);
    $ext   = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    $dir   = __DIR__ . '/../uploads/logos/';
    $fname = 'logo_' . time() . '.' . $ext;
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $dir . $fname)) jsonResponse(['error' => 'Error al guardar'], 500);
    // Delete old logo
    $old = getConfig('factura_logo', '');
    if ($old && file_exists(__DIR__ . '/../' . $old)) @unlink(__DIR__ . '/../' . $old);
    // Save path to config
    $path = 'uploads/logos/' . $fname;
    $stmt = $pdo->prepare("INSERT INTO configuracion (clave,valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor=?");
    $stmt->execute(['factura_logo', $path, $path]);
    jsonResponse(['success' => true, 'path' => $path]);
}

header('Content-Type: application/json; charset=utf-8');

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion ORDER BY clave");
    $config = [];
    foreach ($stmt->fetchAll() as $r) $config[$r['clave']] = $r['valor'];
    jsonResponse($config);
}

if ($method === 'POST') {
    $data    = getJsonInput();
    $allowed = [
        'negocio_nombre','negocio_rif','negocio_direccion','negocio_telefono','negocio_email',
        'factura_pie','factura_color_primario','factura_nota',
        'factura_color_header_texto','factura_color_pie_bg','factura_color_pie_texto',
        'factura_mostrar_logo','factura_titulo','factura_subtitulo',
        'factura_color_fila_bg','factura_color_fila_alt','factura_color_fila_texto',
        'factura_color_fila_borde','factura_color_total_bg','factura_color_total_texto',
        'telegram_bot_token','telegram_chat_id','telegram_notif_ventas','telegram_notif_cierres',
    ];
    $stmt = $pdo->prepare("INSERT INTO configuracion (clave,valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor=?");
    foreach ($allowed as $key) {
        if (array_key_exists($key, $data)) {
            $stmt->execute([$key, (string)$data[$key], (string)$data[$key]]);
        }
    }
    jsonResponse(['success' => true]);
}
