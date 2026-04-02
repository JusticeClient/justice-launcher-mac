<?php
require_once __DIR__ . '/../includes/api.php';

$db = getDB();

try {
    $stmt = $db->prepare("SELECT id, message, type, created_at FROM admin_broadcasts WHERE expires_at > NOW() ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    respond(['broadcasts' => $stmt->fetchAll()]);
} catch (Exception $e) {
    respond(['broadcasts' => []]);
}
