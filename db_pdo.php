<?php
require_once __DIR__ . '/config.php';
function youruniPdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host=' . YOURUNI_DB_HOST . ';port=' . YOURUNI_DB_PORT . ';dbname=' . YOURUNI_DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, YOURUNI_DB_USER, YOURUNI_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
