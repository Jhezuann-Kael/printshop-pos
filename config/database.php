<?php
// Load .env if it exists (development convenience — on production set real env vars)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

$tz = $_ENV['APP_TIMEZONE'] ?? 'America/Caracas';
date_default_timezone_set($tz);

define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? 'negocio_ventas');
define('DB_USER',    $_ENV['DB_USER']    ?? 'negocio_user');
define('DB_PASS',    $_ENV['DB_PASS']    ?? '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión a la base de datos']));
        }
    }
    return $pdo;
}
