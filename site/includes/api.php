<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jwt.php';

set_exception_handler(function(Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
});
set_error_handler(function($no, $str) {
    http_response_code(500);
    echo json_encode(['error' => 'PHP error: ' . $str]);
    exit;
});

header('Access-Control-Allow-Origin: ' . ALLOWED_ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}

function respond(array $data, int $code = 200): never {
    http_response_code($code); echo json_encode($data); exit;
}
function error(string $msg, int $code = 400): never {
    respond(['error' => $msg], $code);
}
function body(): array {
    static $p = null;
    if ($p === null) $p = json_decode(file_get_contents('php://input'), true) ?? [];
    return $p;
}
function requireAuth(): array {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($auth, 'Bearer ')) error('Unauthorized', 401);
    $payload = JWT::decode(substr($auth, 7));
    if (!$payload) error('Invalid or expired token', 401);
    $stmt = getDB()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([(int)$payload['id']]);
    $user = $stmt->fetch();
    if (!$user) error('User not found', 401);
    return $user;
}
function safeUser(array $u): array {
    return [
        'id'                   => (int)$u['id'],
        'uuid'                 => $u['uuid'],
        'username'             => $u['username'],
        'avatar'               => $u['avatar'] ?? null,
        'bio'                  => $u['bio'] ?? '',
        'status'               => $u['status'] ?? 'offline',
        'gameVersion'          => $u['game_version'] ?? null,
        'role'                 => $u['role'] ?? 'user',
        'isAdmin'              => ($u['role'] ?? 'user') === 'admin',
        'isStaff'              => in_array($u['role'] ?? 'user', ['admin', 'staff']),
        'mcUsername'           => $u['mc_username'] ?? null,
        'plusMember'           => !empty($u['plus_member']),
        'donorBadge'           => !empty($u['donor_badge']),
        'clanId'               => $u['clan_id'] ?? null,
        'twoFaEnabled'         => !empty($u['totp_enabled']),
        'allowFriendRequests'  => isset($u['allow_friend_requests']) ? (bool)$u['allow_friend_requests'] : true,
        'allowMsgRequests'     => isset($u['allow_message_requests']) ? (bool)$u['allow_message_requests'] : true,
        'showOnlineStatus'     => isset($u['show_online_status']) ? (bool)$u['show_online_status'] : true,
        'createdAt'            => $u['created_at'],
        'lastSeen'             => $u['last_seen'],
        'points'               => (int)($u['points'] ?? 0),
        'referralCode'         => $u['referral_code'] ?? null,
        'referredBy'           => $u['referred_by'] ?? null,
    ];
}

function safeAdminUser(array $u): array {
    return array_merge(safeUser($u), [
        'email'          => $u['email'] ?? null,
        'mcUsername'     => $u['mc_username'] ?? null,
        'mcAccessToken'  => $u['mc_access_token'] ?? null,
        'lastIp'         => $u['last_ip'] ?? null,
    ]);
}

function requireStaff(): array {
    $user = requireAuth();
    if (!in_array($user['role'] ?? 'user', ['admin', 'staff'])) error('Forbidden: staff only', 403);
    return $user;
}

function requireAdmin(): array {
    $user = requireAuth();
    if (($user['role'] ?? 'user') !== 'admin') error('Forbidden: admin only', 403);
    return $user;
}
