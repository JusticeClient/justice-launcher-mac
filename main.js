const { app, BrowserWindow, ipcMain, shell, net, globalShortcut, screen } = require('electron');
const path = require('path');
const fs = require('fs');
const os = require('os');
const https = require('https');
const http = require('http');
const { spawn, execSync } = require('child_process');
const AdmZip = require('adm-zip');
// Detect REAL hardware architecture (not Electron binary arch, which may differ under Rosetta)
function getHardwareArch() {
  if (process.platform === 'darwin') {
    try {
      const real = require('child_process').execSync('uname -m', { encoding: 'utf8' }).trim();
      if (real === 'arm64') return 'arm64';
    } catch (_) {}
  }
  return process.arch;
}
const REAL_ARCH = getHardwareArch();
app.setPath('userData', path.join(os.homedir(), '.justice-launcher', 'electron-data'));
app.commandLine.appendSwitch('disable-gpu-shader-disk-cache');
app.commandLine.appendSwitch('disk-cache-dir', path.join(os.homedir(), '.justice-launcher', 'electron-cache'));
app.commandLine.appendSwitch('disable-gpu-program-cache');
app.commandLine.appendSwitch('disable-gpu-cache');
app.commandLine.appendSwitch('no-sandbox');
app.commandLine.appendSwitch('disable-http-cache');
const httpAgent = new https.Agent({ keepAlive: true, maxSockets: 128, maxFreeSockets: 48 });
const GAME_DIR = path.join(os.homedir(), '.justice-launcher');
const VERSIONS_DIR = path.join(GAME_DIR, 'versions');
const ASSETS_DIR = path.join(GAME_DIR, 'assets');
const LIBRARIES_DIR = path.join(GAME_DIR, 'libraries');
const INSTANCES_DIR = path.join(GAME_DIR, 'instances');
const JAVA_DIR = path.join(GAME_DIR, 'java');
const AUTH_FILE = path.join(GAME_DIR, 'auth.json');
const ACCOUNTS_FILE = path.join(GAME_DIR, 'accounts.json');
const BUNDLED_THEME = app.isPackaged
  ? path.join(process.resourcesPath, 'nova-theme.zip')
  : path.join(__dirname, 'nova-theme.zip');
const THEME_PACK = path.join(GAME_DIR, 'nova-theme.zip');
function instanceDir(versionId) {
  const d = path.join(INSTANCES_DIR, versionId);
  if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true });
  const m = path.join(d, 'mods');
  if (!fs.existsSync(m)) fs.mkdirSync(m, { recursive: true });
  return d;
}
function modsDir(versionId) {
  return path.join(instanceDir(versionId), 'mods');
}
let mainWindow;
function createWindow() {
  const iconFile = process.platform === 'win32' ? 'icon.ico' : process.platform === 'darwin' ? 'icon.icns' : 'icon.png';
  const iconPath = path.join(__dirname, 'assets', iconFile);
  const isMac = process.platform === 'darwin';
  mainWindow = new BrowserWindow({
    width: 1100, height: 680,
    minWidth: 900, minHeight: 600,
    frame: false,
    titleBarStyle: isMac ? 'hiddenInset' : undefined,
    trafficLightPosition: isMac ? { x: 12, y: 14 } : undefined,
    backgroundColor: '#08060e',
    webPreferences: { nodeIntegration: true, contextIsolation: false, devTools: false },
    title: 'Justice Launcher',
    icon: fs.existsSync(iconPath) ? iconPath : undefined,
  });
  mainWindow.loadFile(path.join(__dirname, 'src', 'index.html'));
  if (process.argv.includes('--dev')) {
    mainWindow.webContents.openDevTools();
  } else {
    mainWindow.webContents.on('devtools-opened', () => {
      mainWindow.webContents.closeDevTools();
    });
    mainWindow.webContents.on('before-input-event', (event, input) => {
      if (input.key === 'F12') { event.preventDefault(); return; }
      if (input.key === 'I' && input.control && input.shift) { event.preventDefault(); return; }
      if (input.key === 'I' && input.meta && input.alt) { event.preventDefault(); return; }
      if (input.key === 'J' && input.control && input.shift) { event.preventDefault(); return; }
      if (input.key === 'C' && input.control && input.shift) { event.preventDefault(); return; }
      if (input.key === 'U' && input.control) { event.preventDefault(); return; }
    });
  }
}
function ensureDirs() {
  [GAME_DIR, VERSIONS_DIR, ASSETS_DIR, LIBRARIES_DIR, INSTANCES_DIR,
    path.join(ASSETS_DIR, 'indexes'), path.join(ASSETS_DIR, 'objects'),
    path.join(os.homedir(), '.justice-launcher', 'electron-data'),
    path.join(os.homedir(), '.justice-launcher', 'electron-cache'),
  ].forEach(d => { try { if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true }); } catch { } });
}
async function findJava() {
  const exe = process.platform === 'win32' ? 'java.exe' : 'java';
  function testJava(p) {
    try { if (fs.existsSync(p)) return p; } catch (_) { }
    return null;
  }
  if (process.env.JAVA_HOME) {
    const j = testJava(path.join(process.env.JAVA_HOME, 'bin', exe));
    if (j) return j;
  }
  if (process.platform === 'win32') {
    const progDirs = [
      process.env['ProgramFiles'],
      process.env['ProgramFiles(x86)'],
      process.env['ProgramW6432'],
      'C:\\Program Files',
      'C:\\Program Files (x86)',
    ].filter((v, i, a) => v && a.indexOf(v) === i);
    const mcRuntimeDirs = [
      path.join(process.env['ProgramFiles(x86)'] || 'C:\\Program Files (x86)', 'Minecraft Launcher', 'runtime'),
      path.join(process.env['ProgramFiles'] || 'C:\\Program Files', 'Minecraft Launcher', 'runtime'),
      path.join(os.homedir(), 'AppData', 'Roaming', '.minecraft', 'runtime'),
    ];
    for (const rDir of mcRuntimeDirs) {
      if (!fs.existsSync(rDir)) continue;
      for (const rName of fs.readdirSync(rDir)) {
        const arch = 'windows-x64';
        const archDir = path.join(rDir, rName, arch);
        if (!fs.existsSync(archDir)) continue;
        for (const jName of fs.readdirSync(archDir)) {
          const j = testJava(path.join(archDir, jName, 'bin', exe));
          if (j) return j;
        }
      }
    }
    const vendors = [
      'Eclipse Adoptium', 'Microsoft', 'Java', 'Zulu', 'BellSoft',
      'Amazon Corretto', 'GraalVM', 'SapMachine', 'OpenJDK',
    ];
    for (const root of progDirs) {
      for (const vendor of vendors) {
        const vd = path.join(root, vendor);
        if (!fs.existsSync(vd)) continue;
        let entries;
        try { entries = fs.readdirSync(vd).sort().reverse(); } catch (_) { continue; }
        for (const entry of entries) {
          const j = testJava(path.join(vd, entry, 'bin', exe));
          if (j) return j;
        }
      }
    }
    try {
      const regOut = execSync(
        'reg query "HKLM\\SOFTWARE\\JavaSoft\\JDK" /s /v JavaHome 2>nul',
        { encoding: 'utf8', timeout: 3000 }
      );
      const match = regOut.match(/JavaHome\s+REG_SZ\s+(.+)/i);
      if (match) {
        const j = testJava(path.join(match[1].trim(), 'bin', exe));
        if (j) return j;
      }
    } catch (_) { }
    try {
      const out = execSync('where java', { encoding: 'utf8', timeout: 3000 });
      const first = out.trim().split('\n')[0].trim();
      if (first && fs.existsSync(first)) return first;
    } catch (_) { }
  } else if (process.platform === 'darwin') {
    try {
      const out = execSync('/usr/libexec/java_home 2>/dev/null', { encoding: 'utf8', timeout: 3000 });
      const j = testJava(path.join(out.trim(), 'bin', 'java'));
      if (j) return j;
    } catch (_) { }
    for (const p of ['/usr/bin/java', '/Library/Java/JavaVirtualMachines']) {
      if (p.includes('JavaVirtualMachines') && fs.existsSync(p)) {
        const entries = fs.readdirSync(p).sort().reverse();
        for (const e of entries) {
          const j = testJava(path.join(p, e, 'Contents', 'Home', 'bin', 'java'));
          if (j) return j;
        }
      } else {
        const j = testJava(p);
        if (j) return j;
      }
    }
  } else {
    for (const p of [
      '/usr/bin/java', '/usr/local/bin/java',
      '/usr/lib/jvm', '/usr/lib/jvm/default-java/bin/java',
      '/usr/lib/jvm/java-21-openjdk-amd64/bin/java',
      '/usr/lib/jvm/java-17-openjdk-amd64/bin/java',
    ]) {
      if (p === '/usr/lib/jvm' && fs.existsSync(p)) {
        const entries = fs.readdirSync(p).sort().reverse();
        for (const e of entries) {
          const j = testJava(path.join(p, e, 'bin', 'java'));
          if (j) return j;
        }
      } else {
        const j = testJava(p);
        if (j) return j;
      }
    }
    try {
      const out = execSync('which java', { encoding: 'utf8', timeout: 3000 });
      const first = out.trim();
      if (first && fs.existsSync(first)) return first;
    } catch (_) { }
  }
  const bundled = getBundledJava();
  if (bundled) return bundled;
  return null;
}
function getBundledJava() {
  const exe = process.platform === 'win32' ? 'java.exe' : 'java';
  if (!fs.existsSync(JAVA_DIR)) return null;
  for (const entry of fs.readdirSync(JAVA_DIR)) {
    const p = path.join(JAVA_DIR, entry, 'bin', exe);
    if (fs.existsSync(p)) return p;
  }
  return null;
}
function getAdoptiumURL() {
  const p = process.platform;
  const a = process.arch;
  const os = p === 'win32' ? 'windows' : p === 'darwin' ? 'mac' : 'linux';
  const arch = (a === 'arm64' || a === 'aarch64') ? 'aarch64' : 'x64';
  const ext = p === 'win32' ? 'zip' : 'tar.gz';
  return `https://api.adoptium.net/v3/binary/latest/21/ga/${os}/${arch}/jdk/hotspot/normal/eclipse?project=jdk`;
}
async function installJava(onProgress) {
  if (!fs.existsSync(JAVA_DIR)) fs.mkdirSync(JAVA_DIR, { recursive: true });
  const isWin = process.platform === 'win32';
  const tmpFile = path.join(JAVA_DIR, isWin ? 'jdk.zip' : 'jdk.tar.gz');
  onProgress('Downloading Java 21...', 0);
  await downloadFile(getAdoptiumURL(), tmpFile, p => onProgress(`Downloading Java 21... ${Math.round(p * 100)}%`, p * 0.85));
  onProgress('Extracting Java...', 0.85);
  if (isWin) {
    const zip = new AdmZip(tmpFile);
    zip.extractAllTo(JAVA_DIR, true);
  } else {
    const { execFileSync } = require('child_process');
    execFileSync('tar', ['-xzf', tmpFile, '-C', JAVA_DIR]);
    const exe = getBundledJavaAfterExtract();
    if (exe) { try { fs.chmodSync(exe, 0o755); } catch (_) { } }
  }
  try { fs.unlinkSync(tmpFile); } catch (_) { }
  const bundled = getBundledJava();
  if (!bundled) throw new Error('Java extraction failed — could not locate java binary after extraction');
  onProgress('Java 21 ready!', 1);
  return bundled;
}
function getBundledJavaAfterExtract() {
  const exe = process.platform === 'win32' ? 'java.exe' : 'java';
  if (!fs.existsSync(JAVA_DIR)) return null;
  for (const entry of fs.readdirSync(JAVA_DIR)) {
    let p = path.join(JAVA_DIR, entry, 'bin', exe);
    if (fs.existsSync(p)) return p;
    p = path.join(JAVA_DIR, entry, 'Contents', 'Home', 'bin', exe);
    if (fs.existsSync(p)) return p;
  }
  return null;
}
ipcMain.handle('check-java', async () => {
  const found = await findJava();
  return { found: !!found, path: found || null };
});
ipcMain.handle('install-java', async (event) => {
  const send = (step, p) => event.sender.send('java-install-progress', { step, progress: p });
  try {
    const javaPath = await installJava(send);
    return { success: true, path: javaPath };
  } catch (e) {
    return { error: e.message };
  }
});
function fetchJSON(url, options = {}) {
  return new Promise((resolve, reject) => {
    const { method = 'GET', headers = {}, body } = options;
    const isHttps = url.startsWith('https');
    const lib = isHttps ? https : http;
    const agentOpt = isHttps ? { agent: httpAgent } : {};
    const get = (u) => {
      const req = lib.request(u, {
        method, ...agentOpt,
        headers: { 'Content-Type': 'application/json', 'User-Agent': 'JusticeLauncher/1.0', ...headers },
      }, (res) => {
        if (res.statusCode === 301 || res.statusCode === 302 || res.statusCode === 303 || res.statusCode === 307 || res.statusCode === 308) return get(res.headers.location);
        let d = ''; res.on('data', c => d += c);
        res.on('end', () => { try { resolve(JSON.parse(d)); } catch (e) { resolve(d); } });
      });
      req.on('error', reject);
      if (body) req.write(typeof body === 'string' ? body : JSON.stringify(body));
      req.end();
    };
    get(url);
  });
}
function downloadFile(url, dest, onProgress) {
  return new Promise((resolve, reject) => {
    if (!url) return reject(new Error('No URL'));
    const dir = path.dirname(dest);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    let settled = false;
    const settle = (fn, val) => { if (!settled) { settled = true; fn(val); } };
    const get = (u, redirects) => {
      if (redirects > 5) return settle(reject, new Error('Too many redirects'));
      const mod = u.startsWith('https') ? https : http;
      const req = mod.get(u, { agent: u.startsWith('https') ? httpAgent : undefined, headers: { 'User-Agent': 'JusticeLauncher/1.0' } }, (res) => {
        if ([301, 302, 303, 307, 308].includes(res.statusCode)) { res.resume(); return get(res.headers.location, (redirects || 0) + 1); }
        if (res.statusCode !== 200) { res.resume(); return settle(reject, new Error(`HTTP ${res.statusCode}`)); }
        const total = parseInt(res.headers['content-length'] || '0');
        let dl = 0;
        const file = fs.createWriteStream(dest);
        res.on('data', c => { dl += c.length; if (total && onProgress) onProgress(dl / total); });
        res.pipe(file);
        file.on('finish', () => { file.close(); settle(resolve); });
        file.on('error', err => { fs.unlink(dest, () => { }); settle(reject, err); });
        res.on('error', err => { file.destroy(); fs.unlink(dest, () => { }); settle(reject, err); });
      });
      req.on('error', err => settle(reject, err));
      req.setTimeout(30000, () => { req.destroy(); settle(reject, new Error('Download timeout')); });
    };
    get(url, 0);
  });
}
async function downloadBatch(tasks, concurrency, onProgress) {
  let done = 0;
  const total = tasks.length;
  // Pre-filter already downloaded files using a Set for O(1) lookups
  const pendingSet = new Set();
  const pending = [];
  for (const t of tasks) {
    if (!fs.existsSync(t.dest)) {
      // Ensure parent directories exist up front (batch mkdir)
      const dir = path.dirname(t.dest);
      if (!pendingSet.has(dir)) { pendingSet.add(dir); if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true }); }
      pending.push(t);
    }
  }
  const skipped = total - pending.length;
  if (skipped > 0) { done = skipped; if (onProgress) onProgress(done, total); }
  async function worker(queue) {
    while (queue.length) {
      const task = queue.shift();
      const maxRetries = 2;
      for (let attempt = 0; attempt <= maxRetries; attempt++) {
        try { await downloadFile(task.url, task.dest, () => { }); break; } catch (_) {
          if (attempt === maxRetries) { /* skip failed file */ }
        }
      }
      done++;
      if (onProgress) onProgress(done, total);
    }
  }
  const queue = [...pending];
  if (!queue.length) return;
  await Promise.all(Array.from({ length: Math.min(concurrency, queue.length) }, () => worker(queue)));
}
function getNativeClassifier() {
  const p = process.platform, a = REAL_ARCH;
  if (p === 'win32') return a === 'x64' ? 'natives-windows' : 'natives-windows-x86';
  if (p === 'darwin') return a === 'arm64' ? 'natives-macos-arm64' : 'natives-macos';
  return 'natives-linux';
}
function evalLibRules(rules) {
  if (!rules || rules.length === 0) return true;
  const osName = process.platform === 'win32' ? 'windows'
    : process.platform === 'darwin' ? 'osx'
      : 'linux';
  let allowed = false;
  for (const rule of rules) {
    const matches = !rule.os || rule.os.name === osName;
    if (matches) allowed = rule.action === 'allow';
  }
  return allowed;
}
function extractNatives(versionJson, nativesDir) {
  if (!fs.existsSync(nativesDir)) fs.mkdirSync(nativesDir, { recursive: true });
  const platform = process.platform;
  const osKey = platform === 'win32' ? 'windows' : platform === 'darwin' ? 'osx' : 'linux';
  const osKeyNew = platform === 'win32' ? 'windows' : platform === 'darwin' ? 'macos' : 'linux';
  const arch = REAL_ARCH; // 'x64' or 'arm64' (real hardware, not Electron binary)
  // Always wipe natives and re-extract fresh to avoid stale/wrong-arch files
  const archMarker = path.join(nativesDir, '.arch');
  const currentArch = `${platform}-${arch}`;
  let needsClean = false;
  if (fs.existsSync(archMarker)) {
    try {
      const prevArch = fs.readFileSync(archMarker, 'utf8').trim();
      if (prevArch !== currentArch) needsClean = true;
    } catch (_) { needsClean = true; }
  } else if (fs.readdirSync(nativesDir).filter(f => !f.startsWith('.')).length > 0) {
    needsClean = true;
  }
  if (needsClean) {
    fs.readdirSync(nativesDir).forEach(f => {
      try { fs.unlinkSync(path.join(nativesDir, f)); } catch (_) { }
    });
  }
  const nativeExts = new Set(['.dll', '.so', '.dylib', '.jnilib']);
  // Collect ALL native JARs for this OS (both x64 and arm64)
  const nativeJarsGeneric = [];  // x64 / generic
  const nativeJarsArch = [];     // arm64-specific (processed LAST to overwrite)
  let extracted = 0;
  for (const lib of (versionJson.libraries || [])) {
    if (!evalLibRules(lib.rules)) continue;
    if (lib.downloads?.artifact?.path) {
      const p = lib.downloads.artifact.path;
      if (p.includes(`natives-${osKeyNew}`) || p.includes(`natives-${osKey}`)) {
        const jarPath = path.join(LIBRARIES_DIR, p);
        if (fs.existsSync(jarPath)) {
          // Sort: arch-specific JARs go last so they overwrite generic ones
          if (p.includes('arm64') || p.includes('aarch64')) {
            nativeJarsArch.push(jarPath);
          } else {
            nativeJarsGeneric.push(jarPath);
          }
        }
      }
    }
    if (lib.natives && lib.downloads?.classifiers) {
      const archBits = arch === 'x64' ? '64' : '32';
      const fromNatives = lib.natives[osKey]?.replace('${arch}', archBits);
      const generic = getNativeClassifier();
      const keys = [...new Set([fromNatives, generic].filter(Boolean))];
      for (const key of keys) {
        const a = lib.downloads.classifiers[key];
        if (a?.path) {
          const jarPath = path.join(LIBRARIES_DIR, a.path);
          if (fs.existsSync(jarPath)) {
            if (key.includes('arm64') || key.includes('aarch64')) {
              nativeJarsArch.push(jarPath);
            } else {
              nativeJarsGeneric.push(jarPath);
            }
          }
        }
      }
    }
  }
  // On arm64: extract generic first, then arm64 overwrites. On x64: only extract generic.
  const jarsToExtract = arch === 'arm64'
    ? [...nativeJarsGeneric, ...nativeJarsArch]
    : nativeJarsGeneric;
  const skipPrefixes = ['META-INF/', 'module-info'];
  for (const jarPath of jarsToExtract) {
    try {
      const zip = new AdmZip(jarPath);
      for (const entry of zip.getEntries()) {
        const name = entry.entryName.replace(/\\/g, '/');
        const ext = path.extname(name).toLowerCase();
        if (entry.isDirectory) continue;
        if (skipPrefixes.some(p => name.startsWith(p))) continue;
        if (!nativeExts.has(ext)) continue;
        const outPath = path.join(nativesDir, path.basename(name));
        try {
          fs.writeFileSync(outPath, entry.getData());
          extracted++;
        } catch (_) { }
      }
    } catch (_) { }
  }
  // Write arch marker
  try { fs.writeFileSync(archMarker, currentArch); } catch (_) { }
  return { jarCount: jarsToExtract.length, extracted };
}
function mavenToPath(name) {
  const [group, artifact, version] = name.split(':');
  const g = group.replace(/\./g, '/');
  return `${g}/${artifact}/${version}/${artifact}-${version}.jar`;
}
function buildClasspath(versionJson) {
  const cp = [];
  for (const lib of (versionJson.libraries || [])) {
    if (!evalLibRules(lib.rules)) continue;
    if (lib.natives && !lib.downloads?.artifact) continue;
    let added = false;
    // Try official downloads path first
    if (lib.downloads?.artifact?.path) {
      const p = path.join(LIBRARIES_DIR, lib.downloads.artifact.path);
      if (fs.existsSync(p)) { cp.push(p); added = true; }
    }
    // Fallback: resolve via maven coordinates (covers Fabric libs and any lib with name but missing/incomplete downloads)
    if (!added && lib.name) {
      const p = path.join(LIBRARIES_DIR, mavenToPath(lib.name));
      if (fs.existsSync(p)) { cp.push(p); added = true; }
    }
  }
  return cp;
}
const MS_CLIENT_ID = '00000000402b5328';
const MS_AUTH_URL = 'https://login.live.com/oauth20_authorize.srf';
const MS_TOKEN_URL = 'https://login.live.com/oauth20_token.srf';
const XBL_AUTH_URL = 'https://user.auth.xboxlive.com/user/authenticate';
const XSTS_AUTH_URL = 'https://xsts.auth.xboxlive.com/xsts/authorize';
const MC_AUTH_URL = 'https://api.minecraftservices.com/authentication/login_with_xbox';
const MC_PROFILE_URL = 'https://api.minecraftservices.com/minecraft/profile';
const REDIRECT_URI = 'https://login.live.com/oauth20_desktop.srf';
function loadAccounts() {
  try {
    const raw = JSON.parse(fs.readFileSync(ACCOUNTS_FILE, 'utf8'));
    if (raw && Array.isArray(raw.accounts)) return raw;
  } catch (_) { }
  try {
    const legacy = JSON.parse(fs.readFileSync(AUTH_FILE, 'utf8'));
    if (legacy && legacy.uuid) {
      const store = { accounts: [legacy], activeUuid: legacy.uuid };
      fs.writeFileSync(ACCOUNTS_FILE, JSON.stringify(store, null, 2));
      return store;
    }
  } catch (_) { }
  return { accounts: [], activeUuid: null };
}
function saveAccounts(store) {
  fs.writeFileSync(ACCOUNTS_FILE, JSON.stringify(store, null, 2));
}
function getActiveAccount() {
  const store = loadAccounts();
  if (!store.activeUuid) return store.accounts[0] || null;
  return store.accounts.find(a => a.uuid === store.activeUuid) || store.accounts[0] || null;
}
function addOrUpdateAccount(authData) {
  const store = loadAccounts();
  const idx = store.accounts.findIndex(a => a.uuid === authData.uuid);
  if (idx >= 0) store.accounts[idx] = authData;
  else store.accounts.push(authData);
  store.activeUuid = authData.uuid;
  saveAccounts(store);
}
function removeAccount(uuid) {
  const store = loadAccounts();
  store.accounts = store.accounts.filter(a => a.uuid !== uuid);
  if (store.activeUuid === uuid) store.activeUuid = store.accounts[0]?.uuid || null;
  saveAccounts(store);
}
function switchAccount(uuid) {
  const store = loadAccounts();
  if (store.accounts.find(a => a.uuid === uuid)) {
    store.activeUuid = uuid;
    saveAccounts(store);
    return true;
  }
  return false;
}
function loadAuth() { return getActiveAccount(); }
function saveAuth(data) { addOrUpdateAccount(data); }
function clearAuth() { const a = getActiveAccount(); if (a) removeAccount(a.uuid); }
async function getMSTokens(code) {
  const params = new URLSearchParams({
    client_id: MS_CLIENT_ID,
    code,
    grant_type: 'authorization_code',
    redirect_uri: REDIRECT_URI,
  });
  const res = await fetchJSON(MS_TOKEN_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString(),
  });
  if (!res.access_token) throw new Error('MS token exchange failed: ' + JSON.stringify(res));
  return res;
}
async function refreshMSTokens(refreshToken) {
  const params = new URLSearchParams({
    client_id: MS_CLIENT_ID,
    refresh_token: refreshToken,
    grant_type: 'refresh_token',
    redirect_uri: REDIRECT_URI,
  });
  const res = await fetchJSON(MS_TOKEN_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString(),
  });
  if (!res.access_token) throw new Error('MS refresh failed');
  return res;
}
async function getXBLToken(msAccessToken) {
  const res = await fetchJSON(XBL_AUTH_URL, {
    method: 'POST',
    body: {
      Properties: {
        AuthMethod: 'RPS',
        SiteName: 'user.auth.xboxlive.com',
        RpsTicket: `d=${msAccessToken}`,
      },
      RelyingParty: 'http://auth.xboxlive.com',
      TokenType: 'JWT',
    },
  });
  if (!res.Token) throw new Error('XBL auth failed');
  const userHash = res.DisplayClaims?.xui?.[0]?.uhs;
  return { token: res.Token, userHash };
}
async function getXSTSToken(xblToken) {
  const res = await fetchJSON(XSTS_AUTH_URL, {
    method: 'POST',
    body: {
      Properties: {
        SandboxId: 'RETAIL',
        UserTokens: [xblToken],
      },
      RelyingParty: 'rp://api.minecraftservices.com/',
      TokenType: 'JWT',
    },
  });
  if (!res.Token) {
    if (res.XErr === 2148916233) throw new Error('This Microsoft account has no Xbox account. Please create one at xbox.com.');
    if (res.XErr === 2148916238) throw new Error('This account is a child account. Please add it to a family at xbox.com.');
    throw new Error('XSTS auth failed: ' + JSON.stringify(res));
  }
  const userHash = res.DisplayClaims?.xui?.[0]?.uhs;
  return { token: res.Token, userHash };
}
async function getMCToken(xstsToken, userHash) {
  const res = await fetchJSON(MC_AUTH_URL, {
    method: 'POST',
    body: { identityToken: `XBL3.0 x=${userHash};${xstsToken}` },
  });
  if (!res.access_token) throw new Error('Minecraft auth failed');
  return res.access_token;
}
async function getMCProfile(mcAccessToken) {
  const res = await fetchJSON(MC_PROFILE_URL, {
    headers: { Authorization: `Bearer ${mcAccessToken}` },
  });
  if (!res.id) throw new Error('Could not fetch Minecraft profile. Make sure you own Minecraft Java Edition.');
  return { uuid: res.id, username: res.name };
}
async function doFullAuth(code) {
  const msTokens = await getMSTokens(code);
  const xbl = await getXBLToken(msTokens.access_token);
  const xsts = await getXSTSToken(xbl.token);
  const mcToken = await getMCToken(xsts.token, xsts.userHash);
  const profile = await getMCProfile(mcToken);
  const authData = {
    mcAccessToken: mcToken,
    uuid: profile.uuid,
    username: profile.username,
    msRefreshToken: msTokens.refresh_token,
    expiresAt: Date.now() + (msTokens.expires_in || 86400) * 1000,
  };
  addOrUpdateAccount(authData);
  return authData;
}
async function refreshAuth(authData) {
  const msTokens = await refreshMSTokens(authData.msRefreshToken);
  const xbl = await getXBLToken(msTokens.access_token);
  const xsts = await getXSTSToken(xbl.token);
  const mcToken = await getMCToken(xsts.token, xsts.userHash);
  const profile = await getMCProfile(mcToken);
  const newData = {
    mcAccessToken: mcToken,
    uuid: profile.uuid,
    username: profile.username,
    msRefreshToken: msTokens.refresh_token || authData.msRefreshToken,
    expiresAt: Date.now() + (msTokens.expires_in || 86400) * 1000,
  };
  saveAuth(newData);
  return newData;
}
async function getValidAuth() {
  const auth = getActiveAccount();
  if (!auth) return null;
  if (Date.now() > (auth.expiresAt || 0) - 600_000) {
    if (auth.authType === 'token') return auth;
    try { return await refreshAuth(auth); } catch (_) { return null; }
  }
  return auth;
}
async function getValidAuthForUUID(uuid) {
  const store = loadAccounts();
  const auth = store.accounts.find(a => a.uuid === uuid);
  if (!auth) return null;
  if (Date.now() > (auth.expiresAt || 0) - 600_000) {
    if (auth.authType === 'token') return auth;
    try { return await refreshAuth(auth); } catch (_) { return null; }
  }
  return auth;
}
let authWindow = null;
ipcMain.handle('auth-status', async () => {
  const store = loadAccounts();
  const storedAccounts = store.accounts.map(a => ({ uuid: a.uuid, username: a.username, authType: a.authType || 'msa' }));
  const auth = await getValidAuth();
  if (!auth) {
    const fallback = getActiveAccount();
    if (fallback) {
      return {
        loggedIn: false,
        needsRefresh: true,
        username: fallback.username,
        uuid: fallback.uuid,
        activeUuid: store.activeUuid,
        accounts: storedAccounts,
      };
    }
    return { loggedIn: false, accounts: storedAccounts };
  }
  return {
    loggedIn: true,
    username: auth.username,
    uuid: auth.uuid,
    mcAccessToken: auth.mcAccessToken || null,
    activeUuid: store.activeUuid,
    accounts: storedAccounts,
  };
});
ipcMain.handle('get-accounts', () => {
  const store = loadAccounts();
  return { accounts: store.accounts.map(a => ({ uuid: a.uuid, username: a.username, authType: a.authType || 'msa' })), activeUuid: store.activeUuid };
});
ipcMain.handle('switch-account', async (event, uuid) => {
  const ok = switchAccount(uuid);
  if (!ok) return { error: 'Account not found' };
  const auth = await getValidAuthForUUID(uuid);
  if (!auth) return { error: 'Could not refresh token for this account' };
  if (!event.sender.isDestroyed()) event.sender.send('auth-changed', {
    loggedIn: true, username: auth.username, uuid: auth.uuid,
    mcAccessToken: auth.mcAccessToken || null,
    accounts: loadAccounts().accounts.map(a => ({ uuid: a.uuid, username: a.username })),
    activeUuid: uuid,
  });
  return { success: true, username: auth.username, uuid: auth.uuid };
});
ipcMain.handle('remove-account', async (event, uuid) => {
  removeAccount(uuid);
  const store = loadAccounts();
  const nextAuth = await getValidAuth();
  const payload = nextAuth
    ? { loggedIn: true, username: nextAuth.username, uuid: nextAuth.uuid, activeUuid: store.activeUuid, accounts: store.accounts.map(a => ({ uuid: a.uuid, username: a.username })) }
    : { loggedIn: false, accounts: [] };
  if (!event.sender.isDestroyed()) event.sender.send('auth-changed', payload);
  return { success: true };
});
ipcMain.handle('auth-login', async (event) => {
  return new Promise((resolve) => {
    if (authWindow) { authWindow.focus(); return resolve({ error: 'Auth already in progress' }); }
    const authUrl = `${MS_AUTH_URL}?` + new URLSearchParams({
      client_id: MS_CLIENT_ID,
      response_type: 'code',
      scope: 'XboxLive.signin XboxLive.offline_access',
      redirect_uri: REDIRECT_URI,
      prompt: 'select_account',
    }).toString();
    authWindow = new BrowserWindow({
      width: 520, height: 680,
      title: 'Sign in with Microsoft',
      webPreferences: { nodeIntegration: false, contextIsolation: true },
      autoHideMenuBar: true,
    });
    authWindow.loadURL(authUrl);
    let handled = false;
    async function handleRedirect(url) {
      if (handled) return;
      if (!url || !url.startsWith(REDIRECT_URI)) return;
      handled = true;
      if (authWindow && !authWindow.isDestroyed()) {
        authWindow.removeAllListeners();
        authWindow.webContents.removeAllListeners();
        authWindow.destroy();
        authWindow = null;
      }
      const parsed = new URL(url);
      const code = parsed.searchParams.get('code');
      const error = parsed.searchParams.get('error');
      if (!code) return resolve({ error: error || 'No auth code returned' });
      try {
        const authData = await doFullAuth(code);
        if (!event.sender.isDestroyed()) {
          event.sender.send('auth-changed', { loggedIn: true, username: authData.username, uuid: authData.uuid });
        }
        resolve({ success: true, username: authData.username, uuid: authData.uuid });
      } catch (err) {
        resolve({ error: err.message });
      }
    }
    authWindow.webContents.on('will-redirect', (_, url) => handleRedirect(url));
    authWindow.webContents.on('will-navigate', (_, url) => handleRedirect(url));
    authWindow.webContents.on('did-navigate', (_, url) => handleRedirect(url));
    authWindow.webContents.on('did-redirect-navigation', (_, url) => handleRedirect(url));
    authWindow.on('closed', () => {
      authWindow = null;
      if (!handled) resolve({ error: 'Cancelled' });
    });
  });
});
ipcMain.handle('auth-logout', async (event, uuid) => {
  if (uuid) removeAccount(uuid);
  else clearAuth();
  const store = loadAccounts();
  const next = await getValidAuth();
  const payload = next
    ? { loggedIn: true, username: next.username, uuid: next.uuid, activeUuid: store.activeUuid, accounts: store.accounts.map(a => ({ uuid: a.uuid, username: a.username })) }
    : { loggedIn: false, accounts: [] };
  if (!event.sender.isDestroyed()) event.sender.send('auth-changed', payload);
  return { success: true };
});
ipcMain.handle('get-versions', async () => {
  try {
    const m = await fetchJSON('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json');
    return m.versions.map(v => ({ id: v.id, type: v.type, url: v.url, releaseTime: v.releaseTime }));
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('get-fabric-versions', async (_, mcVersion) => {
  try {
    const l = await fetchJSON(`https://meta.fabricmc.net/v2/versions/loader/${mcVersion}`);
    return l.slice(0, 10).map(x => ({ loader: x.loader.version, stable: x.loader.stable }));
  } catch (e) { return []; }
});
async function installVanilla(mcVersion, onStep, customNameArg, skipSave = false) {
  const manifest = await fetchJSON('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json');
  const entry = manifest.versions.find(v => v.id === mcVersion);
  if (!entry) throw new Error(`${mcVersion} not in manifest`);
  onStep(`Fetching ${mcVersion} metadata...`, 0.02);
  const vJson = await fetchJSON(entry.url);
  const vDir = path.join(VERSIONS_DIR, mcVersion);
  if (!fs.existsSync(vDir)) fs.mkdirSync(vDir, { recursive: true });
  fs.writeFileSync(path.join(vDir, `${mcVersion}.json`), JSON.stringify(vJson, null, 2));
  const jar = path.join(vDir, `${mcVersion}.jar`);
  if (!fs.existsSync(jar))
    await downloadFile(vJson.downloads.client.url, jar, p => onStep(`Client JAR ${Math.round(p * 100)}%`, 0.02 + p * 0.18));
  const libs = vJson.libraries || [];
  const libTasks = [];
  for (const lib of libs) {
    if (lib.downloads?.artifact?.url && lib.downloads.artifact.path)
      libTasks.push({ url: lib.downloads.artifact.url, dest: path.join(LIBRARIES_DIR, lib.downloads.artifact.path) });
    if (lib.downloads?.classifiers) {
      const osKey = process.platform === 'win32' ? 'windows' : process.platform === 'darwin' ? 'osx' : 'linux';
      const arch = REAL_ARCH === 'x64' ? '64' : '32';
      const wantedKeys = new Set([
        lib.natives?.[osKey]?.replace('${arch}', arch),
        `natives-${osKey === 'osx' ? 'macos' : osKey}`,
        `natives-${osKey}`,
        `natives-${osKey}-${arch}`,
      ].filter(Boolean));
      for (const [key, a] of Object.entries(lib.downloads.classifiers)) {
        if (wantedKeys.has(key) || key.includes(osKey)) {
          if (a?.url && a?.path) libTasks.push({ url: a.url, dest: path.join(LIBRARIES_DIR, a.path) });
        }
      }
    }
  }
  onStep(`Downloading ${libTasks.length} libraries...`, 0.22);
  await downloadBatch(libTasks, 48, (done, total) => {
    onStep(`Libraries ${done}/${total}`, 0.22 + (done / total) * 0.25);
  });
  const ai = vJson.assetIndex;
  const indexPath = path.join(ASSETS_DIR, 'indexes', `${ai.id}.json`);
  if (!fs.existsSync(indexPath)) await downloadFile(ai.url, indexPath, () => { });
  const objects = JSON.parse(fs.readFileSync(indexPath)).objects;
  const assetTasks = Object.values(objects).map(a => {
    const sub = a.hash.substring(0, 2);
    return { url: `https://resources.download.minecraft.net/${sub}/${a.hash}`, dest: path.join(ASSETS_DIR, 'objects', sub, a.hash) };
  });
  onStep(`Downloading ${assetTasks.length} assets...`, 0.48);
  await downloadBatch(assetTasks, 96, (done, total) => {
    onStep(`Assets ${done}/${total}`, 0.48 + (done / total) * 0.50);
  });
  if (!skipSave) saveProfile(mcVersion, 'vanilla', mcVersion, null, customNameArg);
  return vJson;
}
ipcMain.handle('install-vanilla', async (event, { mcVersion, customName }) => {
  try {
    ensureDirs();
    await installVanilla(mcVersion, (step, progress) => event.sender.send('install-progress', { step, progress }), customName);
    event.sender.send('install-progress', { step: '✓ Installed!', progress: 1 });
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('install-fabric', async (event, { mcVersion, loaderVersion, customName }) => {
  try {
    ensureDirs();
    const send = (step, p) => event.sender.send('install-progress', { step, progress: p });
    const vanillaJsonPath = path.join(VERSIONS_DIR, mcVersion, `${mcVersion}.json`);
    if (!fs.existsSync(vanillaJsonPath)) {
      await installVanilla(mcVersion, (step, p) => send(step, p * 0.45), null, true);
    }
    send('Fetching Fabric profile...', 0.46);
    const baseProfileId = `fabric-loader-${loaderVersion}-${mcVersion}`;
    const slug = customName ? customName.toLowerCase().replace(/[^a-z0-9._-]/g, '-').replace(/-+/g, '-').slice(0, 48) : null;
    const profileId = slug && slug !== baseProfileId ? slug : baseProfileId;
    const profile = await fetchJSON(`https://meta.fabricmc.net/v2/versions/loader/${mcVersion}/${loaderVersion}/profile/json`);
    profile.id = profileId;
    const fabricDir = path.join(VERSIONS_DIR, profileId);
    if (!fs.existsSync(fabricDir)) fs.mkdirSync(fabricDir, { recursive: true });
    fs.writeFileSync(path.join(fabricDir, `${profileId}.json`), JSON.stringify(profile, null, 2));
    const libs = profile.libraries || [];
    const fabTasks = [];
    for (const lib of libs) {
      if (lib.name) {
        const [group, artifact, version] = lib.name.split(':');
        const g = group.replace(/\./g, '/');
        const jarName = `${artifact}-${version}.jar`;
        const dest = path.join(LIBRARIES_DIR, g, artifact, version, jarName);
        const base = (lib.url || 'https://repo1.maven.org/maven2/').replace(/\/?$/, '/');
        fabTasks.push({ url: `${base}${g}/${artifact}/${version}/${jarName}`, dest });
      }
    }
    send(`Downloading ${fabTasks.length} Fabric libraries...`, 0.47);
    await downloadBatch(fabTasks, 48, (done, total) => {
      send(`Fabric libs ${done}/${total}`, 0.47 + (done / total) * 0.52);
    });
    send('✓ Fabric installed! Mods folder ready.', 1);
    saveProfile(profileId, 'fabric', mcVersion, loaderVersion, customName || baseProfileId);
    return { success: true, profileId };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('launch-game', async (event, { versionId, username, ram, serverAddress, serverPort, jvmFlags }) => {
  const log = msg => event.sender.send('game-log', msg);
  try {
    const versionDir = path.join(VERSIONS_DIR, versionId);
    const jsonPath = path.join(versionDir, `${versionId}.json`);
    if (!fs.existsSync(jsonPath)) return { error: `Not installed: ${versionId}` };
    event.sender.send('game-status', { status: 'launching' });
    rpcStartTime = Date.now();
    setDiscordPresence('launcher', {
      details: `Playing Minecraft ${versionId}`,
      state: 'Launched via Justice Launcher',
      largeImageKey: 'minecraft_logo',
      largeImageText: `Minecraft ${versionId}`,
      smallImageKey: 'justice_logo',
      smallImageText: 'Justice Launcher',
    });
    log(`\n══════════════════════════════\n Justice Launcher — ${versionId}\n══════════════════════════════\n`);
    let authData = await getValidAuth();
    let playerUsername = username || 'Player';
    let playerUUID = '00000000-0000-0000-0000-000000000000';
    let accessToken = 'offline';
    let userType = 'legacy';
    if (authData) {
      playerUsername = authData.username;
      playerUUID = authData.uuid.replace(/-/g, '').replace(/(.{8})(.{4})(.{4})(.{4})(.{12})/, '$1-$2-$3-$4-$5');
      accessToken = authData.mcAccessToken;
      userType = 'msa';
      log(`Authenticated as: ${playerUsername} (${playerUUID})\n`);
    } else {
      playerUsername = '🔮' + playerUsername;
      log(`Offline mode: ${playerUsername}\n`);
    }
    const vJson = JSON.parse(fs.readFileSync(jsonPath, 'utf8'));
    let parentJson = null;
    if (vJson.inheritsFrom) {
      const pid = vJson.inheritsFrom;
      const pp = path.join(VERSIONS_DIR, pid, `${pid}.json`);
      if (!fs.existsSync(pp)) return { error: `Parent "${pid}" missing. Re-install the mod loader.` };
      parentJson = JSON.parse(fs.readFileSync(pp, 'utf8'));
      log(`Inheriting from: ${pid}\n`);
    }
    const baseDir = parentJson ? path.join(VERSIONS_DIR, vJson.inheritsFrom) : versionDir;
    const nativesDir = path.join(baseDir, 'natives');
    if (fs.existsSync(nativesDir)) {
      const existing = fs.readdirSync(nativesDir).length;
      if (existing < 5) {
        log(`Natives dir has only ${existing} file(s) — clearing for full re-extraction...\n`);
        fs.readdirSync(nativesDir).forEach(f => {
          try { fs.unlinkSync(path.join(nativesDir, f)); } catch (_) { }
        });
      }
    }
    // On macOS arm64, ensure arm64 native JARs are downloaded (may be missing if installed on older build)
    if (process.platform === 'darwin' && REAL_ARCH === 'arm64') {
      const nativeLibs = (parentJson || vJson).libraries || [];
      const missingArm64 = [];
      for (const lib of nativeLibs) {
        if (!lib.downloads?.artifact?.path) continue;
        const p = lib.downloads.artifact.path;
        if (p.includes('natives-macos-arm64') || p.includes('natives-macos-aarch64')) {
          const dest = path.join(LIBRARIES_DIR, p);
          if (!fs.existsSync(dest) && lib.downloads.artifact.url) {
            missingArm64.push({ url: lib.downloads.artifact.url, dest });
          }
        }
      }
      if (missingArm64.length > 0) {
        log(`Downloading ${missingArm64.length} missing arm64 native libraries...\n`);
        await downloadBatch(missingArm64, 8, () => {});
      }
    }
    log('Extracting natives...\n');
    const nativeResult = extractNatives(parentJson || vJson, nativesDir);
    const nativeFilesOnDisk = fs.existsSync(nativesDir) ? fs.readdirSync(nativesDir).filter(f => !f.startsWith('.')).length : 0;
    log(`Natives: scanned ${nativeResult.jarCount} native JARs, extracted ${nativeResult.extracted} new files (${nativeFilesOnDisk} total on disk)\n`);
    if (nativeFilesOnDisk === 0) log('WARNING: No natives on disk! LWJGL will likely fail.\n');
    function deduplicateCp(entries) {
      const seen = new Map();
      for (const p of entries) {
        const parts = p.replace(/\\/g, '/').split('/');
        const jarIdx = parts.length - 1;
        const key = parts.slice(0, jarIdx - 1).join('/');
        if (!seen.has(key)) seen.set(key, p);
      }
      return [...seen.values()];
    }
    const rawCp = [...buildClasspath(vJson)];
    if (parentJson) rawCp.push(...buildClasspath(parentJson));
    const clientJar = parentJson
      ? path.join(VERSIONS_DIR, vJson.inheritsFrom, `${vJson.inheritsFrom}.jar`)
      : path.join(versionDir, `${versionId}.jar`);
    if (!fs.existsSync(clientJar)) return { error: `Client JAR missing: ${clientJar}` };
    rawCp.push(clientJar);
    const finalCp = deduplicateCp(rawCp);
    log(`Classpath: ${finalCp.length} JARs (deduped from ${rawCp.length})\n`);
    const isFabric = !!vJson.inheritsFrom;
    if (isFabric) {
      try { await ensureNovaPresenceMod(versionId, log); } catch (e) {
        log('[NovaPresence] Could not inject mod: ' + e.message + '\n');
      }
      try {
        const baseMcVersion = vJson.inheritsFrom;
        await ensureJusticeMod(versionId, baseMcVersion, log);
      } catch (e) {
        log('[JusticeMod] Could not inject mod: ' + e.message + '\n');
      }
    }
    const mainClass = vJson.mainClass || parentJson?.mainClass;
    const aiId = (parentJson || vJson).assetIndex?.id || vJson.inheritsFrom || versionId;
    log(`Main class: ${mainClass}\n`);
    const instDir = instanceDir(versionId);
    const vModsDir = modsDir(versionId);
    try {
      const settingsPath = path.join(GAME_DIR, 'settings.json');
      let settings = {}; try { settings = JSON.parse(fs.readFileSync(settingsPath, 'utf8')); } catch (_) { }
      if (settings.novaTheme === true) { ensureNovaTheme(versionId); log('Nova theme resource pack applied\n'); }
    } catch (_) { }
    log(`Instance dir: ${instDir}\n`);
    log(`Mods dir: ${vModsDir} (${fs.readdirSync(vModsDir).length} mods)\n`);
    const extraJvmFlags = (event.sender.getURL ? [] : []);
    const jvmArgs = [
      `-Xmx${ram || 2048}M`, `-Xms512M`,
      `-Djava.library.path=${nativesDir}`,
      `-Dorg.lwjgl.librarypath=${nativesDir}`,
      `-Djna.tmpdir=${nativesDir}`,
      `-Dorg.lwjgl.system.SharedLibraryExtractPath=${nativesDir}`,
      `-Dio.netty.native.workdir=${nativesDir}`,
    ];
    const argVars = {
      natives_directory: nativesDir,
      launcher_name: 'justice-launcher',
      launcher_version: '1.0',
      auth_player_name: playerUsername,
      auth_uuid: playerUUID,
      auth_access_token: accessToken,
      user_type: userType,
      version_name: versionId,
      game_directory: instDir,
      assets_root: ASSETS_DIR,
      assets_index_name: aiId,
      version_type: 'release',
      library_directory: LIBRARIES_DIR,
      classpath_separator: path.delimiter,
    };
    if (isFabric) {
      jvmArgs.push(`-Dfabric.gameJarPath=${clientJar}`);
      jvmArgs.push(`-Dfabric.development=false`);
    }
    const gameArgs = [
      '--username', playerUsername,
      '--version', versionId,
      '--gameDir', instDir,
      '--assetsDir', ASSETS_DIR,
      '--assetIndex', aiId,
      '--uuid', playerUUID,
      '--accessToken', accessToken,
      '--userType', userType,
      '--versionType', 'release',
    ];
    if (isFabric) gameArgs.push('--launchTarget', 'client');
    if (serverAddress) { gameArgs.push('--server', serverAddress); gameArgs.push('--port', String(serverPort || 25565)); log(`Connecting to: ${serverAddress}:${serverPort || 25565}\n`); }
    let javaPath = await findJava();
    if (!javaPath) {
      log('Java not found — downloading Java 21 automatically...\n');
      event.sender.send('game-status', { status: 'installing-java' });
      try {
        javaPath = await installJava((step, p) => {
          log(`${step}\n`);
          event.sender.send('java-install-progress', { step, progress: p });
        });
        log(`Java installed: ${javaPath}\n`);
      } catch (e) {
        log(`\nFailed to auto-install Java: ${e.message}\n`);
        log('Please install Java 21 manually from https://adoptium.net\n');
        event.sender.send('game-status', { status: 'closed' });
        return { error: 'Java not found and auto-install failed: ' + e.message };
      }
    }
    log(`Java: ${javaPath}\n`);
    log(`\nSpawning Java...\n────────────────────────────\n`);
    if (jvmFlags) {
      const extra = jvmFlags.trim().split(/\s+/).filter(Boolean);
      jvmArgs.push(...extra);
    }
    const args = [...jvmArgs, '-cp', finalCp.join(path.delimiter), mainClass, ...gameArgs];
    const java = spawn(javaPath, args, { cwd: instDir, detached: false, stdio: ['ignore', 'pipe', 'pipe'] });
    java.stdout.on('data', d => log(d.toString()));
    java.stderr.on('data', d => log(d.toString()));
    java.on('error', err => {
      if (err.code === 'ENOENT') {
        log(`\nJava binary not executable: ${javaPath}\n`);
        log('Try deleting ~/.justice-launcher/java and relaunching to re-download.\n');
      } else {
        log(`\nJava error: ${err.message}\n`);
      }
      event.sender.send('game-status', { status: 'closed' });
    });
    java.on('close', code => {
      log(`\n────────────────────────────\nExit code: ${code}\n`);
      event.sender.send('game-status', { status: 'closed' });
      event.sender.send('overlay-game-stop');
      rpcStartTime = Date.now();
      setDiscordPresence('launcher');
      // Restore launcher window when game closes
      try {
        if (mainWindow && !mainWindow.isDestroyed() && mainWindow.isMinimized()) {
          mainWindow.restore();
          mainWindow.focus();
        }
      } catch (_) { }
    });
    event.sender.send('game-launched-overlay');
    // Minimize launcher after short delay to let game start
    try {
      const settingsPath2 = path.join(GAME_DIR, 'settings.json');
      let s2 = {}; try { s2 = JSON.parse(fs.readFileSync(settingsPath2, 'utf8')); } catch (_) { }
      if (s2.minimizeOnLaunch !== false && mainWindow && !mainWindow.isDestroyed()) {
        setTimeout(() => {
          if (mainWindow && !mainWindow.isDestroyed()) mainWindow.minimize();
        }, 2000);
      }
    } catch (_) { }
    return { success: true, pid: java.pid };
  } catch (e) {
    log(`\nCrash: ${e.stack}\n`);
    event.sender.send('game-status', { status: 'closed' });
    return { error: e.message };
  }
});
function saveProfile(id, type, mcVersion, loaderVersion, customName) {
  const p = path.join(GAME_DIR, 'profiles.json');
  let profiles = {};
  try { profiles = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
  profiles[id] = {
    id, type, mcVersion, loaderVersion,
    customName: customName || null,
    installedAt: new Date().toISOString(),
  };
  fs.writeFileSync(p, JSON.stringify(profiles, null, 2));
}
ipcMain.handle('rename-version', (_, { versionId, customName }) => {
  try {
    const p = path.join(GAME_DIR, 'profiles.json');
    let profiles = {};
    try { profiles = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
    if (!profiles[versionId]) return { error: 'Version not found' };
    profiles[versionId].customName = customName || null;
    fs.writeFileSync(p, JSON.stringify(profiles, null, 2));
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('get-installed-versions', () => {
  try { return JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { return {}; }
});
ipcMain.handle('delete-version', (_, versionId) => {
  try {
    const p = path.join(GAME_DIR, 'profiles.json');
    let profiles = {};
    try { profiles = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
    delete profiles[versionId];
    fs.writeFileSync(p, JSON.stringify(profiles, null, 2));
    const vDir = path.join(VERSIONS_DIR, versionId);
    if (fs.existsSync(vDir)) {
      const rmDir = (d) => {
        for (const f of fs.readdirSync(d)) {
          const fp = path.join(d, f);
          if (fs.statSync(fp).isDirectory()) rmDir(fp);
          else fs.unlinkSync(fp);
        }
        fs.rmdirSync(d);
      };
      rmDir(vDir);
    }
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.on('open-url', (_, url) => shell.openExternal(url));
ipcMain.on('window-minimize', () => mainWindow.minimize());
ipcMain.on('window-maximize', () => mainWindow.isMaximized() ? mainWindow.unmaximize() : mainWindow.maximize());
ipcMain.on('window-close', () => mainWindow.close());
ipcMain.on('open-game-dir', () => shell.openPath(GAME_DIR));
ipcMain.on('open-mods-dir', (_, versionId) => shell.openPath(modsDir(versionId)));
ipcMain.handle('ensure-justice-mod', async (event, versionId, passedMcVersion) => {
  const log = msg => { try { event.sender.send('game-log', msg); } catch { } };

  // --- Determine the base Minecraft version for this instance ---
  let mcVersion = passedMcVersion || null;
  if (!mcVersion) {
    try {
      const vJsonPath = path.join(VERSIONS_DIR, versionId, `${versionId}.json`);
      if (fs.existsSync(vJsonPath)) {
        const vJson = JSON.parse(fs.readFileSync(vJsonPath, 'utf8'));
        mcVersion = vJson.inheritsFrom || null;
      }
    } catch (_) { }
  }
  if (!mcVersion) mcVersion = versionId;
  log(`[JusticeMod] Detected MC version: ${mcVersion}\n`);

  // --- Map MC version to the correct bundled Justice Mod JAR ---
  const JUSTICE_MODS = [
    { file: 'justice-1.21-1.21.1.jar',    versions: ['1.21', '1.21.1'] },
    { file: 'justice-1.21.2-1.21.4.jar',  versions: ['1.21.2', '1.21.3', '1.21.4'] },
    { file: 'justice-1.21.6-1.21.8.jar',  versions: ['1.21.6', '1.21.7', '1.21.8'] },
    { file: 'justice-1.21.9-1.21.10.jar', versions: ['1.21.9', '1.21.10'] },
    { file: 'justice-1.21.11.jar',         versions: ['1.21.11'] },
  ];

  const matched = JUSTICE_MODS.find(m => m.versions.includes(mcVersion));
  if (!matched) {
    log(`[JusticeMod] No Justice Mod available for Minecraft ${mcVersion}, skipping.\n`);
    return { ok: true, skipped: true };
  }

  const dir = modsDir(versionId);
  fs.mkdirSync(dir, { recursive: true });

  // --- Remove any old/mismatched Justice Mod JARs ---
  const allJusticeFiles = JUSTICE_MODS.map(m => m.file);
  for (const oldFile of allJusticeFiles) {
    if (oldFile !== matched.file) {
      const oldPath = path.join(dir, oldFile);
      if (fs.existsSync(oldPath)) {
        try { fs.unlinkSync(oldPath); log(`[JusticeMod] Removed outdated: ${oldFile}\n`); } catch (_) { }
      }
    }
  }
  // Also clean up the legacy justice-mod.jar if present
  const legacyPath = path.join(dir, 'justice-mod.jar');
  if (fs.existsSync(legacyPath)) {
    try { fs.unlinkSync(legacyPath); log('[JusticeMod] Removed legacy justice-mod.jar\n'); } catch (_) { }
  }

  const destPath = path.join(dir, matched.file);
  if (fs.existsSync(destPath)) {
    log(`[JusticeMod] ${matched.file} already present.\n`);
    return { ok: true, skipped: true };
  }

  // --- Find the bundled JAR from known locations ---
  const candidates = [
    path.join(process.resourcesPath || '', 'bundled-mods', 'justice', matched.file),
    path.join(__dirname, 'bundled-mods', 'justice', matched.file),
    path.join(process.cwd(), 'bundled-mods', 'justice', matched.file),
  ];
  log(`[JusticeMod] Looking for ${matched.file}...\n`);
  for (const c of candidates) log(`[JusticeMod]   checking: ${c} → ${fs.existsSync(c) ? 'FOUND' : 'not found'}\n`);
  const src = candidates.find(p => fs.existsSync(p));
  if (!src) {
    log(`[JusticeMod] ERROR: ${matched.file} not found in bundled-mods. Place it in bundled-mods/justice/.\n`);
    return { ok: false, error: `Bundled mod not found: ${matched.file}` };
  }

  try {
    fs.copyFileSync(src, destPath);
    log(`[JusticeMod] Installed ${matched.file} → ${dir}\n`);
    return { ok: true };
  } catch (err) {
    log(`[JusticeMod] ERROR copying mod: ${err.message}\n`);
    return { ok: false, error: err.message };
  }
});
ipcMain.on('open-instance-dir', (_, versionId) => shell.openPath(instanceDir(versionId)));
ipcMain.handle('get-mods', (_, versionId) => {
  try {
    const dir = modsDir(versionId);
    return fs.readdirSync(dir)
      .filter(f => f.endsWith('.jar') || f.endsWith('.jar.disabled'))
      .map(f => {
        const fullPath = path.join(dir, f);
        const stat = fs.statSync(fullPath);
        return {
          name: f,
          displayName: f.replace('.jar.disabled', '').replace('.jar', ''),
          enabled: f.endsWith('.jar'),
          size: stat.size,
          addedAt: stat.birthtime.toISOString(),
          path: fullPath,
        };
      })
      .sort((a, b) => a.displayName.localeCompare(b.displayName));
  } catch (e) { return []; }
});
ipcMain.handle('install-mod', (_, { versionId, sourcePath, fileName }) => {
  try { fs.copyFileSync(sourcePath, path.join(modsDir(versionId), fileName)); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
ipcMain.handle('delete-mod', (_, { versionId, fileName }) => {
  try { fs.unlinkSync(path.join(modsDir(versionId), fileName)); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
ipcMain.handle('toggle-mod', (_, { versionId, fileName }) => {
  try {
    const dir = modsDir(versionId);
    const fullPath = path.join(dir, fileName);
    if (fileName.endsWith('.jar.disabled')) {
      const n = fileName.replace('.jar.disabled', '.jar');
      fs.renameSync(fullPath, path.join(dir, n));
      return { success: true, newName: n };
    } else {
      const n = fileName + '.disabled';
      fs.renameSync(fullPath, path.join(dir, n));
      return { success: true, newName: n };
    }
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('get-mod-count', (_, versionId) => {
  try { return fs.readdirSync(modsDir(versionId)).filter(f => f.endsWith('.jar') || f.endsWith('.jar.disabled')).length; }
  catch (_) { return 0; }
});
ipcMain.handle('get-world-count', (_, versionId) => {
  try {
    const savesDir = path.join(instanceDir(versionId), 'saves');
    if (!fs.existsSync(savesDir)) return 0;
    return fs.readdirSync(savesDir).filter(f => fs.statSync(path.join(savesDir, f)).isDirectory()).length;
  } catch (_) { return 0; }
});
ipcMain.handle('get-resource-pack-count', (_, versionId) => {
  try {
    const dir = path.join(instanceDir(versionId), 'resourcepacks');
    if (!fs.existsSync(dir)) return 0;
    return fs.readdirSync(dir).filter(f => f.endsWith('.zip') || f.endsWith('.zip.disabled')).length;
  } catch (_) { return 0; }
});
ipcMain.handle('get-shader-count', (_, versionId) => {
  try {
    const dir = path.join(instanceDir(versionId), 'shaderpacks');
    if (!fs.existsSync(dir)) return 0;
    return fs.readdirSync(dir).filter(f => f.endsWith('.zip') || f.endsWith('.zip.disabled') || fs.statSync(path.join(dir, f)).isDirectory()).length;
  } catch (_) { return 0; }
});
function ensureNovaTheme(versionId) {
  try {
    if (fs.existsSync(BUNDLED_THEME)) {
      const bMtime = fs.statSync(BUNDLED_THEME).mtimeMs;
      const gMtime = fs.existsSync(THEME_PACK) ? fs.statSync(THEME_PACK).mtimeMs : 0;
      if (bMtime > gMtime) fs.copyFileSync(BUNDLED_THEME, THEME_PACK);
    }
    const packSrc = THEME_PACK;
    if (!fs.existsSync(packSrc)) return;
    const instDir = instanceDir(versionId);
    const rpDir = path.join(instDir, 'resourcepacks');
    if (!fs.existsSync(rpDir)) fs.mkdirSync(rpDir, { recursive: true });
    const dest = path.join(rpDir, 'nova-theme.zip');
    const srcMtime = fs.statSync(packSrc).mtimeMs;
    const dstMtime = fs.existsSync(dest) ? fs.statSync(dest).mtimeMs : 0;
    if (srcMtime > dstMtime) fs.copyFileSync(packSrc, dest);
    const optFile = path.join(instDir, 'options.txt');
    let opts = fs.existsSync(optFile) ? fs.readFileSync(optFile, 'utf8') : '';
    const packEntry = '"file/nova-theme.zip"';
    const rpLine = opts.split('\n').find(l => l.startsWith('resourcePacks:'));
    if (!rpLine) {
      opts += (opts.endsWith('\n') ? '' : '\n') + `resourcePacks:[${packEntry}]\n`;
    } else if (!rpLine.includes(packEntry)) {
      const updated = rpLine.replace('resourcePacks:[', `resourcePacks:[${packEntry},`);
      opts = opts.replace(rpLine, updated);
    }
    fs.writeFileSync(optFile, opts);
  } catch (_) { }
}
ipcMain.handle('get-theme-enabled', () => {
  const p = path.join(GAME_DIR, 'settings.json');
  try { const s = JSON.parse(fs.readFileSync(p, 'utf8')); return s.novaTheme === true; } catch (_) { return false; }
});
ipcMain.handle('set-theme-enabled', (_, val) => {
  const p = path.join(GAME_DIR, 'settings.json');
  let s = {}; try { s = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
  s.novaTheme = val;
  fs.writeFileSync(p, JSON.stringify(s));
  return { success: true };
});
ipcMain.handle('get-settings', () => {
  const p = path.join(GAME_DIR, 'settings.json');
  try { return JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { return {}; }
});
ipcMain.handle('save-settings', (_, settings) => {
  const p = path.join(GAME_DIR, 'settings.json');
  let existing = {};
  try { existing = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
  const merged = { ...existing, ...settings };
  fs.writeFileSync(p, JSON.stringify(merged, null, 2));
  return { success: true };
});
const { net: electronNet } = require('electron');
ipcMain.handle('get-skin-url', async () => {
  const auth = await getValidAuth();
  if (!auth) return { error: 'Not logged in' };
  try {
    const profile = await fetchJSON(MC_PROFILE_URL, {
      headers: { Authorization: `Bearer ${auth.mcAccessToken}` },
    });
    const skin = (profile.skins || []).find(s => s.state === 'ACTIVE');
    const cape = (profile.capes || []).find(c => c.state === 'ACTIVE');
    return {
      skinUrl: skin?.url || null,
      variant: skin?.variant || 'CLASSIC',
      capeName: cape?.alias || null,
      capeUrl: cape?.url || null,
      username: profile.name,
      uuid: profile.id,
    };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('upload-skin', async (_, { filePath, variant }) => {
  const auth = await getValidAuth();
  if (!auth) return { error: 'Not logged in' };
  try {
    const skinData = fs.readFileSync(filePath);
    const boundary = '----JusticeBoundary' + Date.now().toString(16);
    const CRLF = '\r\n';
    const variantStr = (variant || 'classic').toLowerCase();
    let body = Buffer.concat([
      Buffer.from(
        `--${boundary}${CRLF}` +
        `Content-Disposition: form-data; name="variant"${CRLF}${CRLF}` +
        `${variantStr}${CRLF}` +
        `--${boundary}${CRLF}` +
        `Content-Disposition: form-data; name="file"; filename="skin.png"${CRLF}` +
        `Content-Type: image/png${CRLF}${CRLF}`
      ),
      skinData,
      Buffer.from(`${CRLF}--${boundary}--${CRLF}`),
    ]);
    const result = await new Promise((resolve, reject) => {
      const urlObj = new URL('https://api.minecraftservices.com/minecraft/profile/skins');
      const req = https.request(urlObj, {
        method: 'POST',
        agent: httpAgent,
        headers: {
          Authorization: `Bearer ${auth.mcAccessToken}`,
          'Content-Type': `multipart/form-data; boundary=${boundary}`,
          'Content-Length': body.length,
          'User-Agent': 'JusticeLauncher/1.0',
        },
      }, (res) => {
        let d = '';
        res.on('data', c => d += c);
        res.on('end', () => {
          try { resolve({ status: res.statusCode, body: JSON.parse(d) }); }
          catch (_) { resolve({ status: res.statusCode, body: d }); }
        });
      });
      req.on('error', reject);
      req.write(body);
      req.end();
    });
    if (result.status !== 200) {
      return { error: `Server returned ${result.status}: ${JSON.stringify(result.body)}` };
    }
    const skins = result.body.skins || [];
    const active = skins.find(s => s.state === 'ACTIVE');
    return { success: true, skinUrl: active?.url || null, variant: active?.variant || variant };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('reset-skin', async () => {
  const auth = await getValidAuth();
  if (!auth) return { error: 'Not logged in' };
  try {
    await new Promise((resolve, reject) => {
      const req = https.request('https://api.minecraftservices.com/minecraft/profile/skins/active', {
        method: 'DELETE',
        agent: httpAgent,
        headers: { Authorization: `Bearer ${auth.mcAccessToken}`, 'User-Agent': 'JusticeLauncher/1.0' },
      }, res => { res.resume(); resolve(res.statusCode); });
      req.on('error', reject);
      req.end();
    });
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('pick-skin-file', async () => {
  const { dialog } = require('electron');
  const result = await dialog.showOpenDialog(mainWindow, {
    title: 'Select Skin PNG',
    filters: [{ name: 'PNG Image', extensions: ['png'] }],
    properties: ['openFile'],
  });
  if (result.canceled || !result.filePaths.length) return null;
  return result.filePaths[0];
});

/* ═══ SKIN CATALOG ═══════════════════════════════════════════════════ */
const SKIN_CATALOG_DIR = path.join(GAME_DIR, 'skin-catalog');
const SKIN_CATALOG_META = path.join(SKIN_CATALOG_DIR, 'catalog.json');

function ensureSkinCatalog() {
  if (!fs.existsSync(SKIN_CATALOG_DIR)) fs.mkdirSync(SKIN_CATALOG_DIR, { recursive: true });
  if (!fs.existsSync(SKIN_CATALOG_META)) fs.writeFileSync(SKIN_CATALOG_META, '[]', 'utf8');
}

ipcMain.handle('skin-catalog-list', async () => {
  try {
    ensureSkinCatalog();
    const raw = fs.readFileSync(SKIN_CATALOG_META, 'utf8');
    const entries = JSON.parse(raw);
    // Return entries with base64 data for preview
    return entries.map(e => {
      const imgPath = path.join(SKIN_CATALOG_DIR, e.filename);
      let data = null;
      try { data = 'data:image/png;base64,' + fs.readFileSync(imgPath).toString('base64'); } catch (_) {}
      return { ...e, data };
    }).filter(e => e.data);
  } catch (err) { return []; }
});

ipcMain.handle('skin-catalog-save', async (_, { filePath, name, variant, dataUrl }) => {
  try {
    ensureSkinCatalog();
    const id = Date.now().toString(36) + Math.random().toString(36).slice(2, 6);
    const filename = id + '.png';
    const destPath = path.join(SKIN_CATALOG_DIR, filename);

    if (dataUrl) {
      // Save from data URL (base64)
      const base64 = dataUrl.replace(/^data:image\/png;base64,/, '');
      fs.writeFileSync(destPath, Buffer.from(base64, 'base64'));
    } else if (filePath) {
      fs.copyFileSync(filePath, destPath);
    } else {
      return { error: 'No skin data provided' };
    }

    const raw = fs.readFileSync(SKIN_CATALOG_META, 'utf8');
    const entries = JSON.parse(raw);
    entries.unshift({ id, filename, name: name || 'Skin', variant: variant || 'CLASSIC', savedAt: new Date().toISOString() });
    fs.writeFileSync(SKIN_CATALOG_META, JSON.stringify(entries, null, 2), 'utf8');
    return { success: true, id };
  } catch (err) { return { error: err.message }; }
});

ipcMain.handle('skin-catalog-delete', async (_, { id }) => {
  try {
    ensureSkinCatalog();
    const raw = fs.readFileSync(SKIN_CATALOG_META, 'utf8');
    let entries = JSON.parse(raw);
    const entry = entries.find(e => e.id === id);
    if (entry) {
      const imgPath = path.join(SKIN_CATALOG_DIR, entry.filename);
      try { fs.unlinkSync(imgPath); } catch (_) {}
      entries = entries.filter(e => e.id !== id);
      fs.writeFileSync(SKIN_CATALOG_META, JSON.stringify(entries, null, 2), 'utf8');
    }
    return { success: true };
  } catch (err) { return { error: err.message }; }
});

ipcMain.handle('skin-catalog-rename', async (_, { id, name }) => {
  try {
    ensureSkinCatalog();
    const raw = fs.readFileSync(SKIN_CATALOG_META, 'utf8');
    const entries = JSON.parse(raw);
    const entry = entries.find(e => e.id === id);
    if (entry) entry.name = name;
    fs.writeFileSync(SKIN_CATALOG_META, JSON.stringify(entries, null, 2), 'utf8');
    return { success: true };
  } catch (err) { return { error: err.message }; }
});

ipcMain.handle('skin-catalog-get-path', async (_, { id }) => {
  try {
    ensureSkinCatalog();
    const raw = fs.readFileSync(SKIN_CATALOG_META, 'utf8');
    const entries = JSON.parse(raw);
    const entry = entries.find(e => e.id === id);
    if (!entry) return { error: 'Skin not found' };
    return { path: path.join(SKIN_CATALOG_DIR, entry.filename), variant: entry.variant };
  } catch (err) { return { error: err.message }; }
});

ipcMain.handle('auth-token-login', async (event, { token, uuid }) => {
  try {
    const profile = await getMCProfile(token);
    if (!profile.uuid) throw new Error('Profile lookup failed');
    if (uuid && uuid.replace(/-/g, '') !== profile.uuid.replace(/-/g, '')) {
      throw new Error('Supplied UUID does not match the token owner (' + profile.username + ')');
    }
    const authData = {
      mcAccessToken: token,
      uuid: profile.uuid,
      username: profile.username,
      msRefreshToken: null,
      expiresAt: Date.now() + 86400 * 1000,
      authType: 'token',
    };
    addOrUpdateAccount(authData);
    if (!event.sender.isDestroyed()) {
      const store = loadAccounts();
      event.sender.send('auth-changed', {
        loggedIn: true,
        username: authData.username,
        uuid: authData.uuid,
        activeUuid: store.activeUuid,
        accounts: store.accounts.map(a => ({ uuid: a.uuid, username: a.username, authType: a.authType })),
      });
    }
    return { success: true, username: profile.username, uuid: profile.uuid };
  } catch (e) {
    return { error: e.message };
  }
});
const MODRINTH_BASE = 'https://api.modrinth.com/v2';
const MR_HEADERS = { 'User-Agent': 'JusticeLauncher/1.0', 'Accept': 'application/json' };
async function mrGet(endpoint) {
  return fetchJSON(MODRINTH_BASE + endpoint, { headers: MR_HEADERS });
}
ipcMain.handle('mr-search', async (_, { query, mcVersion, category, loader, offset }) => {
  try {
    const facets = [['project_type:mod']];
    if (mcVersion) facets.push([`versions:${mcVersion}`]);
    if (category) facets.push([`categories:${category}`]);
    if (loader) facets.push([`categories:${loader}`]);
    const params = new URLSearchParams({ query: query || '', limit: 20, offset: offset || 0, index: 'relevance', facets: JSON.stringify(facets) });
    return await mrGet(`/search?${params}`);
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('mr-get-versions', async (_, { projectId, mcVersion, loader }) => {
  try {
    const params = new URLSearchParams();
    if (mcVersion) params.set('game_versions', JSON.stringify([mcVersion]));
    if (loader) params.set('loaders', JSON.stringify([loader]));
    const data = await mrGet(`/project/${projectId}/version?${params}`);
    return Array.isArray(data) ? data : [];
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('mr-install-mod', async (event, { versionId: profileId, fileName, downloadUrl }) => {
  try {
    const dest = path.join(modsDir(profileId), fileName);
    await new Promise((resolve, reject) => {
      const get = u => https.get(u, { agent: httpAgent, headers: { 'User-Agent': 'JusticeLauncher/1.0' } }, res => {
        if ([301, 302, 303, 307, 308].includes(res.statusCode)) return get(res.headers.location);
        if (res.statusCode !== 200) { res.resume(); return reject(new Error(`HTTP ${res.statusCode}`)); }
        const total = parseInt(res.headers['content-length'] || '0');
        let done = 0;
        const file = fs.createWriteStream(dest);
        res.on('data', c => { done += c.length; if (total) event.sender.send('mr-dl-progress', { done, total }); });
        res.pipe(file);
        file.on('finish', () => { file.close(); resolve(); });
        file.on('error', err => { try { fs.unlinkSync(dest); } catch (_) { } reject(err); });
      }).on('error', reject);
      get(downloadUrl);
    });
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('mr-categories', async () => {
  try { const c = await mrGet('/tag/category'); return c.filter(x => x.project_type === 'mod').map(x => x.name); }
  catch (_) { return []; }
});
function calcDirSize(dir) {
  let total = 0;
  try { for (const f of fs.readdirSync(dir)) { const fp = path.join(dir, f); const s = fs.statSync(fp); if (s.isDirectory()) total += calcDirSize(fp); else total += s.size; } } catch (_) { }
  return total;
}
ipcMain.handle('get-worlds', (_, versionId) => {
  try {
    const savesDir = path.join(instanceDir(versionId), 'saves');
    if (!fs.existsSync(savesDir)) return [];
    return fs.readdirSync(savesDir)
      .filter(f => fs.statSync(path.join(savesDir, f)).isDirectory())
      .map(name => ({ name, path: path.join(savesDir, name), modified: fs.statSync(path.join(savesDir, name)).mtimeMs, size: calcDirSize(path.join(savesDir, name)) }))
      .sort((a, b) => b.modified - a.modified);
  } catch (e) { return []; }
});
ipcMain.handle('backup-world', async (_, { versionId, worldName }) => {
  try {
    const AdmZip = require('adm-zip');
    const worldPath = path.join(instanceDir(versionId), 'saves', worldName);
    if (!fs.existsSync(worldPath)) return { error: 'World not found' };
    const backupsDir = path.join(GAME_DIR, 'backups', versionId);
    if (!fs.existsSync(backupsDir)) fs.mkdirSync(backupsDir, { recursive: true });
    const ts = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
    const dest = path.join(backupsDir, `${worldName}_${ts}.zip`);
    const zip = new AdmZip(); zip.addLocalFolder(worldPath, worldName); zip.writeZip(dest);
    return { success: true, file: dest };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('get-backups', (_, versionId) => {
  try {
    const dir = path.join(GAME_DIR, 'backups', versionId || '');
    if (!fs.existsSync(dir)) return [];
    return fs.readdirSync(dir).filter(f => f.endsWith('.zip'))
      .map(f => { const fp = path.join(dir, f); const s = fs.statSync(fp); return { name: f, path: fp, size: s.size, created: s.mtimeMs }; })
      .sort((a, b) => b.created - a.created);
  } catch (_) { return []; }
});
ipcMain.handle('restore-backup', (_, { versionId, backupPath }) => {
  try {
    const AdmZip = require('adm-zip');
    const savesDir = path.join(instanceDir(versionId), 'saves');
    if (!fs.existsSync(savesDir)) fs.mkdirSync(savesDir, { recursive: true });
    new AdmZip(backupPath).extractAllTo(savesDir, true);
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('delete-backup', (_, backupPath) => {
  try { fs.unlinkSync(backupPath); return { success: true }; } catch (e) { return { error: e.message }; }
});
ipcMain.on('open-saves-dir', (_, vid) => shell.openPath(path.join(instanceDir(vid), 'saves')));
function shadersDir(versionId) {
  const d = path.join(instanceDir(versionId), 'shaderpacks');
  if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true });
  return d;
}
ipcMain.handle('get-shaders', (_, versionId) => {
  try {
    const dir = shadersDir(versionId);
    return fs.readdirSync(dir)
      .filter(f => f.endsWith('.zip') || f.endsWith('.zip.disabled') || fs.statSync(path.join(dir, f)).isDirectory())
      .map(f => { const fp = path.join(dir, f); const s = fs.statSync(fp); return { name: f, enabled: !f.endsWith('.disabled'), size: s.isDirectory() ? 0 : s.size }; })
      .sort((a, b) => a.name.localeCompare(b.name));
  } catch (_) { return []; }
});
ipcMain.handle('install-shader', (_, { versionId, sourcePath, fileName }) => {
  try { fs.copyFileSync(sourcePath, path.join(shadersDir(versionId), fileName)); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
ipcMain.handle('delete-shader', (_, { versionId, fileName }) => {
  try { fs.unlinkSync(path.join(shadersDir(versionId), fileName)); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
ipcMain.handle('toggle-shader', (_, { versionId, fileName }) => {
  try {
    const dir = shadersDir(versionId), fp = path.join(dir, fileName);
    const n = fileName.endsWith('.disabled') ? fileName.replace('.disabled', '') : fileName + '.disabled';
    fs.renameSync(fp, path.join(dir, n)); return { success: true, newName: n };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('pick-shader-file', async () => {
  const { dialog } = require('electron');
  const r = await dialog.showOpenDialog(mainWindow, { title: 'Pick Shader Pack', filters: [{ name: 'Shaders', extensions: ['zip'] }], properties: ['openFile', 'multiSelections'] });
  return r.canceled ? [] : r.filePaths.map(p => ({ path: p, name: path.basename(p) }));
});
ipcMain.on('open-shaders-dir', (_, vid) => shell.openPath(shadersDir(vid)));
ipcMain.handle('get-screenshots', (_, versionId) => {
  try {
    const dir = path.join(instanceDir(versionId), 'screenshots');
    if (!fs.existsSync(dir)) return [];
    return fs.readdirSync(dir).filter(f => /\.(png|jpg|jpeg)$/i.test(f))
      .map(f => { const fp = path.join(dir, f); return { name: f, path: fp, size: fs.statSync(fp).size, created: fs.statSync(fp).mtimeMs, versionId }; })
      .sort((a, b) => b.created - a.created);
  } catch (_) { return []; }
});
ipcMain.handle('get-all-screenshots', () => {
  try {
    let result = [];
    let profiles = {};
    try { profiles = JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { }
    for (const vid of Object.keys(profiles)) {
      const dir = path.join(instanceDir(vid), 'screenshots');
      if (!fs.existsSync(dir)) continue;
      for (const f of fs.readdirSync(dir).filter(f => /\.(png|jpg|jpeg)$/i.test(f))) {
        const fp = path.join(dir, f); result.push({ name: f, path: fp, versionId: vid, size: fs.statSync(fp).size, created: fs.statSync(fp).mtimeMs });
      }
    }
    return result.sort((a, b) => b.created - a.created);
  } catch (_) { return []; }
});
ipcMain.handle('delete-screenshot', (_, filePath) => {
  try { fs.unlinkSync(filePath); return { success: true }; } catch (e) { return { error: e.message }; }
});
ipcMain.handle('copy-screenshot', async (_, filePath) => {
  try {
    const { clipboard, nativeImage } = require('electron');
    clipboard.writeImage(nativeImage.createFromPath(filePath));
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('screenshot-to-dataurl', (_, filePath) => {
  try {
    const buf = fs.readFileSync(filePath);
    const ext = path.extname(filePath).toLowerCase().slice(1);
    const mime = ext === 'jpg' || ext === 'jpeg' ? 'image/jpeg' : 'image/png';
    return 'data:' + mime + ';base64,' + buf.toString('base64');
  } catch (_) { return null; }
});
ipcMain.on('open-screenshot', (_, fp) => shell.openPath(fp));
ipcMain.on('open-screenshots-dir', (_, vid) => shell.openPath(path.join(instanceDir(vid), 'screenshots')));
const SERVERS_FILE = path.join(GAME_DIR, 'servers.json');
function loadServers() { try { return JSON.parse(fs.readFileSync(SERVERS_FILE, 'utf8')); } catch (_) { return []; } }
function saveServers(list) { fs.mkdirSync(GAME_DIR, { recursive: true }); fs.writeFileSync(SERVERS_FILE, JSON.stringify(list, null, 2)); }
ipcMain.handle('get-servers', () => loadServers());
ipcMain.handle('save-server', (_, { id, name, address, port }) => {
  const list = loadServers();
  const idx = list.findIndex(s => s.id === id);
  const entry = { id: id || Date.now().toString(36), name, address, port: parseInt(port) || 25565, addedAt: new Date().toISOString() };
  if (idx >= 0) list[idx] = entry; else list.push(entry);
  saveServers(list);
  return { success: true, server: entry };
});
ipcMain.handle('delete-server', (_, id) => { saveServers(loadServers().filter(s => s.id !== id)); return { success: true }; });
const PERF_MODS = {
  sodium: { slug: 'sodium', name: 'Sodium', desc: 'Modern rendering engine — massive FPS boost' },
  iris: { slug: 'iris', name: 'Iris Shaders', desc: 'Shader support for Fabric + Sodium' },
  lithium: { slug: 'lithium', name: 'Lithium', desc: 'Game logic optimisations, better TPS' },
  indium: { slug: 'indium', name: 'Indium', desc: 'Sodium addon for mod compatibility' },
  ferritecore: { slug: 'ferrite-core', name: 'FerriteCore', desc: 'Significantly reduces memory usage' },
  entityculling: { slug: 'entityculling', name: 'Entity Culling', desc: 'Skip rendering hidden entities' },
};
ipcMain.handle('get-perf-mods', () => PERF_MODS);
ipcMain.handle('install-perf-preset', async (event, { versionId, mcVersion, slugs }) => {
  const send = (msg, p) => event.sender.send('perf-progress', { msg, progress: p });
  const results = [];
  for (let i = 0; i < slugs.length; i++) {
    const slug = slugs[i], info = PERF_MODS[slug];
    send(`Finding ${info?.name || slug}…`, i / slugs.length);
    try {
      const params = new URLSearchParams({ game_versions: JSON.stringify([mcVersion]), loaders: JSON.stringify(['fabric']), limit: 1 });
      const versions = await fetchJSON(`${MODRINTH_BASE}/project/${slug}/version?${params}`, { headers: MR_HEADERS });
      if (!versions?.length) { results.push({ slug, error: 'No compatible version found' }); continue; }
      const file = versions[0].files.find(f => f.primary) || versions[0].files[0];
      if (!file) { results.push({ slug, error: 'No file' }); continue; }
      const dest = path.join(modsDir(versionId), file.filename);
      if (!fs.existsSync(dest)) await downloadFile(file.url, dest, () => { });
      results.push({ slug, success: true, fileName: file.filename });
    } catch (e) { results.push({ slug, error: e.message }); }
  }
  send('Done!', 1);
  return results;
});
const CRASH_PATTERNS = [
  { re: /OutOfMemoryError/i, title: 'Out of Memory', fix: 'Increase RAM in Settings. Try at least 3–4 GB for modded.' },
  { re: /Failed to locate library.*lwjgl/i, title: 'Missing LWJGL Natives', fix: 'Delete the natives folder for this version and relaunch.' },
  { re: /Unable to load texture.*missing/i, title: 'Missing Texture', fix: 'A mod or resource pack references a missing texture. Check compatibility.' },
  { re: /incompatible_mods|ModResolution/i, title: 'Mod Conflict', fix: 'Two or more mods are incompatible. Check version mismatches.' },
  { re: /UnsatisfiedLinkError/i, title: 'Native Library Error', fix: 'A native library failed to load. Delete natives folder and relaunch.' },
  { re: /ClassNotFoundException/i, title: 'Missing Class', fix: 'A mod dependency is missing. Install Fabric API and check mod requirements.' },
  { re: /Could not find.*fabric.mod.json/i, title: 'Missing Fabric API', fix: 'Install Fabric API — most mods require it.' },
  { re: /AccessDeniedException/i, title: 'Access Denied', fix: 'Another program has locked a file. Close other Minecraft instances.' },
  { re: /StackOverflowError/i, title: 'Stack Overflow', fix: 'Likely a recursive mod bug. Check and update your mods.' },
  { re: /GLFW error.*65544|Unable to create window/i, title: 'Window Creation Failed', fix: 'Graphics driver issue. Update your GPU drivers.' },
  { re: /SSL.*certificate|PKIX/i, title: 'SSL Certificate Error', fix: 'Antivirus or firewall is blocking connections. Add Justice Launcher to exceptions.' },
  { re: /Exit code: 1$|Exit code: -1$/m, title: 'Abnormal Exit', fix: 'Game crashed. Check the full console output for the root exception.' },
];
ipcMain.handle('analyse-crash', (_, versionId) => {
  try {
    const logPath = path.join(instanceDir(versionId), 'logs', 'latest.log');
    const crashDir = path.join(instanceDir(versionId), 'crash-reports');
    let logText = '';
    if (fs.existsSync(logPath)) logText = fs.readFileSync(logPath, 'utf8').slice(-80000);
    if (fs.existsSync(crashDir)) {
      const reports = fs.readdirSync(crashDir).filter(f => f.endsWith('.txt')).sort().reverse();
      if (reports.length) logText += '\n' + fs.readFileSync(path.join(crashDir, reports[0]), 'utf8').slice(-20000);
    }
    if (!logText) return { found: false };
    const matches = CRASH_PATTERNS.filter(p => p.re.test(logText)).map(p => ({ title: p.title, fix: p.fix }));
    const lastLines = logText.split('\n').filter(l => l.includes('Exception') || l.includes('FATAL') || l.includes('Error:')).slice(-6);
    return { found: true, matches, lastLines, logPath };
  } catch (e) { return { found: false, error: e.message }; }
});
ipcMain.handle('export-instance', async (event, { versionId, includeWorlds, includeScreenshots }) => {
  try {
    const { dialog } = require('electron');
    let profiles = {}; try { profiles = JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { }
    const profile = profiles[versionId];
    if (!profile) return { error: 'Version not found' };
    const saveName = (profile.customName || versionId).replace(/[^a-z0-9_\-]/gi, '_');
    const res = await dialog.showSaveDialog(mainWindow, { title: 'Export Instance', defaultPath: `${saveName}-justice.zip`, filters: [{ name: 'Justice Instance', extensions: ['zip'] }] });
    if (res.canceled) return { cancelled: true };
    const AdmZip = require('adm-zip');
    const zip = new AdmZip();
    event.sender.send('export-progress', { msg: 'Adding mods…', progress: .1 });
    const modsD = modsDir(versionId); if (fs.existsSync(modsD)) zip.addLocalFolder(modsD, 'mods');
    const shadD = shadersDir(versionId); if (fs.existsSync(shadD)) zip.addLocalFolder(shadD, 'shaderpacks');
    const confD = path.join(instanceDir(versionId), 'config'); if (fs.existsSync(confD)) zip.addLocalFolder(confD, 'config');
    const opts = path.join(instanceDir(versionId), 'options.txt'); if (fs.existsSync(opts)) zip.addLocalFile(opts, '');
    if (includeWorlds) { event.sender.send('export-progress', { msg: 'Adding worlds…', progress: .5 }); const sd = path.join(instanceDir(versionId), 'saves'); if (fs.existsSync(sd)) zip.addLocalFolder(sd, 'saves'); }
    if (includeScreenshots) { const ssd = path.join(instanceDir(versionId), 'screenshots'); if (fs.existsSync(ssd)) zip.addLocalFolder(ssd, 'screenshots'); }
    zip.addFile('justice-instance.json', Buffer.from(JSON.stringify({ ...profile, exportedAt: new Date().toISOString() }, null, 2)));
    event.sender.send('export-progress', { msg: 'Writing zip…', progress: .9 });
    zip.writeZip(res.filePath);
    event.sender.send('export-progress', { msg: 'Done!', progress: 1 });
    return { success: true, file: res.filePath };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('import-instance', async (event) => {
  try {
    const { dialog } = require('electron');
    const pick = await dialog.showOpenDialog(mainWindow, { title: 'Import Instance', filters: [{ name: 'Justice Instance', extensions: ['zip'] }], properties: ['openFile'] });
    if (pick.canceled) return { cancelled: true };
    const AdmZip = require('adm-zip');
    const zip = new AdmZip(pick.filePaths[0]);
    const manifestEntry = zip.getEntry('justice-instance.json');
    if (!manifestEntry) return { error: 'Not a valid Nova instance file' };
    const manifest = JSON.parse(manifestEntry.getData().toString('utf8'));
    const versionId = manifest.id; if (!versionId) return { error: 'Invalid manifest' };
    event.sender.send('import-progress', { msg: 'Extracting…', progress: .2 });
    for (const entry of zip.getEntries()) {
      if (entry.entryName === 'justice-instance.json' || entry.isDirectory) continue;
      const dest = path.join(instanceDir(versionId), entry.entryName);
      fs.mkdirSync(path.dirname(dest), { recursive: true });
      fs.writeFileSync(dest, entry.getData());
    }
    let profiles = {}; try { profiles = JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { }
    profiles[versionId] = { ...manifest, installedAt: manifest.installedAt || new Date().toISOString() };
    fs.writeFileSync(path.join(GAME_DIR, 'profiles.json'), JSON.stringify(profiles, null, 2));
    event.sender.send('import-progress', { msg: 'Done!', progress: 1 });
    return { success: true, versionId, customName: manifest.customName };
  } catch (e) { return { error: e.message }; }
});
async function ensureNovaPresenceMod(versionId, logFn) {
  const log = logFn || console.log;
  const modsDirPath = modsDir(versionId);
  if (!fs.existsSync(modsDirPath)) fs.mkdirSync(modsDirPath, { recursive: true });
  const existing = fs.readdirSync(modsDirPath)
    .find(f => f.startsWith('nova-presence') && f.endsWith('.jar'));
  if (existing) {
    log(`[NovaPresence] Already present: ${existing}\n`);
    return;
  }
  const candidates = [
    path.join(process.resourcesPath || '', 'nova-presence.jar'),
    path.join(__dirname, 'bundled-mods', 'nova-presence.jar'),
    path.join(process.cwd(), 'bundled-mods', 'nova-presence.jar'),
  ];
  log(`[NovaPresence] Looking for nova-presence.jar...\n`);
  for (const c of candidates) log(`[NovaPresence]   checking: ${c} → ${fs.existsSync(c) ? 'FOUND' : 'not found'}\n`);
  const src = candidates.find(p => fs.existsSync(p));
  if (!src) {
    log(`[NovaPresence] JAR not found in bundled-mods — build the mod and place nova-presence.jar in the bundled-mods folder.\n`);
    return;
  }
  fs.copyFileSync(src, path.join(modsDirPath, 'nova-presence.jar'));
  log(`[NovaPresence] Installed nova-presence.jar → ${modsDirPath}\n`);
}
const JUSTICE_MODS = [
  { file: 'justice-1.21-1.21.1.jar',    versions: ['1.21', '1.21.1'] },
  { file: 'justice-1.21.2-1.21.4.jar',  versions: ['1.21.2', '1.21.3', '1.21.4'] },
  { file: 'justice-1.21.6-1.21.8.jar',  versions: ['1.21.6', '1.21.7', '1.21.8'] },
  { file: 'justice-1.21.9-1.21.10.jar', versions: ['1.21.9', '1.21.10'] },
  { file: 'justice-1.21.11.jar',         versions: ['1.21.11'] },
];
async function ensureJusticeMod(versionId, mcVersion, logFn) {
  const log = logFn || console.log;
  const modsDirPath = modsDir(versionId);
  if (!fs.existsSync(modsDirPath)) fs.mkdirSync(modsDirPath, { recursive: true });
  log(`[JusticeMod] MC version: ${mcVersion}\n`);

  const matched = JUSTICE_MODS.find(m => m.versions.includes(mcVersion));
  if (!matched) {
    log(`[JusticeMod] No Justice Mod for MC ${mcVersion}, skipping.\n`);
    return;
  }

  // Remove any mismatched justice mod JARs
  const allJusticeFiles = JUSTICE_MODS.map(m => m.file);
  for (const oldFile of allJusticeFiles) {
    if (oldFile !== matched.file) {
      const oldPath = path.join(modsDirPath, oldFile);
      if (fs.existsSync(oldPath)) {
        try { fs.unlinkSync(oldPath); log(`[JusticeMod] Removed outdated: ${oldFile}\n`); } catch (_) { }
      }
    }
  }
  // Remove legacy justice-mod.jar
  const legacyPath = path.join(modsDirPath, 'justice-mod.jar');
  if (fs.existsSync(legacyPath)) {
    try { fs.unlinkSync(legacyPath); log('[JusticeMod] Removed legacy justice-mod.jar\n'); } catch (_) { }
  }

  // Already present?
  const destPath = path.join(modsDirPath, matched.file);
  if (fs.existsSync(destPath)) {
    log(`[JusticeMod] ${matched.file} already installed.\n`);
    return;
  }

  // Try bundled first
  const candidates = [
    path.join(process.resourcesPath || '', 'bundled-mods', 'justice', matched.file),
    path.join(__dirname, 'bundled-mods', 'justice', matched.file),
    path.join(process.cwd(), 'bundled-mods', 'justice', matched.file),
  ];
  log(`[JusticeMod] Looking for bundled ${matched.file}...\n`);
  for (const c of candidates) log(`[JusticeMod]   ${c} → ${fs.existsSync(c) ? 'FOUND' : 'not found'}\n`);
  const src = candidates.find(p => {
    try { return fs.existsSync(p) && fs.statSync(p).size > 0; } catch (_) { return false; }
  });
  if (src) {
    fs.copyFileSync(src, destPath);
    log(`[JusticeMod] Installed ${matched.file} from bundled mods.\n`);
    return;
  }

  // Fallback: download from server
  const MOD_URLS = {
    'justice-1.21-1.21.1.jar':    'https://justiceclient.org/mods/justice-1.21-1.21.1.jar',
    'justice-1.21.2-1.21.4.jar':  'https://justiceclient.org/mods/justice-1.21.2-1.21.4.jar',
    'justice-1.21.6-1.21.8.jar':  'https://justiceclient.org/mods/justice-1.21.6-1.21.8.jar',
    'justice-1.21.9-1.21.10.jar': 'https://justiceclient.org/mods/justice-1.21.9-1.21.10.jar',
    'justice-1.21.11.jar':         'https://justiceclient.org/mods/justice-1.21.11.jar',
  };
  const dlUrl = MOD_URLS[matched.file];
  if (!dlUrl) { log(`[JusticeMod] No download URL for ${matched.file}.\n`); return; }
  log(`[JusticeMod] Bundled not found, downloading ${matched.file}...\n`);
  return new Promise((resolve) => {
    const download = (url, redirects = 0) => {
      if (redirects > 5) { log('[JusticeMod] Too many redirects.\n'); resolve(); return; }
      const proto = url.startsWith('https') ? https : http;
      proto.get(url, { headers: { 'User-Agent': 'JusticeLauncher/1.0' } }, (res) => {
        if (res.statusCode === 301 || res.statusCode === 302) {
          download(res.headers.location, redirects + 1); return;
        }
        if (res.statusCode !== 200) {
          log(`[JusticeMod] Download failed: HTTP ${res.statusCode}\n`); resolve(); return;
        }
        const tmp = destPath + '.tmp';
        const file = fs.createWriteStream(tmp);
        res.pipe(file);
        file.on('finish', () => {
          file.close(() => {
            fs.renameSync(tmp, destPath);
            log(`[JusticeMod] Downloaded ${matched.file} successfully.\n`);
            resolve();
          });
        });
        file.on('error', (err) => {
          try { fs.unlinkSync(tmp); } catch (_) { }
          log(`[JusticeMod] Download error: ${err.message}\n`);
          resolve();
        });
      }).on('error', (err) => { log(`[JusticeMod] Network error: ${err.message}\n`); resolve(); });
    };
    download(dlUrl);
  });
}
const SERVERS_DIR = path.join(GAME_DIR, 'servers');
let _serverProc = null;
let _serverPort = 25565;
let _serverPlayers = [];
function serverDir(versionId) {
  const d = path.join(SERVERS_DIR, versionId);
  if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true });
  return d;
}
async function getServerJarUrl(mcVersion) {
  const manifest = await fetchJSON('https://launchermeta.mojang.com/mc/game/version_manifest_v2.json');
  const entry = manifest.versions.find(v => v.id === mcVersion);
  if (!entry) throw new Error(`Version ${mcVersion} not in manifest`);
  const vJson = await fetchJSON(entry.url);
  const dl = vJson.downloads?.server;
  if (!dl?.url) throw new Error(`No server download for ${mcVersion}`);
  return { url: dl.url, sha1: dl.sha1 };
}
async function ensureServerJar(mcVersion, onStep) {
  const dir = serverDir(mcVersion);
  const dest = path.join(dir, 'server.jar');
  if (fs.existsSync(dest)) return dest;
  onStep(`Downloading server.jar for ${mcVersion}…`);
  const { url } = await getServerJarUrl(mcVersion);
  await downloadFile(url, dest);
  return dest;
}
ipcMain.handle('start-server', async (event, { versionId, worldName, port, ram, maxPlayers }) => {
  const send = (ch, data) => event.sender.send(ch, data);
  const log = msg => send('server-log', msg);
  if (_serverProc) return { error: 'A server is already running. Stop it first.' };
  try {
    let profiles = {};
    try { profiles = JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { }
    const prof = profiles[versionId];
    const mcVer = prof?.mcVersion || versionId;
    let javaPath = await findJava();
    if (!javaPath) return { error: 'Java not found. Launch a game first to auto-install Java.' };
    const jarPath = await ensureServerJar(mcVer, msg => log(msg + '\n'));
    const runDir = path.join(serverDir(mcVer), 'run');
    if (!fs.existsSync(runDir)) fs.mkdirSync(runDir, { recursive: true });
    const propsPath = path.join(runDir, 'server.properties');
    const props = [
      `level-name=${worldName}`,
      `server-port=${port || 25565}`,
      `max-players=${maxPlayers || 10}`,
      `online-mode=false`,
      `enable-rcon=false`,
      `view-distance=10`,
      `motd=Justice Launcher - ${worldName}`,
    ].join('\n') + '\n';
    fs.writeFileSync(propsPath, props);
    fs.writeFileSync(path.join(runDir, 'eula.txt'), 'eula=true\n');
    const srcWorld = path.join(instanceDir(versionId), 'saves', worldName);
    const destWorld = path.join(runDir, worldName);
    if (!fs.existsSync(srcWorld)) return { error: `World "${worldName}" not found in saves` };
    if (fs.existsSync(destWorld)) {
      try { fs.rmSync(destWorld, { recursive: true, force: true }); } catch (_) { }
    }
    fs.cpSync(srcWorld, destWorld, { recursive: true });
    const ramMb = (ram || 2) * 1024;
    const args = [`-Xms512m`, `-Xmx${ramMb}m`, `-jar`, jarPath, `--nogui`];
    log(`\n══════════════════════════════\n Justice Server — ${mcVer} — ${worldName}\n══════════════════════════════\n`);
    log(`Java:    ${javaPath}\n`);
    log(`Server:  ${jarPath}\n`);
    log(`RAM:     ${ram}GB\n`);
    log(`Port:    ${port}\n`);
    log(`World:   ${worldName}\n\n`);
    _serverPort = port || 25565;
    _serverPlayers = [];
    _serverProc = spawn(javaPath, args, { cwd: runDir, stdio: ['pipe', 'pipe', 'pipe'] });
    send('server-status', { running: true, port: _serverPort, players: _serverPlayers });
    _serverProc.stdout.on('data', d => {
      const text = d.toString();
      log(text);
      const joined = text.match(/(\w+) joined the game/);
      const left = text.match(/(\w+) left the game/);
      if (joined) { _serverPlayers.push(joined[1]); send('server-status', { running: true, port: _serverPort, players: [..._serverPlayers] }); }
      if (left) { _serverPlayers = _serverPlayers.filter(p => p !== left[1]); send('server-status', { running: true, port: _serverPort, players: [..._serverPlayers] }); }
    });
    _serverProc.stderr.on('data', d => log(d.toString()));
    _serverProc.on('error', err => {
      log(`\nServer error: ${err.message}\n`);
      _serverProc = null;
      send('server-status', { running: false, error: err.message, players: [] });
    });
    _serverProc.on('close', code => {
      log(`\n════════════════════════════\nServer stopped (exit ${code})\n`);
      _serverProc = null;
      _serverPlayers = [];
      send('server-status', { running: false, players: [] });
    });
    return { success: true };
  } catch (e) {
    _serverProc = null;
    return { error: e.message };
  }
});
ipcMain.handle('stop-server', async () => {
  if (!_serverProc) return { error: 'No server running' };
  try {
    _serverProc.stdin.write('stop\n');
    setTimeout(() => { if (_serverProc) { _serverProc.kill(); _serverProc = null; } }, 8000);
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('server-cmd', (_, cmd) => {
  if (!_serverProc) return { error: 'Server not running' };
  try { _serverProc.stdin.write(cmd + '\n'); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
function rpDir(versionId) {
  const d = path.join(instanceDir(versionId), 'resourcepacks');
  if (!fs.existsSync(d)) fs.mkdirSync(d, { recursive: true });
  return d;
}
ipcMain.handle('get-resource-packs', (_, versionId) => {
  try {
    return fs.readdirSync(rpDir(versionId))
      .filter(f => f.endsWith('.zip') || f.endsWith('.zip.disabled'))
      .map(f => ({ name: f, size: fs.statSync(path.join(rpDir(versionId), f)).size }));
  } catch (_) { return []; }
});
ipcMain.handle('pick-rp-file', async (_, versionId) => {
  const { dialog } = require('electron');
  const res = await dialog.showOpenDialog({ properties: ['openFile'], filters: [{ name: 'Resource Packs', extensions: ['zip'] }] });
  if (res.canceled || !res.filePaths.length) return { cancelled: true };
  try {
    const src = res.filePaths[0];
    fs.copyFileSync(src, path.join(rpDir(versionId), path.basename(src)));
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('toggle-rp', (_, { versionId, fileName }) => {
  try {
    const dir = rpDir(versionId);
    if (fileName.endsWith('.disabled')) {
      fs.renameSync(path.join(dir, fileName), path.join(dir, fileName.replace('.disabled', '')));
    } else {
      fs.renameSync(path.join(dir, fileName), path.join(dir, fileName + '.disabled'));
    }
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('delete-rp', (_, { versionId, fileName }) => {
  try { fs.unlinkSync(path.join(rpDir(versionId), fileName)); return { success: true }; }
  catch (e) { return { error: e.message }; }
});
ipcMain.handle('mp-search', async (_, { query, loader, mcVersion, offset }) => {
  try {
    const facets = [['project_type:modpack']];
    if (mcVersion) facets.push([`versions:${mcVersion}`]);
    if (loader) facets.push([`categories:${loader}`]);
    const params = new URLSearchParams({ query: query || '', limit: 20, offset: offset || 0, index: 'relevance', facets: JSON.stringify(facets) });
    return await mrGet(`/search?${params}`);
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('install-modpack', async (event, { url, fileName, packName }) => {
  const send = (ch, d) => event.sender.send(ch, d);
  try {
    const AdmZip = require('adm-zip');
    const tmpDir = path.join(GAME_DIR, 'tmp-modpack');
    if (!fs.existsSync(tmpDir)) fs.mkdirSync(tmpDir, { recursive: true });
    const zipPath = path.join(tmpDir, fileName);
    send('mp-dl-progress', { done: 0, total: 0, step: 'Downloading modpack…' });
    await new Promise((resolve, reject) => {
      const get = u => https.get(u, { agent: httpAgent, headers: { 'User-Agent': 'JusticeLauncher/1.0' } }, res => {
        if ([301, 302, 303, 307, 308].includes(res.statusCode)) return get(res.headers.location);
        if (res.statusCode !== 200) { res.resume(); return reject(new Error(`HTTP ${res.statusCode}`)); }
        const total = parseInt(res.headers['content-length'] || '0');
        let done = 0;
        const file = fs.createWriteStream(zipPath);
        res.on('data', c => { done += c.length; send('mp-dl-progress', { done, total, step: 'Downloading modpack…' }); });
        res.pipe(file);
        file.on('finish', () => { file.close(); resolve(); });
        file.on('error', reject);
      }).on('error', reject);
      get(url);
    });
    send('mp-dl-progress', { done: 0, total: 0, step: 'Reading modpack…' });
    const zip = new AdmZip(zipPath);
    const index = JSON.parse(zip.readAsText('modrinth.index.json'));
    const mcVersion = index.dependencies?.minecraft || '';
    const fabricVersion = index.dependencies?.['fabric-loader'] || '';
    const forgeVersion = index.dependencies?.forge || '';
    const packId = (packName || index.name || 'modpack').replace(/[^a-zA-Z0-9_\-]/g, '_').toLowerCase();
    send('mp-dl-progress', { done: 0, total: 0, step: `Installing Minecraft ${mcVersion}…` });
    if (mcVersion) {
      const existing = (() => { try { return JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { return {}; } })();
      if (!existing[mcVersion] && !existing[`fabric-loader-${fabricVersion}-${mcVersion}`]) {
        await installVanilla(mcVersion, () => { });
      }
    }
    let profileId = mcVersion;
    if (fabricVersion && mcVersion) {
      profileId = `fabric-loader-${fabricVersion}-${mcVersion}`;
      const existing = (() => { try { return JSON.parse(fs.readFileSync(path.join(GAME_DIR, 'profiles.json'), 'utf8')); } catch (_) { return {}; } })();
      if (!existing[profileId]) {
        send('mp-dl-progress', { done: 0, total: 0, step: `Installing Fabric ${fabricVersion}…` });
        const fabricProfile = await fetchJSON(`https://meta.fabricmc.net/v2/versions/loader/${mcVersion}/${fabricVersion}/profile/json`);
        const fabricDir = path.join(VERSIONS_DIR, profileId);
        if (!fs.existsSync(fabricDir)) fs.mkdirSync(fabricDir, { recursive: true });
        fs.writeFileSync(path.join(fabricDir, `${profileId}.json`), JSON.stringify(fabricProfile, null, 2));
        saveProfile(profileId, 'fabric', mcVersion, fabricVersion, packName);
      }
    }
    const files = (index.files || []).filter(f => f.downloads?.length);
    for (let i = 0; i < files.length; i++) {
      const f = files[i];
      const dest = path.join(instanceDir(profileId), f.path.replace(/^(overrides\/)?/, ''));
      fs.mkdirSync(path.dirname(dest), { recursive: true });
      send('mp-dl-progress', { done: i + 1, total: files.length, step: `Downloading mods (${i + 1}/${files.length})…` });
      await downloadFile(f.downloads[0], dest);
    }
    zip.getEntries().forEach(entry => {
      if (entry.entryName.startsWith('overrides/') && !entry.isDirectory) {
        const rel = entry.entryName.replace(/^overrides\//, '');
        const dest = path.join(instanceDir(profileId), rel);
        fs.mkdirSync(path.dirname(dest), { recursive: true });
        fs.writeFileSync(dest, entry.getData());
      }
    });
    try { fs.rmSync(tmpDir, { recursive: true, force: true }); } catch (_) { }
    return { success: true, profileId };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('save-instance-notes', (_, { versionId, notes }) => {
  try {
    const p = path.join(GAME_DIR, 'profiles.json');
    let profiles = {};
    try { profiles = JSON.parse(fs.readFileSync(p, 'utf8')); } catch (_) { }
    if (!profiles[versionId]) return { error: 'Version not found' };
    profiles[versionId].notes = notes || null;
    fs.writeFileSync(p, JSON.stringify(profiles, null, 2));
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
let discordRpc = null;
let rpcStartTime = Date.now();
const DISCORD_CLIENT_ID = '1484766794827698277';

async function initDiscordRPC() {
  try {
    const { Client } = require('discord-rpc');
    discordRpc = new Client({ transport: 'ipc' });
    discordRpc.on('ready', () => {
      setDiscordPresence('launcher');
    });
    await discordRpc.login({ clientId: DISCORD_CLIENT_ID });
  } catch (_) {
    discordRpc = null;
  }
}

const RPC_STATES = {
  launcher: {
    details: 'Justice Launcher',
    state: 'Browsing the launcher',
    largeImageKey: 'justice_logo',
    largeImageText: 'Justice Launcher',
    smallImageKey: null,
    smallImageText: null,
  },
  friends: {
    details: 'Justice Launcher',
    state: 'Chatting with friends',
    largeImageKey: 'justice_logo',
    largeImageText: 'Justice Launcher',
    smallImageKey: 'chat_icon',
    smallImageText: 'Friends & Chat',
  },
  browsing_mods: {
    details: 'Justice Launcher',
    state: 'Browsing mods on Modrinth',
    largeImageKey: 'justice_logo',
    largeImageText: 'Justice Launcher',
    smallImageKey: 'modrinth_icon',
    smallImageText: 'Modrinth',
  },
  settings: {
    details: 'Justice Launcher',
    state: 'Tweaking settings',
    largeImageKey: 'justice_logo',
    largeImageText: 'Justice Launcher',
    smallImageKey: null,
    smallImageText: null,
  },
};

function setDiscordPresence(stateKey, extra = {}) {
  if (!discordRpc) return;
  const base = RPC_STATES[stateKey] || RPC_STATES.launcher;
  try {
    const activity = {
      details: extra.details || base.details,
      state: extra.state || base.state,
      startTimestamp: rpcStartTime,
      largeImageKey: extra.largeImageKey || base.largeImageKey || 'justice_logo',
      largeImageText: extra.largeImageText || base.largeImageText || 'Justice Launcher',
      buttons: [
        { label: 'Get Justice Launcher', url: 'https://justiceclient.org' }
      ],
    };
    if (base.smallImageKey || extra.smallImageKey) {
      activity.smallImageKey = extra.smallImageKey || base.smallImageKey;
      activity.smallImageText = extra.smallImageText || base.smallImageText;
    }
    discordRpc.setActivity(activity);
  } catch (_) { }
}

function destroyDiscordRPC() {
  if (discordRpc) { try { discordRpc.destroy(); } catch (_) { } discordRpc = null; }
}

ipcMain.on('discord-presence', (_, data) => {
  if (!data) return;
  if (data.preset) {
    setDiscordPresence(data.preset, data.extra || {});
  } else {
    setDiscordPresence('launcher', data);
  }
});
const CURRENT_VERSION = '1.0.5';
const VERSION_URL = 'https://justiceclient.org/downloads/version.txt';
ipcMain.handle('check-for-update', async () => {
  return new Promise((resolve) => {
    const proto = VERSION_URL.startsWith('https') ? https : http;
    proto.get(VERSION_URL, { headers: { 'User-Agent': 'JusticeLauncher/' + CURRENT_VERSION } }, (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        const latest = data.trim();
        resolve({ current: CURRENT_VERSION, latest, hasUpdate: latest !== CURRENT_VERSION && latest.length > 0 });
      });
    }).on('error', () => resolve({ current: CURRENT_VERSION, latest: CURRENT_VERSION, hasUpdate: false }));
  });
});
ipcMain.handle('download-update', async (event) => {
  const UPDATE_URL = 'https://justiceclient.org/downloads/justice-launcher-setup.exe';
  const { shell } = require('electron');
  shell.openExternal(UPDATE_URL);
  return { ok: true };
});
let overlayWindow = null;
let overlayToken = null;
let overlayVisible = false;
const isMac = process.platform === 'darwin';
const isWin = process.platform === 'win32';
function createOverlay() {
  if (overlayWindow && !overlayWindow.isDestroyed()) return;
  const display = screen.getPrimaryDisplay();
  const { width, height } = display.bounds;
  overlayWindow = new BrowserWindow({
    width, height,
    x: display.bounds.x,
    y: display.bounds.y,
    transparent: true,
    frame: false,
    alwaysOnTop: true,
    skipTaskbar: true,
    focusable: false,
    resizable: false,
    movable: false,
    hasShadow: false,
    titleBarStyle: 'hidden',
    ...(isMac ? {
      type: 'panel',
      visibleOnAllWorkspaces: true,
      fullscreenable: false,
    } : {}),
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
      devTools: false,
    },
  });
  overlayWindow.setIgnoreMouseEvents(true, { forward: true });
  overlayWindow.loadFile(path.join(__dirname, 'src', 'overlay.html'));
  overlayWindow.setAlwaysOnTop(true, 'screen-saver', 1);
  if (isMac) {
    overlayWindow.setVisibleOnAllWorkspaces(true, { visibleOnFullScreen: true });
  }
  overlayWindow.showInactive();
}
function destroyOverlay() {
  if (overlayWindow && !overlayWindow.isDestroyed()) {
    overlayWindow.close();
    overlayWindow = null;
  }
  try { globalShortcut.unregister('F9'); } catch { }
}
function toggleOverlay() {
  if (!overlayWindow || overlayWindow.isDestroyed()) return;
  overlayVisible = !overlayVisible;
  overlayWindow.webContents.send('overlay-toggle');
}
function registerOverlayShortcut() {
  try { globalShortcut.unregister('F9'); } catch { }
  const ok = globalShortcut.register('F9', toggleOverlay);
  if (!ok) console.log('[Overlay] Could not register F9 — already in use');
}
ipcMain.on('overlay-enable-mouse', () => {
  if (!overlayWindow || overlayWindow.isDestroyed()) return;
  overlayWindow.setIgnoreMouseEvents(false);
  overlayWindow.setFocusable(true);
  setTimeout(() => {
    if (overlayWindow && !overlayWindow.isDestroyed()) overlayWindow.focus();
  }, 50);
});
ipcMain.on('overlay-disable-mouse', () => {
  if (!overlayWindow || overlayWindow.isDestroyed()) return;
  overlayWindow.setIgnoreMouseEvents(true, { forward: true });
  overlayWindow.setFocusable(false);
});
async function overlayFetch(endpoint, token, options = {}) {
  const base = 'https://justiceclient.org';
  return new Promise((resolve) => {
    const urlObj = new URL(base + endpoint);
    const reqOptions = {
      hostname: urlObj.hostname,
      path: urlObj.pathname + urlObj.search,
      method: options.method || 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token,
      },
    };
    const req = https.request(reqOptions, (res) => {
      let data = '';
      res.on('data', c => data += c);
      res.on('end', () => { try { resolve(JSON.parse(data)); } catch { resolve({}); } });
    });
    req.on('error', () => resolve({}));
    if (options.body) req.write(options.body);
    req.end();
  });
}
ipcMain.handle('overlay-fetch-friends', async () => {
  if (!overlayToken) return { friends: [] };
  return overlayFetch('/api/friends.php?action=list', overlayToken);
});
ipcMain.handle('overlay-fetch-messages', async (_, { userId, limit = 60, after = 0 }) => {
  if (!overlayToken) return { messages: [] };
  const qs = after ? `&after=${after}` : '';
  return overlayFetch(`/api/messages.php?userId=${userId}&limit=${limit}${qs}`, overlayToken);
});
ipcMain.handle('overlay-send-message', async (_, { userId, content }) => {
  if (!overlayToken) return { error: 'Not logged in' };
  return overlayFetch(`/api/messages.php?userId=${userId}`, overlayToken, {
    method: 'POST',
    body: JSON.stringify({ content }),
  });
});
ipcMain.handle('overlay-check-typing', async (_, { userId }) => {
  if (!overlayToken) return { typing: false };
  return overlayFetch(`/api/messages.php?action=typing&userId=${userId}`, overlayToken);
});
ipcMain.handle('overlay-typing', async (_, { userId }) => {
  if (!overlayToken) return {};
  return overlayFetch(`/api/messages.php?action=typing&userId=${userId}`, overlayToken, {
    method: 'POST', body: JSON.stringify({}),
  });
});
ipcMain.on('overlay-game-start', (_, token) => {
  overlayToken = token;
  createOverlay();
  registerOverlayShortcut();
});
ipcMain.on('overlay-game-stop', () => {
  destroyOverlay();
  overlayToken = null;
  overlayVisible = false;
});
// Forward chat notifications to overlay
ipcMain.on('overlay-notify', (_, data) => {
  if (overlayWindow && !overlayWindow.isDestroyed()) {
    overlayWindow.webContents.send('overlay-chat-notification', data);
  }
});

ipcMain.handle('open-file-dialog', async (_, opts) => {
  const { dialog } = require('electron');
  const result = await dialog.showOpenDialog(mainWindow, {
    title: opts.title || 'Select File',
    filters: opts.filters || [{ name: 'All Files', extensions: ['*'] }],
    defaultPath: opts.defaultPath || require('os').homedir(),
    properties: ['openFile']
  });
  return result.canceled ? [] : result.filePaths;
});

ipcMain.handle('read-file-base64', async (_, filePath) => {
  try {
    const data = fs.readFileSync(filePath);
    const ext = path.extname(filePath).toLowerCase().replace('.', '');
    const mime = ext === 'jpg' ? 'jpeg' : ext;
    return 'data:image/' + mime + ';base64,' + data.toString('base64');
  } catch { return null; }
});

/* ═══ SKIN IMPORTS ═══════════════════════════════════════════════════════ */
ipcMain.handle('import-skin-default', async () => {
  try {
    // Skins are stored in .minecraft/assets/skins as PNG files
    const mcDirs = [];
    if (process.platform === 'win32') mcDirs.push(path.join(process.env.APPDATA || '', '.minecraft'));
    if (process.platform === 'darwin') mcDirs.push(path.join(os.homedir(), 'Library', 'Application Support', 'minecraft'));
    mcDirs.push(path.join(os.homedir(), '.minecraft'));

    let skinsDir = null;
    for (const d of mcDirs) {
      const sd = path.join(d, 'assets', 'skins');
      if (fs.existsSync(sd)) { skinsDir = sd; break; }
    }
    if (!skinsDir) return { error: 'Minecraft skins folder not found. Expected at .minecraft/assets/skins' };
    const skins = fs.readdirSync(skinsDir).filter(f => f.endsWith('.png'));
    if (!skins.length) return { error: 'No skin files found in .minecraft/assets/skins' };
    // Use the most recently modified skin
    const sorted = skins.map(f => ({ name: f, mtime: fs.statSync(path.join(skinsDir, f)).mtimeMs }))
      .sort((a, b) => b.mtime - a.mtime);
    const skinPath = path.join(skinsDir, sorted[0].name);
    const data = fs.readFileSync(skinPath);
    return { found: true, data: 'data:image/png;base64,' + data.toString('base64'), name: sorted[0].name };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('import-skin-lunar', async () => {
  try {
    // Lunar Client stores skins in saved_skins.json with texture URLs
    const savedSkinsPath = path.join(os.homedir(), '.lunarclient', 'settings', 'game', 'saved_skins.json');
    if (fs.existsSync(savedSkinsPath)) {
      const raw = fs.readFileSync(savedSkinsPath, 'utf8');
      const skinMap = JSON.parse(raw);
      const entries = Object.entries(skinMap);
      if (entries.length) {
        // Return all skins as a list for the user to choose from
        const skins = entries.map(([url, info]) => ({
          url, type: info.type || 'classic', name: info.name || 'Lunar Skin'
        }));
        // Download the first skin's texture
        const firstUrl = skins[0].url;
        const https = require('https');
        const imgData = await new Promise((resolve, reject) => {
          https.get(firstUrl, res => {
            const chunks = [];
            res.on('data', c => chunks.push(c));
            res.on('end', () => resolve(Buffer.concat(chunks)));
            res.on('error', reject);
          }).on('error', reject);
        });
        return {
          found: true,
          data: 'data:image/png;base64,' + imgData.toString('base64'),
          name: skins[0].name,
          variant: skins[0].type === 'slim' ? 'SLIM' : 'CLASSIC',
          allSkins: skins
        };
      }
    }
    // Fallback: check old skins folder
    const lunarDir = path.join(os.homedir(), '.lunarclient', 'skins');
    if (!fs.existsSync(lunarDir)) return { error: 'Lunar Client skins not found. Check if Lunar Client is installed.' };
    const skins = fs.readdirSync(lunarDir).filter(f => f.endsWith('.png'));
    if (!skins.length) return { error: 'No skins found in Lunar Client' };
    const skinPath = path.join(lunarDir, skins[0]);
    const data = fs.readFileSync(skinPath);
    return { found: true, data: 'data:image/png;base64,' + data.toString('base64'), name: skins[0] };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('import-skin-feather', async () => {
  try {
    // Feather uses the same .minecraft/assets/skins folder
    const mcDirs = [];
    if (process.platform === 'win32') mcDirs.push(path.join(process.env.APPDATA || '', '.minecraft'));
    if (process.platform === 'darwin') mcDirs.push(path.join(os.homedir(), 'Library', 'Application Support', 'minecraft'));
    mcDirs.push(path.join(os.homedir(), '.minecraft'));

    let skinsDir = null;
    for (const d of mcDirs) {
      const sd = path.join(d, 'assets', 'skins');
      if (fs.existsSync(sd)) { skinsDir = sd; break; }
    }
    if (!skinsDir) return { error: 'Skin assets folder not found at .minecraft/assets/skins' };
    const skins = fs.readdirSync(skinsDir).filter(f => f.endsWith('.png'));
    if (!skins.length) return { error: 'No skin files found' };
    const sorted = skins.map(f => ({ name: f, mtime: fs.statSync(path.join(skinsDir, f)).mtimeMs }))
      .sort((a, b) => b.mtime - a.mtime);
    const skinPath = path.join(skinsDir, sorted[0].name);
    const data = fs.readFileSync(skinPath);
    return { found: true, data: 'data:image/png;base64,' + data.toString('base64'), name: sorted[0].name };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('check-launcher-dir', (_, dirPath) => {
  return fs.existsSync(dirPath);
});

/* ═══ BEDROCK EDITION SUPPORT ═══════════════════════════════════════════ */
ipcMain.handle('check-bedrock-installed', async () => {
  if (process.platform !== 'win32') return { installed: false, reason: 'Bedrock Edition is only available on Windows' };
  try {
    const out = execSync('powershell -command "Get-AppxPackage -Name Microsoft.MinecraftUWP"', { encoding: 'utf8', timeout: 8000 });
    return { installed: out.trim().length > 0 };
  } catch (_) { return { installed: false }; }
});
ipcMain.handle('launch-bedrock', async () => {
  if (process.platform !== 'win32') return { error: 'Bedrock Edition is only available on Windows' };
  try {
    shell.openExternal('minecraft:');
    return { success: true };
  } catch (e) { return { error: e.message }; }
});
ipcMain.handle('create-bedrock-instance', (_, { name }) => {
  const id = 'bedrock-' + Date.now().toString(36);
  const profilesPath = path.join(GAME_DIR, 'profiles.json');
  let profiles = {};
  try { profiles = JSON.parse(fs.readFileSync(profilesPath, 'utf8')); } catch (_) {}
  profiles[id] = {
    id, customName: name || 'Bedrock Edition', edition: 'bedrock',
    type: 'bedrock', mcVersion: 'Latest', createdAt: new Date().toISOString()
  };
  fs.writeFileSync(profilesPath, JSON.stringify(profiles, null, 2));
  return { success: true, id };
});

/* ═══ LUNAR/FEATHER CLIENT DETECTION ═══════════════════════════════════ */
ipcMain.handle('detect-lunar-client', () => {
  const lunarDir = path.join(os.homedir(), '.lunarclient');
  return { installed: fs.existsSync(lunarDir), path: lunarDir };
});
ipcMain.handle('detect-feather-client', () => {
  const dirs = [
    path.join(os.homedir(), '.feather'),
    path.join(os.homedir(), 'AppData', 'Roaming', '.feather'),
  ];
  for (const d of dirs) { if (fs.existsSync(d)) return { installed: true, path: d }; }
  return { installed: false };
});
ipcMain.handle('get-lunar-mod-equivalents', () => {
  return [
    { lunar: 'Freelook',      modrinth: 'perspective-mod-redux', desc: 'Free camera rotation' },
    { lunar: 'Keystrokes',    modrinth: 'keys',                  desc: 'Show WASD/mouse input on screen' },
    { lunar: 'FPS Display',   modrinth: 'betterf3',              desc: 'Improved debug/FPS overlay' },
    { lunar: 'Zoom',          modrinth: 'ok-zoomer',             desc: 'Smooth camera zoom' },
    { lunar: 'Coordinates',   modrinth: 'coordinates-display',   desc: 'Show XYZ coords on screen' },
    { lunar: 'Minimap',       modrinth: 'xaeros-minimap',        desc: 'In-game minimap' },
    { lunar: 'Armor Status',  modrinth: 'armorstatus',           desc: 'Show armor durability' },
    { lunar: 'Potion Effects',modrinth: 'appleskin',             desc: 'Enhanced potion/food display' },
    { lunar: 'Scroll Hotbar', modrinth: 'item-scroller',         desc: 'Scroll through hotbar' },
    { lunar: 'Chat',          modrinth: 'chatpatches',           desc: 'Chat improvements & timestamps' },
  ];
});

/* ═══ NOTIFICATION SYSTEM ══════════════════════════════════════════════ */
ipcMain.handle('show-native-notification', (_, { title, body }) => {
  const { Notification } = require('electron');
  if (!Notification.isSupported()) return { error: 'Notifications not supported' };
  const iconPath = path.join(__dirname, 'assets', process.platform === 'win32' ? 'icon.ico' : 'icon.png');
  const notif = new Notification({
    title: title || 'Justice Launcher',
    body: body || '',
    icon: fs.existsSync(iconPath) ? iconPath : undefined,
    silent: false
  });
  notif.show();
  notif.on('click', () => { if (mainWindow) { mainWindow.show(); mainWindow.focus(); } });
  return { success: true };
});

app.whenReady().then(() => {
  initDiscordRPC(); ensureDirs(); createWindow();
});
app.on('window-all-closed', () => {
  destroyDiscordRPC();
  httpAgent.destroy();
  if (process.platform !== 'darwin') app.quit();
});
