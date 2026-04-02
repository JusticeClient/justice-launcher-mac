<?php
require_once __DIR__ . '/../includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$b        = body();
$login    = trim($b['login'] ?? '');
$password = $b['password'] ?? '';
$totpCode = trim($b['totp_code'] ?? '');

if (!$login || !$password) error('login and password required');

$db   = getDB();
$stmt = $db->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
$stmt->execute([$login, strtolower($login)]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) error('Invalid username or password', 401);

if (!empty($user['banned'])) {
    $reason = $user['ban_reason'] ? ' Reason: ' . $user['ban_reason'] : '';
    error('Your account has been banned.' . $reason, 403);
}

if (!empty($user['totp_enabled']) && $user['totp_secret']) {
    if (!$totpCode) respond(['requires_2fa' => true]);
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32  = strtoupper(preg_replace('/[^A-Z2-7]/', '', $user['totp_secret']));
    $bits = ''; $key = '';
    for ($i = 0; $i < strlen($b32); $i++) { $val = strpos($alphabet, $b32[$i]); $bits .= sprintf('%05b', $val); }
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) $key .= chr(bindec(substr($bits, $i, 8)));
    $valid = false;
    for ($off = -1; $off <= 1; $off++) {
        $t    = pack('N*', 0) . pack('N*', intdiv(time(), 30) + $off);
        $hash = hash_hmac('sha1', $t, $key, true);
        $o    = ord($hash[19]) & 0xf;
        $c    = (((ord($hash[$o]) & 0x7f) << 24)|((ord($hash[$o+1]) & 0xff) << 16)|((ord($hash[$o+2]) & 0xff) << 8)|(ord($hash[$o+3]) & 0xff)) % 1000000;
        if (str_pad($c, 6, '0', STR_PAD_LEFT) === preg_replace('/\D/', '', $totpCode)) { $valid = true; break; }
    }
    if (!$valid) error('Invalid authenticator code', 401);
}

$token = JWT::encode(['id' => (int)$user['id'], 'uuid' => $user['uuid'], 'username' => $user['username']]);
$ip    = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
$ip    = $ip ? trim(explode(',', $ip)[0]) : null;
$db->prepare("UPDATE users SET token=?, status='online', last_seen=NOW(), last_ip=? WHERE id=?")->execute([$token, $ip, $user['id']]);
respond(['token' => $token, 'user' => safeUser($user)]);
