<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();

if ($action === 'list' && $method === 'GET') {
    $user = requireAuth(); $uid = $user['id'];

    $friends = $db->prepare("
        SELECT u.*,
               c.nickname AS contact_nickname,
               c.notes    AS contact_notes,
               f.created_at AS friend_since
        FROM friends f
        JOIN users u ON u.id = IF(f.from_id=?,f.to_id,f.from_id)
        LEFT JOIN contacts c ON c.owner_id=? AND c.user_id=u.id
        WHERE (f.from_id=? OR f.to_id=?) AND f.status='accepted'
        ORDER BY u.username ASC
    ");
    $friends->execute([$uid,$uid,$uid,$uid]);
    $flist = $friends->fetchAll();

    $incoming = $db->prepare("
        SELECT u.* FROM friends f JOIN users u ON u.id=f.from_id
        WHERE f.to_id=? AND f.status='pending'
    ");
    $incoming->execute([$uid]);

    $outgoing = $db->prepare("
        SELECT u.* FROM friends f JOIN users u ON u.id=f.to_id
        WHERE f.from_id=? AND f.status='pending'
    ");
    $outgoing->execute([$uid]);

    $enriched = array_map(function($row) {
        $u = safeUser($row);
        $u['nickname']     = $row['contact_nickname'] ?? null;
        $u['contactNotes'] = $row['contact_notes'] ?? null;
        return $u;
    }, $flist);

    respond([
        'friends'  => $enriched,
        'incoming' => array_map('safeUser', $incoming->fetchAll()),
        'outgoing' => array_map('safeUser', $outgoing->fetchAll()),
    ]);
}

if ($action === 'request' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $stmt = $db->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$b['username'] ?? '']);
    $target = $stmt->fetch();
    if (!$target) error('User not found', 404);
    if ($target['id'] == $user['id']) error('Cannot add yourself');

    if (!$target['allow_friend_requests']) error('This user is not accepting friend requests', 403);

    $ex = $db->prepare('SELECT * FROM friends WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?)');
    $ex->execute([$user['id'],$target['id'],$target['id'],$user['id']]);
    $row = $ex->fetch();
    if ($row) error($row['status']==='accepted' ? 'Already friends' : 'Request already sent', 409);

    $db->prepare('INSERT INTO friends (from_id,to_id) VALUES (?,?)')->execute([$user['id'],$target['id']]);
    respond(['ok' => true]);
}

if ($action === 'accept' && $method === 'POST') {
    $user = requireAuth();
    $r = $db->prepare("UPDATE friends SET status='accepted' WHERE from_id=? AND to_id=? AND status='pending'");
    $r->execute([body()['userId'], $user['id']]);
    if (!$r->rowCount()) error('No pending request found', 404);
    respond(['ok' => true]);
}

if ($action === 'decline' && $method === 'POST') {
    $user = requireAuth();
    $fid  = (int)(body()['userId'] ?? 0);
    $db->prepare("DELETE FROM friends WHERE from_id=? AND to_id=? AND status='pending'")
       ->execute([$fid, $user['id']]);
    respond(['ok' => true]);
}

if ($action === 'remove' && $method === 'DELETE') {
    $user = requireAuth();
    $fid  = (int)($_GET['userId'] ?? 0);
    $db->prepare('DELETE FROM friends WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?)')
       ->execute([$user['id'],$fid,$fid,$user['id']]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
