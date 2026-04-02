<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'balance';
$db     = getDB();
$user   = requireAuth();
$uid    = $user['id'];

function ensureReferralCode($db, $uid, $username) {
    $stmt = $db->prepare("SELECT referral_code FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $code = $stmt->fetchColumn();
    if ($code) return $code;
    $new = strtoupper(substr(md5($username . $uid . time()), 0, 8));
    try {
        $db->prepare("UPDATE users SET referral_code=? WHERE id=? AND referral_code IS NULL")
           ->execute([$new, $uid]);
    } catch (Exception $e) {}
    $stmt->execute([$uid]);
    return $db->prepare("SELECT referral_code FROM users WHERE id=?")->execute([$uid]) ? $new : $new;
}

if ($action === 'balance' && $method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT points, referral_code, username FROM users WHERE id=?");
        $stmt->execute([$uid]);
        $row  = $stmt->fetch();
        $code = $row['referral_code'] ?? null;
        if (!$code) {
            $code = strtoupper(substr(md5(($row['username']??'u') . $uid . time()), 0, 8));
            try {
                $db->prepare("UPDATE users SET referral_code=? WHERE id=? AND (referral_code IS NULL OR referral_code='')")
                   ->execute([$code, $uid]);
            } catch (Exception $e) {}
        }
        $pts  = (int)($row['points'] ?? 0);
        $host = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        respond([
            'points'        => $pts,
            'referral_code' => $code,
            'referral_url'  => $host . '/register?ref=' . $code,
            'donut_value'   => $pts . ' pts = ' . number_format($pts * 1000000) . ' DonutSMP coins',
        ]);
    } catch (Exception $e) { respond(['points' => 0, 'referral_code' => null, 'referral_url' => '', 'donut_value' => '0 pts']); }
}

if ($action === 'log' && $method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT * FROM points_log WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$uid]);
        respond(['log' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['log' => []]); }
}

if ($action === 'referrals' && $method === 'GET') {
    try {
        $stmt = $db->prepare("SELECT username, created_at FROM users WHERE referred_by=? ORDER BY created_at DESC");
        $stmt->execute([$uid]);
        respond(['referrals' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['referrals' => []]); }
}

error('Unknown action', 404);
