<?php

require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'catalog';
$db     = getDB();

function cosmeticRow(array $c): array {
    return [
        'id'          => (int)$c['id'],
        'name'        => $c['name'],
        'slug'        => $c['slug'],
        'type'        => $c['type'],
        'description' => $c['description'] ?? '',
        'texturePath' => $c['texture_path'],
        'previewPath' => $c['preview_path'] ?? null,
        'rarity'      => $c['rarity'],
        'plusOnly'     => !empty($c['plus_only']),
        'price'       => isset($c['price']) ? (int)$c['price'] : null,
        'active'      => !empty($c['active']),
        'createdAt'   => $c['created_at'],
    ];
}

function makeSlug(string $name): string {
    return preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($name)));
}

if ($action === 'catalog' && $method === 'GET') {
    $type = $_GET['type'] ?? null;
    $sql  = 'SELECT * FROM cosmetics WHERE active = 1';
    $params = [];
    if ($type) {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY rarity DESC, name ASC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    respond(['cosmetics' => array_map('cosmeticRow', $stmt->fetchAll())]);
}

if ($action === 'shop' && $method === 'GET') {
    $type = $_GET['type'] ?? null;
    $sql  = 'SELECT * FROM cosmetics WHERE active = 1 AND price IS NOT NULL';
    $params = [];
    if ($type) {
        $sql .= ' AND type = ?';
        $params[] = $type;
    }
    $sql .= ' ORDER BY price ASC, rarity DESC, name ASC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    respond(['cosmetics' => array_map('cosmeticRow', $stmt->fetchAll())]);
}

if ($action === 'mc-buy' && $method === 'POST') {
    $b = body();
    $mcName     = trim($b['username'] ?? '');
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$mcName || !$cosmeticId) error('username and cosmeticId required');

    $stmt = $db->prepare('SELECT id, points FROM users WHERE mc_username = ? LIMIT 1');
    $stmt->execute([$mcName]);
    $user = $stmt->fetch();
    if (!$user) error('User not found', 404);
    $uid = (int)$user['id'];
    $userPoints = (int)$user['points'];

    $stmt = $db->prepare('SELECT * FROM cosmetics WHERE id = ? AND active = 1');
    $stmt->execute([$cosmeticId]);
    $cos = $stmt->fetch();
    if (!$cos) error('Cosmetic not found or inactive', 404);
    if ($cos['price'] === null) error('This cosmetic is not for sale');
    $price = (int)$cos['price'];

    $stmt = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
    $stmt->execute([$uid, $cosmeticId]);
    if ($stmt->fetch()) error('You already own this cosmetic');

    if ($userPoints < $price) error('Not enough points (need ' . $price . ', have ' . $userPoints . ')');

    if ($cos['plus_only'] && empty($user['plus_member'])) {
        error('This cosmetic requires Plus membership');
    }

    $db->beginTransaction();
    try {
        $db->prepare('UPDATE users SET points = points - ? WHERE id = ? AND points >= ?')
           ->execute([$price, $uid, $price]);

        $db->prepare('INSERT IGNORE INTO user_cosmetics (user_id, cosmetic_id, granted_by) VALUES (?, ?, NULL)')
           ->execute([$uid, $cosmeticId]);

        $db->prepare('INSERT INTO cosmetic_purchases (user_id, cosmetic_id, price_paid) VALUES (?, ?, ?)')
           ->execute([$uid, $cosmeticId, $price]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error('Purchase failed: ' . $e->getMessage(), 500);
    }

    $stmt = $db->prepare('SELECT points FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $newPoints = (int)$stmt->fetchColumn();

    respond(['ok' => true, 'pointsRemaining' => $newPoints]);
}

if ($action === 'buy' && $method === 'POST') {
    $user = requireAuth();
    $b = body();
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$cosmeticId) error('cosmeticId required');

    $uid = (int)$user['id'];
    $userPoints = (int)$user['points'];

    $stmt = $db->prepare('SELECT * FROM cosmetics WHERE id = ? AND active = 1');
    $stmt->execute([$cosmeticId]);
    $cos = $stmt->fetch();
    if (!$cos) error('Cosmetic not found or inactive', 404);
    if ($cos['price'] === null) error('This cosmetic is not for sale');
    $price = (int)$cos['price'];

    $stmt = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
    $stmt->execute([$uid, $cosmeticId]);
    if ($stmt->fetch()) error('You already own this cosmetic');

    if ($userPoints < $price) error('Not enough points (need ' . $price . ', have ' . $userPoints . ')');

    if ($cos['plus_only'] && empty($user['plus_member'])) {
        error('This cosmetic requires Plus membership');
    }

    $db->beginTransaction();
    try {
        $db->prepare('UPDATE users SET points = points - ? WHERE id = ? AND points >= ?')
           ->execute([$price, $uid, $price]);

        $db->prepare('INSERT IGNORE INTO user_cosmetics (user_id, cosmetic_id, granted_by) VALUES (?, ?, NULL)')
           ->execute([$uid, $cosmeticId]);

        $db->prepare('INSERT INTO cosmetic_purchases (user_id, cosmetic_id, price_paid) VALUES (?, ?, ?)')
           ->execute([$uid, $cosmeticId, $price]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error('Purchase failed: ' . $e->getMessage(), 500);
    }

    $stmt = $db->prepare('SELECT points FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $newPoints = (int)$stmt->fetchColumn();

    respond(['ok' => true, 'pointsRemaining' => $newPoints]);
}

if ($action === 'mc-points' && $method === 'GET') {
    $mcName = trim($_GET['username'] ?? '');
    if (!$mcName) error('username required');

    $stmt = $db->prepare('SELECT points FROM users WHERE mc_username = ? LIMIT 1');
    $stmt->execute([$mcName]);
    $row = $stmt->fetch();
    if (!$row) respond(['points' => 0]);

    respond(['points' => (int)$row['points']]);
}

if ($action === 'by-username' && $method === 'GET') {
    $mcName = trim($_GET['username'] ?? '');
    if (!$mcName) error('username required');

    $stmt = $db->prepare('SELECT id FROM users WHERE mc_username = ? LIMIT 1');
    $stmt->execute([$mcName]);
    $row = $stmt->fetch();
    if (!$row) respond(['owned' => [], 'equipped' => []]);
    $uid = (int)$row['id'];

    $stmt = $db->prepare(
        'SELECT c.*
         FROM user_cosmetics uc
         JOIN cosmetics c ON c.id = uc.cosmetic_id
         WHERE uc.user_id = ?
         ORDER BY c.type, c.name'
    );
    $stmt->execute([$uid]);
    $owned = array_map('cosmeticRow', $stmt->fetchAll());

    $stmt = $db->prepare(
        'SELECT ue.slot, c.*
         FROM user_equipped ue
         JOIN cosmetics c ON c.id = ue.cosmetic_id
         WHERE ue.user_id = ?'
    );
    $stmt->execute([$uid]);
    $equipped = [];
    foreach ($stmt->fetchAll() as $r) {
        $equipped[$r['slot']] = cosmeticRow($r);
    }

    respond(['owned' => $owned, 'equipped' => $equipped]);
}

if ($action === 'mc-equip' && $method === 'POST') {
    $b = body();
    $mcName     = trim($b['username'] ?? '');
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$mcName || !$cosmeticId) error('username and cosmeticId required');

    $stmt = $db->prepare('SELECT id FROM users WHERE mc_username = ? LIMIT 1');
    $stmt->execute([$mcName]);
    $row = $stmt->fetch();
    if (!$row) error('User not found', 404);
    $uid = (int)$row['id'];

    $stmt = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
    $stmt->execute([$uid, $cosmeticId]);
    if (!$stmt->fetch()) error('User does not own this cosmetic', 403);

    $stmt = $db->prepare('SELECT type FROM cosmetics WHERE id = ? AND active = 1');
    $stmt->execute([$cosmeticId]);
    $cos = $stmt->fetch();
    if (!$cos) error('Cosmetic not found or inactive', 404);

    $db->prepare(
        'INSERT INTO user_equipped (user_id, slot, cosmetic_id) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE cosmetic_id = VALUES(cosmetic_id), equipped_at = NOW()'
    )->execute([$uid, $cos['type'], $cosmeticId]);

    respond(['ok' => true, 'slot' => $cos['type']]);
}

if ($action === 'mc-unequip' && $method === 'POST') {
    $b = body();
    $mcName = trim($b['username'] ?? '');
    $slot   = $b['slot'] ?? '';
    if (!$mcName) error('username required');
    $validSlots = ['cape','hat','wings','bandana','aura','emoji'];
    if (!in_array($slot, $validSlots, true)) error('Invalid slot');

    $stmt = $db->prepare('SELECT id FROM users WHERE mc_username = ? LIMIT 1');
    $stmt->execute([$mcName]);
    $row = $stmt->fetch();
    if (!$row) error('User not found', 404);

    $db->prepare('DELETE FROM user_equipped WHERE user_id = ? AND slot = ?')
       ->execute([(int)$row['id'], $slot]);

    respond(['ok' => true]);
}

if ($action === 'mc-players' && $method === 'GET') {
    $raw = $_GET['names'] ?? '';
    $names = array_filter(array_map('trim', explode(',', $raw)));
    if (empty($names) || count($names) > 50) {
        respond(['players' => []]);
    }

    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $stmt = $db->prepare(
        "SELECT u.mc_username, ue.slot, c.type, c.texture_path, c.slug, c.name, c.rarity
         FROM users u
         JOIN user_equipped ue ON ue.user_id = u.id
         JOIN cosmetics c ON c.id = ue.cosmetic_id AND c.active = 1
         WHERE u.mc_username IN ($placeholders)"
    );
    $stmt->execute($names);

    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $name = $row['mc_username'];
        if (!isset($result[$name])) $result[$name] = [];
        $result[$name][$row['slot']] = [
            'slug'        => $row['slug'],
            'name'        => $row['name'],
            'type'        => $row['type'],
            'texturePath' => $row['texture_path'],
            'rarity'      => $row['rarity'],
        ];
    }

    respond(['players' => $result]);
}

if ($action === 'mine' && $method === 'GET') {
    $user = requireAuth();
    $uid  = $user['id'];

    $stmt = $db->prepare(
        'SELECT c.*, uc.granted_at, uc.granted_by
         FROM user_cosmetics uc
         JOIN cosmetics c ON c.id = uc.cosmetic_id
         WHERE uc.user_id = ?
         ORDER BY c.type, c.name'
    );
    $stmt->execute([$uid]);
    $owned = [];
    foreach ($stmt->fetchAll() as $row) {
        $item = cosmeticRow($row);
        $item['grantedAt'] = $row['granted_at'];
        $owned[] = $item;
    }

    $stmt = $db->prepare(
        'SELECT ue.slot, c.*
         FROM user_equipped ue
         JOIN cosmetics c ON c.id = ue.cosmetic_id
         WHERE ue.user_id = ?'
    );
    $stmt->execute([$uid]);
    $equipped = [];
    foreach ($stmt->fetchAll() as $row) {
        $equipped[$row['slot']] = cosmeticRow($row);
    }

    respond(['owned' => $owned, 'equipped' => $equipped]);
}

if ($action === 'equip' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$cosmeticId) error('cosmeticId required');

    $stmt = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
    $stmt->execute([$user['id'], $cosmeticId]);
    if (!$stmt->fetch()) error('You do not own this cosmetic', 403);

    $stmt = $db->prepare('SELECT type, plus_only FROM cosmetics WHERE id = ? AND active = 1');
    $stmt->execute([$cosmeticId]);
    $cos = $stmt->fetch();
    if (!$cos) error('Cosmetic not found or inactive', 404);

    if ($cos['plus_only'] && !$user['plus_member']) {
        error('This cosmetic requires Plus membership', 403);
    }

    $db->prepare(
        'INSERT INTO user_equipped (user_id, slot, cosmetic_id) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE cosmetic_id = VALUES(cosmetic_id), equipped_at = NOW()'
    )->execute([$user['id'], $cos['type'], $cosmeticId]);

    respond(['ok' => true, 'slot' => $cos['type']]);
}

if ($action === 'unequip' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $slot = $b['slot'] ?? '';
    $validSlots = ['cape','hat','wings','bandana','aura','emoji'];
    if (!in_array($slot, $validSlots, true)) error('Invalid slot');

    $db->prepare('DELETE FROM user_equipped WHERE user_id = ? AND slot = ?')
       ->execute([$user['id'], $slot]);

    respond(['ok' => true]);
}

if ($action === 'players' && $method === 'GET') {
    requireAuth();
    $raw = $_GET['uuids'] ?? '';
    $uuids = array_filter(array_map('trim', explode(',', $raw)));
    if (empty($uuids) || count($uuids) > 50) {
        respond(['players' => []]);
    }

    $placeholders = implode(',', array_fill(0, count($uuids), '?'));
    $stmt = $db->prepare(
        "SELECT u.uuid, ue.slot, c.type, c.texture_path, c.slug, c.name, c.rarity
         FROM users u
         JOIN user_equipped ue ON ue.user_id = u.id
         JOIN cosmetics c ON c.id = ue.cosmetic_id AND c.active = 1
         WHERE u.uuid IN ($placeholders)"
    );
    $stmt->execute($uuids);

    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $uuid = $row['uuid'];
        if (!isset($result[$uuid])) $result[$uuid] = [];
        $result[$uuid][$row['slot']] = [
            'slug'        => $row['slug'],
            'name'        => $row['name'],
            'type'        => $row['type'],
            'texturePath' => $row['texture_path'],
            'rarity'      => $row['rarity'],
        ];
    }

    respond(['players' => $result]);
}

if ($action === 'user' && $method === 'GET') {
    requireAdmin();
    $userId = (int)($_GET['userId'] ?? 0);
    if (!$userId) error('userId required');

    $stmt = $db->prepare(
        'SELECT c.*, uc.granted_at, uc.granted_by, adm.username AS granted_by_name
         FROM user_cosmetics uc
         JOIN cosmetics c ON c.id = uc.cosmetic_id
         LEFT JOIN users adm ON adm.id = uc.granted_by
         WHERE uc.user_id = ?
         ORDER BY uc.granted_at DESC'
    );
    $stmt->execute([$userId]);

    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $item = cosmeticRow($row);
        $item['grantedAt']     = $row['granted_at'];
        $item['grantedBy']     = $row['granted_by'] ? (int)$row['granted_by'] : null;
        $item['grantedByName'] = $row['granted_by_name'] ?? null;
        $items[] = $item;
    }

    $stmt = $db->prepare('SELECT slot, cosmetic_id FROM user_equipped WHERE user_id = ?');
    $stmt->execute([$userId]);
    $equipped = [];
    foreach ($stmt->fetchAll() as $r) $equipped[$r['slot']] = (int)$r['cosmetic_id'];

    respond(['cosmetics' => $items, 'equipped' => $equipped]);
}

if ($action === 'grant' && $method === 'POST') {
    $admin = requireAdmin();
    $b     = body();
    $userId    = (int)($b['userId'] ?? 0);
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$userId || !$cosmeticId) error('userId and cosmeticId required');

    $stmt = $db->prepare('SELECT id FROM cosmetics WHERE id = ?');
    $stmt->execute([$cosmeticId]);
    if (!$stmt->fetch()) error('Cosmetic not found', 404);

    $stmt = $db->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) error('User not found', 404);

    $db->prepare(
        'INSERT IGNORE INTO user_cosmetics (user_id, cosmetic_id, granted_by) VALUES (?, ?, ?)'
    )->execute([$userId, $cosmeticId, $admin['id']]);

    respond(['ok' => true]);
}

if ($action === 'revoke' && $method === 'POST') {
    requireAdmin();
    $b = body();
    $userId     = (int)($b['userId'] ?? 0);
    $cosmeticId = (int)($b['cosmeticId'] ?? 0);
    if (!$userId || !$cosmeticId) error('userId and cosmeticId required');

    $db->prepare(
        'DELETE FROM user_equipped WHERE user_id = ? AND cosmetic_id = ?'
    )->execute([$userId, $cosmeticId]);

    $db->prepare(
        'DELETE FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?'
    )->execute([$userId, $cosmeticId]);

    respond(['ok' => true]);
}

if ($action === 'create' && $method === 'POST') {
    requireAdmin();

    $name        = trim($_POST['name'] ?? '');
    $type        = $_POST['type'] ?? 'cape';
    $description = trim($_POST['description'] ?? '');
    $rarity      = $_POST['rarity'] ?? 'common';
    $plusOnly     = (int)($_POST['plusOnly'] ?? 0);
    $price        = isset($_POST['price']) && $_POST['price'] !== '' ? (int)$_POST['price'] : null;

    if (!$name) error('Name is required');
    $validTypes    = ['cape','hat','wings','bandana','aura','emoji'];
    $validRarities = ['common','uncommon','rare','epic','legendary'];
    if (!in_array($type, $validTypes, true)) error('Invalid type');
    if (!in_array($rarity, $validRarities, true)) error('Invalid rarity');

    $slug = makeSlug($name);

    $stmt = $db->prepare('SELECT id FROM cosmetics WHERE slug = ?');
    $stmt->execute([$slug]);
    if ($stmt->fetch()) error('A cosmetic with a similar name already exists');

    if (empty($_FILES['texture']) || $_FILES['texture']['error'] !== UPLOAD_ERR_OK) {
        error('Texture file is required (PNG)');
    }
    $file = $_FILES['texture'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['png', 'jpg', 'jpeg'], true)) error('Texture must be PNG or JPG');

    $uploadDir = __DIR__ . '/../uploads/cosmetics';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = $slug . '-' . time() . '.' . $ext;
    $dest     = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        error('Failed to save texture file');
    }

    $texturePath = 'uploads/cosmetics/' . $filename;

    $previewPath = null;
    if (!empty($_FILES['preview']) && $_FILES['preview']['error'] === UPLOAD_ERR_OK) {
        $pFile = $_FILES['preview'];
        $pExt  = strtolower(pathinfo($pFile['name'], PATHINFO_EXTENSION));
        if (in_array($pExt, ['png', 'jpg', 'jpeg'], true)) {
            $pFilename = $slug . '-preview-' . time() . '.' . $pExt;
            $pDest     = $uploadDir . '/' . $pFilename;
            if (move_uploaded_file($pFile['tmp_name'], $pDest)) {
                $previewPath = 'uploads/cosmetics/' . $pFilename;
            }
        }
    }

    $db->prepare(
        'INSERT INTO cosmetics (name, slug, type, description, texture_path, preview_path, rarity, plus_only, price)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    )->execute([$name, $slug, $type, $description, $texturePath, $previewPath, $rarity, $plusOnly, $price]);

    $newId = (int)$db->lastInsertId();

    $stmt = $db->prepare('SELECT * FROM cosmetics WHERE id = ?');
    $stmt->execute([$newId]);
    respond(['cosmetic' => cosmeticRow($stmt->fetch())], 201);
}

if ($action === 'update' && $method === 'POST') {
    requireAdmin();
    $b  = body();
    $id = (int)($b['id'] ?? 0);
    if (!$id) error('id required');

    $fields = [];
    $vals   = [];
    $allowed = ['name','description','rarity','plus_only','active'];
    foreach ($allowed as $col) {
        $camel = lcfirst(str_replace('_', '', ucwords($col, '_')));
        if (isset($b[$camel])) {
            $fields[] = "$col = ?";
            $vals[]   = is_bool($b[$camel]) ? ($b[$camel] ? 1 : 0) : $b[$camel];
        } elseif (isset($b[$col])) {
            $fields[] = "$col = ?";
            $vals[]   = is_bool($b[$col]) ? ($b[$col] ? 1 : 0) : $b[$col];
        }
    }
    if (array_key_exists('price', $b)) {
        $fields[] = "price = ?";
        $vals[]   = ($b['price'] === null || $b['price'] === '') ? null : (int)$b['price'];
    }
    if (empty($fields)) error('No valid fields to update');
    $vals[] = $id;
    $db->prepare('UPDATE cosmetics SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($vals);

    $stmt = $db->prepare('SELECT * FROM cosmetics WHERE id = ?');
    $stmt->execute([$id]);
    respond(['cosmetic' => cosmeticRow($stmt->fetch())]);
}

if ($action === 'upload-preview' && $method === 'POST') {
    requireAdmin();
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) error('id required');

    if (empty($_FILES['preview']) || $_FILES['preview']['error'] !== UPLOAD_ERR_OK) {
        error('Preview image file is required');
    }

    $pFile = $_FILES['preview'];
    $pExt  = strtolower(pathinfo($pFile['name'], PATHINFO_EXTENSION));
    if (!in_array($pExt, ['png', 'jpg', 'jpeg'], true)) error('Preview must be PNG or JPG');

    $stmt = $db->prepare('SELECT slug, preview_path FROM cosmetics WHERE id = ?');
    $stmt->execute([$id]);
    $cos = $stmt->fetch();
    if (!$cos) error('Cosmetic not found', 404);

    if ($cos['preview_path']) {
        $oldFile = __DIR__ . '/../' . $cos['preview_path'];
        if (file_exists($oldFile)) @unlink($oldFile);
    }

    $uploadDir = __DIR__ . '/../uploads/cosmetics';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $pFilename = $cos['slug'] . '-preview-' . time() . '.' . $pExt;
    $pDest     = $uploadDir . '/' . $pFilename;
    if (!move_uploaded_file($pFile['tmp_name'], $pDest)) {
        error('Failed to save preview file');
    }

    $previewPath = 'uploads/cosmetics/' . $pFilename;
    $db->prepare('UPDATE cosmetics SET preview_path = ? WHERE id = ?')->execute([$previewPath, $id]);

    $stmt = $db->prepare('SELECT * FROM cosmetics WHERE id = ?');
    $stmt->execute([$id]);
    respond(['cosmetic' => cosmeticRow($stmt->fetch())]);
}

if ($action === 'delete' && $method === 'POST') {
    requireAdmin();
    $id = (int)(body()['id'] ?? 0);
    if (!$id) error('id required');

    $stmt = $db->prepare('SELECT texture_path FROM cosmetics WHERE id = ?');
    $stmt->execute([$id]);
    $cos = $stmt->fetch();
    if ($cos) {
        $fullPath = __DIR__ . '/../' . $cos['texture_path'];
        if (file_exists($fullPath)) @unlink($fullPath);
    }

    $db->prepare('DELETE FROM cosmetics WHERE id = ?')->execute([$id]);
    respond(['ok' => true]);
}

if ($action === 'admin-list' && $method === 'GET') {
    requireAdmin();
    $stmt = $db->query(
        'SELECT c.*,
                COUNT(DISTINCT uc.user_id) AS owner_count,
                COUNT(DISTINCT ue.user_id) AS equipped_count
         FROM cosmetics c
         LEFT JOIN user_cosmetics uc ON uc.cosmetic_id = c.id
         LEFT JOIN user_equipped ue ON ue.cosmetic_id = c.id
         GROUP BY c.id
         ORDER BY c.type, c.name'
    );
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $item = cosmeticRow($row);
        $item['ownerCount']    = (int)$row['owner_count'];
        $item['equippedCount'] = (int)$row['equipped_count'];
        $items[] = $item;
    }
    respond(['cosmetics' => $items]);
}

error('Unknown action', 404);
