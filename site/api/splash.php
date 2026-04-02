<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT text FROM splash_texts WHERE active=1 ORDER BY RAND() LIMIT 1");
        $row  = $stmt->fetch();
        respond(['text' => $row ? $row['text'] : 'Justice Launcher']);
    } catch (Exception $e) { respond(['text' => 'Justice Launcher']); }
}

$user = requireStaff();

if ($method === 'POST') {
    $b    = body();
    $text = trim($b['text'] ?? '');
    if (!$text || strlen($text) > 200) error('Text required (max 200 chars)');
    try {
        $db->prepare("INSERT INTO splash_texts (text, added_by) VALUES (?,?)")->execute([$text, $user['id']]);
        respond(['ok' => true, 'id' => $db->lastInsertId()]);
    } catch (Exception $e) { error('Something went wrong. Please try again.'); }
}

if ($method === 'DELETE') {
    try { $db->prepare("DELETE FROM splash_texts WHERE id=?")->execute([(int)($_GET['id']??0)]); } catch (Exception $e) {}
    respond(['ok' => true]);
}

if ($method === 'PATCH') {
    $b  = body();
    $id = (int)($b['id'] ?? 0);
    try { $db->prepare("UPDATE splash_texts SET active=? WHERE id=?")->execute([(int)($b['active']??1), $id]); } catch (Exception $e) {}
    respond(['ok' => true]);
}

error('Method not allowed', 405);
