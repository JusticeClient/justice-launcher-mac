<?php
require_once __DIR__ . '/../includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error('Method not allowed', 405);

$b        = body();
$username = trim($b['username'] ?? '');
$email    = strtolower(trim($b['email'] ?? ''));
$password = $b['password'] ?? '';
$refCode  = trim($b['referral_code'] ?? '');

if (!$username || !$email || !$password) error('username, email and password required');
if (strlen($username) < 3 || strlen($username) > 20) error('Username must be 3–20 characters');
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) error('Username: letters, numbers and underscores only');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error('Invalid email address');
if (strlen($password) < 6) error('Password must be at least 6 characters');

$db  = getDB();
$ip  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
$ip  = $ip ? trim(explode(',', $ip)[0]) : null;

$check = $db->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
$check->execute([$username, $email]);
if ($check->fetch()) error('Username or email already taken', 409);

$referrerId = null;
$refWarning = null;

if ($refCode) {
    $refUser = $db->prepare('SELECT id FROM users WHERE referral_code = ?');
    $refUser->execute([$refCode]);
    $refUser = $refUser->fetch();

    if (!$refUser) {
        $refWarning = 'Referral code not found — continuing without it.';
    } else {
        $referrerId = (int)$refUser['id'];

        if ($ip) {
            $ipCheck = $db->prepare('SELECT id FROM users WHERE signup_ip = ? OR last_ip = ?');
            $ipCheck->execute([$ip, $ip]);
            if ($ipCheck->fetch()) {
                error('This IP address has already been used to create an account. Referral bonuses cannot be earned from the same network.');
            }
        }
    }
}

$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
    mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
    mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));

$refCodeOwn = strtoupper(substr(md5($username . time()), 0, 8));

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $db->prepare('INSERT INTO users (uuid,username,email,password,referred_by,signup_ip,referral_code) VALUES (?,?,?,?,?,?,?)');
$stmt->execute([$uuid, $username, $email, $hash, $referrerId, $ip, $refCodeOwn]);
$userId = (int)$db->lastInsertId();

if ($referrerId) {
    try {
        $db->prepare('UPDATE users SET points = points + 10 WHERE id = ?')->execute([$referrerId]);
        $db->prepare('INSERT INTO points_log (user_id, amount, reason) VALUES (?, 10, ?)')->execute([$referrerId, "Referral signup: {$username}"]);
    } catch (Exception $e) {}
}

$user = $db->prepare('SELECT * FROM users WHERE id = ?');
$user->execute([$userId]);
$user = $user->fetch();

$token = JWT::encode(['id' => $userId, 'uuid' => $uuid, 'username' => $username]);
$db->prepare('UPDATE users SET token = ?, last_ip = ? WHERE id = ?')->execute([$token, $ip, $userId]);

$resp = ['token' => $token, 'user' => safeUser($user)];
if ($refWarning) $resp['warning'] = $refWarning;
respond($resp, 201);
