<?php
// ============================================================
//  JUSTICE LAUNCHER — CONFIGURATION
//  Edit these values to match your cPanel database settings
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'justguye_mishka');      // e.g. myuser_justice
define('DB_USER',     'justguye_mcdevs');      // e.g. myuser_admin
define('DB_PASS',     'ha*VQ)A6c69@Zm5');
define('DB_CHARSET',  'utf8mb4');

// Site URL (no trailing slash)
define('SITE_URL',    'https://justiceclient.org');

// JWT secret — change this to a long random string
define('JWT_SECRET',  'GDGHDUISHGHUHSDGIUSDGIHUSDHFOSNDFOISDFNISDBF98saf6y9safy98safa9s8f');

// JWT expiry in seconds (30 days)
define('JWT_EXPIRY',  60 * 60 * 24 * 30);

// ── Download links ────────────────────────────────────────────────────────────
// Point these to wherever you host your installer files.
// Can be direct file paths on your server or external URLs (GitHub releases etc.)
define('DOWNLOAD_WINDOWS', SITE_URL . '/downloads/justice-launcher-setup.exe');
define('DOWNLOAD_MAC',     SITE_URL . '/downloads/justice-launcher.dmg');
define('DOWNLOAD_LINUX',   SITE_URL . '/downloads/justice-launcher.AppImage');
define('LAUNCHER_VERSION', '1.0.0');

// ── CORS — allowed origins for the API ───────────────────────────────────────
define('ALLOWED_ORIGIN', 'https://justiceclient.org'); // or e.g. 'https://yourdomain.com'
