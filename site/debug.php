<?php
// ── JUSTICE LAUNCHER DEBUG PAGE ──────────────────────────────────────────────
// Upload this to your server, visit it in your browser to diagnose issues.
// DELETE THIS FILE after you've fixed everything — it exposes server info.
// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html>
<head>
<title>Justice Launcher — Debug</title>
<style>
  body{font-family:monospace;background:#0a0a0a;color:#e2e8f0;padding:30px;line-height:1.7}
  h1{color:#a78bfa;margin-bottom:20px}
  .ok{color:#34d399}.fail{color:#f43f5e}.warn{color:#f59e0b}
  .block{background:#141414;border:1px solid #2d2d2d;border-radius:8px;padding:16px;margin-bottom:16px}
  .block h2{margin:0 0 12px;font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:#888}
  .row{display:flex;gap:16px;margin-bottom:6px;font-size:13px}
  .label{color:#888;min-width:220px}
</style>
</head>
<body>
<h1>🔮 Justice Launcher — Server Diagnostics</h1>

<?php
// ── 1. PHP version ────────────────────────────────────────────────────────────
echo '<div class="block"><h2>PHP</h2>';
$phpOk = version_compare(PHP_VERSION, '7.4', '>=');
echo '<div class="row"><span class="label">PHP Version</span><span class="' . ($phpOk?'ok':'fail') . '">' . PHP_VERSION . ($phpOk?' ✓':' ✗ — need 7.4+') . '</span></div>';

$exts = ['pdo','pdo_mysql','json','mbstring'];
foreach ($exts as $ext) {
    $ok = extension_loaded($ext);
    echo '<div class="row"><span class="label">Extension: ' . $ext . '</span><span class="' . ($ok?'ok':'fail') . '">' . ($ok?'Loaded ✓':'MISSING ✗') . '</span></div>';
}
echo '</div>';

// ── 2. Config file ────────────────────────────────────────────────────────────
echo '<div class="block"><h2>Config File</h2>';
$configPath = __DIR__ . '/includes/config.php';
if (!file_exists($configPath)) {
    echo '<div class="row"><span class="label">includes/config.php</span><span class="fail">FILE NOT FOUND ✗</span></div>';
    echo '</div>';
} else {
    echo '<div class="row"><span class="label">includes/config.php</span><span class="ok">Found ✓</span></div>';
    require_once $configPath;
    echo '<div class="row"><span class="label">DB_HOST</span><span>' . DB_HOST . '</span></div>';
    echo '<div class="row"><span class="label">DB_NAME</span><span>' . DB_NAME . '</span></div>';
    echo '<div class="row"><span class="label">DB_USER</span><span>' . DB_USER . '</span></div>';
    echo '<div class="row"><span class="label">DB_PASS</span><span>' . str_repeat('*', strlen(DB_PASS)) . ' (' . strlen(DB_PASS) . ' chars)</span></div>';
    echo '<div class="row"><span class="label">SITE_URL</span><span>' . SITE_URL . '</span></div>';
    echo '<div class="row"><span class="label">JWT_SECRET set?</span><span class="' . (strlen(JWT_SECRET)>20?'ok':'warn') . '">' . (strlen(JWT_SECRET)>20?'Yes ('.strlen(JWT_SECRET).' chars) ✓':'Too short — change it!') . '</span></div>';
    echo '</div>';

    // ── 3. Database connection ────────────────────────────────────────────────
    echo '<div class="block"><h2>Database Connection</h2>';
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo '<div class="row"><span class="label">Connection</span><span class="ok">Connected ✓</span></div>';

        // Check tables
        $tables = ['users','friends','messages'];
        foreach ($tables as $t) {
            $exists = $pdo->query("SHOW TABLES LIKE '$t'")->rowCount() > 0;
            echo '<div class="row"><span class="label">Table: ' . $t . '</span><span class="' . ($exists?'ok':'fail') . '">' . ($exists?'Exists ✓':'MISSING — run schema.sql ✗') . '</span></div>';
        }

        // User count
        $count = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        echo '<div class="row"><span class="label">Users in DB</span><span>' . $count . '</span></div>';

    } catch (PDOException $e) {
        echo '<div class="row"><span class="label">Connection</span><span class="fail">FAILED ✗</span></div>';
        echo '<div class="row"><span class="label">Error</span><span class="fail">' . htmlspecialchars($e->getMessage()) . '</span></div>';
        echo '<br><b>Common fixes:</b><ul>
            <li>Make sure the database exists in cPanel → MySQL Databases</li>
            <li>Make sure the user is added to the database with All Privileges</li>
            <li>Check DB_NAME / DB_USER / DB_PASS in config.php exactly match cPanel</li>
        </ul>';
    }
    echo '</div>';
}

// ── 4. File structure ─────────────────────────────────────────────────────────
echo '<div class="block"><h2>API Files</h2>';
$files = [
    'includes/config.php','includes/db.php','includes/jwt.php','includes/api.php',
    'api/register.php','api/login.php','api/user.php','api/friends.php','api/messages.php',
    'assets/js/app.js', '.htaccess',
];
foreach ($files as $f) {
    $exists = file_exists(__DIR__ . '/' . $f);
    echo '<div class="row"><span class="label">' . $f . '</span><span class="' . ($exists?'ok':'fail') . '">' . ($exists?'✓':'MISSING ✗') . '</span></div>';
}
echo '</div>';

// ── 5. Quick API test ─────────────────────────────────────────────────────────
echo '<div class="block"><h2>Quick API Test</h2>';
echo '<p style="font-size:13px;color:#888">Click the button below to test the register endpoint directly:</p>';
echo '<br><button onclick="testApi()" style="background:#7c3aed;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-family:monospace">Test /api/register.php</button>';
echo '<pre id="out" style="margin-top:12px;font-size:12px;color:#888">Results will appear here…</pre>';
echo '</div>';
?>

<script>
async function testApi() {
  const out = document.getElementById('out');
  out.textContent = 'Testing…';
  try {
    const r = await fetch('/api/register.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({username:'TestUser99',email:'test99@example.com',password:'testpass123'})
    });
    const text = await r.text();
    out.style.color = r.ok ? '#34d399' : '#f59e0b';
    out.textContent = 'HTTP ' + r.status + '\n' + text;
  } catch(e) {
    out.style.color = '#f43f5e';
    out.textContent = 'FETCH FAILED: ' + e.message + '\n\nThis usually means:\n- The file does not exist at /api/register.php\n- The server is returning a non-HTTP error\n- A PHP fatal error before any output';
  }
}
</script>

<p style="margin-top:24px;font-size:12px;color:#555">⚠ DELETE debug.php from your server once everything is working.</p>
</body>
</html>
