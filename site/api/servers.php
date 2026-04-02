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

function pingMinecraft(string $host, int $port = 25565, int $timeout = 3): array {
    $result = ['online' => false, 'players' => 0, 'max_players' => 0, 'motd' => '', 'ping' => 0];
    try {
        $start = microtime(true);
        $sock  = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$sock) return $result;
        stream_set_timeout($sock, $timeout);

        $hostPacked = pack('c', strlen($host)) . $host;
        $portPacked = pack('n', $port);
        $handshake  = "\x00\xff\xff\xff\xf5\x0f" . $hostPacked . $portPacked . "\x01";
        $len        = strlen($handshake);
        fwrite($sock, pack('c', $len) . $handshake . "\x01\x00");

        $data = '';
        while (!feof($sock)) { $data .= fread($sock, 2048); if (strlen($data) > 4096) break; }
        fclose($sock);

        $result['ping'] = round((microtime(true) - $start) * 1000);

        $json = substr($data, strpos($data, '{'));
        if ($json) {
            $info = json_decode($json, true);
            if ($info) {
                $result['online']      = true;
                $result['players']     = $info['players']['online'] ?? 0;
                $result['max_players'] = $info['players']['max']    ?? 0;
                $motd = $info['description'] ?? '';
                if (is_array($motd)) $motd = $motd['text'] ?? '';
                $result['motd'] = preg_replace('/§[0-9a-fk-or]/i', '', $motd);
            }
        }
    } catch (Exception $e) {}
    return $result;
}

if ($action === 'list' && $method === 'GET') {
    $stmt = $db->query("SELECT id,name,address,port,description,icon_url,featured,online,players_online,players_max,motd,last_ping FROM servers WHERE 1 ORDER BY featured DESC, sort_order ASC, name ASC");
    respond(['servers' => $stmt->fetchAll()]);
}

if ($action === 'ping' && $method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT id,address,port FROM servers WHERE id=?"); $stmt->execute([$id]);
    $srv = $stmt->fetch();
    if (!$srv) error('Not found', 404);
    $ping = pingMinecraft($srv['address'], $srv['port']);
    $db->prepare("UPDATE servers SET online=?,players_online=?,players_max=?,motd=?,last_ping=NOW() WHERE id=?")->execute([$ping['online']?1:0,$ping['players'],$ping['max_players'],$ping['motd'],$id]);
    respond($ping);
}

$user = requireAuth();
requireAdmin();

if ($action === 'save' && $method === 'POST') {
    $b    = body();
    $id   = (int)($b['id'] ?? 0);
    $name = trim($b['name'] ?? ''); $addr = trim($b['address'] ?? '');
    if (!$name || !$addr) error('Name and address required');
    if ($id) {
        $db->prepare("UPDATE servers SET name=?,address=?,port=?,description=?,icon_url=?,featured=?,sort_order=? WHERE id=?")
           ->execute([$name,$addr,(int)($b['port']??25565),trim($b['description']??''),trim($b['icon_url']??''),(int)($b['featured']??0),(int)($b['sort_order']??0),$id]);
    } else {
        $db->prepare("INSERT INTO servers (name,address,port,description,icon_url,featured,sort_order) VALUES (?,?,?,?,?,?,?)")
           ->execute([$name,$addr,(int)($b['port']??25565),trim($b['description']??''),trim($b['icon_url']??''),(int)($b['featured']??0),(int)($b['sort_order']??0)]);
    }
    respond(['ok' => true]);
}

if ($method === 'DELETE') {
    $db->prepare("DELETE FROM servers WHERE id=?")->execute([(int)($_GET['id']??0)]);
    respond(['ok' => true]);
}

error('Unknown action', 404);
