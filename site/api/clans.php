<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'my';
$db = getDB();
set_exception_handler(function(Throwable $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => "Something went wrong. Please try again later." . $e->getMessage()]);
    exit;
});
$user   = requireAuth();
$uid    = $user['id'];

if ($action === 'my' && $method === 'GET') {
    if (!$user['clan_id']) respond(['clan' => null]);
    $c = $db->prepare("SELECT c.*, u.username AS owner_name FROM clans c JOIN users u ON u.id=c.owner_id WHERE c.id=?");
    $c->execute([$user['clan_id']]);
    $clan = $c->fetch();
    if (!$clan) respond(['clan' => null]);
    $m = $db->prepare("SELECT cm.role, cm.joined_at, u.id, u.username, u.status, u.mc_username, u.role AS user_role FROM clan_members cm JOIN users u ON u.id=cm.user_id WHERE cm.clan_id=? ORDER BY FIELD(cm.role,'owner','officer','member'), u.username");
    $m->execute([$clan['id']]);
    respond(['clan' => $clan, 'members' => $m->fetchAll()]);
}

if ($action === 'search' && $method === 'GET') {
    $q = trim($_GET['q'] ?? '');
    $stmt = $db->prepare("SELECT c.*, u.username AS owner_name, COUNT(cm.user_id) AS member_count FROM clans c JOIN users u ON u.id=c.owner_id LEFT JOIN clan_members cm ON cm.clan_id=c.id WHERE c.name LIKE ? OR c.tag LIKE ? GROUP BY c.id LIMIT 20");
    $like = '%'.$q.'%';
    $stmt->execute([$like, $like]);
    respond(['clans' => $stmt->fetchAll()]);
}

if ($action === 'get' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $c  = $db->prepare("SELECT c.*, u.username AS owner_name, COUNT(cm.user_id) AS member_count FROM clans c JOIN users u ON u.id=c.owner_id LEFT JOIN clan_members cm ON cm.clan_id=c.id WHERE c.id=? GROUP BY c.id");
    $c->execute([$id]);
    $clan = $c->fetch();
    if (!$clan) error('Not found', 404);
    $m = $db->prepare("SELECT cm.role, u.id, u.username, u.status, u.mc_username FROM clan_members cm JOIN users u ON u.id=cm.user_id WHERE cm.clan_id=?");
    $m->execute([$id]);
    respond(['clan' => $clan, 'members' => $m->fetchAll()]);
}

if ($action === 'create' && $method === 'POST') {
    if ($user['clan_id']) error('You are already in a clan');
    $b    = body();
    $name = trim($b['name'] ?? '');
    $tag  = strtoupper(trim($b['tag'] ?? ''));
    $desc = trim($b['description'] ?? '');
    if (!$name || !$tag) error('Name and tag required');
    if (strlen($name) > 50) error('Name too long');
    if (strlen($tag) > 8 || strlen($tag) < 2) error('Tag must be 2-8 characters');
    if (!preg_match('/^[A-Z0-9]+$/', $tag)) error('Tag must be letters and numbers only');
    $db->prepare("INSERT INTO clans (name,tag,description,owner_id,color) VALUES (?,?,?,?,?)")
       ->execute([$name, $tag, $desc, $uid, $b['color'] ?? '#7c3aed']);
    $cid = $db->lastInsertId();
    $db->prepare("INSERT INTO clan_members (clan_id,user_id,role) VALUES (?,?,'owner')")->execute([$cid, $uid]);
    $db->prepare("UPDATE users SET clan_id=? WHERE id=?")->execute([$cid, $uid]);
    respond(['ok' => true, 'id' => $cid]);
}

if ($action === 'invite' && $method === 'POST') {
    if (!$user['clan_id']) error('You are not in a clan');
    $targetId = (int)(body()['userId'] ?? 0);
    $role = $db->prepare("SELECT role FROM clan_members WHERE clan_id=? AND user_id=?");
    $role->execute([$user['clan_id'], $uid]);
    $r = $role->fetch();
    if (!$r || $r['role'] === 'member') error('Only officers and owners can invite', 403);
    $target = $db->prepare("SELECT id, clan_id FROM users WHERE id=?"); $target->execute([$targetId]); $target = $target->fetch();
    if (!$target) error('User not found', 404);
    if ($target['clan_id']) error('User is already in a clan');
    $db->prepare("INSERT IGNORE INTO clan_invites (clan_id,user_id,invited_by) VALUES (?,?,?)")->execute([$user['clan_id'], $targetId, $uid]);
    respond(['ok' => true]);
}

if ($action === 'invites' && $method === 'GET') {
    $stmt = $db->prepare("SELECT ci.*, c.name AS clan_name, c.tag, c.color, u.username AS invited_by_name FROM clan_invites ci JOIN clans c ON c.id=ci.clan_id JOIN users u ON u.id=ci.invited_by WHERE ci.user_id=?");
    $stmt->execute([$uid]);
    respond(['invites' => $stmt->fetchAll()]);
}

if ($action === 'accept' && $method === 'POST') {
    $cid = (int)(body()['clan_id'] ?? 0);
    if ($user['clan_id']) error('Already in a clan');
    $inv = $db->prepare("SELECT id FROM clan_invites WHERE clan_id=? AND user_id=?"); $inv->execute([$cid, $uid]); $inv = $inv->fetch();
    if (!$inv) error('No invite found', 404);
    $db->prepare("INSERT IGNORE INTO clan_members (clan_id,user_id,role) VALUES (?,'member')")->execute([$cid, $uid]);
    $db->prepare("INSERT IGNORE INTO clan_members (clan_id,user_id,role) VALUES (?,?,'member')")->execute([$cid, $uid]);
    $db->prepare("UPDATE users SET clan_id=? WHERE id=?")->execute([$cid, $uid]);
    $db->prepare("DELETE FROM clan_invites WHERE clan_id=? AND user_id=?")->execute([$cid, $uid]);
    respond(['ok' => true]);
}

if ($action === 'leave' && $method === 'POST') {
    if (!$user['clan_id']) error('Not in a clan');
    $cid  = $user['clan_id'];
    $kick = (int)(body()['userId'] ?? $uid);
    if ($kick !== $uid) {
        $r = $db->prepare("SELECT role FROM clan_members WHERE clan_id=? AND user_id=?"); $r->execute([$cid, $uid]); $r = $r->fetch();
        if (!$r || $r['role'] === 'member') error('Forbidden', 403);
    }
    $db->prepare("DELETE FROM clan_members WHERE clan_id=? AND user_id=?")->execute([$cid, $kick]);
    $db->prepare("UPDATE users SET clan_id=NULL WHERE id=?")->execute([$kick]);
    if ($kick === $uid) {
        $next = $db->prepare("SELECT user_id FROM clan_members WHERE clan_id=? AND role='officer' LIMIT 1"); $next->execute([$cid]); $next = $next->fetch();
        if ($next) {
            $db->prepare("UPDATE clan_members SET role='owner' WHERE clan_id=? AND user_id=?")->execute([$cid, $next['user_id']]);
            $db->prepare("UPDATE clans SET owner_id=? WHERE id=?")->execute([$next['user_id'], $cid]);
            $db->prepare("UPDATE users SET clan_id=? WHERE id=?")->execute([$cid, $next['user_id']]);
        } else {
            $db->prepare("DELETE FROM clans WHERE id=?")->execute([$cid]);
            $db->prepare("UPDATE users SET clan_id=NULL WHERE clan_id=?")->execute([$cid]);
        }
    }
    respond(['ok' => true]);
}

if ($action === 'delete' && $method === 'POST') {
    $staffUser = requireStaff();
    $cid = (int)(body()['clan_id'] ?? 0);
    if (!$cid) error('clan_id required');
    $db->prepare("UPDATE users SET clan_id=NULL WHERE clan_id=?")->execute([$cid]);
    $db->prepare("DELETE FROM clans WHERE id=?")->execute([$cid]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
