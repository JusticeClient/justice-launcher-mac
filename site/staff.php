<?php
header('Content-Type: text/html; charset=UTF-8');
$API = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Staff Portal — Justice Launcher</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#06040e;--s1:#0d0b1a;--s2:#120f22;--s3:#1a1630;
  --p:#7c3aed;--pl:#a78bfa;--line:rgba(124,58,237,.18);--line2:rgba(124,58,237,.35);
  --w:#f1f5f9;--w2:#cbd5e1;--w3:#64748b;--w4:#334155;
  --red:#f87171;--green:#4ade80;--amber:#fbbf24;--mono:'JetBrains Mono',monospace;
}
body{background:var(--bg);color:var(--w);font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh}
a{color:var(--pl);text-decoration:none}

.tb-logo{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#4c1d95,var(--p));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:900;color:#fff}
.tb-title{font-size:14px;font-weight:700;color:var(--w)}
.tb-badge{font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;background:rgba(251,191,36,.15);color:var(--amber);border:1px solid rgba(251,191,36,.3);letter-spacing:.06em;text-transform:uppercase}
.tb-user{display:flex;align-items:center;gap:8px;margin-left:auto}
.tb-av{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#4c1d95,var(--p));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff}
.tb-name{font-size:13px;font-weight:600;color:var(--w)}
.logout-btn{padding:5px 12px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:inherit;font-size:12px;cursor:pointer;transition:all .12s}
.logout-btn:hover{border-color:var(--red);color:var(--red)}

.lw-box{width:360px;background:var(--s2);border:1px solid var(--line2);border-radius:16px;padding:36px 32px;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.lw-logo{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#4c1d95,var(--p));display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:900;color:#fff;margin:0 auto 18px}
.lw-box h1{font-size:20px;font-weight:700;text-align:center;margin-bottom:4px}
.lw-box p{font-size:12px;color:var(--w3);text-align:center;margin-bottom:24px}
.field{margin-bottom:12px}
.field label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:5px}
.field input{width:100%;padding:10px 13px;border-radius:9px;border:1px solid var(--line);background:var(--s3);color:var(--w);font-family:inherit;font-size:13px;outline:none;transition:border .15s}
.field input:focus{border-color:var(--p)}
.btn-p{width:100%;padding:11px;border-radius:9px;border:none;background:var(--p);color:#fff;font-size:13px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s}
.btn-p:hover{background:#6d28d9}
.err{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--red);border-radius:8px;padding:9px 12px;font-size:12px;margin-bottom:12px;display:none}

.page{padding:70px 24px 30px;max-width:900px;margin:0 auto;display:none}
.page-title{font-size:20px;font-weight:800;margin-bottom:4px}
.page-sub{font-size:13px;color:var(--w3);margin-bottom:24px}

.sc{background:var(--s2);border:1px solid var(--line);border-radius:13px;padding:20px;margin-bottom:16px}
.sc-title{font-size:12px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--w3);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--line)}

.search-row{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap}
.search-row input{flex:1;min-width:200px;padding:9px 13px;border-radius:9px;border:1px solid var(--line);background:var(--s3);color:var(--w);font-family:inherit;font-size:13px;outline:none;transition:border .15s}
.search-row input:focus{border-color:var(--p)}
.search-row button{padding:9px 18px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer}

.user-result{display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px}
.u-av{width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#4c1d95,var(--p));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:#fff;flex-shrink:0}
.u-info{flex:1;min-width:0}
.u-name{font-size:13px;font-weight:700;color:var(--w);display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.u-sub{font-size:11px;color:var(--w3);margin-top:2px}
.u-actions{display:flex;gap:7px;flex-wrap:wrap}
.tag{font-size:9px;font-weight:800;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em}
.tag-admin{background:rgba(248,113,113,.15);color:var(--red);border:1px solid rgba(248,113,113,.3)}
.tag-staff{background:rgba(251,191,36,.15);color:var(--amber);border:1px solid rgba(251,191,36,.3)}
.tag-banned{background:rgba(248,113,113,.2);color:var(--red);border:1px solid rgba(248,113,113,.4)}
.tag-plus{background:rgba(124,58,237,.2);color:var(--pl);border:1px solid rgba(124,58,237,.4)}
.tag-media{background:rgba(192,132,252,.15);color:#c084fc;border:1px solid rgba(192,132,252,.3)}
.btn-warn{padding:5px 13px;border-radius:7px;border:1px solid rgba(251,191,36,.35);background:rgba(251,191,36,.1);color:var(--amber);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-warn:hover{background:rgba(251,191,36,.2)}
.btn-ban{padding:5px 13px;border-radius:7px;border:1px solid rgba(248,113,113,.35);background:rgba(248,113,113,.1);color:var(--red);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-ban:hover{background:rgba(248,113,113,.2)}
.btn-unban{padding:5px 13px;border-radius:7px;border:1px solid rgba(74,222,128,.35);background:rgba(74,222,128,.1);color:var(--green);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-unban:hover{background:rgba(74,222,128,.2)}

.clan-result{display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px}
.clan-tag-pill{padding:4px 10px;border-radius:7px;font-size:12px;font-weight:900;color:#fff;flex-shrink:0}
.c-info{flex:1;min-width:0}
.c-name{font-size:13px;font-weight:700;color:var(--w)}
.c-sub{font-size:11px;color:var(--w3);margin-top:2px}
.btn-del{padding:5px 13px;border-radius:7px;border:1px solid rgba(248,113,113,.35);background:rgba(248,113,113,.1);color:var(--red);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-del:hover{background:rgba(248,113,113,.2)}

.toast{position:fixed;bottom:20px;right:20px;background:var(--s2);border:1px solid var(--line2);border-radius:10px;padding:10px 16px;font-size:13px;color:var(--w);z-index:9999;opacity:0;transform:translateY(8px);transition:all .22s;pointer-events:none}
.toast.show{opacity:1;transform:translateY(0)}
.empty{text-align:center;padding:24px;color:var(--w4);font-size:13px}

/* Reports */
.rp-stats{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.rp-stat{flex:1;min-width:80px;padding:10px;border-radius:9px;background:var(--s3);border:1px solid var(--line);text-align:center;cursor:pointer;transition:all .12s}
.rp-stat:hover{border-color:var(--line2)}
.rp-stat.active{border-color:var(--p);background:rgba(124,58,237,.1)}
.rp-stat-val{font-size:20px;font-weight:800;color:var(--w)}
.rp-stat-label{font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-top:2px}
.rp-item{padding:14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px;transition:border-color .12s}
.rp-item:hover{border-color:var(--line2)}
.rp-top{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap}
.rp-tag{font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em}
.rp-cat-player{background:rgba(248,113,113,.12);color:var(--red);border:1px solid rgba(248,113,113,.3)}
.rp-cat-bug{background:rgba(251,191,36,.12);color:var(--amber);border:1px solid rgba(251,191,36,.3)}
.rp-cat-general{background:rgba(96,165,250,.12);color:#60a5fa;border:1px solid rgba(96,165,250,.3)}
.rp-st-open{background:rgba(251,191,36,.12);color:var(--amber);border:1px solid rgba(251,191,36,.3)}
.rp-st-in_progress{background:rgba(96,165,250,.12);color:#60a5fa;border:1px solid rgba(96,165,250,.3)}
.rp-st-resolved{background:rgba(74,222,128,.12);color:var(--green);border:1px solid rgba(74,222,128,.3)}
.rp-st-dismissed{background:rgba(255,255,255,.06);color:var(--w3);border:1px solid rgba(255,255,255,.1)}
.rp-subject{font-size:13px;font-weight:700;color:var(--w)}
.rp-desc{font-size:12px;color:var(--w2);margin-top:4px;line-height:1.5;max-height:60px;overflow:hidden}
.rp-desc.expanded{max-height:none}
.rp-meta{font-size:10px;color:var(--w3);margin-top:6px}
.rp-actions{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap;align-items:center}
.rp-sel{padding:5px 10px;border-radius:7px;border:1px solid var(--line);background:var(--s1);color:var(--w);font-family:inherit;font-size:11px;outline:none;cursor:pointer}
.rp-sel:focus{border-color:var(--p)}
.rp-note-input{flex:1;min-width:160px;padding:5px 10px;border-radius:7px;border:1px solid var(--line);background:var(--s1);color:var(--w);font-family:inherit;font-size:11px;outline:none}
.rp-note-input:focus{border-color:var(--p)}
.btn-resolve{padding:5px 13px;border-radius:7px;border:1px solid rgba(74,222,128,.35);background:rgba(74,222,128,.1);color:var(--green);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-resolve:hover{background:rgba(74,222,128,.2)}
.btn-dismiss{padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer;transition:all .12s}
.btn-dismiss:hover{color:var(--w)}
.rp-staff-note{margin-top:6px;padding:6px 10px;border-radius:6px;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);font-size:11px;color:var(--pl)}

/* Chat View */
.chat-view{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:var(--bg);z-index:100}
.chat-view.active{display:flex;flex-direction:column}
.chat-header{display:flex;align-items:center;gap:12px;padding:20px 24px;border-bottom:1px solid var(--line);background:var(--s2)}
.chat-back{padding:8px 14px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--pl);font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;transition:all .12s}
.chat-back:hover{border-color:var(--pl);background:rgba(124,58,237,.1)}
.chat-meta{flex:1;min-width:0}
.chat-title{font-size:15px;font-weight:700;color:var(--w)}
.chat-sub{font-size:12px;color:var(--w3);margin-top:2px}
.chat-status{padding:8px 14px;border-radius:7px;border:none;background:var(--s3);color:var(--w);font-family:inherit;font-size:11px;font-weight:600;outline:none;cursor:pointer}
.chat-body{flex:1;overflow-y:auto;padding:20px 24px;display:flex;flex-direction:column;gap:12px}
.msg{display:flex;gap:12px;margin-bottom:8px;align-items:flex-start}
.msg.staff{flex-direction:row-reverse}
.msg-av{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#4c1d95,var(--p));display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff;flex-shrink:0}
.msg.staff .msg-av{background:linear-gradient(135deg,#1e40af,#3b82f6)}
.msg-content{flex:1;max-width:60%;display:flex;flex-direction:column;gap:4px}
.msg.staff .msg-content{align-items:flex-end}
.msg-bubble{padding:10px 14px;border-radius:10px;background:var(--s3);border:1px solid var(--line);color:var(--w);font-size:13px;word-wrap:break-word;line-height:1.4}
.msg.staff .msg-bubble{background:rgba(124,58,237,.15);border-color:rgba(124,58,237,.3)}
.msg-time{font-size:10px;color:var(--w4)}
.msg-system{text-align:center;font-size:12px;color:var(--w3);padding:8px 0;margin:8px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line)}

.chat-footer{padding:16px 24px;border-top:1px solid var(--line);background:var(--s2);display:flex;gap:8px}
.chat-input{flex:1;padding:10px 13px;border-radius:9px;border:1px solid var(--line);background:var(--s3);color:var(--w);font-family:inherit;font-size:13px;outline:none;resize:none;max-height:100px}
.chat-input:focus{border-color:var(--p)}
.chat-send{padding:10px 18px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;transition:background .12s;align-self:flex-end}
.chat-send:hover{background:#6d28d9}

/* Activity Feed */
.act-card{padding:14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px}
.act-card.status-change{border-left:3px solid var(--amber)}
.act-header{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap}
.act-staff{font-size:12px;font-weight:700;color:var(--w)}
.act-action{font-size:13px;color:var(--w2);margin-bottom:4px;line-height:1.4}
.act-report{font-size:11px;color:var(--pl);margin-bottom:4px;display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.act-reporter{font-size:11px;color:var(--w3)}
.act-time{font-size:10px;color:var(--w4);margin-top:4px}
</style>
</head>
<body>

<div id="login-wall">
  <div class="lw-box">
    <div class="lw-logo">⚖</div>
    <h1>Staff Portal</h1>
    <p>Justice Launcher — Staff access only</p>
    <div class="err" id="lw-err"></div>
    <div class="field"><label>Username or Email</label><input type="text" id="lw-l" placeholder="Username" onkeydown="if(event.key==='Enter')lwLogin()"></div>
    <div class="field"><label>Password</label><input type="password" id="lw-p" placeholder="Password" onkeydown="if(event.key==='Enter')lwLogin()"></div>
    <button class="btn-p" id="lw-btn" onclick="lwLogin()">Sign In</button>
  </div>
</div>

<div id="topbar">
  <div class="tb-logo">⚖</div>
  <div class="tb-title">Staff Portal</div>
  <span class="tb-badge" id="tb-role">Staff</span>
  <div class="tb-user">
    <div class="tb-av" id="tb-av">?</div>
    <div class="tb-name" id="tb-name">—</div>
    <button class="logout-btn" onclick="doLogout()">Sign out</button>
  </div>
</div>

<div class="page" id="main-page">
  <div class="page-title">Staff Portal</div>
  <div class="page-sub">Moderate users and manage clans. You do not have access to the admin panel.</div>

  
  <div class="sc">
    <div class="sc-title">🔍 Find & Moderate User</div>
    <div class="search-row">
      <input type="text" id="user-search" placeholder="Search by username…" onkeydown="if(event.key==='Enter')searchUsers()">
      <button onclick="searchUsers()">Search</button>
    </div>
    <div id="user-results"><div class="empty">Search for a user above</div></div>
  </div>

  
  <div class="sc">
    <div class="sc-title">⚔️ Find & Delete Clan</div>
    <div class="search-row">
      <input type="text" id="clan-search" placeholder="Search by clan name or tag…" onkeydown="if(event.key==='Enter')searchClans()">
      <button onclick="searchClans()">Search</button>
    </div>
    <div id="clan-results"><div class="empty">Search for a clan above</div></div>
  </div>

  <!-- Reports Section -->
  <div class="sc">
    <div class="sc-title">📝 User Reports</div>
    <div class="rp-stats" id="rp-stats">
      <div class="rp-stat active" data-filter="open" onclick="filterReports('open')">
        <div class="rp-stat-val" id="rp-cnt-open">0</div>
        <div class="rp-stat-label">Open</div>
      </div>
      <div class="rp-stat" data-filter="in_progress" onclick="filterReports('in_progress')">
        <div class="rp-stat-val" id="rp-cnt-in_progress">0</div>
        <div class="rp-stat-label">In Progress</div>
      </div>
      <div class="rp-stat" data-filter="resolved" onclick="filterReports('resolved')">
        <div class="rp-stat-val" id="rp-cnt-resolved">0</div>
        <div class="rp-stat-label">Resolved</div>
      </div>
      <div class="rp-stat" data-filter="dismissed" onclick="filterReports('dismissed')">
        <div class="rp-stat-val" id="rp-cnt-dismissed">0</div>
        <div class="rp-stat-label">Dismissed</div>
      </div>
      <div class="rp-stat" data-filter="all" onclick="filterReports('all')">
        <div class="rp-stat-val" id="rp-cnt-total">0</div>
        <div class="rp-stat-label">Total</div>
      </div>
    </div>
    <div id="rp-list"><div class="empty">Loading reports…</div></div>
  </div>

  <div class="sc">
    <div class="sc-title">🏪 Marketplace Review</div>
    <div style="display:flex;gap:6px;margin-bottom:14px">
      <button onclick="loadMpPending()" id="mp-btn-pending" style="padding:5px 13px;border-radius:7px;border:1px solid rgba(124,58,237,.4);background:rgba(124,58,237,.1);color:var(--pl);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer">Pending</button>
      <button onclick="loadMpHistory()" id="mp-btn-history" style="padding:5px 13px;border-radius:7px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:inherit;font-size:11px;font-weight:600;cursor:pointer">History</button>
    </div>
    <div id="mp-queue"><div class="empty">Loading…</div></div>
  </div>

  <div class="sc">
    <div class="sc-title">📋 Recent Actions</div>
    <div id="mod-log"><div class="empty">Loading…</div></div>
  </div>
</div>

<!-- Chat View -->
<div class="chat-view" id="chat-view">
  <div class="chat-header">
    <button class="chat-back" onclick="closeChatView()">← Back</button>
    <div class="chat-meta">
      <div class="chat-title" id="chat-title">Report #0</div>
      <div class="chat-sub" id="chat-sub">Reporter · Unknown</div>
    </div>
    <select class="chat-status" id="chat-status" onchange="updateReportStatus()">
      <option value="open">Open</option>
      <option value="in_progress">In Progress</option>
      <option value="resolved">Resolved</option>
      <option value="dismissed">Dismissed</option>
    </select>
  </div>
  <div class="chat-body" id="chat-messages"></div>
  <div class="chat-footer">
    <textarea class="chat-input" id="chat-input" placeholder="Type your message…" onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendChatMessage()}"></textarea>
    <button class="chat-send" onclick="sendChatMessage()">Send</button>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const API = '<?= $API ?>';
let token = localStorage.getItem('jl_token') || '';
let me    = null;

async function api(path, opts = {}) {
  const r = await fetch(API + path, {
    ...opts,
    headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, ...(opts.headers||{}) }
  });
  return r.json();
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function toast(msg, type = 'ok') {
  const el = document.getElementById('toast');
  el.textContent = msg;
  el.style.borderColor = type === 'er' ? 'rgba(248,113,113,.4)' : 'rgba(124,58,237,.4)';
  el.classList.add('show');
  setTimeout(() => el.classList.remove('show'), 2800);
}

async function lwLogin() {
  const l = document.getElementById('lw-l').value.trim();
  const p = document.getElementById('lw-p').value;
  const err = document.getElementById('lw-err');
  const btn = document.getElementById('lw-btn');
  err.style.display = 'none';
  if (!l || !p) { err.textContent = 'Fill in all fields'; err.style.display = 'block'; return; }
  btn.disabled = true; btn.textContent = 'Signing in…';
  try {
    const r = await fetch(API + '/api/login.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ login: l, password: p })
    });
    const data = await r.json();
    if (data.error) { err.textContent = data.error; err.style.display = 'block'; btn.disabled = false; btn.textContent = 'Sign In'; return; }
    token = data.token;
    localStorage.setItem('jl_token', token);
    me = data.user;
    checkAccess();
  } catch { err.textContent = 'Connection error'; err.style.display = 'block'; btn.disabled = false; btn.textContent = 'Sign In'; }
}

function checkAccess() {
  if (!me) return;
  if (!['admin','staff'].includes(me.role)) {
    const err = document.getElementById('lw-err');
    err.textContent = 'You do not have staff access.';
    err.style.display = 'block';
    token = ''; localStorage.removeItem('jl_token');
    return;
  }
  showApp();
}

function showApp() {
  document.getElementById('login-wall').style.display = 'none';
  document.getElementById('topbar').style.display = 'flex';
  document.getElementById('main-page').style.display = 'block';
  document.getElementById('tb-av').textContent = (me.username||'?')[0].toUpperCase();
  document.getElementById('tb-name').textContent = me.username;
  document.getElementById('tb-role').textContent = me.role === 'admin' ? 'Admin' : 'Staff';
  loadModLog();
  loadReportStats();
  loadReports();
  loadMpPending();
}

function doLogout() { token = ''; localStorage.removeItem('jl_token'); location.reload(); }

async function searchUsers() {
  const q = document.getElementById('user-search').value.trim();
  if (q.length < 2) { toast('Type at least 2 characters','er'); return; }
  const r = await api('/api/user.php?action=search&q=' + encodeURIComponent(q));
  const users = r.users || [];
  const el = document.getElementById('user-results');
  if (!users.length) { el.innerHTML = '<div class="empty">No users found</div>'; return; }
  el.innerHTML = users.map(u => `
    <div class="user-result">
      <div class="u-av">${esc(u.username[0].toUpperCase())}</div>
      <div class="u-info">
        <div class="u-name">
          ${esc(u.username)}
          ${u.role==='admin' ? '<span class="tag tag-admin">Admin</span>' : ''}
          ${u.role==='staff' ? '<span class="tag tag-staff">Staff</span>' : ''}
          ${u.role==='media' ? '<span class="tag tag-media">Media</span>' : ''}
          ${u.banned ? '<span class="tag tag-banned">Banned</span>' : ''}
          ${u.plusMember ? '<span class="tag tag-plus">Plus</span>' : ''}
        </div>
        <div class="u-sub">${esc(u.mcUsername||'No MC username')} · ${esc(u.status||'offline')}</div>
      </div>
      <div class="u-actions">
        <button class="btn-warn" onclick="warnUser(${u.id},'${esc(u.username)}')">⚠️ Warn</button>
        ${u.banned
          ? `<button class="btn-unban" onclick="unbanUser(${u.id},'${esc(u.username)}')">✓ Unban</button>`
          : `<button class="btn-ban" onclick="banUser(${u.id},'${esc(u.username)}')">🚫 Ban</button>`}
      </div>
    </div>`).join('');
}

async function warnUser(id, name) {
  const reason = prompt(`Warn reason for ${name} (optional):`);
  if (reason === null) return;
  const r = await api('/api/moderation.php?action=warn', { method:'POST', body: JSON.stringify({userId:id,reason}) });
  if (r.ok) { toast(`${name} warned`); loadModLog(); searchUsers(); }
  else toast(r.error||'Failed','er');
}

async function banUser(id, name) {
  const reason = prompt(`Ban reason for ${name}:`);
  if (!reason) return;
  const r = await api('/api/moderation.php?action=ban', { method:'POST', body: JSON.stringify({userId:id,reason}) });
  if (r.ok) { toast(`${name} banned`); loadModLog(); searchUsers(); }
  else toast(r.error||'Failed','er');
}

async function unbanUser(id, name) {
  if (!confirm(`Unban ${name}?`)) return;
  const r = await api('/api/moderation.php?action=unban', { method:'POST', body: JSON.stringify({userId:id}) });
  if (r.ok) { toast(`${name} unbanned`); loadModLog(); searchUsers(); }
  else toast(r.error||'Failed','er');
}

async function searchClans() {
  const q = document.getElementById('clan-search').value.trim();
  const r = await api('/api/clans.php?action=search&q=' + encodeURIComponent(q));
  const clans = r.clans || [];
  const el = document.getElementById('clan-results');
  if (!clans.length) { el.innerHTML = '<div class="empty">No clans found</div>'; return; }
  el.innerHTML = clans.map(c => `
    <div class="clan-result">
      <div class="clan-tag-pill" style="background:${esc(c.color||'#7c3aed')}">[${esc(c.tag)}]</div>
      <div class="c-info">
        <div class="c-name">${esc(c.name)}</div>
        <div class="c-sub">Owner: ${esc(c.owner_name)} · ${c.member_count||0} members</div>
      </div>
      <button class="btn-del" onclick="deleteClan(${c.id},'${esc(c.name)}')">🗑 Delete Clan</button>
    </div>`).join('');
}

async function deleteClan(id, name) {
  if (!confirm(`Delete clan "${name}"? This will remove all members from the clan.`)) return;
  const r = await api('/api/clans.php?action=delete', { method:'POST', body: JSON.stringify({clan_id:id}) });
  if (r.ok) { toast(`Clan "${name}" deleted`); searchClans(); }
  else toast(r.error||'Failed','er');
}

async function loadModLog() {
  const el = document.getElementById('mod-log');
  try {
    const r = await api('/api/reports.php?action=activity');
    const activity = r.activity || [];
    if (!activity.length) { el.innerHTML = '<div class="empty">No recent actions</div>'; return; }
    el.innerHTML = activity.map(a => {
      const isStatusChange = a.message.startsWith('changed status to');
      return `
        <div class="act-card${isStatusChange ? ' status-change' : ''}">
          <div class="act-header">
            <span class="act-staff">${esc(a.staff_name||'Staff')}</span>
          </div>
          <div class="act-action">${esc(a.message)}</div>
          <div class="act-report">#${a.report_id} · ${esc(a.report_subject)}</div>
          <div class="act-reporter">by ${esc(a.reporter_name||'Unknown')}</div>
          <div class="act-time">${new Date(a.created_at).toLocaleDateString(undefined,{month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'})}</div>
        </div>`;
    }).join('');
  } catch { el.innerHTML = '<div class="empty">Could not load activity</div>'; }
}

/* ═══ REPORTS ═══════════════════════════════════════════════════ */
let rpFilter = 'open';

async function loadReportStats() {
  try {
    const r = await api('/api/reports.php?action=stats');
    document.getElementById('rp-cnt-open').textContent = r.open || 0;
    document.getElementById('rp-cnt-in_progress').textContent = r.in_progress || 0;
    document.getElementById('rp-cnt-resolved').textContent = r.resolved || 0;
    document.getElementById('rp-cnt-dismissed').textContent = r.dismissed || 0;
    document.getElementById('rp-cnt-total').textContent = r.total || 0;
  } catch {}
}

function filterReports(status) {
  rpFilter = status;
  document.querySelectorAll('.rp-stat').forEach(el => el.classList.toggle('active', el.dataset.filter === status));
  loadReports();
}

async function loadReports() {
  const el = document.getElementById('rp-list');
  el.innerHTML = '<div class="empty">Loading…</div>';
  try {
    const params = rpFilter === 'all' ? '' : '&status=' + rpFilter;
    const r = await api('/api/reports.php?action=list' + params);
    const list = r.reports || [];
    if (!list.length) { el.innerHTML = '<div class="empty">No reports found</div>'; return; }
    el.innerHTML = list.map(rp => `
      <div class="rp-item" id="rp-${rp.id}">
        <div class="rp-top">
          <span class="rp-tag rp-cat-${esc(rp.category)}">${esc(rp.category)}</span>
          <span class="rp-tag rp-st-${esc(rp.status)}">${esc(rp.status.replace('_',' '))}</span>
          <span style="font-size:11px;color:var(--w3)">by ${esc(rp.reporter_username || 'Unknown')}</span>
          ${rp.reported_user ? `<span style="font-size:11px;color:var(--red)">→ ${esc(rp.reported_user)}</span>` : ''}
          <span style="font-size:10px;color:var(--w4);margin-left:auto">#${rp.id}</span>
        </div>
        <div class="rp-subject">${esc(rp.subject)}</div>
        <div class="rp-desc" id="rp-desc-${rp.id}" onclick="this.classList.toggle('expanded')">${esc(rp.description)}</div>
        ${rp.staff_note ? `<div class="rp-staff-note"><strong>Note:</strong> ${esc(rp.staff_note)}</div>` : ''}
        <div class="rp-meta">${new Date(rp.created_at).toLocaleDateString(undefined,{month:'short',day:'numeric',year:'numeric',hour:'2-digit',minute:'2-digit'})}${rp.updated_at ? ' · Updated ' + new Date(rp.updated_at).toLocaleDateString(undefined,{month:'short',day:'numeric'}) : ''}</div>
        <div class="rp-actions">
          <button class="btn-resolve" onclick="openChatView(${rp.id})">Open Chat</button>
        </div>
      </div>`).join('');
  } catch { el.innerHTML = '<div class="empty">Could not load reports</div>'; }
}

/* ═══ CHAT VIEW ═════════════════════════════════════════════════════ */
let chatReportId = null;
let chatPollInterval = null;

async function openChatView(reportId) {
  chatReportId = reportId;
  document.getElementById('chat-view').classList.add('active');
  document.getElementById('chat-input').value = '';
  await loadChatMessages();
  if (chatPollInterval) clearInterval(chatPollInterval);
  chatPollInterval = setInterval(loadChatMessages, 10000);
  document.getElementById('chat-input').focus();
}

function closeChatView() {
  chatReportId = null;
  if (chatPollInterval) clearInterval(chatPollInterval);
  document.getElementById('chat-view').classList.remove('active');
  loadReportStats();
  loadReports();
}

async function loadChatMessages() {
  if (!chatReportId) return;
  try {
    const r = await api('/api/reports.php?action=messages&id=' + chatReportId);
    const report = r.report || {};
    const messages = r.messages || [];

    document.getElementById('chat-title').textContent = 'Report #' + report.id + ': ' + esc(report.subject||'');
    document.getElementById('chat-sub').textContent = esc(report.reporter_name||'Reporter') + ' · ' + esc(report.reported_user||'Unknown');
    document.getElementById('chat-status').value = report.status || 'open';

    const msgEl = document.getElementById('chat-messages');
    if (!messages.length) { msgEl.innerHTML = '<div class="msg-system">No messages yet</div>'; return; }

    msgEl.innerHTML = messages.map(m => {
      if (m.message.startsWith('changed status to')) {
        return `<div class="msg-system">${esc(m.message)}</div>`;
      }
      return `
        <div class="msg${m.is_staff ? ' staff' : ''}">
          <div class="msg-av">${esc(m.username[0].toUpperCase())}</div>
          <div class="msg-content">
            <div class="msg-bubble">${esc(m.message)}</div>
            <div class="msg-time">${new Date(m.created_at).toLocaleTimeString(undefined,{hour:'2-digit',minute:'2-digit'})}</div>
          </div>
        </div>`;
    }).join('');

    msgEl.scrollTop = msgEl.scrollHeight;
  } catch (e) {
    console.error('Failed to load chat messages', e);
  }
}

async function sendChatMessage() {
  if (!chatReportId) return;
  const msg = document.getElementById('chat-input').value.trim();
  if (!msg) return;
  document.getElementById('chat-input').value = '';
  try {
    const r = await api('/api/reports.php?action=send-message', {
      method: 'POST',
      body: JSON.stringify({ report_id: chatReportId, message: msg })
    });
    if (r.error) { toast(r.error, 'er'); return; }
    await loadChatMessages();
  } catch { toast('Failed to send message', 'er'); }
}

async function updateReportStatus() {
  if (!chatReportId) return;
  const status = document.getElementById('chat-status').value;
  try {
    const r = await api('/api/reports.php?action=update', {
      method: 'POST',
      body: JSON.stringify({ id: chatReportId, status: status })
    });
    if (r.error) { toast(r.error, 'er'); return; }
    toast('Status updated to ' + status);
    await loadChatMessages();
  } catch { toast('Failed to update status', 'er'); }
}

async function loadMpPending() {
  document.getElementById('mp-btn-pending').style.background = 'rgba(124,58,237,.1)';
  document.getElementById('mp-btn-pending').style.borderColor = 'rgba(124,58,237,.4)';
  document.getElementById('mp-btn-pending').style.color = 'var(--pl)';
  document.getElementById('mp-btn-history').style.background = 'transparent';
  document.getElementById('mp-btn-history').style.borderColor = 'var(--line)';
  document.getElementById('mp-btn-history').style.color = 'var(--w3)';
  const el = document.getElementById('mp-queue');
  el.innerHTML = '<div class="empty">Loading…</div>';
  try {
    const r = await api('/api/marketplace.php?action=pending');
    const items = r.listings || [];
    if (!items.length) { el.innerHTML = '<div class="empty">No pending listings</div>'; return; }
    el.innerHTML = items.map(i => `
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px">
        <img src="${API}/${esc(i.previewPath || i.texturePath)}" style="width:44px;height:44px;border-radius:8px;border:1px solid var(--line);object-fit:cover" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:700;color:var(--w)">${esc(i.name)} <span class="tag" style="background:rgba(124,58,237,.2);color:var(--pl);border:1px solid rgba(124,58,237,.4)">${esc(i.type)}</span></div>
          <div style="font-size:11px;color:var(--w3);margin-top:2px">by ${esc(i.sellerName)} · ${i.price} pts</div>
        </div>
        <div style="display:flex;gap:6px">
          <button class="btn-unban" onclick="mpReview(${i.id},'approve')">Approve</button>
          <button class="btn-ban" onclick="mpReview(${i.id},'reject')">Reject</button>
        </div>
      </div>`).join('');
  } catch { el.innerHTML = '<div class="empty">Failed to load</div>'; }
}

async function loadMpHistory() {
  document.getElementById('mp-btn-history').style.background = 'rgba(124,58,237,.1)';
  document.getElementById('mp-btn-history').style.borderColor = 'rgba(124,58,237,.4)';
  document.getElementById('mp-btn-history').style.color = 'var(--pl)';
  document.getElementById('mp-btn-pending').style.background = 'transparent';
  document.getElementById('mp-btn-pending').style.borderColor = 'var(--line)';
  document.getElementById('mp-btn-pending').style.color = 'var(--w3)';
  const el = document.getElementById('mp-queue');
  el.innerHTML = '<div class="empty">Loading…</div>';
  try {
    const r = await api('/api/marketplace.php?action=review-history');
    const items = r.listings || [];
    if (!items.length) { el.innerHTML = '<div class="empty">No review history</div>'; return; }
    el.innerHTML = items.map(i => {
      const sc = i.status === 'approved' ? 'var(--green)' : 'var(--red)';
      const sb = i.status === 'approved' ? 'rgba(74,222,128,.12)' : 'rgba(248,113,113,.12)';
      return `
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--s3);border:1px solid var(--line);border-radius:10px;margin-bottom:8px">
        <img src="${API}/${esc(i.previewPath || i.texturePath)}" style="width:44px;height:44px;border-radius:8px;border:1px solid var(--line);object-fit:cover" onerror="this.style.display='none'">
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:700;color:var(--w)">${esc(i.name)} <span class="tag" style="background:${sb};color:${sc}">${esc(i.status)}</span></div>
          <div style="font-size:11px;color:var(--w3);margin-top:2px">by ${esc(i.sellerName)} · ${i.price} pts${i.reviewerName ? ' · by ' + esc(i.reviewerName) : ''}${i.reviewNote ? ' · "' + esc(i.reviewNote) + '"' : ''}</div>
        </div>
      </div>`;
    }).join('');
  } catch { el.innerHTML = '<div class="empty">Failed to load</div>'; }
}

async function mpReview(id, decision) {
  const note = decision === 'reject' ? prompt('Rejection reason (optional):') || '' : '';
  if (decision === 'reject' && note === null) return;
  try {
    const r = await api('/api/marketplace.php?action=review', {
      method: 'POST',
      body: JSON.stringify({ id, decision, note })
    });
    if (r.ok) { toast('Listing ' + decision + 'd'); loadMpPending(); }
    else toast(r.error || 'Failed', 'er');
  } catch { toast('Failed', 'er'); }
}

async function init() {
  if (!token) return;
  try {
    const r = await api('/api/user.php?action=me');
    if (r.user && ['admin','staff'].includes(r.user.role)) {
      me = r.user; showApp();
    } else {
      token = ''; localStorage.removeItem('jl_token');
    }
  } catch {}
}
init();
</script>
</body>
</html>
