# Justice Launcher — Complete UI Overhaul Prompt

## READ THIS FIRST

Before making ANY changes, run the ui-ux-pro-max design intelligence toolkit to inform your decisions:

```bash
cd /path/to/project/ui-ux-pro-max-skill
python3 src/ui-ux-pro-max/scripts/search.py "gaming launcher dark theme glassmorphism" --domain style
python3 src/ui-ux-pro-max/scripts/search.py "desktop app sidebar navigation resize" --domain ux
python3 src/ui-ux-pro-max/scripts/search.py "dark purple gaming UI" --domain color
python3 src/ui-ux-pro-max/scripts/search.py "desktop application layout" --domain product
python3 src/ui-ux-pro-max/scripts/search.py "gaming launcher" --domain landing
```

Use the results from these searches to guide every visual and UX decision you make throughout this overhaul. The toolkit has databases of best practices, color palettes, typography, and UX anti-patterns — lean on them heavily.

---

## PROJECT CONTEXT

- **App**: Justice Launcher — a custom Minecraft client launcher
- **Tech**: Electron 28 + vanilla HTML/CSS/JS (NO React, NO frameworks)
- **Single file**: ALL UI lives in `src/index.html` (~6400 lines — `<style>`, HTML, `<script>` all in one file)
- **Theme**: Dark purple/magenta glassmorphism, Space Grotesk + JetBrains Mono fonts
- **CSS variables**: `--bg0` through `--bg4`, `--t1` through `--t4`, `--purple` variants, `--border`, `--border-hi`, etc.
- **Navigation**: `nav(buttonElement, pageName)` function toggles `.active` class on `.page` elements
- **IPC**: `ipc.send()` and `ipc.invoke()` for Electron main process communication
- **State**: `localStorage` for persisting settings

---

## WHAT YOU MUST NOT TOUCH

1. **The Play button** — do NOT change its styling, size, gradient, glow animation, or behavior. It stays exactly as-is.
2. **`main.js`** — do not modify the Electron main process
3. **Game launching logic** — all `ipc.send('launch-instance', ...)` calls stay the same
4. **Authentication flow** — Microsoft login flow stays the same
5. **The color scheme** — keep the dark purple/magenta glassmorphism theme
6. **No frameworks** — stay vanilla HTML/CSS/JS

---

## TASK 1: PLAY HUB LAYOUT — TRUE CENTER WITH CONTENT ABOVE AND BELOW

The Play button MUST be in the exact vertical center of the main content area. Right now it's pushed toward the top with empty space below. Fix this.

### Layout structure (top to bottom):

```
┌──────────────────────────────────────────┐
│  TOP SECTION (above play button)         │
│  - Player quick info / account chip      │
│  - Or: News ticker / latest updates      │
│  - Or: Quick-access tiles                │
│  Use your design judgment — pick what     │
│  looks best for a gaming launcher.        │
│  This section should be useful, not       │
│  just filler. Keep it compact.            │
├──────────────────────────────────────────┤
│                                          │
│         Version Selector Dropdown        │
│                                          │
│          ┌──────────────────┐            │
│          │    ▶  PLAY       │            │
│          └──────────────────┘            │
│                                          │
│     — Mods  — Worlds  — Packs  — Shaders │
│                                          │
│  Browse Mods · Open Worlds · Screenshots │
│              · New Instance              │
│                                          │
├──────────────────────────────────────────┤
│  BOTTOM SECTION                          │
│  Instance list / cards grid              │
│  Search bar + New Instance button        │
│  (scrollable independently)              │
└──────────────────────────────────────────┘
```

### Rules:
- The play button is the visual anchor — vertically centered in the hero area
- The hero area (version selector + play button + stats + quick actions) should take the MAJORITY of the screen
- The top section should be compact (60-80px max) — just enough to fill dead space and provide utility
- The bottom instance section should be collapsible/minimizable — user can drag it up/down (see Task 3)
- **ZERO blank space** — every pixel should serve a purpose
- The version selector dropdown, when opened, should appear BELOW the version selector button and ABOVE the play button visually (it currently has `z-index:20` on `.play-version-selector` and `z-index:100` on `.play-ver-dropdown` — keep those or increase them so it always appears on top of everything)
- The `+ New Instance` option inside the dropdown should stay inside the dropdown (it works fine now)

---

## TASK 2: SIDEBAR RESIZE — SMOOTH, PIXEL-PERFECT, WORKS ON EVERY PANEL

The current sidebar resize is **laggy, delayed, and broken**. Rip it out and rebuild it properly.

### Requirements:

#### Resize handle behavior:
- The resize handle must be a thin (4-6px) invisible hit area on the edge of each panel
- On hover, it shows a subtle 2px line (purple-tinted)
- The cursor changes to `col-resize` (for side panels) or `row-resize` (for vertical splits)
- **Dragging must move the panel edge in real-time, exactly matching the mouse position** — no lag, no delay, no debounce
- Use `mousemove` with `requestAnimationFrame` for smooth 60fps dragging
- Disable text selection during drag (`user-select: none` on body)
- Disable pointer events on iframes/webviews during drag

#### Left sidebar:
- **Min width**: 50px (collapsed — shows only icons)
- **Max width**: 320px
- **Snap-to-collapse**: If user drags below 80px, snap to 50px (collapsed state)
- When collapsed: only show icons centered, hide all text labels, section headers, and the user chip name/subtitle
- **Collapse button**: A clearly visible button (at least 28x28px) at the top of the sidebar. Clicking it toggles between collapsed (50px) and last-used expanded width
- When collapsed via snap-drag, user CANNOT drag it back out — they must click the collapse button to expand it again. This prevents accidental micro-drags.
- The collapse button should have a chevron icon that flips direction based on state

#### Right sidebar:
- Same resize behavior as left sidebar, but mirrored (handle on the LEFT edge)
- Same min/max/snap behavior
- Same collapse button behavior (top of right sidebar, chevron flips)
- **Min width**: 50px (collapsed)
- **Max width**: 320px

#### Center content — vertical resize (between hero and instance list):
- A horizontal resize handle between the play hero area and the instance list below
- Cursor: `row-resize`
- **Min hero height**: 300px (so play button area never gets too small)
- **Max hero height**: 85% of available height
- **Min instance list height**: 80px
- Drag behavior: same smoothness requirements as horizontal — real-time, 60fps, no lag

#### State persistence:
- Save ALL panel widths and heights to `localStorage`:
  - `justice-left-sidebar-width`
  - `justice-left-sidebar-collapsed` (boolean)
  - `justice-right-sidebar-width`
  - `justice-right-sidebar-collapsed` (boolean)
  - `justice-hero-height` (ratio or px)
- On app load, restore all saved dimensions
- If no saved state exists, use sensible defaults (left sidebar: 220px, right sidebar: 200px, hero: 60%)

#### Implementation approach:
```javascript
// Pseudocode for resize handler — follow this pattern
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

      // Snap-to-collapse
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
    // Save to localStorage
    localStorage.setItem(options.storageKey, target.style.width);
    localStorage.setItem(options.storageKey + '-collapsed', target.classList.contains('collapsed'));
  });
}
```

---

## TASK 3: RESPONSIVE LAYOUT — LOOKS GOOD AT ANY SIZE

The app must look good whether it's fullscreen (1920x1080) or windowed small (900x600).

### Rules:
- **No horizontal scrollbars ever** — everything flexes
- **Sidebar text truncates** with ellipsis when sidebar gets narrow (before collapsing)
- **Play button and stats row** should scale slightly at very small window sizes (use `clamp()` or media queries)
- **Instance grid** should reflow from multi-column to single-column at small widths
- **Fonts never go below 12px** — if space is too tight, hide elements instead of shrinking text below readability
- When BOTH sidebars are collapsed, the center content should use the full width gracefully
- **Test at these sizes**: 1920x1080, 1440x900, 1280x720, 1024x768, 900x600

### Media query breakpoints:
```css
/* Compact mode — small windows */
@media (max-width: 1100px) {
  /* Reduce padding, tighten gaps */
  /* Instance grid: fewer columns */
  /* Consider auto-collapsing right sidebar */
}

@media (max-width: 900px) {
  /* Stack layout if needed */
  /* Auto-collapse both sidebars */
  /* Play hero takes full height minus small instance peek */
}
```

---

## TASK 4: EVERYTHING BIGGER — GLOBAL SIZE PASS

The user has repeatedly asked for bigger UI elements. Do a comprehensive pass:

### Minimum sizes (enforce these everywhere):
- **Body text / labels**: 13px minimum, prefer 14px
- **Navigation items**: 15px font, 44px tall hit targets (accessibility standard)
- **Section headers**: 12px minimum (uppercase labels like "CONTENT", "PLAYER")
- **Buttons**: minimum 36px height, 14px font
- **Input fields**: 44px height, 14px font
- **Icons in nav**: 20px minimum
- **Collapse/toggle buttons**: 32x32px minimum, clearly visible
- **Titlebar**: 48px height, logo text 18px
- **Window controls** (minimize/maximize/close): 48x36px
- **Modal titles**: 20px
- **Modal close buttons**: 36x36px hit area
- **Toggle switches**: 44x24px
- **Sidebar user chip**: generous padding (12px 16px), avatar 36px
- **Status bar text**: 13px minimum
- **Tab labels** (Friends/Clans/News): 14px
- **Instance cards**: bigger icons (52px), title 15px, subtitle 13px

### Approach:
- Do a search-and-replace pass through all CSS in the `<style>` tag
- Find every `font-size` declaration and ensure it meets the minimums above
- Find every `padding` on interactive elements and ensure adequate hit targets
- Find every `width`/`height` on buttons and ensure 36px+ height

---

## TASK 5: ZERO BLANK SPACE

Go through every page and eliminate dead space:

- **Play page**: The hero should expand to fill available space. Instance list fills the rest. No gaps between them.
- **Other pages** (Mods, Worlds, Shaders, etc.): Content grids should have `align-content: start` but the container should fill the page. Use subtle background patterns or adjust padding so nothing feels empty.
- **Sidebars**: Nav items should have appropriate spacing but not excessive gaps. Section dividers should be tight.
- **Right sidebar**: When showing "Sign in to see friends" or similar empty states, style them attractively — a nice centered message with an icon, not just floating text.
- **Between sections**: Use 1px borders or subtle gradients instead of blank space for visual separation
- **Instance cards**: Should fill their grid cells fully. Consistent heights.

---

## TASK 6: STATE PERSISTENCE — REMEMBER EVERYTHING

Every user preference and UI state must persist across app restarts via `localStorage`:

### What to persist:
- All panel widths/heights and collapsed states (from Task 2)
- Selected instance (already done — verify it works)
- RAM slider value (already done — verify)
- Right sidebar active tab (Friends/Clans/News)
- Any section collapse states in the sidebar (CONTENT, PLAYER, EXTRAS sections)
- Search field contents (clear on restart is fine, but remember if user was on a search page)
- Scroll positions on pages (nice to have)
- Window size/position (this is Electron main process — skip if it's already handled there)

### Implementation:
```javascript
// Create a unified state manager
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

// Initialize on load
UIState.load();
```

Apply saved state immediately on DOM ready — before any transitions play, so the UI doesn't flash/animate into the saved position.

---

## TASK 7: VISUAL POLISH — MAKE IT FEEL PREMIUM

Use the ui-ux-pro-max toolkit results to guide these decisions. The goal is a launcher that feels like it belongs next to Steam, Lunar Client, or Prism Launcher in quality.

### Specific improvements:
- **Sidebar nav items**: Active state should have a filled background with subtle left border accent (not just background color change). Hover states should feel responsive — slight translateX, background fade-in.
- **Cards**: Instance cards, mod cards, etc. should have subtle inner shadows, consistent border-radius (12-14px), and a slight scale-up on hover (1.02x).
- **Transitions**: Everything should use the existing `--transition-smooth` cubic bezier. No instant state changes. But keep transitions SHORT (150-250ms) — the current ones may feel sluggish.
- **Scrollbars**: Thin (4px), rounded, purple-tinted, only visible on hover. Already partially done — verify consistency across all scrollable areas.
- **Focus states**: All interactive elements should have visible focus indicators for keyboard navigation (outline or box-shadow with purple glow).
- **Loading states**: Any async operations should show skeleton loaders or subtle pulse animations, never blank space.
- **Empty states**: Every page should have a nice empty state with icon + message + call-to-action button when there's no content.

---

## TASK 8: FIX THE COLLAPSED SIDEBAR STATE

When the sidebar is collapsed (50px, icons only):
- Icons should be perfectly centered (both horizontally and vertically in their row)
- No text should leak out or be partially visible
- Tooltips should appear on hover showing the nav item name (use CSS `::after` pseudo-element or a lightweight JS tooltip)
- The section headers (CONTENT, PLAYER, EXTRAS) should become thin horizontal divider lines
- The user chip at the bottom should show only the avatar circle, centered
- The collapse button should show a "expand" chevron (pointing right for left sidebar, pointing left for right sidebar)
- Settings and Game Folder buttons at the bottom should also be icon-only, centered

---

## IMPLEMENTATION ORDER

1. **Read ui-ux-pro-max results** — run the search commands at the top of this file first
2. **Task 2** — Rebuild resize system (this affects everything else)
3. **Task 1** — Play hub layout with top/bottom sections
4. **Task 8** — Fix collapsed sidebar states
5. **Task 4** — Global size pass (bigger everything)
6. **Task 5** — Zero blank space pass
7. **Task 3** — Responsive layout (media queries)
8. **Task 6** — State persistence
9. **Task 7** — Visual polish pass
10. **Final verification** — Test at multiple window sizes, test collapse/expand, test persistence across reload

---

## TESTING CHECKLIST

After implementing, verify ALL of these:

- [ ] Play button is visually centered in the hero area (not top-heavy)
- [ ] Content exists ABOVE the play button (top section)
- [ ] Instance list exists BELOW the play button
- [ ] Left sidebar resizes smoothly with mouse drag (60fps, no lag)
- [ ] Left sidebar snaps to collapsed when dragged below 80px
- [ ] Left sidebar cannot be dragged open from collapsed — must click button
- [ ] Right sidebar has identical resize behavior (mirrored)
- [ ] Vertical resize between hero and instance list works
- [ ] All resize handles show subtle visual indicator on hover
- [ ] Collapse buttons are 32x32px+ and clearly visible
- [ ] All nav text is 15px+, all buttons are 36px+ height
- [ ] No blank space on play page, mods page, worlds page
- [ ] localStorage saves and restores: sidebar widths, collapsed states, hero height
- [ ] App looks good at 1920x1080 (fullscreen)
- [ ] App looks good at 1280x720 (windowed)
- [ ] App looks good at 900x600 (small window)
- [ ] Collapsed sidebar shows centered icons with tooltips
- [ ] No horizontal scrollbars at any size
- [ ] Version dropdown appears above play button (high z-index)
- [ ] Play button is completely untouched (same size, gradient, glow, animation)
- [ ] All transitions are smooth (150-250ms) using `--transition-smooth`
- [ ] Right sidebar empty states look polished, not bare

---

## FINAL NOTES

- This is a single-file app. All CSS is in `<style>`, all HTML is in `<body>`, all JS is in `<script>`. Work within this structure.
- The file is ~6400 lines. Be careful with edits — verify line counts after changes.
- The existing CSS custom properties (`:root` variables) should be used everywhere. Don't hardcode colors.
- The user's exact words: "I want it to be formatted for full screen and not full screen", "less blank space please just everywhere", "every button should just be bigger", "I want the resize to just move exactly with my mouse", "it should all be bigger, everything should be bigger, everything."
- When in doubt, make it bigger, make it smoother, fill the space.
