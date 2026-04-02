<?php
session_start();

define('API_BASE', 'https://justiceclient.org');

function apiCall($path, $method = 'GET', $body = null, $token = null) {
    $url = API_BASE . $path;
    $opts = [
        'http' => [
            'method'        => $method,
            'header'        => "Content-Type: application/json\r\n" .
                               ($token ? "Authorization: Bearer {$token}\r\n" : ''),
            'ignore_errors' => true,
            'timeout'       => 10,
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = is_string($body) ? $body : json_encode($body);
    }
    $ctx = stream_context_create($opts);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) return ['error' => 'Connection failed'];
    return json_decode($raw, true) ?: ['error' => 'Invalid response'];
}

function token() { return $_SESSION['jl_token'] ?? null; }
function user()  { return $_SESSION['jl_user']  ?? null; }

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax'];

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $res   = apiCall('/api/login.php', 'POST', $input);
        if (!empty($res['token']) && !empty($res['user'])) {
            $_SESSION['jl_token'] = $res['token'];
            $_SESSION['jl_user']  = $res['user'];
            echo json_encode(['ok' => true, 'user' => $res['user']]);
        } else {
            echo json_encode($res);
        }
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'auto-login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $t = $data['token'] ?? '';
        if (!$t) { echo json_encode(['error' => 'No token']); exit; }
        $res = apiCall('/api/user.php?action=me', 'GET', null, $t);
        if (!empty($res['user'])) {
            $_SESSION['jl_token'] = $t;
            $_SESSION['jl_user'] = $res['user'];
            echo json_encode(['ok' => true, 'user' => $res['user']]);
        } else {
            echo json_encode(['error' => 'Invalid token']);
        }
        exit;
    }

    if ($action === 'me') {
        $t = token();
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        $res = apiCall('/api/user.php?action=me', 'GET', null, $t);
        if (!empty($res['user'])) {
            $_SESSION['jl_user'] = $res['user'];
            echo json_encode(['ok' => true, 'user' => $res['user']]);
        } else {
            unset($_SESSION['jl_token'], $_SESSION['jl_user']);
            echo json_encode(['error' => 'Session expired']);
        }
        exit;
    }

    if ($action === 'trades') {
        $t = token();
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        echo json_encode(apiCall('/api/trades.php?action=list', 'GET', null, $t));
        exit;
    }

    if ($action === 'my-inventory') {
        $t = token();
        $u = user();
        if (!$t || !$u) { echo json_encode(['error' => 'Not logged in']); exit; }
        echo json_encode(apiCall('/api/trades.php?action=inventory&user_id=' . $u['id'], 'GET', null, $t));
        exit;
    }

    if ($action === 'their-inventory' && isset($_GET['userId'])) {
        $t  = token();
        $id = intval($_GET['userId']);
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        echo json_encode(apiCall('/api/trades.php?action=inventory&user_id=' . $id, 'GET', null, $t));
        exit;
    }

    if ($action === 'search' && isset($_GET['q'])) {
        $t = token();
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        $q = urlencode($_GET['q']);
        echo json_encode(apiCall('/api/user.php?action=search&q=' . $q, 'GET', null, $t));
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = token();
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        $body = file_get_contents('php://input');
        echo json_encode(apiCall('/api/trades.php?action=create', 'POST', $body, $t));
        exit;
    }

    if ($action === 'respond' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $t = token();
        if (!$t) { echo json_encode(['error' => 'Not logged in']); exit; }
        $body = file_get_contents('php://input');
        echo json_encode(apiCall('/api/trades.php?action=respond', 'POST', $body, $t));
        exit;
    }

    echo json_encode(['error' => 'Unknown action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trading — Justice Launcher</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg0:#08060e;--bg1:#0e0b18;--bg2:#141020;--bg3:#1c1730;--bg4:#231e3a;
      --border:rgba(139,92,246,0.12);--border-hi:rgba(139,92,246,0.35);
      --purple:#a78bfa;--purple-hi:#c4b5fd;--purple-dim:#7c3aed;
      --accent:#e879f9;--green:#4ade80;--amber:#fbbf24;--red:#f87171;
      --t1:#f0edf5;--t2:#c4bdd0;--t3:#8b82a0;--t4:#5a5270;
      --shadow:rgba(0,0,0,.4);
      --transition-smooth:cubic-bezier(.4,0,.2,1);
    }
    body {
      font-family: 'Space Grotesk', sans-serif;
      background: var(--bg0);
      color: var(--t1);
      min-height: 100vh;
      overflow-x: hidden;
    }
    a { color: var(--purple); text-decoration: none; }
    a:hover { text-decoration: underline; }
    #bg-canvas { position: fixed; inset: 0; z-index: 0; opacity: .3; pointer-events: none; }
    .topbar {
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      height: 56px; padding: 0 28px;
      background: rgba(8,6,14,.82); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
    }
    .topbar-left { display: flex; align-items: center; gap: 14px; }
    .gem { width: 22px; height: 22px; background: linear-gradient(135deg,var(--purple-dim),var(--accent)); clip-path: polygon(50% 0%,100% 38%,82% 100%,18% 100%,0% 38%); box-shadow: 0 0 16px rgba(167,139,250,.5); flex-shrink: 0; }
    .topbar-title { font-size: 16px; font-weight: 800; letter-spacing: .06em; background: linear-gradient(90deg,var(--purple-hi),var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .topbar-nav { display: flex; gap: 6px; }
    .topbar-nav a { padding: 6px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; color: var(--t3); transition: all .15s; text-decoration: none; }
    .topbar-nav a:hover { color: var(--t1); background: rgba(255,255,255,.05); }
    .topbar-nav a.active { color: var(--purple-hi); background: rgba(139,92,246,.12); }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .user-chip { display: flex; align-items: center; gap: 8px; padding: 5px 12px 5px 5px; border-radius: 10px; background: rgba(255,255,255,.04); border: 1px solid var(--border); cursor: pointer; transition: all .15s; }
    .user-chip:hover { background: rgba(139,92,246,.08); border-color: var(--border-hi); }
    .user-av { width: 28px; height: 28px; border-radius: 7px; background: linear-gradient(135deg,var(--purple-dim),var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; }
    .user-name { font-size: 12px; font-weight: 600; color: var(--t1); }
    .container { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; padding: 28px 24px 60px; }
    .auth-gate { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; padding: 80px 20px; text-align: center; }
    .auth-gate svg { opacity: .25; }
    .auth-gate h2 { font-size: 22px; font-weight: 800; }
    .auth-gate p { font-size: 13px; color: var(--t3); max-width: 380px; line-height: 1.7; }
    .login-box { margin-top: 8px; display: flex; flex-direction: column; gap: 8px; width: 280px; }
    .login-box input { padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.1); background: rgba(0,0,0,.35); color: var(--t1); font-family: 'Space Grotesk', sans-serif; font-size: 13px; outline: none; transition: border-color .2s; }
    .login-box input:focus { border-color: var(--purple); }
    .login-box input::placeholder { color: var(--t4); }
    .btn-primary { padding: 10px 22px; border-radius: 8px; border: none; background: var(--purple-dim); color: #fff; font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .btn-primary:hover { filter: brightness(1.15); transform: scale(1.02); }
    .btn-primary:disabled { opacity: .45; cursor: not-allowed; transform: none; filter: none; }
    .btn-secondary { padding: 10px 18px; border-radius: 8px; border: 1px solid rgba(255,255,255,.12); background: transparent; color: var(--t2); font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .btn-secondary:hover { background: rgba(255,255,255,.05); }
    .btn-accept { padding: 9px 22px; border-radius: 8px; border: none; background: var(--green); color: #fff; font-family: 'Space Grotesk', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .btn-accept:hover { filter: brightness(1.1); transform: scale(1.02); }
    .btn-decline { padding: 9px 18px; border-radius: 8px; border: 1px solid rgba(248,113,113,.3); background: transparent; color: var(--red); font-family: 'Space Grotesk', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .15s; }
    .btn-decline:hover { background: rgba(248,113,113,.08); }
    .page-hdr { margin-bottom: 24px; }
    .page-hdr h1 { font-size: 24px; font-weight: 800; }
    .page-hdr h1 span { color: var(--purple); }
    .page-hdr p { font-size: 13px; color: var(--t3); margin-top: 4px; }
    .tabs { display: flex; gap: 4px; margin-bottom: 20px; }
    .tab { padding: 9px 18px; border-radius: 8px; border: 1px solid transparent; background: rgba(255,255,255,.03); color: var(--t3); font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s; position: relative; }
    .tab:hover { color: var(--t1); background: rgba(255,255,255,.06); }
    .tab.active { color: var(--purple-hi); background: rgba(139,92,246,.1); border-color: rgba(139,92,246,.25); }
    .tab-badge { font-size: 10px; padding: 1px 6px; border-radius: 8px; background: rgba(251,191,36,.18); color: var(--amber); font-weight: 700; margin-left: 6px; }
    .trade-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 12px; }
    .trade-card { padding: 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.02); backdrop-filter: blur(6px); cursor: pointer; transition: all .2s var(--transition-smooth); }
    .trade-card:hover { background: rgba(139,92,246,.06); border-color: rgba(139,92,246,.2); transform: translateY(-1px); }
    .tc-top { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .tc-av { width: 34px; height: 34px; border-radius: 9px; background: linear-gradient(135deg,var(--purple-dim),var(--accent)); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: #fff; flex-shrink: 0; }
    .tc-name { font-size: 13px; font-weight: 600; color: var(--t1); }
    .tc-time { font-size: 10px; color: var(--t4); }
    .tc-items { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 8px; }
    .tc-item { font-size: 10px; padding: 3px 8px; border-radius: 6px; background: rgba(255,255,255,.05); color: var(--t3); border: 1px solid rgba(255,255,255,.06); }
    .tc-status { font-size: 11px; font-weight: 600; }
    .tc-status.pending { color: var(--amber); }
    .tc-status.accepted { color: var(--green); }
    .tc-status.declined { color: var(--red); }
    .tc-status.cancelled { color: var(--t4); }
    .tc-actions { display: flex; gap: 8px; margin-top: 10px; }
    .item-grid { display: flex; gap: 8px; flex-wrap: wrap; }
    .item-box { width: 72px; text-align: center; }
    .item-icon { width: 60px; height: 60px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 4px; border: 1px solid rgba(255,255,255,.08); }
    .item-icon.cape { background: linear-gradient(135deg,rgba(139,92,246,.15),rgba(168,85,247,.1)); border-color: rgba(139,92,246,.25); }
    .item-icon.wings { background: linear-gradient(135deg,rgba(74,222,128,.12),rgba(34,197,94,.08)); border-color: rgba(74,222,128,.2); }
    .item-name { font-size: 10px; color: var(--t2); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .item-type { font-size: 8.5px; color: var(--t4); text-transform: uppercase; letter-spacing: .04em; }
    .offer-box { background: rgba(10,5,20,.4); backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,.08); border-radius: 14px; padding: 18px; margin-bottom: 14px; }
    .offer-label { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--t3); margin-bottom: 10px; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.6); backdrop-filter: blur(6px); z-index: 500; display: none; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--bg2); border: 1px solid var(--border); border-radius: 16px; width: 540px; max-width: 95vw; max-height: 80vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 24px 64px rgba(0,0,0,.5); }
    .modal-hdr { padding: 18px 20px 14px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-hdr h3 { font-size: 15px; font-weight: 700; }
    .modal-close { width: 28px; height: 28px; border-radius: 6px; border: none; background: transparent; color: var(--t3); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .12s; }
    .modal-close:hover { background: rgba(255,255,255,.08); color: var(--t1); }
    .modal-body { padding: 18px 20px; overflow-y: auto; flex: 1; }
    .modal-body::-webkit-scrollbar { width: 4px; } .modal-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }
    .modal-footer { padding: 14px 20px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 8px; }
    .search-input { width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,.1); background: rgba(0,0,0,.3); color: var(--t1); font-family: 'Space Grotesk', sans-serif; font-size: 13px; outline: none; transition: border-color .2s; }
    .search-input:focus { border-color: var(--purple); }
    .search-input::placeholder { color: var(--t4); }
    .user-result { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; cursor: pointer; transition: background .12s; border: 1px solid transparent; }
    .user-result:hover { background: rgba(139,92,246,.06); }
    .user-result.selected { background: rgba(139,92,246,.1); border-color: rgba(139,92,246,.3); }
    .inv-section { margin-top: 16px; }
    .inv-label { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--t3); margin-bottom: 8px; }
    .inv-grid { display: flex; gap: 6px; flex-wrap: wrap; }
    .inv-item { width: 68px; padding: 8px 4px; text-align: center; border-radius: 8px; border: 1px solid rgba(255,255,255,.06); background: rgba(255,255,255,.02); cursor: pointer; transition: all .15s; }
    .inv-item:hover { background: rgba(139,92,246,.06); border-color: rgba(139,92,246,.15); }
    .inv-item.selected { background: rgba(139,92,246,.12); border-color: rgba(139,92,246,.4); box-shadow: 0 0 12px rgba(139,92,246,.15); }
    .inv-item .ii-icon { font-size: 24px; margin-bottom: 4px; }
    .inv-item .ii-name { font-size: 9px; color: var(--t2); font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .arrow-divider { display: flex; align-items: center; justify-content: center; padding: 12px 0; color: var(--t4); }
    .detail-overlay { position: fixed; inset: 0; z-index: 400; background: rgba(0,0,0,.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; }
    .detail-overlay.open { display: flex; }
    .detail-box { background: var(--bg1); border: 1px solid var(--border); border-radius: 16px; width: 520px; max-width: 95vw; max-height: 80vh; overflow-y: auto; padding: 24px; box-shadow: 0 24px 64px rgba(0,0,0,.5); }
    .detail-box::-webkit-scrollbar { width: 4px; } .detail-box::-webkit-scrollbar-thumb { background: rgba(255,255,255,.08); border-radius: 4px; }
    #toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px); padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600; color: #fff; z-index: 999; transition: transform .3s var(--transition-smooth), opacity .3s; opacity: 0; pointer-events: none; }
    #toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
    #toast.success { background: rgba(74,222,128,.2); border: 1px solid rgba(74,222,128,.3); color: var(--green); backdrop-filter: blur(12px); }
    #toast.error { background: rgba(248,113,113,.2); border: 1px solid rgba(248,113,113,.3); color: var(--red); backdrop-filter: blur(12px); }
    .empty-state { text-align: center; padding: 50px 20px; color: var(--t4); }
    .empty-state svg { margin-bottom: 12px; opacity: .25; }
    .empty-state p { font-size: 13px; }
    @media (max-width: 600px) {
      .trade-grid { grid-template-columns: 1fr; }
      .topbar-nav { display: none; }
      .container { padding: 18px 14px 40px; }
    }
  </style>
</head>
<body>
  <canvas id="bg-canvas"></canvas>
  <div class="topbar">
    <div class="topbar-left">
      <div class="gem"></div>
      <span class="topbar-title">JUSTICE LAUNCHER</span>
    </div>
    <div class="topbar-nav">
      <a href="https://justiceclient.org">Home</a>
      <a href="https://justiceclient.org/downloads">Downloads</a>
      <a class="active" href="#">Trading</a>
    </div>
    <div class="topbar-right" id="topbar-right">
      <span style="font-size:12px;color:var(--t4)" id="topbar-status">Not signed in</span>
    </div>
  </div>
  <div class="container">
    <div id="auth-gate" class="auth-gate">
      <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
      <h2>Cosmetic Trading</h2>
      <p>Trade capes and wings with other Justice players. Sign in to your Justice account to get started.</p>
      <div class="login-box">
        <input type="text" id="login-field" placeholder="Username or email">
        <input type="password" id="pass-field" placeholder="Password" onkeydown="if(event.key==='Enter')doLogin()">
        <button class="btn-primary" onclick="doLogin()" id="login-btn">Sign In</button>
        <div id="login-error" style="font-size:11px;color:var(--red);display:none"></div>
      </div>
    </div>
    <div id="trading-ui" style="display:none">
      <div class="page-hdr" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
          <h1>Cosmetic <span>Trading</span></h1>
          <p>Send and receive cape &amp; wing trades with other players</p>
        </div>
        <button class="btn-primary" onclick="openNewTrade()" style="flex-shrink:0">
          <span style="margin-right:4px">+</span> New Trade
        </button>
      </div>

      <div class="tabs">
        <button class="tab active" onclick="switchTab('incoming',this)">Incoming <span class="tab-badge" id="badge-incoming" style="display:none">0</span></button>
        <button class="tab" onclick="switchTab('outgoing',this)">Outgoing <span class="tab-badge" id="badge-outgoing" style="display:none">0</span></button>
        <button class="tab" onclick="switchTab('history',this)">History</button>
      </div>

      <div id="trade-list" class="trade-grid"></div>
    </div>
  </div>
  <div class="detail-overlay" id="detail-overlay" onclick="if(event.target===this)closeDetail()">
    <div class="detail-box" id="detail-box"></div>
  </div>
  <div class="modal-overlay" id="new-trade-modal" onclick="if(event.target===this)closeNewTrade()">
    <div class="modal">
      <div class="modal-hdr">
        <h3>New Trade</h3>
        <button class="modal-close" onclick="closeNewTrade()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <div id="step-user">
          <div class="inv-label">Find a player</div>
          <input type="text" class="search-input" id="user-search" placeholder="Search by username..." oninput="searchUser(this.value)">
          <div id="user-results" style="margin-top:8px;max-height:180px;overflow-y:auto"></div>
        </div>
        <div id="step-items" style="display:none">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;padding:10px;border-radius:8px;background:rgba(139,92,246,.06);border:1px solid rgba(139,92,246,.15)">
            <div class="tc-av" id="sel-av">?</div>
            <div>
              <div style="font-size:12px;font-weight:600;color:var(--t1)" id="sel-name">—</div>
              <div style="font-size:10px;color:var(--t3)">Trading with this player</div>
            </div>
            <button class="btn-secondary" onclick="backToSearch()" style="margin-left:auto;padding:4px 10px;font-size:10px">Change</button>
          </div>
          <div class="inv-section">
            <div class="inv-label">Your items to offer</div>
            <div class="inv-grid" id="my-items"></div>
            <div id="my-empty" style="font-size:11px;color:var(--t4);padding:8px 0;display:none">You don't have any tradeable cosmetics.</div>
          </div>
          <div class="arrow-divider">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          </div>
          <div class="inv-section">
            <div class="inv-label">Items you want from them</div>
            <div class="inv-grid" id="their-items"></div>
            <div id="their-empty" style="font-size:11px;color:var(--t4);padding:8px 0;display:none">This player has no tradeable cosmetics.</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeNewTrade()">Cancel</button>
        <button class="btn-primary" id="submit-btn" onclick="submitTrade()" disabled>Send Trade Request</button>
      </div>
    </div>
  </div>

  <div id="toast"></div>

  <script>
    let user = null;
    let currentTab = 'incoming';
    let cache = { incoming: [], outgoing: [], history: [] };

    let targetUser = null;
    let myInv = [], theirInv = [];
    let mySel = new Set(), theirSel = new Set();

    function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function emoji(type) { return type === 'cape' ? '\u{1F9E5}' : type === 'wings' ? '\u{1FABD}' : '\u2728'; }
    let toastTimer;
    function toast(msg, type = 'success') {
      const t = document.getElementById('toast');
      t.textContent = (type === 'success' ? '\u2713 ' : '\u2717 ') + msg;
      t.className = 'show ' + type;
      clearTimeout(toastTimer);
      toastTimer = setTimeout(() => t.className = '', 3000);
    }
    function timeAgo(d) {
      const ms = Date.now() - new Date(d).getTime();
      const m = Math.floor(ms / 60000);
      if (m < 1) return 'Just now';
      if (m < 60) return m + 'm ago';
      const h = Math.floor(m / 60);
      if (h < 24) return h + 'h ago';
      const dy = Math.floor(h / 24);
      if (dy < 7) return dy + 'd ago';
      return new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    }

    async function api(action, opts = {}) {
      const url = 'trading.php?ajax=' + action + (opts.params || '');
      const fetchOpts = { headers: { 'Content-Type': 'application/json' } };
      if (opts.method) fetchOpts.method = opts.method;
      if (opts.body)   fetchOpts.body = opts.body;
      const r = await fetch(url, fetchOpts);
      return r.json();
    }

    async function doLogin() {
      const login = document.getElementById('login-field').value.trim();
      const pass = document.getElementById('pass-field').value;
      const err = document.getElementById('login-error');
      if (!login || !pass) { err.textContent = 'Enter username and password'; err.style.display = 'block'; return; }
      err.style.display = 'none';
      document.getElementById('login-btn').disabled = true;
      document.getElementById('login-btn').textContent = 'Signing in...';
      try {
        const r = await api('login', {
          method: 'POST',
          body: JSON.stringify({ login, password: pass }),
        });
        if (r.error) { err.textContent = r.error; err.style.display = 'block'; return; }
        if (r.requires_2fa) { err.textContent = '2FA required \u2014 please use the launcher to sign in first, then refresh this page.'; err.style.display = 'block'; return; }
        user = r.user;
        showLoggedIn();
      } catch (e) { err.textContent = 'Connection failed'; err.style.display = 'block'; }
      finally { document.getElementById('login-btn').disabled = false; document.getElementById('login-btn').textContent = 'Sign In'; }
    }

    async function restoreSession() {
      try {
        const r = await api('me');
        if (r.ok && r.user) { user = r.user; showLoggedIn(); return; }
      } catch {}
      var t = localStorage.getItem('jl_token');
      if (!t) return;
      try {
        const r = await api('auto-login', { method: 'POST', body: JSON.stringify({ token: t }) });
        if (r.ok && r.user) { user = r.user; showLoggedIn(); }
      } catch {}
    }

    function showLoggedIn() {
      document.getElementById('auth-gate').style.display = 'none';
      document.getElementById('trading-ui').style.display = 'block';
      document.getElementById('topbar-right').innerHTML = `
        <div class="user-chip" onclick="doLogout()">
          <div class="user-av">${user.username[0].toUpperCase()}</div>
          <span class="user-name">${esc(user.username)}</span>
        </div>`;
      loadTrades();
    }

    function doLogout() {
      if (!confirm('Sign out?')) return;
      api('logout').then(() => location.reload());
    }

    async function loadTrades() {
      try {
        const r = await api('trades');
        const trades = r.trades || [];
        cache.incoming = trades.filter(t => t.to_user_id === user.id && t.status === 'pending');
        cache.outgoing = trades.filter(t => t.from_user_id === user.id && t.status === 'pending');
        cache.history = trades.filter(t => t.status !== 'pending');
        const ib = document.getElementById('badge-incoming');
        const ob = document.getElementById('badge-outgoing');
        if (ib) { ib.textContent = cache.incoming.length; ib.style.display = cache.incoming.length ? 'inline' : 'none'; }
        if (ob) { ob.textContent = cache.outgoing.length; ob.style.display = cache.outgoing.length ? 'inline' : 'none'; }
        renderList();
      } catch { document.getElementById('trade-list').innerHTML = '<div class="empty-state"><p>Could not load trades. Check your connection.</p></div>'; }
    }

    function switchTab(tab, btn) {
      currentTab = tab;
      document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      renderList();
    }

    function renderList() {
      const el = document.getElementById('trade-list');
      const trades = cache[currentTab] || [];
      if (!trades.length) {
        const msgs = { incoming: 'No incoming trade requests', outgoing: 'No outgoing trade requests', history: 'No trade history yet' };
        el.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          <p>${msgs[currentTab]}</p></div>`;
        return;
      }
      el.innerHTML = trades.map(t => {
        const isIncoming = currentTab === 'incoming';
        const other = isIncoming ? t.from_username : t.to_username;
        const offer = (t.offer_items || []).slice(0, 3);
        const want = (t.want_items || []).slice(0, 3);
        const statusLabel = t.status === 'pending' ? '\u23F3 Pending' : t.status === 'accepted' ? '\u2713 Accepted' : '\u2717 ' + t.status.charAt(0).toUpperCase() + t.status.slice(1);
        return `<div class="trade-card" onclick="viewDetail(${t.id})">
          <div class="tc-top">
            <div class="tc-av">${(other||'?')[0].toUpperCase()}</div>
            <div style="flex:1;min-width:0">
              <div class="tc-name">${esc(other)}</div>
              <div class="tc-time">${timeAgo(t.created_at)}</div>
            </div>
          </div>
          <div class="tc-items">
            ${offer.map(i => `<span class="tc-item">${emoji(i.type)} ${esc(i.name)}</span>`).join('')}
            ${offer.length && want.length ? '<span style="color:var(--t4);font-size:9px">\u21C4</span>' : ''}
            ${want.map(i => `<span class="tc-item">${emoji(i.type)} ${esc(i.name)}</span>`).join('')}
          </div>
          <div class="tc-status ${t.status}">${statusLabel}</div>
          ${currentTab === 'incoming' ? `<div class="tc-actions" onclick="event.stopPropagation()">
            <button class="btn-accept" onclick="respond(${t.id},'accept')">Accept</button>
            <button class="btn-decline" onclick="respond(${t.id},'decline')">Decline</button>
          </div>` : ''}
          ${currentTab === 'outgoing' ? `<div class="tc-actions" onclick="event.stopPropagation()">
            <button class="btn-decline" onclick="respond(${t.id},'cancel')">Cancel</button>
          </div>` : ''}
        </div>`;
      }).join('');
    }

    function viewDetail(id) {
      const all = [...cache.incoming, ...cache.outgoing, ...cache.history];
      const t = all.find(x => x.id === id);
      if (!t) return;
      const isIncoming = t.to_user_id === user.id;
      const other = isIncoming ? t.from_username : t.to_username;
      const isPending = t.status === 'pending';
      const box = document.getElementById('detail-box');
      box.innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px">
          <div class="tc-av" style="width:44px;height:44px;font-size:17px">${(other||'?')[0].toUpperCase()}</div>
          <div>
            <div style="font-size:17px;font-weight:700">Trade with ${esc(other)}</div>
            <div style="font-size:11px;color:var(--t3)">${timeAgo(t.created_at)} \u00B7 ${isIncoming ? 'They sent this to you' : 'You sent this'}</div>
          </div>
          <button class="modal-close" onclick="closeDetail()" style="margin-left:auto">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="offer-box">
          <div class="offer-label">${isIncoming ? esc(other) + ' offers' : 'You offer'}</div>
          <div class="item-grid">
            ${(t.offer_items||[]).map(i => `<div class="item-box"><div class="item-icon ${i.type}">${emoji(i.type)}</div><div class="item-name">${esc(i.name)}</div><div class="item-type">${i.type}</div></div>`).join('') || '<div style="font-size:12px;color:var(--t4);padding:8px">No items offered</div>'}
          </div>
        </div>
        <div style="text-align:center;padding:4px 0;color:var(--t4)">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        </div>
        <div class="offer-box">
          <div class="offer-label">${isIncoming ? 'They want from you' : 'You want from ' + esc(other)}</div>
          <div class="item-grid">
            ${(t.want_items||[]).map(i => `<div class="item-box"><div class="item-icon ${i.type}">${emoji(i.type)}</div><div class="item-name">${esc(i.name)}</div><div class="item-type">${i.type}</div></div>`).join('') || '<div style="font-size:12px;color:var(--t4);padding:8px">No items requested</div>'}
          </div>
        </div>
        ${isPending && isIncoming ? `<div style="display:flex;gap:8px;margin-top:16px"><button class="btn-accept" onclick="respond(${t.id},'accept');closeDetail()">Accept Trade</button><button class="btn-decline" onclick="respond(${t.id},'decline');closeDetail()">Decline</button></div>` : ''}
        ${isPending && !isIncoming ? `<div style="margin-top:16px"><button class="btn-decline" onclick="respond(${t.id},'cancel');closeDetail()">Cancel Trade</button></div>` : ''}
        ${!isPending ? `<div style="margin-top:14px;padding:10px 14px;border-radius:8px;background:${t.status==='accepted'?'rgba(74,222,128,.08)':'rgba(248,113,113,.08)'};border:1px solid ${t.status==='accepted'?'rgba(74,222,128,.15)':'rgba(248,113,113,.15)'};font-size:12px;font-weight:600;color:${t.status==='accepted'?'var(--green)':'var(--red)'}">
          ${t.status==='accepted'?'\u2713 This trade was accepted':'\u2717 This trade was '+t.status}</div>` : ''}`;
      document.getElementById('detail-overlay').classList.add('open');
    }
    function closeDetail() { document.getElementById('detail-overlay').classList.remove('open'); }

    async function respond(id, action) {
      try {
        const r = await api('respond', { method: 'POST', body: JSON.stringify({ trade_id: id, response: action }) });
        if (r.error) { toast(r.error, 'error'); return; }
        toast(action === 'accept' ? 'Trade accepted!' : action === 'decline' ? 'Trade declined' : 'Trade cancelled');
        await loadTrades();
      } catch (e) { toast('Failed: ' + e.message, 'error'); }
    }

    function openNewTrade() {
      targetUser = null; mySel.clear(); theirSel.clear(); myInv = []; theirInv = [];
      document.getElementById('step-user').style.display = 'block';
      document.getElementById('step-items').style.display = 'none';
      document.getElementById('user-search').value = '';
      document.getElementById('user-results').innerHTML = '';
      document.getElementById('submit-btn').disabled = true;
      document.getElementById('new-trade-modal').classList.add('open');
    }
    function closeNewTrade() { document.getElementById('new-trade-modal').classList.remove('open'); }

    let searchTimer;
    function searchUser(q) {
      clearTimeout(searchTimer);
      q = q.trim();
      if (q.length < 2) { document.getElementById('user-results').innerHTML = ''; return; }
      searchTimer = setTimeout(async () => {
        try {
          const r = await api('search', { params: '&q=' + encodeURIComponent(q) });
          const users = (r.users || []).filter(u => u.id !== user.id);
          const el = document.getElementById('user-results');
          if (!users.length) { el.innerHTML = '<div style="padding:10px;font-size:11px;color:var(--t4)">No players found</div>'; return; }
          el.innerHTML = users.slice(0, 8).map(u => `
            <div class="user-result" onclick="selectUser(${u.id},'${esc(u.username).replace(/'/g,"\\'")}')">
              <div class="tc-av">${u.username[0].toUpperCase()}</div>
              <div><div style="font-size:12px;font-weight:600;color:var(--t1)">${esc(u.username)}</div><div style="font-size:10px;color:var(--t3)">ID: ${u.id}</div></div>
            </div>`).join('');
        } catch { document.getElementById('user-results').innerHTML = '<div style="padding:10px;font-size:11px;color:var(--t4)">Search failed</div>'; }
      }, 350);
    }

    async function selectUser(id, name) {
      targetUser = { id, username: name };
      document.getElementById('step-user').style.display = 'none';
      document.getElementById('step-items').style.display = 'block';
      document.getElementById('sel-name').textContent = name;
      document.getElementById('sel-av').textContent = name[0].toUpperCase();
      try {
        const [a, b] = await Promise.all([
          api('my-inventory'),
          api('their-inventory', { params: '&userId=' + id }),
        ]);
        myInv = a.items || [];
        theirInv = b.items || [];
      } catch { myInv = []; theirInv = []; }
      renderInvs();
    }
    function backToSearch() {
      targetUser = null; mySel.clear(); theirSel.clear();
      document.getElementById('step-user').style.display = 'block';
      document.getElementById('step-items').style.display = 'none';
      updateSubmit();
    }

    function renderInvs() {
      const mg = document.getElementById('my-items');
      const tg = document.getElementById('their-items');
      if (!myInv.length) { mg.innerHTML = ''; document.getElementById('my-empty').style.display = 'block'; }
      else { document.getElementById('my-empty').style.display = 'none'; mg.innerHTML = myInv.map(i => `<div class="inv-item${mySel.has(i.id)?' selected':''}" onclick="toggleItem('my',${i.id})"><div class="ii-icon">${emoji(i.type)}</div><div class="ii-name">${esc(i.name)}</div></div>`).join(''); }
      if (!theirInv.length) { tg.innerHTML = ''; document.getElementById('their-empty').style.display = 'block'; }
      else { document.getElementById('their-empty').style.display = 'none'; tg.innerHTML = theirInv.map(i => `<div class="inv-item${theirSel.has(i.id)?' selected':''}" onclick="toggleItem('their',${i.id})"><div class="ii-icon">${emoji(i.type)}</div><div class="ii-name">${esc(i.name)}</div></div>`).join(''); }
    }
    function toggleItem(side, id) {
      const s = side === 'my' ? mySel : theirSel;
      s.has(id) ? s.delete(id) : s.add(id);
      renderInvs(); updateSubmit();
    }
    function updateSubmit() {
      const ok = targetUser && (mySel.size > 0 || theirSel.size > 0);
      document.getElementById('submit-btn').disabled = !ok;
    }

    async function submitTrade() {
      if (!targetUser) return;
      const offer = [...mySel].map(id => myInv.find(i => i.id === id)).filter(Boolean);
      const want = [...theirSel].map(id => theirInv.find(i => i.id === id)).filter(Boolean);
      if (!offer.length && !want.length) { toast('Select at least one item', 'error'); return; }
      try {
        const r = await api('create', {
          method: 'POST',
          body: JSON.stringify({
            to_user_id: targetUser.id,
            offer_items: offer.map(i => ({ id: i.id, name: i.name, type: i.type })),
            want_items: want.map(i => ({ id: i.id, name: i.name, type: i.type })),
          }),
        });
        if (r.error) { toast(r.error, 'error'); return; }
        toast('Trade request sent to ' + targetUser.username + '!');
        closeNewTrade();
        switchTab('outgoing', document.querySelectorAll('.tab')[1]);
        await loadTrades();
      } catch (e) { toast('Failed: ' + e.message, 'error'); }
    }

    (function() {
      const c = document.getElementById('bg-canvas');
      if (!c) return;
      const ctx = c.getContext('2d');
      let W, H;
      const COLORS = ['#7c3aed','#6d28d9','#a78bfa','#4c1d95','#8b5cf6'];
      const pts = [];
      function resize() { W = c.width = window.innerWidth; H = c.height = window.innerHeight; }
      function spawn() { return { x: Math.random()*W, y: Math.random()*H, r: Math.random()*1.6+.4, dx: (Math.random()-.5)*.3, dy: (Math.random()-.5)*.3, a: Math.random()*.5+.1, color: COLORS[Math.floor(Math.random()*COLORS.length)] }; }
      function frame() {
        ctx.clearRect(0,0,W,H);
        pts.forEach(p => { p.x+=p.dx;p.y+=p.dy; if(p.x<0)p.x=W;if(p.x>W)p.x=0;if(p.y<0)p.y=H;if(p.y>H)p.y=0; ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=p.color;ctx.globalAlpha=p.a;ctx.fill();ctx.globalAlpha=1; });
        requestAnimationFrame(frame);
      }
      resize(); window.addEventListener('resize', resize);
      for (let i = 0; i < 30; i++) pts.push(spawn());
      frame();
    })();

    restoreSession();
  </script>
</body>
</html>
