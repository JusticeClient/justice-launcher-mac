<?php
if (file_exists(__DIR__ . '/includes/config.php')) require_once __DIR__ . '/includes/config.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice — Report</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#05030d;--s1:#0a0818;--s2:#0e0b20;--s3:#131028;
  --line:rgba(255,255,255,.07);--line2:rgba(139,92,246,.22);
  --p:#7c3aed;--p2:#6d28d9;--pl:#a78bfa;--px:#c4b5fd;
  --red:#f87171;--green:#4ade80;--amber:#fbbf24;
  --w:#f5f3ff;--w2:rgba(245,243,255,.55);--w3:rgba(245,243,255,.25);--w4:rgba(245,243,255,.08);
  --f:'Inter',system-ui,sans-serif;--mono:'JetBrains Mono',monospace;
}
html,body{height:100%;font-family:var(--f);background:var(--bg);color:var(--w);-webkit-font-smoothing:antialiased}

.topbar{height:52px;display:flex;align-items:center;padding:0 24px;gap:12px;background:rgba(5,3,13,.9);backdrop-filter:blur(20px);border-bottom:1px solid var(--line);position:fixed;top:0;left:0;right:0;z-index:100}
.topbar-logo{display:flex;align-items:center;gap:9px;text-decoration:none;font-weight:800;font-size:14px;color:var(--w);letter-spacing:-.03em}
.logo-sq{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;box-shadow:0 0 14px rgba(124,58,237,.5)}
.topbar-links{display:flex;gap:4px;align-items:center;margin-left:28px}
.topbar-links a{font-size:13px;font-weight:500;color:var(--w3);text-decoration:none;padding:6px 14px;border-radius:8px;transition:all .12s}
.topbar-links a:hover{color:var(--w);background:rgba(255,255,255,.05)}
.topbar-links a.active{color:var(--pl);background:rgba(124,58,237,.12)}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.topbar-user{font-size:13px;font-weight:600;color:var(--w2);display:flex;align-items:center;gap:8px}
.topbar-av{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.btn-logout{padding:5px 12px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:all .12s}
.btn-logout:hover{border-color:var(--red);color:var(--red)}
.btn-login{padding:6px 16px;border-radius:8px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer}

.page{padding-top:68px;min-height:100vh}
.inner{max-width:700px;margin:0 auto;padding:24px 24px 60px}
.page-hdr{margin-bottom:24px}
.page-title{font-size:28px;font-weight:800;letter-spacing:-.04em}
.page-sub{font-size:13px;color:var(--w3);margin-top:4px}

.card{background:var(--s2);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px}
.card-title{font-size:13px;font-weight:700;color:var(--w);margin-bottom:12px;display:flex;align-items:center;gap:6px}

/* Form */
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:6px}
.form-input{width:100%;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--s1);color:var(--w);font-family:var(--f);font-size:13px;outline:none;transition:border .12s}
.form-input:focus{border-color:var(--p)}
textarea.form-input{min-height:120px;resize:vertical;line-height:1.6}
.form-select{width:100%;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--s1);color:var(--w);font-family:var(--f);font-size:13px;outline:none;appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23a78bfa' viewBox='0 0 16 16'%3E%3Cpath d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;padding-right:36px}
.form-select:focus{border-color:var(--p)}

.cat-cards{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px}
.cat-card{padding:16px 12px;border-radius:12px;border:2px solid var(--line);background:var(--s1);cursor:pointer;text-align:center;transition:all .15s}
.cat-card:hover{border-color:var(--line2)}
.cat-card.selected{border-color:var(--p);background:rgba(124,58,237,.08)}
.cat-icon{font-size:24px;margin-bottom:6px}
.cat-name{font-size:12px;font-weight:700;color:var(--w)}
.cat-desc{font-size:10px;color:var(--w3);margin-top:2px}

.submit-btn{width:100%;padding:12px;border-radius:10px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:14px;font-weight:700;cursor:pointer;transition:all .12s}
.submit-btn:hover{background:var(--p2)}
.submit-btn:disabled{opacity:.5;cursor:default}

/* Report list items */
.report-item{padding:14px;background:var(--s1);border:1px solid var(--line);border-radius:10px;margin-bottom:8px}
.report-header{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap}
.report-cat{font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em}
.cat-player{background:rgba(248,113,113,.12);color:var(--red);border:1px solid rgba(248,113,113,.3)}
.cat-bug{background:rgba(251,191,36,.12);color:var(--amber);border:1px solid rgba(251,191,36,.3)}
.cat-general{background:rgba(96,165,250,.12);color:#60a5fa;border:1px solid rgba(96,165,250,.3)}
.report-status{font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em}
.st-open{background:rgba(251,191,36,.12);color:var(--amber);border:1px solid rgba(251,191,36,.3)}
.st-in_progress{background:rgba(96,165,250,.12);color:#60a5fa;border:1px solid rgba(96,165,250,.3)}
.st-resolved{background:rgba(74,222,128,.12);color:var(--green);border:1px solid rgba(74,222,128,.3)}
.st-dismissed{background:rgba(255,255,255,.06);color:var(--w3);border:1px solid rgba(255,255,255,.1)}
.report-subject{font-size:13px;font-weight:600;color:var(--w)}
.report-desc{font-size:12px;color:var(--w2);margin-top:4px;line-height:1.5}
.report-meta{font-size:10px;color:var(--w3);margin-top:8px}
.staff-note{margin-top:8px;padding:8px 12px;border-radius:8px;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);font-size:12px;color:var(--pl);line-height:1.5}

/* Report cards in list view */
.report-card{display:flex;flex-direction:column;gap:12px;padding:16px;background:var(--s1);border:1px solid var(--line);border-radius:12px;cursor:pointer;transition:all .15s}
.report-card:hover{border-color:var(--line2);background:var(--s2)}
.report-card-header{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.report-card-title{font-size:14px;font-weight:600;color:var(--w);flex:1}
.report-card-meta{display:flex;align-items:center;gap:8px;margin-top:8px;font-size:11px;color:var(--w3)}
.report-card-count{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:6px;background:rgba(124,58,237,.12);color:var(--pl);font-size:11px;font-weight:600}

/* Chat view */
.back-btn{padding:8px 12px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:13px;cursor:pointer;transition:all .12s;margin-bottom:16px}
.back-btn:hover{border-color:var(--pl);color:var(--pl)}

.chat-container{display:flex;flex-direction:column;height:calc(100vh - 150px);background:var(--s2);border:1px solid var(--line);border-radius:14px;overflow:hidden}
.chat-messages{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:12px}
.chat-message{display:flex;gap:8px;animation:slideIn .2s ease}
.chat-message.user{justify-content:flex-end}
.chat-message.staff{justify-content:flex-start}
.chat-message.system{justify-content:center}

.message-bubble{max-width:60%;padding:10px 14px;border-radius:12px;word-wrap:break-word;line-height:1.4;font-size:13px}
.message-bubble.user{background:var(--p);color:#fff;border-radius:12px 4px 12px 12px}
.message-bubble.staff{background:var(--s1);color:var(--w);border:1px solid var(--line);border-radius:4px 12px 12px 12px}
.message-system{padding:8px 12px;border-radius:8px;background:transparent;color:var(--w3);text-align:center;font-size:12px;font-style:italic}

.message-author{font-size:11px;color:var(--w3);margin-bottom:4px;font-weight:600}
.message-time{font-size:10px;color:var(--w3);margin-top:4px;opacity:.7}

.chat-input-area{padding:16px;border-top:1px solid var(--line);background:var(--s1)}
.chat-input{flex:1;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--bg);color:var(--w);font-family:var(--f);font-size:13px;outline:none;transition:border .12s}
.chat-input:focus{border-color:var(--p)}
.chat-send-btn{padding:10px 16px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:13px;font-weight:600;cursor:pointer;transition:all .12s}
.chat-send-btn:hover{background:var(--p2)}
.chat-send-btn:disabled{opacity:.5;cursor:default}

/* Login wall */
.login-wall{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;text-align:center;min-height:60vh;padding:40px}
.login-wall h2{font-size:24px;font-weight:800;letter-spacing:-.04em}
.login-wall p{font-size:13px;color:var(--w3);max-width:380px;line-height:1.6}
.lw-form{display:flex;flex-direction:column;gap:10px;width:100%;max-width:320px}
.lw-input{padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--s2);color:var(--w);font-family:var(--f);font-size:13px;outline:none}
.lw-input:focus{border-color:var(--p)}
.lw-err{font-size:12px;color:var(--red);display:none}
.lw-btn{padding:11px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:14px;font-weight:700;cursor:pointer}

.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 22px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;background:rgba(15,10,30,.92);border:1px solid var(--line2);backdrop-filter:blur(12px);z-index:999;opacity:0;transition:opacity .2s;pointer-events:none}
.toast.show{opacity:1}

@media(max-width:560px){.cat-cards{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="topbar">
  <a href="/" class="topbar-logo">
    <div class="logo-sq"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg></div>
    Justice
  </a>
  <div class="topbar-links">
    <a href="/">Home</a>
    <a href="/shop.php">Shop</a>
    <a href="/points.php">Points</a>
    <a href="/report.php" class="active">Report</a>
    <a href="/social.php">Social</a>
  </div>
  <div class="topbar-right">
    <div id="user-info" class="topbar-user" style="display:none">
      <div class="topbar-av" id="user-av">?</div>
      <span id="user-name">Player</span>
    </div>
    <button id="btn-logout" class="btn-logout" style="display:none" onclick="logout()">Sign out</button>
    <button id="btn-login-nav" class="btn-login" onclick="document.getElementById('login-wall').style.display='flex';document.getElementById('main-content').style.display='none'">Log in</button>
  </div>
</div>

<!-- Login Wall -->
<div id="login-wall" class="page" style="display:none">
  <div class="login-wall">
    <h2>Sign in to Justice</h2>
    <p>Log in to submit reports and help us keep the community safe.</p>
    <div class="lw-form">
      <input id="lw-user" class="lw-input" placeholder="Username or email" autocomplete="username">
      <input id="lw-pass" class="lw-input" type="password" placeholder="Password" autocomplete="current-password">
      <div id="lw-err" class="lw-err"></div>
      <button class="lw-btn" onclick="doLogin()">Sign In</button>
      <div style="font-size:12px;color:var(--w3);text-align:center;margin-top:4px">Don't have an account? <a href="/register.php" style="color:var(--pl);text-decoration:none;font-weight:600">Sign up</a></div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div id="main-content" class="page" style="display:none">
  <div class="inner">
    <div class="page-hdr">
      <div class="page-title">📝 Submit a Report</div>
      <div class="page-sub">Report players, bugs, or general issues. Staff will review your report and take action.</div>
    </div>

    <!-- Report Form -->
    <div class="card">
      <div class="card-title">New Report</div>

      <div class="form-label" style="margin-bottom:8px">Category</div>
      <div class="cat-cards" id="cat-cards">
        <div class="cat-card selected" data-cat="player" onclick="selectCat('player')">
          <div class="cat-icon">🚨</div>
          <div class="cat-name">Player</div>
          <div class="cat-desc">Report a player</div>
        </div>
        <div class="cat-card" data-cat="bug" onclick="selectCat('bug')">
          <div class="cat-icon">🐛</div>
          <div class="cat-name">Bug</div>
          <div class="cat-desc">Launcher or server bug</div>
        </div>
        <div class="cat-card" data-cat="general" onclick="selectCat('general')">
          <div class="cat-icon">💬</div>
          <div class="cat-name">General</div>
          <div class="cat-desc">Other issue or suggestion</div>
        </div>
      </div>

      <div id="player-field" class="form-group">
        <label class="form-label">Reported Player Username</label>
        <input id="rp-player" class="form-input" type="text" placeholder="Enter the player's username">
      </div>

      <div class="form-group">
        <label class="form-label">Subject</label>
        <input id="rp-subject" class="form-input" type="text" placeholder="Short summary of the issue" maxlength="200">
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="rp-desc" class="form-input" placeholder="Describe the issue in detail. Include any relevant info like timestamps, server names, error messages, steps to reproduce, etc."></textarea>
      </div>

      <button id="submit-btn" class="submit-btn" onclick="submitReport()">Submit Report</button>
    </div>

    <!-- My Reports (List View) -->
    <div id="reports-list-view" class="card">
      <div class="card-title">My Reports</div>
      <div id="my-reports" style="font-size:13px;color:var(--w3)">Loading…</div>
    </div>

    <!-- Chat View -->
    <div id="chat-view" style="display:none">
      <button id="back-btn" class="back-btn" onclick="closeChatView()">← Back to reports</button>
      <div class="chat-container">
        <div id="chat-messages" class="chat-messages"></div>
        <div id="chat-input-area" class="chat-input-area">
          <div style="display:flex;gap:8px;margin-bottom:8px">
            <input id="chat-input" class="chat-input" type="text" placeholder="Type a message..." />
            <button id="chat-send-btn" class="chat-send-btn" onclick="sendMessage()">Send</button>
          </div>
          <div id="chat-disabled-note" style="display:none;text-align:center;font-size:12px;color:var(--w3);padding:8px">This report is closed. You cannot send new messages.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
const API = '';
let token = localStorage.getItem('jl_token') || null;
let user = null;
let selectedCat = 'player';

function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.borderColor = type === 'error' ? 'rgba(248,113,113,.4)' : 'rgba(34,197,94,.4)';
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 3000);
}
async function api(url, opts = {}) {
  if (!opts.headers) opts.headers = {};
  opts.headers['Content-Type'] = 'application/json';
  if (token) opts.headers['Authorization'] = 'Bearer ' + token;
  const r = await fetch(API + url, opts);
  return r.json();
}

// Category selection
function selectCat(cat) {
  selectedCat = cat;
  document.querySelectorAll('.cat-card').forEach(c => c.classList.toggle('selected', c.dataset.cat === cat));
  document.getElementById('player-field').style.display = cat === 'player' ? 'block' : 'none';
}

// Auth
async function init() {
  if (!token) { showLoginWall(); return; }
  try {
    const r = await api('/api/user.php?action=me');
    if (r.error) { showLoginWall(); return; }
    user = r.user || r;
    showApp();
    loadMyReports();
  } catch { showLoginWall(); }
}

function showLoginWall() {
  document.getElementById('login-wall').style.display = 'block';
  document.getElementById('main-content').style.display = 'none';
  document.getElementById('btn-login-nav').style.display = '';
  document.getElementById('btn-logout').style.display = 'none';
  document.getElementById('user-info').style.display = 'none';
}
function showApp() {
  document.getElementById('login-wall').style.display = 'none';
  document.getElementById('main-content').style.display = 'block';
  document.getElementById('btn-login-nav').style.display = 'none';
  document.getElementById('btn-logout').style.display = '';
  document.getElementById('user-info').style.display = 'flex';
  if (user) {
    document.getElementById('user-av').textContent = (user.username || '?')[0].toUpperCase();
    document.getElementById('user-name').textContent = user.username || 'Player';
  }
}
async function doLogin() {
  const l = document.getElementById('lw-user').value.trim();
  const p = document.getElementById('lw-pass').value;
  const e = document.getElementById('lw-err');
  if (!l || !p) { e.textContent = 'Enter username and password'; e.style.display = 'block'; return; }
  try {
    const r = await fetch(API + '/api/login.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ login: l, password: p })
    });
    const d = await r.json();
    if (d.error) { e.textContent = d.error; e.style.display = 'block'; return; }
    token = d.token; localStorage.setItem('jl_token', token); user = d.user || null;
    showApp(); loadMyReports();
  } catch { e.textContent = 'Could not connect.'; e.style.display = 'block'; }
}
function logout() { token = null; user = null; localStorage.removeItem('jl_token'); showLoginWall(); }

// Submit
async function submitReport() {
  const btn = document.getElementById('submit-btn');
  const subject = document.getElementById('rp-subject').value.trim();
  const desc = document.getElementById('rp-desc').value.trim();
  const player = document.getElementById('rp-player').value.trim();

  if (!subject || subject.length < 3) { showToast('Subject must be at least 3 characters', 'error'); return; }
  if (!desc || desc.length < 10) { showToast('Description must be at least 10 characters', 'error'); return; }
  if (selectedCat === 'player' && !player) { showToast('Enter the player username you are reporting', 'error'); return; }

  btn.disabled = true; btn.textContent = 'Submitting...';
  try {
    const r = await api('/api/reports.php?action=submit', {
      method: 'POST',
      body: JSON.stringify({
        category: selectedCat,
        subject: subject,
        description: desc,
        reported_user: player,
      })
    });
    if (r.error) { showToast(r.error, 'error'); btn.disabled = false; btn.textContent = 'Submit Report'; return; }
    showToast(r.message || 'Report submitted!');
    // Clear form
    document.getElementById('rp-subject').value = '';
    document.getElementById('rp-desc').value = '';
    document.getElementById('rp-player').value = '';
    loadMyReports();
  } catch {
    showToast('Failed to submit', 'error');
  }
  btn.disabled = false; btn.textContent = 'Submit Report';
}

// My reports
let currentReportId = null;
let chatPollInterval = null;

async function loadMyReports() {
  const el = document.getElementById('my-reports');
  try {
    const r = await api('/api/reports.php?action=my-reports');
    const list = r.reports || [];
    if (!list.length) { el.innerHTML = '<div style="color:var(--w3);text-align:center;padding:16px">No reports yet.</div>'; return; }
    el.innerHTML = list.map(rp => {
      const catClass = 'cat-' + rp.category;
      const stClass = 'st-' + rp.status;
      const dateStr = new Date(rp.created_at).toLocaleDateString(undefined, {month:'short',day:'numeric',year:'numeric'});
      const msgCount = rp.message_count || 0;
      return `<div class="report-card" onclick="openChatView(${rp.id})">
        <div class="report-card-header">
          <span class="report-cat ${catClass}">${esc(rp.category)}</span>
          <span class="report-status ${stClass}">${esc(rp.status.replace('_',' '))}</span>
        </div>
        <div class="report-card-title">${esc(rp.subject)}</div>
        <div class="report-card-meta">
          <span>${dateStr}</span>
          <span class="report-card-count">💬 ${msgCount} message${msgCount !== 1 ? 's' : ''}</span>
        </div>
      </div>`;
    }).join('');
  } catch { el.innerHTML = '<div style="color:var(--w3)">Could not load reports.</div>'; }
}

async function openChatView(reportId) {
  currentReportId = reportId;
  document.getElementById('reports-list-view').style.display = 'none';
  document.getElementById('chat-view').style.display = 'block';
  await loadChatMessages();
  // Start polling for new messages
  if (chatPollInterval) clearInterval(chatPollInterval);
  chatPollInterval = setInterval(() => {
    if (currentReportId) loadChatMessages();
  }, 10000);
  // Focus on input
  document.getElementById('chat-input').focus();
}

function closeChatView() {
  currentReportId = null;
  if (chatPollInterval) clearInterval(chatPollInterval);
  document.getElementById('chat-view').style.display = 'none';
  document.getElementById('reports-list-view').style.display = 'block';
  loadMyReports();
}

async function loadChatMessages() {
  if (!currentReportId) return;
  const el = document.getElementById('chat-messages');
  try {
    const r = await api(`/api/reports.php?action=messages&id=${currentReportId}`);
    const report = r.report || {};
    const messages = r.messages || [];

    // Check if report is closed
    const isClosed = report.status === 'resolved' || report.status === 'dismissed';
    document.getElementById('chat-input').style.display = isClosed ? 'none' : 'block';
    document.getElementById('chat-send-btn').style.display = isClosed ? 'none' : 'block';
    document.getElementById('chat-disabled-note').style.display = isClosed ? 'block' : 'none';
    document.getElementById('chat-input').disabled = isClosed;

    if (!messages.length) {
      el.innerHTML = '<div style="color:var(--w3);text-align:center;padding:20px">No messages yet. Start a conversation by sending a message below.</div>';
      return;
    }

    el.innerHTML = messages.map(msg => {
      const isSystem = msg.message.startsWith('changed status to');
      const isUser = !msg.is_staff;
      const time = new Date(msg.created_at).toLocaleTimeString(undefined, {hour:'2-digit',minute:'2-digit'});

      if (isSystem) {
        return `<div class="chat-message system">
          <div class="message-system">${esc(msg.message)}</div>
        </div>`;
      }

      const msgClass = isUser ? 'user' : 'staff';
      return `<div class="chat-message ${msgClass}">
        <div>
          ${!isUser ? `<div class="message-author">Staff</div>` : ''}
          <div class="message-bubble ${msgClass}">${esc(msg.message)}</div>
          <div class="message-time">${time}</div>
        </div>
      </div>`;
    }).join('');

    // Auto-scroll to bottom
    el.scrollTop = el.scrollHeight;
  } catch { document.getElementById('chat-messages').innerHTML = '<div style="color:var(--w3)">Could not load messages.</div>'; }
}

async function sendMessage() {
  if (!currentReportId) return;
  const input = document.getElementById('chat-input');
  const message = input.value.trim();
  if (!message) return;

  const btn = document.getElementById('chat-send-btn');
  btn.disabled = true;

  try {
    const r = await api('/api/reports.php?action=send-message', {
      method: 'POST',
      body: JSON.stringify({ report_id: currentReportId, message: message })
    });
    if (r.error) { showToast(r.error, 'error'); btn.disabled = false; return; }
    input.value = '';
    await loadChatMessages();
  } catch { showToast('Failed to send message', 'error'); }
  btn.disabled = false;
}

document.getElementById('chat-input')?.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
});

document.getElementById('lw-pass').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
init();
</script>
</body>
</html>
