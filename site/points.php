<?php
if (file_exists(__DIR__ . '/includes/config.php')) require_once __DIR__ . '/includes/config.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice — Points</title>
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

/* Topbar */
.topbar{
  height:52px;display:flex;align-items:center;padding:0 24px;gap:12px;
  background:rgba(5,3,13,.9);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--line);position:fixed;top:0;left:0;right:0;z-index:100;
}
.topbar-logo{display:flex;align-items:center;gap:9px;text-decoration:none;font-weight:800;font-size:14px;color:var(--w);letter-spacing:-.03em}
.logo-sq{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;box-shadow:0 0 14px rgba(124,58,237,.5)}
.topbar-links{display:flex;gap:4px;align-items:center;margin-left:28px}
.topbar-links a{font-size:13px;font-weight:500;color:var(--w3);text-decoration:none;padding:6px 14px;border-radius:8px;transition:all .12s}
.topbar-links a:hover{color:var(--w);background:rgba(255,255,255,.05)}
.topbar-links a.active{color:var(--pl);background:rgba(124,58,237,.12)}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.topbar-user{font-size:13px;font-weight:600;color:var(--w2)}
.topbar-av{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.points-badge{display:flex;align-items:center;gap:5px;padding:4px 12px;border-radius:7px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.25);font-size:12px;font-weight:700;color:var(--pl)}
.btn-logout{padding:5px 12px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:all .12s}
.btn-logout:hover{border-color:var(--red);color:var(--red)}
.btn-login{padding:6px 16px;border-radius:8px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer;transition:all .12s}
.btn-login:hover{background:var(--p2)}

/* Page */
.page{padding-top:68px;min-height:100vh}
.inner{max-width:900px;margin:0 auto;padding:24px 24px 60px;width:100%}
.page-hdr{margin-bottom:24px}
.page-title{font-size:28px;font-weight:800;letter-spacing:-.04em}
.page-sub{font-size:13px;color:var(--w3);margin-top:4px}

/* Cards */
.card{background:var(--s2);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:16px}
.card-title{font-size:13px;font-weight:700;color:var(--w);margin-bottom:6px;display:flex;align-items:center;gap:6px}
.card-desc{font-size:12px;color:var(--w3);margin-bottom:14px;line-height:1.5}

/* Balance hero */
.balance-card{
  background:linear-gradient(135deg,#1e1040,#2d1b69);
  border:1px solid rgba(124,58,237,.4);border-radius:16px;
  padding:28px;margin-bottom:20px;text-align:center;
}
.balance-label{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--pl);margin-bottom:8px}
.balance-val{font-size:52px;font-weight:900;color:var(--w);margin-bottom:4px}
.balance-sub{font-size:13px;color:var(--pl);margin-bottom:18px}
.balance-info{background:rgba(0,0,0,.3);border-radius:10px;padding:12px 16px;font-size:12px;color:var(--w2);line-height:1.6}

/* Daily reward */
.daily-card{background:linear-gradient(135deg,rgba(34,197,94,.08),rgba(16,185,129,.05));border:1px solid rgba(34,197,94,.25);border-radius:14px;padding:20px;margin-bottom:16px}
.streak-bar{display:flex;gap:6px;margin-bottom:10px}
.streak-day{flex:1;text-align:center;padding:10px 4px;border-radius:10px;min-width:0;transition:all .2s}
.streak-day .day-label{font-size:9px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
.streak-day .day-pts{font-size:15px;font-weight:800;margin-top:3px}
.claim-btn{
  padding:10px 24px;border-radius:10px;border:none;
  background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;
  font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer;
  box-shadow:0 4px 14px rgba(34,197,94,.3);transition:all .15s;
}
.claim-btn:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(34,197,94,.4)}
.claim-btn:disabled{opacity:.5;cursor:default;transform:none;box-shadow:none;background:rgba(255,255,255,.08);color:var(--w3)}

/* Playtime */
.playtime-card{background:linear-gradient(135deg,rgba(96,165,250,.08),rgba(59,130,246,.05));border:1px solid rgba(96,165,250,.25);border-radius:14px;padding:20px;margin-bottom:16px}
.stat-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.stat-box{background:rgba(0,0,0,.25);border-radius:10px;padding:14px;text-align:center}
.stat-label{font-size:10px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--w3);margin-bottom:4px}
.stat-val{font-size:22px;font-weight:800;color:#60a5fa}
.stat-sub{font-size:11px;color:var(--w3);margin-top:3px}

/* Withdraw */
.wd-card{background:var(--s2);border:1px solid rgba(124,58,237,.3);border-radius:14px;padding:20px}
.wd-input{width:100%;padding:10px 13px;border-radius:8px;border:1px solid var(--line);background:var(--s1);color:var(--w);font-family:var(--f);font-size:13px;outline:none;transition:border .12s}
.wd-input:focus{border-color:var(--p)}
.wd-btn{padding:10px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer;width:100%;transition:all .12s}
.wd-btn:hover{background:var(--p2)}

/* Log items */
.log-item{display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.log-reason{font-size:12px;color:var(--w2);flex:1}
.log-amount{font-size:12px;font-weight:700}
.log-date{font-size:10px;color:var(--w3)}

/* Referral */
.ref-input{flex:1;padding:9px 12px;border-radius:8px;border:1px solid var(--line);background:var(--s1);color:var(--w2);font-family:var(--mono);font-size:12px;outline:none}
.ref-copy{padding:9px 16px;border-radius:8px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:12px;font-weight:700;cursor:pointer}
.ref-copy:hover{background:var(--p2)}

/* Login wall */
.login-wall{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;text-align:center;min-height:60vh;padding:40px}
.login-wall h2{font-size:24px;font-weight:800;letter-spacing:-.04em}
.login-wall p{font-size:13px;color:var(--w3);max-width:380px;line-height:1.6}
.lw-form{display:flex;flex-direction:column;gap:10px;width:100%;max-width:320px}
.lw-input{padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--s2);color:var(--w);font-family:var(--f);font-size:13px;outline:none}
.lw-input:focus{border-color:var(--p)}
.lw-err{font-size:12px;color:var(--red);display:none}
.lw-btn{padding:11px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:14px;font-weight:700;cursor:pointer}

/* Toast */
.toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);padding:10px 22px;border-radius:10px;font-size:13px;font-weight:600;color:#fff;background:rgba(15,10,30,.92);border:1px solid var(--line2);backdrop-filter:blur(12px);z-index:999;opacity:0;transition:opacity .2s;pointer-events:none}
.toast.show{opacity:1}

@media(max-width:640px){
  .stat-grid{grid-template-columns:1fr}
  .streak-bar{flex-wrap:wrap}
  .streak-day{min-width:calc(25% - 6px)}
}
</style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
  <a href="/" class="topbar-logo">
    <div class="logo-sq">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg>
    </div>
    Justice
  </a>
  <div class="topbar-links">
    <a href="/">Home</a>
    <a href="/shop.php">Shop</a>
    <a href="/points.php" class="active">Points</a>
    <a href="/social.php">Social</a>
  </div>
  <div class="topbar-right">
    <div id="points-chip" class="points-badge" style="display:none">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span id="top-pts">0</span> pts
    </div>
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
    <p>Log in to view your points balance, claim daily rewards, track playtime, and manage withdrawals.</p>
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
      <div class="page-title">⭐ Justice Points</div>
      <div class="page-sub">Earn points by playing, logging in daily, and referring friends — worth real DonutSMP coins</div>
    </div>

    <!-- Balance -->
    <div class="balance-card">
      <div class="balance-label">Your Balance</div>
      <div class="balance-val" id="balance-val">—</div>
      <div class="balance-sub" id="balance-sub">points</div>
      <div class="balance-info">
        💰 <strong style="color:var(--w)">1 point = 1,000,000 DonutSMP coins</strong><br>
        10 points = 10,000,000 DonutSMP coins
      </div>
    </div>

    <!-- Daily Login Reward -->
    <div class="daily-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
        <div>
          <div class="card-title">🎁 Daily Login Reward</div>
          <div style="font-size:12px;color:var(--w3)">Log in every day for bigger rewards. Streak resets if you miss a day!</div>
        </div>
        <button id="claim-btn" class="claim-btn" onclick="claimDaily()">Claim Now</button>
      </div>
      <div id="streak-bar" class="streak-bar"></div>
      <div id="daily-msg" style="font-size:12px;color:var(--w3)">Loading…</div>
    </div>

    <!-- Playtime Stats -->
    <div class="playtime-card">
      <div class="card-title">🎮 Playtime Rewards</div>
      <div class="card-desc">Earn 1 point for every 30 minutes of Minecraft playtime. Points are awarded automatically while you play in the launcher!</div>
      <div class="stat-grid">
        <div class="stat-box">
          <div class="stat-label">Today</div>
          <div class="stat-val" id="pt-today">0m</div>
          <div class="stat-sub" id="pt-today-pts">0 pts earned</div>
        </div>
        <div class="stat-box">
          <div class="stat-label">All Time</div>
          <div class="stat-val" id="pt-total">0h</div>
          <div class="stat-sub" id="pt-total-pts">0 pts earned</div>
        </div>
        <div class="stat-box">
          <div class="stat-label">Sessions</div>
          <div class="stat-val" id="pt-sessions">0</div>
          <div class="stat-sub">total games</div>
        </div>
      </div>
    </div>

    <!-- Referral -->
    <div class="card">
      <div class="card-title">🔗 Your Referral Link</div>
      <div class="card-desc">Share this link — you get 10 points for every unique friend who signs up.</div>
      <div style="display:flex;gap:8px">
        <input id="ref-url" class="ref-input" type="text" readonly value="Loading…">
        <button class="ref-copy" onclick="copyRef()">Copy Link</button>
      </div>
    </div>

    <!-- Referrals List -->
    <div class="card">
      <div class="card-title">People You Referred</div>
      <div id="ref-list" style="font-size:13px;color:var(--w3)">Loading…</div>
    </div>

    <!-- Points History -->
    <div class="card">
      <div class="card-title">Points History</div>
      <div id="pts-log" style="font-size:13px;color:var(--w3)">Loading…</div>
    </div>

    <!-- Withdraw -->
    <div class="wd-card">
      <div class="card-title">💰 Withdraw Points</div>
      <div class="card-desc">Request a payout to your DonutSMP account. Admins will process it and give you the coins in-game.</div>
      <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
        <div style="display:flex;gap:8px">
          <input id="wd-pts" class="wd-input" type="number" min="1" placeholder="Points to withdraw" style="flex:1">
          <input id="wd-mc" class="wd-input" type="text" placeholder="Your MC username" style="flex:1">
        </div>
        <div id="wd-preview" style="font-size:12px;color:var(--pl);padding:8px 12px;background:rgba(124,58,237,.1);border-radius:8px;display:none"></div>
        <button class="wd-btn" onclick="doWithdraw()">Request Withdrawal</button>
      </div>
      <div style="font-size:13px;font-weight:700;color:var(--w);margin-bottom:8px">My Withdrawal History</div>
      <div id="wd-list" style="font-size:13px;color:var(--w3)">Loading…</div>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
const API = '';
let token = localStorage.getItem('jl_token') || null;
let user = null;

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

// Auth
async function init() {
  if (!token) { showLoginWall(); return; }
  try {
    const r = await api('/api/user.php?action=me');
    if (r.error) { showLoginWall(); return; }
    user = r.user || r;
    showApp();
    loadAll();
  } catch { showLoginWall(); }
}

function showLoginWall() {
  document.getElementById('login-wall').style.display = 'block';
  document.getElementById('main-content').style.display = 'none';
  document.getElementById('btn-login-nav').style.display = '';
  document.getElementById('btn-logout').style.display = 'none';
  document.getElementById('user-info').style.display = 'none';
  document.getElementById('points-chip').style.display = 'none';
}

function showApp() {
  document.getElementById('login-wall').style.display = 'none';
  document.getElementById('main-content').style.display = 'block';
  document.getElementById('btn-login-nav').style.display = 'none';
  document.getElementById('btn-logout').style.display = '';
  document.getElementById('user-info').style.display = 'flex';
  document.getElementById('points-chip').style.display = 'flex';
  const av = document.getElementById('user-av');
  const nm = document.getElementById('user-name');
  if (user) {
    av.textContent = (user.username || '?')[0].toUpperCase();
    nm.textContent = user.username || 'Player';
  }
}

async function doLogin() {
  const l = document.getElementById('lw-user').value.trim();
  const p = document.getElementById('lw-pass').value;
  const e = document.getElementById('lw-err');
  if (!l || !p) { e.textContent = 'Enter username and password'; e.style.display = 'block'; return; }
  try {
    const r = await fetch(API + '/api/login.php', {
      method: 'POST', headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ login: l, password: p })
    });
    const d = await r.json();
    if (d.error) { e.textContent = d.error; e.style.display = 'block'; return; }
    token = d.token;
    localStorage.setItem('jl_token', token);
    user = d.user || null;
    showApp();
    loadAll();
  } catch { e.textContent = 'Could not connect.'; e.style.display = 'block'; }
}

function logout() {
  token = null; user = null;
  localStorage.removeItem('jl_token');
  showLoginWall();
}

// Load all data
async function loadAll() {
  loadBalance();
  loadDaily();
  loadPlaytime();
  loadReferrals();
  loadLog();
  loadWithdrawals();
}

// Balance
async function loadBalance() {
  try {
    const r = await api('/api/points.php?action=balance');
    if (r.error) return;
    document.getElementById('balance-val').textContent = (r.points || 0).toLocaleString();
    document.getElementById('balance-sub').textContent = r.donut_value || 'pts';
    document.getElementById('top-pts').textContent = (r.points || 0).toLocaleString();
    document.getElementById('ref-url').value = r.referral_url || '';
  } catch {}
}

// Daily Reward
async function loadDaily() {
  const btn = document.getElementById('claim-btn');
  const bar = document.getElementById('streak-bar');
  const msg = document.getElementById('daily-msg');
  try {
    const r = await api('/api/rewards.php?action=daily-status');
    if (r.error) { msg.textContent = 'Could not load'; return; }
    const rewards = r.streak_rewards || [];
    const claimed = r.claimed_today;

    bar.innerHTML = rewards.map(d => {
      const isCurrent = d.day === r.streak_day;
      const isPast = d.day < r.streak_day || (claimed && d.day === r.streak_day);
      const isActive = isPast || (claimed && isCurrent);
      const bg = isActive ? 'linear-gradient(135deg,#22c55e,#16a34a)'
               : isCurrent && !claimed ? 'linear-gradient(135deg,#fbbf24,#f59e0b)'
               : 'rgba(255,255,255,.06)';
      const border = isActive ? '1px solid rgba(34,197,94,.4)'
                   : isCurrent && !claimed ? '1px solid rgba(251,191,36,.4)'
                   : '1px solid rgba(255,255,255,.08)';
      const color = isActive ? '#fff' : isCurrent && !claimed ? '#fbbf24' : 'var(--w3)';
      return `<div class="streak-day" style="background:${bg};border:${border}">
        <div class="day-label" style="color:${color}">${d.label}</div>
        <div class="day-pts" style="color:${color}">+${d.points}</div>
      </div>`;
    }).join('');

    if (claimed) {
      btn.disabled = true;
      btn.textContent = '✓ Claimed';
      msg.innerHTML = `<span style="color:var(--green);font-weight:600">+${r.today_reward} pts earned today!</span> Come back tomorrow for more.`;
    } else {
      btn.disabled = false;
      btn.textContent = `Claim +${r.today_reward} pts`;
      msg.textContent = r.current_streak > 0
        ? `${r.current_streak}-day streak! Claim now to keep it going.`
        : 'Start your streak today!';
    }
  } catch { msg.textContent = 'Could not load daily status'; }
}

async function claimDaily() {
  const btn = document.getElementById('claim-btn');
  if (btn.disabled) return;
  btn.disabled = true; btn.textContent = 'Claiming...';
  try {
    const r = await api('/api/rewards.php?action=daily-claim', { method: 'POST' });
    if (r.error) { showToast(r.error, 'error'); btn.disabled = false; btn.textContent = 'Claim Now'; return; }
    showToast('🎁 ' + r.message);
    loadBalance();
    loadDaily();
    loadLog();
  } catch {
    showToast('Failed to claim', 'error');
    btn.disabled = false; btn.textContent = 'Claim Now';
  }
}

// Playtime
async function loadPlaytime() {
  try {
    const r = await api('/api/rewards.php?action=playtime-stats');
    if (r.error) return;
    const tMin = r.today_minutes || 0;
    const tH = Math.floor(tMin/60), tM = tMin%60;
    document.getElementById('pt-today').textContent = tH > 0 ? tH+'h '+tM+'m' : tM+'m';
    document.getElementById('pt-today-pts').textContent = (r.today_points||0)+' pts earned';
    document.getElementById('pt-total').textContent = r.total_hours || '0h 0m';
    document.getElementById('pt-total-pts').textContent = (r.total_points||0)+' pts earned';
    document.getElementById('pt-sessions').textContent = r.total_sessions || 0;
  } catch {}
}

// Referrals
async function loadReferrals() {
  try {
    const r = await api('/api/points.php?action=referrals');
    const list = r.referrals || [];
    const el = document.getElementById('ref-list');
    el.innerHTML = list.length
      ? list.map(u => `<div class="log-item"><div class="log-reason">${esc(u.username)}</div><div class="log-amount" style="color:var(--green)">+10 pts</div><div class="log-date">${new Date(u.created_at).toLocaleDateString()}</div></div>`).join('')
      : '<div style="color:var(--w3)">No referrals yet. Share your link!</div>';
  } catch {}
}

// Log
async function loadLog() {
  try {
    const r = await api('/api/points.php?action=log');
    const list = r.log || [];
    const el = document.getElementById('pts-log');
    el.innerHTML = list.length
      ? list.map(l => `<div class="log-item"><div class="log-reason">${esc(l.reason)}</div><div class="log-amount" style="color:${l.amount>0?'var(--green)':'var(--red)'}">${l.amount>0?'+':''}${l.amount}</div><div class="log-date">${new Date(l.created_at).toLocaleDateString()}</div></div>`).join('')
      : '<div style="color:var(--w3)">No history yet.</div>';
  } catch {}
}

// Withdrawals
async function loadWithdrawals() {
  try {
    const r = await api('/api/withdrawals.php?action=list');
    const list = r.withdrawals || [];
    const el = document.getElementById('wd-list');
    el.innerHTML = list.length
      ? list.map(w => {
          const sc = w.status==='pending'?'var(--amber)':w.status==='completed'?'var(--green)':'var(--red)';
          return `<div class="log-item">
            <div style="flex:1;min-width:0">
              <div style="font-size:12px;font-weight:600;color:var(--w)">${w.points} pts → ${(w.points*1000000).toLocaleString()} coins</div>
              <div style="font-size:10.5px;color:var(--w3)">To: ${esc(w.mc_username)} · ${new Date(w.created_at).toLocaleDateString()}</div>
            </div>
            <span style="font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;letter-spacing:.05em;color:${sc}">${esc(w.status)}</span>
            ${w.status==='pending'?`<button onclick="cancelWd(${w.id})" style="padding:4px 10px;border-radius:6px;border:1px solid rgba(248,113,113,.3);background:transparent;color:var(--red);font-family:var(--f);font-size:10px;cursor:pointer">Cancel</button>`:''}
          </div>`;
        }).join('')
      : '<div style="color:var(--w3)">No withdrawals yet.</div>';
  } catch {}
}

// Withdraw
document.getElementById('wd-pts').addEventListener('input', () => {
  const pts = parseInt(document.getElementById('wd-pts').value || 0);
  const el = document.getElementById('wd-preview');
  if (pts > 0) { el.style.display = 'block'; el.textContent = pts + ' pts = ' + (pts*1000000).toLocaleString() + ' DonutSMP coins'; }
  else el.style.display = 'none';
});

async function doWithdraw() {
  const pts = parseInt(document.getElementById('wd-pts').value);
  const mc = document.getElementById('wd-mc').value.trim();
  if (!pts || pts < 1) { showToast('Enter points amount', 'error'); return; }
  if (!mc) { showToast('Enter MC username', 'error'); return; }
  const r = await api('/api/withdrawals.php?action=request', { method:'POST', body:JSON.stringify({points:pts,mc_username:mc}) });
  if (r.error) { showToast(r.error, 'error'); return; }
  showToast('Withdrawal requested!');
  document.getElementById('wd-pts').value = '';
  loadBalance();
  loadWithdrawals();
  loadLog();
}

async function cancelWd(id) {
  if (!confirm('Cancel this withdrawal? Points will be refunded.')) return;
  const r = await api('/api/withdrawals.php?action=cancel', { method:'POST', body:JSON.stringify({id}) });
  if (r.error) { showToast(r.error, 'error'); return; }
  showToast('Withdrawal cancelled — points refunded!');
  loadBalance();
  loadWithdrawals();
  loadLog();
}

function copyRef() {
  const v = document.getElementById('ref-url').value;
  if (!v || v === 'Loading…') return;
  navigator.clipboard.writeText(v).then(() => showToast('Referral link copied!'));
}

// Enter key for login
document.getElementById('lw-pass').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });

// Init
init();
</script>
</body>
</html>
