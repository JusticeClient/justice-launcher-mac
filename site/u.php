<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/includes/api.php';
$db = getDB();
$username = trim($_GET['u'] ?? preg_replace('#^.*/u/#', '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if (!$username) { header('Location: /'); exit; }
$stmt = $db->prepare("SELECT u.*, c.name AS clan_name, c.tag AS clan_tag, c.color AS clan_color FROM users u LEFT JOIN clans c ON c.id=u.clan_id WHERE u.username=?");
$stmt->execute([$username]);
$u = $stmt->fetch();
if (!$u) { http_response_code(404); die('<h1>User not found</h1>'); }

$safeU    = safeUser($u);
$isOnline = $safeU['status'] && $safeU['status'] !== 'offline';
$dotColor = $safeU['status'] === 'in-game' ? '#a78bfa' : ($isOnline ? '#4ade80' : '#475569');
$statusLabel = $safeU['status'] === 'in-game' ? '🎮 In Game' . ($safeU['gameVersion'] ? ' ('.$safeU['gameVersion'].')' : '') : ($isOnline ? 'Online' : 'Offline');

$lastSeen = 'Unknown';
if ($safeU['lastSeen']) {
    $diff = time() - strtotime($safeU['lastSeen']);
    if ($diff < 60) $lastSeen = 'Just now';
    elseif ($diff < 3600) $lastSeen = floor($diff/60).'m ago';
    elseif ($diff < 86400) $lastSeen = floor($diff/3600).'h ago';
    else $lastSeen = date('M j, Y', strtotime($safeU['lastSeen']));
}

$fCount = $db->prepare("SELECT COUNT(*) FROM forum_threads WHERE author_id=?"); $fCount->execute([$u['id']]); $fCount = (int)$fCount->fetchColumn();
$rCount = $db->prepare("SELECT COUNT(*) FROM forum_replies WHERE author_id=?"); $rCount->execute([$u['id']]); $rCount = (int)$rCount->fetchColumn();
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($u['username']) ?> — Justice Launcher</title>
<meta property="og:title" content="<?= htmlspecialchars($u['username']) ?> on Justice Launcher">
<meta property="og:description" content="<?= htmlspecialchars($u['bio'] ?? 'Justice Launcher player') ?>">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#06040e;color:#f1f5f9;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
a{color:#a78bfa;text-decoration:none}.back{display:inline-flex;align-items:center;gap:6px;padding:10px 0;font-size:13px;color:#64748b;margin-bottom:20px}.back:hover{color:#a78bfa}
.wrap{max-width:700px;margin:0 auto;padding:30px 20px}
.profile-card{background:#0d0b1a;border:1px solid rgba(124,58,237,.2);border-radius:18px;overflow:hidden;margin-bottom:20px}
.profile-banner{height:8px;background:linear-gradient(90deg,#4c1d95,#7c3aed,#a78bfa)}
.profile-body{padding:24px}
.profile-top{display:flex;align-items:center;gap:20px;margin-bottom:20px}
.av{width:70px;height:70px;border-radius:18px;background:linear-gradient(135deg,#4c1d95,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:30px;font-weight:900;color:#fff;flex-shrink:0;position:relative}
.av-dot{position:absolute;bottom:-2px;right:-2px;width:16px;height:16px;border-radius:50%;border:3px solid #0d0b1a;background:<?= $dotColor ?>}
.profile-name{font-size:24px;font-weight:800;color:#f1f5f9;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.badge{font-size:10px;font-weight:800;padding:2px 7px;border-radius:5px;text-transform:uppercase;letter-spacing:.06em}
.badge-admin{background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3)}
.badge-plus{background:rgba(124,58,237,.2);color:#a78bfa;border:1px solid rgba(124,58,237,.4)}
.badge-donor{background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3)}
.badge-media{background:rgba(192,132,252,.15);color:#c084fc;border:1px solid rgba(192,132,252,.3)}
.profile-status{font-size:13px;color:#64748b;margin-top:4px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.stat-box{background:#120f22;border-radius:10px;padding:14px 16px}
.stat-label{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#475569;margin-bottom:5px}
.stat-val{font-size:14px;font-weight:600;color:#e2e8f0}
.clan-box{margin-top:12px;padding:14px 16px;background:#120f22;border-radius:10px;display:flex;align-items:center;gap:12px}
.clan-tag{padding:4px 10px;border-radius:7px;font-size:12px;font-weight:800;color:#fff}
.bio{margin-top:14px;font-size:13px;color:#94a3b8;line-height:1.6;padding:14px 16px;background:#120f22;border-radius:10px}
.nav{margin-bottom:30px;display:flex;gap:8px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:0}
.nav a{padding:10px 14px;font-size:13px;font-weight:600;color:#64748b;border-bottom:2px solid transparent}
.nav a.on{color:#a78bfa;border-color:#7c3aed}
</style>
</head><body>
<div class="wrap">
  <a href="/" class="back">← Back to Justice</a>
  <div class="profile-card">
    <div class="profile-banner"></div>
    <div class="profile-body">
      <div class="profile-top">
        <div class="av"><?= strtoupper($u['username'][0]) ?><div class="av-dot"></div></div>
        <div>
          <div class="profile-name">
            <?= htmlspecialchars($u['username']) ?>
            <?php if ($safeU['role']==='admin'):  ?><span class="badge badge-admin">Admin</span><?php endif ?>
            <?php if ($safeU['role']==='staff'):  ?><span class="badge badge-admin">Staff</span><?php endif ?>
            <?php if ($safeU['role']==='media'):  ?><span class="badge badge-media">Media</span><?php endif ?>
            <?php if (!empty($u['plus_member'])): ?><span class="badge badge-plus">Plus</span><?php endif ?>
            <?php if (!empty($u['donor_badge'])): ?><span class="badge badge-donor">Donor</span><?php endif ?>
          </div>
          <div class="profile-status"><?= htmlspecialchars($statusLabel) ?> · Last seen <?= $lastSeen ?></div>
        </div>
      </div>

      <?php if ($u['bio']): ?>
        <div class="bio"><?= nl2br(htmlspecialchars($u['bio'])) ?></div>
      <?php endif; ?>

      <?php if ($u['clan_name']): ?>
        <div class="clan-box">
          <div class="clan-tag" style="background:<?= htmlspecialchars($u['clan_color'] ?? '#7c3aed') ?>">[<?= htmlspecialchars($u['clan_tag']) ?>]</div>
          <div style="font-size:13px;color:#e2e8f0"><?= htmlspecialchars($u['clan_name']) ?></div>
        </div>
      <?php endif; ?>

      <div class="grid" style="margin-top:14px">
        <div class="stat-box"><div class="stat-label">Minecraft</div><div class="stat-val"><?= htmlspecialchars($u['mc_username'] ?? 'Not linked') ?></div></div>
        <div class="stat-box"><div class="stat-label">Status</div><div class="stat-val"><?= htmlspecialchars($statusLabel) ?></div></div>
        <div class="stat-box"><div class="stat-label">Forum Posts</div><div class="stat-val"><?= $fCount + $rCount ?></div></div>
        <div class="stat-box"><div class="stat-label">Member Since</div><div class="stat-val"><?= date('M Y', strtotime($u['created_at'])) ?></div></div>
      </div>
    </div>
  </div>
</div>
</body></html>
