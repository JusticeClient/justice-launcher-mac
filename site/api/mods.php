<?php
require_once __DIR__ . '/../includes/api.php';
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    try {
        $stmt = $db->query("SELECT mod_id, mod_name, count FROM mod_usage ORDER BY count DESC LIMIT 12");
        respond(['mods' => $stmt->fetchAll()]);
    } catch (Exception $e) { respond(['mods' => []]); }
}

if ($method === 'POST') {
    requireAuth();
    $b    = body();
    $mods = $b['mods'] ?? [];
    if (!is_array($mods)) error('mods must be an array');
    try {
        foreach (array_slice($mods, 0, 50) as $mod) {
            $id   = preg_replace('/[^a-z0-9_\-]/', '', strtolower($mod['id'] ?? ''));
            $name = substr(trim($mod['name'] ?? $id), 0, 200);
            if (!$id) continue;
            $db->prepare("INSERT INTO mod_usage (mod_id, mod_name, count) VALUES (?,?,1) ON DUPLICATE KEY UPDATE count=count+1, mod_name=VALUES(mod_name)")
               ->execute([$id, $name]);
        }
    } catch (Exception $e) {}
    respond(['ok' => true]);
}

error('Method not allowed', 405);
