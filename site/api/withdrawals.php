<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db     = getDB();
$user   = requireAuth();
$uid    = $user['id'];

if ($action === 'list' && $method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT * FROM point_withdrawals WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$uid]);
        respond(['withdrawals' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['withdrawals' => []]); }
}

if ($action === 'request' && $method === 'POST') {
    $b      = body();
    $points = (int)($b['points'] ?? 0);
    $mc     = trim($b['mc_username'] ?? '');

    if ($points < 1)  error('Must withdraw at least 1 point');
    if (!$mc)         error('Minecraft username required');
    if (strlen($mc) > 50) error('Minecraft username too long');

    try {
        $bal = $db->prepare("SELECT points FROM users WHERE id=?");
        $bal->execute([$uid]); $bal = (int)$bal->fetchColumn();
        if ($points > $bal) error("Not enough points (you have {$bal})");

        $pending = $db->prepare("SELECT COUNT(*) FROM point_withdrawals WHERE user_id=? AND status='pending'");
        $pending->execute([$uid]);
        if ((int)$pending->fetchColumn() >= 3) error('You already have 3 pending withdrawals. Wait for them to be processed.');

        $db->prepare("UPDATE users SET points = points - ? WHERE id=?")->execute([$points, $uid]);
        $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?, ?, ?)")->execute([$uid, -$points, "Withdrawal request: {$points} pts to {$mc}"]);
        $db->prepare("INSERT INTO point_withdrawals (user_id, points, mc_username) VALUES (?,?,?)")->execute([$uid, $points, $mc]);
        respond(['ok' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Not enough') !== false || strpos($e->getMessage(), 'pending') !== false) throw $e;
        error('Something went wrong. Please try again.');
    }
}

if ($action === 'cancel' && $method === 'POST') {
    $id = (int)(body()['id'] ?? 0);
    try {
        $row = $db->prepare("SELECT * FROM point_withdrawals WHERE id=? AND user_id=? AND status='pending'");
        $row->execute([$id, $uid]); $row = $row->fetch();
        if (!$row) error('Withdrawal not found or not cancellable', 404);

        $db->prepare("UPDATE point_withdrawals SET status='cancelled', handled_at=NOW() WHERE id=?")->execute([$id]);
        $db->prepare("UPDATE users SET points = points + ? WHERE id=?")->execute([$row['points'], $uid]);
        $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?, ?, ?)")->execute([$uid, $row['points'], "Withdrawal cancelled (refunded)"]);
        respond(['ok' => true]);
    } catch (Exception $e) { error($e->getMessage()); }
}

error('Unknown action', 404);
