<?php
if (file_exists(__DIR__ . '/includes/config.php')) require_once __DIR__ . '/includes/config.php';
$version  = defined('LAUNCHER_VERSION') ? LAUNCHER_VERSION : '1.0.0';
$dl_win   = defined('DOWNLOAD_WINDOWS') ? DOWNLOAD_WINDOWS : '#';
$dl_mac   = defined('DOWNLOAD_MAC')     ? DOWNLOAD_MAC     : '#';
$dl_linux = defined('DOWNLOAD_LINUX')   ? DOWNLOAD_LINUX   : '#';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice Client</title>
<meta name="description" content="Justice Launcher is a free, premium Minecraft launcher with 50,000+ mods, shader support, FPS optimization, and a built-in social layer. Better than Lunar. Free forever.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&family=Press+Start+2P&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#050309;
  --bg2:#080512;
  --bg3:#0c0918;
  --p:#7c3aed;
  --p2:#6d28d9;
  --pl:#a78bfa;
  --px:#c4b5fd;
  --pink:#e879f9;
  --green:#22c55e;
  --teal:#2dd4bf;
  --red:#f87171;
  --amber:#fbbf24;
  --w:#f0eeff;
  --w2:rgba(240,238,255,.58);
  --w3:rgba(240,238,255,.28);
  --w4:rgba(240,238,255,.07);
  --line:rgba(255,255,255,.05);
  --line2:rgba(139,92,246,.2);
  --display:'Bebas Neue',sans-serif;
  --f:'Outfit',system-ui,sans-serif;
  --m:'JetBrains Mono',monospace;
  --glow:0 0 60px rgba(124,58,237,.35),0 0 120px rgba(124,58,237,.15);
}

html{scroll-behavior:smooth}
body{
  font-family:var(--f);
  background:var(--bg);
  color:var(--w);
  overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
  position:relative;
}

body::before{
  content:'';
  position:fixed;inset:0;z-index:0;
  pointer-events:none;
  background-image:
    radial-gradient(2.5px 70px at 0px 235px,   rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 235px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 117.5px,   rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 252px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 252px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 126px,     rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 150px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 150px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 75px,      rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 253px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 253px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 126.5px,   rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 204px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 204px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 102px,     rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 134px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 134px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 67px,      rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 179px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 179px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 89.5px,    rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 299px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 299px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 149.5px,   rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 215px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 215px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 107.5px,   rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 281px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 281px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 140.5px,   rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 158px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 158px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 79px,      rgba(167,139,250,.18) 100%,transparent),
    radial-gradient(2.5px 70px at 0px 210px,    rgba(124,58,237,.12),transparent),
    radial-gradient(2.5px 70px at 300px 210px,  rgba(124,58,237,.12),transparent),
    radial-gradient(1px 1px at 150px 105px,     rgba(167,139,250,.18) 100%,transparent);
  background-size:
    300px 235px,300px 235px,300px 235px,
    300px 252px,300px 252px,300px 252px,
    300px 150px,300px 150px,300px 150px,
    300px 253px,300px 253px,300px 253px,
    300px 204px,300px 204px,300px 204px,
    300px 134px,300px 134px,300px 134px,
    300px 179px,300px 179px,300px 179px,
    300px 299px,300px 299px,300px 299px,
    300px 215px,300px 215px,300px 215px,
    300px 281px,300px 281px,300px 281px,
    300px 158px,300px 158px,300px 158px,
    300px 210px,300px 210px,300px 210px;
  animation:site-falling 150s linear infinite;
}
@keyframes site-falling{
  0%  {background-position:0px 0px,3px 0px,151.5px 117.5px,25px 0px,28px 0px,176.5px 126px,50px 0px,53px 0px,201.5px 75px,75px 0px,78px 0px,226.5px 126.5px,100px 0px,103px 0px,251.5px 102px,125px 0px,128px 0px,276.5px 67px,150px 0px,153px 0px,301.5px 89.5px,175px 0px,178px 0px,326.5px 149.5px,200px 0px,203px 0px,351.5px 107.5px,225px 0px,228px 0px,376.5px 140.5px,250px 0px,253px 0px,401.5px 79px,275px 0px,278px 0px,426.5px 105px}
  100%{background-position:0px 6000px,3px 6000px,151.5px 6117.5px,25px 10000px,28px 10000px,176.5px 10126px,50px 4000px,53px 4000px,201.5px 4075px,75px 14000px,78px 14000px,226.5px 14126.5px,100px 4500px,103px 4500px,251.5px 4602px,125px 7000px,128px 7000px,276.5px 7067px,150px 8000px,153px 8000px,301.5px 8089.5px,175px 11000px,178px 11000px,326.5px 11149.5px,200px 12000px,203px 12000px,351.5px 12107.5px,225px 15000px,228px 15000px,376.5px 15140.5px,250px 4200px,253px 4200px,401.5px 4279px,275px 5500px,278px 5500px,426.5px 5605px}
}

section,nav,footer,#versions{position:relative;z-index:1}
body::after{
  content:'';pointer-events:none;
  position:fixed;inset:0;z-index:9999;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  opacity:.032;mix-blend-mode:overlay;
}

nav{
  position:fixed;top:0;left:0;right:0;z-index:500;
  height:66px;display:flex;align-items:center;
  padding:0 56px;
  transition:background .3s,border-color .3s,backdrop-filter .3s;
  border-bottom:1px solid transparent;
}
nav.scrolled{
  background:rgba(8,6,15,.82);
  backdrop-filter:blur(20px) saturate(160%);
  border-color:rgba(139,92,246,.12);
}
nav::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(167,139,250,.25),transparent);
  opacity:0;transition:opacity .3s;
}
nav.scrolled::after{opacity:1}
.nav-logo{display:flex;align-items:center;gap:11px;text-decoration:none;font-family:var(--f);font-weight:800;font-size:16px;color:var(--w);letter-spacing:-.02em}
.nav-logo-sq{
  width:30px;height:30px;border-radius:8px;
  background:linear-gradient(135deg,#7c3aed,#a855f7);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 0 18px rgba(124,58,237,.55);
}
.nav-links{display:flex;gap:4px;align-items:center;margin-left:40px}
.nav-links a{font-size:14px;font-weight:500;color:var(--w3);text-decoration:none;padding:6px 14px;border-radius:8px;transition:all .15s;letter-spacing:-.01em}
.nav-links a:hover{color:var(--w);background:rgba(255,255,255,.05)}
.nav-end{display:flex;gap:8px;align-items:center;margin-left:auto}

.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:9px;border:none;font-family:var(--f);font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .16s;white-space:nowrap;letter-spacing:-.01em}
.btn-sm{padding:7px 15px;font-size:13px;border-radius:8px}
.btn-p{
  background:linear-gradient(135deg,#7c3aed,#9333ea);
  color:#fff;
  box-shadow:0 0 0 1px rgba(167,139,250,.25),0 4px 20px rgba(124,58,237,.4);
}
.btn-p:hover{box-shadow:0 0 0 1px rgba(167,139,250,.4),0 8px 32px rgba(124,58,237,.6);transform:translateY(-1px)}
.btn-ghost{background:rgba(124,58,237,.08);border:1px solid rgba(139,92,246,.28);color:var(--pl)}
.btn-ghost:hover{background:rgba(124,58,237,.16);color:var(--px);border-color:rgba(139,92,246,.5)}
.btn-outline{background:transparent;border:1px solid rgba(255,255,255,.12);color:var(--w2)}
.btn-outline:hover{border-color:rgba(139,92,246,.4);color:var(--w);background:rgba(124,58,237,.06)}

.user-pill{display:none;align-items:center;gap:7px;padding:5px 13px 5px 5px;border-radius:100px;background:rgba(124,58,237,.1);border:1px solid rgba(139,92,246,.25);font-size:13px;font-weight:600;color:var(--w)}
.user-pill.on{display:flex}
.uav{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#e879f9);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;box-shadow:0 0 10px rgba(124,58,237,.4)}
.udot{width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 8px #22c55e}
.usign{background:none;border:none;color:var(--w3);font-family:var(--f);font-size:12px;cursor:pointer;transition:color .12s}
.usign:hover{color:var(--red)}

@keyframes heroFadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes subtlePulse{0%,100%{opacity:.8;transform:scale(1)}50%{opacity:1;transform:scale(1.03)}}
.hero-fadeIn{animation:heroFadeInUp .8s ease-out forwards}
#hero{
  position:relative;isolation:isolate;height:100vh;overflow:hidden;
  background:var(--bg);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  text-align:center;
  padding:140px 40px 0;
}

.hero-bg{
  position:absolute;inset:0;z-index:-30;
  background-image:
    radial-gradient(80% 55% at 50% 52%, rgba(160,110,240,.30) 0%, rgba(109,40,217,.35) 27%, rgba(50,28,70,.30) 47%, rgba(30,28,55,.40) 60%, rgba(5,3,9,.92) 78%, var(--bg) 88%),
    radial-gradient(85% 60% at 14% 0%, rgba(170,140,230,.35) 0%, rgba(124,58,237,.30) 30%, rgba(40,18,50,0) 64%),
    radial-gradient(70% 50% at 86% 22%, rgba(139,92,246,.20) 0%, rgba(16,18,28,0) 55%),
    linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,0) 40%);
  background-color:var(--bg);
}

.hero-vignette{position:absolute;inset:0;z-index:-20;background:radial-gradient(130% 110% at 50% 0%,transparent 55%,rgba(5,3,9,.90))}

.hero-grid{
  pointer-events:none;position:absolute;inset:0;z-index:-10;
  mix-blend-mode:screen;opacity:.15;
  background-image:
    repeating-linear-gradient(90deg, rgba(255,255,255,.09) 0 1px, transparent 1px 96px),
    repeating-linear-gradient(90deg, rgba(255,255,255,.05) 0 1px, transparent 1px 24px),
    repeating-radial-gradient(80% 55% at 50% 52%, rgba(255,255,255,.08) 0 1px, transparent 1px 120px);
  background-blend-mode:screen;
}

.hero-center-glow{
  pointer-events:none;position:absolute;bottom:128px;left:50%;z-index:0;
  height:120px;width:80px;transform:translateX(-50%);border-radius:6px;
  background:linear-gradient(to bottom, rgba(167,139,250,.45), rgba(124,58,237,.25), transparent);
  animation:subtlePulse 8s ease-in-out infinite;
  filter:blur(8px);
}

.hero-pillars{pointer-events:none;position:absolute;inset:0 0 0 0;z-index:0;height:54vh;bottom:0;top:auto}
.hero-pillars-fade{position:absolute;inset:0;background:linear-gradient(to top, var(--bg), rgba(5,3,9,.92), transparent)}
.hero-pillars-bars{position:absolute;inset:0 0 0 0;bottom:0;display:flex;align-items:flex-end;gap:1px;padding:0 2px}
.hero-pillar{flex:1;background:var(--bg);transition:height 1s ease-in-out;height:0}

.hero-inner{position:relative;z-index:2;max-width:820px;width:100%}

.hero-announce{
  display:inline-flex;align-items:center;gap:7px;
  padding:5px 14px;border-radius:100px;
  background:rgba(124,58,237,.08);
  border:1px solid rgba(139,92,246,.18);
  font-size:12px;font-weight:500;color:var(--pl);
  letter-spacing:-.01em;
  margin-bottom:28px;
  text-decoration:none;cursor:default;
  transition:border-color .2s;
}
.hero-announce:hover{border-color:rgba(139,92,246,.35)}
.hero-announce-dot{width:5px;height:5px;border-radius:50%;background:var(--pl);opacity:.7}
.hero-announce-sep{display:none}
.hero-announce-pill{display:none}
.hero-announce-arr{display:none}

h1.hero-title{
  font-family:var(--f);
  font-size:clamp(36px,5.5vw,60px);
  line-height:1.08;
  letter-spacing:-.03em;
  font-weight:700;
  color:#fff;
  margin-bottom:20px;
  text-transform:none;
}
.hero-title .line2{
  font-family:'Press Start 2P',monospace;
  font-size:clamp(20px,2.5vw,36px);
  line-height:1;
  letter-spacing:.02em;
  background:linear-gradient(90deg,#c4b5fd,#e879f9,#a78bfa);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  filter:drop-shadow(0 0 30px rgba(232,121,249,.5)) drop-shadow(0 0 60px rgba(124,58,237,.3));
  display:block;
  margin:10px 0 8px;
}

.hero-sub{
  font-size:16px;color:rgba(255,255,255,.8);
  line-height:1.65;max-width:520px;
  margin:0 auto 32px;font-weight:400;
  text-wrap:balance;
}

.hero-cta{display:flex;align-items:center;justify-content:center;gap:12px;flex-wrap:wrap;margin-bottom:56px}
.btn-hero{
  padding:12px 24px;font-size:14px;border-radius:100px;font-weight:600;
  background:#fff;
  color:#000;border:none;cursor:pointer;
  font-family:var(--f);text-decoration:none;
  display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 2px 12px rgba(0,0,0,.25);
  letter-spacing:-.01em;
  transition:all .18s;
}
.btn-hero::before{display:none}
.btn-hero:hover{background:rgba(255,255,255,.9);transform:translateY(-1px)}
.free-badge{background:#22c55e;color:#fff;font-size:10px;font-weight:800;letter-spacing:.06em;padding:2px 7px;border-radius:5px;font-family:var(--f)}
.btn-hero-ghost{
  padding:12px 24px;font-size:14px;border-radius:100px;font-weight:600;
  background:transparent;
  color:rgba(255,255,255,.9);border:1px solid rgba(255,255,255,.2);
  font-family:var(--f);text-decoration:none;
  display:inline-flex;align-items:center;gap:8px;
  backdrop-filter:blur(4px);
  transition:all .18s;letter-spacing:-.01em;
}
.btn-hero-ghost:hover{border-color:rgba(255,255,255,.4);color:#fff;transform:translateY(-1px)}

.hero-preview{
  position:relative;z-index:2;
  width:100%;max-width:1000px;margin:0 auto;
  padding:0 24px;
}
.hero-preview-wrap{
  position:relative;
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.09);
  border-radius:18px 18px 0 0;
  padding:12px;
  box-shadow:0 -4px 60px rgba(124,58,237,.12),0 0 0 1px rgba(255,255,255,.04);
  overflow:hidden;
}

.hero-preview-wrap::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:60%;
  background:linear-gradient(to bottom,transparent,rgba(8,6,15,.95));
  pointer-events:none;z-index:2;
}

.hero-preview-bar{
  display:flex;align-items:center;gap:6px;
  padding:0 4px 10px;
}
.hero-preview-dot{width:10px;height:10px;border-radius:50%}
.hero-preview-dot:nth-child(1){background:#f87171}
.hero-preview-dot:nth-child(2){background:#fbbf24}
.hero-preview-dot:nth-child(3){background:#22c55e}
.hero-preview-screen{
  border-radius:10px;
  overflow:hidden;
  background:linear-gradient(160deg,#0c0820 0%,#130b2e 40%,#0a061a 100%);
  border:1px solid rgba(139,92,246,.15);
  min-height:340px;
  display:grid;
  grid-template-columns:220px 1fr;
}

.preview-sidebar{
  border-right:1px solid rgba(255,255,255,.06);
  padding:20px 16px;
  display:flex;flex-direction:column;gap:6px;
}
.preview-sidebar-logo{
  display:flex;align-items:center;gap:8px;
  padding:8px 10px;border-radius:8px;
  margin-bottom:12px;
}
.preview-sidebar-logo-sq{width:26px;height:26px;border-radius:6px;background:linear-gradient(135deg,#7c3aed,#a855f7);flex-shrink:0;box-shadow:0 0 12px rgba(124,58,237,.5)}
.preview-sidebar-logo-txt{font-size:13px;font-weight:700;color:var(--w);letter-spacing:-.01em}
.preview-nav-item{
  display:flex;align-items:center;gap:9px;
  padding:8px 10px;border-radius:7px;
  font-size:12.5px;font-weight:500;color:var(--w3);
  transition:all .12s;
}
.preview-nav-item.active{background:rgba(124,58,237,.15);color:var(--pl)}
.preview-nav-item svg{flex-shrink:0;opacity:.7}
.preview-nav-item.active svg{opacity:1}

.preview-main{padding:24px;display:flex;flex-direction:column;gap:16px}
.preview-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
.preview-title{font-family:var(--display);font-size:22px;letter-spacing:.02em;text-transform:uppercase;color:var(--w)}
.preview-pill{padding:4px 12px;border-radius:6px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);font-size:10px;font-weight:700;color:#22c55e;letter-spacing:.05em;text-transform:uppercase}

.preview-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.preview-card{
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
  border-radius:10px;padding:14px;
}
.preview-card-icon{width:32px;height:32px;border-radius:8px;margin-bottom:10px;background:linear-gradient(135deg,rgba(124,58,237,.3),rgba(168,85,247,.2));display:flex;align-items:center;justify-content:center;color:var(--pl)}
.preview-card-title{font-size:12px;font-weight:700;color:var(--w);margin-bottom:3px}
.preview-card-sub{font-size:10.5px;color:var(--w3)}
.preview-card.featured{border-color:rgba(139,92,246,.3);background:rgba(124,58,237,.08)}

.preview-bar-wrap{margin-top:4px}
.preview-bar-label{display:flex;justify-content:space-between;font-size:10px;color:var(--w3);margin-bottom:5px}
.preview-bar{height:3px;background:rgba(255,255,255,.06);border-radius:2px;overflow:hidden}
.preview-bar-fill{height:100%;background:linear-gradient(90deg,#7c3aed,#e879f9);border-radius:2px}

.hero-stats{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:wrap}
.stat-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:9px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);font-size:13px;font-weight:500;color:var(--w2);backdrop-filter:blur(8px)}
.stat-pill-icon{color:var(--pl)}

.hero-partners{position:relative;z-index:10;width:100%;max-width:700px;margin:0 auto;padding:0 24px 90px}
.hero-partners-inner{display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:16px 28px}
.hero-partner-label{font-size:10.5px;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.35);font-weight:500;font-family:var(--m)}
.hero-partner-label:nth-child(even){color:rgba(255,255,255,.25)}

#versions{
  padding:28px 56px;
  border-top:1px solid rgba(255,255,255,.04);
  border-bottom:1px solid rgba(255,255,255,.04);
  background:rgba(0,0,0,.3);
  display:flex;align-items:center;justify-content:center;gap:16px;
  overflow-x:auto;scrollbar-width:none;
}
#versions::-webkit-scrollbar{display:none}
.ver-label{font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--w3);white-space:nowrap;flex-shrink:0;font-family:var(--m)}
.ver-pills{display:flex;gap:8px;align-items:center;flex-shrink:0}
.vpill{
  padding:6px 14px;border-radius:7px;
  font-family:var(--m);font-size:12px;font-weight:500;
  white-space:nowrap;border:1px solid;transition:all .14s;cursor:default;
}
.vpill-latest{background:rgba(124,58,237,.18);border-color:rgba(139,92,246,.45);color:var(--pl);box-shadow:0 0 12px rgba(124,58,237,.2);transition:all .18s}
.vpill-latest:hover{box-shadow:0 0 20px rgba(124,58,237,.4);border-color:rgba(139,92,246,.7)}
.vpill-normal{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.08);color:var(--w3);transition:all .18s}
.vpill-normal:hover{border-color:rgba(139,92,246,.3);color:var(--w2);background:rgba(124,58,237,.08)}
.vpill-loader{background:rgba(45,212,191,.08);border-color:rgba(45,212,191,.2);color:var(--teal);transition:all .18s}
.vpill-loader:hover{box-shadow:0 0 16px rgba(45,212,191,.25);border-color:rgba(45,212,191,.5)}
.vpill-fabric{background:rgba(124,58,237,.12);border-color:rgba(139,92,246,.35);color:var(--pl)}
.vpill-fabric:hover{box-shadow:0 0 16px rgba(124,58,237,.35);border-color:rgba(139,92,246,.6)}
.vpill-forge{background:rgba(251,146,60,.08);border-color:rgba(251,146,60,.25);color:#fb923c}
.vpill-forge:hover{box-shadow:0 0 16px rgba(251,146,60,.25);border-color:rgba(251,146,60,.5)}
.vpill-optifine{background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.25);color:#22c55e}
.vpill-optifine:hover{box-shadow:0 0 16px rgba(34,197,94,.25);border-color:rgba(34,197,94,.5)}
.ver-div{width:1px;height:20px;background:var(--line);flex-shrink:0}

.section-label{
  font-size:11.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;
  color:var(--pl);margin-bottom:16px;
  display:flex;align-items:center;gap:10px;font-family:var(--m);
}
.section-label::before{content:'';width:24px;height:1px;background:var(--pl);box-shadow:0 0 8px var(--pl)}
h2.section-title{
  font-family:var(--display);
  font-size:clamp(52px,6vw,80px);
  line-height:1;letter-spacing:.01em;
  text-transform:uppercase;
  margin-bottom:64px;
}
h2.section-title span{
  background:linear-gradient(90deg,var(--pl),var(--pink));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

#features{padding:100px 0;position:relative;overflow:hidden}
#features .feat-top{padding:0 56px;margin-bottom:0}

.falling-bg,.falling-overlay{display:none}

.orbital-wrap{
  position:relative;width:100%;height:850px;
  display:flex;align-items:center;justify-content:center;
  cursor:default;
  margin-top:20px;
}

.orb-ring{
  position:absolute;border-radius:50%;border:1px solid rgba(139,92,246,.15);
  pointer-events:none;
  box-shadow:0 0 40px rgba(124,58,237,.06);
  animation:ring-glow 6s ease-in-out infinite;
}
@keyframes ring-glow{0%,100%{border-color:rgba(139,92,246,.12);box-shadow:0 0 30px rgba(124,58,237,.04)}50%{border-color:rgba(139,92,246,.22);box-shadow:0 0 50px rgba(124,58,237,.1)}}
.orb-ring-1{width:250px;height:250px}
.orb-ring-2{width:550px;height:550px}
.orb-ring-3{width:775px;height:775px}

.orb-core{
  position:absolute;width:90px;height:90px;border-radius:50%;z-index:10;
  background:linear-gradient(135deg,#7c3aed,#a855f7,#e879f9);
  box-shadow:0 0 60px rgba(124,58,237,.9),0 0 120px rgba(124,58,237,.4),0 0 200px rgba(124,58,237,.15);
  display:flex;align-items:center;justify-content:center;
  animation:core-pulse 3s ease-in-out infinite;
}
.orb-core::before{
  content:'';position:absolute;width:100px;height:100px;border-radius:50%;
  border:1px solid rgba(167,139,250,.25);animation:ring-expand 2.5s ease-out infinite;
}
.orb-core::after{
  content:'';position:absolute;width:130px;height:130px;border-radius:50%;
  border:1px solid rgba(167,139,250,.12);animation:ring-expand 2.5s ease-out infinite;animation-delay:.5s;
}
@keyframes core-pulse{0%,100%{box-shadow:0 0 50px rgba(124,58,237,.8),0 0 100px rgba(124,58,237,.35)}50%{box-shadow:0 0 70px rgba(124,58,237,1),0 0 140px rgba(124,58,237,.5)}}
@keyframes ring-expand{0%{opacity:.6;transform:scale(1)}100%{opacity:0;transform:scale(1.5)}}
.orb-core-icon{color:rgba(255,255,255,.95);position:relative;z-index:1}

.orb-node{
  position:absolute;
  cursor:pointer;
  transition:opacity .4s ease;
  display:flex;flex-direction:column;align-items:center;gap:0;
}
.orb-dot{
  width:62px;height:62px;border-radius:50%;
  background:rgba(8,6,15,.95);
  border:2px solid rgba(139,92,246,.4);
  display:flex;align-items:center;justify-content:center;
  color:var(--pl);
  transition:all .3s ease;
  position:relative;z-index:2;
  box-shadow:0 4px 16px rgba(0,0,0,.5);
}
.orb-node:hover .orb-dot,
.orb-node.active .orb-dot{
  background:linear-gradient(135deg,rgba(124,58,237,.4),rgba(168,85,247,.3));
  border-color:var(--pl);
  border-width:2px;
  box-shadow:0 0 24px rgba(124,58,237,.65),0 0 50px rgba(124,58,237,.25);
  color:var(--px);
  transform:scale(1.15);
}
.orb-node.related .orb-dot{
  border-color:rgba(232,121,249,.6);
  animation:node-pulse 1.5s ease-in-out infinite;
}
@keyframes node-pulse{0%,100%{box-shadow:0 0 10px rgba(232,121,249,.3)}50%{box-shadow:0 0 24px rgba(232,121,249,.7)}}

.orb-halo{
  position:absolute;top:50%;left:50%;
  border-radius:50%;pointer-events:none;
  background:radial-gradient(circle,rgba(124,58,237,.14),transparent 70%);
  transform:translate(-50%,-50%);
  transition:all .3s;
  z-index:1;
}
.orb-node:hover .orb-halo,.orb-node.active .orb-halo{
  background:radial-gradient(circle,rgba(124,58,237,.32),transparent 70%);
}

.orb-label{
  position:absolute;
  white-space:nowrap;
  font-family:var(--f);
  font-size:13px;
  font-weight:800;
  letter-spacing:-.01em;
  color:var(--w);
  text-align:center;
  pointer-events:none;
  background:rgba(6,4,14,.88);
  padding:5px 13px;
  border-radius:20px;
  border:1px solid rgba(139,92,246,.2);
  box-shadow:0 2px 12px rgba(0,0,0,.5);
  transition:all .2s ease;
  line-height:1.2;
}
.orb-node:hover .orb-label,.orb-node.active .orb-label{
  color:var(--px);
  border-color:rgba(139,92,246,.5);
  background:rgba(124,58,237,.18);
  box-shadow:0 2px 16px rgba(124,58,237,.25);
}

#orb-svg{
  position:absolute;inset:0;width:100%;height:100%;
  pointer-events:none;z-index:1;
}

.orb-detail{
  position:absolute;
  left:50%;top:50%;
  transform:translate(-50%,-50%) scale(.95);
  z-index:50;
  width:360px;
  background:rgba(6,4,14,.97);
  border:1px solid rgba(139,92,246,.35);
  border-radius:18px;
  padding:28px 30px;
  box-shadow:0 0 0 1px rgba(255,255,255,.04),0 28px 80px rgba(0,0,0,.9),0 0 100px rgba(124,58,237,.18);
  backdrop-filter:blur(28px);
  pointer-events:none;
  opacity:0;
  transition:opacity .22s ease,transform .22s ease;
}
.orb-detail.visible{
  opacity:1;
  transform:translate(-50%,-50%) scale(1);
  pointer-events:auto;
}
.orb-detail::before{
  content:'';position:absolute;top:0;left:0;right:0;height:1px;
  background:linear-gradient(90deg,transparent,rgba(139,92,246,.6),transparent);
  border-radius:18px 18px 0 0;
}
.orb-detail-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.orb-detail-status{
  font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;
  padding:3px 10px;border-radius:5px;font-family:var(--m);
}
.orb-close{
  width:28px;height:28px;border-radius:7px;
  background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);
  cursor:pointer;color:var(--w3);
  display:flex;align-items:center;justify-content:center;
  transition:all .12s;flex-shrink:0;
}
.orb-close:hover{background:rgba(248,113,113,.12);border-color:rgba(248,113,113,.3);color:var(--red)}
.status-active{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.22);color:#22c55e}
.status-soon{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.22);color:#fbbf24}
.orb-detail-title{
  font-family:var(--display);font-size:36px;letter-spacing:.03em;text-transform:uppercase;
  margin-bottom:10px;color:var(--w);line-height:1;
}
.orb-detail-desc{font-size:14.5px;color:var(--w2);line-height:1.65;margin-bottom:20px;font-weight:400}
.orb-energy-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px}
.orb-energy-label{font-size:10px;font-weight:700;color:var(--w3);letter-spacing:.08em;text-transform:uppercase;font-family:var(--m)}
.orb-energy-val{font-size:10px;font-weight:700;color:var(--pl);font-family:var(--m)}
.orb-energy-bar{width:100%;height:3px;background:rgba(255,255,255,.07);border-radius:2px;overflow:hidden;margin-bottom:20px}
.orb-energy-fill{height:100%;border-radius:2px;background:linear-gradient(90deg,#7c3aed,#e879f9);transition:width .6s ease}
.orb-related-label{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--w3);font-family:var(--m);margin-bottom:8px}
.orb-related-pills{display:flex;flex-wrap:wrap;gap:6px}
.orb-related-pill{
  font-size:12px;font-weight:600;padding:5px 14px;border-radius:7px;
  background:rgba(124,58,237,.1);border:1px solid rgba(139,92,246,.22);
  color:var(--pl);cursor:pointer;transition:all .15s;font-family:var(--m);
}
.orb-related-pill:hover{background:rgba(124,58,237,.22);border-color:var(--pl);color:var(--px)}

#showcase{padding:0 56px 120px}
.showcase-header{margin-bottom:48px}
.mosaic{
  display:grid;
  grid-template-columns:1.6fr 1fr 1fr;
  grid-template-rows:300px 280px;
  gap:12px;
}
.mimg{
  border-radius:14px;overflow:hidden;
  position:relative;cursor:default;
  background:var(--bg3);
  border:1px solid rgba(255,255,255,.06);
}
.mimg:hover .mimg-overlay{opacity:1}
.mimg-big{grid-row:span 2}
.mimg-overlay{
  position:absolute;inset:0;
  background:linear-gradient(180deg,transparent 40%,rgba(8,6,15,.9));
  display:flex;align-items:flex-end;padding:20px;
  opacity:0;transition:opacity .25s;
}
.mimg-label{font-family:var(--m);font-size:11px;font-weight:500;color:var(--px);letter-spacing:.06em;text-transform:uppercase;background:rgba(8,6,15,.7);padding:5px 10px;border-radius:6px;border:1px solid rgba(139,92,246,.2)}

.shader-night{background:linear-gradient(135deg,#0a0520 0%,#1a0a3a 30%,#0d1a3a 60%,#030815 100%)}
.shader-sunset{background:linear-gradient(135deg,#1a0520 0%,#3d1060 30%,#8b2a80 55%,#c44d3a 75%,#e8823a 100%)}
.shader-forest{background:linear-gradient(135deg,#050f08 0%,#0d2a10 30%,#1a4a1e 55%,#0a2010 80%,#040a06 100%)}
.shader-plains{background:linear-gradient(160deg,#0c0820 0%,#1a1040 30%,#2a1560 50%,#1a0d40 75%,#080415 100%)}
.shader-cave{background:linear-gradient(135deg,#030208 0%,#0a0520 30%,#150a35 55%,#0d0618 80%,#030208 100%)}

.shader-night::before{content:'';position:absolute;width:150px;height:150px;border-radius:50%;top:20%;left:30%;background:radial-gradient(ellipse,rgba(167,139,250,.15),transparent);filter:blur(30px)}
.shader-sunset::before{content:'';position:absolute;width:200px;height:200px;border-radius:50%;top:-20px;right:20%;background:radial-gradient(ellipse,rgba(232,121,249,.25),transparent);filter:blur(40px)}
.shader-forest::before{content:'';position:absolute;width:180px;height:180px;border-radius:50%;top:30%;left:20%;background:radial-gradient(ellipse,rgba(45,212,191,.1),transparent);filter:blur(35px)}
.shader-plains::before{content:'';position:absolute;width:300px;height:300px;border-radius:50%;top:10%;left:10%;background:radial-gradient(ellipse,rgba(124,58,237,.12),transparent);filter:blur(50px)}
.shader-cave::before{content:'';position:absolute;width:100px;height:100px;border-radius:50%;top:40%;left:40%;background:radial-gradient(ellipse,rgba(167,139,250,.2),transparent);filter:blur(25px)}

.mimg-info{
  position:absolute;bottom:16px;left:16px;
  font-family:var(--m);font-size:11px;color:var(--w3);
  letter-spacing:.06em;text-transform:uppercase;
  background:rgba(8,6,15,.6);
  padding:4px 10px;border-radius:5px;
  border:1px solid rgba(255,255,255,.08);
}

#trust{
  padding:120px 56px;
  border-top:1px solid var(--line);
  display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;
}
.trust-points{display:flex;flex-direction:column;gap:36px}
.trust-point{display:flex;align-items:flex-start;gap:20px}
.trust-icon{
  width:48px;height:48px;border-radius:12px;flex-shrink:0;
  background:rgba(124,58,237,.1);border:1px solid rgba(139,92,246,.2);
  display:flex;align-items:center;justify-content:center;color:var(--pl);
  margin-top:2px;
}
.trust-point h3{font-size:18px;font-weight:700;letter-spacing:-.02em;margin-bottom:6px}
.trust-point p{font-size:15px;color:var(--w2);line-height:1.6}

.stat-cards{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.scard{
  background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.07);
  border-radius:14px;padding:28px 24px;
  transition:border-color .18s;
  position:relative;overflow:hidden;
}
.scard::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--p),var(--pink));opacity:.5}
.scard:hover{border-color:rgba(139,92,246,.3)}
.scard-num{font-family:var(--display);font-size:48px;letter-spacing:.01em;line-height:1;color:var(--w);margin-bottom:6px}
.scard-label{font-size:13.5px;color:var(--w3);font-weight:500}

#dl-cta{
  padding:140px 56px;
  text-align:center;position:relative;overflow:hidden;
  border-top:1px solid var(--line);
}
.dl-cta-bg{
  position:absolute;inset:0;
  background:radial-gradient(ellipse 70% 80% at 50% 50%,rgba(124,58,237,.12),transparent);
}
.dl-cta-glow{
  position:absolute;top:50%;left:50%;
  transform:translate(-50%,-50%);
  width:700px;height:450px;
  background:radial-gradient(ellipse,rgba(124,58,237,.22),transparent 65%);
  filter:blur(60px);pointer-events:none;
  animation:cta-glow-pulse 4s ease-in-out infinite;
}
@keyframes cta-glow-pulse{0%,100%{opacity:.8}50%{opacity:1}}
#dl-cta .inner{position:relative;z-index:2;max-width:700px;margin:0 auto}
#dl-cta h2{
  font-family:var(--display);
  font-size:clamp(52px,6vw,84px);
  text-transform:uppercase;
  line-height:1;letter-spacing:.01em;
  margin-bottom:20px;
}
#dl-cta h2 span{
  background:linear-gradient(90deg,var(--pl),var(--pink));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
#dl-cta p{font-size:18px;color:var(--w2);margin-bottom:44px;line-height:1.6}
.dl-cta-btns{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:32px}
.platform-row{display:flex;align-items:center;justify-content:center;gap:20px}
.platform-link{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--w3);text-decoration:none;transition:color .14s;font-weight:500}
.platform-link:hover{color:var(--w)}
.platform-link svg{opacity:.5;transition:opacity .14s}
.platform-link:hover svg{opacity:1}

footer{
  padding:64px 56px 32px;
  border-top:1px solid var(--line);
  background:rgba(4,2,11,.6);
  position:relative;z-index:1;
}
.footer-top{
  display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;
  gap:48px;margin-bottom:56px;
}
.footer-brand{}
.footer-brand .nav-logo{margin-bottom:14px;display:inline-flex}
.footer-brand p{font-size:14px;color:var(--w3);line-height:1.7;max-width:240px}
.footer-col h4{font-size:11.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--w3);margin-bottom:18px;font-family:var(--m)}
.footer-col a{display:block;font-size:14px;color:var(--w3);text-decoration:none;margin-bottom:10px;transition:color .12s;font-weight:400}
.footer-col a:hover{color:var(--w)}
.footer-bottom{
  border-top:1px solid var(--line);padding-top:24px;
  display:flex;align-items:center;justify-content:space-between;
}
.footer-copy{font-size:13px;color:var(--w3)}
.footer-legal{display:flex;gap:20px}
.footer-legal a{font-size:13px;color:var(--w3);text-decoration:none;transition:color .12s}
.footer-legal a:hover{color:var(--w)}

.ov{position:fixed;inset:0;background:rgba(0,0,0,.78);backdrop-filter:blur(14px);z-index:600;display:none;align-items:center;justify-content:center;padding:20px}
.ov.on{display:flex}
.mod{
  background:linear-gradient(160deg,rgba(16,13,30,.98),rgba(8,6,15,.98));
  border:1px solid rgba(139,92,246,.2);
  border-radius:20px;padding:36px;
  width:100%;max-width:420px;max-height:90vh;overflow-y:auto;
  position:relative;
  box-shadow:0 0 0 1px rgba(255,255,255,.04),0 40px 100px rgba(0,0,0,.9),0 0 100px rgba(124,58,237,.12);
}
.mod::-webkit-scrollbar{width:4px}.mod::-webkit-scrollbar-thumb{background:rgba(139,92,246,.3);border-radius:2px}
.mx{position:absolute;top:18px;right:18px;width:30px;height:30px;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);cursor:pointer;color:var(--w3);display:flex;align-items:center;justify-content:center;transition:all .12s}
.mx:hover{background:rgba(248,113,113,.1);border-color:rgba(248,113,113,.25);color:var(--red)}
.mod h3{font-family:var(--display);font-size:32px;letter-spacing:.02em;text-transform:uppercase;margin-bottom:6px}
.msub{font-size:14px;color:var(--w3);margin-bottom:28px;line-height:1.55}
.fld{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fld label{font-size:11px;font-weight:700;color:var(--w3);letter-spacing:.08em;text-transform:uppercase;font-family:var(--m)}
.fld input{padding:12px 15px;border-radius:10px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:var(--w);font-family:var(--f);font-size:14px;outline:none;transition:border-color .12s,box-shadow .12s}
.fld input:focus{border-color:rgba(124,58,237,.5);box-shadow:0 0 0 3px rgba(124,58,237,.1)}
.fld input::placeholder{color:var(--w3)}
.ferr{font-size:12.5px;color:var(--red);background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.18);border-radius:8px;padding:10px 14px;display:none;margin-bottom:12px}
.fsub{width:100%;padding:14px;border-radius:10px;border:none;background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;font-family:var(--f);font-size:15px;font-weight:700;cursor:pointer;transition:all .15s;box-shadow:0 4px 20px rgba(124,58,237,.4),0 0 0 1px rgba(167,139,250,.2);letter-spacing:-.02em}
.fsub:hover{box-shadow:0 8px 32px rgba(124,58,237,.6),0 0 0 1px rgba(167,139,250,.35);transform:translateY(-1px)}
.fsub:disabled{opacity:.45;transform:none;cursor:not-allowed}
.msw{font-size:13.5px;color:var(--w3);text-align:center;margin-top:18px}
.msw a{color:var(--pl);cursor:pointer;font-weight:600;text-decoration:none}
.msw a:hover{color:var(--px)}

#toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%);padding:12px 22px;border-radius:10px;font-size:13.5px;font-weight:600;opacity:0;pointer-events:none;transition:opacity .22s;z-index:9998;backdrop-filter:blur(16px);white-space:nowrap}
#toast.on{opacity:1}
#toast.ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#22c55e;box-shadow:0 4px 24px rgba(34,197,94,.15)}
#toast.er{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:var(--red)}

#compare{
  padding:120px 56px;
  border-top:1px solid var(--line);
  text-align:center;
}
#compare .compare-header{max-width:560px;margin:0 auto 56px}
.compare-table-wrap{
  max-width:780px;margin:0 auto;
  border-radius:18px;overflow:hidden;
  border:1px solid rgba(255,255,255,.07);
}
.compare-table{width:100%;border-collapse:collapse}
.compare-table th{
  padding:18px 24px;font-size:11px;font-weight:700;
  letter-spacing:.1em;text-transform:uppercase;
  color:var(--w3);font-family:var(--m);
  background:rgba(255,255,255,.025);
  border-bottom:1px solid rgba(255,255,255,.06);
  text-align:center;
}
.compare-table th:first-child{text-align:left}
.compare-table th.col-justice{
  color:var(--pl);
  background:rgba(124,58,237,.1);
  border-bottom-color:rgba(139,92,246,.25);
  position:relative;
}
.compare-table th.col-justice::after{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,#7c3aed,#e879f9);
}
.compare-table td{
  padding:16px 24px;font-size:14px;
  border-bottom:1px solid rgba(255,255,255,.04);
  text-align:center;color:var(--w2);
}
.compare-table td:first-child{text-align:left;font-weight:600;color:var(--w);font-size:13.5px}
.compare-table td.col-justice{background:rgba(124,58,237,.07);color:var(--w);box-shadow:inset 0 0 30px rgba(124,58,237,.05)}
.compare-table tr:last-child td{border-bottom:none}
.compare-table tr:hover td{background:rgba(255,255,255,.02)}
.compare-table tr:hover td.col-justice{background:rgba(124,58,237,.09)}
.cmp-yes{color:#22c55e;font-size:16px;font-weight:800}
.cmp-no{color:rgba(255,255,255,.2);font-size:16px}
.cmp-partial{color:#fbbf24;font-size:13px;font-weight:600}

#testimonials{
  padding:0;
  border-top:1px solid var(--line);
  border-bottom:1px solid var(--line);
  background:rgba(255,255,255,.015);
  overflow:hidden;
  position:relative;
  z-index:1;
}
#testimonials::before,#testimonials::after{
  content:'';position:absolute;top:0;bottom:0;width:120px;z-index:2;pointer-events:none;
}
#testimonials::before{left:0;background:linear-gradient(90deg,var(--bg),transparent)}
#testimonials::after{right:0;background:linear-gradient(-90deg,var(--bg),transparent)}
.ticker-track{
  display:flex;gap:0;
  animation:ticker-scroll 55s linear infinite;
  width:max-content;
}
.ticker-track:hover{animation-play-state:paused}
@keyframes ticker-scroll{
  0%{transform:translateX(0)}
  100%{transform:translateX(-50%)}
}
.ticker-item{
  display:flex;align-items:center;gap:14px;
  padding:20px 36px;
  border-right:1px solid var(--line);
  flex-shrink:0;
  white-space:nowrap;
}
.ticker-stars{color:#fbbf24;font-size:12px;letter-spacing:2px}
.ticker-quote{font-size:14px;color:var(--w2);font-weight:500;font-style:italic}
.ticker-who{font-size:12px;color:var(--w3);font-weight:600;font-family:var(--m)}

#how{
  padding:120px 56px;
  border-bottom:1px solid var(--line);
  text-align:center;
}
#how .how-header{max-width:560px;margin:0 auto 72px}
.how-steps{
  display:grid;grid-template-columns:repeat(3,1fr);
  gap:2px;
  max-width:900px;margin:0 auto;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.07);
  border-radius:18px;
  overflow:hidden;
}
.how-step{
  padding:52px 40px;
  background:var(--bg);
  position:relative;
  text-align:left;
}
.how-step:not(:last-child)::after{
  content:'';position:absolute;right:0;top:50%;transform:translateY(-50%);
  width:1px;height:50%;background:var(--line);
}
.how-step-num{
  font-family:var(--display);font-size:72px;letter-spacing:.01em;
  color:rgba(255,255,255,.9);line-height:1;margin-bottom:20px;
  text-shadow:0 0 30px rgba(124,58,237,.4);
}
.how-step-title{font-size:18px;font-weight:800;letter-spacing:-.02em;margin-bottom:10px;color:var(--w)}
.how-step-desc{font-size:14px;color:var(--w3);line-height:1.65;font-weight:400}
.how-step-icon{
  width:42px;height:42px;border-radius:10px;
  background:rgba(124,58,237,.1);border:1px solid rgba(139,92,246,.2);
  display:flex;align-items:center;justify-content:center;
  color:var(--pl);margin-bottom:18px;
}

.reveal{opacity:0;transform:translateY(22px);transition:opacity .6s cubic-bezier(.22,1,.36,1),transform .6s cubic-bezier(.22,1,.36,1)}
.reveal.visible{opacity:1;transform:none}
.reveal-delay-1{transition-delay:.1s}
.reveal-delay-2{transition-delay:.2s}
.reveal-delay-3{transition-delay:.3s}
.reveal-delay-4{transition-delay:.4s}
</style>
</head>
<body>

<nav id="nav">
  <a href="#" class="nav-logo">
    <div class="nav-logo-sq">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg>
    </div>
    Justice
  </a>
  <div class="nav-links">
    <a href="#features">Features</a>
    <a href="#showcase">Shaders</a>
    <a href="/shop.php">Shop</a>
    <a href="/trading.php">Trading</a>
    <a href="/marketplace.php">Marketplace</a>
    <a href="/points.php">Points</a>
    <a href="/report.php">Report</a>
    <a href="#dl-cta">Download</a>
  </div>
  <div class="nav-end">
    <div class="user-pill" id="upill">
      <div class="uav" id="uav">?</div>
      <div class="udot"></div>
      <span id="uname">Player</span>
      <button class="usign" onclick="doLogout()">Sign out</button>
    </div>
    <div id="nav-points" style="display:none;align-items:center;gap:5px;padding:6px 13px;border-radius:8px;background:rgba(124,58,237,.1);border:1px solid rgba(139,92,246,.25);cursor:pointer;font-size:12.5px;font-weight:700;color:#a78bfa" onclick="showIdxReferral()">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span id="nav-pts-val">0 pts</span>
    </div>
    <a href="/admin.php" class="btn btn-sm" id="btn-admin" style="display:none;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:#f87171">Admin</a>
    <a href="/staff.php" class="btn btn-sm" id="btn-staff" style="display:none;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);color:#fbbf24">Staff</a>
    <a href="/social.php" class="btn btn-sm btn-ghost" id="btn-social">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Social
    </a>
    <a href="#" class="btn btn-sm btn-outline" id="btn-li" onclick="showM('login');return false">Log in</a>
    <a href="#" class="btn btn-sm btn-p" id="btn-reg" onclick="showM('register');return false">Sign up</a>
  </div>
</nav>

<section id="hero">
  
  <div class="hero-bg"></div>
  <div class="hero-vignette" aria-hidden="true"></div>
  <div class="hero-grid" aria-hidden="true"></div>

  
  <div class="hero-inner">
    <div class="hero-anim-group" style="opacity:0">
      <span class="hero-announce">
        <span class="hero-announce-dot"></span>
        v<?=htmlspecialchars($version)?> — 4× FPS Mode is live
      </span>
    </div>

    <h1 class="hero-title hero-anim-group" style="opacity:0">Welcome to<br><span class="line2">Justice Client</span></h1>

    <p class="hero-sub hero-anim-group" style="opacity:0">The Performance based Client.</p>

    <div class="hero-cta hero-anim-group" style="opacity:0">
      <a href="#dl-cta" class="btn-hero">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download (Free)
      </a>
      <a href="#features" class="btn-hero-ghost">
        Read More
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
  </div>

  
  <div class="hero-partners hero-anim-group" style="opacity:0">
    <div class="hero-partners-inner">
      <span class="hero-partner-label">Fabric</span>
      <span class="hero-partner-label">1.21.x</span>
      <span class="hero-partner-label">Modrinth</span>
      <span class="hero-partner-label">50,000+ Mods</span>
      <span class="hero-partner-label">Social</span>
      <span class="hero-partner-label">Chatting</span>
    </div>
  </div>

  
  <div class="hero-center-glow" aria-hidden="true"></div>

  
  <div class="hero-pillars" aria-hidden="true">
    <div class="hero-pillars-fade"></div>
    <div class="hero-pillars-bars" id="hero-pillars-bars"></div>
  </div>
</section>

<div id="versions">
  <span class="ver-label">Versions</span>
  <div class="ver-pills">
    <span class="vpill vpill-latest">1.21.x — Latest</span>
    <span class="vpill vpill-normal">1.20.x</span>
    <span class="vpill vpill-normal">1.19.x</span>
    <span class="vpill vpill-normal">1.18.x</span>
    <span class="vpill vpill-normal">1.16.x</span>
    <span class="vpill vpill-normal">1.8.9</span>
  </div>
  <div class="ver-div"></div>
  <div class="ver-pills">
    <span class="vpill vpill-fabric">Fabric</span>
    <span class="vpill vpill-forge">Forge</span>
    <span class="vpill vpill-optifine">OptiFine</span>
  </div>
</div>

<section id="how">
  <div class="how-header">
    <div class="section-label reveal" style="justify-content:center">How it works</div>
    <h2 class="section-title reveal reveal-delay-1" style="margin-bottom:16px">Download. Setup.<br><span>Play.</span></h2>
    <p class="reveal reveal-delay-2" style="font-size:16px;color:var(--w3);max-width:420px;margin:0 auto;line-height:1.7">No setup wizards. No account walls. No reading the docs. Justice is designed to get out of your way.</p>
  </div>
  <div class="how-steps reveal reveal-delay-2">
    <div class="how-step">
      <div class="how-step-num">01</div>
      <div class="how-step-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      </div>
      <div class="how-step-title">Download</div>
      <div class="how-step-desc">One installer. Runs on Windows, macOS, and Linux. No Java hunting, no prerequisite installs — we handle that.</div>
    </div>
    <div class="how-step">
      <div class="how-step-num">02</div>
      <div class="how-step-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      </div>
      <div class="how-step-title">Pick your setup</div>
      <div class="how-step-desc">Choose a version, drop in mods from Modrinth, add a shader pack. Done in under two minutes — or just hit play vanilla.</div>
    </div>
    <div class="how-step">
      <div class="how-step-num">03</div>
      <div class="how-step-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      </div>
      <div class="how-step-title">Launch</div>
      <div class="how-step-desc">Hit play. Justice optimizes in the background, keeps things updated, and stays out of your way while you play.</div>
    </div>
  </div>
</section>

<section id="features">
  <div class="falling-bg"></div>
  <div class="falling-overlay"></div>
  <div class="feat-top" style="position:relative;z-index:2;text-align:center">
    <div class="section-label reveal" style="justify-content:center">What's inside</div>
    <h2 class="section-title reveal reveal-delay-1" style="text-align:center;margin-bottom:24px">Built for <span>players.</span><br>Not investors.</h2>
  </div>

  
  <div class="orbital-wrap" id="orbital-wrap" style="position:relative;z-index:2">
    
    <svg id="orb-svg" xmlns="http://www.w3.org/2000/svg"></svg>

    
    <div class="orb-ring orb-ring-1"></div>
    <div class="orb-ring orb-ring-2"></div>
    <div class="orb-ring orb-ring-3"></div>

    
    <div class="orb-core">
      <svg class="orb-core-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg>
    </div>

    
    <div id="orb-nodes"></div>

    
    <div class="orb-detail" id="orb-detail">
      <div class="orb-detail-top">
        <span class="orb-detail-status" id="orb-d-status"></span>
      </div>
      <div class="orb-detail-title" id="orb-d-title"></div>
      <div class="orb-detail-desc" id="orb-d-desc"></div>
      <div class="orb-energy-row">
        <span class="orb-energy-label">Integration Depth</span>
        <span class="orb-energy-val" id="orb-d-energy-val"></span>
      </div>
      <div class="orb-energy-bar"><div class="orb-energy-fill" id="orb-d-fill"></div></div>
      <div id="orb-d-related"></div>
    </div>
  </div>
</section>

<section id="showcase">
  <div class="showcase-header" style="text-align:center">
    <div class="section-label reveal" style="justify-content:center">Shaders</div>
    <h2 class="section-title reveal reveal-delay-1" style="text-align:center">Drop in.<br><span>Press play.</span></h2>
  </div>
  <div class="shader-names reveal" style="display:flex;flex-wrap:wrap;gap:12px;padding:0 56px 80px;justify-content:center;align-items:center">
    <span class="vpill vpill-loader">Complementary</span>
    <span class="vpill vpill-loader">BSL Shaders</span>
    <span class="vpill vpill-loader">Sildur's Vibrant</span>
    <span class="vpill vpill-loader">SEUS Renewed</span>
    <span class="vpill vpill-loader">Chocapic13</span>
    <span class="vpill vpill-loader">Iris Shaders</span>
    <span class="vpill vpill-loader">Rethinking Voxels</span>
    <span class="vpill vpill-normal">+ dozens more</span>
  </div>
</section>

<section id="trust">
  <div class="trust-points">
    <div class="section-label reveal">Why trust us</div>
    <div class="trust-point reveal reveal-delay-1">
      <div class="trust-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <h3>No malware. Not negotiable.</h3>
        <p>Clean builds, open development. Every release is inspectable. We don't hide what's in the launcher because there's nothing to hide.</p>
      </div>
    </div>
    <div class="trust-point reveal reveal-delay-2">
      <div class="trust-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div>
        <h3>Free means free.</h3>
        <p>Not freemium. Not "free tier." Not free with a catch. The whole launcher — every version, every feature — costs zero dollars now and forever.</p>
      </div>
    </div>
    <div class="trust-point reveal reveal-delay-3">
      <div class="trust-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
      </div>
      <div>
        <h3>Runs on everything.</h3>
        <p>Windows, macOS, Linux. Old hardware, new hardware, potato hardware. If Java can run it, Justice will launch it.</p>
      </div>
    </div>
  </div>
  <div class="stat-cards reveal">
    <div class="scard">
      <div class="scard-num">100K+</div>
      <div class="scard-label">Downloads worldwide</div>
    </div>
    <div class="scard">
      <div class="scard-num">4.9</div>
      <div class="scard-label">Average user rating</div>
    </div>
    <div class="scard">
      <div class="scard-num">50K+</div>
      <div class="scard-label">Available mods</div>
    </div>
    <div class="scard">
      <div class="scard-num">$0</div>
      <div class="scard-label">Hidden costs. Ever.</div>
    </div>
  </div>
</section>

<section id="compare">
  <div class="compare-header">
    <div class="section-label reveal" style="justify-content:center">vs The Others</div>
    <h2 class="section-title reveal reveal-delay-1" style="margin-bottom:16px">You've tried the rest.<br><span>Now try free.</span></h2>
    <p class="reveal reveal-delay-2" style="font-size:16px;color:var(--w3);line-height:1.7">Every launcher says they're the best. Here's what the feature sheet actually looks like.</p>
  </div>
  <div class="compare-table-wrap reveal reveal-delay-2">
    <table class="compare-table">
      <thead>
        <tr>
          <th></th>
          <th class="col-justice">Justice ⚡</th>
          <th>Lunar Client</th>
          <th>Badlion</th>
          <th>Vanilla</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>100% Free</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-partial">Free / Paid Features</span></td>
          <td><span class="cmp-partial">Free / Paid Features</span></td>
          <td><span class="cmp-yes">✓</span></td>
        </tr>
        <tr>
          <td>50,000+ Mods (Modrinth)</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
        </tr>
        <tr>
          <td>Shader Support</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-partial">Free / Paid</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
        </tr>
        <tr>
          <td>FPS Optimization</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-no">✗</span></td>
        </tr>
        <tr>
          <td>Multi-Version Profiles</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-partial">Limited</span></td>
          <td><span class="cmp-partial">Limited</span></td>
          <td><span class="cmp-yes">✓</span></td>
        </tr>
        <tr>
          <td>Voice Chat (Coming Soon)</td>
          <td class="col-justice"><span class="cmp-partial">Coming Soon</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
        </tr>
        <tr>
          <td>Social / Friends List</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-no">✗</span></td>
        </tr>
        <tr>
          <td>No Account Required</td>
          <td class="col-justice"><span class="cmp-yes">✓</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-no">✗</span></td>
          <td><span class="cmp-yes">✓</span></td>
        </tr>
      </tbody>
    </table>
  </div>
</section>

<section id="dl-cta">
  <div class="dl-cta-bg"></div>
  <div class="dl-cta-glow"></div>
  <div class="inner">
    <h2 class="reveal">Ready to<br><span>download?</span></h2>
    <p class="reveal reveal-delay-1">Justice runs on Windows, macOS, and Linux. No account required. Just install and play.</p>
    <div class="dl-cta-btns reveal reveal-delay-2">
      <a href="<?=htmlspecialchars($dl_win)?>" class="btn-hero" style="padding:14px 32px;font-size:16px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download (Free)
      </a>
    </div>
    <div class="platform-row reveal reveal-delay-3">
      <a href="<?=htmlspecialchars($dl_win)?>" class="platform-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M0 12.5h11.5V24H0zm12.5 0H24V24H12.5zM0 0h11.5v11.5H0zM12.5 0H24v11.5H12.5z"/></svg>
        Windows
      </a>
      <a href="<?=htmlspecialchars($dl_mac)?>" class="platform-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M18 7c0-3.3-2.7-6-6-6S6 3.7 6 7c-2.2.5-4 2.5-4 4.9C2 14.8 4.2 17 7 17h1v4h2v-4h4v4h2v-4h1c2.8 0 5-2.2 5-5.1 0-2.4-1.8-4.4-4-4.9z"/></svg>
        macOS
      </a>
      <a href="<?=htmlspecialchars($dl_linux)?>" class="platform-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Linux
      </a>
    </div>
  </div>
</section>

<footer>
  <div class="footer-top">
    <div class="footer-brand">
      <a href="#" class="nav-logo">
        <div class="nav-logo-sq"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg></div>
        Justice
      </a>
      <p>The Minecraft launcher that charges you nothing and owes you nothing. Built for players, by people who play.</p>
    </div>
    <div class="footer-col">
      <h4>Product</h4>
      <a href="#features">Features</a>
      <a href="#showcase">Shaders</a>
      <a href="#dl-cta">Download</a>
      <a href="#versions">Versions</a>
    </div>
    <div class="footer-col">
      <h4>Community</h4>
      <a href="/social.php">Social Hub</a>
      <a href="/trading.php">Trading</a>
      <a href="/marketplace.php">Marketplace</a>
      <a href="#" onclick="showM('register');return false">Create Account</a>
      <a href="#" onclick="showM('login');return false">Log In</a>
    </div>
    <div class="footer-col">
      <h4>Support</h4>
      <a href="#">FAQ</a>
      <a href="#">Bug Reports</a>
      <a href="#">Discord</a>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="footer-copy">© 2026 Justice Client. Not affiliated with Mojang or Microsoft.</span>
    <div class="footer-legal">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Use</a>
    </div>
  </div>
</footer>

<div class="ov" id="ov-login">
  <div class="mod">
    <button class="mx" onclick="hideM()"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <h3>Welcome Back</h3><p class="msub">Log in to access friends and chat.</p>
    <div class="fld"><label>Username or Email</label><input type="text" id="li-l" placeholder="your_username" autocomplete="username"></div>
    <div class="fld"><label>Password</label><input type="password" id="li-p" placeholder="••••••••" autocomplete="current-password" onkeydown="if(event.key==='Enter')subLogin()"></div>
    <div class="ferr" id="li-e"></div>
    <button class="fsub" id="li-b" onclick="subLogin()">Log In</button>
    <div class="msw">No account? <a onclick="showM('register')">Create one free →</a></div>
  </div>
</div>

<div class="ov" id="ov-register">
  <div class="mod">
    <button class="mx" onclick="hideM()"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <h3>Join Justice</h3><p class="msub">Create an account to unlock friends, chat, and more.</p>
    <div class="fld"><label>Username</label><input type="text" id="re-u" placeholder="cool_username" maxlength="20" autocomplete="username"></div>
    <div class="fld"><label>Email</label><input type="email" id="re-e" placeholder="you@example.com" autocomplete="email"></div>
    <div class="fld"><label>Password</label><input type="password" id="re-p" placeholder="At least 6 characters" autocomplete="new-password"></div>
    <div class="fld"><label>Referral Code <span style="font-weight:400;color:var(--w3);text-transform:none;letter-spacing:0;font-family:var(--f)">(optional)</span></label><input type="text" id="re-ref" placeholder="AB12CD34" maxlength="12" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()" onkeydown="if(event.key==='Enter')subReg()"></div>
    <div class="ferr" id="re-er"></div>
    <button class="fsub" id="re-b" onclick="subReg()">Create Account</button>
    <div class="msw">Already have an account? <a onclick="showM('login')">Log in</a></div>
  </div>
</div>

<div id="toast"></div>

<div class="ov" id="ov-referral" onclick="if(event.target===this)hideM()">
  <div class="mod" onclick="event.stopPropagation()">
    <button class="mx" onclick="hideM()"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <div style="text-align:center;margin-bottom:22px"><div style="font-size:30px;margin-bottom:10px">⭐</div><h3>Justice Points</h3><p class="msub">Earn 10 points per referral. 1 pt = 1,000,000 DonutSMP coins.</p></div>
    <div style="background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);border-radius:12px;padding:18px;margin-bottom:18px;text-align:center">
      <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--pl);margin-bottom:8px;font-family:var(--m)">Balance</div>
      <div id="idx-ref-pts" style="font-family:var(--display);font-size:52px;letter-spacing:.02em;color:var(--w);line-height:1">—</div>
      <div id="idx-ref-coins" style="font-size:12px;color:var(--pl);margin-top:4px">pts</div>
    </div>
    <div style="margin-bottom:14px">
      <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:7px;font-family:var(--m)">Referral Link</div>
      <div style="display:flex;gap:8px">
        <input id="idx-ref-url" type="text" readonly style="flex:1;padding:10px 13px;border-radius:9px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--w2);font-size:12px;font-family:monospace;outline:none" value="Loading…">
        <button onclick="copyIdxRef()" style="padding:10px 18px;border-radius:9px;border:none;background:var(--p);color:#fff;font-family:var(--f);font-size:12px;font-weight:700;cursor:pointer">Copy</button>
      </div>
    </div>
    <div><div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:8px;font-family:var(--m)">Referred Users</div><div id="idx-ref-list" style="font-size:13px;color:var(--w3)">Loading…</div></div>
    <div style="margin-top:18px;padding-top:18px;border-top:1px solid var(--line)">
      <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:12px;font-family:var(--m)">Withdraw → DonutSMP Coins</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <input id="idx-wd-pts" type="number" min="1" placeholder="Points" oninput="updateIdxWdPreview()" style="width:110px;padding:9px 12px;border-radius:9px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--w);font-family:var(--f);font-size:13px;outline:none">
        <input id="idx-wd-mc" type="text" placeholder="Minecraft username" style="flex:1;padding:9px 12px;border-radius:9px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--w);font-family:var(--f);font-size:13px;outline:none">
      </div>
      <div id="idx-wd-preview" style="font-size:12px;color:var(--pl);margin-bottom:10px;display:none"></div>
      <button onclick="idxRequestWithdrawal()" style="width:100%;padding:12px;border-radius:10px;border:none;background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;font-family:var(--f);font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 20px rgba(124,58,237,.4)">Request Withdrawal</button>
      <div style="margin-top:14px"><div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--w3);margin-bottom:8px;font-family:var(--m)">My Withdrawals</div><div id="idx-wd-list" style="font-size:13px;color:var(--w3)">Loading…</div></div>
    </div>
  </div>
</div>

<script>const API_BASE=window.location.origin;</script>
<script src="assets/js/app.js"></script>
<script>
// Nav scroll
const navEl=document.getElementById('nav');
window.addEventListener('scroll',()=>{navEl.classList.toggle('scrolled',window.scrollY>40)},{ passive:true });

// Scroll reveal
const revealObs=new IntersectionObserver(entries=>entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');revealObs.unobserve(e.target)}}),{threshold:.1});
document.querySelectorAll('.reveal').forEach(el=>revealObs.observe(el));

// Hero Web3 pillar + fadeIn animation
(function(){
  const pillars=[92,84,78,70,62,54,46,34,18,34,46,54,62,70,78,84,92];
  const barsEl=document.getElementById('hero-pillars-bars');
  if(!barsEl)return;
  pillars.forEach(function(h,i){
    const bar=document.createElement('div');
    bar.className='hero-pillar';
    bar.dataset.h=h;
    bar.style.transitionDelay=Math.abs(i-Math.floor(pillars.length/2))*60+'ms';
    barsEl.appendChild(bar);
  });
  setTimeout(function(){
    // Animate pillars
    document.querySelectorAll('.hero-pillar').forEach(function(b){b.style.height=b.dataset.h+'%'});
    // Animate content with staggered delays
    var groups=document.querySelectorAll('.hero-anim-group');
    groups.forEach(function(el,i){
      el.style.animationDelay=i*150+'ms';
      el.classList.add('hero-fadeIn');
    });
  },150);
})();

// Modals
function showM(w){hideM();document.getElementById('ov-'+w).classList.add('on');setTimeout(()=>{const i=document.querySelector('#ov-'+w+' input');if(i)i.focus()},70)}
function hideM(){document.querySelectorAll('.ov').forEach(o=>o.classList.remove('on'))}
document.querySelectorAll('.ov').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)hideM()}));
document.addEventListener('keydown',e=>{if(e.key==='Escape')hideM()});
function showModal(w){showM(w)}function hideModals(){hideM()}

// Auth
async function subLogin(){
  const l=document.getElementById('li-l').value.trim(),p=document.getElementById('li-p').value;
  const e=document.getElementById('li-e'),b=document.getElementById('li-b');
  e.style.display='none';
  if(!l||!p){e.textContent='Fill in all fields';e.style.display='block';return}
  b.disabled=true;b.textContent='Logging in…';
  try{const r=await fetch(API_BASE+'/api/login.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({login:l,password:p})});const d=await r.json();if(d.error){e.textContent=d.error;e.style.display='block'}else{localStorage.setItem('jl_token',d.token);window.location.href='/social.php';}}catch{e.textContent='Could not connect.';e.style.display='block'}
  b.disabled=false;b.textContent='Log In';
}
async function subReg(){
  const u=document.getElementById('re-u').value.trim(),em=document.getElementById('re-e').value.trim(),p=document.getElementById('re-p').value,ref=document.getElementById('re-ref').value.trim();
  const e=document.getElementById('re-er'),b=document.getElementById('re-b');
  e.style.display='none';
  if(!u||!em||!p){e.textContent='Fill in all fields';e.style.display='block';return}
  b.disabled=true;b.textContent='Creating…';
  try{const r=await fetch(API_BASE+'/api/register.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({username:u,email:em,password:p,referral_code:ref})});const d=await r.json();if(d.error){e.textContent=d.error;e.style.display='block'}else{localStorage.setItem('jl_token',d.token);window.location.href='/social.php';}}catch{e.textContent='Could not connect.';e.style.display='block'}
  b.disabled=false;b.textContent='Create Account';
}
(function(){const params=new URLSearchParams(window.location.search);const ref=params.get('ref');if(ref){const el=document.getElementById('re-ref');if(el)el.value=ref.toUpperCase();showM('register');}})();

function setLoggedIn(u){
  document.getElementById('upill').classList.add('on');
  document.getElementById('uav').textContent=(u.username||'?')[0].toUpperCase();
  document.getElementById('uname').textContent=u.username;
  document.getElementById('btn-li').style.display='none';
  document.getElementById('btn-reg').style.display='none';
  if(u.isAdmin){const ab=document.getElementById('btn-admin');if(ab)ab.style.display='inline-flex';}
  if(u.isStaff){const sb=document.getElementById('btn-staff');if(sb)sb.style.display='inline-flex';}
  loadNavPoints();hideM();
}
async function loadNavPoints(){try{const tok=localStorage.getItem('jl_token');if(!tok)return;const r=await fetch(API_BASE+'/api/points.php?action=balance',{headers:{'Authorization':'Bearer '+tok}});const d=await r.json();const pts=d.points||0;const el=document.getElementById('nav-pts-val');if(el)el.textContent=pts.toLocaleString()+' pts';const wrap=document.getElementById('nav-points');if(wrap)wrap.style.display='flex';window._idxRefUrl=d.referral_url||'';window._idxRefPts=pts;window._idxRefCoins=d.donut_value||'';}catch(e){}}
async function showIdxReferral(){showM('referral');document.getElementById('idx-ref-pts').textContent=(window._idxRefPts||0).toLocaleString();document.getElementById('idx-ref-coins').textContent=window._idxRefCoins||'pts';document.getElementById('idx-ref-url').value=window._idxRefUrl||'';try{const tok=localStorage.getItem('jl_token');const r=await fetch(API_BASE+'/api/points.php?action=referrals',{headers:{'Authorization':'Bearer '+tok}});const d=await r.json();const refs=d.referrals||[];const el=document.getElementById('idx-ref-list');if(!refs.length){el.textContent='No referrals yet.';}else{el.innerHTML=refs.map(u=>`<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.05)"><span style="color:var(--w)">${esc(u.username)}</span><span style="color:#22c55e;font-weight:600">+10 pts</span></div>`).join('');}}catch(e){}loadIdxWithdrawals();}
function updateIdxWdPreview(){const pts=parseInt(document.getElementById('idx-wd-pts').value||0);const el=document.getElementById('idx-wd-preview');if(pts>0){el.style.display='block';el.textContent=pts+' pts = '+(pts*1000000).toLocaleString()+' DonutSMP coins';}else el.style.display='none';}
async function idxRequestWithdrawal(){const tok=localStorage.getItem('jl_token');const pts=parseInt(document.getElementById('idx-wd-pts').value||0);const mc=document.getElementById('idx-wd-mc').value.trim();if(!pts||pts<1){showToast('Enter points amount','er');return;}if(!mc){showToast('Enter your Minecraft username','er');return;}const r=await fetch(API_BASE+'/api/withdrawals.php?action=request',{method:'POST',headers:{'Content-Type':'application/json','Authorization':'Bearer '+tok},body:JSON.stringify({points:pts,mc_username:mc})}).then(r=>r.json());if(r.error){showToast(r.error,'er');return;}showToast('Withdrawal requested!');document.getElementById('idx-wd-pts').value='';loadNavPoints();loadIdxWithdrawals();}
async function loadIdxWithdrawals(){const el=document.getElementById('idx-wd-list');if(!el)return;const tok=localStorage.getItem('jl_token');try{const r=await fetch(API_BASE+'/api/withdrawals.php?action=list',{headers:{'Authorization':'Bearer '+tok}}).then(r=>r.json());const ws=r.withdrawals||[];if(!ws.length){el.textContent='No withdrawals yet.';return;}el.innerHTML=ws.map(w=>{const sc=w.status==='pending'?'#fbbf24':w.status==='completed'?'#22c55e':'#f87171';return `<div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.05)"><div style="flex:1;font-size:12px"><span style="color:var(--w);font-weight:600">${w.points} pts</span><span style="color:var(--w3)"> → ${(w.points*1000000).toLocaleString()} coins · ${esc(w.mc_username)}</span></div><span style="font-size:9px;font-weight:800;padding:2px 7px;border-radius:4px;text-transform:uppercase;background:${sc}18;color:${sc};border:1px solid ${sc}33">${esc(w.status)}</span>${w.status==='pending'?`<button onclick="idxCancelWithdrawal(${w.id})" style="font-size:10px;padding:3px 9px;border-radius:5px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;cursor:pointer;font-family:var(--f)">Cancel</button>`:''}</div>`;}).join('');}catch(e){el.textContent='No withdrawals yet.';}}
async function idxCancelWithdrawal(id){const tok=localStorage.getItem('jl_token');if(!confirm('Cancel and refund?'))return;const r=await fetch(API_BASE+'/api/withdrawals.php?action=cancel',{method:'POST',headers:{'Content-Type':'application/json','Authorization':'Bearer '+tok},body:JSON.stringify({id})}).then(r=>r.json());if(r.error){showToast(r.error,'er');return;}showToast('Cancelled — points refunded!');loadNavPoints();loadIdxWithdrawals();}
function copyIdxRef(){const url=document.getElementById('idx-ref-url').value;if(!url)return;navigator.clipboard.writeText(url).then(()=>showToast('Link copied!'));}
function doLogout(){localStorage.removeItem('jl_token');document.getElementById('upill').classList.remove('on');document.getElementById('btn-li').style.display='';document.getElementById('btn-reg').style.display='';['btn-admin','btn-staff'].forEach(id=>{const el=document.getElementById(id);if(el)el.style.display='none';});const np=document.getElementById('nav-points');if(np)np.style.display='none';showToast('Signed out');}
let _tt;function showToast(m,t='ok'){const el=document.getElementById('toast');el.textContent=m;el.className='on '+t;clearTimeout(_tt);_tt=setTimeout(()=>el.className='',3500)}
// ─── ORBITAL TIMELINE ───
(function(){
  const features = [
    {id:1,title:'FPS Boost',desc:"Doesn't just launch Minecraft — it makes it faster. Built-in patches push your frames higher without touching mods.",status:'active',energy:95,relatedIds:[2,6],icon:'<polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'},
    {id:2,title:'Shaders',desc:'Drop any shader pack in and it works. No extra installs, no config files, no headaches whatsoever.',status:'active',energy:88,relatedIds:[1,3],icon:'<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>'},
    {id:3,title:'50K+ Mods',desc:'Modrinth is built right in. Search, install, and update mods without ever opening a browser tab.',status:'active',energy:100,relatedIds:[2,4],icon:'<circle cx="12" cy="12" r="10"/><path d="M8 12h8M12 8v8"/>'},
    {id:4,title:'Anti-Cheat',desc:"Play on any server without getting flagged. Justice doesn't touch what it shouldn't — period.",status:'active',energy:80,relatedIds:[3,5],icon:'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'},
    {id:5,title:'Auto-Updates',desc:'Justice stays current automatically. Mods, loader, launcher — all handled silently in the background.',status:'active',energy:75,relatedIds:[4,6],icon:'<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>'},
    {id:6,title:'Profiles',desc:'Separate profiles per version, per server, per mood. Switch in one click, zero conflicts, zero pain.',status:'active',energy:70,relatedIds:[1,5],icon:'<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'},
    {id:7,title:'Voice Chat',desc:'Proximity voice built right in. No server plugins, no separate app — talk to whoever is next to you in-game.',status:'soon',energy:35,relatedIds:[3,4],icon:'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.35 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.6a16 16 0 0 0 6 6z"/>'},
    {id:8,title:'Friends',desc:"See who's online, send messages, jump into servers together. All from the launcher — no Discord needed.",status:'active',energy:82,relatedIds:[7,6],icon:'<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'},
  ];

  const RADIUS = 300;
  const wrap = document.getElementById('orbital-wrap');
  const nodesEl = document.getElementById('orb-nodes');
  const detail = document.getElementById('orb-detail');
  const svgEl = document.getElementById('orb-svg');
  let angle = 0, autoRotate = true, activeId = null;
  let nodeEls = {}, nodePositions = {};

  // inject close button
  detail.querySelector('.orb-detail-top').insertAdjacentHTML('beforeend',
    `<button class="orb-close" onclick="orbClose()"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`
  );

  // build nodes
  features.forEach(f => {
    const node = document.createElement('div');
    node.className = 'orb-node';
    node.dataset.id = f.id;
    node.innerHTML = `
      <div class="orb-halo" style="width:${f.energy*.5+70}px;height:${f.energy*.5+70}px"></div>
      <div class="orb-dot"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${f.icon}</svg></div>
      <div class="orb-label" id="lbl-${f.id}">${f.title}</div>`;
    node.addEventListener('click', e => { e.stopPropagation(); toggleFeature(f.id); });
    nodesEl.appendChild(node);
    nodeEls[f.id] = node;
  });

  function getPos(index, total, curAngle) {
    const a = ((index / total) * 360 + curAngle) * Math.PI / 180;
    const cx = wrap.offsetWidth / 2, cy = wrap.offsetHeight / 2;
    return { x: cx + RADIUS * Math.cos(a), y: cy + RADIUS * Math.sin(a), a };
  }

  function positionNodes() {
    const total = features.length;
    const cx = wrap.offsetWidth / 2, cy = wrap.offsetHeight / 2;
    features.forEach((f, i) => {
      const {x, y, a} = getPos(i, total, angle);
      const el = nodeEls[f.id];
      const isActive = el.classList.contains('active');
      const depth = Math.sin(a);
      el.style.left = (x - 31) + 'px';
      el.style.top  = (y - 31) + 'px';
      el.style.opacity = isActive ? 1 : Math.max(0.3, 0.3 + 0.7 * ((1 + depth) / 2));
      el.style.zIndex = isActive ? 100 : Math.round(10 + 40 * ((1 + depth) / 2));
      // label centred below the dot
      const lbl = document.getElementById('lbl-'+f.id);
      lbl.style.left = '31px';
      lbl.style.top  = '72px';
      lbl.style.transform = 'translateX(-50%)';
      nodePositions[f.id] = {x, y};
    });
    drawLines();
  }

  function drawLines() {
    while(svgEl.firstChild) svgEl.removeChild(svgEl.firstChild);
    if(!activeId) return;
    const f = features.find(x => x.id === activeId);
    const from = nodePositions[activeId];
    if(!f||!from) return;
    f.relatedIds.forEach(rid => {
      const to = nodePositions[rid]; if(!to) return;
      const line = document.createElementNS('http://www.w3.org/2000/svg','line');
      line.setAttribute('x1',from.x); line.setAttribute('y1',from.y);
      line.setAttribute('x2',to.x);   line.setAttribute('y2',to.y);
      line.setAttribute('stroke','rgba(167,139,250,.3)');
      line.setAttribute('stroke-width','1.5');
      line.setAttribute('stroke-dasharray','5,4');
      svgEl.appendChild(line);
    });
  }

  function tick() {
    // very slow — ~0.07°/frame = ~85s per full orbit so labels are easily readable
    if(autoRotate) angle = (angle + 0.07) % 360;
    positionNodes();
    requestAnimationFrame(tick);
  }

  function toggleFeature(id) {
    if(activeId === id) { orbClose(); return; }
    activeId = id;
    autoRotate = false;
    const f = features.find(x => x.id === id);
    Object.values(nodeEls).forEach(el => el.classList.remove('active','related'));
    nodeEls[id].classList.add('active');
    f.relatedIds.forEach(rid => { if(nodeEls[rid]) nodeEls[rid].classList.add('related'); });

    // populate panel (centered via CSS)
    document.getElementById('orb-d-title').textContent = f.title;
    document.getElementById('orb-d-desc').textContent = f.desc;
    const statusEl = document.getElementById('orb-d-status');
    statusEl.textContent = f.status==='active' ? 'Active' : 'Coming Soon';
    statusEl.className = 'orb-detail-status '+(f.status==='active'?'status-active':'status-soon');
    document.getElementById('orb-d-energy-val').textContent = f.energy+'%';
    document.getElementById('orb-d-fill').style.width = f.energy+'%';
    const relDiv = document.getElementById('orb-d-related');
    if(f.relatedIds.length) {
      const rel = f.relatedIds.map(rid=>features.find(x=>x.id===rid)).filter(Boolean);
      relDiv.innerHTML = `<div class="orb-related-label">Connected</div><div class="orb-related-pills">${rel.map(r=>`<span class="orb-related-pill" onclick="orbJump(${r.id})">${r.title}</span>`).join('')}</div>`;
    } else relDiv.innerHTML='';

    detail.classList.add('visible');
    positionNodes();
  }

  window.orbJump = function(id){ toggleFeature(id); };
  window.orbClose = function(){
    activeId=null; autoRotate=true;
    Object.values(nodeEls).forEach(el=>el.classList.remove('active','related'));
    detail.classList.remove('visible');
    while(svgEl.firstChild) svgEl.removeChild(svgEl.firstChild);
  };

  wrap.addEventListener('click', e => {
    if(e.target===wrap||e.target.id==='orb-svg'||e.target.classList.contains('orb-ring')) orbClose();
  });

  tick();
})();

(async()=>{const tk=localStorage.getItem('jl_token');if(!tk)return;try{const r=await fetch(API_BASE+'/api/user.php?action=me',{headers:{Authorization:'Bearer '+tk}});const d=await r.json();if(d.user)setLoggedIn(d.user);else localStorage.removeItem('jl_token')}catch{}})();
</script>
</body>
</html>