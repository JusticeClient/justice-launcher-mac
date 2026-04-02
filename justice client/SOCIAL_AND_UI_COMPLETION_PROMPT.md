# Justice Launcher — Social Sign-In, Username Display, Console Fix, Points UI & Code Cleanup

## Project Context

- **File**: `src/index.html` (~6400 lines — `<style>`, HTML, `<script>` all in one file)
- **Tech**: Electron 28, vanilla HTML/CSS/JS, no frameworks
- **Theme**: Dark purple/magenta glassmorphism, Space Grotesk + JetBrains Mono fonts
- **CSS variables**: `--bg0` through `--bg4`, `--t1` through `--t4`, `--purple` variants, `--border`, `--border-hi`
- **State**: `localStorage` for persisting settings
- **Background**: `src/assets/minecraft-bg.png` on body with `background-size: cover`
- **Panels**: Semi-transparent `rgba()` backgrounds with NO blur (blur only used on loading screen)
- **IPC**: `ipc.send()` and `ipc.invoke()` for Electron main process communication

---

## WHAT YOU MUST NOT TOUCH

1. **The Play button** — do NOT change its styling, size, gradient, glow animation, or behavior
2. **Game launching logic** — all `ipc.send('launch-instance', ...)` calls stay the same
3. **The color scheme** — keep the dark purple/magenta glassmorphism theme
4. **No frameworks** — stay vanilla HTML/CSS/JS
5. **Microsoft sign-in flow** — don't break the existing Microsoft OAuth, this is separate

---

## TASK 1: SOCIAL SIGN-IN — Separate from Microsoft Sign-In

Currently the app only has Microsoft sign-in (for the Minecraft account). There needs to be a **separate social sign-in** for the Justice social features (friends, chat, clans, points, etc.). This is its own authentication — NOT the Microsoft account.

### CRITICAL: The backend ALREADY EXISTS

The social system is **already fully built on the backend** — there is an existing database with friends, clans, points, chat, and all social features. The IPC handlers and/or API endpoints for social sign-in, friends, points, etc. are already implemented in `main.js` or connected services. **Do NOT create a new backend or mock data.** Instead:

1. **Search `main.js` for ALL existing IPC handlers** — look for `ipcMain.handle(...)` and `ipcMain.on(...)` calls related to social, friends, auth, points, clans, chat, etc.
2. **Search for existing API calls** — look for `fetch()`, `axios`, `http`, or any HTTP client calls to `justiceclient.org` or any other backend URL
3. **Map every existing backend endpoint** to the frontend buttons and UI elements
4. **Reconnect** the frontend to these existing handlers — make sure every button in the social UI actually calls the correct existing IPC channel

The work here is **frontend wiring**, not backend creation. Find what already exists and connect the UI to it.

### What this means:

- The Microsoft sign-in is for launching Minecraft (the game account). That stays as-is.
- The **Justice social sign-in** is for the launcher's own social system — friends list, chat, clans, points, referrals, etc.
- Users should be able to sign into the social system independently of their Microsoft account.
- The backend for all of this already exists — you just need to connect the frontend buttons to the existing IPC/API calls.

### Where to put the social sign-in:

**Right sidebar** — When the user is NOT signed into the social system, the right sidebar should show a sign-in prompt instead of the friends list. This replaces the current "Sign in to see friends online" placeholder.

```html
<!-- Right sidebar social sign-in state (shown when NOT signed in) -->
<div class="social-signin-prompt" style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; padding:24px; text-align:center; gap:16px;">
  <div style="width:64px; height:64px; border-radius:50%; background:rgba(139,92,246,0.2); display:flex; align-items:center; justify-content:center;">
    <!-- User/social icon -->
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

### Social sign-in button style:

```css
.social-signin-btn {
  padding: 10px 24px;
  background: rgba(139, 92, 246, 0.5);
  border: 1px solid rgba(139, 92, 246, 0.5);
  border-radius: 10px;
  color: #fff;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  width: 100%;
  max-width: 200px;
}

.social-signin-btn:hover {
  background: rgba(139, 92, 246, 0.7);
}
```

### Social sign-in modal:

When the user clicks "Sign In", open a modal with username/password fields for the Justice social account:

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
      // Show error inline in the modal
      alert(result.error || 'Sign in failed');
    }
  } catch (err) {
    console.error('Social sign in error:', err);
  }
}

function updateSocialUI() {
  // If signed in, show friends/clans/chat in right sidebar
  // If not signed in, show the sign-in prompt
  const signedIn = socialUser !== null;
  const prompt = document.querySelector('.social-signin-prompt');
  const socialContent = document.querySelector('.social-content');
  if (prompt) prompt.style.display = signedIn ? 'none' : 'flex';
  if (socialContent) socialContent.style.display = signedIn ? 'block' : 'none';
}

// On app load, check for saved social session
function loadSocialSession() {
  try {
    const saved = localStorage.getItem('justice-social-user');
    if (saved) {
      socialUser = JSON.parse(saved);
      updateSocialUI();
    }
  } catch {}
}

// Call on DOMContentLoaded
loadSocialSession();
```

### IPC handler (main.js) — USE THE EXISTING ONE:

**The backend already has social sign-in, friends, points, clans, and chat fully implemented.** Do NOT create new IPC handlers. Instead:

1. **Search `main.js` for existing social/auth IPC handlers** — grep for patterns like `social`, `auth`, `login`, `signin`, `sign-in`, `friend`, `points`, `clan`, `chat`
2. **Find the existing channel names** — they might be named like `social-signin`, `social-login`, `get-friends`, `get-points`, `add-friend`, `send-message`, etc.
3. **Wire the frontend sign-in modal to the existing IPC call** — use `ipc.invoke('whatever-the-existing-channel-is', { username, password })`
4. **Wire ALL social UI elements to their existing backend calls** — friends list, add friend, clans, points balance, chat, etc.

If for some reason a handler is missing in `main.js` for a specific social feature, THEN create it — but check first. The expectation is that everything already exists on the backend.

---

## TASK 2: SHOW USERNAME INSTEAD OF "PLAYER"

Currently in the left sidebar, the section header says **"PLAYER"** as a generic label. The bottom-left user chip area shows the Microsoft account name (e.g., "Mrjew_") with "Microsoft Account" subtitle.

### Fix the top-left display:

The **user chip** at the bottom-left of the sidebar should be more prominent and show:

1. **The player's Minecraft username** (from the Microsoft sign-in) — NOT the generic word "Player"
2. The username should update dynamically after Microsoft sign-in

### Where to get the username:

The Microsoft auth flow already returns the player's username. Look for where the account data is stored after sign-in:
- Check `localStorage` for any stored account/profile data
- Check for IPC calls that fetch the account info (`ipc.invoke('get-account')` or similar)
- Check the existing user chip element — it already shows "Mrjew_" in the bottom-left, so the data IS available

### What to change:

Find the user chip element in the bottom-left of the sidebar and make sure:
- It shows the actual Minecraft username (e.g., "Mrjew_"), not "Player"
- If the user isn't signed in, show "Not Signed In" or "Sign In" as a clickable prompt
- The "PLAYER" section header in the sidebar can stay as "PLAYER" (that's the nav section name) — but the user chip needs the real username

---

## TASK 3: SHOW MINECRAFT SKIN FACE IN SIDEBAR

Like other Minecraft clients (Lunar, Badlion, Prism Launcher), the user chip in the sidebar should display the player's **Minecraft skin face** (the head portion of their skin).

### How to get the skin face:

Minecraft skin faces can be fetched from these free APIs:

```
https://mc-heads.net/avatar/{username}/32
https://crafatar.com/avatars/{username}?size=32&overlay
https://minotar.net/helm/{username}/32
```

Any of these will return a 32x32 PNG of the player's skin face (with helmet overlay).

### Where to display it:

In the **user chip** at the bottom-left of the sidebar, replace the current avatar circle (the purple "M" initial) with the actual skin face:

```html
<!-- User chip in sidebar bottom -->
<div class="user-chip">
  <img
    class="skin-face"
    src=""
    alt=""
    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
  >
  <div class="skin-face-fallback" style="display:none;">
    <!-- Fallback initial letter if skin fails to load -->
    M
  </div>
  <div class="user-chip-info">
    <span class="user-chip-name">Mrjew_</span>
    <span class="user-chip-subtitle">Microsoft Account</span>
  </div>
</div>
```

### Skin face style:

```css
.skin-face {
  width: 32px;
  height: 32px;
  border-radius: 4px;  /* Slight rounding, NOT full circle — Minecraft skins look best with square/slight-round */
  image-rendering: pixelated;  /* CRITICAL — keeps the pixel art crisp, not blurry */
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

### Loading the skin dynamically:

```js
function updateSkinFace(username) {
  const img = document.querySelector('.skin-face');
  if (img && username) {
    img.src = `https://mc-heads.net/avatar/${encodeURIComponent(username)}/32`;
    img.alt = username;
  }
}

// Call this after Microsoft sign-in succeeds, passing the Minecraft username
// Also call on app load if a saved username exists
```

### Key rules:

- `image-rendering: pixelated` is ESSENTIAL — without it, the 8x8 pixel skin face will be blurry
- Use a slight border-radius (4px), NOT a full circle — Minecraft faces are square and look weird in circles
- The fallback (initial letter in a purple circle) should show if the image fails to load
- Cache the skin URL in localStorage so it loads instantly on next app launch without waiting for the network

---

## TASK 4: GAME CONSOLE — CAPTURE STARTUP LOGS

The Console page (under Player section in the left sidebar) is not capturing the game's startup output. The code for capturing logs is likely already set up but not connected properly.

### What to check and fix:

1. **Find the console page element** — Look for the console/log display area in the HTML
2. **Find the IPC listener for game output** — There should be an IPC channel that forwards game stdout/stderr to the renderer. Common patterns:
   - `ipc.on('game-output', ...)` or `ipc.on('console-log', ...)` or `ipc.on('game-log', ...)`
   - The main process likely spawns the Java process and pipes its stdout
3. **Make sure the listener is registered BEFORE the game launches** — If the listener is set up inside a page-specific init function that only runs when you navigate to the Console page, it will miss early output. The listener should be registered globally on app startup.
4. **Make sure logs are buffered** — Store all log lines in an array so that when the user navigates to the Console page, they see the full history (not just lines that arrived while they were on that page)

### Console display improvements:

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

/* Color-code log levels */
.log-info { color: rgba(255, 255, 255, 0.7); }
.log-warn { color: #fbbf24; }
.log-error { color: #f87171; }
.log-debug { color: rgba(139, 92, 246, 0.7); }
```

### JavaScript for log capturing:

```js
const gameLogBuffer = [];
const MAX_LOG_LINES = 5000;

// Register this GLOBALLY on app startup, NOT inside a page init
ipc.on('game-output', (event, data) => {
  gameLogBuffer.push(data);
  if (gameLogBuffer.length > MAX_LOG_LINES) gameLogBuffer.shift();

  // If the console page is currently visible, append the line immediately
  const consoleEl = document.querySelector('.console-output');
  if (consoleEl && document.querySelector('.console-page.active')) {
    appendLogLine(consoleEl, data);
    // Auto-scroll to bottom if user hasn't scrolled up
    if (consoleEl.scrollTop + consoleEl.clientHeight >= consoleEl.scrollHeight - 50) {
      consoleEl.scrollTop = consoleEl.scrollHeight;
    }
  }
});

function appendLogLine(container, data) {
  const line = document.createElement('div');
  line.textContent = data.text || data;
  // Classify log level
  const text = (data.text || data).toLowerCase();
  if (text.includes('error') || text.includes('exception')) line.className = 'log-error';
  else if (text.includes('warn')) line.className = 'log-warn';
  else if (text.includes('debug')) line.className = 'log-debug';
  else line.className = 'log-info';
  container.appendChild(line);
}

// When navigating TO the console page, render the full buffer
function showConsolePage() {
  const consoleEl = document.querySelector('.console-output');
  if (!consoleEl) return;
  consoleEl.innerHTML = '';
  gameLogBuffer.forEach(data => appendLogLine(consoleEl, data));
  consoleEl.scrollTop = consoleEl.scrollHeight;
}
```

### What to look for in main.js:

Check that the main process is forwarding game output to the renderer:

```js
// In main.js, wherever the Java process is spawned:
gameProcess.stdout.on('data', (data) => {
  mainWindow.webContents.send('game-output', { text: data.toString(), level: 'info' });
});

gameProcess.stderr.on('data', (data) => {
  mainWindow.webContents.send('game-output', { text: data.toString(), level: 'error' });
});
```

**IMPORTANT:** The code for this might already exist. Search the entire codebase for `game-output`, `console-log`, `game-log`, `stdout`, `stderr`, and `spawn`. Wire up whatever exists. Don't duplicate — connect.

---

## TASK 5: TOP INFO BAR — ONLINE FRIENDS COUNT + POINTS (REPLACE DUPLICATE STATS)

### The problem right now:

There are **duplicate stats showing** — "mods" and "worlds" chips appear BOTH below the Play button AND somewhere above it. This duplication is messy. Also, the points display in the right sidebar is overlapping other content and looks buggy.

### The fix — reorganize info so nothing is duplicated:

**TOP BAR** (the row of pill/chip stats above the play area, below the titlebar — where "mods" and "worlds" currently show):
- **Remove the duplicate mods/worlds from this top area**
- **Replace with**: Online Friends count + Points counter
- These should be compact pill chips, clickable:
  - Friends chip → clicking navigates to Friends tab in right sidebar
  - Points chip → clicking navigates to Points page

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

**BELOW THE PLAY BUTTON** — keep the Mods, Worlds, Packs, Shaders chips as-is (these are the ONLY place they show, no duplicates):

```html
<!-- These stay below Play — the counts must be REAL, pulled from actual data -->
<div class="play-stats-row">
  <div class="stat-chip">— Mods <span id="mod-count">0</span></div>
  <div class="stat-chip">— Worlds <span id="world-count">0</span></div>
  <div class="stat-chip">— Packs <span id="pack-count">0</span></div>
  <div class="stat-chip">— Shaders <span id="shader-count">0</span></div>
</div>
```

**RIGHT SIDEBAR** — **REMOVE the points display entirely** from the right sidebar. No points chip, no "0 pts" next to the username. The points are now in the top info bar. The right sidebar should ONLY show: Friends/Clans/News tabs and their content.

### Real counters — these must reflect ACTUAL data:

The mod/world/pack/shader counts must be real counts pulled from the data, NOT hardcoded or placeholders showing "—":

```js
// Update counts from actual instance data
function updateStatCounts() {
  const instance = getCurrentInstance(); // whatever function gets the selected instance
  if (!instance) return;

  // Count actual files/items for the selected instance
  // These should use existing IPC calls or data already loaded
  const modCount = document.getElementById('mod-count');
  const worldCount = document.getElementById('world-count');
  const packCount = document.getElementById('pack-count');
  const shaderCount = document.getElementById('shader-count');

  // Wire to existing data sources — search for where mod lists, world lists, etc. are loaded
  if (modCount) modCount.textContent = getModCount(); // find the existing function
  if (worldCount) worldCount.textContent = getWorldCount();
  if (packCount) packCount.textContent = getPackCount();
  if (shaderCount) shaderCount.textContent = getShaderCount();
}

// Update online friends count from friends list data
function updateOnlineFriendsCount() {
  // Count friends with 'online' status from the existing friends data
  const onlineCount = document.querySelectorAll('.friend-item.online, .friend-item[data-status="online"]').length;
  const el = document.getElementById('online-friends-count');
  if (el) el.textContent = onlineCount;
}

// Update points from existing social data
function updatePointsChip() {
  // Pull from existing points data — search for where points balance is stored/fetched
  const el = document.getElementById('points-balance-chip');
  if (el) el.textContent = getSocialPoints(); // find the existing function
}

// Call these whenever instance changes, friends list updates, or points change
```

### Style for top info bar:

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

## TASK 6: POINTS PAGE — CENTER CONTENT & COMPLETE THE UI

The Points page content is currently left-aligned and not centered in the main content area. Fix the layout so it looks clean and professional.

### Current state (from screenshot):

- The page has: YOUR BALANCE section, referral link, people referred, points history, withdraw points, withdrawal history
- All content is pushed to the left side, not centered
- The cards/sections are about 400-500px wide but sitting flush-left in a much wider content area

### Fix — center the Points page content:

```css
.points-page {
  display: flex;
  flex-direction: column;
  align-items: center;  /* CENTER horizontally */
  padding: 24px;
  overflow-y: auto;
  height: 100%;
}

.points-page > * {
  width: 100%;
  max-width: 600px;  /* Keep cards at a readable width, centered */
}

.points-page h1,
.points-page .page-title {
  text-align: center;
}

.points-page .page-subtitle {
  text-align: center;
}
```

### Complete the Points page UI:

Make sure every section of the Points page is fully styled and functional:

1. **YOUR BALANCE** card — centered, prominent number, shows conversion rate to DonutSMP coins
2. **Referral Link** card — input with copy button, both properly styled
3. **People You Referred** — list of referred users, or "Nothing here yet" empty state
4. **Points History** — scrollable list of point transactions with dates and amounts (green for earned, red for spent)
5. **Withdraw Points** — form with input field for amount, username field, and "Request Withdrawal" button
6. **Withdrawal History** — list of past withdrawals with status badges (PENDING, COMPLETED, DENIED)

### Card style for Points page sections:

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

### Make sure the page is COMPLETE — no placeholder sections, no TODO comments, no missing functionality. Every button should do something, every section should be styled.

---

## TASK 7: HIDE BACKEND CODE FROM THE UI

The user should NOT see any backend code, API calls, IPC details, raw JSON, error stack traces, or debug output anywhere in the UI — **except** on the Console page, which is specifically for viewing game logs.

### What to hide/clean up:

1. **No raw JSON in the UI** — If any page displays raw API responses or JSON objects, format them into proper UI elements
2. **No IPC error messages shown to user** — Catch all IPC errors and show friendly error messages instead (e.g., "Something went wrong. Try again." not `Error: ECONNREFUSED 127.0.0.1:3000`)
3. **No console.log output visible** — Make sure `console.log`, `console.error`, `console.warn` calls in the renderer don't display anything in the app UI (they go to DevTools only, which is fine)
4. **No stack traces in alerts** — Replace any `alert(err)` or `alert(err.stack)` with user-friendly error messages
5. **No debug panels or dev tools left visible** — Search for any debug/dev sections that might be showing
6. **No raw URLs visible** — API endpoint URLs shouldn't be shown to the user (referral links are fine, those are user-facing)
7. **Error handling everywhere** — Wrap all `ipc.invoke()` calls in try/catch blocks with user-friendly error handling

### Search patterns to find and fix:

```
// Find these patterns and clean them up:
alert(err)
alert(error)
alert(JSON.stringify
console.log(    // These are fine in script, but make sure they don't output to UI
.innerText = JSON.stringify
.textContent = JSON.stringify
.innerHTML = err
.innerHTML = error
catch (e) { /* empty */ }    // Replace with proper error handling
```

### Friendly error toast/notification system:

Instead of `alert()` for errors, use a toast notification:

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
```

```css
@keyframes toastIn {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
@keyframes toastOut {
  from { opacity: 1; transform: translateX(0); }
  to { opacity: 0; transform: translateX(20px); }
}
```

Replace all `alert()` calls throughout the app with `showToast()` calls.

---

## TASK 8: ADMIN/STAFF TAGS — DISPLAY USER ROLE BADGES

Users can have roles like **Admin** or **Staff**. These tags need to be visible in BOTH places:

### A. Left sidebar user chip (bottom-left):

Next to the username "Mrjew_", show a small role badge if the user has one:

```html
<div class="user-chip">
  <img class="skin-face" src="..." alt="">
  <div class="user-chip-info">
    <div class="user-chip-name-row">
      <span class="user-chip-name">Mrjew_</span>
      <span class="role-badge role-admin">Admin</span>  <!-- or "Staff" -->
    </div>
    <span class="user-chip-subtitle">Microsoft Account</span>
  </div>
</div>
```

### B. Right sidebar social area:

Next to the username at the top of the right sidebar (where "mrjew_" shows), display the same role badge.

### Role badge styles:

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

### Where to get the role data:

The role/tag comes from the social backend. Search `main.js` for existing IPC handlers that return user profile data — there should be a field like `role`, `tag`, `rank`, or `group` in the user object. Wire it to the badge display. If no role is present, don't show any badge.

---

## TASK 9: FIX CLANS — BROKEN AUTH CHECK + NO CREATE OPTION

### THE BUG:

The Clans tab shows **"Sign in to view and manage your clan"** even when the user IS already signed in. This is a broken condition check. The code is checking a sign-in state variable that either isn't being set correctly, or it's checking the wrong variable (e.g., checking Microsoft auth instead of social auth, or the social auth state isn't propagating to the Clans tab).

### ROOT CAUSE — find and fix:

1. **Search for the "Sign in to view and manage your clan" text** in `src/index.html` — find the exact element
2. **Find the condition that controls whether it shows** — there will be an `if` check like `if (!signedIn)` or `if (!socialUser)` or similar
3. **Debug WHY it thinks the user isn't signed in** — most likely:
   - The clans tab is checking a different auth state than the friends tab (friends tab works, clans doesn't)
   - The social auth state variable isn't being set when social sign-in succeeds
   - The clans tab has its own separate auth check that's broken
4. **Fix the condition** so it uses the same auth state that the Friends tab uses (since Friends works correctly when signed in)

### After fixing the auth check:

When signed in and NOT in a clan, show:
- A "Create Clan" button (not the sign-in message)
- Create Clan modal: Clan Name input, Clan Tag input (short, like [JC]), optional description
- Submit calls the existing backend IPC handler

When signed in and IN a clan, show:
- Clan name and tag at top
- Members list with online status
- Invite player button
- Leave clan button

### Backend wiring:

Search `main.js` for existing clan IPC handlers. Look for: `create-clan`, `get-clan`, `join-clan`, `leave-clan`, `clan-members`, `invite-to-clan`, etc. The backend for clans already exists — the UI just isn't connecting to it because the auth check is broken.

---

## TASK 10: FIX NEWS TAB — REGRESSION, WAS WORKING BEFORE

### THE BUG:

The News tab is permanently stuck on **"Loading latest news..."** and never loads anything. This is a REGRESSION — news WAS loading correctly in a previous version of the app. The backend has news data in the database. The loading spinner just spins forever.

### DEBUGGING STEPS — do all of these:

1. **Search for "Loading latest news" text** in `src/index.html` — find the exact element and the code around it
2. **Find the function that loads news** — search for `loadNews`, `fetchNews`, `getNews`, or whatever function is supposed to populate the news tab
3. **Find the IPC call** — search for `ipc.invoke` calls related to news. Look for channel names like `get-news`, `fetch-news`, `news`, `announcements`, `get-announcements`
4. **Check if the IPC call is actually being made** — add a `console.log('Loading news...')` before the call to verify it runs. It might not be firing at all
5. **Check if the IPC handler exists in `main.js`** — search `main.js` for the matching `ipcMain.handle(...)`. It might have been accidentally removed or renamed
6. **Check for silent errors** — the IPC call might be throwing an error that's being swallowed by an empty `catch` block. This is common: `catch(e) {}` eats the error and the UI stays on "Loading..."
7. **Check if the news rendering function exists** — even if data comes back, if `renderNewsItems` (or equivalent) is broken or missing, nothing will display
8. **Check the network** — if the news comes from an API (not local IPC), the URL might have changed or CORS might be blocking it

### The fix pattern:

```js
async function loadNews() {
  const newsContainer = document.querySelector('.news-content');
  if (!newsContainer) return;

  newsContainer.innerHTML = '<div class="loading-state">Loading news...</div>';

  try {
    console.log('[News] Fetching news...'); // Debug: verify this fires
    const news = await ipc.invoke('get-news'); // USE THE CORRECT EXISTING CHANNEL NAME
    console.log('[News] Got response:', news); // Debug: see what comes back

    if (!news || (Array.isArray(news) && news.length === 0)) {
      newsContainer.innerHTML = '<div class="empty-state"><p>No news yet.</p></div>';
      return;
    }
    renderNewsItems(newsContainer, news);
  } catch (err) {
    console.error('[News] Failed to load:', err); // Debug: see the actual error
    newsContainer.innerHTML = `
      <div class="empty-state">
        <p>Could not load news.</p>
        <button onclick="loadNews()" style="padding:7px 14px; background:rgba(139,92,246,0.4); border:1px solid rgba(139,92,246,0.5); border-radius:8px; color:#fff; cursor:pointer; margin-top:8px;">Try Again</button>
      </div>
    `;
  }
}
```

### IMPORTANT: The news data IS in the database. The backend handler likely exists. Find it, verify it works, reconnect the frontend to it. If the handler was renamed or removed during a refactor, restore it.

---

## TASK 11: VERIFY ADD FRIEND WORKS END-TO-END

The Add Friend button exists in the UI but needs to be verified that it actually works:

1. **Click "Add Friend"** — the modal should open
2. **Enter a username** — the input should accept text
3. **Click "Send Request"** — this should call the existing IPC handler and either succeed or show an error
4. **On success** — the modal should close, and the friend should appear in the friends list (or in a "Pending" section)
5. **On error** — show a toast notification with a friendly error message (not a raw error)

### What to check in the code:

- The `sendFriendRequest()` function (or equivalent) must call `ipc.invoke('add-friend', ...)` (or whatever the existing channel is)
- The response must be handled — success closes modal + refreshes list, error shows toast
- The friends list must refresh after adding a friend
- Search `main.js` for the matching IPC handler and verify it exists and works

---

## TASK 12: INSTANCE SELECTOR — SHOW LOADER TYPE (VANILLA/FABRIC)

The instance selector above the Play button (the dropdown that shows the selected instance) must display the **loader type** — whether the instance is Vanilla or Fabric. The instance cards in the bottom bar already show this correctly (green "Vanilla" badge or orange "Fabric" badge), but the selector above Play does not.

### From the screenshots:

- The instance selector currently shows: icon + instance name + "MC 1.21.11" version
- It should ALSO show: "Vanilla" or "Fabric" badge, matching the style used on the instance cards

### What to add:

```html
<!-- Inside the instance selector display -->
<div class="instance-selector">
  <img class="instance-icon" src="..." alt="">
  <div class="instance-info">
    <span class="instance-name">screenshots</span>
    <span class="instance-meta">1.21.11 · <span class="loader-badge loader-vanilla">Vanilla</span></span>
  </div>
  <span class="dropdown-arrow">▾</span>
</div>
```

### Loader badge styles (match the instance cards):

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

From screenshots: The top selector correctly shows "Fabric" for the "new" instance, BUT when you open the dropdown, ALL items in the list show "Vanilla" — even the ones that are actually Fabric. The dropdown rendering code is either hardcoding "Vanilla" or not reading each instance's actual loader type.

**The fix:**

Find where the dropdown items are rendered (search for the code that creates/populates the instance list in the dropdown). Each dropdown item must read the `loader` or `loaderType` field from **that specific instance's** data object — NOT use a default or fallback value.

```js
// WRONG — hardcoded or using a default:
badgeEl.textContent = 'Vanilla';
badgeEl.className = 'loader-badge loader-vanilla';

// RIGHT — read from each instance's data:
const loaderType = instance.loader || instance.loaderType || instance.modLoader || 'Vanilla';
badgeEl.textContent = loaderType;
badgeEl.className = 'loader-badge loader-' + loaderType.toLowerCase();
```

Search for the dropdown render function — it likely loops through instances and creates list items. Inside that loop, find where it sets the loader text and fix it to use each instance's real data. The instance cards in the bottom bar already show the correct loader, so the data IS there — the dropdown just isn't reading it.

### Dynamic update:

When the user selects a different instance from the dropdown, update the loader badge to match that instance's loader type. The loader type data is already available in the instance objects — search for where instance data is stored (it already shows on the instance cards, so the data exists).

---

## TASK 13: INSTANCE SELECTION SYNC — BOTTOM BAR ↔ TOP SELECTOR WITH CONFIRMATION

The instance selector above the Play button and the instance cards in the bottom bar must stay in sync. When you click an instance card in the bottom bar, it should switch the active instance at the top — but with a **confirmation popup** to prevent misclicks.

### Current problem:

- The top selector shows one instance (e.g., "new · 1.21.11 · Vanilla")
- The bottom bar shows all instance cards (new, adsada, fabric-loader-0.18...)
- Clicking an instance card in the bottom bar should update the top, but there's no confirmation and they may not be synced

### The fix:

**When clicking an instance card in the bottom bar:**

1. Do NOT immediately switch the active instance
2. Show a **confirmation popup** asking: "Switch to [instance name]?"
3. If confirmed → update the top selector to show the new instance (name, version, loader badge)
4. If cancelled → nothing happens, the current instance stays selected

**Confirmation popup (inline, not a full modal — keep it lightweight):**

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
    // Update the top selector to show this instance
    setActiveInstance(pendingInstanceSwitch); // use whatever the existing function is
    // Update the top selector display (name, version, loader badge)
    updateInstanceSelector(pendingInstanceSwitch);
  }
  cancelInstanceSwitch();
}

function cancelInstanceSwitch() {
  pendingInstanceSwitch = null;
  document.getElementById('instance-confirm-popup').classList.add('hidden');
}

// Also close on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') cancelInstanceSwitch();
});
```

### Sync rules:

1. **The top selector always reflects the currently active instance** — name, version, AND loader type (Vanilla/Fabric)
2. **The bottom bar highlights the active instance** — the currently selected instance card should have a visible highlight border (e.g., the pink/magenta border already visible on some cards in the screenshots)
3. **Clicking "Play" on an instance card in the bottom bar** should ALSO switch to that instance and launch it (no confirmation needed for Play — the user's intent is clear)
4. **Clicking the instance card itself (not the Play button)** triggers the confirmation popup
5. **The top dropdown selector** can also be used to switch instances directly (this already works presumably — just make sure it stays in sync)

### What already works — DON'T break:

The "Play" buttons on instance cards likely already launch that instance. Keep that behavior. The confirmation is ONLY for clicking the card body (selecting without launching).

---

## TASK 14: CENTER ALL PAGES WITH SIMILAR LAYOUT ISSUES

The Points page isn't the only page with content shoved to one side. **Every page** that has a single-column content layout (not a two-panel split layout) should have its content centered in the main content area.

### Pages to check and fix:

1. **Points** — center all cards (already covered in Task 6)
2. **Plus** — the perks list and "Message modevs on Discord" button should be centered
3. **Events** — whatever content is here should be centered
4. **Screenshots** — gallery content should be centered or use a responsive grid
5. **News** (when viewing full articles) — text content should be centered with a readable max-width
6. **Any other page** where content is flush-left in a wide content area

### The universal fix for single-column pages:

```css
/* Apply to ALL single-column content pages */
.page-content-centered {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px;
  overflow-y: auto;
  height: 100%;
}

.page-content-centered > * {
  width: 100%;
  max-width: 700px;  /* Readable width, centered */
}

.page-content-centered .page-title {
  text-align: center;
}

.page-content-centered .page-subtitle {
  text-align: center;
  color: rgba(255, 255, 255, 0.5);
}
```

### Apply this class to every single-column page's content container. For two-panel pages (Mods, Worlds, Host World), DON'T center — those have their own split layouts.

---

## TASK 14: FULL CODE COMPLETION & BUG AUDIT

Go through the ENTIRE `src/index.html` and make sure every feature is fully implemented — no placeholder code, no TODO comments, no half-built sections, no empty event handlers.

### Checklist:

1. **Every nav item in the sidebar should navigate to a real, styled page** — no blank pages
2. **Every button should have an onclick handler that does something** — no dead buttons
3. **Every form should submit/process** — no forms that do nothing when you click Submit
4. **Every page should have proper empty states** — not blank white/black space
5. **Every modal should open AND close** — no modals that open but can't be dismissed
6. **Every dropdown should populate and select** — no empty dropdowns
7. **Remove or complete any TODO/FIXME/HACK comments** — either implement what the TODO says or remove the TODO and the dead code around it
8. **Remove any commented-out code blocks** — if code is commented out, it's dead weight. Either it works and should be uncommented, or it doesn't and should be deleted
9. **All IPC handlers should have matching listeners** — if the renderer calls `ipc.invoke('something')`, make sure `main.js` has a matching `ipcMain.handle('something', ...)`
10. **No undefined function calls** — search for function calls and verify every function that's called actually exists

### Search for incomplete code:

```
// Search the file for these patterns:
TODO
FIXME
HACK
XXX
TEMP
// placeholder
// stub
// not implemented
// wire up later
() => {}      // empty arrow functions
function() {} // empty functions
```

Fix or remove every instance found.

---

## TASK 9: RESPONSIVE LAYOUT — PLAY BUTTON CENTERED & APP ORGANIZED AT ALL SIZES

When the app is NOT fullscreen (windowed, resized smaller, etc.), the layout must stay clean, organized, and the Play button must remain perfectly centered in the main content area. No elements should break, overlap, or look awkward at any reasonable window size.

### Play button centering — at ALL window sizes:

The Play button must ALWAYS be dead center of the main content area, regardless of window size. The main content area is the space between the left sidebar and right sidebar. The Play button should be centered both horizontally AND vertically within that area.

```css
/* The play hub / home page container */
.play-page,
.play-hub,
[class*="play-page"],
[class*="play-hub"] {
  display: flex;
  flex-direction: column;
  justify-content: center;  /* Vertical center */
  align-items: center;      /* Horizontal center */
  height: 100%;
  min-height: 0;  /* Prevent flexbox overflow */
  position: relative;
}

/* The play button wrapper should be centered */
.play-button-wrapper,
.play-section,
[class*="play-btn-wrap"],
[class*="play-section"] {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1;
  width: 100%;
}
```

### General responsive rules:

1. **All pages must use `flex` or `grid` layouts that adapt to available space** — no fixed pixel widths that break at smaller sizes (except min-widths for readability)
2. **Sidebars should auto-collapse to icon-only mode when the window gets narrow** — if the main content area would be less than ~500px wide, auto-collapse one or both sidebars
3. **Text should NOT overflow or get clipped** — use `overflow: hidden; text-overflow: ellipsis; white-space: nowrap;` on labels that might get too long
4. **Cards and grids should reflow** — mod cards, modpack cards, etc. should use `flex-wrap: wrap` so they stack vertically when there isn't room for multiple columns
5. **No horizontal scrollbars** — the app should never show a horizontal scrollbar at any size. Every container should handle overflow gracefully
6. **Minimum window size**: The app should look good down to 1024x768. Below that, it's acceptable for things to be tight, but nothing should break

### Test these window sizes:

- **1920x1080** (fullscreen on 1080p) — everything spacious, 3-panel layout
- **1440x900** — slightly tighter, still 3-panel
- **1280x720** — sidebars may need to be narrower, but still functional
- **1024x768** — minimum supported size, sidebars may auto-collapse to icons

At EVERY size, the Play button must be centered in whatever space is available in the main content area.

---

## TASK 16: BOTTOM BAR — GROWS UPWARD FROM BOTTOM, NEVER DETACHES

### THE PROBLEM (from screenshots):

When dragging the bottom bar upward, it **lifts off the bottom of the window** and floats in the middle of the screen with raw background image visible BELOW it. This is wrong. The bottom bar must be like a drawer — its bottom edge is WELDED to the bottom of the window, and dragging just makes it taller. Think of it like pulling up a window shade — the bottom stays fixed, the top edge moves up.

### CRITICAL RULE — READ THIS CAREFULLY:

The bottom bar is NOT a floating panel. It is anchored to `bottom: 0` at ALL times. When you "expand" it, you are INCREASING ITS HEIGHT, not changing its position. The bottom of the bar never moves. Only the top edge moves upward.

```
WRONG (what it does now — bar detaches and floats):

  ┌─────────────────────────┐
  │     main content         │
  │                          │
  │   ┌──────────────────┐   │  ← bar floating in middle
  │   │  instance cards   │   │
  │   └──────────────────┘   │
  │                          │
  │   RAW BACKGROUND IMAGE   │  ← exposed gap below bar = BAD
  └─────────────────────────┘

RIGHT (what it should do — bar grows upward from bottom):

  ┌─────────────────────────┐
  │     main content         │
  │                          │
  ├─────────────────────────┤  ← top edge of bar moves UP
  │  drag handle ───         │
  │  search + new instance   │
  │  instance cards          │
  │  performance settings    │
  │  (more content as needed)│
  └─────────────────────────┘  ← bottom ALWAYS at window edge
```

### Implementation — the bar is a flex child, NOT position:absolute:

**DO NOT use `position: absolute` or `position: fixed` for the bottom bar.** That's what causes it to detach. Instead, make it a **flex child** of the app layout that grows upward by increasing its height:

```css
/* The overall app container */
.app-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  overflow: hidden;
}

/* Titlebar at top — fixed height */
.titlebar { flex-shrink: 0; }

/* Middle area takes ALL remaining space (shrinks when bottom bar grows) */
.main-area {
  flex: 1;
  display: flex;
  min-height: 0;  /* CRITICAL — allows this to shrink below its content size */
  overflow: hidden;
}

/* Bottom bar — NO position:absolute, NO position:fixed */
/* It's a flex child that grows by increasing height */
#bottom-bar {
  flex-shrink: 0;
  height: 45px;              /* default collapsed height */
  min-height: 45px;
  max-height: 60vh;          /* never take more than 60% of viewport */
  background: rgba(10, 5, 20, 0.7);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: height 0.15s ease;
  /* NO position: absolute */
  /* NO position: fixed */
  /* NO bottom: 0 */
}
```

**WHY this works:** In a flex column layout, the bottom bar is always the last child → it's always at the bottom. When you increase its `height`, the `.main-area` above it SHRINKS to make room (because `.main-area` has `flex: 1` and `min-height: 0`). The bar grows upward, pushing the content area smaller. The bottom edge of the bar stays at the bottom of the window because that's where flex puts the last child.

### Drag handle at top of bottom bar:

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

/* The default bar content (always visible) */
.bottom-bar-default {
  display: flex;
  align-items: center;
  padding: 0 16px;
  height: 45px;
  flex-shrink: 0;
}

/* Expanded content (visible when dragged up) */
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
const BOTTOM_BAR_SNAP_EXPANDED_RATIO = 0.4; // 40% of window height

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

    // Snap to nearest snap point
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

  // Restore saved height
  const saved = localStorage.getItem('justice-bottom-bar-height');
  if (saved) {
    bar.style.height = saved + 'px';
  }
}

// Call on DOMContentLoaded
initBottomBarDrag();
```

### VERIFY — do this check after implementing:

1. Collapse the bottom bar to 45px → look at the very bottom of the window → is there ANY gap or raw background showing below the bar? If yes, it's broken.
2. Expand the bar to medium (~200px) → the bar should be 200px tall with its bottom touching the window edge and the main content area above it shrunk to make room.
3. Expand to max (~60vh) → same thing, bottom edge of bar = bottom edge of window. Main content is squeezed small but still visible above.
4. At NO point during dragging should the bar detach from the bottom. The bar's bottom edge is ALWAYS at `y = window.innerHeight`.

**If the bar is using `position: absolute` or `position: fixed` — that's the bug. Remove it and make it a flex child as shown above.**

---

## IMPLEMENTATION ORDER

1. **Task 15** — Code completion & bug audit first (fix the foundation)
2. **Task 7** — Hide backend code / clean up error handling / toast system
3. **Task 16** — Bottom bar anchored to bottom + expandable + no background leak
4. **Task 17** — Responsive layout + Play button always centered at all sizes
5. **Task 2** — Show username instead of "Player"
6. **Task 3** — Show Minecraft skin face
7. **Task 8** — Admin/Staff tag badges in sidebar + right sidebar
8. **Task 12** — Instance selector shows Vanilla/Fabric loader type
9. **Task 13** — Instance selection sync: bottom bar ↔ top selector with confirmation
10. **Task 5** — Top info bar: Online Friends count + Points (remove duplicates)
11. **Task 4** — Fix game console log capture
12. **Task 1** — Social sign-in system
13. **Task 9** — Fix Clans: create/view/manage clan
14. **Task 10** — Fix News: load real news from backend
15. **Task 11** — Verify Add Friend works end-to-end
16. **Task 6** — Center and complete Points page
17. **Task 14** — Center ALL single-column pages (Plus, Events, etc.)
18. **Final pass** — Resize window to multiple sizes, click every button, verify zero duplicated info, verify instance sync works

---

## TESTING CHECKLIST

After implementing, verify ALL of these:

### Auth & Social
- [ ] Right sidebar shows social sign-in prompt when not signed into Justice Social
- [ ] Social sign-in modal opens, accepts credentials, signs in
- [ ] After social sign-in, right sidebar shows friends/clans/news content
- [ ] Microsoft sign-in still works (unchanged)
- [ ] Social sign-in is separate and independent from Microsoft sign-in

### Username, Skin & Tags
- [ ] User chip in bottom-left sidebar shows actual Minecraft username (not "Player")
- [ ] User chip shows Minecraft skin face (pixelated rendering, slight border-radius)
- [ ] Skin face has fallback if image fails to load
- [ ] Admin/Staff badge shows next to username in sidebar user chip (if user has a role)
- [ ] Admin/Staff badge shows next to username in right sidebar (if user has a role)
- [ ] Users without a role show NO badge (not "Player" — just nothing)

### Instance Selector & Sync
- [ ] Instance selector above Play shows loader type badge (Vanilla/Fabric/Forge)
- [ ] Loader badge updates when switching instances
- [ ] Badge colors match: green=Vanilla, yellow/amber=Fabric, blue=Forge
- [ ] Clicking an instance CARD (body) in bottom bar shows confirmation popup "Switch to [name]?"
- [ ] Confirming switches the top selector to that instance (name, version, loader badge all update)
- [ ] Cancelling keeps the current instance unchanged
- [ ] Clicking "Play" on a bottom bar instance card launches it directly (no confirmation needed)
- [ ] The active instance is highlighted in the bottom bar (visible border/glow)
- [ ] Top selector and bottom bar are ALWAYS in sync — same instance selected in both
- [ ] Escape key closes the confirmation popup

### Top Info Bar & Stats (NO DUPLICATES)
- [ ] Top info bar shows Online Friends count + Points balance
- [ ] Friends chip is clickable (opens friends tab)
- [ ] Points chip is clickable (navigates to Points page)
- [ ] Points display REMOVED from right sidebar (no longer showing "0 pts" there)
- [ ] Mods/Worlds/Packs/Shaders chips show ONLY below Play button (not duplicated anywhere else)
- [ ] All stat counts are REAL numbers pulled from actual data (not hardcoded dashes or zeros)
- [ ] ZERO duplicate information anywhere in the app

### Friends & Social Features
- [ ] Add Friend button opens modal
- [ ] Add Friend modal accepts username, sends request via IPC, closes on success
- [ ] Add Friend shows toast error on failure (not raw error)
- [ ] Friends list refreshes after adding a friend
- [ ] Online friends show with green status dot
- [ ] Offline friends show grayed out

### Clans
- [ ] Clans tab shows "Create Clan" button when not in a clan (when signed in)
- [ ] Create Clan modal opens with name/tag inputs
- [ ] Creating a clan calls the existing backend IPC
- [ ] If in a clan, shows clan info with members list

### News
- [ ] News tab loads real news from backend (not stuck on "Loading latest news...")
- [ ] If news fails to load, shows error with "Try Again" button
- [ ] News items display with title, date, and content

### Console
- [ ] Game Console page captures startup logs from the beginning
- [ ] Console shows full log history when navigating to it mid-game
- [ ] Console color-codes log levels (info, warn, error, debug)

### Page Layouts — Everything Centered
- [ ] Points page content is centered in the main content area
- [ ] Plus page content is centered
- [ ] Events page content is centered
- [ ] Screenshots page content is centered (or responsive grid)
- [ ] ALL single-column pages are centered with max-width
- [ ] Two-panel pages (Mods, Worlds, Host World) keep their split layout

### Code Quality
- [ ] No raw JSON, stack traces, or API errors visible anywhere in the UI
- [ ] All alert() calls replaced with toast notifications
- [ ] No TODO/FIXME comments remain in the code
- [ ] No empty/dead functions remain
- [ ] Every page is complete and navigable
- [ ] Every button works

### Responsive & Layout
- [ ] Play button is perfectly centered at 1920x1080
- [ ] Play button is perfectly centered at 1280x720
- [ ] Play button is perfectly centered at 1024x768
- [ ] App layout stays organized and readable at all tested window sizes
- [ ] No horizontal scrollbars appear at any size
- [ ] Sidebars auto-collapse at narrow window widths if needed

### Bottom Bar
- [ ] Bottom bar is ALWAYS anchored to the bottom edge of the window
- [ ] Bottom bar has a drag handle that expands the panel when dragged up
- [ ] Bottom bar snaps to collapsed (~45px), medium (~200px), or expanded (~40%) height
- [ ] NO raw background image visible below the bottom bar at ANY height or state
- [ ] Dark overlay covers 100% of window height — no gaps anywhere
- [ ] Bottom bar expanded state shows useful content (performance settings, etc.)
- [ ] Bottom bar height preference persists across app restarts
