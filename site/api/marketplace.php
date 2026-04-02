<?php

require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

$db->exec("
    CREATE TABLE IF NOT EXISTS marketplace_listings (
        id           INT AUTO_INCREMENT PRIMARY KEY,
        seller_id    INT NOT NULL,
        name         VARCHAR(100) NOT NULL,
        description  TEXT,
        type         ENUM('cape','wings') NOT NULL,
        price        INT NOT NULL,
        texture_path VARCHAR(255) NOT NULL,
        preview_path VARCHAR(255) DEFAULT NULL,
        status       ENUM('pending','approved','rejected','sold','removed') DEFAULT 'pending',
        reviewed_by  INT DEFAULT NULL,
        review_note  TEXT DEFAULT NULL,
        buyer_id     INT DEFAULT NULL,
        sales_count  INT NOT NULL DEFAULT 0,
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_seller (seller_id),
        INDEX idx_status (status),
        INDEX idx_type   (type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$db->exec("ALTER TABLE marketplace_listings ADD COLUMN IF NOT EXISTS sales_count INT NOT NULL DEFAULT 0");

function listingRow(array $r): array {
    return [
        'id'          => (int)$r['id'],
        'sellerId'    => (int)$r['seller_id'],
        'sellerName'  => $r['seller_name'] ?? null,
        'name'        => $r['name'],
        'description' => $r['description'] ?? '',
        'type'        => $r['type'],
        'price'       => (int)$r['price'],
        'texturePath' => $r['texture_path'],
        'previewPath' => $r['preview_path'] ?? null,
        'status'      => $r['status'],
        'reviewedBy'  => $r['reviewed_by'] ? (int)$r['reviewed_by'] : null,
        'reviewNote'  => $r['review_note'] ?? null,
        'buyerId'     => $r['buyer_id'] ? (int)$r['buyer_id'] : null,
        'buyer'       => $r['buyer_name'] ?? null,
        'salesCount'  => (int)($r['sales_count'] ?? 0),
        'createdAt'   => $r['created_at'],
    ];
}

if ($action === 'browse' && $method === 'GET') {
    $type = $_GET['type'] ?? null;
    $sort = $_GET['sort'] ?? 'newest';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 24;
    $offset = ($page - 1) * $limit;

    $currentUserId = null;
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) {
        $payload = JWT::decode(substr($auth, 7));
        if ($payload) $currentUserId = (int)$payload['id'];
    }

    $sql = "SELECT ml.*, u.username AS seller_name FROM marketplace_listings ml JOIN users u ON u.id = ml.seller_id WHERE ml.status = 'approved'";
    $params = [];

    if ($type && in_array($type, ['cape', 'wings'])) {
        $sql .= ' AND ml.type = ?';
        $params[] = $type;
    }

    if ($sort === 'price-low')  $sql .= ' ORDER BY ml.price ASC, ml.created_at DESC';
    elseif ($sort === 'price-high') $sql .= ' ORDER BY ml.price DESC, ml.created_at DESC';
    else $sql .= ' ORDER BY ml.created_at DESC';

    $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $listings = array_map(function($r) use ($currentUserId) {
        $item = listingRow($r);
        $item['isOwn'] = $currentUserId && (int)$r['seller_id'] === $currentUserId;
        return $item;
    }, $rows);

    $countSql = "SELECT COUNT(*) FROM marketplace_listings WHERE status = 'approved'";
    $countParams = [];
    if ($type && in_array($type, ['cape', 'wings'])) {
        $countSql .= ' AND type = ?';
        $countParams[] = $type;
    }
    $total = $db->prepare($countSql);
    $total->execute($countParams);
    $totalCount = (int)$total->fetchColumn();

    respond([
        'listings' => $listings,
        'total'    => $totalCount,
        'page'     => $page,
        'pages'    => (int)ceil($totalCount / $limit) ?: 1,
    ]);
}

if ($action === 'my-listings' && $method === 'GET') {
    $user = requireAuth();
    $stmt = $db->prepare("SELECT ml.*, u.username AS seller_name FROM marketplace_listings ml JOIN users u ON u.id = ml.seller_id WHERE ml.seller_id = ? ORDER BY ml.created_at DESC");
    $stmt->execute([$user['id']]);
    respond(['listings' => array_map('listingRow', $stmt->fetchAll())]);
}

if ($action === 'create' && $method === 'POST') {
    $user = requireAuth();

    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $price = (int)($_POST['price'] ?? 0);

    if (!$name || strlen($name) > 100) error('Name is required (max 100 chars)');
    if (!in_array($type, ['cape', 'wings'])) error('Type must be cape or wings');
    if ($price < 1) error('Price must be at least 1 point');
    if ($price > 999999) error('Price too high');

    if (!isset($_FILES['texture']) || $_FILES['texture']['error'] !== UPLOAD_ERR_OK) {
        error('Texture file is required');
    }
    if (!isset($_FILES['preview']) || $_FILES['preview']['error'] !== UPLOAD_ERR_OK) {
        error('Preview image is required');
    }

    $uploadDir = __DIR__ . '/../uploads/marketplace/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $file = $_FILES['texture'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'png') error('Only PNG files are allowed for texture');
    if ($file['size'] > 2 * 1024 * 1024) error('Texture too large (max 2MB)');
    $info = @getimagesize($file['tmp_name']);
    if (!$info || $info[2] !== IMAGETYPE_PNG) error('Invalid PNG texture');
    $texFilename = 'ml_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.png';
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $texFilename)) error('Failed to save texture');
    $texturePath = 'uploads/marketplace/' . $texFilename;

    $prev = $_FILES['preview'];
    $prevExt = strtolower(pathinfo($prev['name'], PATHINFO_EXTENSION));
    if (!in_array($prevExt, ['png', 'jpg', 'jpeg'])) error('Preview must be PNG or JPG');
    if ($prev['size'] > 3 * 1024 * 1024) error('Preview too large (max 3MB)');
    $prevInfo = @getimagesize($prev['tmp_name']);
    if (!$prevInfo) error('Invalid preview image');
    $prevFilename = 'mlp_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $prevExt;
    if (!move_uploaded_file($prev['tmp_name'], $uploadDir . $prevFilename)) error('Failed to save preview');
    $previewPath = 'uploads/marketplace/' . $prevFilename;

    $stmt = $db->prepare('INSERT INTO marketplace_listings (seller_id, name, description, type, price, texture_path, preview_path) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'], $name, $desc, $type, $price, $texturePath, $previewPath]);

    respond(['ok' => true, 'id' => (int)$db->lastInsertId(), 'message' => 'Listing submitted for review']);
}

if ($action === 'remove' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $id   = (int)($b['id'] ?? $b['listingId'] ?? 0);
    if (!$id) error('id required');

    $stmt = $db->prepare("SELECT * FROM marketplace_listings WHERE id = ? AND seller_id = ? AND status IN ('pending','approved')");
    $stmt->execute([$id, $user['id']]);
    $listing = $stmt->fetch();
    if (!$listing) error('Listing not found or cannot be removed');

    $db->prepare("UPDATE marketplace_listings SET status = 'removed' WHERE id = ?")->execute([$id]);
    respond(['ok' => true]);
}

if ($action === 'buy' && $method === 'POST') {
    $user = requireAuth();
    $b    = body();
    $id   = (int)($b['id'] ?? $b['listingId'] ?? 0);
    if (!$id) error('id required');

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT * FROM marketplace_listings WHERE id = ? AND status = 'approved' FOR UPDATE");
        $stmt->execute([$id]);
        $listing = $stmt->fetch();
        if (!$listing) { $db->rollBack(); error('Listing not available'); }
        if ((int)$listing['seller_id'] === (int)$user['id']) { $db->rollBack(); error('Cannot buy your own listing'); }

        $already = $db->prepare("SELECT c.id FROM cosmetics c JOIN user_cosmetics uc ON uc.cosmetic_id = c.id WHERE uc.user_id = ? AND c.slug LIKE ?");
        $already->execute([$user['id'], '%-ml' . $listing['id'] . '-%']);
        if ($already->fetch()) { $db->rollBack(); error('You already own this item'); }

        $fresh = $db->prepare('SELECT points FROM users WHERE id = ? FOR UPDATE');
        $fresh->execute([$user['id']]);
        $buyerPoints = (int)$fresh->fetchColumn();
        if ($buyerPoints < (int)$listing['price']) { $db->rollBack(); error('Not enough points'); }

        $db->prepare('UPDATE users SET points = points - ? WHERE id = ?')->execute([(int)$listing['price'], (int)$user['id']]);

        $sellerUpdate = $db->prepare('UPDATE users SET points = points + ? WHERE id = ?');
        $sellerUpdate->execute([(int)$listing['price'], (int)$listing['seller_id']]);
        if ($sellerUpdate->rowCount() === 0) {
            $db->rollBack();
            error('Seller account not found');
        }

        $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?, ?, ?)")
           ->execute([(int)$user['id'], -(int)$listing['price'], 'Marketplace purchase: ' . $listing['name']]);
        $db->prepare("INSERT INTO points_log (user_id, amount, reason) VALUES (?, ?, ?)")
           ->execute([(int)$listing['seller_id'], (int)$listing['price'], 'Marketplace sale: ' . $listing['name']]);

        $cosSlug = preg_replace('/[^a-z0-9]+/', '-', strtolower(trim($listing['name'])));
        $uniqueSlug = $cosSlug . '-ml' . $listing['id'] . '-' . $user['id'] . '-' . time();
        $previewPath = $listing['preview_path'] ?? null;
        $db->prepare('INSERT INTO cosmetics (name, slug, type, description, texture_path, preview_path, rarity, plus_only, active, price) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 1, NULL)')
           ->execute([$listing['name'], $uniqueSlug, $listing['type'], $listing['description'] ?? '', $listing['texture_path'], $previewPath, 'uncommon']);
        $cosmeticId = (int)$db->lastInsertId();

        $db->prepare('INSERT INTO user_cosmetics (user_id, cosmetic_id, granted_at) VALUES (?, ?, NOW())')->execute([(int)$user['id'], $cosmeticId]);

        $db->prepare('UPDATE marketplace_listings SET sales_count = sales_count + 1 WHERE id = ?')->execute([$id]);

        $db->commit();

        $pts = $db->prepare('SELECT points FROM users WHERE id = ?');
        $pts->execute([$user['id']]);
        $newBalance = (int)$pts->fetchColumn();

        respond(['ok' => true, 'message' => 'Purchase complete! The cosmetic has been added to your inventory.', 'pointsRemaining' => $newBalance]);
    } catch (\Exception $e) {
        $db->rollBack();
        error('Purchase failed: ' . $e->getMessage(), 500);
    }
}

if ($action === 'pending' && $method === 'GET') {
    requireStaff();
    $stmt = $db->prepare("SELECT ml.*, u.username AS seller_name FROM marketplace_listings ml JOIN users u ON u.id = ml.seller_id WHERE ml.status = 'pending' ORDER BY ml.created_at ASC");
    $stmt->execute();
    respond(['listings' => array_map('listingRow', $stmt->fetchAll())]);
}

if ($action === 'review-history' && $method === 'GET') {
    requireStaff();
    $stmt = $db->prepare("SELECT ml.*, u.username AS seller_name, r.username AS reviewer_name FROM marketplace_listings ml JOIN users u ON u.id = ml.seller_id LEFT JOIN users r ON r.id = ml.reviewed_by WHERE ml.status IN ('approved','rejected') ORDER BY ml.updated_at DESC LIMIT 50");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    $listings = array_map(function($r) {
        $item = listingRow($r);
        $item['reviewerName'] = $r['reviewer_name'] ?? null;
        return $item;
    }, $rows);
    respond(['listings' => $listings]);
}

if ($action === 'review' && $method === 'POST') {
    $staff = requireStaff();
    $b     = body();
    $id       = (int)($b['id'] ?? 0);
    $decision = $b['decision'] ?? '';
    $note     = trim($b['note'] ?? '');

    if (!$id) error('id required');
    if (!in_array($decision, ['approve', 'reject'])) error('decision must be approve or reject');

    $stmt = $db->prepare("SELECT * FROM marketplace_listings WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) error('Listing not found or already reviewed');

    $newStatus = $decision === 'approve' ? 'approved' : 'rejected';
    $db->prepare('UPDATE marketplace_listings SET status = ?, reviewed_by = ?, review_note = ? WHERE id = ?')
       ->execute([$newStatus, $staff['id'], $note ?: null, $id]);

    respond(['ok' => true]);
}

error('Unknown action', 404);
