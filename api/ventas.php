<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/telegram.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = getCurrentUser();

if ($method === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT v.*, u.nombre as vendedor FROM ventas v JOIN usuarios u ON u.id = v.usuario_id WHERE v.id = ?");
    $stmt->execute([$id]);
    $venta = $stmt->fetch();
    if (!$venta) jsonResponse(['error' => 'Venta no encontrada'], 404);

    $stmtDet = $pdo->prepare("SELECT dv.*, c.nombre as categoria_nombre, c.color, c.icono FROM detalle_ventas dv JOIN categorias c ON c.id = dv.categoria_id WHERE dv.venta_id = ?");
    $stmtDet->execute([$id]);
    $venta['detalles'] = $stmtDet->fetchAll();

    $stmtMixto = $pdo->prepare("SELECT * FROM ventas_pago_mixto WHERE venta_id = ?");
    $stmtMixto->execute([$id]);
    $venta['pago_mixto'] = $stmtMixto->fetchAll();

    jsonResponse($venta);
}

if ($method === 'GET') {
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = 15;
    $offset = ($page - 1) * $limit;
    $buscar = trim($_GET['buscar'] ?? '');
    $fecha  = trim($_GET['fecha'] ?? '');
    $estado = trim($_GET['estado'] ?? '');

    $where  = ["1=1"];
    $params = [];

    if ($buscar) {
        $where[] = "(v.numero_venta LIKE ? OR v.cliente LIKE ? OR v.cliente_cedula LIKE ?)";
        $params  = array_merge($params, ["%$buscar%", "%$buscar%", "%$buscar%"]);
    }
    if ($fecha)  { $where[] = "DATE(v.creado_en) = ?"; $params[] = $fecha; }
    if ($estado) { $where[] = "v.estado = ?";          $params[] = $estado; }

    $whereStr  = implode(' AND ', $where);
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ventas v WHERE $whereStr");
    $stmtCount->execute($params);
    $total = $stmtCount->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT v.id, v.numero_venta, v.cliente, v.cliente_cedula, v.cliente_telefono,
               v.total, v.descuento, v.total_final, v.metodo_pago, v.estado, v.notas,
               v.tasa_bcv, v.total_bs, v.creado_en,
               u.nombre as vendedor
        FROM ventas v JOIN usuarios u ON u.id = v.usuario_id
        WHERE $whereStr ORDER BY v.creado_en DESC LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $ventas = $stmt->fetchAll();

    foreach ($ventas as &$venta) {
        $stmtDet = $pdo->prepare("
            SELECT dv.*, c.nombre as categoria_nombre, c.color as categoria_color, c.icono as categoria_icono
            FROM detalle_ventas dv JOIN categorias c ON c.id = dv.categoria_id WHERE dv.venta_id = ?
        ");
        $stmtDet->execute([$venta['id']]);
        $venta['detalles'] = $stmtDet->fetchAll();
    }

    jsonResponse(['ventas' => $ventas, 'total' => (int)$total, 'pagina' => $page, 'paginas' => ceil($total / $limit)]);
}

if ($method === 'POST') {
    $isMultipart = !empty($_POST);
    if ($isMultipart) {
        $data = $_POST;
        $data['items']      = json_decode($_POST['items'] ?? '[]', true) ?? [];
        $data['pago_mixto'] = [];
    } else {
        $data = getJsonInput();
    }

    $cliente          = sanitize($data['cliente'] ?? '');
    $cliente_cedula   = sanitize($data['cliente_cedula'] ?? '');
    $cliente_telefono = sanitize($data['cliente_telefono'] ?? '');
    $metodo_pago      = $data['metodo_pago'] ?? 'fisico_bs';
    $descuento        = (float)($data['descuento'] ?? 0);
    $notas            = sanitize($data['notas'] ?? '');
    $items            = $data['items'] ?? [];
    $pago_mixto       = $data['pago_mixto'] ?? [];
    $referencia_pm    = sanitize($data['referencia_pm'] ?? '');

    // Handle pago_movil comprobante image upload
    $comprobante_pm = null;
    if ($metodo_pago === 'pago_movil' && !empty($_FILES['comprobante_pm']['tmp_name'])) {
        $allowedMime = ['image/jpeg','image/png','image/webp'];
        if (in_array($_FILES['comprobante_pm']['type'], $allowedMime)) {
            $ext   = strtolower(pathinfo($_FILES['comprobante_pm']['name'], PATHINFO_EXTENSION));
            $dir   = __DIR__ . '/../uploads/comprobantes/';
            $fname = 'vta_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['comprobante_pm']['tmp_name'], $dir . $fname)) {
                $comprobante_pm = 'uploads/comprobantes/' . $fname;
            }
        }
    }

    if (empty($items)) jsonResponse(['error' => 'Debe agregar al menos un producto'], 400);

    $metodosValidos = ['fisico_bs', 'fisico_usd', 'pago_movil', 'mixto'];
    if (!in_array($metodo_pago, $metodosValidos)) jsonResponse(['error' => 'Método de pago inválido'], 400);

    if ($metodo_pago === 'mixto' && empty($pago_mixto)) {
        jsonResponse(['error' => 'Detalle el desglose del pago mixto'], 400);
    }

    $total = 0;
    foreach ($items as $item) {
        $total += (float)$item['precio_unitario'] * (int)$item['cantidad'];
    }
    $total_final   = max(0, $total - $descuento);
    $numero_venta  = generateNumeroVenta();
    $tasa_bcv      = getBcvRate();
    $total_bs      = $tasa_bcv > 0 ? round($total_final * $tasa_bcv, 2) : null;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO ventas (numero_venta, usuario_id, cliente, cliente_cedula, cliente_telefono,
                                total, descuento, total_final, metodo_pago, notas,
                                tasa_bcv, total_bs, referencia_pm, comprobante_pm)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$numero_venta, $user['id'], $cliente, $cliente_cedula, $cliente_telefono,
                         $total, $descuento, $total_final, $metodo_pago, $notas,
                         $tasa_bcv ?: null, $total_bs, $referencia_pm ?: null, $comprobante_pm]);
        $venta_id = $pdo->lastInsertId();

        foreach ($items as $item) {
            $subtotal = (float)$item['precio_unitario'] * (int)$item['cantidad'];
            $stmt = $pdo->prepare("INSERT INTO detalle_ventas (venta_id, categoria_id, descripcion, cantidad, precio_unitario, subtotal) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$venta_id, (int)$item['categoria_id'], sanitize($item['descripcion']), (int)$item['cantidad'], (float)$item['precio_unitario'], $subtotal]);
        }

        if ($metodo_pago === 'mixto') {
            foreach ($pago_mixto as $pm) {
                $stmt = $pdo->prepare("INSERT INTO ventas_pago_mixto (venta_id, metodo, monto) VALUES (?,?,?)");
                $stmt->execute([$venta_id, $pm['metodo'], (float)$pm['monto']]);
            }
        }

        $pdo->commit();

        // Telegram notification (non-blocking, best-effort)
        $ventaData = [
            'numero_venta' => $numero_venta,
            'cliente'      => $cliente,
            'total_final'  => $total_final,
            'metodo_pago'  => $metodo_pago,
        ];
        $itemsForTg = array_map(fn($it) => [
            'descripcion' => $it['descripcion'] ?? '',
            'cantidad'    => (int)$it['cantidad'],
            'subtotal'    => (float)$it['precio_unitario'] * (int)$it['cantidad'],
        ], $items);
        tgVenta($ventaData, $itemsForTg, $tasa_bcv);

        jsonResponse(['success' => true, 'numero_venta' => $numero_venta, 'id' => $venta_id, 'total_final' => $total_final, 'tasa_bcv' => $tasa_bcv, 'total_bs' => $total_bs]);
    } catch (Exception $e) {
        $pdo->rollBack();
        jsonResponse(['error' => 'Error al registrar la venta'], 500);
    }
}

if ($method === 'DELETE') {
    requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID inválido'], 400);
    $stmt = $pdo->prepare("UPDATE ventas SET estado = 'cancelada' WHERE id = ?");
    $stmt->execute([$id]);
    jsonResponse(['success' => true]);
}
