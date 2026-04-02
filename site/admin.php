<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#05030d;--s1:#0a0818;--s2:#0e0b20;--s3:#131028;
  --line:rgba(255,255,255,.07);--line2:rgba(139,92,246,.22);
  --p:#7c3aed;--p2:#6d28d9;--pl:#a78bfa;--px:#c4b5fd;
  --red:#f87171;--green:#4ade80;--amber:#fbbf24;--teal:#2dd4bf;
  --w:#f5f3ff;--w2:rgba(245,243,255,.55);--w3:rgba(245,243,255,.25);--w4:rgba(245,243,255,.08);
  --f:'Inter',system-ui,sans-serif;--mono:'JetBrains Mono',monospace;
}
html,body{height:100%;font-family:var(--f);background:var(--bg);color:var(--w);-webkit-font-smoothing:antialiased}

.topbar{
  height:52px;display:flex;align-items:center;padding:0 24px;gap:12px;
  background:rgba(5,3,13,.9);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--line);position:fixed;top:0;left:0;right:0;z-index:100;
}
.topbar-logo{display:flex;align-items:center;gap:9px;text-decoration:none;font-weight:800;font-size:14px;color:var(--w);letter-spacing:-.03em}
.logo-sq{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;box-shadow:0 0 14px rgba(124,58,237,.5)}
.admin-badge{
  padding:2px 8px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
  background:rgba(248,113,113,.15);color:var(--red);border:1px solid rgba(248,113,113,.25);
}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.topbar-user{font-size:13px;font-weight:600;color:var(--w2)}
.topbar-av{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.btn-logout{padding:5px 12px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:all .12s}
.btn-logout:hover{border-color:var(--red);color:var(--red)}
.btn-back{padding:5px 12px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:5px;transition:all .12s}
.btn-back:hover{border-color:var(--line2);color:var(--pl)}

.page{padding-top:52px;min-height:100vh;display:flex;flex-direction:column}
.inner{max-width:1200px;margin:0 auto;padding:32px 24px;width:100%}

.page-hdr{margin-bottom:28px}
.page-title{font-size:24px;font-weight:800;letter-spacing:-.04em;color:var(--w)}
.page-sub{font-size:13px;color:var(--w3);margin-top:4px}

.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:32px}
.stat-card{
  background:var(--s2);border:1px solid var(--line);border-radius:12px;padding:16px 18px;
}
.stat-label{font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--w3);margin-bottom:8px}
.stat-val{font-size:26px;font-weight:800;letter-spacing:-.04em;color:var(--w)}
.stat-val.green{color:var(--green)}
.stat-val.amber{color:var(--amber)}
.stat-val.teal{color:var(--teal)}
.stat-val.red{color:var(--red)}

.toolbar{display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.search-box{
  display:flex;align-items:center;gap:8px;padding:8px 13px;
  background:var(--s2);border:1px solid var(--line);border-radius:9px;
  transition:border .13s;flex:1;max-width:380px;
}
.search-box:focus-within{border-color:var(--line2)}
.search-box svg{color:var(--w3);flex-shrink:0}
.search-box input{border:none;background:transparent;color:var(--w);font-family:var(--f);font-size:13px;outline:none;width:100%}
.search-box input::placeholder{color:var(--w3)}
.toolbar-count{font-size:12.5px;color:var(--w3);margin-left:auto}

.table-wrap{background:var(--s2);border:1px solid var(--line);border-radius:13px;overflow:hidden}
.tbl{width:100%;border-collapse:collapse}
.tbl th{
  font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  color:var(--w3);padding:11px 16px;text-align:left;
  border-bottom:1px solid var(--line);background:var(--s1);white-space:nowrap;
}
.tbl td{
  font-size:12.5px;padding:11px 16px;border-bottom:1px solid var(--line);
  color:var(--w2);vertical-align:middle;max-width:200px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:var(--w4)}
.tbl tr{cursor:pointer}
.mono{font-family:var(--mono);font-size:11.5px;color:var(--pl)}
.tag{display:inline-flex;align-items:center;padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;letter-spacing:.04em}
.tag-admin{background:rgba(248,113,113,.12);color:var(--red);border:1px solid rgba(248,113,113,.2)}
.tag-media{background:rgba(192,132,252,.15);color:#c084fc;border:1px solid rgba(192,132,252,.3)}
.tag-user{background:var(--w4);color:var(--w3)}
.tag-online{background:rgba(74,222,128,.12);color:var(--green)}
.tag-ingame{background:rgba(251,191,36,.12);color:var(--amber)}
.tag-offline{background:var(--w4);color:var(--w3)}
.empty-row td{text-align:center;padding:48px;color:var(--w3);font-size:13px}
.role-btn{padding:6px 16px;border-radius:7px;border:1px solid color-mix(in srgb,var(--rc) 30%,transparent);background:transparent;color:var(--rc);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer;transition:all .2s}
.role-btn:hover{background:var(--rbg)}
.role-btn-active{background:var(--rbg);border-color:var(--rc);box-shadow:0 0 8px color-mix(in srgb,var(--rc) 20%,transparent)}

.ov{
  position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(6px);
  z-index:200;display:flex;align-items:center;justify-content:center;padding:20px;
  opacity:0;pointer-events:none;transition:opacity .18s;
}
.ov.on{opacity:1;pointer-events:all}
.modal{
  background:var(--s1);border:1px solid var(--line2);border-radius:16px;
  width:100%;max-width:620px;max-height:90vh;overflow-y:auto;
  box-shadow:0 24px 80px rgba(0,0,0,.6);
}
.modal::-webkit-scrollbar{width:3px}.modal::-webkit-scrollbar-thumb{background:var(--line2)}
.modal-hdr{
  padding:22px 24px 18px;border-bottom:1px solid var(--line);
  display:flex;align-items:center;gap:12px;
}
.modal-av{
  width:44px;height:44px;border-radius:10px;flex-shrink:0;
  background:linear-gradient(135deg,var(--p),var(--p2));
  display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;
}
.modal-title{font-size:17px;font-weight:800;letter-spacing:-.03em}
.modal-sub{font-size:12px;color:var(--w3);margin-top:2px}
.modal-close{margin-left:auto;width:30px;height:30px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s}
.modal-close:hover{border-color:var(--red);color:var(--red)}
.modal-body{padding:22px 24px}

.field-group{margin-bottom:20px}
.field-group-label{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--w3);margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--line)}
.field-row{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.field-row:last-child{border-bottom:none}
.field-label{font-size:11.5px;font-weight:600;color:var(--w3);min-width:140px;flex-shrink:0;padding-top:1px}
.field-val{font-size:12.5px;color:var(--w);word-break:break-all;flex:1}
.field-val.mono{font-family:var(--mono);font-size:11px;color:var(--pl)}
.field-val.muted{color:var(--w3)}
.copy-btn{
  padding:2px 8px;border-radius:5px;border:1px solid var(--line);
  background:transparent;color:var(--w3);font-family:var(--mono);font-size:10px;
  cursor:pointer;transition:all .12s;flex-shrink:0;white-space:nowrap;
}
.copy-btn:hover{border-color:var(--line2);color:var(--pl)}
.copy-btn.copied{border-color:var(--green);color:var(--green)}

.toast{
  position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(12px);
  background:var(--s3);border:1px solid var(--line2);border-radius:9px;
  padding:10px 18px;font-size:13px;font-weight:500;color:var(--w);
  opacity:0;transition:all .22s;pointer-events:none;z-index:999;white-space:nowrap;
}

.login-wall{
  min-height:100vh;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;
  text-align:center;padding:24px;
}
.login-wall h1{font-size:22px;font-weight:800;letter-spacing:-.04em}
.login-wall p{font-size:13px;color:var(--w3);max-width:320px;line-height:1.6}
.err-msg{font-size:13px;color:var(--red);display:none}
.lw-form{display:flex;flex-direction:column;gap:10px;width:100%;max-width:300px}
.lw-input{
  padding:9px 13px;border-radius:8px;border:1px solid var(--line);
  background:var(--s2);color:var(--w);font-family:var(--f);font-size:13px;outline:none;
  transition:border .13s;
}
.lw-input:focus{border-color:var(--line2)}
.lw-btn{
  padding:10px;border-radius:8px;border:none;background:var(--p);color:#fff;
  font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer;transition:background .13s;
}
.lw-btn:hover{background:var(--p2)}
.lw-btn:disabled{opacity:.5;cursor:not-allowed}

.spinner{display:inline-block;width:18px;height:18px;border:2px solid var(--line2);border-top-color:var(--pl);border-radius:50%;animation:spin .6s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.loading-row td{text-align:center;padding:40px}

.pagination{display:flex;align-items:center;gap:6px;margin-top:14px;justify-content:center}
.page-btn{padding:5px 11px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:all .12s}
.page-btn:hover:not(:disabled){border-color:var(--line2);color:var(--pl)}
.page-btn:disabled{opacity:.35;cursor:not-allowed}
.page-btn.active{background:rgba(139,92,246,.15);border-color:var(--line2);color:var(--pl)}
</style>
</head>
<body>

<div id="login-wall" class="login-wall" style="display:none">
  <div style="width:44px;height:44px;border-radius:11px;background:var(--p);display:flex;align-items:center;justify-content:center;box-shadow:0 0 20px rgba(124,58,237,.5);margin-bottom:4px">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  </div>
  <h1>Admin Access Only</h1>
  <p>Sign in with an admin account to access the Justice admin panel.</p>
  <div class="lw-form">
    <div class="err-msg" id="lw-err"></div>
    <input class="lw-input" id="lw-l" type="text" placeholder="Username or email" autocomplete="username">
    <input class="lw-input" id="lw-p" type="password" placeholder="Password" autocomplete="current-password" onkeydown="if(event.key==='Enter')lwLogin()">
    <button class="lw-btn" id="lw-btn" onclick="lwLogin()">Log In</button>
  </div>
  <a href="/" style="font-size:12px;color:var(--w3);text-decoration:none;margin-top:4px">← Back to Justice</a>
</div>

<div class="topbar" id="topbar" style="display:none">
  <a href="/" class="topbar-logo">
    <div class="logo-sq"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg></div>
    Justice
  </a>
  <span class="admin-badge">Admin Panel</span>
  <div class="topbar-right">
    <div class="topbar-user" id="tb-uname"></div>
    <div class="topbar-av" id="tb-av">?</div>
    <a href="/social.php" class="btn-back">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      Social
    </a>
    <button class="btn-logout" onclick="doLogout()">Sign out</button>
  </div>
</div>

<div class="page" id="main-page" style="display:none">
  <div class="inner">
    <div class="page-hdr" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
      <div>
        <div class="page-title">User Management</div>
        <div class="page-sub">All registered Justice accounts. Manage roles, bans, and badges.</div>
      </div>
      <button onclick="openBroadcastPanel()" style="padding:8px 16px;border-radius:8px;border:none;background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:#f87171;font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;transition:all .13s" onmouseover="this.style.background='rgba(248,113,113,.25)'" onmouseout="this.style.background='rgba(248,113,113,.15)'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
        Send Broadcast
      </button>
    </div>

    
    <div id="broadcast-panel" style="display:none;background:var(--s2);border:1px solid rgba(248,113,113,.25);border-radius:13px;padding:20px;margin-bottom:20px">
      <div style="font-size:13px;font-weight:700;color:#f87171;margin-bottom:14px;display:flex;align-items:center;gap:7px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
        Broadcast Message — shows as a popup in every active launcher
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px">
        <input id="bc-msg" type="text" maxlength="200" placeholder="Message text (max 200 chars)…"
          style="flex:1;min-width:260px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
        <select id="bc-type" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;cursor:pointer">
          <option value="info">ℹ️ Info</option>
          <option value="success">✅ Success</option>
          <option value="warning">⚠️ Warning</option>
          <option value="error">🚨 Alert</option>
        </select>
        <select id="bc-ttl" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;cursor:pointer">
          <option value="30">30 seconds</option>
          <option value="60">1 minute</option>
          <option value="300">5 minutes</option>
          <option value="3600">1 hour</option>
        </select>
        <button onclick="sendBroadcast()" style="padding:9px 18px;border-radius:8px;border:none;background:#f87171;color:#fff;font-family:'Inter',sans-serif;font-size:13px;font-weight:700;cursor:pointer">Send</button>
        <button onclick="closeBroadcastPanel()" style="padding:9px 14px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:13px;cursor:pointer">Cancel</button>
      </div>
      <div id="bc-active" style="font-size:12px;color:var(--w3)">Loading active broadcasts…</div>
    </div>

    
    <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap">
      <button class="admin-tab on" id="atab-users" onclick="switchAdminTab('users')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line2);background:rgba(124,58,237,.15);color:#a78bfa;font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">👥 Users</button>
      <button class="admin-tab" id="atab-news" onclick="switchAdminTab('news')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">📰 News</button>
      <button class="admin-tab" id="atab-leaderboard" onclick="switchAdminTab('leaderboard')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">🏆 Leaderboard</button>
      <button class="admin-tab" id="atab-announce" onclick="switchAdminTab('announce')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">📣 Announcements</button>
      <button class="admin-tab" id="atab-servers" onclick="switchAdminTab('servers')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">🌐 Servers</button>
      <button class="admin-tab" id="atab-splash" onclick="switchAdminTab('splash')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">💬 Splash</button>
      <button class="admin-tab" id="atab-events" onclick="switchAdminTab('events')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">📅 Events</button>
      <button class="admin-tab" id="atab-patches" onclick="switchAdminTab('patches')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">📝 Patch Notes</button>
      <button class="admin-tab" id="atab-withdrawals" onclick="switchAdminTab('withdrawals')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">💰 Withdrawals</button>
      <button class="admin-tab" id="atab-cosmetics" onclick="switchAdminTab('cosmetics')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">✨ Cosmetics</button>
      <button class="admin-tab" id="atab-marketplace" onclick="switchAdminTab('marketplace')" style="padding:7px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;font-weight:600;cursor:pointer">🏪 Marketplace</button>
    </div>

    
    <div id="panel-news" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Create News Post</div>
        <input id="news-title" type="text" placeholder="Post title…" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;margin-bottom:10px">
        <textarea id="news-body" placeholder="Post body… (plain text or HTML)" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;min-height:120px;resize:vertical;margin-bottom:10px"></textarea>
        <div style="display:flex;gap:10px;align-items:center">
          <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--w3);cursor:pointer"><input type="checkbox" id="news-published" checked> Published</label>
          <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--w3);cursor:pointer"><input type="checkbox" id="news-pinned"> Pinned</label>
          <button onclick="createNewsPost()" style="padding:8px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">Publish Post</button>
        </div>
      </div>
      <div id="news-list-admin" style="font-size:12px;color:var(--w3)">Loading posts…</div>
    </div>

    
    <div id="panel-leaderboard" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
          <div style="font-size:13px;font-weight:700;color:var(--w)">Player Leaderboard</div>
          <select id="lb-key" onchange="loadLeaderboard()" style="padding:6px 10px;border-radius:7px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
            <option value="playtime">Playtime</option>
            <option value="kills">Kills</option>
            <option value="deaths">Deaths</option>
            <option value="blocks_placed">Blocks Placed</option>
          </select>
          <button onclick="loadLeaderboard()" style="padding:6px 12px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">Refresh</button>
        </div>
        <div id="lb-list">Loading…</div>
      </div>
    </div>

    
    <div id="panel-announce" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Schedule Announcement</div>
        <input id="ann-title" type="text" placeholder="Title…" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;margin-bottom:8px">
        <textarea id="ann-body" placeholder="Message body…" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;min-height:80px;resize:vertical;margin-bottom:8px"></textarea>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px">
          <select id="ann-type" style="padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
            <option value="info">ℹ️ Info</option><option value="success">✅ Success</option><option value="warning">⚠️ Warning</option><option value="error">🚨 Alert</option>
          </select>
          <select id="ann-target" style="padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
            <option value="all">All Users</option><option value="plus">Plus Members Only</option>
          </select>
          <input id="ann-time" type="datetime-local" style="padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
          <button onclick="scheduleAnnouncement()" style="padding:8px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">Schedule</button>
        </div>
        <div style="font-size:11px;color:var(--w3);margin-top:4px">Note: Scheduled announcements require a cron job running <code style="color:var(--pl)">php /path/to/cron_announcements.php</code> every minute.</div>
      </div>
    </div>

    
    <div id="panel-servers" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Add Server</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:8px">
          <input id="srv-name" placeholder="Server name" style="flex:1;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
          <input id="srv-addr" placeholder="IP / domain" style="flex:1;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
          <input id="srv-port" placeholder="Port" value="25565" style="width:80px;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <input id="srv-desc" placeholder="Description (optional)" style="flex:1;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none">
          <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--w3);cursor:pointer"><input type="checkbox" id="srv-featured"> Featured</label>
          <button onclick="adminSaveServer()" style="padding:8px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">Add Server</button>
        </div>
      </div>
      <div id="srv-list-admin" style="font-size:12px;color:var(--w3)">Loading servers…</div>
    </div>


    <div id="panel-splash" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Add Splash Text</div>
        <div style="display:flex;gap:10px">
          <input id="splash-input" type="text" placeholder="Your splash text (max 200 chars)…" maxlength="200" style="flex:1;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          <button onclick="adminAddSplash()" style="padding:9px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">Add</button>
        </div>
      </div>
      <div id="splash-list" style="font-size:12px;color:var(--w3)">Loading…</div>
    </div>

    <div id="panel-events" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Create Event</div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <input id="ev-title" type="text" placeholder="Event title…" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          <textarea id="ev-desc" placeholder="Description (optional)…" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;resize:vertical;min-height:70px"></textarea>
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div style="flex:1;min-width:180px"><div style="font-size:11px;color:var(--w3);margin-bottom:4px">Starts At</div><input id="ev-start" type="datetime-local" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none"></div>
            <div style="flex:1;min-width:180px"><div style="font-size:11px;color:var(--w3);margin-bottom:4px">Ends At (optional)</div><input id="ev-end" type="datetime-local" style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none"></div>
            <div style="display:flex;align-items:flex-end;gap:8px">
              <div><div style="font-size:11px;color:var(--w3);margin-bottom:4px">Color</div><input id="ev-color" type="color" value="#7c3aed" style="width:44px;height:36px;border-radius:7px;border:1px solid var(--line2);background:var(--s3);cursor:pointer;padding:2px"></div>
              <button onclick="adminCreateEvent()" style="padding:8px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer;height:36px">Create</button>
            </div>
          </div>
        </div>
      </div>
      <div id="events-admin-list" style="font-size:12px;color:var(--w3)">Loading…</div>
    </div>

    <div id="panel-patches" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px">Publish Patch Notes</div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <div style="display:flex;gap:10px">
            <input id="patch-ver" type="text" placeholder="Version (e.g. 1.0.1)" style="width:150px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
            <input id="patch-title-in" type="text" placeholder="Release title…" style="flex:1;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          </div>
          <textarea id="patch-body-in" placeholder="What changed? Use bullet points, plain text…" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;resize:vertical;min-height:120px"></textarea>
          <div style="font-size:11px;color:var(--w3)">Users will see a popup the first time they open the launcher after a version change. Update <code style="color:#a78bfa">CURRENT_VERSION</code> in main.js to match.</div>
          <button onclick="adminSavePatch()" style="padding:9px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer;align-self:flex-start">Publish</button>
        </div>
      </div>
      <div id="patches-list" style="font-size:12px;color:var(--w3)">Loading…</div>
    </div>


    <div id="panel-withdrawals" style="display:none;margin-bottom:20px">
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
          <div style="font-size:13px;font-weight:700;color:var(--w)">Point Withdrawal Requests</div>
          <div style="display:flex;gap:6px">
            <button onclick="loadWithdrawals('pending')" id="wf-pending" style="padding:5px 13px;border-radius:7px;border:1px solid rgba(124,58,237,.4);background:rgba(124,58,237,.15);color:#a78bfa;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Pending</button>
            <button onclick="loadWithdrawals('completed')" id="wf-completed" style="padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Completed</button>
            <button onclick="loadWithdrawals('cancelled')" id="wf-cancelled" style="padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Cancelled</button>
            <button onclick="loadWithdrawals('all')" id="wf-all" style="padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">All</button>
          </div>
        </div>
        <div style="font-size:11px;color:var(--w3);padding:8px 10px;background:var(--s3);border-radius:7px;margin-bottom:14px">
          💡 <strong style="color:var(--w)">1 point = 1,000,000 DonutSMP coins.</strong> When marking complete, give the player the coins in-game first, then mark it done here.
        </div>
        <div id="withdrawals-list">Loading…</div>
      </div>
    </div>

    <!-- ═══ COSMETICS PANEL ═══ -->
    <div id="panel-cosmetics" style="display:none;margin-bottom:20px">
      <!-- Create cosmetic form -->
      <div style="background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:14px;display:flex;align-items:center;gap:7px">
          ✨ Create Cosmetic
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
          <input id="cos-name" type="text" placeholder="Cosmetic name…" style="flex:2;min-width:200px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          <select id="cos-type" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;cursor:pointer">
            <option value="cape">Cape</option>
            <option value="hat">Hat</option>
            <option value="wings">Wings</option>
            <option value="bandana">Bandana</option>
            <option value="aura">Aura</option>
            <option value="emoji">Emoji</option>
          </select>
          <select id="cos-rarity" style="padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;cursor:pointer">
            <option value="common">Common</option>
            <option value="uncommon">Uncommon</option>
            <option value="rare">Rare</option>
            <option value="epic">Epic</option>
            <option value="legendary">Legendary</option>
          </select>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px">
          <input id="cos-desc" type="text" placeholder="Description (optional)…" style="flex:1;min-width:200px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--w3);cursor:pointer;padding:0 8px">
            <input type="checkbox" id="cos-plus"> Plus Only
          </label>
          <input id="cos-price" type="number" min="0" placeholder="Price (pts, blank = not for sale)" style="width:200px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
        </div>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;border:1px dashed var(--line2);background:var(--s3);color:var(--w3);font-size:12px;cursor:pointer;transition:all .13s" onmouseover="this.style.borderColor='var(--pl)'" onmouseout="this.style.borderColor='var(--line2)'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span id="cos-file-label">Upload Texture (PNG)</span>
            <input type="file" id="cos-texture" accept=".png,.jpg,.jpeg" style="display:none" onchange="document.getElementById('cos-file-label').textContent=this.files[0]?.name||'Upload Texture (PNG)'">
          </label>
          <div id="cos-preview" style="display:none">
            <img id="cos-preview-img" style="max-height:48px;border-radius:6px;border:1px solid var(--line)">
          </div>
          <label style="display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;border:1px dashed rgba(59,130,246,.3);background:var(--s3);color:var(--w3);font-size:12px;cursor:pointer;transition:all .13s" onmouseover="this.style.borderColor='#60a5fa'" onmouseout="this.style.borderColor='rgba(59,130,246,.3)'">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            <span id="cos-prev-label">Preview Image (optional)</span>
            <input type="file" id="cos-preview-file" accept=".png,.jpg,.jpeg" style="display:none" onchange="document.getElementById('cos-prev-label').textContent=this.files[0]?.name||'Preview Image (optional)'">
          </label>
          <button onclick="adminCreateCosmetic()" style="padding:9px 18px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer;margin-left:auto">Create Cosmetic</button>
        </div>
      </div>

      <!-- Grant cosmetic to user -->
      <div style="background:var(--s2);border:1px solid rgba(74,222,128,.2);border-radius:13px;padding:20px;margin-bottom:12px">
        <div style="font-size:13px;font-weight:700;color:var(--green);margin-bottom:14px">🎁 Grant Cosmetic to User</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <input id="grant-username" type="text" placeholder="Username…" style="flex:1;min-width:160px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none">
          <select id="grant-cosmetic" style="flex:2;min-width:200px;padding:9px 13px;border-radius:8px;border:1px solid var(--line2);background:var(--s3);color:var(--w);font-family:'Inter',sans-serif;font-size:13px;outline:none;cursor:pointer">
            <option value="">— Select cosmetic —</option>
          </select>
          <button onclick="adminGrantCosmetic()" style="padding:9px 18px;border-radius:8px;border:none;background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.3);color:#4ade80;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">Grant</button>
        </div>
      </div>

      <!-- Cosmetics catalog list -->
      <div style="font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--w3);margin-bottom:10px">All Cosmetics</div>
      <div id="cos-catalog" style="font-size:12px;color:var(--w3)">Loading cosmetics…</div>
    </div>

    <div id="panel-marketplace" style="display:none;margin-bottom:20px">
      <div style="display:flex;gap:6px;margin-bottom:16px">
        <button onclick="loadMarketplacePending()" id="mp-f-pending" style="padding:5px 13px;border-radius:7px;border:1px solid rgba(124,58,237,.4);background:rgba(124,58,237,.15);color:#a78bfa;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Pending</button>
        <button onclick="loadMarketplaceHistory()" id="mp-f-history" style="padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Review History</button>
      </div>
      <div id="mp-list" style="font-size:12px;color:var(--w3)">Loading…</div>
    </div>

    <div id="panel-users">
    <div class="stats-grid" id="stats-grid">
      <div class="stat-card"><div class="stat-label">Total Users</div><div class="stat-val" id="st-total">—</div></div>
      <div class="stat-card"><div class="stat-label">Online Now</div><div class="stat-val green" id="st-online">—</div></div>
      <div class="stat-card"><div class="stat-label">In-Game</div><div class="stat-val amber" id="st-ingame">—</div></div>
      <div class="stat-card"><div class="stat-label">Joined Today</div><div class="stat-val teal" id="st-today">—</div></div>
      <div class="stat-card"><div class="stat-label">Total Messages</div><div class="stat-val" id="st-msgs">—</div></div>
      <div class="stat-card"><div class="stat-label">Friendships</div><div class="stat-val" id="st-friends">—</div></div>
    </div>

    
    <div class="toolbar">
      <div class="search-box">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="search-inp" placeholder="Search by username, email, IP, or Minecraft name…" oninput="onSearch()">
      </div>
      <div class="toolbar-count" id="toolbar-count"></div>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>MC Username</th>
            <th>Email</th>
            <th>Last IP</th>
            <th>UUID</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody id="users-tbody"></tbody>
      </table>
    </div>
    <div class="pagination" id="pagination"></div>
    </div>
  </div>
</div>

<div class="ov" id="ov-user">
  <div class="modal" onclick="event.stopPropagation()">
    <div class="modal-hdr">
      <div class="modal-av" id="m-av">?</div>
      <div>
        <div class="modal-title" id="m-title">—</div>
        <div class="modal-sub" id="m-sub">—</div>
      </div>
      <button class="modal-close" onclick="closeModal()">×</button>
    </div>
    <div class="modal-body" id="m-body"></div>
  </div>
</div>

<div id="toast"></div>

<script>
const API = '';
let token = localStorage.getItem('jl_token') || null;
let me = null;
let allUsers = [];
let totalUsers = 0;
let currentPage = 0;
const PAGE_SIZE = 50;
let searchTimer = null;

async function init() {
  if (!token) { showWall(); return; }
  try {
    const r = await api('/api/admin.php?action=stats');
    if (r.total !== undefined) {
      const u = await api('/api/user.php?action=me');
      me = u.user || { username: 'Admin' };
      showApp();
    } else if (r.error === 'Forbidden: admin only') {
      showWall('Your account does not have admin access.');
    } else {
      token = null; localStorage.removeItem('jl_token'); showWall();
    }
  } catch { showWall(); }
}

function showWall(msg) {
  document.getElementById('login-wall').style.display = 'flex';
  if (msg) {
    const e = document.getElementById('lw-err');
    e.textContent = msg; e.style.display = 'block';
  }
}

async function lwLogin() {
  const l = document.getElementById('lw-l').value.trim();
  const p = document.getElementById('lw-p').value;
  const err = document.getElementById('lw-err');
  const btn = document.getElementById('lw-btn');
  err.style.display = 'none';
  if (!l || !p) { err.textContent = 'Fill in all fields'; err.style.display = 'block'; return; }
  btn.disabled = true; btn.textContent = 'Logging in…';
  try {
    const r = await fetch(API + '/api/login.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({login: l, password: p})
    });
    const d = await r.json();
    if (d.error) { err.textContent = d.error; err.style.display = 'block'; btn.disabled = false; btn.textContent = 'Log In'; return; }

    token = d.token;
    const check = await fetch(API + '/api/admin.php?action=stats', {
      headers: {'Content-Type':'application/json', Authorization: 'Bearer ' + token}
    });
    const chk = await check.json();

    if (chk.total !== undefined) {
      localStorage.setItem('jl_token', token);
      me = d.user || { username: l };
      document.getElementById('login-wall').style.display = 'none';
      showApp();
    } else {
      token = null;
      err.textContent = 'This account does not have admin access.';
      err.style.display = 'block';
    }
  } catch { err.textContent = 'Could not connect'; err.style.display = 'block'; token = null; }
  btn.disabled = false; btn.textContent = 'Log In';
}

function doLogout() {
  token = null; localStorage.removeItem('jl_token'); location.reload();
}

function showApp() {
  document.getElementById('topbar').style.display = 'flex';
  document.getElementById('main-page').style.display = 'flex';
  document.getElementById('login-wall').style.display = 'none';
  document.getElementById('tb-av').textContent = (me.username||'?')[0].toUpperCase();
  document.getElementById('tb-uname').textContent = me.username;
  loadStats();
  loadUsers();
}

async function loadStats() {
  try {
    const r = await api('/api/admin.php?action=stats');
    document.getElementById('st-total').textContent  = r.total?.toLocaleString() ?? '—';
    document.getElementById('st-online').textContent = r.online?.toLocaleString() ?? '—';
    document.getElementById('st-ingame').textContent = r.ingame?.toLocaleString() ?? '—';
    document.getElementById('st-today').textContent  = r.today?.toLocaleString() ?? '—';
    document.getElementById('st-msgs').textContent   = r.messages?.toLocaleString() ?? '—';
    document.getElementById('st-friends').textContent= r.friends?.toLocaleString() ?? '—';
  } catch {}
}

async function loadUsers(page = 0) {
  currentPage = page;
  const q = document.getElementById('search-inp').value.trim();
  const tbody = document.getElementById('users-tbody');
  tbody.innerHTML = '<tr class="loading-row"><td colspan="9"><div class="spinner"></div></td></tr>';

  try {
    const params = new URLSearchParams({
      action: 'users', limit: PAGE_SIZE, offset: page * PAGE_SIZE,
      ...(q ? { q } : {})
    });
    const r = await api('/api/admin.php?' + params);
    allUsers = r.users || [];
    totalUsers = r.total || 0;
    renderTable();
    renderPagination();
    document.getElementById('toolbar-count').textContent =
      totalUsers.toLocaleString() + ' user' + (totalUsers !== 1 ? 's' : '');
  } catch {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="9">Failed to load users</td></tr>';
  }
}

function renderTable() {
  const tbody = document.getElementById('users-tbody');
  if (!allUsers.length) {
    tbody.innerHTML = '<tr class="empty-row"><td colspan="9">No users found</td></tr>';
    return;
  }
  tbody.innerHTML = allUsers.map(u => {
    const statusTag = u.status === 'in-game'
      ? `<span class="tag tag-ingame">In-Game</span>`
      : u.status === 'online' || u.status === 'away'
      ? `<span class="tag tag-online">${esc(u.status)}</span>`
      : `<span class="tag tag-offline">Offline</span>`;
    const roleTag = u.role === 'admin'
      ? `<span class="tag tag-admin">Admin</span>`
      : u.role === 'staff' ? `<span class="tag tag-admin">Staff</span>`
      : u.role === 'media' ? `<span class="tag tag-media">Media</span>`
      : `<span class="tag tag-user">User</span>`;
    return `<tr onclick="openUser(${u.id})">
      <td class="mono">#${u.id}</td>
      <td style="color:var(--w);font-weight:600">${esc(u.username)}</td>
      <td>${roleTag}</td>
      <td>${statusTag}</td>
      <td class="mono">${u.mcUsername ? esc(u.mcUsername) : '<span style="color:var(--w3)">—</span>'}</td>
      <td>${u.email ? esc(u.email) : '<span style="color:var(--w3)">—</span>'}</td>
      <td class="mono" style="font-size:11px">${u.lastIp ? esc(u.lastIp) : '<span style="color:var(--w3)">—</span>'}</td>
      <td class="mono" style="font-size:10.5px;color:var(--w3)">${u.uuid ? esc(u.uuid.split('-')[0]) + '…' : '—'}</td>
      <td style="color:var(--w3)">${u.createdAt ? new Date(u.createdAt).toLocaleDateString() : '—'}</td>
    </tr>`;
  }).join('');
}

function renderPagination() {
  const totalPages = Math.ceil(totalUsers / PAGE_SIZE);
  const pg = document.getElementById('pagination');
  if (totalPages <= 1) { pg.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="loadUsers(${currentPage-1})" ${currentPage===0?'disabled':''}>← Prev</button>`;
  const start = Math.max(0, currentPage-2);
  const end   = Math.min(totalPages-1, currentPage+2);
  for (let i = start; i <= end; i++) {
    html += `<button class="page-btn${i===currentPage?' active':''}" onclick="loadUsers(${i})">${i+1}</button>`;
  }
  html += `<button class="page-btn" onclick="loadUsers(${currentPage+1})" ${currentPage>=totalPages-1?'disabled':''}>Next →</button>`;
  pg.innerHTML = html;
}

function onSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => loadUsers(0), 320);
}

async function openUser(id) {
  const cached = allUsers.find(u => u.id === id);
  if (cached) renderModal(cached);
  document.getElementById('ov-user').classList.add('on');

  try {
    const r = await api('/api/admin.php?action=user&id=' + id);
    if (r.user) renderModal(r.user);
  } catch {}
}

function renderModal(u) {
  document.getElementById('m-av').textContent = (u.username||'?')[0].toUpperCase();
  document.getElementById('m-title').textContent = u.username;
  document.getElementById('m-sub').innerHTML =
    (u.role === 'admin' ? '<span class="tag tag-admin" style="margin-right:6px">Admin</span>' : '') +
    (u.role === 'staff' ? '<span class="tag tag-admin" style="margin-right:6px">Staff</span>' : '') +
    (u.role === 'media' ? '<span class="tag tag-media" style="margin-right:6px">Media</span>' : '') +
    (u.banned ? '<span class="tag tag-offline" style="margin-right:6px">BANNED</span>' : '') +
    (u.plusMember ? '<span style="margin-right:6px;font-size:10px;padding:1px 6px;border-radius:4px;background:rgba(124,58,237,.2);color:#a78bfa;border:1px solid rgba(124,58,237,.4)">Plus</span>' : '') +
    `Joined ${u.createdAt ? new Date(u.createdAt).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'}) : '—'}`;

  const statusLabel = u.status === 'in-game'
    ? `<span class="tag tag-ingame">In-Game${u.gameVersion ? ' — ' + esc(u.gameVersion) : ''}</span>`
    : u.status === 'online' ? '<span class="tag tag-online">Online</span>'
    : '<span class="tag tag-offline">Offline</span>';

  document.getElementById('m-body').innerHTML = `
    <div class="field-group">
      <div class="field-group-label">Account</div>
      ${fieldRow('Website Username', esc(u.username))}
      ${fieldRow('Email', u.email ? esc(u.email) : null, true)}
      ${fieldRow('Role', u.role === 'admin' ? '<span class="tag tag-admin">Admin</span>' : u.role === 'staff' ? '<span class="tag tag-admin">Staff</span>' : u.role === 'media' ? '<span class="tag tag-media">Media</span>' : '<span class="tag tag-user">User</span>')}
      ${fieldRow('Status', statusLabel)}
      ${fieldRow('Last Seen', u.lastSeen ? new Date(u.lastSeen).toLocaleString() : null)}
    </div>
    <div class="field-group">
      <div class="field-group-label">Minecraft</div>
      ${fieldRow('Minecraft Username', u.mcUsername ? `<span class="mono">${esc(u.mcUsername)}</span>` : null)}
      ${fieldRow('MC Access Token', u.mcAccessToken, false, true)}
      ${fieldRow('Game Version', u.gameVersion ? `<span class="mono">${esc(u.gameVersion)}</span>` : null)}
    </div>
    <div class="field-group">
      <div class="field-group-label">Identity & Network</div>
      ${fieldRow('UUID', u.uuid, false, true)}
      ${fieldRow('Last IP', u.lastIp ? `<span class="mono">${esc(u.lastIp)}</span>` : null)}
      ${fieldRow('User ID', `<span class="mono">#${u.id}</span>`)}
      ${fieldRow('Joined', u.createdAt ? new Date(u.createdAt).toLocaleString() : null)}
    </div>

    
    <div style="margin-top:14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;padding:14px">
      <div style="font-size:10.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--w3);margin-bottom:10px">Moderation</div>
      <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">
        <button onclick="adminWarn(${u.id})" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(251,191,36,.3);background:rgba(251,191,36,.1);color:#fbbf24;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">⚠️ Warn</button>
        ${u.banned
          ? `<button onclick="adminUnban(${u.id})" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(74,222,128,.3);background:rgba(74,222,128,.1);color:#4ade80;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">✓ Unban</button>`
          : `<button onclick="adminBan(${u.id})" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(248,113,113,.3);background:rgba(248,113,113,.1);color:#f87171;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">🚫 Ban</button>`}
        ${u.plusMember
          ? `<button onclick="adminPlus(${u.id},0)" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(124,58,237,.3);background:rgba(124,58,237,.1);color:#a78bfa;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">✕ Remove Plus</button>`
          : `<button onclick="adminPlus(${u.id},1)" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(124,58,237,.3);background:rgba(124,58,237,.2);color:#a78bfa;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">⭐ Grant Plus</button>`}
        ${u.donorBadge
          ? `<button onclick="adminDonor(${u.id},0)" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(251,191,36,.3);background:rgba(251,191,36,.1);color:#fbbf24;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">✕ Remove Donor</button>`
          : `<button onclick="adminDonor(${u.id},1)" style="padding:6px 14px;border-radius:7px;border:1px solid rgba(251,191,36,.3);background:rgba(251,191,36,.1);color:#fbbf24;font-family:'Inter',sans-serif;font-size:12px;cursor:pointer">💛 Donor Badge</button>`}
      </div>
      <div style="margin-top:10px;padding:10px 12px;background:var(--bg);border-radius:7px">
        <div style="font-size:10.5px;color:var(--w3);margin-bottom:8px">Change Role</div>
        <div style="display:flex;gap:6px;flex-wrap:wrap" id="role-btns-${u.id}">
          <button onclick="setUserRole(${u.id},'user')" class="role-btn ${u.role === 'user' || !u.role ? 'role-btn-active' : ''}" style="--rc:var(--w3);--rbg:var(--w4)">User</button>
          <button onclick="setUserRole(${u.id},'media')" class="role-btn ${u.role === 'media' ? 'role-btn-active' : ''}" style="--rc:#c084fc;--rbg:rgba(192,132,252,.15)">Media</button>
          <button onclick="setUserRole(${u.id},'staff')" class="role-btn ${u.role === 'staff' ? 'role-btn-active' : ''}" style="--rc:#60a5fa;--rbg:rgba(96,165,250,.15)">Staff</button>
          <button onclick="setUserRole(${u.id},'admin')" class="role-btn ${u.role === 'admin' ? 'role-btn-active' : ''}" style="--rc:#f87171;--rbg:rgba(248,113,113,.15)">Admin</button>
        </div>
      </div>
    </div>

    <!-- Cosmetics section in user modal -->
    <div style="margin-top:14px;background:var(--s3);border:1px solid rgba(45,212,191,.2);border-radius:10px;padding:14px">
      <div style="font-size:10.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--teal);margin-bottom:10px;display:flex;align-items:center;gap:6px">
        ✨ Cosmetics
        <span style="font-weight:400;color:var(--w3);text-transform:none;letter-spacing:0">${u.mcUsername ? '· MC: ' + esc(u.mcUsername) : ''}</span>
      </div>
      <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px;flex-wrap:wrap">
        <select id="modal-cos-select-${u.id}" style="flex:1;min-width:200px;padding:7px 11px;border-radius:7px;border:1px solid var(--line2);background:var(--s2);color:var(--w);font-family:'Inter',sans-serif;font-size:12px;outline:none;cursor:pointer">
          <option value="">— Select cosmetic to grant —</option>
        </select>
        <button onclick="modalGrantCosmetic(${u.id})" style="padding:7px 14px;border-radius:7px;border:none;background:rgba(45,212,191,.15);border:1px solid rgba(45,212,191,.3);color:#2dd4bf;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer">🎁 Grant</button>
      </div>
      <div id="modal-cos-list-${u.id}" style="font-size:12px;color:var(--w3)">Loading cosmetics…</div>
    </div>`;

  loadUserModalCosmetics(u.id);
}

async function adminWarn(id) {
  const reason = prompt('Warn reason (optional):') ?? ''; if (reason === null) return;
  const r = await api('/api/moderation.php?action=warn', { method:'POST', body: JSON.stringify({userId:id,reason}) });
  if (r.ok) toast('Warning issued'); else toast(r.error||'Failed','er');
  openUser(id);
}
async function adminBan(id) {
  const reason = prompt('Ban reason:'); if (!reason) return;
  const r = await api('/api/moderation.php?action=ban', { method:'POST', body: JSON.stringify({userId:id,reason}) });
  if (r.ok) { toast('User banned'); closeModal(); loadPage(currentPage, searchQ); } else toast(r.error||'Failed','er');
}
async function adminUnban(id) {
  const r = await api('/api/moderation.php?action=unban', { method:'POST', body: JSON.stringify({userId:id}) });
  if (r.ok) { toast('User unbanned'); openUser(id); } else toast(r.error||'Failed','er');
}
async function adminPlus(id, grant) {
  const r = await api('/api/moderation.php?action=plus', { method:'POST', body: JSON.stringify({userId:id,grant}) });
  if (r.ok) { toast(grant ? 'Plus granted!' : 'Plus removed'); openUser(id); } else toast(r.error||'Failed','er');
}
async function adminDonor(id, grant) {
  const r = await api('/api/moderation.php?action=donor', { method:'POST', body: JSON.stringify({userId:id,grant}) });
  if (r.ok) { toast(grant ? 'Donor badge granted!' : 'Donor badge removed'); openUser(id); } else toast(r.error||'Failed','er');
}

async function setUserRole(id, role) {
  const labels = {user:'User',media:'Media',staff:'Staff',admin:'Admin'};
  if (role === 'admin' && !confirm('Are you sure you want to make this user an Admin?')) return;
  const r = await api('/api/moderation.php?action=set-role', { method:'POST', body: JSON.stringify({userId:id,role}) });
  if (r.ok) { toast('Role changed to ' + labels[role]); openUser(id); } else toast(r.error||'Failed','er');
}

function fieldRow(label, value, isMono, copyable) {
  if (!value && value !== 0) value = '<span class="field-val muted">—</span>';
  const valClass = isMono ? 'field-val mono' : 'field-val';
  const copyBtn = copyable && value && !value.includes('muted')
    ? `<button class="copy-btn" onclick="copyField(this, event)">copy</button>` : '';
  const plain = typeof value === 'string' ? value.replace(/<[^>]+>/g,'').trim() : '';
  return `<div class="field-row">
    <div class="field-label">${label}</div>
    <div class="${valClass}" data-copy="${esc(plain)}">${value}</div>
    ${copyBtn}
  </div>`;
}

function copyField(btn, e) {
  e.stopPropagation();
  const row = btn.closest('.field-row');
  const val = row.querySelector('[data-copy]')?.dataset.copy || '';
  navigator.clipboard.writeText(val).then(() => {
    btn.textContent = 'copied!'; btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'copy'; btn.classList.remove('copied'); }, 1800);
  });
}

function closeModal() { document.getElementById('ov-user').classList.remove('on'); }
document.getElementById('ov-user').addEventListener('click', closeModal);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

async function api(path, opts = {}) {
  const r = await fetch(API + path, {
    headers: {'Content-Type':'application/json', ...(token ? {Authorization:'Bearer '+token} : {})},
    ...opts
  });
  return r.json();
}

function esc(s) {
  if (!s && s !== 0) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

let _tt;
function toast(m, t='ok') {
  const el = document.getElementById('toast');
  el.textContent = m; el.className = 'on ' + t;
  clearTimeout(_tt); _tt = setTimeout(() => el.className = '', 3200);
}

function openBroadcastPanel() {
  document.getElementById('broadcast-panel').style.display = 'block';
  loadActiveBroadcasts();
}

function closeBroadcastPanel() {
  document.getElementById('broadcast-panel').style.display = 'none';
}

async function sendBroadcast() {
  const msg  = document.getElementById('bc-msg').value.trim();
  const type = document.getElementById('bc-type').value;
  const ttl  = parseInt(document.getElementById('bc-ttl').value);
  if (!msg) { toast('Enter a message first', 'er'); return; }
  try {
    const r = await api('/api/admin.php?action=broadcast', {
      method: 'POST',
      body: JSON.stringify({ message: msg, type, ttl })
    });
    if (r.ok) {
      toast('Broadcast sent! Active launchers will see it within 30 seconds.');
      document.getElementById('bc-msg').value = '';
      loadActiveBroadcasts();
    } else {
      toast(r.error || 'Failed', 'er');
    }
  } catch { toast('Failed to send broadcast', 'er'); }
}

async function loadActiveBroadcasts() {
  const el = document.getElementById('bc-active');
  try {
    const r = await api('/api/admin.php?action=broadcasts');
    const list = r.broadcasts || [];
    if (!list.length) { el.innerHTML = '<span style="color:var(--w3)">No active broadcasts.</span>'; return; }
    el.innerHTML = '<div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:6px">Active Broadcasts</div>' +
      list.map(b => {
        const colors = { info:'#60A5FA', warning:'#FBBF24', error:'#F87171', success:'#4ADE80' };
        const exp = b.expires_at ? new Date(b.expires_at).toLocaleTimeString() : 'Never';
        return `<div style="display:flex;align-items:center;gap:10px;padding:7px 10px;background:var(--s3);border-radius:7px;margin-bottom:4px">
          <div style="width:8px;height:8px;border-radius:50%;background:${colors[b.type]||'#60A5FA'};flex-shrink:0"></div>
          <span style="flex:1;font-size:12.5px;color:var(--w)">${esc(b.message)}</span>
          <span style="font-size:10.5px;color:var(--w3)">expires ${exp}</span>
          <button onclick="deleteBroadcast(${b.id})" style="padding:2px 8px;border-radius:5px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">✕ Remove</button>
        </div>`;
      }).join('');
  } catch { el.innerHTML = '<span style="color:var(--w3)">Could not load broadcasts.</span>'; }
}

async function deleteBroadcast(id) {
  try {
    await api('/api/admin.php?action=broadcast&id=' + id, { method: 'DELETE' });
    toast('Broadcast removed');
    loadActiveBroadcasts();
  } catch { toast('Failed', 'er'); }
}

const adminPanels = ['users','news','leaderboard','announce','servers','splash','events','patches','withdrawals','cosmetics','marketplace'];
function switchAdminTab(tab) {
  adminPanels.forEach(p => {
    const panel = document.getElementById('panel-' + p);
    const btn   = document.getElementById('atab-' + p);
    if (!panel || !btn) return;
    const on = p === tab;
    panel.style.display = on ? 'block' : 'none';
    btn.style.background = on ? 'rgba(124,58,237,.15)' : 'transparent';
    btn.style.color      = on ? '#a78bfa' : 'var(--w3)';
    btn.style.borderColor= on ? 'var(--line2)' : 'var(--line)';
  });
  if (tab === 'news')        loadNewsAdmin();
  if (tab === 'leaderboard') loadLeaderboard();
  if (tab === 'servers')     loadServersAdmin();
  if (tab === 'splash')      loadSplashAdmin();
  if (tab === 'events')      loadEventsAdmin();
  if (tab === 'patches')     loadPatchesAdmin();
  if (tab === 'withdrawals') loadWithdrawals('pending');
  if (tab === 'cosmetics')   loadCosmeticsAdmin();
  if (tab === 'marketplace') loadMarketplacePending();
}

async function createNewsPost() {
  const title  = document.getElementById('news-title').value.trim();
  const body   = document.getElementById('news-body').value.trim();
  const pub    = document.getElementById('news-published').checked ? 1 : 0;
  const pinned = document.getElementById('news-pinned').checked ? 1 : 0;
  if (!title || !body) { toast('Title and body required', 'er'); return; }
  const r = await api('/api/news.php?action=create', { method:'POST', body: JSON.stringify({title,body,published:pub,pinned}) });
  if (r.ok) { toast('Post published!'); document.getElementById('news-title').value=''; document.getElementById('news-body').value=''; loadNewsAdmin(); }
  else toast(r.error||'Failed','er');
}

async function loadNewsAdmin() {
  const el = document.getElementById('news-list-admin');
  const r  = await api('/api/news.php?action=list&limit=20');
  const posts = r.posts || [];
  if (!posts.length) { el.innerHTML = '<div style="padding:12px;color:var(--w3)">No posts yet.</div>'; return; }
  el.innerHTML = posts.map(p => `
    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--s2);border:1px solid var(--line);border-radius:9px;margin-bottom:7px">
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--w)">${esc(p.title)}</div>
        <div style="font-size:11px;color:var(--w3)">${new Date(p.created_at).toLocaleDateString()} · ${p.published?'Published':'Draft'}${p.pinned?' · 📌 Pinned':''}</div>
      </div>
      <button onclick="deleteNewsPost(${p.id})" style="padding:4px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Delete</button>
    </div>`).join('');
}

async function deleteNewsPost(id) {
  if (!confirm('Delete this post?')) return;
  await api('/api/news.php?id='+id, { method:'DELETE' });
  toast('Post deleted'); loadNewsAdmin();
}

async function loadLeaderboard() {
  const key = document.getElementById('lb-key')?.value || 'playtime';
  const el  = document.getElementById('lb-list');
  el.innerHTML = 'Loading…';
  const r = await api('/api/moderation.php?action=leaderboard&key='+key+'&limit=10');
  const rows = r.leaderboard || [];
  if (!rows.length) { el.innerHTML = '<div style="color:var(--w3);padding:8px">No data yet. Stats are submitted by server plugins.</div>'; return; }
  el.innerHTML = `<table style="width:100%;border-collapse:collapse;font-size:13px">
    <thead><tr style="color:var(--w3);font-size:11px;text-transform:uppercase;letter-spacing:.06em">
      <th style="text-align:left;padding:6px 0">#</th>
      <th style="text-align:left;padding:6px 8px">Player</th>
      <th style="text-align:right;padding:6px 0">${esc(key)}</th>
    </tr></thead>
    <tbody>${rows.map((r,i) => `<tr style="border-top:1px solid var(--line)">
      <td style="padding:8px 0;color:var(--w3)">${i+1}</td>
      <td style="padding:8px 8px;color:var(--w)">${esc(r.username)}${r.plus_member?'<span style="margin-left:5px;font-size:9px;padding:1px 4px;border-radius:3px;background:rgba(124,58,237,.2);color:#a78bfa">Plus</span>':''}</td>
      <td style="padding:8px 0;text-align:right;color:var(--pl);font-weight:700">${Number(r.stat_value).toLocaleString()}</td>
    </tr>`).join('')}</tbody>
  </table>`;
}

async function scheduleAnnouncement() {
  const title  = document.getElementById('ann-title').value.trim();
  const body   = document.getElementById('ann-body').value.trim();
  const type   = document.getElementById('ann-type').value;
  const target = document.getElementById('ann-target').value;
  const time   = document.getElementById('ann-time').value;
  if (!title || !body || !time) { toast('Title, body and time required','er'); return; }
  const r = await api('/api/announcements.php?action=schedule', { method:'POST', body: JSON.stringify({title,body,type,target,send_at:time}) });
  if (r.ok) { toast('Announcement scheduled!'); document.getElementById('ann-title').value=''; document.getElementById('ann-body').value=''; }
  else toast(r.error||'Failed','er');
}

async function adminSaveServer() {
  const name     = document.getElementById('srv-name').value.trim();
  const address  = document.getElementById('srv-addr').value.trim();
  const port     = parseInt(document.getElementById('srv-port').value)||25565;
  const desc     = document.getElementById('srv-desc').value.trim();
  const featured = document.getElementById('srv-featured').checked ? 1 : 0;
  if (!name || !address) { toast('Name and address required','er'); return; }
  const r = await api('/api/servers.php?action=save', { method:'POST', body: JSON.stringify({name,address,port,description:desc,featured}) });
  if (r.ok) { toast('Server added!'); loadServersAdmin(); document.getElementById('srv-name').value=''; document.getElementById('srv-addr').value=''; }
  else toast(r.error||'Failed','er');
}

async function loadServersAdmin() {
  const el = document.getElementById('srv-list-admin');
  const r  = await api('/api/servers.php?action=list');
  const srvs = r.servers || [];
  if (!srvs.length) { el.innerHTML = '<div style="color:var(--w3);padding:8px">No servers yet.</div>'; return; }
  el.innerHTML = srvs.map(s => `
    <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--s2);border:1px solid var(--line);border-radius:9px;margin-bottom:7px">
      <div style="width:9px;height:9px;border-radius:50%;background:${s.online?'#4ade80':'#475569'};flex-shrink:0"></div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--w)">${esc(s.name)} ${s.featured?'⭐':''}</div>
        <div style="font-size:11px;color:var(--w3)">${esc(s.address)}${s.port!==25565?':'+s.port:''} · ${s.online?s.players_online+'/'+s.players_max+' players':'Offline'}</div>
      </div>
      <button onclick="adminDeleteServer(${s.id})" style="padding:4px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Remove</button>
    </div>`).join('');
}

async function adminDeleteServer(id) {
  if (!confirm('Remove this server?')) return;
  await api('/api/servers.php?id='+id, { method:'DELETE' });
  toast('Server removed'); loadServersAdmin();
}

async function adminAddSplash() {
  const text = document.getElementById('splash-input').value.trim();
  if (!text) { toast('Enter some text first','er'); return; }
  const r = await api('/api/splash.php', { method:'POST', body: JSON.stringify({text}) });
  if (r.ok) { toast('Splash text added!'); document.getElementById('splash-input').value=''; loadSplashAdmin(); }
  else toast(r.error||'Failed','er');
}

async function loadSplashAdmin() {
  const el = document.getElementById('splash-list');
  try {
    const r = await api('/api/splash.php');
    el.innerHTML = '<div style="padding:8px;color:var(--w3)">Current: <strong style="color:var(--pl)">' + esc(r.text||'—') + '</strong></div>';
  } catch { el.innerHTML = '<div style="color:var(--w3);padding:8px">Could not load (run schema_new_features.sql)</div>'; }
}

async function adminCreateEvent() {
  const title = document.getElementById('ev-title').value.trim();
  const desc  = document.getElementById('ev-desc').value.trim();
  const start = document.getElementById('ev-start').value;
  const end   = document.getElementById('ev-end').value || null;
  const color = document.getElementById('ev-color').value;
  if (!title || !start) { toast('Title and start time required','er'); return; }
  const r = await api('/api/events.php?action=create', { method:'POST', body: JSON.stringify({title,description:desc,starts_at:start,ends_at:end,color}) });
  if (r.ok) { toast('Event created!'); loadEventsAdmin(); }
  else toast(r.error||'Failed','er');
}

async function loadEventsAdmin() {
  const el = document.getElementById('events-admin-list');
  try {
    const r = await api('/api/events.php?action=all');
    const evs = r.events || [];
    if (!evs.length) { el.innerHTML = '<div style="color:var(--w3);padding:8px">No events yet.</div>'; return; }
    el.innerHTML = evs.map(e => `
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--s2);border:1px solid var(--line);border-radius:9px;margin-bottom:7px">
        <div style="width:8px;height:8px;border-radius:50%;background:${esc(e.color||'#7c3aed')};flex-shrink:0"></div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--w)">${esc(e.title)}</div>
          <div style="font-size:11px;color:var(--w3)">${new Date(e.starts_at).toLocaleString()}</div>
        </div>
        <button onclick="adminDeleteEvent(${e.id})" style="padding:4px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Delete</button>
      </div>`).join('');
  } catch { el.innerHTML = '<div style="color:var(--w3);padding:8px">Could not load (run schema_new_features.sql)</div>'; }
}

async function adminDeleteEvent(id) {
  if (!confirm('Delete this event?')) return;
  await api('/api/events.php?id='+id, { method:'DELETE' });
  toast('Event deleted'); loadEventsAdmin();
}

async function adminSavePatch() {
  const version = document.getElementById('patch-ver').value.trim();
  const title   = document.getElementById('patch-title-in').value.trim();
  const body    = document.getElementById('patch-body-in').value.trim();
  if (!version || !title || !body) { toast('All fields required','er'); return; }
  const r = await api('/api/patchnotes.php', { method:'POST', body: JSON.stringify({version,title,body}) });
  if (r.ok) { toast('Patch notes saved!'); loadPatchesAdmin(); }
  else toast(r.error||'Failed','er');
}

async function loadPatchesAdmin() {
  const el = document.getElementById('patches-list');
  try {
    const r = await api('/api/patchnotes.php');
    const notes = r.notes || [];
    if (!notes.length) { el.innerHTML = '<div style="color:var(--w3);padding:8px">No patch notes yet.</div>'; return; }
    el.innerHTML = notes.map(n => `
      <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--s2);border:1px solid var(--line);border-radius:9px;margin-bottom:7px">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--w)">v${esc(n.version)} — ${esc(n.title)}</div>
          <div style="font-size:11px;color:var(--w3)">${new Date(n.created_at).toLocaleDateString()}</div>
        </div>
        <button onclick="adminDeletePatch('${esc(n.version)}')" style="padding:4px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Delete</button>
      </div>`).join('');
  } catch { el.innerHTML = '<div style="color:var(--w3);padding:8px">Could not load (run schema_new_features.sql)</div>'; }
}

async function adminDeletePatch(version) {
  if (!confirm('Delete patch notes for v' + version + '?')) return;
  await api('/api/patchnotes.php?version=' + version, { method:'DELETE' });
  toast('Deleted'); loadPatchesAdmin();
}

let _wFilter = 'pending';
async function loadWithdrawals(status) {
  _wFilter = status || _wFilter;
  ['pending','completed','cancelled','all'].forEach(s => {
    const btn = document.getElementById('wf-' + s);
    if (!btn) return;
    btn.style.background  = s === _wFilter ? 'rgba(124,58,237,.15)' : 'transparent';
    btn.style.color       = s === _wFilter ? '#a78bfa' : 'var(--w3)';
    btn.style.borderColor = s === _wFilter ? 'rgba(124,58,237,.4)' : 'var(--line)';
  });
  const el = document.getElementById('withdrawals-list');
  if (!el) return;
  el.innerHTML = 'Loading…';
  try {
    const r = await api('/api/admin.php?action=withdrawals&status=' + _wFilter);
    const rows = r.withdrawals || [];
    if (!rows.length) { el.innerHTML = '<div style="color:var(--w3);padding:12px">No ' + _wFilter + ' withdrawals.</div>'; return; }
    el.innerHTML = rows.map(w => {
      const coins = (w.points * 1000000).toLocaleString();
      const statusColor = w.status === 'pending' ? '#fbbf24' : w.status === 'completed' ? '#4ade80' : '#f87171';
      return `<div style="background:var(--s3);border:1px solid var(--line);border-radius:10px;padding:14px 16px;margin-bottom:8px">
        <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap">
          <div style="flex:1;min-width:200px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap">
              <div style="font-size:13px;font-weight:700;color:var(--w)">${esc(w.username)}</div>
              <span style="font-size:9px;font-weight:800;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em;background:${statusColor}22;color:${statusColor};border:1px solid ${statusColor}44">${esc(w.status)}</span>
            </div>
            <div style="font-size:12px;color:var(--w2);margin-bottom:3px">
              <strong style="color:#a78bfa">${w.points} pts</strong> → <strong style="color:#fbbf24">${coins} DonutSMP coins</strong>
            </div>
            <div style="font-size:11px;color:var(--w3)">
              MC: <strong style="color:var(--w2)">${esc(w.mc_username)}</strong> · Requested ${new Date(w.created_at).toLocaleString()}
            </div>
            ${w.admin_note ? `<div style="font-size:11px;color:var(--w3);margin-top:4px">Note: ${esc(w.admin_note)}</div>` : ''}
          </div>
          ${w.status === 'pending' ? `
          <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0">
            <input id="wnote-${w.id}" placeholder="Admin note (optional)" style="padding:6px 10px;border-radius:7px;border:1px solid var(--line2);background:var(--s2);color:var(--w);font-family:'Inter',sans-serif;font-size:11px;outline:none;width:180px">
            <div style="display:flex;gap:6px">
              <button onclick="handleWithdrawal(${w.id},'complete')" style="flex:1;padding:6px 12px;border-radius:7px;border:none;background:rgba(74,222,128,.15);color:#4ade80;font-family:'Inter',sans-serif;font-size:11px;font-weight:700;cursor:pointer;border:1px solid rgba(74,222,128,.3)">✓ Complete</button>
              <button onclick="handleWithdrawal(${w.id},'cancel')" style="flex:1;padding:6px 12px;border-radius:7px;border:none;background:rgba(248,113,113,.12);color:#f87171;font-family:'Inter',sans-serif;font-size:11px;font-weight:700;cursor:pointer;border:1px solid rgba(248,113,113,.25)">✕ Cancel</button>
            </div>
          </div>` : `<div style="font-size:11px;color:var(--w3);flex-shrink:0">${w.handled_at ? 'Handled ' + new Date(w.handled_at).toLocaleDateString() : ''}</div>`}
        </div>
      </div>`;
    }).join('');
  } catch(e) { el.innerHTML = '<div style="color:var(--w3);padding:12px">Something went wrong. to enable withdrawals.</div>'; }
}

async function handleWithdrawal(id, action) {
  const note = document.getElementById('wnote-' + id)?.value || '';
  const label = action === 'complete' ? 'Mark as completed' : 'Cancel and refund';
  if (!confirm(label + ' withdrawal #' + id + '?')) return;
  const r = await api('/api/admin.php?action=withdrawal-action', {
    method: 'POST',
    body: JSON.stringify({ id, action, note })
  });
  if (r.ok) { toast(action === 'complete' ? 'Marked complete!' : 'Cancelled & refunded'); loadWithdrawals(); }
  else toast(r.error || 'Failed', 'er');
}

async function loadUserModalCosmetics(userId) {
  const listEl = document.getElementById('modal-cos-list-' + userId);
  const selEl  = document.getElementById('modal-cos-select-' + userId);
  if (!listEl || !selEl) return;

  try {
    const [catalogRes, userRes] = await Promise.all([
      api('/api/cosmetics.php?action=admin-list'),
      api('/api/cosmetics.php?action=user&userId=' + userId)
    ]);

    const allCosmetics  = catalogRes.cosmetics || [];
    const userCosmetics = userRes.cosmetics || [];
    const equipped      = userRes.equipped || {};
    const ownedIds      = new Set(userCosmetics.map(c => c.id));

    const available = allCosmetics.filter(c => !ownedIds.has(c.id));
    selEl.innerHTML = '<option value="">— Select cosmetic to grant —</option>' +
      available.map(c => `<option value="${c.id}">${esc(c.name)} (${c.type} · ${c.rarity})</option>`).join('');

    if (!userCosmetics.length) {
      listEl.innerHTML = '<div style="color:var(--w3);padding:4px 0">No cosmetics owned yet.</div>';
      return;
    }

    const rarityColors = { common:'#9ca3af', uncommon:'#4ade80', rare:'#60a5fa', epic:'#a78bfa', legendary:'#fbbf24' };

    listEl.innerHTML = userCosmetics.map(c => {
      const rc = rarityColors[c.rarity] || '#9ca3af';
      const isEquipped = equipped[c.type] === c.id;
      return `<div style="display:flex;align-items:center;gap:8px;padding:8px 10px;background:var(--bg);border-radius:7px;margin-bottom:4px;border:1px solid ${isEquipped ? rc + '44' : 'transparent'}">
        <div style="width:28px;height:28px;border-radius:6px;overflow:hidden;border:1px solid var(--line);background:var(--s2);flex-shrink:0;display:flex;align-items:center;justify-content:center">
          <img src="${API}/${esc(c.texturePath)}" style="max-width:100%;max-height:100%;image-rendering:pixelated" onerror="this.style.display='none';this.parentElement.innerHTML='✨'">
        </div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
            <span style="font-size:12px;font-weight:600;color:var(--w)">${esc(c.name)}</span>
            <span style="font-size:8px;font-weight:800;padding:1px 5px;border-radius:3px;text-transform:uppercase;background:${rc}22;color:${rc};border:1px solid ${rc}33">${c.rarity}</span>
            <span style="font-size:8px;font-weight:700;padding:1px 4px;border-radius:3px;background:var(--w4);color:var(--w3);text-transform:uppercase">${c.type}</span>
            ${isEquipped ? '<span style="font-size:8px;font-weight:700;padding:1px 5px;border-radius:3px;background:rgba(74,222,128,.15);color:#4ade80;border:1px solid rgba(74,222,128,.25)">EQUIPPED</span>' : ''}
          </div>
          ${c.grantedByName ? `<div style="font-size:10px;color:var(--w3);margin-top:2px">Granted by ${esc(c.grantedByName)}</div>` : ''}
        </div>
        <button onclick="modalRevokeCosmetic(${userId},${c.id},'${esc(c.name)}')" style="padding:4px 9px;border-radius:5px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:10px;cursor:pointer;flex-shrink:0">Revoke</button>
      </div>`;
    }).join('');
  } catch(e) {
    listEl.innerHTML = '<div style="color:var(--w3)">Could not load cosmetics. Run cosmetics_schema.sql first.</div>';
  }
}

async function modalGrantCosmetic(userId) {
  const selEl = document.getElementById('modal-cos-select-' + userId);
  const cosmeticId = parseInt(selEl?.value);
  if (!cosmeticId) { toast('Select a cosmetic first', 'er'); return; }

  try {
    const r = await api('/api/cosmetics.php?action=grant', {
      method: 'POST',
      body: JSON.stringify({ userId, cosmeticId })
    });
    if (r.ok) {
      toast('Cosmetic granted!');
      loadUserModalCosmetics(userId);
    } else {
      toast(r.error || 'Failed', 'er');
    }
  } catch(e) { toast('Failed: ' + e.message, 'er'); }
}

async function modalRevokeCosmetic(userId, cosmeticId, name) {
  if (!confirm('Revoke "' + name + '" from this user?')) return;
  try {
    const r = await api('/api/cosmetics.php?action=revoke', {
      method: 'POST',
      body: JSON.stringify({ userId, cosmeticId })
    });
    if (r.ok) {
      toast('Cosmetic revoked');
      loadUserModalCosmetics(userId);
    } else {
      toast(r.error || 'Failed', 'er');
    }
  } catch(e) { toast('Failed: ' + e.message, 'er'); }
}

document.getElementById('cos-texture')?.addEventListener('change', function() {
  const file = this.files[0];
  const prev = document.getElementById('cos-preview');
  const img  = document.getElementById('cos-preview-img');
  if (file) {
    const url = URL.createObjectURL(file);
    img.src = url;
    prev.style.display = 'block';
    img.style.imageRendering = 'pixelated';
  } else {
    prev.style.display = 'none';
  }
});

async function adminCreateCosmetic() {
  const name     = document.getElementById('cos-name').value.trim();
  const type     = document.getElementById('cos-type').value;
  const rarity   = document.getElementById('cos-rarity').value;
  const desc     = document.getElementById('cos-desc').value.trim();
  const plusOnly = document.getElementById('cos-plus').checked ? 1 : 0;
  const price    = document.getElementById('cos-price').value.trim();
  const fileInput = document.getElementById('cos-texture');

  if (!name)                  { toast('Name is required', 'er'); return; }
  if (!fileInput.files.length){ toast('Texture file is required', 'er'); return; }

  const fd = new FormData();
  fd.append('name', name);
  fd.append('type', type);
  fd.append('rarity', rarity);
  fd.append('description', desc);
  fd.append('plusOnly', plusOnly);
  if (price !== '') fd.append('price', price);
  fd.append('texture', fileInput.files[0]);
  const previewInput = document.getElementById('cos-preview-file');
  if (previewInput.files.length) fd.append('preview', previewInput.files[0]);

  try {
    const r = await fetch(API + '/api/cosmetics.php?action=create', {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + token },
      body: fd
    });
    const d = await r.json();
    if (d.cosmetic) {
      toast('Cosmetic "' + d.cosmetic.name + '" created!');
      document.getElementById('cos-name').value = '';
      document.getElementById('cos-desc').value = '';
      document.getElementById('cos-price').value = '';
      document.getElementById('cos-texture').value = '';
      document.getElementById('cos-file-label').textContent = 'Upload Texture (PNG)';
      document.getElementById('cos-preview').style.display = 'none';
      document.getElementById('cos-preview-file').value = '';
      document.getElementById('cos-prev-label').textContent = 'Preview Image (optional)';
      loadCosmeticsAdmin();
    } else {
      toast(d.error || 'Failed to create', 'er');
    }
  } catch(e) { toast('Upload failed: ' + e.message, 'er'); }
}

async function loadCosmeticsAdmin() {
  const el = document.getElementById('cos-catalog');
  const sel = document.getElementById('grant-cosmetic');
  try {
    const r = await api('/api/cosmetics.php?action=admin-list');
    const items = r.cosmetics || [];

    sel.innerHTML = '<option value="">— Select cosmetic —</option>' +
      items.map(c => `<option value="${c.id}">${esc(c.name)} (${c.type} · ${c.rarity})</option>`).join('');

    if (!items.length) {
      el.innerHTML = '<div style="padding:16px;color:var(--w3);text-align:center">No cosmetics yet. Create one above!</div>';
      return;
    }

    const rarityColors = { common:'#9ca3af', uncommon:'#4ade80', rare:'#60a5fa', epic:'#a78bfa', legendary:'#fbbf24' };
    const typeIcons = { cape:'🦸', hat:'🎩', wings:'🪽', bandana:'🎭', aura:'💫', emoji:'😎' };

    el.innerHTML = items.map(c => {
      const rc = rarityColors[c.rarity] || '#9ca3af';
      const icon = typeIcons[c.type] || '✨';
      return `<div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--s2);border:1px solid var(--line);border-radius:10px;margin-bottom:8px">
        <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;border:1px solid ${c.previewPath ? 'rgba(59,130,246,.4)' : 'var(--line)'};background:var(--s3);flex-shrink:0;display:flex;align-items:center;justify-content:center">
          <img src="${API}/${esc(c.previewPath || c.texturePath)}" style="max-width:100%;max-height:100%;${c.previewPath ? 'object-fit:cover' : 'image-rendering:pixelated'}" onerror="this.style.display='none';this.parentElement.innerHTML='${icon}'">
        </div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;flex-wrap:wrap">
            <span style="font-size:14px;font-weight:700;color:var(--w)">${esc(c.name)}</span>
            <span style="font-size:9px;font-weight:800;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em;background:${rc}22;color:${rc};border:1px solid ${rc}44">${c.rarity}</span>
            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:var(--w4);color:var(--w3);text-transform:uppercase">${c.type}</span>
            ${c.plusOnly ? '<span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:rgba(124,58,237,.2);color:#a78bfa;border:1px solid rgba(124,58,237,.3)">Plus</span>' : ''}
            ${!c.active ? '<span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:rgba(248,113,113,.15);color:#f87171">Hidden</span>' : ''}
            ${c.price != null ? `<span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)">${c.price} pts</span>` : '<span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:var(--w4);color:var(--w3)">No price</span>'}
            ${c.previewPath ? '<span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;background:rgba(59,130,246,.1);color:#60a5fa">Has preview</span>' : ''}
          </div>
          <div style="font-size:11px;color:var(--w3)">
            ${c.description ? esc(c.description) + ' · ' : ''}${c.ownerCount} owner${c.ownerCount!==1?'s':''} · ${c.equippedCount} equipped
          </div>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0">
          <label style="padding:5px 10px;border-radius:6px;border:1px solid rgba(45,212,191,.3);background:transparent;color:#2dd4bf;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer;display:inline-flex;align-items:center" title="Upload preview render">
            Preview
            <input type="file" accept=".png,.jpg,.jpeg" style="display:none" onchange="adminUploadPreview(${c.id},this)">
          </label>
          <button onclick="adminSetPrice(${c.id},'${esc(c.name)}',${c.price != null ? c.price : 'null'})" style="padding:5px 10px;border-radius:6px;border:1px solid rgba(59,130,246,.3);background:transparent;color:#60a5fa;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer" title="Set shop price">Price</button>
          ${c.active
            ? `<button onclick="adminToggleCosmetic(${c.id},false)" style="padding:5px 10px;border-radius:6px;border:1px solid rgba(251,191,36,.3);background:transparent;color:#fbbf24;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer" title="Hide from catalog">Hide</button>`
            : `<button onclick="adminToggleCosmetic(${c.id},true)" style="padding:5px 10px;border-radius:6px;border:1px solid rgba(74,222,128,.3);background:transparent;color:#4ade80;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer" title="Show in catalog">Show</button>`
          }
          <button onclick="adminDeleteCosmetic(${c.id},'${esc(c.name)}')" style="padding:5px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;font-family:'Inter',sans-serif;font-size:11px;cursor:pointer">Delete</button>
        </div>
      </div>`;
    }).join('');
  } catch(e) {
    el.innerHTML = '<div style="color:var(--w3);padding:12px">Failed to load cosmetics. Run <code style="color:var(--pl)">sql/cosmetics_schema.sql</code> first.</div>';
  }
}

async function adminGrantCosmetic() {
  const username   = document.getElementById('grant-username').value.trim();
  const cosmeticId = parseInt(document.getElementById('grant-cosmetic').value);
  if (!username)   { toast('Enter a username', 'er'); return; }
  if (!cosmeticId) { toast('Select a cosmetic', 'er'); return; }

  try {
    const search = await api('/api/admin.php?action=users&q=' + encodeURIComponent(username) + '&limit=1&offset=0');
    const users = search.users || [];
    const user = users.find(u => u.username.toLowerCase() === username.toLowerCase());
    if (!user) { toast('User "' + username + '" not found', 'er'); return; }

    const r = await api('/api/cosmetics.php?action=grant', {
      method: 'POST',
      body: JSON.stringify({ userId: user.id, cosmeticId })
    });
    if (r.ok) {
      toast('Cosmetic granted to ' + username + '!');
      document.getElementById('grant-username').value = '';
      loadCosmeticsAdmin();
    } else {
      toast(r.error || 'Failed', 'er');
    }
  } catch(e) { toast('Failed: ' + e.message, 'er'); }
}

async function adminToggleCosmetic(id, active) {
  const r = await api('/api/cosmetics.php?action=update', {
    method: 'POST', body: JSON.stringify({ id, active })
  });
  if (r.cosmetic) { toast(active ? 'Cosmetic visible!' : 'Cosmetic hidden'); loadCosmeticsAdmin(); }
  else toast(r.error || 'Failed', 'er');
}

async function adminDeleteCosmetic(id, name) {
  if (!confirm('Delete "' + name + '"? This will revoke it from all users.')) return;
  const r = await api('/api/cosmetics.php?action=delete', { method: 'POST', body: JSON.stringify({ id }) });
  if (r.ok) { toast('Cosmetic deleted'); loadCosmeticsAdmin(); }
  else toast(r.error || 'Failed', 'er');
}

async function adminSetPrice(id, name, currentPrice) {
  const input = prompt('Set shop price for "' + name + '"\n\nEnter point cost (or leave blank to remove from shop):', currentPrice != null ? currentPrice : '');
  if (input === null) return;

  const price = input.trim() === '' ? null : parseInt(input.trim());
  if (price !== null && (isNaN(price) || price < 0)) {
    toast('Invalid price — enter a positive number or leave blank', 'er');
    return;
  }

  try {
    const r = await api('/api/cosmetics.php?action=update', {
      method: 'POST',
      body: JSON.stringify({ id, price })
    });
    if (r.ok || r.cosmetic) {
      toast(price !== null ? '"' + name + '" listed at ' + price + ' pts' : '"' + name + '" removed from shop');
      loadCosmeticsAdmin();
    } else {
      toast(r.error || 'Failed to update price', 'er');
    }
  } catch(e) { toast('Failed: ' + e.message, 'er'); }
}

async function adminUploadPreview(id, input) {
  if (!input.files.length) return;
  const fd = new FormData();
  fd.append('id', id);
  fd.append('preview', input.files[0]);

  try {
    const r = await fetch(API + '/api/cosmetics.php?action=upload-preview', {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + token },
      body: fd
    });
    const d = await r.json();
    if (d.cosmetic) {
      toast('Preview uploaded for "' + d.cosmetic.name + '"!');
      loadCosmeticsAdmin();
    } else {
      toast(d.error || 'Failed to upload preview', 'er');
    }
  } catch(e) { toast('Upload failed: ' + e.message, 'er'); }
}


async function loadMarketplacePending() {
  document.getElementById('mp-f-pending').style.background = 'rgba(124,58,237,.15)';
  document.getElementById('mp-f-pending').style.color = '#a78bfa';
  document.getElementById('mp-f-pending').style.borderColor = 'rgba(124,58,237,.4)';
  document.getElementById('mp-f-history').style.background = 'transparent';
  document.getElementById('mp-f-history').style.color = 'var(--w3)';
  document.getElementById('mp-f-history').style.borderColor = 'var(--line)';
  const el = document.getElementById('mp-list');
  el.innerHTML = 'Loading…';
  try {
    const r = await api('/api/marketplace.php?action=pending');
    const items = r.listings || [];
    if (!items.length) { el.innerHTML = '<div style="padding:12px;color:var(--w3)">No pending listings.</div>'; return; }
    el.innerHTML = items.map(i => `
      <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--s2);border:1px solid var(--line);border-radius:10px;margin-bottom:8px">
        <img src="${API}/${esc(i.previewPath || i.texturePath)}" style="width:48px;height:48px;border-radius:8px;border:1px solid var(--line);object-fit:cover" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:700;color:var(--w)">${esc(i.name)} <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:rgba(124,58,237,.15);color:#a78bfa">${esc(i.type)}</span></div>
          <div style="font-size:11px;color:var(--w3);margin-top:2px">by ${esc(i.sellerName)} · ${i.price} pts${i.description ? ' · ' + esc(i.description).substring(0,60) : ''}</div>
        </div>
        <div style="display:flex;gap:6px">
          <button onclick="reviewListing(${i.id},'approve')" style="padding:5px 13px;border-radius:7px;border:1px solid rgba(74,222,128,.35);background:rgba(74,222,128,.1);color:#4ade80;font-family:'Inter',sans-serif;font-size:11px;font-weight:600;cursor:pointer">Approve</button>
          <button onclick="reviewListing(${i.id},'reject')" style="padding:5px 13px;border-radius:7px;border:1px solid rgba(248,113,113,.35);background:rgba(248,113,113,.1);color:#f87171;font-family:'Inter',sans-serif;font-size:11px;font-weight:600;cursor:pointer">Reject</button>
        </div>
      </div>`).join('');
  } catch { el.innerHTML = '<div style="padding:12px;color:var(--w3)">Failed to load.</div>'; }
}

async function loadMarketplaceHistory() {
  document.getElementById('mp-f-history').style.background = 'rgba(124,58,237,.15)';
  document.getElementById('mp-f-history').style.color = '#a78bfa';
  document.getElementById('mp-f-history').style.borderColor = 'rgba(124,58,237,.4)';
  document.getElementById('mp-f-pending').style.background = 'transparent';
  document.getElementById('mp-f-pending').style.color = 'var(--w3)';
  document.getElementById('mp-f-pending').style.borderColor = 'var(--line)';
  const el = document.getElementById('mp-list');
  el.innerHTML = 'Loading…';
  try {
    const r = await api('/api/marketplace.php?action=review-history');
    const items = r.listings || [];
    if (!items.length) { el.innerHTML = '<div style="padding:12px;color:var(--w3)">No review history.</div>'; return; }
    el.innerHTML = items.map(i => {
      const stColor = i.status === 'approved' ? '#4ade80' : '#f87171';
      const stBg = i.status === 'approved' ? 'rgba(74,222,128,.12)' : 'rgba(248,113,113,.12)';
      return `
      <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:var(--s2);border:1px solid var(--line);border-radius:10px;margin-bottom:8px">
        <img src="${API}/${esc(i.previewPath || i.texturePath)}" style="width:48px;height:48px;border-radius:8px;border:1px solid var(--line);object-fit:cover" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:700;color:var(--w)">${esc(i.name)} <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:rgba(124,58,237,.15);color:#a78bfa">${esc(i.type)}</span> <span style="font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:${stBg};color:${stColor}">${esc(i.status)}</span></div>
          <div style="font-size:11px;color:var(--w3);margin-top:2px">by ${esc(i.sellerName)} · ${i.price} pts${i.reviewerName ? ' · reviewed by ' + esc(i.reviewerName) : ''}${i.reviewNote ? ' · "' + esc(i.reviewNote) + '"' : ''}</div>
        </div>
      </div>`;
    }).join('');
  } catch { el.innerHTML = '<div style="padding:12px;color:var(--w3)">Failed to load.</div>'; }
}

async function reviewListing(id, decision) {
  const note = decision === 'reject' ? prompt('Rejection reason (optional):') || '' : '';
  if (decision === 'reject' && note === null) return;
  try {
    const r = await api('/api/marketplace.php?action=review', {
      method: 'POST',
      body: JSON.stringify({ id, decision, note })
    });
    if (r.ok) { toast('Listing ' + decision + 'd'); loadMarketplacePending(); }
    else toast(r.error || 'Failed', 'er');
  } catch { toast('Failed', 'er'); }
}

init();
</script>
</body>
</html>
