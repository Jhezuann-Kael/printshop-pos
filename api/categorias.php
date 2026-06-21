<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre");
jsonResponse(['categorias' => $stmt->fetchAll()]);
