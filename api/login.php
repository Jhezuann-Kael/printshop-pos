<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método no permitido'], 405);
}

$data = getJsonInput();
$usuario = trim($data['usuario'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($usuario) || empty($password)) {
    jsonResponse(['error' => 'Usuario y contraseña son requeridos'], 400);
}

$result = login($usuario, $password);

if ($result['success']) {
    jsonResponse(['success' => true, 'nombre' => $result['nombre'], 'rol' => $result['rol']]);
} else {
    jsonResponse(['error' => $result['message']], 401);
}
