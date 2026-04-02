<?php
/**
 * Justice Launcher — Announcement Cron
 * Add to cPanel cron jobs: * * * * * php /home/yourusername/public_html/cron_announcements.php
 */
require_once __DIR__ . '/includes/api.php';
$db = getDB();

// Find announcements due to be sent
$stmt = $db->prepare("SELECT * FROM announcements WHERE sent=0 AND send_at <= NOW()");
$stmt->execute();
$due = $stmt->fetchAll();

foreach ($due as $ann) {
    // Mark as sent — the launcher polls /api/announcements.php?action=pending and picks these up
    $db->prepare("UPDATE announcements SET sent=1 WHERE id=?")->execute([$ann['id']]);
    echo "[" . date('Y-m-d H:i:s') . "] Sent announcement #{$ann['id']}: {$ann['title']}\n";
}

echo "Checked " . count($due) . " announcements.\n";
