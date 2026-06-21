<?php
require_once __DIR__ . '/../config/database.php';

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['usuario']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        if (isApiRequest()) {
            http_response_code(401);
            die(json_encode(['error' => 'No autorizado']));
        }
        header('Location: /index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['rol'] !== 'admin') {
        if (isApiRequest()) {
            http_response_code(403);
            die(json_encode(['error' => 'Acceso denegado']));
        }
        header('Location: /dashboard.php');
        exit;
    }
}

function isApiRequest(): bool {
    return isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
        || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));
}

function login(string $usuario, string $password): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, nombre, usuario, password_hash, rol, activo FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if (!$user || !$user['activo']) {
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
    }

    startSecureSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol'] = $user['rol'];

    return ['success' => true, 'nombre' => $user['nombre'], 'rol' => $user['rol']];
}

function logout(): void {
    startSecureSession();
    $_SESSION = [];
    session_destroy();
}

function getCurrentUser(): array {
    startSecureSession();
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nombre' => $_SESSION['nombre'] ?? '',
        'usuario' => $_SESSION['usuario'] ?? '',
        'rol' => $_SESSION['rol'] ?? ''
    ];
}
