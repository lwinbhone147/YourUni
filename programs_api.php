<?php
require_once __DIR__ . '/db_pdo.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$key = strtolower(trim((string)($_GET['key'] ?? '')));
if (!preg_match('/^[a-z0-9-]{1,120}$/', $key)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid-key'], JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    $pdo = youruniPdo();
    $mappings = [];
    // The normalized specialization tables are authoritative when mappings exist.
    try {
        $stmt = $pdo->prepare("SELECT u.page, u.uni_name, su.status, su.source_url, su.last_verified_at
            FROM specialization_universities su
            JOIN specializations s ON s.id = su.specialization_id
            JOIN uni u ON u.id = su.uni_id
            WHERE s.slug = :key AND s.is_active = 1
            ORDER BY CASE su.status WHEN 'offered' THEN 0 WHEN 'unverified' THEN 1 ELSE 2 END, u.uni_name");
        $stmt->execute(['key' => $key]);
        $mappings = $stmt->fetchAll();
    } catch (Throwable $schemaError) {
        // Backward-compatible fallback for an older project/database before the new tables were imported.
        error_log('YourUni specialization mapping query unavailable; trying legacy table: ' . $schemaError->getMessage());
    }
    if (!$mappings) {
        $legacy = $pdo->prepare("SELECT u.page, u.uni_name, p.status, p.source_url, p.last_verified_at
            FROM university_programs p JOIN uni u ON u.id = p.uni_id
            WHERE p.program_key = :key
            ORDER BY CASE p.status WHEN 'offered' THEN 0 WHEN 'unverified' THEN 1 ELSE 2 END, u.uni_name");
        $legacy->execute(['key' => $key]);
        $mappings = $legacy->fetchAll();
    }
    echo json_encode(['key' => $key, 'mappings' => $mappings], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('YourUni programs API: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'unavailable'], JSON_UNESCAPED_UNICODE);
}
