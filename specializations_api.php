<?php
require_once __DIR__ . '/db_pdo.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$category = strtolower(trim((string)($_GET['category'] ?? '')));
if (!preg_match('/^[a-z0-9-]{1,120}$/', $category)) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid-category'], JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    $pdo = youruniPdo();
    $stmt = $pdo->prepare("SELECT s.slug, s.title, s.title_my, s.description, s.description_my, s.work_context, s.work_context_my, s.icon, s.badge_text, s.sort_order
        FROM specializations s
        JOIN opportunities o ON o.id = s.opportunity_id
        WHERE o.slug = :category AND o.is_active = 1 AND s.is_active = 1
        ORDER BY s.sort_order ASC, s.title ASC");
    $stmt->execute(['category' => $category]);
    echo json_encode(['category' => $category, 'specializations' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('YourUni specializations API: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'unavailable'], JSON_UNESCAPED_UNICODE);
}
