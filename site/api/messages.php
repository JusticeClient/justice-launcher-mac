<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

if ($action === 'unread' && $method === 'GET') {
    $user = requireAuth();
    $stmt = $db->prepare("SELECT from_id AS userId, COUNT(*) AS cnt FROM messages WHERE to_id=? AND read_at IS NULL GROUP BY from_id");
    $stmt->execute([$user['id']]);
    respond(['unread' => $stmt->fetchAll()]);
}

if ($action === 'typing' && $method === 'POST') {
    $user = requireAuth();
    $fid  = (int)(body()['userId'] ?? 0);
    if (!$fid) error('userId required');
    $db->prepare("INSERT INTO typing_status (user_id, to_user_id, updated_at) VALUES (?,?,NOW()) ON DUPLICATE KEY UPDATE updated_at=NOW()")
       ->execute([$user['id'], $fid]);
    respond(['ok' => true]);
}

if ($action === 'typing' && $method === 'GET') {
    $user = requireAuth();
    $fid  = (int)($_GET['userId'] ?? 0);
    if (!$fid) error('userId required');
    $stmt = $db->prepare("SELECT updated_at FROM typing_status WHERE user_id=? AND to_user_id=?");
    $stmt->execute([$fid, $user['id']]);
    $row = $stmt->fetch();
    $isTyping = $row && (strtotime($row['updated_at']) > time() - 4);
    respond(['typing' => $isTyping]);
}

if ($method === 'GET' && !$action) {
    $user  = requireAuth(); $uid = $user['id'];
    $fid   = (int)($_GET['userId'] ?? 0);
    $limit = min((int)($_GET['limit'] ?? 50), 200);
    $after = (int)($_GET['after'] ?? 0);
    if (!$fid) error('userId required');

    $chk = $db->prepare("SELECT 1 FROM friends WHERE ((from_id=? AND to_id=?) OR (from_id=? AND to_id=?)) AND status='accepted'");
    $chk->execute([$uid,$fid,$fid,$uid]);
    if (!$chk->fetch()) error('Not friends', 403);

    if ($after > 0) {
        $stmt = $db->prepare("SELECT * FROM messages WHERE ((from_id=? AND to_id=?) OR (from_id=? AND to_id=?)) AND id > ? ORDER BY created_at ASC LIMIT ?");
        $stmt->execute([$uid,$fid,$fid,$uid,$after,$limit]);
        $msgs = $stmt->fetchAll();
    } else {
        $stmt = $db->prepare("SELECT * FROM messages WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?) ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$uid,$fid,$fid,$uid,$limit]);
        $msgs = array_reverse($stmt->fetchAll());
    }

    $db->prepare("UPDATE messages SET read_at=NOW() WHERE from_id=? AND to_id=? AND read_at IS NULL")->execute([$fid,$uid]);
    respond(['messages' => $msgs]);
}

if ($method === 'POST' && !$action) {
    $user    = requireAuth(); $uid = $user['id'];
    $fid     = (int)($_GET['userId'] ?? 0);
    $b       = body();
    $content  = trim($b['content'] ?? '');
    $imageUrl = trim($b['imageUrl'] ?? '');

    if (!$fid) error('userId required');
    if (!$content && !$imageUrl) error('Message cannot be empty');
    if (strlen($content) > 2000) error('Message too long');
    if ($imageUrl && strlen($imageUrl) > 500) error('Image URL too long');

    $chk = $db->prepare("SELECT 1 FROM friends WHERE ((from_id=? AND to_id=?) OR (from_id=? AND to_id=?)) AND status='accepted'");
    $chk->execute([$uid,$fid,$fid,$uid]);
    if (!$chk->fetch()) error('Not friends', 403);

    $db->prepare('INSERT INTO messages (from_id,to_id,content,image_url) VALUES (?,?,?,?)')
       ->execute([$uid, $fid, $content, $imageUrl ?: null]);
    $msg = $db->prepare('SELECT * FROM messages WHERE id=?');
    $msg->execute([$db->lastInsertId()]);
    respond(['message' => $msg->fetch()], 201);
}

error('Method not allowed', 405);
