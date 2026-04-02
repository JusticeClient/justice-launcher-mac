<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();

// GET  list incoming message requests
if ($action === 'list' && $method === 'GET') {
    $user = requireAuth();
    $stmt = $db->prepare("
        SELECT mr.*, u.username, u.avatar, u.uuid
        FROM message_requests mr
        JOIN users u ON u.id = mr.from_id
        WHERE mr.to_id=? AND mr.status='pending'
        ORDER BY mr.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    respond(['requests' => $stmt->fetchAll()]);
}

// POST  send a message request to someone
if ($action === 'send' && $method === 'POST') {
    $user    = requireAuth(); $uid = $user['id'];
    $b       = body();
    $toUser  = $db->prepare('SELECT * FROM users WHERE username=?');
    $toUser->execute([$b['username'] ?? '']);
    $target  = $toUser->fetch();
    if (!$target) error('User not found', 404);
    if ($target['id'] == $uid) error('Cannot message yourself');

    // Check privacy
    if (!$target['allow_message_requests']) error('This user is not accepting message requests', 403);

    // Already friends → just send a message directly
    $areFriends = $db->prepare("SELECT 1 FROM friends WHERE ((from_id=? AND to_id=?) OR (from_id=? AND to_id=?)) AND status='accepted'");
    $areFriends->execute([$uid,$target['id'],$target['id'],$uid]);
    if ($areFriends->fetch()) error('Already friends — send a direct message instead', 409);

    $content = trim($b['content'] ?? '');
    if (!$content) error('Message cannot be empty');
    if (strlen($content) > 500) error('Message request too long (max 500 chars)');

    // Upsert — replace if already sent (resend)
    $db->prepare("
        INSERT INTO message_requests (from_id,to_id,content) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE content=VALUES(content), status='pending', created_at=NOW()
    ")->execute([$uid, $target['id'], $content]);

    respond(['ok' => true]);
}

// POST  accept a message request
if ($action === 'accept' && $method === 'POST') {
    $user = requireAuth();
    $rid  = (int)(body()['requestId'] ?? 0);

    $req = $db->prepare("SELECT * FROM message_requests WHERE id=? AND to_id=? AND status='pending'");
    $req->execute([$rid, $user['id']]);
    $request = $req->fetch();
    if (!$request) error('Request not found', 404);

    // Accept → add as friends + move content into real messages
    $db->prepare("UPDATE message_requests SET status='accepted' WHERE id=?")->execute([$rid]);

    // Create friendship
    $ex = $db->prepare('SELECT * FROM friends WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?)');
    $ex->execute([$request['from_id'],$user['id'],$user['id'],$request['from_id']]);
    if (!$ex->fetch()) {
        $db->prepare("INSERT INTO friends (from_id,to_id,status) VALUES (?,?,'accepted')")->execute([$request['from_id'],$user['id']]);
    }

    // Port the message across
    $db->prepare('INSERT INTO messages (from_id,to_id,content,created_at) VALUES (?,?,?,?)')
       ->execute([$request['from_id'],$user['id'],$request['content'],$request['created_at']]);

    respond(['ok' => true, 'fromId' => (int)$request['from_id']]);
}

// POST  decline
if ($action === 'decline' && $method === 'POST') {
    $user = requireAuth();
    $rid  = (int)(body()['requestId'] ?? 0);
    $db->prepare("UPDATE message_requests SET status='declined' WHERE id=? AND to_id=?")->execute([$rid,$user['id']]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
