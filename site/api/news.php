<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';
$db = getDB();
set_exception_handler(function(Throwable $e) {
    header("Content-Type: application/json");
    echo json_encode(["error" => "Something went wrong. Please try again later." . $e->getMessage()]);
    exit;
});

if ($action === 'list' && $method === 'GET') {
    $limit  = min((int)($_GET['limit'] ?? 10), 50);
    $offset = max((int)($_GET['offset'] ?? 0), 0);
    $stmt = $db->prepare("SELECT n.*, u.username AS author_name FROM news_posts n JOIN users u ON u.id=n.author_id WHERE n.published=1 ORDER BY n.pinned DESC, n.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    respond(['posts' => $stmt->fetchAll()]);
}

if ($action === 'get' && $method === 'GET') {
    $slug = $_GET['slug'] ?? '';
    $stmt = $db->prepare("SELECT n.*, u.username AS author_name FROM news_posts n JOIN users u ON u.id=n.author_id WHERE n.slug=? AND n.published=1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    if (!$post) error('Not found', 404);
    respond(['post' => $post]);
}

$admin = requireAdmin();

if ($action === 'create' && $method === 'POST') {
    $b     = body();
    $title = trim($b['title'] ?? '');
    $body2 = trim($b['body'] ?? '');
    if (!$title || !$body2) error('Title and body required');
    $slug  = preg_replace('/[^a-z0-9]+/', '-', strtolower($title)) . '-' . time();
    $excerpt = substr(strip_tags($body2), 0, 200);
    $db->prepare("INSERT INTO news_posts (title,slug,body,excerpt,author_id,published,pinned) VALUES (?,?,?,?,?,?,?)")
       ->execute([$title, $slug, $body2, $excerpt, $admin['id'], (int)($b['published']??0), (int)($b['pinned']??0)]);
    respond(['ok' => true, 'id' => $db->lastInsertId(), 'slug' => $slug]);
}

if ($action === 'update' && $method === 'PATCH') {
    $b  = body();
    $id = (int)($_GET['id'] ?? 0);
    $db->prepare("UPDATE news_posts SET title=?,body=?,excerpt=?,published=?,pinned=?,updated_at=NOW() WHERE id=?")
       ->execute([trim($b['title']??''), trim($b['body']??''), substr(strip_tags($b['body']??''),0,200), (int)($b['published']??0), (int)($b['pinned']??0), $id]);
    respond(['ok' => true]);
}

if ($method === 'DELETE') {
    $db->prepare("DELETE FROM news_posts WHERE id=?")->execute([(int)($_GET['id'] ?? 0)]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
