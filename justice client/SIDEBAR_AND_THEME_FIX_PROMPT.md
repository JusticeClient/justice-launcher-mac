# Justice Launcher — Final Polish: Consistent Panels, Bottom Bar, Add Friend, Bug Scan

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

## TASK 1: VISUAL CONSISTENCY — All Panels Must Match the Same Overlay Style

Multiple pages have sub-panels (like version lists, filter sidebars, etc.) that use a solid opaque black background instead of the semi-transparent overlay that the main sidebars use. This looks inconsistent and breaks the aesthetic. EVERY panel in the entire app needs to use the same treatment.

### Pages with this problem (found from screenshots):

1. **Mods page** — The "INSTALLED VERSIONS" list on the left has a solid black/opaque background. Should be semi-transparent.
2. **World Manager** — The "VERSIONS" list on the left has a solid black background. Should be semi-transparent.
3. **Host World** — The left panel (version list + "Select a version to see worlds") has a solid black background. Should be semi-transparent.
4. **Any other page** with a sub-panel, filter sidebar, or secondary container — check ALL of them.

### The fix — apply ONE consistent panel style everywhere:

Search the entire CSS for ANY element that has an opaque dark background. This includes:
- `background: #000`, `background: #0a0510`, `background: #12081f`, `background: black`
- `background: var(--bg0)` or `var(--bg1)` where those variables are opaque hex colors
- `background-color: rgb(...)` with no alpha
- ANY panel, card, container, or sidebar-within-a-page that isn't using the standard overlay

Replace ALL of them with the standard semi-transparent overlay:

```css
/* THE STANDARD PANEL STYLE — use this everywhere */
/* For main sidebars, sub-panels, filter bars, version lists, etc. */
background: rgba(10, 5, 20, 0.7);
```

**Specific elements to find and fix (search by class/id):**

```css
/* Sub-panels inside pages (version lists, filter sidebars) */
.version-list,
.versions-panel,
.installed-versions,
.filter-panel,
.filter-sidebar,
.page-sidebar,
.sub-panel,
[class*="version-list"],
[class*="installed"],
[class*="filter-panel"],
[class*="sub-sidebar"] {
  background: rgba(10, 5, 20, 0.7) !important;
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}

/* Content cards (mod cards, modpack cards, etc.) */
.mod-card,
.modpack-card,
.card,
[class*="-card"],
[class*="-item"] {
  background: rgba(10, 5, 20, 0.5) !important;
  border: 1px solid rgba(255, 255, 255, 0.06);
}

/* Dropdowns, selects, inputs inside pages */
select,
.dropdown,
[class*="select"],
[class*="dropdown"] {
  background: rgba(10, 5, 20, 0.7) !important;
}
```

### How to do a thorough sweep:

1. Search the CSS for `background:` and `background-color:` — check every single one
2. Any that set an opaque color (no alpha channel) on a visible panel/container should be converted to `rgba(10, 5, 20, 0.7)` (for panels) or `rgba(10, 5, 20, 0.5)` (for cards/items)
3. Also search for inline `style="background..."` in the HTML
4. Also check if `--bg0`, `--bg1`, `--bg2` etc. are used as backgrounds — if those CSS variables hold opaque values, the elements using them will be opaque. Either make the variables rgba, or override with specific rgba values

### What the consistent visual hierarchy should be:

```
LAYER                          OPACITY    PURPOSE
─────────────────────────────────────────────────────
body background image          100%       Minecraft mountains, sharp, visible
body::before dark overlay      ~15%       Subtle dimming for readability
Titlebar                       0.7        Dark tint, no blur
Left sidebar                   0.7        Dark tint, no blur
Right sidebar                  0.7        Dark tint, no blur
Bottom bar                     0.7        Dark tint, no blur
Sub-panels (version lists)     0.7        SAME as sidebars — not opaque black
Main content area              0.35       Light tint, bg clearly visible
Pages inside main              transparent The bg shows through from main
Content cards (mods, etc)      0.5        Slightly darker than main for contrast
```

Every panel at 0.7 opacity, every card at 0.5, pages transparent. No exceptions. No opaque blacks anywhere.

---

## TASK 2: CENTER AND CLEAN UP PAGE LAYOUTS

Several pages look slightly off — content isn't centered properly, or there's awkward empty space. Make each page feel professionally laid out.

### Specific pages to check and fix:

**A. Mods page (Installed Versions + mod list):**
- The left panel (INSTALLED VERSIONS) and right panel (mod list / "Select a version") should be properly sized
- The "Select a version on the left to manage its mods" empty state should be perfectly centered vertically and horizontally in the right content area
- The hexagon icon and text should be centered as a group

**B. World Manager:**
- Same layout as Mods — left version list + right content area
- "Select a version from the left to see your worlds" empty state should be centered
- The left panel should be a reasonable width (~220-240px), not too narrow

**C. Host World:**
- Two-panel layout: left (version list + world list) and right (hosting controls)
- Both empty states centered in their respective panels
- "Select a world to host" centered with its icon

**D. Mod Browser & Modpack Browser:**
- These look pretty good already — the cards, search bar, and filter panel are well laid out
- Make sure the filter panel on the left has the same semi-transparent overlay (not opaque)
- Make sure the content cards are evenly spaced

**E. All empty states across the app:**
Every page that shows an empty/placeholder state (icon + "Select a version..." or "No items found" type message) should:
```css
.empty-state,
.placeholder,
[class*="empty"],
[class*="placeholder"] {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  text-align: center;
  gap: 12px;
  color: rgba(255, 255, 255, 0.5);
}

.empty-state svg,
.empty-state .icon {
  width: 48px;
  height: 48px;
  opacity: 0.4;
}

.empty-state p,
.empty-state span {
  font-size: 14px;
  max-width: 300px;
  line-height: 1.5;
}
```

### General layout rules to enforce on ALL pages:

1. **Padding**: Every page should have consistent padding (16-20px) around its content edges
2. **Gap**: Space between cards/items should be consistent (12px for tight lists, 16px for card grids)
3. **Max-width**: Text content shouldn't stretch to 100% width on large screens — cap at a reasonable max-width or center it
4. **Alignment**: Left panels should have consistent widths across pages (e.g., version lists always ~220px)
5. **Border**: Sub-panels that sit next to the main content should have a subtle right border: `border-right: 1px solid rgba(255, 255, 255, 0.06)`

---

## TASK 3: BOTTOM BAR — Match Sidebar Overlay Style

The bottom bar (with "Search instances..." input and "+ New Instance" button) needs the same semi-transparent overlay as the sidebars.

```css
#bottom-bar,
.bottom-bar,
.instance-bar,
.footer-bar,
[class*="bottom-bar"] {
  background: rgba(10, 5, 20, 0.7) !important;
  backdrop-filter: none !important;
  -webkit-backdrop-filter: none !important;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
```

---

## TASK 4: ADD FRIEND BUTTON — Right Sidebar Friends Tab

Add an "Add Friend" button to the right sidebar's Friends tab. It should be functional and connected to the backend.

### UI placement:

Put it at the top of the Friends tab content, as a compact button:

```html
<!-- Inside Friends tab, before the friends list -->
<div class="friends-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px;">
  <button class="add-friend-btn" onclick="openAddFriendModal()">
    + Add Friend
  </button>
</div>
```

### Button style:

```css
.add-friend-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  background: rgba(139, 92, 246, 0.3);
  border: 1px solid rgba(139, 92, 246, 0.4);
  border-radius: 8px;
  color: #FFFFFF;
  font-family: 'Space Grotesk', sans-serif;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s;
  width: 100%;
  justify-content: center;
}

.add-friend-btn:hover {
  background: rgba(139, 92, 246, 0.5);
}
```

### Add Friend Modal:

```html
<div id="add-friend-modal" class="modal hidden">
  <div class="modal-backdrop" onclick="closeAddFriendModal()"></div>
  <div class="modal-content" style="background:rgba(10,5,20,0.95); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:24px; width:400px; max-width:90vw; color:#FFF; position:relative;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <h3 style="margin:0; font-size:18px;">Add Friend</h3>
      <button onclick="closeAddFriendModal()" style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer; opacity:0.6;">&times;</button>
    </div>
    <label style="font-size:13px; color:rgba(255,255,255,0.6);">Enter username</label>
    <input type="text" id="friend-username" placeholder="Minecraft username..." style="width:100%; padding:10px 14px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; font-size:14px; margin-top:8px; box-sizing:border-box; outline:none;">
    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
      <button onclick="closeAddFriendModal()" style="padding:8px 16px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:8px; color:#fff; cursor:pointer;">Cancel</button>
      <button onclick="sendFriendRequest()" style="padding:8px 16px; background:rgba(139,92,246,0.6); border:1px solid rgba(139,92,246,0.5); border-radius:8px; color:#fff; cursor:pointer;">Send Request</button>
    </div>
  </div>
</div>
```

### Modal CSS:

```css
#add-friend-modal {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  justify-content: center;
  align-items: center;
}
#add-friend-modal.hidden { display: none; }
#add-friend-modal .modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
}
#add-friend-modal .modal-content { position: relative; }
#add-friend-modal input:focus {
  border-color: rgba(139, 92, 246, 0.6);
}
#add-friend-modal input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}
```

### JavaScript:

```js
function openAddFriendModal() {
  document.getElementById('add-friend-modal').classList.remove('hidden');
  const input = document.getElementById('friend-username');
  input.value = '';
  input.focus();
}

function closeAddFriendModal() {
  document.getElementById('add-friend-modal').classList.add('hidden');
}

async function sendFriendRequest() {
  const username = document.getElementById('friend-username').value.trim();
  if (!username) {
    document.getElementById('friend-username').style.borderColor = 'rgba(239,68,68,0.6)';
    return;
  }

  try {
    const result = await ipc.invoke('add-friend', { username });
    if (result.success) {
      closeAddFriendModal();
      if (typeof refreshFriendsList === 'function') refreshFriendsList();
    } else {
      alert(result.error || 'Failed to send friend request');
    }
  } catch (err) {
    console.error('Add friend error:', err);
    alert('Could not send friend request.');
  }
}

// Keyboard shortcuts for modal
document.addEventListener('keydown', (e) => {
  const modal = document.getElementById('add-friend-modal');
  if (!modal || modal.classList.contains('hidden')) return;
  if (e.key === 'Enter') sendFriendRequest();
  if (e.key === 'Escape') closeAddFriendModal();
});
```

### Backend IPC (main.js):

Check if there's already a friends system. If so, integrate with it. If not, create a basic local one:

```js
const { ipcMain } = require('electron');

ipcMain.handle('add-friend', async (event, { username }) => {
  try {
    // Check for existing friends system first — integrate with it if found
    // Otherwise, use local storage:
    const Store = require('electron-store');
    const store = new Store();
    const friends = store.get('friends', []);

    if (friends.find(f => f.username.toLowerCase() === username.toLowerCase())) {
      return { success: false, error: 'Already in your friends list' };
    }

    friends.push({ username, addedAt: new Date().toISOString(), status: 'pending' });
    store.set('friends', friends);
    return { success: true };
  } catch (err) {
    return { success: false, error: err.message };
  }
});
```

**IMPORTANT:** Look at the existing codebase first. The launcher likely already has a friends data source and rendering function. Wire into that, don't create a duplicate system.

---

## TASK 5: BUG SCAN — Full App Audit

After completing all tasks above, do a thorough scan of `src/index.html`.

### Check for:

**A. JavaScript errors:**
- Extract the `<script>` block and run `new Function(code)` for syntax check
- Unclosed braces, missing semicolons, mismatched parens
- Undefined function calls
- querySelector references to nonexistent elements

**B. CSS issues:**
- Unclosed rules (missing `}`)
- Invalid property values
- Conflicting `!important` overrides
- CSS variables that are referenced but never defined
- Any remaining opaque backgrounds that should be rgba

**C. HTML structure:**
- All tags properly closed
- No duplicate IDs
- All onclick/onchange handlers reference existing functions

**D. Functional spot-check (go through each):**

1. Left sidebar expanded — nav works, accordion works, one active at a time
2. Left sidebar collapsed — icons visible, tooltips work, scrollable, popups not clipped
3. Right sidebar — Friends/Clans/News tabs work, Add Friend button and modal work
4. Play Hub — version selector, Play button, quick links all work
5. Mods page — version list has transparent overlay (not black), selecting version loads mods
6. Modpacks page — search, filter, Install buttons present
7. Mod Browser (Modrinth) — loads, cards display, Install buttons work
8. Worlds page — version list transparent, selecting version shows worlds
9. Host World — version list transparent, both panels display correctly
10. Resource Packs, Shaders, Screenshots — all load without errors
11. Settings — all sections present, theme toggle works if implemented
12. Background image — sharp and visible on ALL pages (no opaque panels blocking it)
13. Bottom bar — matches sidebar style
14. Loading screen — blur effect, smooth unblur reveal
15. Window resize — no layout breaks at 1024x768 minimum

### Fix every bug found. For each fix note what was broken, why, and how you fixed it.
