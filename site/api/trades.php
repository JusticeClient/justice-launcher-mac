<?php

require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

$db->exec("
    CREATE TABLE IF NOT EXISTS trades (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        from_user_id INT NOT NULL,
        to_user_id   INT NOT NULL,
        offer_items  JSON NOT NULL,
        want_items   JSON NOT NULL,
        status       ENUM('pending','accepted','declined','cancelled') DEFAULT 'pending',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_from (from_user_id),
        INDEX idx_to   (to_user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

function tradeCosmeticRow(array $c): array {
    return [
        'id'          => (int)$c['id'],
        'name'        => $c['name'],
        'type'        => $c['type'],
        'rarity'      => $c['rarity'],
        'texturePath' => $c['texture_path'] ?? null,
        'previewPath' => $c['preview_path'] ?? null,
    ];
}

function enrichTrade(array $t, PDO $db): array {
    $stmt = $db->prepare('SELECT id, username FROM users WHERE id IN (?, ?)');
    $stmt->execute([$t['from_user_id'], $t['to_user_id']]);
    $users = [];
    foreach ($stmt->fetchAll() as $u) $users[(int)$u['id']] = $u['username'];

    return [
        'id'            => (int)$t['id'],
        'from_user_id'  => (int)$t['from_user_id'],
        'to_user_id'    => (int)$t['to_user_id'],
        'from_username' => $users[(int)$t['from_user_id']] ?? 'Unknown',
        'to_username'   => $users[(int)$t['to_user_id']] ?? 'Unknown',
        'offer_items'   => json_decode($t['offer_items'], true) ?: [],
        'want_items'    => json_decode($t['want_items'], true) ?: [],
        'status'        => $t['status'],
        'created_at'    => $t['created_at'],
        'updated_at'    => $t['updated_at'],
    ];
}

if ($action === 'list' && $method === 'GET') {
    $user = requireAuth();
    $uid  = $user['id'];

    $stmt = $db->prepare('
        SELECT * FROM trades
        WHERE from_user_id = ? OR to_user_id = ?
        ORDER BY created_at DESC
        LIMIT 100
    ');
    $stmt->execute([$uid, $uid]);

    $trades = [];
    foreach ($stmt->fetchAll() as $t) {
        $trades[] = enrichTrade($t, $db);
    }

    respond(['trades' => $trades]);
}

if ($action === 'inventory' && $method === 'GET') {
    $user   = requireAuth();
    $userId = (int)($_GET['user_id'] ?? $user['id']);

    $stmt = $db->prepare('
        SELECT c.*
        FROM user_cosmetics uc
        JOIN cosmetics c ON c.id = uc.cosmetic_id
        WHERE uc.user_id = ?
        ORDER BY c.type, c.name
    ');
    $stmt->execute([$userId]);

    $items = array_map('tradeCosmeticRow', $stmt->fetchAll());

    respond(['items' => $items]);
}

if ($action === 'create' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();

    $toUserId   = (int)($b['to_user_id'] ?? 0);
    $offerItems = $b['offer_items'] ?? [];
    $wantItems  = $b['want_items']  ?? [];

    if (!$toUserId)                              error('to_user_id required');
    if ($toUserId === $user['id'])               error('Cannot trade with yourself');
    if (empty($offerItems) && empty($wantItems)) error('Select at least one item');

    $stmt = $db->prepare('SELECT id FROM users WHERE id = ?');
    $stmt->execute([$toUserId]);
    if (!$stmt->fetch()) error('User not found', 404);

    if (!empty($offerItems)) {
        $ids = array_map(fn($i) => (int)$i['id'], $offerItems);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT cosmetic_id FROM user_cosmetics
            WHERE user_id = ? AND cosmetic_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$user['id']], $ids));
        $ownedIds = array_column($stmt->fetchAll(), 'cosmetic_id');
        foreach ($ids as $id) {
            if (!in_array($id, $ownedIds)) error("You don't own cosmetic ID $id");
        }
    }

    if (!empty($wantItems)) {
        $ids = array_map(fn($i) => (int)$i['id'], $wantItems);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT cosmetic_id FROM user_cosmetics
            WHERE user_id = ? AND cosmetic_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$toUserId], $ids));
        $ownedIds = array_column($stmt->fetchAll(), 'cosmetic_id');
        foreach ($ids as $id) {
            if (!in_array($id, $ownedIds)) error("Target user doesn't own cosmetic ID $id");
        }
    }

    $cleanOffer = array_map(fn($i) => ['id' => (int)$i['id'], 'name' => $i['name'], 'type' => $i['type']], $offerItems);
    $cleanWant  = array_map(fn($i) => ['id' => (int)$i['id'], 'name' => $i['name'], 'type' => $i['type']], $wantItems);

    $stmt = $db->prepare('
        INSERT INTO trades (from_user_id, to_user_id, offer_items, want_items)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([
        $user['id'],
        $toUserId,
        json_encode($cleanOffer),
        json_encode($cleanWant),
    ]);

    respond(['ok' => true, 'trade_id' => (int)$db->lastInsertId()]);
}

if ($action === 'respond' && $method === 'POST') {
    $user     = requireAuth();
    $b        = body();
    $tradeId  = (int)($b['trade_id'] ?? 0);
    $response = $b['response'] ?? '';

    if (!$tradeId) error('trade_id required');
    if (!in_array($response, ['accept', 'decline', 'cancel'])) error('Invalid response');

    $stmt = $db->prepare('SELECT * FROM trades WHERE id = ? AND status = ?');
    $stmt->execute([$tradeId, 'pending']);
    $trade = $stmt->fetch();
    if (!$trade) error('Trade not found or already resolved', 404);

    if ($response === 'cancel') {
        if ((int)$trade['from_user_id'] !== (int)$user['id']) error('Only the sender can cancel');
    } else {
        if ((int)$trade['to_user_id'] !== (int)$user['id']) error('Only the recipient can accept or decline');
    }

    if ($response === 'accept') {
        $db->beginTransaction();
        try {
            $offerItems = json_decode($trade['offer_items'], true) ?: [];
            $wantItems  = json_decode($trade['want_items'], true) ?: [];

            $fromId = (int)$trade['from_user_id'];
            $toId   = (int)$trade['to_user_id'];

            foreach ($offerItems as $item) {
                $chk = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
                $chk->execute([$fromId, (int)$item['id']]);
                if (!$chk->fetch()) { $db->rollBack(); error('Sender no longer owns "' . $item['name'] . '"'); }
            }
            foreach ($wantItems as $item) {
                $chk = $db->prepare('SELECT 1 FROM user_cosmetics WHERE user_id = ? AND cosmetic_id = ?');
                $chk->execute([$toId, (int)$item['id']]);
                if (!$chk->fetch()) { $db->rollBack(); error('You no longer own "' . $item['name'] . '"'); }
            }

            foreach ($offerItems as $item) {
                $cid = (int)$item['id'];
                $db->prepare('DELETE FROM user_equipped WHERE user_id = ? AND cosmetic_id = ?')->execute([$fromId, $cid]);
                $db->prepare('UPDATE user_cosmetics SET user_id = ?, granted_by = NULL, granted_at = NOW() WHERE user_id = ? AND cosmetic_id = ?')
                   ->execute([$toId, $fromId, $cid]);
            }

            foreach ($wantItems as $item) {
                $cid = (int)$item['id'];
                $db->prepare('DELETE FROM user_equipped WHERE user_id = ? AND cosmetic_id = ?')->execute([$toId, $cid]);
                $db->prepare('UPDATE user_cosmetics SET user_id = ?, granted_by = NULL, granted_at = NOW() WHERE user_id = ? AND cosmetic_id = ?')
                   ->execute([$fromId, $toId, $cid]);
            }

            $db->prepare("UPDATE trades SET status = 'accepted' WHERE id = ?")->execute([$tradeId]);

            $allItemIds = array_merge(
                array_map(fn($i) => (int)$i['id'], $offerItems),
                array_map(fn($i) => (int)$i['id'], $wantItems)
            );
            if (!empty($allItemIds)) {
                $pending = $db->prepare("SELECT id, offer_items, want_items FROM trades WHERE id != ? AND status = 'pending' AND (from_user_id IN (?, ?) OR to_user_id IN (?, ?))");
                $pending->execute([$tradeId, $fromId, $toId, $fromId, $toId]);
                foreach ($pending->fetchAll() as $pt) {
                    $ptOffer = array_column(json_decode($pt['offer_items'], true) ?: [], 'id');
                    $ptWant  = array_column(json_decode($pt['want_items'], true) ?: [], 'id');
                    $ptAll   = array_merge($ptOffer, $ptWant);
                    if (array_intersect($allItemIds, $ptAll)) {
                        $db->prepare("UPDATE trades SET status = 'cancelled' WHERE id = ?")->execute([$pt['id']]);
                    }
                }
            }

            $db->commit();
            respond(['ok' => true]);

        } catch (\Exception $e) {
            $db->rollBack();
            error('Trade failed: ' . $e->getMessage(), 500);
        }
    } else {
        $newStatus = $response === 'cancel' ? 'cancelled' : 'declined';
        $db->prepare('UPDATE trades SET status = ? WHERE id = ?')->execute([$newStatus, $tradeId]);
        respond(['ok' => true]);
    }
}

error('Unknown action', 404);
