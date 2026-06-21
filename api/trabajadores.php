<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();
header('Content-Type: application/json; charset=utf-8');

$pdo    = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user   = getCurrentUser();

/* ── Historial de pagos de un trabajador ── */
if ($method === 'GET' && isset($_GET['pagos'])) {
    $uid  = (int)$_GET['pagos'];
    $stmt = $pdo->prepare("
        SELECT pt.*, u.nombre as registrado_por_nombre
        FROM pagos_trabajadores pt JOIN usuarios u ON u.id = pt.registrado_por
        WHERE pt.usuario_id = ? ORDER BY pt.fecha DESC LIMIT 60
    ");
    $stmt->execute([$uid]);
    jsonResponse(['pagos' => $stmt->fetchAll()]);
}

/* ── Datos de un trabajador ── */
if ($method === 'GET' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $t = $stmt->fetch();
    if (!$t) jsonResponse(['error' => 'No encontrado'], 404);
    unset($t['password_hash']);
    jsonResponse($t);
}

/* ── Listar todos ── */
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, nombre, apellido, usuario, email, rol, activo, cedula, telefono, lugar_residencia, fecha_nacimiento, banco_pago_movil, telefono_pago_movil, cedula_pago_movil, creado_en FROM usuarios ORDER BY rol DESC, nombre");
    jsonResponse(['trabajadores' => $stmt->fetchAll()]);
}

/* ── Registrar pago ── */
if ($method === 'POST' && isset($_GET['pago'])) {
    // multipart/form-data (file upload supported)
    $uid     = (int)($_POST['usuario_id'] ?? 0);
    $monto   = (float)($_POST['monto']    ?? 0);
    $desc    = sanitize($_POST['descripcion'] ?? '');
    $fecha   = $_POST['fecha']   ?? date('Y-m-d');
    $tasaBcv = (float)($_POST['tasa_bcv'] ?? 0);
    $montoBs = (float)($_POST['monto_bs'] ?? 0);

    if (!$uid || $monto <= 0) jsonResponse(['error' => 'Datos inválidos'], 400);

    $imgPath = null;
    if (!empty($_FILES['imagen']['tmp_name'])) {
        $allowedMime = ['image/jpeg','image/png','image/webp','image/gif'];
        if (in_array($_FILES['imagen']['type'], $allowedMime)) {
            $ext   = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $dir   = __DIR__ . '/../uploads/comprobantes/';
            $fname = 'comp_' . $uid . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $fname)) {
                $imgPath = 'uploads/comprobantes/' . $fname;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO pagos_trabajadores
        (usuario_id, monto, moneda, descripcion, fecha, registrado_por, tasa_bcv, monto_bs, imagen_comprobante)
        VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $monto, 'usd', $desc, $fecha, $user['id'],
                    $tasaBcv ?: null, $montoBs ?: null, $imgPath]);
    jsonResponse(['success' => true]);
}

/* ── Crear trabajador ── */
if ($method === 'POST') {
    $d = getJsonInput();

    $nombre    = sanitize($d['nombre']   ?? '');
    $apellido  = sanitize($d['apellido'] ?? '');
    $usuario   = sanitize($d['usuario']  ?? '');
    $email     = sanitize($d['email']    ?? '');
    $rol       = in_array($d['rol'] ?? '', ['admin','vendedor']) ? $d['rol'] : 'vendedor';
    $password  = $d['password'] ?? '';
    $telefono  = sanitize($d['telefono']  ?? '');
    $cedula    = sanitize($d['cedula']    ?? '');
    $lugar     = sanitize($d['lugar_residencia']    ?? '');
    $fnac      = $d['fecha_nacimiento']  ?? null;
    $banco     = sanitize($d['banco_pago_movil']    ?? '');
    $tel_pm    = sanitize($d['telefono_pago_movil'] ?? '');
    $ced_pm    = sanitize($d['cedula_pago_movil']   ?? '');

    if (!$nombre || !$usuario || !$password)
        jsonResponse(['error' => 'Nombre, usuario y contraseña son requeridos'], 400);

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    if ($stmt->fetch()) jsonResponse(['error' => 'El nombre de usuario ya existe'], 409);

    if ($cedula) {
        $chk = $pdo->prepare("SELECT id FROM usuarios WHERE cedula = ? LIMIT 1");
        $chk->execute([$cedula]);
        if ($chk->fetch()) jsonResponse(['error' => 'Ya existe un trabajador con esa cédula'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios
          (nombre, apellido, usuario, email, password_hash, rol, telefono, cedula,
           lugar_residencia, fecha_nacimiento, banco_pago_movil, telefono_pago_movil, cedula_pago_movil)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([$nombre, $apellido, $usuario, $email, $hash, $rol, $telefono, $cedula,
                    $lugar, $fnac ?: null, $banco, $tel_pm, $ced_pm]);
    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
}

/* ── Editar trabajador ── */
if ($method === 'PUT') {
    $d  = getJsonInput();
    $id = (int)($d['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID requerido'], 400);

    $fields = ['nombre','apellido','email','rol','telefono','cedula','lugar_residencia',
               'fecha_nacimiento','banco_pago_movil','telefono_pago_movil','cedula_pago_movil'];
    $set = []; $params = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $d)) {
            $set[]    = "$f = ?";
            $params[] = ($d[$f] === '' || $d[$f] === null) ? null : sanitize((string)$d[$f]);
        }
    }
    if (isset($d['activo'])) { $set[] = 'activo = ?'; $params[] = (int)(bool)$d['activo']; }
    if (!empty($d['password'])) {
        $set[]    = 'password_hash = ?';
        $params[] = password_hash($d['password'], PASSWORD_BCRYPT);
    }

    if ($set) {
        $params[] = $id;
        $pdo->prepare("UPDATE usuarios SET " . implode(', ', $set) . " WHERE id = ?")->execute($params);
    }
    jsonResponse(['success' => true]);
}
