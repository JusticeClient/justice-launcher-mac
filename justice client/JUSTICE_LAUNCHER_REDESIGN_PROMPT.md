# Justice Launcher — Full UI Redesign Prompt for Claude Code

## Context

You are working on **Justice Launcher**, a custom Minecraft client launcher built with **Electron 28 + vanilla HTML/CSS/JS** (no React, no frameworks). The entire UI lives in a single file: `src/index.html` (~5700 lines, containing `<style>`, HTML, and `<script>` all in one file). The main process is `main.js`.

The launcher already has 20+ pages (Play, Mods, Modpacks, Modrinth, Worlds, Resource Packs, Shaders, Screenshots, Host World, Servers, Skin, Console, Friends, Search, News, Clans, Plus, Events, Points, Settings). All navigation is sidebar-based with page switching via class toggling. The design uses a dark purple/magenta glassmorphism theme with Space Grotesk + JetBrains Mono fonts and CSS custom properties for theming.

There is a **UI/UX Pro Max** design intelligence skill available in the project at `ui-ux-pro-max-skill/`. Before making any design decisions, **run searches against it** to get best practices:

```bash
python3 ui-ux-pro-max-skill/src/ui-ux-pro-max/scripts/search.py "<query>" --domain <domain>
```

Domains: `style`, `typography`, `color`, `ux`, `landing`, `product`
Stack: `--stack html-tailwind`

Example queries to run before starting:
- `"glassmorphism dark mode gaming"` --domain style
- `"sidebar navigation collapsible"` --domain ux
- `"animation transitions fade"` --domain ux
- `"gaming launcher desktop app"` --domain product
- `"dark purple neon color palette"` --domain color

---

## Design Philosophy

**ZERO BLANK SPACE.** Every pixel should serve a purpose. The app should feel dense, information-rich, and premium — like a high-end gaming client. Think Steam, Lunar Client, or Overwolf. When someone launches this app, the reaction should be: "Holy shit, this is professionally made."

Everything should be **easy to see at default size** — no squinting. Text should be readable, buttons should be obvious, and the layout should guide your eyes naturally.

---

## Layout Architecture — Three-Panel Design

Redesign the layout from a two-panel (sidebar + content) to a **three-panel layout**:

```
┌──────────────────────────────────────────────────────────────────┐
│  TITLEBAR (Justice Launcher logo + window controls)              │
├────────────┬──────────────────────────────────┬──────────────────┤
│            │                                  │                  │
│  LEFT      │        MAIN CONTENT              │   RIGHT          │
│  SIDEBAR   │                                  │   SIDEBAR        │
│            │   ┌────────────────────────┐     │                  │
│  Content   │   │  Version Selector ▼    │     │   Social         │
│  ─────     │   └────────────────────────┘     │   ──────         │
│  Mods      │                                  │   Friends        │
│  Modpacks  │   ┌────────────────────────┐     │   Search         │
│  Modrinth  │   │                        │     │   Clans          │
│  Worlds    │   │     ▶  PLAY            │     │   News           │
│  Res.Packs │   │                        │     │                  │
│  Shaders   │   └────────────────────────┘     │                  │
│  Screens   │                                  │                  │
│  Host      │   [Mods: 12] [Worlds: 3]        │                  │
│  Servers   │   [Packs: 5] [Shaders: 2]       │                  │
│            │                                  │                  │
│  Player    │                                  │                  │
│  ──────    │                                  │                  │
│  Skin      │                                  │                  │
│  Console   │                                  │                  │
│            │                                  │                  │
│  Extras    │                                  │                  │
│  ──────    │                                  │                  │
│  Plus      │                                  │                  │
│  Events    │                                  │                  │
│  Points    │                                  │                  │
│            │                                  │                  │
│ ─────────  │                                  │                  │
│ ⚙ Settings │                                  │                  │
├────────────┴──────────────────────────────────┴──────────────────┤
│  STATUS BAR (RAM slider, progress, game status)                  │
└──────────────────────────────────────────────────────────────────┘
```

### Left Sidebar (Navigation)

- **Sections with collapsible headers**: "Content", "Player", "Extras" — clicking the header collapses/expands that group with a smooth accordion animation
- **Minimizable**: A toggle button (hamburger or `«` chevron) at the top of the sidebar that collapses it to just icons (icon-only mode, ~50px wide). Animate the transition smoothly (width + text fade out/in)
- **Resizable**: The right edge of the sidebar is a **draggable separator**. User can click and drag to make the sidebar wider or narrower. Minimum width: 50px (icon-only). Maximum width: 300px. Store the preference in localStorage
- **Settings button at the bottom**: Settings should be at the very bottom of the sidebar, visually separated. When clicked, it opens a **slide-up panel/overlay** from the bottom of the screen (not a full page navigation) — like a settings drawer. The panel should cover about 70% of the screen height, have a drag handle at the top to resize, and a close button. Use a smooth slide-up animation
- **Compact spacing**: Reduce padding/gaps between nav items. No wasted vertical space. The sidebar should feel dense but organized
- **Bigger, clearer text**: Nav item font size should be 14px minimum. Icons should be 16px inside 32px containers

### Right Sidebar (Social Panel)

- **Same behavior as left sidebar**: Minimizable (toggle to icon-only), resizable via draggable separator
- **Contains**: Friends list (with online/offline/in-game status dots), Search Players, Clans, News feed
- **Steam-style friends list**: Show online friends at top with green dots, playing friends with amber dots, offline at bottom grayed out. Show what game/server they're on
- **Quick chat**: Clicking a friend opens an inline chat panel within the right sidebar (not a separate page)
- **Minimize chevron** `»` on the left edge of the right sidebar

### Main Content Area — The Play Hub

The default/home view should be **completely redesigned**:

- **Center stage: Big Play Button** — A large, prominent, glowing play button dead center of the content area. This button should be:
  - At least 120px wide, 50px tall (or larger)
  - Gradient background (purple to accent, or a vivid green like #4ade80)
  - Pulsing glow animation when idle (subtle)
  - Scale-up + intensify glow on hover
  - Satisfying press animation on click (scale down briefly, then launch)
  - The text "PLAY" in bold, 18px+ font

- **Version Selector above the Play button**: A clean dropdown/selector positioned directly above the play button showing the currently selected instance. Should display:
  - Instance name (which the user can customize)
  - Version number + loader type (Vanilla/Fabric)
  - A dropdown arrow that opens a compact instance list to switch between instances
  - "New Instance +" button at the bottom of the dropdown

- **Stats row below the Play button**: A horizontal row of compact stat chips showing: Mods count, Worlds count, Packs count, Shaders count — all for the currently selected instance. These should be small, pill-shaped, and informational

- **NO BLANK SPACE around the play button area**. Fill remaining space with:
  - Recent activity or instance thumbnails
  - Quick-action tiles (e.g., "Browse Mods", "Open World", "View Screenshots")
  - A subtle animated background (particle effect, slow-moving gradient mesh, or the existing hero orbs but more prominent)

---

## Animations & Transitions

Implement these throughout the entire app:

### Page Transitions
- When switching between pages via the sidebar, the outgoing page should **fade out + slide slightly left** and the incoming page should **fade in + slide slightly from the right**. Duration: 200-250ms. Use CSS transitions or requestAnimationFrame
- Implementation: Instead of instant `display:none/flex` toggling, add transition classes:
  ```css
  .page { opacity: 0; transform: translateX(12px); transition: opacity 0.2s ease, transform 0.2s ease; pointer-events: none; }
  .page.active { opacity: 1; transform: translateX(0); pointer-events: auto; }
  ```

### Element Animations
- **Cards**: Stagger-fade-in when a page loads. Each card appears 30-50ms after the previous one. Use CSS `animation-delay` or IntersectionObserver
- **Sidebar items**: On first load, items cascade in from left with a slight delay between each
- **Modals**: Already have scale+fade animation (keep and refine). Add a subtle backdrop blur intensification
- **Lists** (mod list, friend list, version list): Items slide in from the left with stagger
- **Hover effects**: All interactive elements should have smooth, satisfying hover transitions (already partially done — enhance with subtle scale, glow, or color shifts)

### Micro-interactions
- **Toggle switches**: Animate the knob with a slight bounce/overshoot
- **Buttons**: Subtle press-down on click (scale 0.97), release back
- **Dropdown opens**: Expand with a slight spring animation (overshoot then settle)
- **Sidebar collapse/expand**: Smooth width animation with content fading appropriately
- **Tab switching**: Underline slides to the active tab position

### Sound Effects (Optional — implement with Web Audio API)
- Add optional UI sounds that can be toggled in settings:
  - Soft click on button press
  - Whoosh on page transition
  - Success chime on game launch
  - Subtle pop on notification/toast
- Keep sounds very short (< 200ms) and subtle. Generate them programmatically with Web Audio API oscillators — no external audio files needed
- Add a "UI Sounds" toggle in Settings (default: off)

---

## Titlebar Improvements

- Make "JUSTICE LAUNCHER" text **bigger** — at least 15px, font-weight 800
- The gradient logo text should be more prominent
- Window controls (minimize, maximize, close) should be **bigger** — at least 36x28px
- Add subtle hover animations to window controls

---

## Text & Readability — Everything Bigger

Global rule: **Nothing should require squinting.**

- Body/nav text: minimum 13-14px
- Page titles: 24-28px
- Section headers: 16-18px
- Card titles: 14-15px
- Metadata/subtitles: 12px minimum (never smaller)
- Button text: 13px minimum
- Badge/pill text: 10px minimum
- All text colors should have at minimum 4.5:1 contrast ratio against their background
- The secondary text color (--t2) should be clearly readable, not faded into the background

---

## Compact, No Blank Space — Specific Rules

1. **Instance cards**: Make the grid tighter. Reduce gap from 12px to 10px. Cards should fill the space
2. **Page padding**: Reduce from 22-26px to 16-20px where possible
3. **Hero section**: Either make it useful (add quick actions, news ticker, or animated background) or reduce its height significantly. No large empty decorative areas
4. **Empty states**: When a page has no content (no mods, no worlds), show a compact helpful message with an action button — not a huge centered empty state with massive padding
5. **Sidebar**: Padding between items should be 2-4px, not 8-10px
6. **Modal padding**: Keep modals tight. 18-20px padding max
7. **Status bar**: Make it compact but readable. 40px height max

---

## Visual Style References

Browse 21st.dev (https://21st.dev) for visual inspiration. Specifically look at:
- Hero section components with animated gradients and particle effects
- Glassmorphism card designs
- Animated buttons with glow effects
- Dark mode dashboard layouts

Implement any compelling visual patterns you find in **vanilla CSS/JS** (not React). The goal is to capture that premium, modern feel while keeping the existing tech stack.

---

## Settings Drawer (Bottom Panel)

Replace the current full-page Settings with a **slide-up drawer**:

- Triggered by clicking the Settings gear icon at the bottom of the left sidebar
- Slides up from the bottom of the screen, covering ~70% of viewport height
- Has a **drag handle** at the top (a small centered bar) that can be dragged to resize
- Semi-transparent blurred backdrop behind it
- Close button (X) in the top-right corner
- Can also be closed by clicking the backdrop or pressing Escape
- Smooth animation: 300ms slide-up with ease-out curve
- Settings content inside should be organized in a **tabbed layout**:
  - Account
  - Game
  - Performance (JVM flags, presets)
  - Appearance
  - Advanced (crash analyzer, export/import)

---

## Data Import Feature (Lower Priority)

Add a section in Settings or a dedicated modal for importing data from other Minecraft clients:
- Drag-and-drop zone for importing: resource packs, mod packs, worlds, settings
- Auto-detect file types and route them to the correct location
- Show import progress with a nice progress bar
- This is a stretch goal — implement the UI shell but the backend can be wired up later

---

## Technical Implementation Notes

- All changes go in `src/index.html` (CSS in the `<style>` tag, HTML in `<body>`, JS in the `<script>` tag)
- The app uses `ipc.send()` and `ipc.invoke()` to communicate with the Electron main process (`main.js`)
- Page navigation currently works via `nav(buttonElement, pageName)` function which toggles `.active` class
- CSS custom properties are defined in `:root` — use them for all colors
- The existing JavaScript handles game launching, mod management, account auth, etc. — **do not break any existing functionality**
- localStorage is used for persisting settings
- Test that all existing pages still work after the layout restructuring

---

## Implementation Order

1. **First**: Run UI/UX Pro Max searches to inform design decisions
2. **Second**: Restructure the layout (three-panel with resizable/collapsible sidebars)
3. **Third**: Redesign the Play hub (centered play button, version selector, no blank space)
4. **Fourth**: Implement the Settings drawer (slide-up panel)
5. **Fifth**: Move social items to the right sidebar
6. **Sixth**: Add page transition animations
7. **Seventh**: Add micro-interactions and element animations (stagger, fade, hover)
8. **Eighth**: Increase all text sizes and improve contrast
9. **Ninth**: Eliminate remaining blank space everywhere
10. **Tenth**: (Optional) Add sound effects system
11. **Last**: Full regression test — click through every page, open every modal, verify nothing is broken

---

## What NOT to Change

- Do not change the Electron main process (`main.js`) unless absolutely necessary for a new IPC channel
- Do not change the core game-launching logic
- Do not remove any existing pages or features
- Do not change the authentication flow
- Do not change the purple/magenta color scheme (enhance it, don't replace it)
- Do not switch to React or any framework — keep it vanilla HTML/CSS/JS
- Do not add external CSS frameworks (no Tailwind, no Bootstrap) — keep custom CSS

---

## Summary

Transform the Justice Launcher from a good-looking app into a **jaw-dropping, premium gaming client** that feels alive. Big centered play button, three-panel layout with resizable/collapsible sidebars, smooth animations on everything, zero wasted space, and text that's easy to read at first glance. Make it feel like it cost $50,000 to build.
