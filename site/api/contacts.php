<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();

// GET  list all contacts
if ($action === 'list' && $method === 'GET') {
    $user = requireAuth();
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.uuid, u.avatar, u.status, u.game_version,
               u.allow_friend_requests, u.allow_message_requests, u.show_online_status,
               u.created_at as user_created_at, u.last_seen, u.bio
        FROM contacts c
        JOIN users u ON u.id = c.user_id
        WHERE c.owner_id = ?
        ORDER BY COALESCE(c.nickname, u.username) ASC
    ");
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll();

    $contacts = array_map(function($r) {
        $u = safeUser(array_merge($r, ['id'=>$r['user_id'],'created_at'=>$r['user_created_at']]));
        $u['contactId']    = (int)$r['id'];
        $u['nickname']     = $r['nickname'];
        $u['notes']        = $r['notes'];
        $u['displayName']  = $r['nickname'] ?: $r['username'];
        $u['contactSince'] = $r['created_at'];
        return $u;
    }, $rows);

    respond(['contacts' => $contacts]);
}

// POST  add / update a contact
if ($action === 'save' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $uid  = (int)($b['userId'] ?? 0);
    if (!$uid) error('userId required');

    $target = $db->prepare('SELECT id FROM users WHERE id=?');
    $target->execute([$uid]);
    if (!$target->fetch()) error('User not found', 404);
    if ($uid === (int)$user['id']) error('Cannot add yourself');

    $nickname = isset($b['nickname']) ? trim(substr($b['nickname'], 0, 40)) : null;
    $notes    = isset($b['notes'])    ? trim(substr($b['notes'],    0, 500)) : null;

    $db->prepare("
        INSERT INTO contacts (owner_id,user_id,nickname,notes)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE nickname=VALUES(nickname), notes=VALUES(notes), updated_at=NOW()
    ")->execute([$user['id'], $uid, $nickname ?: null, $notes ?: null]);

    respond(['ok' => true]);
}

// DELETE  remove a contact
if ($action === 'remove' && $method === 'DELETE') {
    $user = requireAuth();
    $uid  = (int)($_GET['userId'] ?? 0);
    $db->prepare('DELETE FROM contacts WHERE owner_id=? AND user_id=?')->execute([$user['id'], $uid]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
