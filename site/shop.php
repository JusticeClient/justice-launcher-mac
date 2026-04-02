<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice — Cosmetics Shop</title>
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
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px}
.topbar-user{font-size:13px;font-weight:600;color:var(--w2)}
.topbar-av{width:26px;height:26px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff}
.points-badge{
  display:flex;align-items:center;gap:5px;padding:4px 12px;border-radius:7px;
  background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.25);
  font-size:12px;font-weight:700;color:#60a5fa;
}
.btn-logout{padding:5px 12px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:all .12s}
.btn-logout:hover{border-color:var(--red);color:var(--red)}

.page{padding-top:52px;min-height:100vh}
.inner{max-width:1100px;margin:0 auto;padding:32px 24px;width:100%}
.page-hdr{margin-bottom:28px;text-align:center}
.page-title{font-size:26px;font-weight:800;letter-spacing:-.04em;color:var(--w)}
.page-sub{font-size:13px;color:var(--w3);margin-top:6px}

.filter-bar{display:flex;gap:8px;justify-content:center;margin-bottom:28px;flex-wrap:wrap}
.filter-btn{
  padding:6px 16px;border-radius:8px;border:1px solid var(--line);background:transparent;
  color:var(--w3);font-family:var(--f);font-size:12px;font-weight:600;cursor:pointer;transition:all .12s;
}
.filter-btn:hover{border-color:var(--line2);color:var(--pl)}
.filter-btn.active{background:var(--p);border-color:var(--p);color:#fff}

.shop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}

.shop-card{
  background:var(--s2);border:1px solid var(--line);border-radius:14px;overflow:hidden;
  transition:border-color .15s,transform .15s;display:flex;flex-direction:column;
}
.shop-card:hover{border-color:var(--line2);transform:translateY(-2px)}
.shop-card.owned{border-color:rgba(74,222,128,.25)}
.shop-card.owned:hover{border-color:rgba(74,222,128,.45)}

.card-tex{
  height:120px;display:flex;align-items:center;justify-content:center;
  background:var(--s3);border-bottom:1px solid var(--line);position:relative;overflow:hidden;
}
.card-tex img{max-width:80%;max-height:80%;object-fit:contain}
.card-tex img.pixelated{image-rendering:pixelated}
.card-tex .type-icon{position:absolute;top:8px;left:10px;font-size:16px;opacity:.6}
.card-tex .rarity-dot{
  position:absolute;top:8px;right:10px;width:8px;height:8px;border-radius:50%;
}

.card-body{padding:14px 16px;flex:1;display:flex;flex-direction:column}
.card-name{font-size:14px;font-weight:700;color:var(--w);margin-bottom:3px}
.card-meta{display:flex;align-items:center;gap:6px;margin-bottom:4px;flex-wrap:wrap}
.card-badge{
  font-size:9px;font-weight:800;padding:1px 6px;border-radius:4px;
  text-transform:uppercase;letter-spacing:.04em;
}
.card-desc{font-size:11px;color:var(--w3);margin-bottom:12px;line-height:1.4;flex:1}

.card-footer{display:flex;align-items:center;justify-content:space-between;gap:8px}
.card-price{font-size:16px;font-weight:800;color:#60a5fa;letter-spacing:-.02em}
.card-price small{font-size:10px;font-weight:600;color:var(--w3);margin-left:2px}

.btn-buy{
  padding:7px 18px;border-radius:8px;border:none;
  background:var(--p);color:#fff;font-family:var(--f);font-size:12px;font-weight:700;
  cursor:pointer;transition:all .15s;
}
.btn-buy:hover{background:var(--p2);transform:scale(1.04)}
.btn-buy:disabled{opacity:.4;cursor:not-allowed;transform:none}

.btn-owned{
  padding:7px 18px;border-radius:8px;border:1px solid rgba(74,222,128,.3);
  background:rgba(74,222,128,.08);color:var(--green);font-family:var(--f);font-size:12px;font-weight:700;
  cursor:default;
}

.btn-cant-afford{
  padding:7px 18px;border-radius:8px;border:1px solid rgba(248,113,113,.2);
  background:rgba(248,113,113,.06);color:var(--w3);font-family:var(--f);font-size:11px;font-weight:600;
  cursor:not-allowed;
}

.toast-area{position:fixed;top:62px;right:16px;display:flex;flex-direction:column;gap:6px;z-index:999}
.toast{
  padding:10px 18px;border-radius:8px;font-size:12px;font-weight:600;font-family:var(--f);
  background:var(--s2);border:1px solid var(--line2);color:var(--pl);box-shadow:0 8px 24px rgba(0,0,0,.4);
  animation:slideIn .25s ease-out;
}
.toast.er{border-color:rgba(248,113,113,.3);color:var(--red)}
.toast.ok{border-color:rgba(74,222,128,.3);color:var(--green)}
@keyframes slideIn{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:none}}

.login-overlay{
  position:fixed;inset:0;background:var(--bg);z-index:200;display:flex;align-items:center;justify-content:center;
}
.login-box{
  background:var(--s2);border:1px solid var(--line);border-radius:16px;padding:36px;
  max-width:380px;width:90%;text-align:center;
}
.login-box h2{font-size:20px;font-weight:800;margin-bottom:6px}
.login-box p{font-size:13px;color:var(--w3);margin-bottom:20px}
.login-input{
  width:100%;padding:10px 14px;border-radius:9px;border:1px solid var(--line);background:var(--s3);
  color:var(--w);font-family:var(--f);font-size:13px;margin-bottom:10px;outline:none;
  transition:border .12s;
}
.login-input:focus{border-color:var(--line2)}
.login-btn{
  width:100%;padding:10px;border-radius:9px;border:none;background:var(--p);color:#fff;
  font-family:var(--f);font-size:13px;font-weight:700;cursor:pointer;transition:background .12s;
}
.login-btn:hover{background:var(--p2)}

.empty-state{
  text-align:center;padding:60px 20px;color:var(--w3);
}
.empty-state .icon{font-size:48px;margin-bottom:12px;opacity:.5}
.empty-state h3{font-size:16px;font-weight:700;color:var(--w2);margin-bottom:6px}
</style>
</head>
<body>

<div class="login-overlay" id="login-overlay" style="display:none">
  <div class="login-box">
    <div style="margin-bottom:16px">
      <div class="logo-sq" style="margin:0 auto 10px;width:36px;height:36px;font-size:16px">J</div>
    </div>
    <h2>Justice Shop</h2>
    <p>Log in to purchase cosmetics with your points.</p>
    <input class="login-input" id="login-user" type="text" placeholder="Username">
    <input class="login-input" id="login-pass" type="password" placeholder="Password">
    <button class="login-btn" onclick="doLogin()">Log In</button>
    <div style="margin-top:12px;font-size:11px;color:var(--w3)">
      Or browse as a guest — <a href="#" onclick="guestBrowse()" style="color:var(--pl);text-decoration:none">view catalog</a>
    </div>
  </div>
</div>

<div class="topbar">
  <a href="#" class="topbar-logo">
    <div class="logo-sq" style="font-size:12px">J</div>
    <span>Justice</span>
  </a>
  <span style="font-size:12px;font-weight:700;color:var(--pl);letter-spacing:.04em;text-transform:uppercase">Shop</span>
  <div class="topbar-right">
    <div class="points-badge" id="points-display" style="display:none">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v12M6 12h12"/></svg>
      <span id="points-val">0</span> pts
    </div>
    <span class="topbar-user" id="username-display"></span>
    <div class="topbar-av" id="user-av" style="display:none"></div>
    <button class="btn-logout" id="btn-logout" style="display:none" onclick="logout()">Log out</button>
  </div>
</div>

<div class="toast-area" id="toast-area"></div>

<div class="page">
  <div class="inner">
    <div class="page-hdr">
      <div class="page-title">Cosmetics Shop</div>
      <div class="page-sub">Spend your points on capes, hats, wings, and more.</div>
    </div>

    <div class="filter-bar" id="filter-bar">
      <button class="filter-btn active" data-type="all" onclick="setFilter('all',this)">All</button>
      <button class="filter-btn" data-type="cape" onclick="setFilter('cape',this)">Capes</button>
      <button class="filter-btn" data-type="hat" onclick="setFilter('hat',this)">Hats</button>
      <button class="filter-btn" data-type="wings" onclick="setFilter('wings',this)">Wings</button>
      <button class="filter-btn" data-type="bandana" onclick="setFilter('bandana',this)">Bandanas</button>
      <button class="filter-btn" data-type="aura" onclick="setFilter('aura',this)">Auras</button>
    </div>

    <div id="shop-grid" class="shop-grid"></div>
  </div>
</div>

<script>
const API = '';
let token = localStorage.getItem('jl_token') || null;
let username = null;
let userPoints = 0;
let ownedIds = new Set();
let shopItems = [];
let currentFilter = 'all';
let isGuest = false;

function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function toast(msg, type) {
  const area = document.getElementById('toast-area');
  const t = document.createElement('div');
  t.className = 'toast' + (type === 'er' ? ' er' : type === 'ok' ? ' ok' : '');
  t.textContent = msg;
  area.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

async function api(url, opts = {}) {
  if (token && !opts.headers) opts.headers = {};
  if (token) opts.headers['Authorization'] = 'Bearer ' + token;
  if (opts.body && typeof opts.body === 'string' && !opts.headers['Content-Type']) {
    opts.headers['Content-Type'] = 'application/json';
  }
  const r = await fetch(API + url, opts);
  return r.json();
}

async function doLogin() {
  const user = document.getElementById('login-user').value.trim();
  const pass = document.getElementById('login-pass').value;
  if (!user || !pass) { toast('Enter username and password', 'er'); return; }

  try {
    const r = await fetch(API + '/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ login: user, password: pass })
    });
    const d = await r.json();
    if (d.requires_2fa) {
      toast('2FA not yet supported in shop — log in on the main site first', 'er');
      return;
    }
    if (d.token) {
      token = d.token;
      username = d.user?.username || user;
      localStorage.setItem('jl_token', token);
      document.getElementById('login-overlay').style.display = 'none';
      initShop();
    } else {
      toast(d.error || 'Login failed', 'er');
    }
  } catch(e) { toast('Login error: ' + e.message, 'er'); }
}

function guestBrowse() {
  isGuest = true;
  document.getElementById('login-overlay').style.display = 'none';
  initShop();
}

function logout() {
  token = null;
  username = null;
  localStorage.removeItem('jl_token');
  location.reload();
}

async function initShop() {
  updateUserUI();

  try {
    const r = await fetch(API + '/api/cosmetics.php?action=shop');
    const d = await r.json();
    shopItems = d.cosmetics || [];
  } catch(e) {
    shopItems = [];
    toast('Failed to load shop', 'er');
  }

  if (token && username) {
    try {
      const [ownedRes, userRes] = await Promise.all([
        api('/api/cosmetics.php?action=mine'),
        api('/api/user.php?action=me')
      ]);
      const owned = ownedRes.owned || [];
      ownedIds = new Set(owned.map(c => c.id));
      userPoints = userRes.user?.points ?? 0;
      document.getElementById('points-val').textContent = userPoints.toLocaleString();
      document.getElementById('points-display').style.display = 'flex';
    } catch(e) { }
  }

  renderGrid();
}

function updateUserUI() {
  if (username && token) {
    document.getElementById('username-display').textContent = username;
    document.getElementById('user-av').textContent = username[0].toUpperCase();
    document.getElementById('user-av').style.display = 'flex';
    document.getElementById('btn-logout').style.display = '';
  }
}

function setFilter(type, btn) {
  currentFilter = type;
  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  renderGrid();
}

function renderGrid() {
  const grid = document.getElementById('shop-grid');
  const rarityColors = { common:'#9ca3af', uncommon:'#4ade80', rare:'#60a5fa', epic:'#a78bfa', legendary:'#fbbf24' };
  const typeIcons = { cape:'\u{1f9b8}', hat:'\u{1f3a9}', wings:'\u{1FABD}', bandana:'\u{1f3ad}', aura:'\u{1f4ab}', emoji:'\u{1f60e}' };
  const rarityOrder = { legendary:0, epic:1, rare:2, uncommon:3, common:4 };

  let items = shopItems;
  if (currentFilter !== 'all') {
    items = items.filter(c => c.type === currentFilter);
  }

  items.sort((a, b) => {
    const aOwn = ownedIds.has(a.id) ? 1 : 0;
    const bOwn = ownedIds.has(b.id) ? 1 : 0;
    if (aOwn !== bOwn) return aOwn - bOwn;
    return (rarityOrder[a.rarity] ?? 5) - (rarityOrder[b.rarity] ?? 5);
  });

  if (!items.length) {
    grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
      <div class="icon">\u{1F6D2}</div>
      <h3>${currentFilter === 'all' ? 'No items in the shop yet' : 'No ' + currentFilter + 's available'}</h3>
      <p style="font-size:12px">Check back later for new cosmetics!</p>
    </div>`;
    return;
  }

  grid.innerHTML = items.map(c => {
    const rc = rarityColors[c.rarity] || '#9ca3af';
    const icon = typeIcons[c.type] || '\u2728';
    const owned = ownedIds.has(c.id);
    const canAfford = userPoints >= c.price;

    let btnHtml;
    if (owned) {
      btnHtml = `<span class="btn-owned">\u2713 Owned</span>`;
    } else if (!token) {
      btnHtml = `<button class="btn-buy" onclick="toast('Log in to purchase','er')">Log in</button>`;
    } else if (!canAfford) {
      btnHtml = `<span class="btn-cant-afford">Need ${(c.price - userPoints).toLocaleString()} more</span>`;
    } else {
      btnHtml = `<button class="btn-buy" id="buy-${c.id}" onclick="buyCosmetic(${c.id},'${esc(c.name)}',${c.price})">Buy</button>`;
    }

    return `<div class="shop-card${owned ? ' owned' : ''}">
      <div class="card-tex">
        <span class="type-icon">${icon}</span>
        <span class="rarity-dot" style="background:${rc};box-shadow:0 0 6px ${rc}80"></span>
        <img src="${API}/${esc(c.previewPath || c.texturePath)}" class="${c.previewPath ? '' : 'pixelated'}" style="${c.previewPath ? 'max-width:90%;max-height:90%;border-radius:4px' : ''}" onerror="this.style.display='none'">
      </div>
      <div class="card-body">
        <div class="card-name">${esc(c.name)}</div>
        <div class="card-meta">
          <span class="card-badge" style="background:${rc}22;color:${rc};border:1px solid ${rc}44">${c.rarity}</span>
          <span class="card-badge" style="background:var(--w4);color:var(--w3)">${c.type}</span>
        </div>
        ${c.description ? `<div class="card-desc">${esc(c.description)}</div>` : '<div class="card-desc" style="flex:1"></div>'}
        <div class="card-footer">
          <div class="card-price">${c.price.toLocaleString()}<small>pts</small></div>
          ${btnHtml}
        </div>
      </div>
    </div>`;
  }).join('');
}

async function buyCosmetic(id, name, price) {
  if (!confirm('Buy "' + name + '" for ' + price.toLocaleString() + ' points?')) return;

  const btn = document.getElementById('buy-' + id);
  if (btn) { btn.disabled = true; btn.textContent = '...'; }

  try {
    const r = await api('/api/cosmetics.php?action=buy', {
      method: 'POST',
      body: JSON.stringify({ cosmeticId: id })
    });
    if (r.ok) {
      toast('"' + name + '" purchased!', 'ok');
      ownedIds.add(id);
      userPoints = r.pointsRemaining ?? (userPoints - price);
      document.getElementById('points-val').textContent = userPoints.toLocaleString();
      renderGrid();
    } else {
      toast(r.error || 'Purchase failed', 'er');
      if (btn) { btn.disabled = false; btn.textContent = 'Buy'; }
    }
  } catch(e) {
    toast('Error: ' + e.message, 'er');
    if (btn) { btn.disabled = false; btn.textContent = 'Buy'; }
  }
}

(async () => {
  if (token) {
    try {
      const r = await fetch(API + '/api/user.php?action=me', {
        headers: { Authorization: 'Bearer ' + token }
      });
      const d = await r.json();
      if (d.user) {
        username = d.user.username;
        initShop();
      } else {
        token = null;
        localStorage.removeItem('jl_token');
        document.getElementById('login-overlay').style.display = '';
      }
    } catch(e) {
      document.getElementById('login-overlay').style.display = '';
    }
  } else {
    document.getElementById('login-overlay').style.display = '';
  }
})();
</script>
</body>
</html>
