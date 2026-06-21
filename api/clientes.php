<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user   = getCurrentUser();

/* ── Buscar clientes (autocomplete) ── */
if ($method === 'GET' && isset($_GET['buscar'])) {
    $q    = '%' . sanitize($_GET['buscar']) . '%';
    $stmt = $pdo->prepare("SELECT id, tipo, nombre, cedula_rif, telefono FROM clientes WHERE nombre LIKE ? OR cedula_rif LIKE ? OR telefono LIKE ? ORDER BY nombre LIMIT 15");
    $stmt->execute([$q, $q, $q]);
    jsonResponse(['clientes' => $stmt->fetchAll()]);
}

/* ── Obtener uno ── */
if ($method === 'GET' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ? LIMIT 1");
    $stmt->execute([(int)$_GET['id']]);
    $c = $stmt->fetch();
    if (!$c) jsonResponse(['error' => 'No encontrado'], 404);
    jsonResponse($c);
}

/* ── Listar todos ── */
if ($method === 'GET') {
    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = 30;
    $off   = ($page - 1) * $limit;
    $q     = sanitize($_GET['q'] ?? '');
    $tipo  = in_array($_GET['tipo'] ?? '', ['persona','empresa']) ? $_GET['tipo'] : '';

    $where  = [];
    $params = [];
    if ($q) { $where[] = '(c.nombre LIKE ? OR c.cedula_rif LIKE ? OR c.telefono LIKE ?)'; $params = array_merge($params, ["%$q%","%$q%","%$q%"]); }
    if ($tipo) { $where[] = 'c.tipo = ?'; $params[] = $tipo; }
    $sql = "FROM clientes c LEFT JOIN usuarios u ON u.id = c.registrado_por" . ($where ? " WHERE " . implode(' AND ', $where) : '');

    $cnt = $pdo->prepare("SELECT COUNT(*) $sql");
    $cnt->execute($params);
    $total   = (int)$cnt->fetchColumn();
    $paginas = (int)ceil($total / $limit) ?: 1;

    $stmt = $pdo->prepare("SELECT c.*, u.nombre as registrado_por_nombre $sql ORDER BY c.nombre LIMIT $limit OFFSET $off");
    $stmt->execute($params);

    jsonResponse(['clientes' => $stmt->fetchAll(), 'total' => $total, 'pagina' => $page, 'paginas' => $paginas]);
}

/* ── Crear cliente ── */
if ($method === 'POST') {
    $d      = getJsonInput();
    $tipo   = in_array($d['tipo'] ?? '', ['persona','empresa']) ? $d['tipo'] : 'persona';
    $nombre = sanitize($d['nombre'] ?? '');
    $ced    = sanitize($d['cedula_rif'] ?? '');
    $tel    = sanitize($d['telefono']  ?? '');
    $notas  = sanitize($d['notas']    ?? '');

    if (!$nombre) jsonResponse(['error' => 'El nombre es requerido'], 400);

    if ($ced) {
        $chk = $pdo->prepare("SELECT id FROM clientes WHERE cedula_rif = ? LIMIT 1");
        $chk->execute([$ced]);
        if ($chk->fetch()) jsonResponse(['error' => 'Ya existe un cliente con esa cédula/RIF'], 409);
    }

    $stmt = $pdo->prepare("INSERT INTO clientes (tipo, nombre, cedula_rif, telefono, notas, registrado_por) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$tipo, $nombre, $ced ?: null, $tel ?: null, $notas ?: null, $user['id']]);
    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
}

/* ── Editar cliente ── */
if ($method === 'PUT') {
    $d   = getJsonInput();
    $id  = (int)($d['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID requerido'], 400);

    $tipo   = in_array($d['tipo'] ?? '', ['persona','empresa']) ? $d['tipo'] : 'persona';
    $nombre = sanitize($d['nombre'] ?? '');
    $ced    = sanitize($d['cedula_rif'] ?? '');
    $tel    = sanitize($d['telefono']   ?? '');
    $notas  = sanitize($d['notas']      ?? '');

    if (!$nombre) jsonResponse(['error' => 'El nombre es requerido'], 400);

    if ($ced) {
        $chk = $pdo->prepare("SELECT id FROM clientes WHERE cedula_rif = ? AND id != ? LIMIT 1");
        $chk->execute([$ced, $id]);
        if ($chk->fetch()) jsonResponse(['error' => 'Ya existe un cliente con esa cédula/RIF'], 409);
    }

    $pdo->prepare("UPDATE clientes SET tipo=?, nombre=?, cedula_rif=?, telefono=?, notas=? WHERE id=?")
        ->execute([$tipo, $nombre, $ced ?: null, $tel ?: null, $notas ?: null, $id]);
    jsonResponse(['success' => true]);
}

/* ── Eliminar cliente ── */
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID requerido'], 400);
    $pdo->prepare("DELETE FROM clientes WHERE id=?")->execute([$id]);
    jsonResponse(['success' => true]);
}
