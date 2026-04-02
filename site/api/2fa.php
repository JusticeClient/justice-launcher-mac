<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();
$user   = requireAuth();
$uid    = $user['id'];

function base32Decode(string $b32): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32      = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
    $bits = ''; $out = '';
    for ($i = 0; $i < strlen($b32); $i++) {
        $val  = strpos($alphabet, $b32[$i]);
        $bits .= sprintf('%05b', $val);
    }
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) $out .= chr(bindec(substr($bits, $i, 8)));
    return $out;
}

function totpGenerate(string $secret, int $offset = 0): string {
    $key      = base32Decode($secret);
    $time     = intdiv(time(), 30) + $offset;
    $msg      = pack('N*', 0) . pack('N*', $time);
    $hash     = hash_hmac('sha1', $msg, $key, true);
    $offset2  = ord($hash[19]) & 0xf;
    $code     = (((ord($hash[$offset2]) & 0x7f) << 24)
               | ((ord($hash[$offset2+1]) & 0xff) << 16)
               | ((ord($hash[$offset2+2]) & 0xff) << 8)
               |  (ord($hash[$offset2+3]) & 0xff)) % 1000000;
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

function totpVerify(string $secret, string $code): bool {
    $code = preg_replace('/\D/', '', $code);
    for ($i = -1; $i <= 1; $i++) { if (totpGenerate($secret, $i) === $code) return true; }
    return false;
}

function generateSecret(): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret   = '';
    for ($i = 0; $i < 16; $i++) $secret .= $alphabet[random_int(0, 31)];
    return $secret;
}

if ($action === 'setup' && $method === 'GET') {
    $secret  = generateSecret();
    $issuer  = 'Justice Launcher';
    $account = urlencode($user['username'] . '@justiceclient.org');
    $otpUrl  = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    $qrUrl   = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpUrl);
    $_SESSION['pending_totp'] = $secret;
    respond(['secret' => $secret, 'qr_url' => $qrUrl, 'otp_url' => $otpUrl]);
}

if ($action === 'confirm' && $method === 'POST') {
    $code   = trim(body()['code'] ?? '');
    $secret = $_SESSION['pending_totp'] ?? '';
    if (!$secret) error('No pending setup. Call /setup first.');
    if (!totpVerify($secret, $code)) error('Invalid code. Try again.');
    $db->prepare("UPDATE users SET totp_secret=?, totp_enabled=1 WHERE id=?")->execute([$secret, $uid]);
    unset($_SESSION['pending_totp']);
    respond(['ok' => true]);
}

if ($action === 'disable' && $method === 'POST') {
    $code = trim(body()['code'] ?? '');
    $stmt = $db->prepare("SELECT totp_secret FROM users WHERE id=?"); $stmt->execute([$uid]);
    $row  = $stmt->fetch();
    if (!$row || !$row['totp_secret']) error('2FA is not enabled');
    if (!totpVerify($row['totp_secret'], $code)) error('Invalid code');
    $db->prepare("UPDATE users SET totp_secret=NULL, totp_enabled=0 WHERE id=?")->execute([$uid]);
    respond(['ok' => true]);
}

if ($action === 'status' && $method === 'GET') {
    $stmt = $db->prepare("SELECT totp_enabled FROM users WHERE id=?"); $stmt->execute([$uid]);
    respond(['enabled' => (bool)$stmt->fetchColumn()]);
}

error('Unknown action', 404);
