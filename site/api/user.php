<?php
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'me';
$db     = getDB();

// GET me
if ($action === 'me' && $method === 'GET') {
    respond(['user' => safeUser(requireAuth())]);
}

// PATCH status
if ($action === 'status' && $method === 'PATCH') {
    $user = requireAuth();
    $b    = body();
    $valid = ['online','in-game','offline','away'];
    $status = $b['status'] ?? 'online';
    if (!in_array($status, $valid, true)) error('Invalid status');
    $db->prepare('UPDATE users SET status=?, game_version=?, last_seen=NOW() WHERE id=?')
       ->execute([$status, $b['gameVersion'] ?? null, $user['id']]);
    respond(['ok' => true]);
}

// PATCH settings  — privacy toggles
if ($action === 'settings' && $method === 'PATCH') {
    $user = requireAuth();
    $b    = body();
    $fields = [];
    $vals   = [];
    foreach (['allow_friend_requests','allow_message_requests','show_online_status'] as $col) {
        $key = lcfirst(str_replace('_', '', ucwords($col, '_')));
        // accept camelCase keys from frontend
        $map = ['allowfriendrequests'=>'allow_friend_requests','allowmsgrequests'=>'allow_message_requests','allowmessagerequests'=>'allow_message_requests','showonlinestatus'=>'show_online_status'];
        foreach ($b as $bk => $bv) {
            if (strtolower($bk) === strtolower(str_replace('_','',$col))) {
                $fields[] = "$col = ?";
                $vals[]   = $bv ? 1 : 0;
            }
        }
    }
    if (empty($fields)) error('No valid settings provided');
    $vals[] = $user['id'];
    $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id=?')->execute($vals);
    $fresh = $db->prepare('SELECT * FROM users WHERE id=?');
    $fresh->execute([$user['id']]);
    respond(['user' => safeUser($fresh->fetch())]);
}

// GET search
if ($action === 'search' && $method === 'GET') {
    $user = requireAuth();
    $q    = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) respond(['users' => []]);
    $stmt = $db->prepare('SELECT * FROM users WHERE username LIKE ? AND id != ? LIMIT 10');
    $stmt->execute(['%'.$q.'%', $user['id']]);
    respond(['users' => array_map('safeUser', $stmt->fetchAll())]);
}

// GET profile
if ($action === 'profile' && $method === 'GET') {
    requireAuth();
    $stmt = $db->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$_GET['username'] ?? '']);
    $u = $stmt->fetch();
    if (!$u) error('User not found', 404);
    respond(['user' => safeUser($u)]);
}

// GET version
if ($action === 'version' && $method === 'GET') {
    respond([
        'version'   => LAUNCHER_VERSION,
        'downloads' => ['windows'=>DOWNLOAD_WINDOWS,'mac'=>DOWNLOAD_MAC,'linux'=>DOWNLOAD_LINUX],
        'supported_mc' => ['1.21.1','1.21.2','1.21.3','1.21.4','1.21.5','1.21.6','1.21.7','1.21.8','1.21.9','1.21.10','1.21.11'],
    ]);
}

// PATCH mcdata — saves MC username, access token, and game version from the launcher
if ($action === 'mcdata' && $method === 'PATCH') {
    $user = requireAuth();
    $b    = body();
    $fields = [];
    $vals   = [];
    if (isset($b['mcUsername'])) { $fields[] = 'mc_username = ?';      $vals[] = substr((string)$b['mcUsername'], 0, 30); }
    if (isset($b['mcAccessToken'])) { $fields[] = 'mc_access_token = ?'; $vals[] = substr((string)$b['mcAccessToken'], 0, 2000); }
    if (isset($b['gameVersion'])) { $fields[] = 'game_version = ?';     $vals[] = substr((string)$b['gameVersion'], 0, 100); }
    if (empty($fields)) respond(['ok' => true]); // nothing to update
    $vals[] = $user['id'];
    $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($vals);
    respond(['ok' => true]);
}

error('Unknown action', 404);
