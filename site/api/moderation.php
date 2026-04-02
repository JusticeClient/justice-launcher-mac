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
$admin  = requireAdmin();

if ($action === 'log' && $method === 'GET') {
    $targetId = (int)($_GET['userId'] ?? 0);
    $stmt = $db->prepare("SELECT m.*, u.username AS admin_name FROM mod_actions m JOIN users u ON u.id=m.admin_id WHERE m.target_id=? ORDER BY m.created_at DESC");
    $stmt->execute([$targetId]);
    respond(['actions' => $stmt->fetchAll()]);
}

if ($action === 'warn' && $method === 'POST') {
    $b        = body();
    $targetId = (int)($b['userId'] ?? 0);
    $reason   = trim($b['reason'] ?? '');
    if (!$targetId) error('userId required');
    $db->prepare("INSERT INTO mod_actions (target_id,admin_id,action,reason) VALUES (?,?,'warn',?)")->execute([$targetId,$admin['id'],$reason]);
    $db->prepare("UPDATE users SET warn_count=warn_count+1 WHERE id=?")->execute([$targetId]);
    respond(['ok' => true]);
}

if ($action === 'ban' && $method === 'POST') {
    $b        = body();
    $targetId = (int)($b['userId'] ?? 0);
    $reason   = trim($b['reason'] ?? '');
    if (!$targetId) error('userId required');
    $db->prepare("INSERT INTO mod_actions (target_id,admin_id,action,reason) VALUES (?,?,'ban',?)")->execute([$targetId,$admin['id'],$reason]);
    $db->prepare("UPDATE users SET banned=1, ban_reason=? WHERE id=?")->execute([$reason,$targetId]);
    respond(['ok' => true]);
}

if ($action === 'unban' && $method === 'POST') {
    $targetId = (int)(body()['userId'] ?? 0);
    $db->prepare("INSERT INTO mod_actions (target_id,admin_id,action,reason) VALUES (?,?,'unban','Unbanned by admin')")->execute([$targetId,$admin['id']]);
    $db->prepare("UPDATE users SET banned=0, ban_reason=NULL WHERE id=?")->execute([$targetId]);
    respond(['ok' => true]);
}

if ($action === 'plus' && $method === 'POST') {
    if ($staff['role'] !== 'admin') error('Admin only', 403);
    $b        = body();
    $targetId = (int)($b['userId'] ?? 0);
    $grant    = (int)($b['grant'] ?? 1);
    $db->prepare("UPDATE users SET plus_member=?, plus_since=? WHERE id=?")->execute([$grant, $grant ? date('Y-m-d H:i:s') : null, $targetId]);
    respond(['ok' => true]);
}

if ($action === 'donor' && $method === 'POST') {
    if ($staff['role'] !== 'admin') error('Admin only', 403);
    $b        = body();
    $targetId = (int)($b['userId'] ?? 0);
    $db->prepare("UPDATE users SET donor_badge=? WHERE id=?")->execute([(int)($b['grant']??1), $targetId]);
    respond(['ok' => true]);
}

if ($action === 'set-role' && $method === 'POST') {
    $b        = body();
    $targetId = (int)($b['userId'] ?? 0);
    $newRole  = trim($b['role'] ?? 'user');
    if (!$targetId) error('userId required');
    $allowed = ['user', 'staff', 'media', 'admin'];
    if (!in_array($newRole, $allowed)) error('Invalid role. Allowed: ' . implode(', ', $allowed));
    // Prevent admins from demoting themselves
    if ($targetId === (int)$admin['id'] && $newRole !== 'admin') error('You cannot change your own role');
    $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $targetId]);
    $db->prepare("INSERT INTO mod_actions (target_id,admin_id,action,reason) VALUES (?,?,?,?)")->execute([
        $targetId, $admin['id'], 'role_change', 'Role changed to ' . $newRole
    ]);
    respond(['ok' => true, 'role' => $newRole]);
}

if ($action === 'leaderboard' && $method === 'GET') {
    $key   = preg_replace('/[^a-z_]/', '', $_GET['key'] ?? 'playtime');
    $limit = min((int)($_GET['limit'] ?? 10), 50);
    $stmt  = $db->prepare("SELECT ps.stat_value, u.id, u.username, u.mc_username, u.role, u.plus_member FROM player_stats ps JOIN users u ON u.id=ps.user_id WHERE ps.stat_key=? ORDER BY ps.stat_value DESC LIMIT ?");
    $stmt->execute([$key, $limit]);
    respond(['leaderboard' => $stmt->fetchAll(), 'key' => $key]);
}

if ($action === 'stat' && $method === 'POST') {
    if ($staff['role'] !== 'admin') error('Admin only', 403);
    $b      = body();
    $userId = (int)($b['userId'] ?? 0);
    $key    = preg_replace('/[^a-z_]/', '', $b['key'] ?? '');
    $val    = (int)($b['value'] ?? 0);
    if (!$userId || !$key) error('userId and key required');
    $db->prepare("INSERT INTO player_stats (user_id,stat_key,stat_value) VALUES (?,?,?) ON DUPLICATE KEY UPDATE stat_value=?")->execute([$userId,$key,$val,$val]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
