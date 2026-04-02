<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $version = $_GET['version'] ?? '';
    try {
        if ($version) {
            $stmt = $db->prepare("SELECT * FROM patch_notes WHERE version=?");
            $stmt->execute([$version]);
            respond(['note' => $stmt->fetch() ?: null]);
        } else {
            $stmt = $db->query("SELECT * FROM patch_notes ORDER BY created_at DESC LIMIT 10");
            respond(['notes' => $stmt->fetchAll()]);
        }
    } catch (Exception $e) { respond(['note' => null, 'notes' => []]); }
}

$admin = requireAdmin();

if ($method === 'POST') {
    $b       = body();
    $version = trim($b['version'] ?? '');
    $title   = trim($b['title'] ?? '');
    $body2   = trim($b['body'] ?? '');
    if (!$version || !$title || !$body2) error('version, title and body required');
    try {
        $db->prepare("INSERT INTO patch_notes (version,title,body) VALUES (?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title),body=VALUES(body)")
           ->execute([$version, $title, $body2]);
        respond(['ok' => true]);
    } catch (Exception $e) { error('Something went wrong. Please try again.'); }
}

if ($method === 'DELETE') {
    try { $db->prepare("DELETE FROM patch_notes WHERE version=?")->execute([$_GET['version']??'']); } catch (Exception $e) {}
    respond(['ok' => true]);
}

error('Method not allowed', 405);
