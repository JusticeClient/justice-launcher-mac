<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();
$user   = requireAuth();
$uid    = $user['id'];

if ($action === 'list' && $method === 'GET') {
    $targetId = (int)($_GET['userId'] ?? $uid);
    try {
        $stmt = $db->prepare("SELECT s.*, u.username FROM screenshots s JOIN users u ON u.id=s.user_id WHERE s.user_id=? AND (s.public=1 OR s.user_id=?) ORDER BY s.created_at DESC LIMIT 30");
        $stmt->execute([$targetId, $uid]);
        respond(['screenshots' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['screenshots' => []]); }
}

if ($action === 'upload' && $method === 'POST') {
    $b       = body();
    $data    = $b['image'] ?? '';
    $caption = substr(trim($b['caption'] ?? ''), 0, 300);
    $public  = isset($b['public']) ? (int)$b['public'] : 1;
    if (!$data) error('image required');

    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $data)) error('Invalid image format');
    $data = preg_replace('/^data:image\/[a-z]+;base64,/', '', $data);
    $raw  = base64_decode($data);
    if (!$raw || strlen($raw) > 8 * 1024 * 1024) error('Image too large (max 8MB)');

    $uploadDir = __DIR__ . '/../uploads/screenshots/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext      = 'png';
    $filename = $uid . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    file_put_contents($uploadDir . $filename, $raw);

    try {
        $db->prepare("INSERT INTO screenshots (user_id, filename, caption, public) VALUES (?,?,?,?)")
           ->execute([$uid, $filename, $caption, $public]);
        respond(['ok' => true, 'id' => $db->lastInsertId(), 'filename' => $filename]);
    } catch (Exception $e) {
        @unlink($uploadDir . $filename);
        error('Something went wrong. Please try again.');
    }
}

if ($action === 'delete' && $method === 'DELETE') {
    $id   = (int)($_GET['id'] ?? 0);
    try {
        $row = $db->prepare("SELECT filename, user_id FROM screenshots WHERE id=?");
        $row->execute([$id]); $row = $row->fetch();
        if ($row && ($row['user_id'] == $uid || $user['role'] === 'admin' || $user['role'] === 'staff')) {
            @unlink(__DIR__ . '/../uploads/screenshots/' . $row['filename']);
            $db->prepare("DELETE FROM screenshots WHERE id=?")->execute([$id]);
        }
    } catch (Exception $e) {}
    respond(['ok' => true]);
}

error('Unknown action', 404);
