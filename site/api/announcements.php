<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db = getDB();
set_exception_handler(function(Throwable $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => "Something went wrong. Please try again later." . $e->getMessage()]);
    exit;
});

if ($action === 'pending' && $method === 'GET') {
    $user  = requireAuth();
    $plus  = !empty($user['plus_member']);
    $stmt  = $db->prepare("SELECT id,title,body,type FROM announcements WHERE sent=1 AND (target='all' OR (target='plus' AND ?)) ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$plus ? 1 : 0]);
    respond(['announcements' => $stmt->fetchAll()]);
}

$admin = requireAdmin();

if ($action === 'schedule' && $method === 'POST') {
    $b      = body();
    $title  = trim($b['title'] ?? '');
    $body2  = trim($b['body']  ?? '');
    $type   = in_array($b['type']??'', ['info','warning','success','error']) ? $b['type'] : 'info';
    $target = in_array($b['target']??'', ['all','plus','new']) ? $b['target'] : 'all';
    $time   = $b['send_at'] ?? '';
    if (!$title || !$body2 || !$time) error('Title, body and send_at required');
    $db->prepare("INSERT INTO announcements (title,body,type,target,send_at,created_by) VALUES (?,?,?,?,?,?)")
       ->execute([$title, $body2, $type, $target, $time, $admin['id']]);
    respond(['ok' => true]);
}

if ($action === 'list' && $method === 'GET') {
    $stmt = $db->query("SELECT a.*,u.username AS created_by_name FROM announcements a JOIN users u ON u.id=a.created_by ORDER BY a.send_at DESC LIMIT 50");
    respond(['announcements' => $stmt->fetchAll()]);
}

if ($method === 'DELETE') {
    $db->prepare("DELETE FROM announcements WHERE id=?")->execute([(int)($_GET['id']??0)]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
