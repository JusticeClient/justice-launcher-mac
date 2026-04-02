<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'users';
$db     = getDB();

$admin = requireAdmin();

if ($action === 'users' && $method === 'GET') {
    $search = trim($_GET['q'] ?? '');
    $limit  = min((int)($_GET['limit'] ?? 50), 200);
    $offset = max((int)($_GET['offset'] ?? 0), 0);

    if ($search !== '') {
        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE username LIKE ? OR email LIKE ? OR mc_username LIKE ? OR last_ip LIKE ?
            ORDER BY created_at DESC LIMIT ? OFFSET ?
        ");
        $like = '%' . $search . '%';
        $stmt->execute([$like, $like, $like, $like, $limit, $offset]);
    } else {
        $stmt = $db->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM users" .
        ($search !== '' ? " WHERE username LIKE ? OR email LIKE ? OR mc_username LIKE ? OR last_ip LIKE ?" : ""));
    if ($search !== '') {
        $like = '%' . $search . '%';
        $countStmt->execute([$like, $like, $like, $like]);
    } else {
        $countStmt->execute();
    }

    respond([
        'users' => array_map('safeAdminUser', $stmt->fetchAll()),
        'total' => (int)$countStmt->fetchColumn(),
    ]);
}

if ($action === 'user' && $method === 'GET') {
    $id   = (int)($_GET['id'] ?? 0);
    if (!$id) error('id required');
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) error('User not found', 404);
    respond(['user' => safeAdminUser($user)]);
}

if ($action === 'stats' && $method === 'GET') {
    $total   = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $online  = (int)$db->query("SELECT COUNT(*) FROM users WHERE status != 'offline'")->fetchColumn();
    $ingame  = (int)$db->query("SELECT COUNT(*) FROM users WHERE status = 'in-game'")->fetchColumn();
    $today   = (int)$db->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $msgs    = (int)$db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
    $friends = (int)$db->query("SELECT COUNT(*) FROM friends WHERE status='accepted'")->fetchColumn();
    respond(['total' => $total, 'online' => $online, 'ingame' => $ingame, 'today' => $today, 'messages' => $msgs, 'friends' => $friends]);
}

if ($action === 'broadcast' && $method === 'POST') {
    $b       = body();
    $message = trim($b['message'] ?? '');
    $type    = in_array($b['type'] ?? '', ['info','warning','error','success']) ? $b['type'] : 'info';
    $ttl     = min((int)($b['ttl'] ?? 30), 300);
    if (!$message) error('Message required');
    if (strlen($message) > 200) error('Message too long (max 200 chars)');
    $expires = date('Y-m-d H:i:s', time() + $ttl);
    try {
        $db->prepare('INSERT INTO admin_broadcasts (message, type, created_by, expires_at) VALUES (?,?,?,?)')
           ->execute([$message, $type, $admin['id'], $expires]);
        respond(['ok' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) {
        error('Broadcasts table missing — run schema_fix.sql first');
    }
}

if ($action === 'broadcast' && $method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) error('id required');
    try {
        $db->prepare('DELETE FROM admin_broadcasts WHERE id = ?')->execute([$id]);
    } catch (Exception $e) {}
    respond(['ok' => true]);
}

if ($action === 'broadcasts' && $method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT b.*, u.username AS sent_by FROM admin_broadcasts b JOIN users u ON u.id=b.created_by WHERE b.expires_at > NOW() OR b.expires_at IS NULL ORDER BY b.created_at DESC LIMIT 20");
        $stmt->execute();
        respond(['broadcasts' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        respond(['broadcasts' => [], 'notice' => '']);
    }
}

if ($action === 'withdrawals' && $method === 'GET') {
    $status = $_GET['status'] ?? 'pending';
    $allowed = ['pending','completed','cancelled','all'];
    if (!in_array($status, $allowed)) $status = 'pending';
    try {
        $where = $status === 'all' ? '' : "WHERE w.status='{$status}'";
        $stmt  = $db->query("SELECT w.*, u.username, u.mc_username AS user_mc FROM point_withdrawals w JOIN users u ON u.id=w.user_id {$where} ORDER BY w.created_at DESC LIMIT 100");
        respond(['withdrawals' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['withdrawals' => [], 'error' => 'Something went wrong.']); }
}

if ($action === 'withdrawal-action' && $method === 'POST') {
    $b      = body();
    $id     = (int)($b['id'] ?? 0);
    $act    = $b['action'] ?? '';
    $note   = trim($b['note'] ?? '');
    if (!in_array($act, ['complete','cancel'])) error('Invalid action');
    $status = $act === 'complete' ? 'completed' : 'cancelled';
    try {
        $row = $db->prepare("SELECT * FROM point_withdrawals WHERE id=?");
        $row->execute([$id]); $row = $row->fetch();
        if (!$row) error('Withdrawal not found', 404);

        $db->prepare("UPDATE point_withdrawals SET status=?, admin_note=?, handled_by=?, handled_at=NOW() WHERE id=?")
           ->execute([$status, $note, $admin['id'], $id]);

        if ($act === 'cancel' && $row['status'] === 'pending') {
            $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([$row['points'], $row['user_id']]);
            $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?,?,?)")->execute([$row['user_id'], $row['points'], "Withdrawal cancelled by admin: refunded"]);
        }
        respond(['ok' => true]);
    } catch (Exception $e) { error($e->getMessage()); }
}

error('Unknown action', 404);

