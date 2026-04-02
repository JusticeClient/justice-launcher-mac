<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();

if ($action === 'list' && $method === 'GET') {
    try {
        $stmt = $db->query("SELECT e.*, u.username AS creator, s.name AS server_name FROM events e JOIN users u ON u.id=e.created_by LEFT JOIN servers s ON s.id=e.server_id WHERE e.ends_at IS NULL OR e.ends_at >= NOW() ORDER BY e.starts_at ASC LIMIT 20");
        respond(['events' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['events' => []]); }
}

if ($action === 'all' && $method === 'GET') {
    try {
        $stmt = $db->query("SELECT e.*, u.username AS creator, s.name AS server_name FROM events e JOIN users u ON u.id=e.created_by LEFT JOIN servers s ON s.id=e.server_id ORDER BY e.starts_at DESC LIMIT 50");
        respond(['events' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['events' => []]); }
}

$user = requireStaff();

if ($action === 'create' && $method === 'POST') {
    $b = body();
    $title = trim($b['title'] ?? '');
    $desc  = trim($b['description'] ?? '');
    $start = trim($b['starts_at'] ?? '');
    $end   = trim($b['ends_at'] ?? '') ?: null;
    $color = trim($b['color'] ?? '#7c3aed');
    $sid   = (int)($b['server_id'] ?? 0) ?: null;
    if (!$title || !$start) error('Title and start date required');
    try {
        $db->prepare("INSERT INTO events (title,description,server_id,starts_at,ends_at,color,created_by) VALUES (?,?,?,?,?,?,?)")
           ->execute([$title, $desc, $sid, $start, $end, $color, $user['id']]);
        respond(['ok' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) { error('Something went wrong. Please try again.'); }
}

if ($method === 'DELETE') {
    try {
        $db->prepare("DELETE FROM events WHERE id=?")->execute([(int)($_GET['id']??0)]);
    } catch (Exception $e) {}
    respond(['ok' => true]);
}

error('Unknown action', 404);
