<?php
/**
 * JUSTICE LAUNCHER — Reports API (with live chat)
 *
 * User endpoints (auth required):
 *   POST ?action=submit        → Submit a new report
 *   GET  ?action=my-reports    → List the current user's reports
 *   GET  ?action=messages&id=  → Get chat messages for a report
 *   POST ?action=send-message  → Send a chat message on a report
 *
 * Staff endpoints (staff/admin only):
 *   GET  ?action=list          → List all reports (filterable)
 *   POST ?action=update        → Update report status
 *   GET  ?action=stats         → Report counts by status
 *   GET  ?action=activity      → Recent staff actions on reports
 */
require_once __DIR__ . '/../includes/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

// Auto-create tables on first use
$db->exec("CREATE TABLE IF NOT EXISTS reports (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED NOT NULL,
  category      ENUM('player','bug','general') NOT NULL DEFAULT 'general',
  subject       VARCHAR(200) NOT NULL,
  description   TEXT NOT NULL,
  reported_user VARCHAR(100) DEFAULT NULL,
  status        ENUM('open','in_progress','resolved','dismissed') NOT NULL DEFAULT 'open',
  handled_by    INT UNSIGNED DEFAULT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME DEFAULT NULL,
  INDEX idx_reports_user (user_id),
  INDEX idx_reports_status (status),
  INDEX idx_reports_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->exec("CREATE TABLE IF NOT EXISTS report_messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_id   INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  is_staff    TINYINT(1) NOT NULL DEFAULT 0,
  message     TEXT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rm_report (report_id),
  INDEX idx_rm_created (report_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ═══════════════════════════════════════════════════════════════════
//  SUBMIT REPORT
// ═══════════════════════════════════════════════════════════════════
if ($action === 'submit' && $method === 'POST') {
    $user = requireAuth();
    $uid  = $user['id'];
    $data = body();

    $category     = $data['category'] ?? 'general';
    $subject      = trim($data['subject'] ?? '');
    $description  = trim($data['description'] ?? '');
    $reportedUser = trim($data['reported_user'] ?? '');

    if (!in_array($category, ['player', 'bug', 'general'])) error('Invalid category');
    if (strlen($subject) < 3 || strlen($subject) > 200) error('Subject must be 3-200 characters');
    if (strlen($description) < 10) error('Description must be at least 10 characters');
    if ($category === 'player' && strlen($reportedUser) < 1) error('Player username is required for player reports');

    // Rate limit: max 5 open reports per user
    $stmt = $db->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND status IN ('open','in_progress')");
    $stmt->execute([$uid]);
    if ((int)$stmt->fetchColumn() >= 5) error('You already have 5 open reports. Wait for some to be resolved.');

    $db->beginTransaction();
    try {
        $db->prepare(
            "INSERT INTO reports (user_id, category, subject, description, reported_user, status, created_at)
             VALUES (?, ?, ?, ?, ?, 'open', NOW())"
        )->execute([$uid, $category, $subject, $description, $category === 'player' ? $reportedUser : null]);

        $reportId = (int)$db->lastInsertId();

        // Insert the description as the first chat message
        $db->prepare(
            "INSERT INTO report_messages (report_id, user_id, is_staff, message, created_at)
             VALUES (?, ?, 0, ?, NOW())"
        )->execute([$reportId, $uid, $description]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

    respond([
        'success' => true,
        'id'      => $reportId,
        'message' => 'Report submitted! Staff will review it soon.',
    ]);
}

// ═══════════════════════════════════════════════════════════════════
//  MY REPORTS
// ═══════════════════════════════════════════════════════════════════
if ($action === 'my-reports' && $method === 'GET') {
    $user = requireAuth();
    $stmt = $db->prepare(
        "SELECT r.id, r.category, r.subject, r.reported_user, r.status, r.created_at, r.updated_at,
                (SELECT COUNT(*) FROM report_messages rm WHERE rm.report_id = r.id) as message_count,
                (SELECT COUNT(*) FROM report_messages rm WHERE rm.report_id = r.id AND rm.is_staff = 1) as staff_replies
         FROM reports r WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 25"
    );
    $stmt->execute([$user['id']]);
    respond(['reports' => $stmt->fetchAll()]);
}

// ═══════════════════════════════════════════════════════════════════
//  GET MESSAGES FOR A REPORT
// ═══════════════════════════════════════════════════════════════════
if ($action === 'messages' && $method === 'GET') {
    $user = requireAuth();
    $reportId = (int)($_GET['id'] ?? 0);
    if (!$reportId) error('Missing report id');

    $isStaff = in_array($user['role'] ?? 'user', ['admin', 'staff']);

    // Verify access: must be the reporter OR staff
    $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();
    if (!$report) error('Report not found');
    if (!$isStaff && (int)$report['user_id'] !== (int)$user['id']) error('Access denied');

    // Fetch messages with usernames
    $stmt = $db->prepare(
        "SELECT rm.*, u.username
         FROM report_messages rm
         LEFT JOIN users u ON u.id = rm.user_id
         WHERE rm.report_id = ?
         ORDER BY rm.created_at ASC"
    );
    $stmt->execute([$reportId]);

    respond([
        'report'   => [
            'id'            => (int)$report['id'],
            'category'      => $report['category'],
            'subject'       => $report['subject'],
            'reported_user' => $report['reported_user'],
            'status'        => $report['status'],
            'created_at'    => $report['created_at'],
        ],
        'messages' => $stmt->fetchAll(),
    ]);
}

// ═══════════════════════════════════════════════════════════════════
//  SEND MESSAGE ON A REPORT
// ═══════════════════════════════════════════════════════════════════
if ($action === 'send-message' && $method === 'POST') {
    $user = requireAuth();
    $data = body();
    $reportId = (int)($data['report_id'] ?? 0);
    $message  = trim($data['message'] ?? '');

    if (!$reportId) error('Missing report_id');
    if (strlen($message) < 1) error('Message cannot be empty');
    if (strlen($message) > 2000) error('Message too long (max 2000 chars)');

    $isStaff = in_array($user['role'] ?? 'user', ['admin', 'staff']);

    // Verify access
    $stmt = $db->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();
    if (!$report) error('Report not found');
    if (!$isStaff && (int)$report['user_id'] !== (int)$user['id']) error('Access denied');

    // Don't allow messages on dismissed/resolved reports (unless staff reopening)
    if (in_array($report['status'], ['resolved', 'dismissed']) && !$isStaff) {
        error('This report is closed. Contact staff if you need to reopen it.');
    }

    $db->beginTransaction();
    try {
        $db->prepare(
            "INSERT INTO report_messages (report_id, user_id, is_staff, message, created_at)
             VALUES (?, ?, ?, ?, NOW())"
        )->execute([$reportId, $user['id'], $isStaff ? 1 : 0, $message]);

        // If staff is replying to an open report, auto-set to in_progress
        if ($isStaff && $report['status'] === 'open') {
            $db->prepare("UPDATE reports SET status = 'in_progress', handled_by = ?, updated_at = NOW() WHERE id = ?")
               ->execute([$user['id'], $reportId]);
        }

        // Update the report's updated_at timestamp
        $db->prepare("UPDATE reports SET updated_at = NOW() WHERE id = ?")->execute([$reportId]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }

    respond([
        'success'  => true,
        'username' => $user['username'],
        'is_staff' => $isStaff,
    ]);
}

// ═══════════════════════════════════════════════════════════════════
//  LIST ALL REPORTS (staff only)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'list' && $method === 'GET') {
    $user = requireAuth();
    if (!in_array($user['role'] ?? 'user', ['admin', 'staff'])) error('Forbidden', 403);

    $status   = $_GET['status'] ?? null;
    $category = $_GET['category'] ?? null;
    $page     = max(1, (int)($_GET['page'] ?? 1));
    $limit    = 20;
    $offset   = ($page - 1) * $limit;

    $where = []; $params = [];
    if ($status && in_array($status, ['open', 'in_progress', 'resolved', 'dismissed'])) {
        $where[] = 'r.status = ?'; $params[] = $status;
    }
    if ($category && in_array($category, ['player', 'bug', 'general'])) {
        $where[] = 'r.category = ?'; $params[] = $category;
    }
    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $countStmt = $db->prepare("SELECT COUNT(*) FROM reports r $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $sql = "SELECT r.*, u.username as reporter_username, h.username as handler_username,
                   (SELECT COUNT(*) FROM report_messages rm WHERE rm.report_id = r.id) as message_count
            FROM reports r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN users h ON h.id = r.handled_by
            $whereSQL
            ORDER BY
              CASE r.status WHEN 'open' THEN 0 WHEN 'in_progress' THEN 1 WHEN 'resolved' THEN 2 WHEN 'dismissed' THEN 3 END,
              r.created_at DESC
            LIMIT $limit OFFSET $offset";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    respond(['reports' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $limit)]);
}

// ═══════════════════════════════════════════════════════════════════
//  UPDATE REPORT STATUS (staff only)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'update' && $method === 'POST') {
    $user = requireAuth();
    if (!in_array($user['role'] ?? 'user', ['admin', 'staff'])) error('Forbidden', 403);

    $data      = body();
    $reportId  = (int)($data['id'] ?? 0);
    $newStatus = $data['status'] ?? null;
    if (!$reportId) error('Missing report id');

    $stmt = $db->prepare("SELECT id, status FROM reports WHERE id = ?");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch();
    if (!$report) error('Report not found');

    if (!$newStatus || !in_array($newStatus, ['open', 'in_progress', 'resolved', 'dismissed'])) {
        error('Invalid status');
    }

    $oldStatus = $report['status'];
    $db->prepare("UPDATE reports SET status = ?, handled_by = ?, updated_at = NOW() WHERE id = ?")
       ->execute([$newStatus, $user['id'], $reportId]);

    // Auto-post a system message about the status change
    if ($oldStatus !== $newStatus) {
        $statusLabel = str_replace('_', ' ', $newStatus);
        $db->prepare(
            "INSERT INTO report_messages (report_id, user_id, is_staff, message, created_at)
             VALUES (?, ?, 1, ?, NOW())"
        )->execute([$reportId, $user['id'], "changed status to {$statusLabel}"]);
    }

    respond(['success' => true]);
}

// ═══════════════════════════════════════════════════════════════════
//  REPORT STATS (staff only)
// ═══════════════════════════════════════════════════════════════════
if ($action === 'stats' && $method === 'GET') {
    $user = requireAuth();
    if (!in_array($user['role'] ?? 'user', ['admin', 'staff'])) error('Forbidden', 403);

    $stmt = $db->query("SELECT status, COUNT(*) as cnt FROM reports GROUP BY status");
    $counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'dismissed' => 0, 'total' => 0];
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['status']] = (int)$row['cnt'];
        $counts['total'] += (int)$row['cnt'];
    }
    respond($counts);
}

// ═══════════════════════════════════════════════════════════════════
//  RECENT ACTIVITY (staff only) — replaces old mod log
// ═══════════════════════════════════════════════════════════════════
if ($action === 'activity' && $method === 'GET') {
    $user = requireAuth();
    if (!in_array($user['role'] ?? 'user', ['admin', 'staff'])) error('Forbidden', 403);

    // Recent report actions: status changes + staff messages
    $stmt = $db->query(
        "SELECT rm.message, rm.is_staff, rm.created_at, u.username as staff_name,
                r.id as report_id, r.subject as report_subject, r.status as report_status,
                r.category as report_category, reporter.username as reporter_name
         FROM report_messages rm
         JOIN reports r ON r.id = rm.report_id
         JOIN users u ON u.id = rm.user_id
         LEFT JOIN users reporter ON reporter.id = r.user_id
         WHERE rm.is_staff = 1
         ORDER BY rm.created_at DESC
         LIMIT 20"
    );
    respond(['activity' => $stmt->fetchAll()]);
}

error('Unknown action', 404);
