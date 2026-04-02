# Justice Launcher — Master Prompt (All Requirements)

## Project Context

You are working on **Justice Launcher**, a custom Minecraft client launcher built with **Electron 28 + vanilla HTML/CSS/JS** (no React, no frameworks). The entire UI lives in a single file: `src/index.html` (~6400 lines, containing `<style>`, HTML, and `<script>` all in one file). The main process is `main.js`.

The launcher has 20+ pages (Play, Mods, Modpacks, Modrinth, Worlds, Resource Packs, Shaders, Screenshots, Host World, Servers, Skin, Console, Friends, Search, News, Clans, Plus, Events, Points, Settings). All navigation is sidebar-based with page switching via class toggling. The design uses a dark purple/magenta glassmorphism theme with Space Grotesk + JetBrains Mono fonts and CSS custom properties for theming.

**There is a UI/UX Pro Max design intelligence skill available** at `ui-ux-pro-max-skill/`. Before making major design decisions, run searches:

```bash
python3 ui-ux-pro-max-skill/src/ui-ux-pro-max/scripts/search.py "<query>" --domain <domain>
```

Domains: `style`, `typography`, `color`, `ux`, `landing`, `product`
Example queries: `"glassmorphism dark mode gaming"` --domain style, `"sidebar navigation collapsible"` --domain ux, `"dark purple neon color palette"` --domain color

**Design Philosophy:** ZERO blank space. Every pixel serves a purpose. The app should feel dense, information-rich, and premium — like a high-end gaming client (Steam, Lunar Client, Overwolf). Everything should be easy to read at default size — no squinting. Text should be readable, buttons obvious, layout should guide the eye naturally.

---

## WHAT YOU MUST NOT TOUCH

1. **The Play button** — do NOT change its styling, size, gradient, glow animation, or behavior. It stays exactly as-is.
2. **`main.js`** — do not modify the Electron main process unless absolutely necessary for a new IPC channel
3. **Game launching logic** — all `ipc.send('launch-instance', ...)` calls stay the same
4. **Authentication flows** — Microsoft OAuth and existing account systems stay the same
5. **The color scheme** — keep the dark purple/magenta glassmorphism theme (enhance it, don't replace)
6. **No frameworks** — stay vanilla HTML/CSS/JS, no React, no Tailwind, no Bootstrap
7. **Existing pages and features** — do not remove any pages or core functionality

---

## CRITICAL RULE: THE BACKEND ALREADY EXISTS — HERE ARE THE EXACT API ENDPOINTS

The social system is **already fully built** at `https://justiceclient.org/api/`. All API calls use JSON. Auth uses a JWT token passed as `Authorization: Bearer <token>` header. The token is stored as `jlToken` in the app and the user object as `jlUser`.

**DO NOT create a new backend or mock data. DO NOT use IPC for social features. Call the HTTP API directly from the renderer using `fetch()`.**

### API Reference — EXACT ENDPOINTS:

**Auth:**
```
POST https://justiceclient.org/api/login.php
Body: { "username": "...", "password": "..." }
Response: { "token": "jwt...", "user": { "id", "username", "role", "clan_id", ... } }
```

**News (NO AUTH REQUIRED):**
```
GET https://justiceclient.org/api/news.php?action=list&limit=10&offset=0
Response: { "posts": [{ "title", "body", "excerpt", "author_name", "created_at", "pinned" }, ...] }

GET https://justiceclient.org/api/news.php?action=get&slug=some-slug
Response: { "post": { "title", "body", "author_name", "created_at" } }
```

**Clans (AUTH REQUIRED — pass `Authorization: Bearer ${jlToken}`):**
```
GET  /api/clans.php?action=my          → { "clan": {...} | null, "members": [...] }
GET  /api/clans.php?action=search&q=x  → { "clans": [...] }
GET  /api/clans.php?action=get&id=5    → { "clan": {...}, "members": [...] }
POST /api/clans.php?action=create      → Body: { "name", "tag", "description", "color" }
POST /api/clans.php?action=invite      → Body: { "userId": 123 }
GET  /api/clans.php?action=invites     → { "invites": [...] }
POST /api/clans.php?action=accept      → Body: { "clan_id": 5 }
POST /api/clans.php?action=leave       → Body: { "userId": 123 } (or omit for self)
```

**Friends (AUTH REQUIRED):**
```
GET  /api/friends.php?action=list                → { "friends": [...] }
GET  /api/friends.php?action=search&q=partialName → { "users": [...] }
POST /api/friends.php?action=add                 → Body: { "userId": 123 }
POST /api/friends.php?action=remove              → Body: { "userId": 123 }
```

**Points (AUTH REQUIRED):**
```
GET  /api/points.php?action=balance    → { "points": 0, "referral_code": "..." }
POST /api/points.php?action=withdraw   → Body: { "amount": 100, "mc_username": "..." }
GET  /api/points.php?action=history    → { "history": [...] }
```

### Helper function for all API calls:

```js
const JL_API = 'https://justiceclient.org/api';

async function jlFetch(endpoint, options = {}) {
  const headers = { 'Content-Type': 'application/json' };
  if (jlToken) headers['Authorization'] = 'Bearer ' + jlToken;
  const res = await fetch(JL_API + endpoint, { ...options, headers });
  const data = await res.json();
  if (data.error) throw new Error(data.error);
  return data;
}

// Usage:
const news = await jlFetch('/news.php?action=list&limit=10');
const clan = await jlFetch('/clans.php?action=my');
const result = await jlFetch('/clans.php?action=create', {
  method: 'POST',
  body: JSON.stringify({ name: 'Cool Clan', tag: 'CC', description: '' })
});
```

---

## KNOWN BUGS — FIX THESE FIRST (HIGHEST PRIORITY)

These are bugs confirmed by the user AFTER the previous round of changes. Fix ALL of these before doing anything else:

### BUG 1: SOCIAL LOGIN OPENS MICROSOFT PAGE INSTEAD OF JUSTICE LOGIN
**What happens:** Clicking "Sign In" for Justice Social opens the Microsoft OAuth login page instead of the Justice social login modal/API.
**Root cause:** The sign-in button is calling the Microsoft auth flow instead of the Justice social API.
**The fix:** The social sign-in must call `POST https://justiceclient.org/api/login.php` with `{ username, password }`. It must NOT open the Microsoft OAuth flow. These are TWO COMPLETELY SEPARATE sign-ins:
- Microsoft OAuth = for Minecraft game account (launches via Electron's external browser)
- Justice Social = for friends/clans/points (calls the REST API directly via `fetch()`, stays in-app)

Find the social sign-in button's onclick handler and make sure it calls the Justice API, not the Microsoft flow.

### BUG 2: INSTANCE DROPDOWN STILL SHOWS "VANILLA" FOR EVERYTHING
**What happens:** When opening the instance selector dropdown, every instance shows a yellow "Vanilla" badge — even Fabric instances. The TOP selector shows the correct type, but the dropdown items don't.
**Root cause:** The dropdown render loop is hardcoding the loader type or reading from the wrong field.
**The fix:** Inside the dropdown render loop (where it creates list items for each instance), find where it sets the badge text. It must read `instance.loader` or `instance.loaderType` or `instance.modLoader` for EACH individual instance — not use a default. The instance cards in the bottom bar show correct types, so the data exists.

### BUG 3: INSTANCE SWITCHES IMMEDIATELY ON CLICK (BEFORE CONFIRMATION)
**What happens:** Clicking an instance card in the bottom bar switches to it immediately, THEN shows the confirmation popup asking "Switch to [name]?" — which is backwards. The switch should only happen AFTER clicking "Switch" in the popup.
**The fix:** The click handler on instance cards must:
1. Store the pending instance in a variable
2. Show the confirmation popup
3. Do NOT call `setActiveInstance()` or update the selector yet
4. Only switch when the user clicks "Switch" in the popup
5. If the user clicks "Cancel" or presses Escape, revert — nothing changes

Find the instance card click handler and remove the premature `setActiveInstance()` call. Move it into the `confirmInstanceSwitch()` function only.

### BUG 4: FRIEND SEARCH NEEDS LIVE AUTOCOMPLETE
**What happens:** The friend search/add friend input requires an exact username match. User wants to type "p" and see all users starting with "p", narrowing as they type.
**The fix:** The friend search must call `GET /api/friends.php?action=search&q={input}` on every keystroke (debounced by 300ms). Display results as a dropdown list below the input. Clicking a result sends the friend request.

```js
let friendSearchTimeout;
function onFriendSearchInput(e) {
  clearTimeout(friendSearchTimeout);
  const q = e.target.value.trim();
  if (q.length < 1) { hideFriendResults(); return; }
  friendSearchTimeout = setTimeout(async () => {
    try {
      const data = await jlFetch('/friends.php?action=search&q=' + encodeURIComponent(q));
      showFriendResults(data.users);
    } catch (err) {
      console.error('Friend search error:', err);
    }
  }, 300);
}
```

### BUG 5: CLANS TAB SAYS "SIGN IN" EVEN WHEN SIGNED IN
**What happens:** The Clans tab shows "Sign in to view and manage your clan" even when the user IS signed into Justice Social and the Friends tab works fine.
**Root cause:** The Clans tab has a broken auth check — it's checking a different variable than what the Friends tab uses, or the `jlToken` isn't being passed to the clan init function.
**The fix:** The Clans tab must:
1. Check `jlToken` (same variable the Friends tab uses)
2. If signed in, call `GET /api/clans.php?action=my` with `Authorization: Bearer ${jlToken}`
3. If response has `clan: null` → show "Create Clan" button
4. If response has clan data → show clan info with members
5. If NOT signed in → show "Sign in to view clans"

**EXACT API call:**
```js
async function initClansPage() {
  if (!jlToken) {
    showClansSignInPrompt();
    return;
  }
  try {
    const data = await jlFetch('/clans.php?action=my');
    if (!data.clan) {
      showCreateClanUI();
    } else {
      showClanDetails(data.clan, data.members);
    }
  } catch (err) {
    showToast('Could not load clan data', 'error');
  }
}
```

### BUG 6: NEWS TAB STUCK ON "LOADING LATEST NEWS..."
**What happens:** News tab shows "Loading latest news..." forever and never loads.
**Root cause:** The news fetch is either not calling the correct endpoint, the auth header is causing a 401 on a public endpoint, or the response isn't being parsed correctly.
**CRITICAL: The news endpoint does NOT require authentication.** Do NOT send an auth header for news list/get.
**The fix:**
```js
async function loadNews() {
  const container = document.querySelector('.news-content') || document.querySelector('[class*="news"]');
  if (!container) return;
  container.innerHTML = '<div style="text-align:center;padding:40px;color:rgba(255,255,255,0.5);">Loading news...</div>';
  try {
    // NO AUTH HEADER for news — it's a public endpoint
    const res = await fetch('https://justiceclient.org/api/news.php?action=list&limit=10&offset=0');
    const data = await res.json();
    if (!data.posts || data.posts.length === 0) {
      container.innerHTML = '<div style="text-align:center;padding:40px;color:rgba(255,255,255,0.5);">No news yet.</div>';
      return;
    }
    container.innerHTML = '';
    data.posts.forEach(post => {
      const card = document.createElement('div');
      card.style.cssText = 'background:rgba(10,5,20,0.5);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:16px;margin-bottom:12px;cursor:pointer;';
      card.innerHTML = `
        <h3 style="margin:0 0 6px 0;font-size:15px;color:#fff;">${post.pinned ? '📌 ' : ''}${post.title}</h3>
        <p style="margin:0 0 8px 0;font-size:12px;color:rgba(255,255,255,0.4);">By ${post.author_name} · ${new Date(post.created_at).toLocaleDateString()}</p>
        <p style="margin:0;font-size:13px;color:rgba(255,255,255,0.6);line-height:1.5;">${post.excerpt || post.body.substring(0, 200)}</p>
      `;
      container.appendChild(card);
    });
  } catch (err) {
    container.innerHTML = '<div style="text-align:center;padding:40px;color:rgba(255,255,255,0.5);"><p>Could not load news.</p><button onclick="loadNews()" style="padding:7px 14px;background:rgba(139,92,246,0.4);border:1px solid rgba(139,92,246,0.5);border-radius:8px;color:#fff;cursor:pointer;margin-top:8px;">Try Again</button></div>';
  }
}
```

### BUG 7: RAM SETTING NOT SAVING
**What happens:** User sets RAM to 8GB but it resets to 2GB on next launch. RAM preference should persist.
**The fix:** When the RAM slider/input changes, save the value to localStorage AND show a confirmation toast:
```js
function onRamChange(value) {
  localStorage.setItem('justice-ram-mb', value);
  showToast('RAM set to ' + (value / 1024) + ' GB', 'success');
  // Also update the UIState if that system exists:
  if (typeof UIState !== 'undefined') UIState.set('ramMb', value);
}

// On app load, restore RAM:
function restoreRamSetting() {
  const saved = localStorage.getItem('justice-ram-mb');
  if (saved) {
    const ramSlider = document.querySelector('[class*="ram"]') || document.getElementById('ram-slider');
    if (ramSlider) ramSlider.value = saved;
    // Update the display label too
    updateRamDisplay(saved);
  }
}
```

---

## TASK 1: SOCIAL SIGN-IN — `fetch()` TO JUSTICE API, NOT MICROSOFT

The social sign-in calls `POST https://justiceclient.org/api/login.php`. It does NOT open Microsoft OAuth, does NOT open an external browser, does NOT navigate away from the app. It is a simple `fetch()` call that happens in-app.

**THIS IS THE #1 BUG RIGHT NOW** — the sign-in button is incorrectly triggering the Microsoft OAuth flow. Find the onclick handler for the social sign-in button and replace it with a direct `fetch()` call.

### The correct sign-in function:

```js
async function socialSignIn() {
  const username = document.getElementById('social-username').value.trim();
  const password = document.getElementById('social-password').value;
  if (!username || !password) return;

  try {
    const res = await fetch('https://justiceclient.org/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ username, password })
    });
    const data = await res.json();
    if (data.error) { showToast(data.error, 'error'); return; }

    // Store token and user globally
    jlToken = data.token;
    jlUser = data.user;
    localStorage.setItem('jlToken', data.token);
    localStorage.setItem('jlUser', JSON.stringify(data.user));

    closeSocialSignIn();
    updateSocialUI(); // refresh right sidebar to show friends/clans/news
    showToast('Signed in as ' + data.user.username, 'success');
  } catch (err) {
    showToast('Sign in failed. Check your connection.', 'error');
  }
}
```

**IMPORTANT:** Search for any code that calls `ipc.invoke('social-signin')` or `ipc.send('social-signin')` and replace it with the `fetch()` call above. Social auth goes directly to the REST API, NOT through Electron IPC.

### Where to put the sign-in prompt:

**Right sidebar** — When the user is NOT signed into the social system (`!jlToken`), the right sidebar shows a sign-in prompt instead of the friends list.

```html
<!-- Right sidebar social sign-in state (shown when NOT signed in) -->
<div class="social-signin-prompt" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:24px; text-align:center; gap:16px;">
  <div style="width:64px; height:64px; border-radius:50%; background:rgba(139,92,246,0.2); display:flex; align-items:center; justify-content:center;">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgba(139,92,246,0.8)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
      <circle cx="9" cy="7" r="4"/>
      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
    </svg>
  </div>
  <h3 style="margin:0; font-size:16px; color:#fff;">Justice Social</h3>
  <p style="margin:0; font-size:13px; color:rgba(255,255,255,0.5); line-height:1.5;">Sign in to see friends, join clans, earn points, and chat.</p>
  <button class="social-signin-btn" onclick="openSocialSignIn()">Sign In</button>
  <p style="margin:0; font-size:12px; color:rgba(255,255,255,0.35);">Don't have an account? <a href="#" onclick="openSocialSignUp()" style="color:rgba(139,92,246,0.8);">Sign Up</a></p>
</div>
```

### Social sign-in modal:

```html
<div id="social-signin-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="closeSocialSignIn()"></div>
  <div class="modal-content" style="background:rgba(10,5,20,0.95); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:24px; width:380px; max-width:90vw; color:#FFF; position:relative;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h3 style="margin:0; font-size:18px;">Justice Social Sign In</h3>
      <button onclick="closeSocialSignIn()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; opacity:0.6;">&times;</button>
    </div>

    <div style="display:flex; flex-direction:column; gap:14px;">
      <div>
        <label style="font-size:13px; color:rgba(255,255,255,0.6); display:block; margin-bottom:6px;">Username or Email</label>
        <input type="text" id="social-username" placeholder="Enter your username..." style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:14px; box-sizing:border-box; outline:none;">
      </div>
      <div>
        <label style="font-size:13px; color:rgba(255,255,255,0.6); display:block; margin-bottom:6px;">Password</label>
        <input type="password" id="social-password" placeholder="Enter your password..." style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:14px; box-sizing:border-box; outline:none;">
      </div>
    </div>

    <button onclick="socialSignIn()" style="width:100%; padding:11px; background:rgba(139,92,246,0.6); border:1px solid rgba(139,92,246,0.5); border-radius:8px; color:#fff; font-size:14px; font-weight:600; cursor:pointer; margin-top:18px;">Sign In</button>

    <div style="text-align:center; margin-top:14px;">
      <p style="margin:0; font-size:12px; color:rgba(255,255,255,0.4);">Don't have an account? <a href="#" onclick="switchToSignUp()" style="color:rgba(139,92,246,0.8);">Create one</a></p>
    </div>
  </div>
</div>
```

### JavaScript for social auth:

```js
let socialUser = null;

function openSocialSignIn() {
  document.getElementById('social-signin-modal').classList.remove('hidden');
  document.getElementById('social-username').focus();
}

function closeSocialSignIn() {
  document.getElementById('social-signin-modal').classList.add('hidden');
}

async function socialSignIn() {
  const username = document.getElementById('social-username').value.trim();
  const password = document.getElementById('social-password').value;
  if (!username || !password) return;

  try {
    const result = await ipc.invoke('social-signin', { username, password });
    if (result.success) {
      socialUser = result.user;
      localStorage.setItem('justice-social-user', JSON.stringify(result.user));
      closeSocialSignIn();
      updateSocialUI();
    } else {
      showToast(result.error || 'Sign in failed', 'error');
    }
  } catch (err) {
    console.error('Social sign in error:', err);
    showToast('Sign in failed. Try again.', 'error');
  }
}

function updateSocialUI() {
  const signedIn = socialUser !== null;
  const prompt = document.querySelector('.social-signin-prompt');
  const socialContent = document.querySelector('.social-content');
  if (prompt) prompt.style.display = signedIn ? 'none' : 'flex';
  if (socialContent) socialContent.style.display = signedIn ? 'block' : 'none';
}

function loadSocialSession() {
  try {
    const saved = localStorage.getItem('justice-social-user');
    if (saved) {
      socialUser = JSON.parse(saved);
      updateSocialUI();
    }
  } catch {}
}

loadSocialSession();
```

**Search `main.js` first for the existing social sign-in IPC handler** — it may already be named `social-signin`, `login`, `authenticate`, etc. Wire the frontend modal to whatever the existing channel is.

---

## TASK 2: SHOW USERNAME & MINECRAFT SKIN FACE IN SIDEBAR

### Fix the user chip — show actual Minecraft username:

The **user chip** at the bottom-left of the sidebar should show:
1. The player's actual Minecraft username (e.g., "Mrjew_"), NOT the generic word "Player"
2. The username updates dynamically after Microsoft sign-in
3. The username is already available from the Microsoft auth flow — just display it

### Show Minecraft skin face:

Like other Minecraft clients, the user chip should display the player's **Minecraft skin face** (the head portion). Use free APIs:

```
https://mc-heads.net/avatar/{username}/32
https://crafatar.com/avatars/{username}?size=32&overlay
https://minotar.net/helm/{username}/32
```

### User chip HTML:

```html
<div class="user-chip">
  <img
    class="skin-face"
    src=""
    alt=""
    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
  >
  <div class="skin-face-fallback" style="display:none;">
    M
  </div>
  <div class="user-chip-info">
    <span class="user-chip-name">Mrjew_</span>
    <span class="user-chip-subtitle">Microsoft Account</span>
  </div>
</div>
```

### Skin face CSS:

```css
.skin-face {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  image-rendering: pixelated;  /* CRITICAL — keeps pixel art crisp */
  flex-shrink: 0;
}

.skin-face-fallback {
  width: 32px;
  height: 32px;
  border-radius: 4px;
  background: rgba(139, 92, 246, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.8);
  flex-shrink: 0;
}
```

### Update skin dynamically:

```js
function updateSkinFace(username) {
  const img = document.querySelector('.skin-face');
  if (img && username) {
    img.src = `https://mc-heads.net/avatar/${encodeURIComponent(username)}/32`;
    img.alt = username;
  }
}

// Call after Microsoft sign-in succeeds and on app load if username exists
```

Cache skin URL in localStorage for instant loading on next launch.

---

## TASK 3: ADMIN/STAFF ROLE BADGES

Users can have roles like **Admin** or **Staff**. Display badges in two places:

### A. Left sidebar user chip (bottom-left):

```html
<div class="user-chip-name-row">
  <span class="user-chip-name">Mrjew_</span>
  <span class="role-badge role-admin">Admin</span>  <!-- or "Staff" -->
</div>
```

### B. Right sidebar:

Display the same role badge next to the username at the top of the right sidebar.

### Role badge CSS:

```css
.role-badge {
  display: inline-flex;
  align-items: center;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  flex-shrink: 0;
}

.role-admin {
  background: rgba(239, 68, 68, 0.25);
  color: #f87171;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.role-staff {
  background: rgba(59, 130, 246, 0.25);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}
```

**Search `main.js`** for user profile IPC handlers that return role/tag/rank. Wire the badge display to that data. If no role exists, don't show any badge.

---

## TASK 4: GAME CONSOLE — CAPTURE STARTUP LOGS

The Console page must capture the game's startup output. Code for this may already exist — search for it first.

### What to check:

1. **Find the console page element** — look for the console/log display area in HTML
2. **Find the IPC listener** — search for `ipc.on('game-output', ...)` or similar patterns: `game-log`, `console-log`, `stdout`, `stderr`
3. **Make sure the listener is registered GLOBALLY on app startup** — not inside a page-specific init that only runs when navigating to Console
4. **Make sure logs are buffered** — store all log lines so full history shows when navigating to the page

### Console display CSS:

```css
.console-output {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  line-height: 1.6;
  padding: 12px;
  background: rgba(10, 5, 20, 0.8);
  border-radius: 8px;
  overflow-y: auto;
  height: 100%;
  white-space: pre-wrap;
  word-break: break-all;
  color: rgba(255, 255, 255, 0.8);
}

.log-info { color: rgba(255, 255, 255, 0.7); }
.log-warn { color: #fbbf24; }
.log-error { color: #f87171; }
.log-debug { color: rgba(139, 92, 246, 0.7); }
```

### JavaScript for log capturing (global):

```js
const gameLogBuffer = [];
const MAX_LOG_LINES = 5000;

ipc.on('game-output', (event, data) => {
  gameLogBuffer.push(data);
  if (gameLogBuffer.length > MAX_LOG_LINES) gameLogBuffer.shift();

  const consoleEl = document.querySelector('.console-output');
  if (consoleEl && document.querySelector('.console-page.active')) {
    appendLogLine(consoleEl, data);
    if (consoleEl.scrollTop + consoleEl.clientHeight >= consoleEl.scrollHeight - 50) {
      consoleEl.scrollTop = consoleEl.scrollHeight;
    }
  }
});

function appendLogLine(container, data) {
  const line = document.createElement('div');
  line.textContent = data.text || data;
  const text = (data.text || data).toLowerCase();
  if (text.includes('error') || text.includes('exception')) line.className = 'log-error';
  else if (text.includes('warn')) line.className = 'log-warn';
  else if (text.includes('debug')) line.className = 'log-debug';
  else line.className = 'log-info';
  container.appendChild(line);
}

function showConsolePage() {
  const consoleEl = document.querySelector('.console-output');
  if (!consoleEl) return;
  consoleEl.innerHTML = '';
  gameLogBuffer.forEach(data => appendLogLine(consoleEl, data));
  consoleEl.scrollTop = consoleEl.scrollHeight;
}
```

**Important:** Search `main.js` for existing console output forwarding — don't duplicate if it already exists.

---

## TASK 5: TOP INFO BAR — ONLINE FRIENDS COUNT + POINTS (REMOVE DUPLICATES)

### The problem:

There are **duplicate stats showing** — "mods" and "worlds" chips appear both below the Play button AND somewhere above. Also, points display in the right sidebar is overlapping content.

### The fix — create a TOP INFO BAR:

**Above the play area**, place a bar with:
- **Friends chip** — shows online friends count, clickable to open friends tab
- **Points chip** — shows points balance, clickable to navigate to Points page

```html
<div class="top-info-bar">
  <div class="info-chip" onclick="showFriendsTab()">
    <span class="chip-icon">👥</span>
    <span class="chip-value" id="online-friends-count">0</span>
    <span class="chip-label">Online</span>
  </div>
  <div class="info-chip" onclick="nav(this, 'points')">
    <span class="chip-icon">⭐</span>
    <span class="chip-value" id="points-balance-chip">0</span>
    <span class="chip-label">pts</span>
  </div>
</div>
```

### Keep ONLY below Play button:

Mods, Worlds, Packs, Shaders stats (the ONLY place they show):

```html
<div class="play-stats-row">
  <div class="stat-chip">— Mods <span id="mod-count">0</span></div>
  <div class="stat-chip">— Worlds <span id="world-count">0</span></div>
  <div class="stat-chip">— Packs <span id="pack-count">0</span></div>
  <div class="stat-chip">— Shaders <span id="shader-count">0</span></div>
</div>
```

### Remove from right sidebar:

**Delete the points display entirely** from the right sidebar. The right sidebar shows ONLY: Friends/Clans/News tabs and their content.

### Real counters from actual data:

```js
function updateStatCounts() {
  const instance = getCurrentInstance();
  if (!instance) return;

  const modCount = document.getElementById('mod-count');
  const worldCount = document.getElementById('world-count');
  const packCount = document.getElementById('pack-count');
  const shaderCount = document.getElementById('shader-count');

  if (modCount) modCount.textContent = getModCount();
  if (worldCount) worldCount.textContent = getWorldCount();
  if (packCount) packCount.textContent = getPackCount();
  if (shaderCount) shaderCount.textContent = getShaderCount();
}

function updateOnlineFriendsCount() {
  const onlineCount = document.querySelectorAll('.friend-item.online, .friend-item[data-status="online"]').length;
  const el = document.getElementById('online-friends-count');
  if (el) el.textContent = onlineCount;
}

function updatePointsChip() {
  const el = document.getElementById('points-balance-chip');
  if (el) el.textContent = getSocialPoints();
}
```

### Top info bar CSS:

```css
.top-info-bar {
  display: flex;
  justify-content: center;
  gap: 12px;
  padding: 8px 0;
}

.info-chip {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  background: rgba(10, 5, 20, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: background 0.2s;
}

.info-chip:hover {
  background: rgba(139, 92, 246, 0.2);
}

.chip-icon { font-size: 14px; }
.chip-label { color: rgba(255, 255, 255, 0.5); font-size: 11px; }
```

---

## TASK 6: SIDEBAR RESIZE — SMOOTH, PIXEL-PERFECT, 60FPS

The current sidebar resize is laggy and broken. Rebuild it.

### Requirements:

#### Resize handle behavior:
- Thin (4-6px) invisible hit area on the edge of each panel
- On hover, shows subtle 2px line (purple-tinted)
- Cursor changes to `col-resize` or `row-resize`
- **Dragging must move in real-time, exactly matching the mouse position** — no lag, no debounce
- Use `mousemove` with `requestAnimationFrame` for smooth 60fps
- Disable text selection during drag
- Disable pointer events on iframes during drag

#### Left sidebar:
- **Min width**: 50px (collapsed — icons only)
- **Max width**: 320px
- **Snap-to-collapse**: Drag below 80px → snap to 50px
- When collapsed: icons centered, no text, no user chip name/subtitle
- **Collapse button**: 28x28px minimum, clearly visible at top of sidebar
- Clicking collapse button toggles between collapsed (50px) and last-used expanded width
- When collapsed via drag, user CANNOT drag it open — they must click the collapse button
- Collapse button has chevron that flips direction based on state

#### Right sidebar:
- Same behavior as left sidebar but mirrored
- Handle on the LEFT edge
- Same min/max/snap/collapse behavior

#### Vertical resize (between hero and instance list):
- Horizontal resize handle
- Cursor: `row-resize`
- **Min hero height**: 300px
- **Max hero height**: 85% of available height
- **Min instance list height**: 80px
- Smooth 60fps dragging

#### State persistence:
- Save to `localStorage`:
  - `justice-left-sidebar-width`
  - `justice-left-sidebar-collapsed`
  - `justice-right-sidebar-width`
  - `justice-right-sidebar-collapsed`
  - `justice-hero-height`
- Restore on app load
- Default if no saved state: left sidebar 220px, right sidebar 200px, hero 60%

#### Implementation pattern:

```javascript
function initResize(handle, target, options) {
  let isResizing = false;
  let startX, startWidth;

  handle.addEventListener('mousedown', (e) => {
    isResizing = true;
    startX = e.clientX;
    startWidth = target.getBoundingClientRect().width;
    document.body.style.userSelect = 'none';
    document.body.style.cursor = options.cursor || 'col-resize';
  });

  document.addEventListener('mousemove', (e) => {
    if (!isResizing) return;
    requestAnimationFrame(() => {
      const delta = e.clientX - startX;
      let newWidth = options.invert ? startWidth - delta : startWidth + delta;
      newWidth = Math.max(options.min, Math.min(options.max, newWidth));

      if (newWidth < options.snapThreshold) {
        newWidth = options.min;
        target.classList.add('collapsed');
      } else {
        target.classList.remove('collapsed');
      }

      target.style.width = newWidth + 'px';
    });
  });

  document.addEventListener('mouseup', () => {
    if (!isResizing) return;
    isResizing = false;
    document.body.style.userSelect = '';
    document.body.style.cursor = '';
    localStorage.setItem(options.storageKey, target.style.width);
    localStorage.setItem(options.storageKey + '-collapsed', target.classList.contains('collapsed'));
  });
}
```

---

## TASK 7: HIDE BACKEND CODE FROM THE UI

The user should NOT see backend code, API calls, IPC details, raw JSON, error stack traces, or debug output anywhere in the UI (except Console page for game logs).

### What to hide:

1. **No raw JSON** in the UI — format API responses into proper UI elements
2. **No IPC error messages shown to user** — catch all errors and show friendly messages
3. **No console.log output visible** — log output stays in DevTools only
4. **No stack traces in alerts** — replace with friendly error messages
5. **No debug panels or dev tools visible**
6. **No raw URLs visible** to user (except referral links)
7. **Error handling everywhere** — wrap all `ipc.invoke()` in try/catch with user-friendly messages

### Search patterns to find and fix:

```
alert(err)
alert(error)
alert(JSON.stringify
.innerText = JSON.stringify
.textContent = JSON.stringify
.innerHTML = err
catch (e) { /* empty */ }
```

### Friendly error toast system:

```html
<div id="toast-container" style="position:fixed; top:60px; right:20px; z-index:10001; display:flex; flex-direction:column; gap:8px;"></div>
```

```js
function showToast(message, type = 'info', duration = 4000) {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.textContent = message;
  toast.style.cssText = `
    padding: 10px 18px;
    background: rgba(10, 5, 20, 0.95);
    border: 1px solid ${type === 'error' ? 'rgba(248,113,113,0.4)' : type === 'success' ? 'rgba(74,222,128,0.4)' : 'rgba(139,92,246,0.4)'};
    border-radius: 8px;
    color: #fff;
    font-size: 13px;
    font-family: 'Space Grotesk', sans-serif;
    animation: toastIn 0.25s ease;
    cursor: pointer;
    max-width: 320px;
  `;
  toast.onclick = () => toast.remove();
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastOut 0.2s ease forwards';
    setTimeout(() => toast.remove(), 200);
  }, duration);
}

@keyframes toastIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
@keyframes toastOut {
  from { opacity: 1; transform: translateX(0); }
  to { opacity: 0; transform: translateX(20px); }
}
```

Replace all `alert()` calls with `showToast()`.

---

## TASK 8: INSTANCE SELECTOR — SHOW LOADER TYPE (VANILLA/FABRIC/FORGE)

The instance selector above the Play button must display the **loader type** — Vanilla, Fabric, or Forge. The instance cards in the bottom bar already show this correctly (with colored badges), but the selector above Play does not.

### Update the instance selector display:

```html
<div class="instance-selector">
  <img class="instance-icon" src="..." alt="">
  <div class="instance-info">
    <span class="instance-name">screenshots</span>
    <span class="instance-meta">1.21.11 · <span class="loader-badge loader-vanilla">Vanilla</span></span>
  </div>
  <span class="dropdown-arrow">▾</span>
</div>
```

### Loader badge styles (match instance cards):

```css
.loader-badge {
  display: inline-block;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  text-transform: capitalize;
}

.loader-vanilla {
  background: rgba(74, 222, 128, 0.2);
  color: #4ade80;
  border: 1px solid rgba(74, 222, 128, 0.3);
}

.loader-fabric {
  background: rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.loader-forge {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}
```

### CRITICAL BUG: Dropdown items all show "Vanilla" regardless of actual loader type

The dropdown rendering code is hardcoding "Vanilla" or not reading each instance's real loader type. **Fix this:**

Find where the dropdown items are rendered (search for the code that populates the instance list in the dropdown). Each dropdown item must read the `loader` or `loaderType` field from **that specific instance's data** — NOT use a default.

```js
// WRONG — hardcoded:
badgeEl.textContent = 'Vanilla';

// RIGHT — read from each instance's data:
const loaderType = instance.loader || instance.loaderType || instance.modLoader || 'Vanilla';
badgeEl.textContent = loaderType;
badgeEl.className = 'loader-badge loader-' + loaderType.toLowerCase();
```

When the user selects a different instance, update the loader badge to match that instance's real loader type.

---

## TASK 9: INSTANCE SELECTION SYNC — BOTTOM BAR ↔ TOP SELECTOR WITH CONFIRMATION

The instance selector above the Play button and instance cards in the bottom bar must stay in sync. **When clicking an instance card (not the Play button), show a confirmation popup** to prevent misclicks.

### When clicking a card in the bottom bar:

1. Do NOT immediately switch
2. Show confirmation popup: "Switch to [instance name]?"
3. If confirmed → update top selector (name, version, loader badge)
4. If cancelled → stay on current instance

### Confirmation popup HTML:

```html
<div id="instance-confirm-popup" class="instance-confirm hidden">
  <div class="instance-confirm-content">
    <p>Switch to <strong id="confirm-instance-name">instance</strong>?</p>
    <div class="instance-confirm-buttons">
      <button class="confirm-cancel" onclick="cancelInstanceSwitch()">Cancel</button>
      <button class="confirm-yes" onclick="confirmInstanceSwitch()">Switch</button>
    </div>
  </div>
</div>
```

### Confirmation popup CSS:

```css
.instance-confirm {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10000;
  animation: popIn 0.15s ease;
}

.instance-confirm.hidden { display: none; }

.instance-confirm-content {
  background: rgba(10, 5, 20, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 20px 24px;
  text-align: center;
  min-width: 250px;
}

.instance-confirm-content p {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: #fff;
}

.instance-confirm-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.confirm-cancel {
  padding: 7px 16px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: #fff;
  font-size: 13px;
  cursor: pointer;
}

.confirm-yes {
  padding: 7px 16px;
  background: rgba(139, 92, 246, 0.6);
  border: 1px solid rgba(139, 92, 246, 0.5);
  border-radius: 8px;
  color: #fff;
  font-size: 13px;
  cursor: pointer;
}

@keyframes popIn {
  from { opacity: 0; transform: translate(-50%, -50%) scale(0.95); }
  to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
```

### JavaScript:

```js
let pendingInstanceSwitch = null;

function requestInstanceSwitch(instanceData) {
  pendingInstanceSwitch = instanceData;
  const popup = document.getElementById('instance-confirm-popup');
  const nameEl = document.getElementById('confirm-instance-name');
  if (nameEl) nameEl.textContent = instanceData.name;
  popup.classList.remove('hidden');
}

function confirmInstanceSwitch() {
  if (pendingInstanceSwitch) {
    setActiveInstance(pendingInstanceSwitch);
    updateInstanceSelector(pendingInstanceSwitch);
  }
  cancelInstanceSwitch();
}

function cancelInstanceSwitch() {
  pendingInstanceSwitch = null;
  document.getElementById('instance-confirm-popup').classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') cancelInstanceSwitch();
});
```

### Sync rules:

1. **Top selector always reflects active instance** — name, version, loader type
2. **Bottom bar highlights active instance** — visible border/glow
3. **Clicking "Play" on a card** launches directly (no confirmation)
4. **Clicking the card body** triggers confirmation popup
5. **Top dropdown** can also switch instances (keep existing behavior)

---

## TASK 10: FIX CLANS — BROKEN AUTH CHECK + CREATE OPTION

### THE BUG:

Clans tab shows **"Sign in to view and manage your clan"** even when the user IS already signed in. This is a broken condition check.

### ROOT CAUSE — find and fix:

1. **Search for the "Sign in to view and manage your clan" text** — find the exact element
2. **Find the condition controlling whether it shows** — there will be an `if` check
3. **Debug WHY it thinks the user isn't signed in** — most likely:
   - Clans tab is checking a different auth state than Friends (which works)
   - Social auth state variable isn't being set
   - Clans tab has its own broken auth check
4. **Fix the condition** to use the same auth state as Friends (since Friends works)

### After fixing auth check:

**When signed in and NOT in a clan:**
- Show "Create Clan" button
- Modal: Clan Name input, Clan Tag input, optional description

**When signed in and IN a clan:**
- Show clan name and tag at top
- Members list with online status
- Invite player button
- Leave clan button

### Backend wiring:

**Search `main.js`** for clan IPC handlers: `create-clan`, `get-clan`, `join-clan`, `leave-clan`, `clan-members`, `invite-to-clan`. Wire the UI buttons to these existing handlers.

---

## TASK 11: FIX NEWS TAB — REGRESSION, WAS WORKING BEFORE

### THE BUG:

News tab stuck on **"Loading latest news..."** forever. This is a REGRESSION — it WAS working before. The backend has news data. The spinner just spins.

### DEBUGGING STEPS — do all of these:

1. **Search for "Loading latest news" text** — find the exact element and code around it
2. **Find the function that loads news** — search for `loadNews`, `fetchNews`, `getNews`
3. **Find the IPC call** — search for `ipc.invoke` calls related to news: `get-news`, `fetch-news`, `news`, `announcements`
4. **Check if the IPC call is being made** — add `console.log('Loading news...')` before the call to verify
5. **Check if the IPC handler exists in `main.js`** — search for matching `ipcMain.handle(...)`. It might have been removed or renamed
6. **Check for silent errors** — the IPC call might be throwing an error swallowed by empty `catch` block
7. **Check if the news rendering function exists** — if data comes back, but rendering is broken, nothing displays
8. **Check the network** — if news comes from an API, the URL might have changed or CORS might be blocking

### The fix pattern:

```js
async function loadNews() {
  const newsContainer = document.querySelector('.news-content');
  if (!newsContainer) return;

  newsContainer.innerHTML = '<div class="loading-state">Loading news...</div>';

  try {
    console.log('[News] Fetching news...');
    const news = await ipc.invoke('get-news'); // USE THE CORRECT EXISTING CHANNEL NAME
    console.log('[News] Got response:', news);

    if (!news || (Array.isArray(news) && news.length === 0)) {
      newsContainer.innerHTML = '<div class="empty-state"><p>No news yet.</p></div>';
      return;
    }
    renderNewsItems(newsContainer, news);
  } catch (err) {
    console.error('[News] Failed to load:', err);
    newsContainer.innerHTML = `
      <div class="empty-state">
        <p>Could not load news.</p>
        <button onclick="loadNews()" style="padding:7px 14px; background:rgba(139,92,246,0.4); border:1px solid rgba(139,92,246,0.5); border-radius:8px; color:#fff; cursor:pointer; margin-top:8px;">Try Again</button>
      </div>
    `;
  }
}
```

**The news data IS in the database.** Find the backend handler, verify it works, reconnect the frontend to it. If the handler was renamed during a refactor, restore it.

---

## TASK 12: VERIFY ADD FRIEND WORKS END-TO-END

The Add Friend button exists but needs end-to-end verification:

1. **Click "Add Friend"** → modal opens
2. **Enter username** → input accepts text
3. **Click "Send Request"** → calls IPC handler, succeeds or shows error
4. **On success** → modal closes, friend appears in list or "Pending" section
5. **On error** → show toast notification with friendly message (not raw error)

### What to check:

- `sendFriendRequest()` calls `ipc.invoke('add-friend', ...)` (or whatever the existing channel is)
- Response handled — success closes modal + refreshes list, error shows toast
- Friends list refreshes after adding
- **Search `main.js`** for matching IPC handler and verify it exists and works

---

## TASK 13: POINTS PAGE — CENTER CONTENT & COMPLETE THE UI

The Points page content is currently left-aligned. Center it in the main content area.

### Fix layout:

```css
.points-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px;
  overflow-y: auto;
  height: 100%;
}

.points-page > * {
  width: 100%;
  max-width: 600px;
}

.points-page h1,
.points-page .page-title {
  text-align: center;
}
```

### Complete the Points page UI:

Make sure every section is fully styled and functional:

1. **YOUR BALANCE** — centered, prominent number, conversion rate to DonutSMP coins
2. **Referral Link** — input with copy button, both properly styled
3. **People You Referred** — list of referred users or "Nothing here yet"
4. **Points History** — scrollable transaction list (green for earned, red for spent)
5. **Withdraw Points** — form with amount input, username field, "Request Withdrawal" button
6. **Withdrawal History** — list with status badges (PENDING, COMPLETED, DENIED)

### Card style:

```css
.points-card {
  background: rgba(10, 5, 20, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 16px;
}

.points-card h3 {
  font-size: 14px;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.9);
  margin: 0 0 12px 0;
}

.points-balance-number {
  font-size: 48px;
  font-weight: 800;
  color: #fff;
  text-align: center;
  margin: 8px 0;
}

.points-balance-subtitle {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
}
```

**Make sure the page is COMPLETE** — no placeholder sections, no TODO comments, no missing functionality.

---

## TASK 14: CENTER ALL PAGES WITH SINGLE-COLUMN LAYOUT

**Every page** with single-column content (not two-panel) should be centered in the main content area.

### Pages to check and fix:

1. Points (already covered in Task 13)
2. Plus — perks list and Discord message button centered
3. Events — all content centered
4. Screenshots — gallery centered or responsive grid
5. News (full article view) — text centered with readable max-width
6. Any other page with left-aligned content

### Universal fix:

```css
.single-column-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px;
  overflow-y: auto;
  height: 100%;
}

.single-column-page > * {
  width: 100%;
  max-width: 700px;
}
```

---

## TASK 15: BOTTOM BAR — GROWS UPWARD FROM BOTTOM, NEVER DETACHES

### THE PROBLEM:

When dragging the bottom bar upward, it **lifts off the bottom of the window** and floats in the middle with raw background visible BELOW it. This is WRONG. The bottom bar must be anchored to the bottom — dragging makes it taller, not detached.

```
WRONG (current behavior):

  ┌─────────────────────────┐
  │     main content         │
  │                          │
  │   ┌──────────────────┐   │  ← bar floating
  │   │  instance cards   │   │
  │   └──────────────────┘   │
  │                          │
  │   RAW BACKGROUND IMAGE   │  ← exposed gap = BAD
  └─────────────────────────┘

RIGHT (what it should do):

  ┌─────────────────────────┐
  │     main content         │
  │                          │
  ├─────────────────────────┤  ← top edge moves UP
  │  drag handle             │
  │  instance cards          │
  │  (more content)          │
  └─────────────────────────┘  ← bottom ALWAYS at window edge
```

### CRITICAL RULE:

The bottom bar is NOT floating. It is anchored to `bottom: 0` at ALL times. Dragging INCREASES its HEIGHT, not changes position. The bottom of the bar NEVER moves. Only the top edge moves upward.

### Implementation — bar is a flex child, NOT position:absolute:

**DO NOT use `position: absolute` or `position: fixed`.** That's what causes detachment. Make it a **flex child** that grows:

```css
.app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

.titlebar { flex-shrink: 0; }

.main-area {
  flex: 1;
  display: flex;
  min-height: 0;
  overflow: hidden;
}

#bottom-bar {
  flex-shrink: 0;
  height: 45px;
  min-height: 45px;
  max-height: 60vh;
  background: rgba(10, 5, 20, 0.7);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: height 0.15s ease;
  /* NO position: absolute */
  /* NO position: fixed */
}
```

**WHY:** In a flex column, the bottom bar is always the last child → always at the bottom. When you increase its `height`, the `.main-area` above SHRINKS (because it has `flex: 1` and `min-height: 0`). The bar grows upward.

### Drag handle CSS:

```css
.bottom-bar-drag-handle {
  width: 100%;
  height: 12px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: ns-resize;
  flex-shrink: 0;
}

.bottom-bar-drag-handle::after {
  content: '';
  width: 36px;
  height: 3px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 2px;
  transition: background 0.2s;
}

.bottom-bar-drag-handle:hover::after {
  background: rgba(255, 255, 255, 0.3);
}

.bottom-bar-default {
  display: flex;
  align-items: center;
  padding: 0 16px;
  height: 45px;
  flex-shrink: 0;
}

.bottom-bar-expanded {
  flex: 1;
  overflow-y: auto;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
```

### Drag behavior JavaScript:

```js
let bottomBarDragging = false;
let bottomBarStartY = 0;
let bottomBarStartHeight = 0;

const BOTTOM_BAR_SNAP_COLLAPSED = 45;
const BOTTOM_BAR_SNAP_MEDIUM = 200;
const BOTTOM_BAR_SNAP_EXPANDED_RATIO = 0.4;

function initBottomBarDrag() {
  const handle = document.querySelector('.bottom-bar-drag-handle');
  const bar = document.getElementById('bottom-bar');
  if (!handle || !bar) return;

  handle.addEventListener('mousedown', (e) => {
    bottomBarDragging = true;
    bottomBarStartY = e.clientY;
    bottomBarStartHeight = bar.offsetHeight;
    bar.style.transition = 'none';
    document.body.style.cursor = 'ns-resize';
    document.body.style.userSelect = 'none';
    e.preventDefault();
  });

  document.addEventListener('mousemove', (e) => {
    if (!bottomBarDragging) return;
    const delta = bottomBarStartY - e.clientY;
    const newHeight = Math.max(BOTTOM_BAR_SNAP_COLLAPSED, Math.min(window.innerHeight * 0.6, bottomBarStartHeight + delta));
    bar.style.height = newHeight + 'px';
  });

  document.addEventListener('mouseup', () => {
    if (!bottomBarDragging) return;
    bottomBarDragging = false;
    document.body.style.cursor = '';
    document.body.style.userSelect = '';

    const bar = document.getElementById('bottom-bar');
    bar.style.transition = 'height 0.15s ease';
    const h = bar.offsetHeight;
    const maxExpanded = window.innerHeight * BOTTOM_BAR_SNAP_EXPANDED_RATIO;

    let snapTo;
    if (h < (BOTTOM_BAR_SNAP_COLLAPSED + BOTTOM_BAR_SNAP_MEDIUM) / 2) {
      snapTo = BOTTOM_BAR_SNAP_COLLAPSED;
    } else if (h < (BOTTOM_BAR_SNAP_MEDIUM + maxExpanded) / 2) {
      snapTo = BOTTOM_BAR_SNAP_MEDIUM;
    } else {
      snapTo = maxExpanded;
    }

    bar.style.height = snapTo + 'px';
    localStorage.setItem('justice-bottom-bar-height', snapTo);
  });

  const saved = localStorage.getItem('justice-bottom-bar-height');
  if (saved) {
    bar.style.height = saved + 'px';
  }
}

initBottomBarDrag();
```

### VERIFY — critical check:

1. Collapse bottom bar to 45px → look at window bottom → **Is there ANY gap or raw background showing BELOW the bar?** If yes, it's broken.
2. Expand to medium → bar should be 200px tall with bottom edge touching window edge
3. Expand to max → same thing, bottom edge always at `y = window.innerHeight`
4. At NO point should the bar detach from the bottom

**If bar uses `position: absolute` or `position: fixed` — that's the bug. Remove it and make it a flex child.**

---

## TASK 16: RESPONSIVE LAYOUT — PLAY BUTTON CENTERED AT ALL SIZES

The Play button must ALWAYS be dead center of the main content area, regardless of window size. When resized smaller, everything must stay clean and organized with no breaking, overlapping, or awkward behavior.

### Play button centering CSS — at ALL sizes:

```css
.play-page,
.play-hub,
[class*="play-page"],
[class*="play-hub"] {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  min-height: 0;
  position: relative;
}

.play-button-wrapper,
.play-section,
[class*="play-btn-wrap"] {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  width: 100%;
}
```

### General responsive rules:

1. **All pages use `flex` or `grid` layouts** that adapt to available space — no fixed pixel widths that break
2. **Sidebars auto-collapse to icon-only** when window narrows (below ~500px main content area)
3. **Text doesn't overflow** — use `overflow: hidden; text-overflow: ellipsis; white-space: nowrap;` on long labels
4. **Cards and grids reflow** — use `flex-wrap: wrap` so they stack vertically when space is tight
5. **No horizontal scrollbars ever** — graceful overflow handling everywhere
6. **Minimum window size**: 1024x768 → looks good (below that, acceptable for things to be tight)

### Test these window sizes:

- **1920x1080** — fullscreen on 1080p, everything spacious
- **1440x900** — slightly tighter, still 3-panel
- **1280x720** — sidebars narrower but functional
- **1024x768** — minimum supported, sidebars may auto-collapse

**At EVERY size, the Play button must be centered** in whatever space is available.

### Media queries:

```css
@media (max-width: 1100px) {
  /* Reduce padding, tighten gaps */
  /* Instance grid: fewer columns */
}

@media (max-width: 900px) {
  /* Stack layout if needed */
  /* Auto-collapse both sidebars */
}
```

---

## TASK 17: EVERYTHING BIGGER — GLOBAL SIZE PASS

### Minimum sizes (enforce everywhere):

- **Body text / labels**: 13px minimum, prefer 14px
- **Navigation items**: 15px font, 44px tall hit targets
- **Section headers**: 12px minimum (uppercase labels)
- **Buttons**: minimum 36px height, 14px font
- **Input fields**: 44px height, 14px font
- **Icons in nav**: 20px minimum
- **Collapse/toggle buttons**: 32x32px minimum, clearly visible
- **Titlebar**: 48px height, logo text 18px
- **Window controls**: 48x36px
- **Modal titles**: 20px
- **Modal close buttons**: 36x36px hit area
- **Toggle switches**: 44x24px
- **Sidebar user chip**: generous padding (12px 16px), avatar 36px
- **Status bar text**: 13px minimum
- **Tab labels** (Friends/Clans/News): 14px
- **Instance cards**: bigger icons (52px), title 15px, subtitle 13px

### Approach:

- Search-and-replace pass through all CSS in `<style>`
- Find every `font-size` and ensure it meets minimums
- Find every `padding` on interactive elements — ensure adequate hit targets
- Find every button `width`/`height` — ensure 36px+ height

---

## TASK 18: ZERO BLANK SPACE

Go through every page and eliminate dead space:

- **Play page**: Hero expands to fill available space. Instance list fills the rest. No gaps.
- **Other pages** (Mods, Worlds, Shaders, etc.): Content grids fill the page. Use subtle background patterns or adjust padding so nothing feels empty.
- **Sidebars**: Appropriate spacing but not excessive gaps. Use 1px borders or subtle gradients for visual separation instead of blank space.
- **Right sidebar**: Empty states styled attractively — nice centered message with icon, not floating text.
- **Instance cards**: Fill their grid cells fully. Consistent heights.

---

## TASK 19: STATE PERSISTENCE

Every user preference and UI state persists across app restarts via `localStorage`:

### What to persist:

- All panel widths/heights and collapsed states
- Selected instance
- RAM slider value
- Right sidebar active tab (Friends/Clans/News)
- Section collapse states in sidebar (CONTENT, PLAYER, EXTRAS)
- Search field contents (optional)
- Scroll positions on pages (nice to have)
- Bottom bar height

### Implementation:

```js
const UIState = {
  _key: 'justice-ui-state',
  _state: {},

  load() {
    try {
      this._state = JSON.parse(localStorage.getItem(this._key) || '{}');
    } catch { this._state = {}; }
  },

  save() {
    localStorage.setItem(this._key, JSON.stringify(this._state));
  },

  get(key, defaultVal) {
    return this._state[key] !== undefined ? this._state[key] : defaultVal;
  },

  set(key, val) {
    this._state[key] = val;
    this.save();
  }
};

UIState.load();
```

Apply saved state immediately on DOM ready — before animations play, so the UI doesn't flash.

---

## TASK 20: VISUAL POLISH & ANIMATIONS

Use ui-ux-pro-max toolkit results to guide these decisions. Goal is a launcher that feels like Steam, Lunar Client, or Prism Launcher in quality.

### Specific improvements:

- **Sidebar nav items**: Active state has filled background with subtle left border accent. Hover states feel responsive — slight translateX, background fade-in.
- **Cards**: Instance cards, mod cards, etc. have subtle inner shadows, consistent border-radius (12-14px), scale-up on hover (1.02x).
- **Transitions**: Everything uses `--transition-smooth` cubic bezier. No instant state changes. Keep transitions SHORT (150-250ms) — not sluggish.
- **Scrollbars**: Thin (4px), rounded, purple-tinted, only visible on hover. Consistent across all scrollable areas.
- **Focus states**: All interactive elements have visible focus indicators (outline or box-shadow with purple glow) for keyboard navigation.
- **Loading states**: Any async operations show skeleton loaders or subtle pulse animations, never blank space.
- **Empty states**: Every page has nice empty state with icon + message + call-to-action button when there's no content.

### Collapsed sidebar state:

When the sidebar is collapsed (50px, icons only):
- Icons perfectly centered (both horizontally and vertically)
- No text leaks out
- Tooltips appear on hover (CSS `::after` pseudo-element or lightweight JS)
- Section headers become thin horizontal divider lines
- User chip shows only avatar circle, centered
- Collapse button shows "expand" chevron (pointing right for left sidebar, pointing left for right sidebar)
- Settings and Game Folder buttons icon-only, centered

---

## TASK 21: CODE COMPLETION AUDIT

Search `src/index.html` for and remove:

- TODO/FIXME/HACK/XXX comments
- Empty catch blocks: `catch(e) {}`
- `alert()` calls (replace with `showToast()`)
- `.innerHTML = err` or `.textContent = JSON.stringify`
- `console.log` output visible in UI (DevTools only)
- Undefined function calls
- Duplicate IDs in HTML
- Empty arrow functions: `() => {}`
- Empty functions: `function() {}`

Fix or remove every instance found.

---

## IMPLEMENTATION ORDER

### PHASE 0 — FIX BUGS FIRST (from KNOWN BUGS section above)
1. **BUG 1** — Fix social login: must call `fetch()` to Justice API, NOT open Microsoft OAuth
2. **BUG 2** — Fix instance dropdown: each item must show its OWN loader type
3. **BUG 3** — Fix instance switch: must NOT switch until user clicks "Switch" in confirmation
4. **BUG 4** — Fix friend search: add live autocomplete with debounced API search
5. **BUG 5** — Fix Clans: check `jlToken`, call `/api/clans.php?action=my`, show create/view
6. **BUG 6** — Fix News: call `/api/news.php?action=list` (NO auth header), render posts
7. **BUG 7** — Fix RAM save: persist to localStorage, restore on load, show confirmation toast

### PHASE 1 — Core Features
8. **Task 1** — Social sign-in system (with correct `fetch()` to API)
9. **Task 2** — Username + Minecraft skin face in sidebar
10. **Task 3** — Admin/Staff role badges from `jlUser.role`
11. **Task 5** — Top info bar: online friends + points (remove duplicates)
12. **Task 8** — Instance selector loader badges
13. **Task 9** — Instance selection sync with confirmation popup

### PHASE 2 — Layout & Polish
14. **Task 15** — Bottom bar anchored to bottom + expandable
15. **Task 16** — Responsive layout, Play button centered at all sizes
16. **Task 13** — Points page centered + complete UI
17. **Task 14** — Center all single-column pages
18. **Task 7** — Hide backend code, toast system for errors
19. **Task 4** — Game console log capture

### PHASE 3 — Final Polish
20. **Task 6** — Sidebar resize smooth + pixel-perfect
21. **Task 17** — Everything bigger global size pass
22. **Task 18** — Zero blank space everywhere
23. **Task 19** — State persistence (RAM, sidebar widths, etc.)
24. **Task 20** — Visual polish & animations
25. **Task 21** — Code completion audit

### PHASE 4 — Full Verification (see MANDATORY VERIFICATION section below)

---

## MANDATORY VERIFICATION — FRONTEND ↔ BACKEND WIRING AUDIT

This is the MOST IMPORTANT section. After all tasks are complete, do a full audit:

### Step 1: Map ALL API Calls and IPC Channels

**For social features (friends, clans, news, points, auth):** These use `fetch()` to `https://justiceclient.org/api/`. Search `src/index.html` for every `fetch(` and `jlFetch(` call. Verify:
- Login calls `/api/login.php` via `fetch()` — NOT Microsoft OAuth, NOT IPC
- News calls `/api/news.php?action=list` — NO auth header
- Clans calls `/api/clans.php?action=my` — WITH auth header
- Friends calls `/api/friends.php?action=search` — WITH auth header
- Points calls `/api/points.php?action=balance` — WITH auth header

**For game features (launch, mods, worlds, etc.):** These use Electron IPC. Search `main.js` for every `ipcMain.handle(...)` and `ipcMain.on(...)`. Then search `src/index.html` for every `ipc.invoke(...)` and `ipc.on(...)`. Verify they match.

```
// SOCIAL = fetch() to REST API (no IPC)
fetch('https://justiceclient.org/api/login.php', ...)    // auth
fetch('https://justiceclient.org/api/news.php', ...)     // news (public)
fetch('https://justiceclient.org/api/clans.php', ...)    // clans (authed)
fetch('https://justiceclient.org/api/friends.php', ...)  // friends (authed)
fetch('https://justiceclient.org/api/points.php', ...)   // points (authed)

// GAME = IPC to Electron main process
ipcMain.handle('launch-instance', ...)
ipcMain.handle('get-mods', ...)
...etc...
```

Then search `src/index.html` for every `ipc.invoke(...)` and `ipc.on(...)`. Make sure every frontend call has a matching backend handler and vice versa. No orphaned IPC calls. No handlers with no frontend consumers.

### Step 2: Click Every Button

Go through EVERY button, link, and interactive element in the app. Trace its onclick handler to the function it calls. Verify that function exists, does something, and handles errors. No dead buttons. No buttons that go nowhere.

### Step 3: Test Every Page

Navigate to EVERY page via the sidebar. Verify:
- Page loads
- Content displays
- No JavaScript errors
- No blank screens
- All data populates

Check in DevTools for any console errors. Zero errors allowed.

### Step 4: Test Social Flow End-to-End

1. Start app (not signed into social)
2. Right sidebar shows sign-in prompt
3. Click sign in → modal opens
4. Enter credentials → IPC call fires → backend responds → UI updates
5. Friends tab shows real friends list
6. Clans tab shows create clan or clan info (NOT "sign in to view")
7. News tab loads real news (NOT stuck on "Loading...")
8. Add Friend works end-to-end
9. Points show in top info bar with real data

### Step 5: Test Instance System

1. Instance selector shows correct name + version + loader badge (Vanilla/Fabric)
2. Open dropdown → ALL items show CORRECT loader types (not all "Vanilla")
3. Click instance card in bottom bar → confirmation popup appears
4. Confirm → top selector updates
5. Click Play on card → launches directly
6. Bottom bar and top selector always in sync

### Step 6: Test Bottom Bar

1. Bottom bar's bottom edge = window bottom edge at all times
2. Drag handle up → bar grows taller, content area shrinks
3. NO gap/raw background visible below bar at any height
4. Snap to collapsed/medium/expanded
5. Height persists across reload

### Step 7: Test Responsive

1. **1920x1080** — Play centered, 3-panel layout, everything spacious
2. **1280x720** — Play centered, sidebars narrower
3. **1024x768** — Play centered, sidebars may auto-collapse

### Step 8: Code Quality Scan

Search for and fix:
- TODO/FIXME/HACK/XXX comments
- Empty catch blocks
- alert() calls (replace with showToast())
- .innerHTML = err or .textContent = JSON.stringify
- console.log output visible in UI
- Undefined function calls
- Duplicate IDs in HTML

---

## TESTING CHECKLIST

After implementation, verify ALL of these:

### Authentication & Social
- [ ] Right sidebar shows social sign-in prompt when not signed in
- [ ] Social sign-in modal opens, accepts credentials
- [ ] After social sign-in, right sidebar shows friends/clans/news
- [ ] Microsoft sign-in still works (unchanged)
- [ ] Social sign-in is separate from Microsoft sign-in

### Username, Skin & Badges
- [ ] User chip shows actual Minecraft username (not "Player")
- [ ] User chip shows Minecraft skin face (pixelated, slight border-radius)
- [ ] Skin face has fallback if image fails
- [ ] Admin/Staff badge shows next to username in sidebar (if applicable)
- [ ] Admin/Staff badge shows in right sidebar (if applicable)
- [ ] Users without role show NO badge

### Instance Selector & Sync
- [ ] Instance selector above Play shows loader type (Vanilla/Fabric/Forge)
- [ ] Loader badge updates when switching instances
- [ ] Badge colors match: green=Vanilla, yellow=Fabric, blue=Forge
- [ ] Clicking instance card body in bottom bar shows confirmation popup
- [ ] Confirming switches top selector to that instance
- [ ] Cancelling keeps current instance unchanged
- [ ] Clicking "Play" on card launches directly (no confirmation)
- [ ] Active instance highlighted in bottom bar
- [ ] Top selector and bottom bar always in sync
- [ ] Escape key closes confirmation popup

### Top Info Bar & Stats (NO DUPLICATES)
- [ ] Top info bar shows Online Friends count + Points balance
- [ ] Friends chip is clickable (opens friends tab)
- [ ] Points chip is clickable (navigates to Points page)
- [ ] Points display REMOVED from right sidebar
- [ ] Mods/Worlds/Packs/Shaders chips show ONLY below Play button
- [ ] All stat counts are REAL numbers (not hardcoded)
- [ ] ZERO duplicate information anywhere

### Friends & Social Features
- [ ] Add Friend button opens modal
- [ ] Add Friend sends request via IPC, closes on success
- [ ] Add Friend shows toast error on failure
- [ ] Friends list refreshes after adding
- [ ] Online friends show with green status dot
- [ ] Offline friends grayed out

### Clans
- [ ] Clans tab shows "Create Clan" button when not in clan (when signed in)
- [ ] Create Clan modal opens with name/tag inputs
- [ ] Creating clan calls backend IPC
- [ ] When in clan, shows clan info with members list

### News
- [ ] News tab loads real news from backend
- [ ] If news fails to load, shows error with "Try Again" button
- [ ] News items display with title, date, content

### Console
- [ ] Game Console captures startup logs from beginning
- [ ] Console shows full log history when navigating to it mid-game
- [ ] Console color-codes log levels

### Page Layouts — Everything Centered
- [ ] Points page content is centered
- [ ] Plus page content is centered
- [ ] Events page content is centered
- [ ] Screenshots page content is centered (or responsive grid)
- [ ] ALL single-column pages are centered with max-width

### Code Quality
- [ ] No raw JSON, stack traces, or API errors visible in UI
- [ ] All alert() calls replaced with toast
- [ ] No TODO/FIXME comments remain
- [ ] No empty/dead functions remain
- [ ] Every page is complete and navigable
- [ ] Every button works

### Responsive & Layout
- [ ] Play button perfectly centered at 1920x1080
- [ ] Play button perfectly centered at 1280x720
- [ ] Play button perfectly centered at 1024x768
- [ ] App layout organized at all tested sizes
- [ ] No horizontal scrollbars at any size
- [ ] Sidebars auto-collapse at narrow widths if needed

### Bottom Bar
- [ ] Bottom bar ALWAYS anchored to bottom edge
- [ ] Drag handle expands panel when dragged up
- [ ] Snaps to collapsed, medium, or expanded height
- [ ] NO raw background visible below bar at ANY height
- [ ] Dark overlay covers 100% of window height
- [ ] Bottom bar expanded shows useful content
- [ ] Height preference persists across restarts

### Sidebar Resize
- [ ] Left sidebar resizes smoothly with mouse drag (60fps, no lag)
- [ ] Left sidebar snaps to collapsed when dragged below 80px
- [ ] Left sidebar cannot be dragged open from collapsed (must click button)
- [ ] Right sidebar has identical behavior (mirrored)
- [ ] Vertical resize between hero and instance list works
- [ ] All resize handles show visual indicator on hover
- [ ] Collapse buttons are 32x32px+ and clearly visible
- [ ] All sidebar widths and collapsed states persist

### Text & Sizing
- [ ] All nav text is 15px+
- [ ] All buttons are 36px+ height
- [ ] No text requires squinting
- [ ] Titlebar text is 18px minimum
- [ ] Window controls are 48x36px
- [ ] User chip padding is generous (12px 16px)

### Visual Polish
- [ ] Page transitions fade out/slide left + fade in/slide right (200-250ms)
- [ ] Cards scale slightly on hover (1.02x)
- [ ] Scrollbars thin (4px), rounded, purple-tinted, hover-visible
- [ ] Focus states visible on interactive elements (purple glow)
- [ ] Loading states show skeleton or pulse (not blank)
- [ ] Empty states attractive with icon + message + button

---

## SUMMARY

Transform Justice Launcher into a **jaw-dropping, premium gaming client**:
- Three-panel layout with resizable/collapsible sidebars
- Smooth animations on everything
- Zero wasted space
- Text easy to read at first glance
- Play button centered and prominent
- Social features fully wired to existing backend
- Responsive at all window sizes
- Everything bigger and bolder
- Friendly error messages (no backend code visible)
- Professional quality comparable to Steam, Lunar Client, or Prism Launcher

This is a COMPLETE implementation guide. Follow the tasks in order, test thoroughly at the end, and ensure every button, page, and feature works flawlessly.
