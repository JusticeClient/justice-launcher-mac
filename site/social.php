<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Justice — Social</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#05030d;--s1:#0a0818;--s2:#0e0b20;--s3:#131028;--s4:#18153200;
  --line:rgba(255,255,255,.07);--line2:rgba(139,92,246,.22);
  --p:#7c3aed;--p2:#6d28d9;--pl:#a78bfa;--px:#c4b5fd;
  --pink:#e879f9;--green:#4ade80;--amber:#fbbf24;--red:#f87171;--teal:#2dd4bf;
  --w:#f5f3ff;--w2:rgba(245,243,255,.55);--w3:rgba(245,243,255,.25);--w4:rgba(245,243,255,.08);
  --f:'Inter',system-ui,sans-serif;--m:'JetBrains Mono',monospace;
}
html{height:100%}
body{font-family:var(--f);background:var(--bg);color:var(--w);height:100%;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased;padding-top:52px}

#topbar{
  position:fixed;top:0;left:0;right:0;z-index:100;
  height:52px;flex-shrink:0;display:flex;align-items:center;
  padding:0 20px;gap:16px;
  background:rgba(5,3,13,.8);backdrop-filter:blur(18px);
  border-bottom:1px solid var(--line);
}
.topbar-logo{display:flex;align-items:center;gap:8px;text-decoration:none;font-weight:700;font-size:14px;color:var(--w);letter-spacing:-.02em}
.tsq{width:24px;height:24px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;box-shadow:0 0 10px rgba(124,58,237,.4)}
.topbar-title{font-size:13.5px;font-weight:600;color:var(--w);margin-left:4px}
.topbar-back{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--w2);text-decoration:none;transition:color .12s}
.topbar-back:hover{color:var(--w)}
.topbar-user{display:flex;align-items:center;gap:8px;margin-left:auto;font-size:13px;font-weight:600;color:var(--w)}
.tav{width:24px;height:24px;border-radius:6px;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0}
.topbar-right{display:flex;align-items:center;gap:8px;margin-left:auto}

#login-wall{
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:12px;text-align:center;padding:40px;
  flex:1;
}
#login-wall h2{font-size:22px;font-weight:800;letter-spacing:-.04em}
#login-wall p{font-size:13px;color:var(--w3);max-width:320px;line-height:1.6}
.lw-form{display:flex;flex-direction:column;gap:10px;width:100%;max-width:300px}
.lw-input{padding:10px 13px;border-radius:9px;border:1px solid var(--line);background:var(--s2);color:var(--w);font-family:var(--f);font-size:13px;outline:none;transition:border .13s}
.lw-input:focus{border-color:var(--p)}
.lw-err{font-size:12px;color:var(--red);display:none;text-align:left}
.btn-full{width:100%;padding:11px;border-radius:9px;font-size:14px;font-weight:700;font-family:var(--f);cursor:pointer;border:none;transition:all .14s}
#wrapper{display:flex;flex-direction:column;flex:1;overflow:hidden}
.app{flex:1;display:flex;overflow:hidden;min-height:0}

.sidebar{
  width:260px;flex-shrink:0;
  background:var(--s1);border-right:1px solid var(--line);
  display:flex;flex-direction:column;overflow:hidden;
}
.sb-hdr{padding:16px 16px 10px;border-bottom:1px solid var(--line)}
.sb-hdr-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.sb-hdr-title{font-size:13px;font-weight:700;color:var(--w);letter-spacing:-.02em}
.sb-tabs{display:flex;gap:2px}
.stab{
  flex:1;padding:6px 0;border-radius:6px;border:none;
  background:transparent;color:var(--w3);font-family:var(--f);
  font-size:11.5px;font-weight:600;cursor:pointer;transition:all .12s;
  letter-spacing:-.01em;position:relative;
}
.stab.on{background:var(--w4);color:var(--w)}
.stab:hover:not(.on){color:var(--w2)}
.stab-badge{
  position:absolute;top:1px;right:3px;
  width:16px;height:16px;border-radius:8px;
  background:var(--red);color:#fff;
  font-size:9px;font-weight:700;
  display:flex;align-items:center;justify-content:center;
}
.sb-search{padding:10px 12px;border-bottom:1px solid var(--line)}
.sb-search-box{
  display:flex;align-items:center;gap:7px;
  background:var(--w4);border:1px solid var(--line);
  border-radius:7px;padding:6px 10px;transition:border .12s;
}
.sb-search-box:focus-within{border-color:var(--line2)}
.sb-search-box svg{color:var(--w3);flex-shrink:0}
.sb-search-box input{flex:1;border:none;background:transparent;color:var(--w);font-family:var(--f);font-size:12.5px;outline:none}
.sb-search-box input::placeholder{color:var(--w3)}
.sb-list{flex:1;overflow-y:auto;padding:6px 8px}
.sb-list::-webkit-scrollbar{width:3px}.sb-list::-webkit-scrollbar-thumb{background:var(--line2);border-radius:2px}
.sb-section{font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--w3);padding:8px 6px 4px}
.sb-item{
  display:flex;align-items:center;gap:9px;padding:7px 8px;border-radius:8px;
  cursor:pointer;transition:background .12s;border:1px solid transparent;
}
.sb-item:hover{background:var(--w4)}
.sb-item.on{background:rgba(139,92,246,.1);border-color:var(--line2)}
.sav{
  width:30px;height:30px;border-radius:8px;flex-shrink:0;
  background:linear-gradient(135deg,var(--p),var(--p2));
  display:flex;align-items:center;justify-content:center;
  font-size:12px;font-weight:700;color:#fff;position:relative;
}
.sdot{position:absolute;bottom:-1px;right:-1px;width:8px;height:8px;border-radius:50%;border:2px solid var(--s1)}
.sdot.online{background:var(--green)}.sdot.ingame{background:var(--amber)}.sdot.offline{background:var(--w3)}
.sadmin{font-size:9px;font-weight:800;padding:1px 5px;border-radius:4px;background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3);letter-spacing:.04em;text-transform:uppercase;vertical-align:middle;margin-left:4px;flex-shrink:0}
.sstaff{font-size:9px;font-weight:800;padding:1px 5px;border-radius:4px;background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3);letter-spacing:.04em;text-transform:uppercase;vertical-align:middle;margin-left:4px;flex-shrink:0}
.smedia{font-size:9px;font-weight:800;padding:1px 5px;border-radius:4px;background:rgba(192,132,252,.15);color:#c084fc;border:1px solid rgba(192,132,252,.3);letter-spacing:.04em;text-transform:uppercase;vertical-align:middle;margin-left:4px;flex-shrink:0}
.msg-read{font-size:9px;color:var(--p);margin-top:1px;padding:0 3px;text-align:right}
.msg-img{max-width:220px;max-height:220px;border-radius:8px;margin-top:4px;cursor:pointer;display:block}
.typing-indicator{display:none;align-items:center;gap:6px;padding:8px 18px;font-size:11px;color:var(--w3)}
.typing-indicator.on{display:flex}
.typing-dots{display:flex;gap:3px}
.typing-dot{width:5px;height:5px;border-radius:50%;background:var(--w3);animation:tdot 1.2s infinite}
.typing-dot:nth-child(2){animation-delay:.2s}.typing-dot:nth-child(3){animation-delay:.4s}
@keyframes tdot{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-4px)}}
.chat-input-wrap-inner{display:flex;align-items:flex-end;gap:9px;flex:1}
.img-btn{width:32px;height:32px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--w3);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;flex-shrink:0}
.img-btn:hover{border-color:var(--line2);color:var(--pl)}
.sinfo{flex:1;min-width:0}
.sname{font-size:12.5px;font-weight:600;color:var(--w);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;letter-spacing:-.01em}
.ssub{font-size:10.5px;color:var(--w3);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sunread{
  font-size:10px;font-weight:700;min-width:18px;height:16px;
  background:var(--p);color:#fff;border-radius:8px;
  display:flex;align-items:center;justify-content:center;padding:0 5px;flex-shrink:0;
}
.sb-add-btn{
  margin:8px;padding:8px;border-radius:8px;
  border:1px dashed var(--line);background:transparent;
  color:var(--w3);font-family:var(--f);font-size:12px;font-weight:500;
  cursor:pointer;transition:all .12s;display:flex;align-items:center;justify-content:center;gap:6px;
}
.sb-add-btn:hover{border-color:var(--line2);color:var(--pl);background:rgba(139,92,246,.05)}

.main{flex:1;display:flex;flex-direction:column;overflow:hidden;min-width:0}

.empty-state{
  flex:1;display:flex;flex-direction:column;
  align-items:center;justify-content:center;gap:12px;
  color:var(--w3);text-align:center;padding:40px;
}
.empty-state svg{opacity:.35}
.empty-state h3{font-size:16px;font-weight:700;color:var(--w2);letter-spacing:-.02em}
.empty-state p{font-size:13px;color:var(--w3);line-height:1.6;max-width:300px}

.chat-hdr{
  display:flex;align-items:center;gap:12px;padding:14px 20px;
  border-bottom:1px solid var(--line);flex-shrink:0;background:var(--s1);
}
.chat-hdr-av{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--p),var(--p2));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff;flex-shrink:0;position:relative}
.chat-hdr-name{font-size:14px;font-weight:700;letter-spacing:-.02em}
.chat-hdr-sub{font-size:11px;color:var(--w3);margin-top:1px}
.chat-hdr-actions{display:flex;gap:6px;margin-left:auto}
.icon-btn{
  width:30px;height:30px;border-radius:7px;border:1px solid var(--line);
  background:transparent;color:var(--w3);cursor:pointer;
  display:flex;align-items:center;justify-content:center;transition:all .12s;
}
.icon-btn:hover{border-color:var(--line2);color:var(--w);background:var(--w4)}

.chat-msgs{flex:1;overflow-y:auto;padding:16px 18px;display:flex;flex-direction:column;gap:3px}
.chat-msgs::-webkit-scrollbar{width:3px}.chat-msgs::-webkit-scrollbar-thumb{background:var(--line2);border-radius:2px}
.msg{display:flex;flex-direction:column;max-width:72%}
.msg.me{align-self:flex-end;align-items:flex-end}
.msg.them{align-self:flex-start}
.bubble{padding:8px 12px;border-radius:11px;font-size:13px;line-height:1.5;word-break:break-word;letter-spacing:-.01em}
.msg.me .bubble{background:var(--p);color:#fff;border-radius:11px 11px 3px 11px}
.msg.them .bubble{background:var(--s3);border:1px solid var(--line);color:var(--w);border-radius:11px 11px 11px 3px}
.msg-ts{font-size:10px;color:var(--w3);margin-top:3px;padding:0 3px}
.msg-date-divider{text-align:center;font-size:10.5px;color:var(--w3);padding:12px 0;letter-spacing:.02em}

.chat-input-wrap{
  display:flex;align-items:center;gap:9px;
  padding:12px 16px;border-top:1px solid var(--line);flex-shrink:0;
}
.chat-input{
  flex:1;padding:9px 13px;border-radius:9px;
  border:1px solid var(--line);background:var(--s2);
  color:var(--w);font-family:var(--f);font-size:13px;outline:none;
  transition:border .13s;letter-spacing:-.01em;resize:none;
  max-height:120px;overflow-y:auto;
}
.chat-input:focus{border-color:var(--line2)}
.chat-input::placeholder{color:var(--w3)}
.send-btn{
  width:34px;height:34px;border-radius:8px;border:none;
  background:var(--p);color:#fff;cursor:pointer;
  display:flex;align-items:center;justify-content:center;transition:all .13s;flex-shrink:0;
}
.send-btn:hover{background:var(--p2)}
.send-btn:disabled{opacity:.4;cursor:not-allowed}

.panel{flex:1;overflow-y:auto;padding:24px}
.panel::-webkit-scrollbar{width:3px}.panel::-webkit-scrollbar-thumb{background:var(--line2);border-radius:2px}
.panel-title{font-size:18px;font-weight:800;letter-spacing:-.03em;margin-bottom:4px}
.panel-sub{font-size:13px;color:var(--w2);margin-bottom:24px;line-height:1.55}

.req-card{
  background:var(--s2);border:1px solid var(--line);border-radius:12px;
  padding:16px;margin-bottom:10px;display:flex;align-items:flex-start;gap:12px;
}
.req-av{width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--p),var(--p2));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.req-body{flex:1;min-width:0}
.req-from{font-size:13px;font-weight:700;letter-spacing:-.02em;margin-bottom:4px}
.req-msg{font-size:12.5px;color:var(--w2);line-height:1.5;margin-bottom:10px}
.req-time{font-size:10.5px;color:var(--w3)}
.req-actions{display:flex;gap:7px;flex-shrink:0;flex-direction:column;align-items:flex-end}

.contact-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:10px;margin-top:4px}
.contact-card{
  background:var(--s2);border:1px solid var(--line);border-radius:12px;
  padding:16px;transition:all .14s;cursor:pointer;
}
.contact-card:hover{border-color:var(--line2);background:var(--s3)}
.contact-card-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.cav{width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,var(--p),var(--p2));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0;position:relative}
.contact-name{font-size:13.5px;font-weight:700;letter-spacing:-.02em}
.contact-username{font-size:11px;color:var(--w3);margin-top:2px}
.contact-nick-badge{font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;background:rgba(139,92,246,.15);border:1px solid var(--line2);color:var(--pl);margin-top:4px;display:inline-block}
.contact-actions{display:flex;gap:6px;margin-top:10px;padding-top:10px;border-top:1px solid var(--line)}

.setting-group{margin-bottom:28px}
.setting-group-label{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--w3);margin-bottom:12px}
.setting-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 16px;background:var(--s2);border:1px solid var(--line);
  border-radius:10px;margin-bottom:8px;gap:16px;
}
.setting-info h4{font-size:13.5px;font-weight:600;letter-spacing:-.01em;margin-bottom:3px}
.setting-info p{font-size:12px;color:var(--w3);line-height:1.5}
.toggle{
  width:42px;height:24px;border-radius:12px;
  background:var(--line);border:none;cursor:pointer;
  position:relative;transition:background .15s;flex-shrink:0;
}
.toggle.on{background:var(--p)}
.toggle::after{
  content:'';position:absolute;top:3px;left:3px;
  width:18px;height:18px;border-radius:9px;background:#fff;
  transition:left .15s;
}
.toggle.on::after{left:21px}

.ov{position:fixed;inset:0;z-index:999;background:rgba(5,3,13,.88);backdrop-filter:blur(18px);display:none;align-items:center;justify-content:center;padding:16px}
.ov.on{display:flex;animation:pop .15s ease}
@keyframes pop{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.mod{background:var(--s2);border:1px solid var(--line2);border-radius:14px;padding:28px;width:100%;max-width:420px;box-shadow:0 28px 60px rgba(0,0,0,.7);position:relative}
.mx{position:absolute;top:12px;right:12px;width:26px;height:26px;border-radius:6px;border:1px solid var(--line);background:transparent;color:var(--w3);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s}
.mx:hover{border-color:var(--line2);color:var(--w)}
.mod h3{font-size:18px;font-weight:800;letter-spacing:-.03em;margin-bottom:5px}
.mod-sub{font-size:13px;color:var(--w2);margin-bottom:20px;line-height:1.5}
.fld{margin-bottom:13px}
.fld label{display:block;font-size:10.5px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--w3);margin-bottom:6px}
.fld input,.fld textarea{width:100%;padding:9px 12px;border-radius:7px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--w);font-family:var(--f);font-size:13px;outline:none;transition:border .13s;box-sizing:border-box;letter-spacing:-.01em}
.fld textarea{resize:vertical;min-height:70px}
.fld input:focus,.fld textarea:focus{border-color:var(--line2);background:rgba(139,92,246,.06)}
.fld input::placeholder,.fld textarea::placeholder{color:var(--w3)}
.ferr{font-size:12px;color:var(--red);margin:-3px 0 10px;display:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:7px;border:none;font-family:var(--f);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .14s;white-space:nowrap;letter-spacing:-.01em}
.btn-p{background:var(--p);color:#fff;box-shadow:0 2px 10px rgba(124,58,237,.4)}
.btn-p:hover{background:var(--p2)}
.btn-line{background:transparent;border:1px solid var(--line);color:var(--w2)}
.btn-line:hover{border-color:var(--line2);color:var(--w)}
.btn-green{background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.25);color:var(--green)}
.btn-green:hover{background:rgba(74,222,128,.2)}
.btn-red{background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.25);color:var(--red)}
.btn-red:hover{background:rgba(248,113,113,.2)}
.btn-sm{padding:5px 11px;font-size:12px;border-radius:6px}
.btn-full{width:100%;justify-content:center;padding:10px}

.tag{font-size:9.5px;font-weight:700;padding:2px 8px;border-radius:4px;letter-spacing:.04em;text-transform:uppercase}
.tag-am{background:rgba(251,191,36,.1);color:var(--amber);border:1px solid rgba(251,191,36,.2)}

.lw-form{display:flex;flex-direction:column;gap:10px;width:100%;max-width:300px;margin-top:8px}
.lw-input{padding:10px 13px;border-radius:8px;border:1px solid var(--line);background:rgba(255,255,255,.04);color:var(--w);font-family:var(--f);font-size:13.5px;outline:none;transition:border .13s}
.lw-input:focus{border-color:var(--line2)}
.lw-input::placeholder{color:var(--w3)}
.lw-err{font-size:12px;color:var(--red);text-align:center;display:none}

@media(max-width:680px){
  .sidebar{width:200px}
  .sname{font-size:12px}
}
</style>
</head>
<body>

<div class="topbar" id="topbar">
  <a href="/" class="topbar-logo">
    <div class="tsq"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.95)" stroke-width="2.5"><path d="M12 2L22 7v10l-10 5L2 17V7z"/></svg></div>
    Justice
  </a>
  <a href="/" class="topbar-back">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Back to home
  </a>
  <div class="topbar-right">
    <div class="topbar-user" id="topbar-user" style="display:none">
      <div class="tav" id="topbar-av">?</div>
      <span id="topbar-uname">Player</span>
    </div>
    <div id="topbar-points" style="display:none;align-items:center;gap:6px;padding:4px 10px;border-radius:8px;background:rgba(124,58,237,.12);border:1px solid rgba(124,58,237,.25);cursor:pointer" onclick="showReferralModal()" title="Your Justice Points — click to get referral link">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <span id="topbar-pts" style="font-size:12px;font-weight:700;color:#a78bfa">0 pts</span>
    </div>
    <a href="/admin.php" class="btn btn-sm" id="topbar-admin" style="display:none;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.25);color:#f87171;text-decoration:none;align-items:center;gap:5px">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Admin
    </a>
    <a href="/staff.php" class="btn btn-sm" id="topbar-staff" style="display:none;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.25);color:#fbbf24;text-decoration:none;align-items:center;gap:5px">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Staff
    </a>
    <button class="btn btn-line btn-sm" id="topbar-logout" style="display:none" onclick="doLogout()">Sign out</button>
  </div>
</div>

<div id="wrapper">
<div id="login-wall" style="display:flex">
  <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity:.3"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
  <h2>Sign in to continue</h2>
  <p>Log in to your Justice account to see your friends, messages, contacts and privacy settings.</p>
  <div class="lw-form">
    <input class="lw-input" type="text" id="lw-l" placeholder="Username or email" autocomplete="username">
    <input class="lw-input" type="password" id="lw-p" placeholder="Password" autocomplete="current-password" onkeydown="if(event.key==='Enter')lwLogin()">
    <div class="lw-err" id="lw-err"></div>
    <button class="btn btn-p btn-full" id="lw-btn" onclick="lwLogin()">Log In</button>
    <div style="font-size:12px;color:var(--w3);text-align:center">No account? <a href="/" style="color:var(--pl)">Create one at justiceclient.org</a></div>
  </div>
</div>

<div class="app" id="app" style="display:none">

  
  <div class="sidebar">
    <div class="sb-hdr">
      <div class="sb-hdr-row">
        <div class="sb-hdr-title" id="sb-title">Friends</div>
      </div>
      <div class="sb-tabs">
        <button class="stab on" onclick="switchTab('friends',this)" id="tab-friends">Friends</button>
        <button class="stab" onclick="switchTab('requests',this)" id="tab-requests">
          Requests
          <span class="stab-badge" id="req-badge" style="display:none">0</span>
        </button>
        <button class="stab" onclick="switchTab('contacts',this)" id="tab-contacts">Contacts</button>
        <button class="stab" onclick="switchTab('settings',this)" id="tab-settings">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </button>
      </div>
    </div>
    <div class="sb-search" id="sb-search-wrap">
      <div class="sb-search-box">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="sb-q" placeholder="Search…" oninput="filterSidebar()">
      </div>
    </div>
    <div class="sb-list" id="sb-list"></div>
    <button class="sb-add-btn" id="sb-add-btn" onclick="openAddModal()">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      <span id="sb-add-label">Add Friend</span>
    </button>
  </div>

  
  <div class="main" id="main-panel">
    <div class="empty-state" id="empty-state">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      <h3>Select a conversation</h3>
      <p>Choose a friend from the sidebar to start chatting, or switch to Contacts to find someone.</p>
    </div>

    
    <div id="chat-view" style="display:none;flex-direction:column;flex:1;overflow:hidden">
      <div class="chat-hdr">
        <div class="chat-hdr-av" id="chat-av">?</div>
        <div>
          <div class="chat-hdr-name" id="chat-name">—</div>
          <div class="chat-hdr-sub" id="chat-sub">offline</div>
        </div>
        <div class="chat-hdr-actions">
          <button class="icon-btn" title="Edit nickname" onclick="openNicknameModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button class="icon-btn" title="Add to contacts" onclick="openContactModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
          </button>
          <button class="icon-btn" title="Remove friend" onclick="removeFriend()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="17" y1="8" x2="23" y2="8"/></svg>
          </button>
        </div>
      </div>
      <div class="chat-msgs" id="chat-msgs"></div>
      <div class="typing-indicator" id="typing-indicator">
        <div class="typing-dots"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>
        <span id="typing-name">Someone</span> is typing…
      </div>
      <div class="chat-input-wrap">
        <input type="file" id="img-input" accept="image/*" style="display:none" onchange="handleImageSelect(event)">
        <button class="img-btn" title="Send image" onclick="document.getElementById('img-input').click()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </button>
        <textarea class="chat-input" id="chat-in" placeholder="Message…" rows="1" onkeydown="onChatKey(event)" oninput="autoResize(this);onTyping()"></textarea>
        <button class="send-btn" onclick="sendMsg()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </div>
    </div>

    
    <div id="requests-view" style="display:none" class="panel">
      
      <div id="friend-reqs-section" style="display:none;margin-bottom:28px">
        <div class="panel-title" style="font-size:15px">Friend Requests</div>
        <div class="panel-sub" style="margin-bottom:12px">Players who want to be your friend.</div>
        <div id="friend-reqs-list"></div>
      </div>
      
      <div class="panel-title">Message Requests</div>
      <div class="panel-sub">People who aren't your friends yet have sent you a message. Accept to add them as a friend and start chatting.</div>
      <div id="requests-list"></div>
    </div>

    
    <div id="contacts-view" style="display:none" class="panel">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
          <div class="panel-title">Contacts</div>
          <div style="font-size:13px;color:var(--w2)">People you've saved with custom nicknames</div>
        </div>
        <button class="btn btn-line" onclick="openMsgRequestModal()">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Send Message Request
        </button>
      </div>
      <div class="contact-grid" id="contacts-grid"></div>
    </div>

    
    <div id="settings-view" style="display:none" class="panel">
      <div class="panel-title">Privacy &amp; Settings</div>
      <div class="panel-sub">Control who can reach you on Justice Launcher.</div>
      <div class="setting-group">
        <div class="setting-group-label">Privacy</div>
        <div class="setting-row">
          <div class="setting-info">
            <h4>Friend Requests</h4>
            <p>Allow other players to send you friend requests. Disable this to stop new requests.</p>
          </div>
          <button class="toggle on" id="tog-fr" onclick="toggleSetting('allowFriendRequests',this)"></button>
        </div>
        <div class="setting-row">
          <div class="setting-info">
            <h4>Message Requests</h4>
            <p>Allow non-friends to send you a message request. Disable to only receive messages from friends.</p>
          </div>
          <button class="toggle on" id="tog-mr" onclick="toggleSetting('allowMessageRequests',this)"></button>
        </div>
        <div class="setting-row">
          <div class="setting-info">
            <h4>Show Online Status</h4>
            <p>Let your friends see when you're online or in-game. Disable to appear offline to everyone.</p>
          </div>
          <button class="toggle on" id="tog-os" onclick="toggleSetting('showOnlineStatus',this)"></button>
        </div>
      </div>
      <div class="setting-group">
        <div class="setting-group-label">Account</div>
        <div class="setting-row">
          <div class="setting-info">
            <h4>Sign Out</h4>
            <p>Sign out of your Justice account on this device.</p>
          </div>
          <button class="btn btn-red" onclick="doLogout()">Sign Out</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="ov" id="ov-add">
  <div class="mod">
    <button class="mx" onclick="closeOv('ov-add')"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <h3 id="add-mod-title">Add Friend</h3>
    <p class="mod-sub" id="add-mod-sub">Search by username to send a friend request.</p>
    <div class="fld"><label>Username</label><input type="text" id="add-q" placeholder="their_username" oninput="searchUsers()"></div>
    <div id="add-results"></div>
  </div>
</div>

<div class="ov" id="ov-msgreq">
  <div class="mod">
    <button class="mx" onclick="closeOv('ov-msgreq')"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <h3>Send Message Request</h3>
    <p class="mod-sub">Send a message to someone who isn't your friend yet. They can accept to start chatting.</p>
    <div class="fld"><label>Username</label><input type="text" id="mr-user" placeholder="their_username"></div>
    <div class="fld"><label>Message</label><textarea id="mr-content" placeholder="Hey, I wanted to reach out…" maxlength="500"></textarea></div>
    <div class="ferr" id="mr-err"></div>
    <button class="btn btn-p btn-full" onclick="sendMsgRequest()">Send Request</button>
  </div>
</div>

<div class="ov" id="ov-contact">
  <div class="mod">
    <button class="mx" onclick="closeOv('ov-contact')"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <h3>Save Contact</h3>
    <p class="mod-sub">Give this person a nickname — it'll show everywhere instead of their username.</p>
    <div class="fld"><label>Nickname <span style="color:var(--w3);font-weight:400;letter-spacing:0;font-size:10.5px">(optional)</span></label><input type="text" id="c-nick" placeholder="e.g. My Buddy" maxlength="40"></div>
    <div class="fld"><label>Notes <span style="color:var(--w3);font-weight:400;letter-spacing:0;font-size:10.5px">(optional)</span></label><textarea id="c-notes" placeholder="Any notes about this person…" maxlength="500"></textarea></div>
    <input type="hidden" id="c-uid">
    <div class="ferr" id="c-err"></div>
    <div style="display:flex;gap:8px;margin-top:4px">
      <button class="btn btn-p" style="flex:1;justify-content:center" onclick="saveContact()">Save</button>
      <button class="btn btn-red" onclick="removeContact()">Remove Contact</button>
    </div>
  </div>
</div>
</div>

<div id="toast"></div>

<div class="ov" id="ov-referral" onclick="if(event.target===this)this.classList.remove('on')">
  <div class="mod" style="max-width:420px" onclick="event.stopPropagation()">
    <button class="mx" onclick="document.getElementById('ov-referral').classList.remove('on')"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    <div style="text-align:center;margin-bottom:20px">
      <div style="font-size:28px;margin-bottom:8px">⭐</div>
      <h3 style="font-size:18px;font-weight:800;margin-bottom:4px">Justice Points</h3>
      <p class="msub">Earn 10 points per friend you refer. Points = DonutSMP coins.</p>
    </div>
    <div style="background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);border-radius:10px;padding:14px;margin-bottom:16px;text-align:center">
      <div style="font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#a78bfa;margin-bottom:6px">Your Balance</div>
      <div id="ref-modal-pts" style="font-size:36px;font-weight:900;color:#f1f5f9">—</div>
      <div style="font-size:12px;color:#a78bfa;margin-top:2px" id="ref-modal-coins">pts</div>
    </div>
    <div style="margin-bottom:12px">
      <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(245,243,255,.25);margin-bottom:6px">Your Referral Link</div>
      <div style="display:flex;gap:8px">
        <input id="ref-modal-url" type="text" readonly style="flex:1;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(245,243,255,.55);font-size:12px;font-family:monospace;outline:none" value="Loading…">
        <button onclick="copyRefUrl()" style="padding:9px 16px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:'Inter',sans-serif;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap">Copy</button>
      </div>
      <div style="font-size:11px;color:rgba(245,243,255,.25);margin-top:8px">Share this link — earn <strong style="color:#a78bfa">10 points</strong> for every unique friend who signs up. 1 pt = 1,000,000 DonutSMP coins.</div>
    </div>
    <div>
      <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(245,243,255,.25);margin-bottom:8px">Who You've Referred</div>
      <div id="ref-modal-list" style="font-size:12px;color:rgba(245,243,255,.25)">Loading…</div>
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,.07)">
      <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(245,243,255,.25);margin-bottom:10px">Withdraw Points → DonutSMP Coins</div>
      <div style="display:flex;gap:8px;margin-bottom:8px;flex-wrap:wrap">
        <input id="soc-wd-pts" type="number" min="1" placeholder="Points" oninput="updateSocWdPreview()" style="width:100px;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:var(--w);font-family:var(--f);font-size:12px;outline:none">
        <input id="soc-wd-mc" type="text" placeholder="Your Minecraft username" style="flex:1;min-width:140px;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:var(--w);font-family:var(--f);font-size:12px;outline:none">
      </div>
      <div id="soc-wd-preview" style="font-size:11px;color:#a78bfa;margin-bottom:8px;display:none"></div>
      <button onclick="socRequestWithdrawal()" style="width:100%;padding:9px;border-radius:8px;border:none;background:#7c3aed;color:#fff;font-family:var(--f);font-size:12px;font-weight:700;cursor:pointer">Request Withdrawal</button>
      <div style="margin-top:12px">
        <div style="font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:rgba(245,243,255,.25);margin-bottom:8px">My Withdrawals</div>
        <div id="soc-wd-list" style="font-size:12px;color:rgba(245,243,255,.25)">Loading…</div>
      </div>
    </div>
  </div>
</div>

<script>
const API = '';
let token = localStorage.getItem('jl_token') || null;
let me = null;
let friends = [], contacts = [], requests = [], incomingFriendReqs = [];
let activeChat = null;
let activeTab = 'friends';
let pollTimer = null;

async function init() {
  if (!token) { showWall(); return; }
  try {
    const r = await api('/api/user.php?action=me');
    if (r.user) { me = r.user; showApp(); }
    else { token = null; localStorage.removeItem('jl_token'); showWall(); }
  } catch { showWall(); }
}

function showWall() {
  document.getElementById('login-wall').style.display = 'flex';
}

async function lwLogin() {
  const l = document.getElementById('lw-l').value.trim();
  const p = document.getElementById('lw-p').value;
  const err = document.getElementById('lw-err');
  const btn = document.getElementById('lw-btn');
  err.style.display = 'none';
  if (!l || !p) { err.textContent = 'Fill in all fields'; err.style.display = 'block'; return; }
  btn.disabled = true; btn.textContent = 'Logging in…';
  try {
    const r = await fetch(API + '/api/login.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({login:l,password:p}) });
    const d = await r.json();
    if (d.error) { err.textContent = d.error; err.style.display = 'block'; }
    else { token = d.token; localStorage.setItem('jl_token',token); me = d.user; document.getElementById('login-wall').style.display = 'none'; showApp(); }
  } catch { err.textContent = 'Could not connect'; err.style.display = 'block'; }
  btn.disabled = false; btn.textContent = 'Log In';
}

async function loadTopbarPoints() {
  try {
    const r = await api('/api/points.php?action=balance');
    const pts = r.points || 0;
    const el = document.getElementById('topbar-pts');
    if (el) el.textContent = pts.toLocaleString() + ' pts';
    const wrap = document.getElementById('topbar-points');
    if (wrap) wrap.style.display = 'flex';
    window._refUrl  = r.referral_url || '';
    window._refPts  = pts;
    window._refCoins = r.donut_value || '';
  } catch(e) {}
}

async function showReferralModal() {
  document.getElementById('ov-referral').classList.add('on');
  document.getElementById('ref-modal-pts').textContent  = (window._refPts || 0).toLocaleString();
  document.getElementById('ref-modal-coins').textContent = window._refCoins || 'pts';
  document.getElementById('ref-modal-url').value = window._refUrl || 'Loading…';
  if (me && me.mcUsername) {
    const mcEl = document.getElementById('soc-wd-mc');
    if (mcEl && !mcEl.value) mcEl.value = me.mcUsername;
  }
  try {
    const r = await api('/api/points.php?action=referrals');
    const refs = r.referrals || [];
    const el   = document.getElementById('ref-modal-list');
    if (!refs.length) {
      el.innerHTML = '<div style="padding:8px 0">No referrals yet — share your link!</div>';
    } else {
      el.innerHTML = refs.map(u => `<div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04)"><span>${esc(u.username)}</span><span style="color:#4ade80">+10 pts · ${new Date(u.created_at).toLocaleDateString()}</span></div>`).join('');
    }
  } catch(e) {}
  loadSocWithdrawals();
}

function updateSocWdPreview() {
  const pts = parseInt(document.getElementById('soc-wd-pts').value || 0);
  const el  = document.getElementById('soc-wd-preview');
  if (pts > 0) { el.style.display='block'; el.textContent = pts + ' pts = ' + (pts*1000000).toLocaleString() + ' DonutSMP coins'; }
  else el.style.display = 'none';
}

async function socRequestWithdrawal() {
  const pts = parseInt(document.getElementById('soc-wd-pts').value || 0);
  const mc  = document.getElementById('soc-wd-mc').value.trim();
  if (!pts || pts < 1) { showToast('Enter points amount'); return; }
  if (!mc) { showToast('Enter your Minecraft username'); return; }
  const r = await api('/api/withdrawals.php?action=request', { method:'POST', body:JSON.stringify({points:pts,mc_username:mc}) });
  if (r.error) { showToast(r.error); return; }
  showToast('Withdrawal requested! Admins will process it soon.');
  document.getElementById('soc-wd-pts').value = '';
  loadTopbarPoints();
  loadSocWithdrawals();
}

async function loadSocWithdrawals() {
  const el = document.getElementById('soc-wd-list');
  if (!el) return;
  try {
    const r  = await api('/api/withdrawals.php?action=list');
    const ws = r.withdrawals || [];
    if (!ws.length) { el.innerHTML = '<div>No withdrawals yet.</div>'; return; }
    el.innerHTML = ws.map(w => {
      const sc = w.status==='pending'?'#fbbf24':w.status==='completed'?'#4ade80':'#f87171';
      return `<div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid rgba(255,255,255,.04);flex-wrap:wrap">
        <div style="flex:1;min-width:0;font-size:11px">
          <span style="color:var(--w)">${w.points} pts</span> → <span style="color:#fbbf24">${(w.points*1000000).toLocaleString()} coins</span>
          <span style="color:rgba(245,243,255,.25)"> · ${esc(w.mc_username)}</span>
        </div>
        <span style="font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;background:${sc}22;color:${sc};border:1px solid ${sc}44">${esc(w.status)}</span>
        ${w.status==='pending'?`<button onclick="socCancelWithdrawal(${w.id})" style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid rgba(248,113,113,.3);background:transparent;color:#f87171;cursor:pointer;font-family:var(--f)">Cancel</button>`:''}
      </div>`;
    }).join('');
  } catch(e) { el.innerHTML = '<div>No withdrawals yet.</div>'; }
}

async function socCancelWithdrawal(id) {
  if (!confirm('Cancel and refund this withdrawal?')) return;
  const r = await api('/api/withdrawals.php?action=cancel', { method:'POST', body:JSON.stringify({id}) });
  if (r.error) { showToast(r.error); return; }
  showToast('Cancelled — points refunded!');
  loadTopbarPoints();
  loadSocWithdrawals();
}

function copyRefUrl() {
  const url = document.getElementById('ref-modal-url').value;
  if (!url || url === 'Loading…') return;
  navigator.clipboard.writeText(url).then(() => {
    showToast('Referral link copied!');
  });
}

function doLogout() {
  token = null; localStorage.removeItem('jl_token');
  location.reload();
}

function showApp() {
  document.getElementById('login-wall').style.display = 'none';
  document.getElementById('app').style.display = 'flex';
  document.getElementById('topbar-user').style.display = 'flex';
  document.getElementById('topbar-logout').style.display = '';
  document.getElementById('topbar-av').textContent = (me.username||'?')[0].toUpperCase();
  document.getElementById('topbar-uname').textContent = me.username;
  loadTopbarPoints();
  if (me.isAdmin) {
    const adminBtn = document.getElementById('topbar-admin');
    adminBtn.style.display = 'inline-flex';
  }
  if (me.isStaff) {
    const staffBtn = document.getElementById('topbar-staff');
    if (staffBtn) staffBtn.style.display = 'inline-flex';
  }
  setToggleUI('tog-fr', me.allowFriendRequests !== false);
  setToggleUI('tog-mr', me.allowMsgRequests !== false);
  setToggleUI('tog-os', me.showOnlineStatus !== false);
  loadAll();
  startPoll();
}

async function loadAll() {
  await Promise.all([loadFriends(), loadContacts(), loadRequests()]);
  renderSidebar();
}

async function loadFriends() {
  try {
    const r = await api('/api/friends.php?action=list');
    friends = r.friends || [];
    incomingFriendReqs = r.incoming || [];
    renderPendingIncoming(incomingFriendReqs);
  } catch {}
}

async function loadContacts() {
  try { const r = await api('/api/contacts.php?action=list'); contacts = r.contacts || []; } catch {}
}

async function loadRequests() {
  try {
    const r = await api('/api/requests.php?action=list');
    requests = r.requests || [];
    updateReqBadge();
  } catch {}
}

function switchTab(tab, el) {
  activeTab = tab;
  document.querySelectorAll('.stab').forEach(t => t.classList.remove('on'));
  el.classList.add('on');
  document.getElementById('sb-search-wrap').style.display = ['friends','contacts'].includes(tab) ? '' : 'none';
  document.getElementById('sb-add-btn').style.display = ['friends','contacts'].includes(tab) ? '' : 'none';
  document.getElementById('sb-add-label').textContent = tab === 'contacts' ? 'Send Message Request' : 'Add Friend';
  renderSidebar();
  ['chat-view','requests-view','contacts-view','settings-view','empty-state'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  if (tab === 'requests') { document.getElementById('requests-view').style.display = 'block'; renderRequests(); }
  else if (tab === 'contacts') { document.getElementById('contacts-view').style.display = 'block'; renderContacts(); }
  else if (tab === 'settings') { document.getElementById('settings-view').style.display = 'block'; }
  else { document.getElementById('empty-state').style.display = 'flex'; activeChat = null; }
}

function renderSidebar() {
  const q = (document.getElementById('sb-q')?.value || '').toLowerCase();
  const list = document.getElementById('sb-list');
  if (activeTab === 'settings') { list.innerHTML = ''; return; }
  if (activeTab === 'requests') { list.innerHTML = ''; return; }

  if (activeTab === 'contacts') {
    const filtered = contacts.filter(c => !q || (c.displayName||'').toLowerCase().includes(q) || (c.username||'').toLowerCase().includes(q));
    list.innerHTML = filtered.length ? filtered.map(c => `
      <div class="sb-item${activeChat?.id===c.id?' on':''}" onclick="openChat(${c.id})">
        <div class="sav">${(c.displayName||c.username||'?')[0].toUpperCase()}</div>
        <div class="sinfo">
          <div class="sname">${esc(c.displayName||c.username)}</div>
          ${c.nickname ? `<div class="ssub">@${esc(c.username)}</div>` : '<div class="ssub">Contact</div>'}
        </div>
      </div>`) .join('') : '<div class="sb-section">No contacts yet</div>';
    return;
  }

  const online  = friends.filter(f => f.status !== 'offline' && (!q || (f.nickname||f.username).toLowerCase().includes(q)));
  const offline = friends.filter(f => f.status === 'offline'  && (!q || (f.nickname||f.username).toLowerCase().includes(q)));

  let html = '';
  if (online.length) {
    html += `<div class="sb-section">Online — ${online.length}</div>`;
    html += online.map(f => friendItem(f)).join('');
  }
  if (offline.length) {
    html += `<div class="sb-section">Offline — ${offline.length}</div>`;
    html += offline.map(f => friendItem(f)).join('');
  }
  if (!online.length && !offline.length) html = '<div class="sb-section" style="text-align:center;padding:24px 0">No friends yet</div>';
  list.innerHTML = html;
}

function friendItem(f) {
  const name       = f.nickname || f.username;
  const dot        = f.status === 'in-game' ? 'ingame' : f.status === 'online' ? 'online' : 'offline';
  const sub        = f.status === 'in-game' ? `Playing ${f.gameVersion||'Minecraft'}` : f.status;
  const unread     = f._unread > 0 ? `<div class="sunread">${f._unread}</div>` : '';
  const adminBadge = f.role === 'admin' ? `<span class="sadmin">Admin</span>` : '';
  const staffBadge = f.role === 'staff' ? `<span class="sstaff">Staff</span>` : '';
  const mediaBadge = f.role === 'media' ? `<span class="smedia">Media</span>` : '';
  return `<div class="sb-item${activeChat?.id===f.id?' on':''}" onclick="openChat(${f.id})">
    <div class="sav">${name[0].toUpperCase()}<div class="sdot ${dot}"></div></div>
    <div class="sinfo"><div class="sname">${esc(name)}${adminBadge}${staffBadge}${mediaBadge}</div><div class="ssub">${esc(sub)}</div></div>
    ${unread}
  </div>`;
}

function filterSidebar() { renderSidebar(); }

function renderPendingIncoming(inc) {
  const section = document.getElementById('friend-reqs-section');
  const list    = document.getElementById('friend-reqs-list');
  if (!section || !list) return;
  if (!inc || !inc.length) { section.style.display = 'none'; updateReqBadge(); return; }
  section.style.display = '';
  list.innerHTML = inc.map(f => `
    <div class="req-card">
      <div class="req-av">${(f.username||'?')[0].toUpperCase()}</div>
      <div class="req-body">
        <div class="req-from">${esc(f.username)}</div>
        <div class="req-msg" style="color:var(--w3)">Wants to be your friend</div>
        <div class="req-time">${f.createdAt ? new Date(f.createdAt).toLocaleDateString() : ''}</div>
      </div>
      <div class="req-actions">
        <button class="btn btn-green btn-sm" onclick="acceptFriendReq(${f.id})">Accept</button>
        <button class="btn btn-red btn-sm" onclick="declineFriendReq(${f.id})">Decline</button>
      </div>
    </div>`).join('');
  updateReqBadge();
}

function updateReqBadge() {
  const total = (incomingFriendReqs||[]).length + (requests||[]).length;
  const badge = document.getElementById('req-badge');
  if (!badge) return;
  badge.textContent = total;
  badge.style.display = total ? 'flex' : 'none';
}

async function acceptFriendReq(userId) {
  try {
    await api('/api/friends.php?action=accept', { method:'POST', body: JSON.stringify({userId}) });
    toast('Friend added!');
    await loadFriends();
    renderSidebar();
    if (activeTab === 'requests') renderRequests();
  } catch { toast('Failed to accept','er'); }
}

async function declineFriendReq(userId) {
  try {
    await api('/api/friends.php?action=decline', { method:'POST', body: JSON.stringify({userId}) });
    incomingFriendReqs = incomingFriendReqs.filter(f => f.id !== userId);
    renderPendingIncoming(incomingFriendReqs);
    if (activeTab === 'requests') renderRequests();
  } catch { toast('Failed','er'); }
}

async function openChat(userId) {
  const f = [...friends, ...contacts].find(x => x.id === userId || x.user_id === userId);
  if (!f) return;

  activeChat = f;
  f._unread = 0;
  renderSidebar();

  const name = f.nickname || f.displayName || f.username;
  document.getElementById('chat-av').textContent = name[0].toUpperCase();
  document.getElementById('chat-name').textContent = name;
  document.getElementById('chat-sub').textContent = f.status === 'in-game' ? `Playing ${f.gameVersion||'Minecraft'}` : (f.status || 'offline');

  ['empty-state','requests-view','contacts-view','settings-view'].forEach(id => {
    const el = document.getElementById(id); if(el) el.style.display='none';
  });
  const cv = document.getElementById('chat-view');
  cv.style.display = 'flex';

  const msgs = document.getElementById('chat-msgs');
  msgs.innerHTML = '<div class="msg-date-divider">Loading…</div>';
  try {
    const r = await api(`/api/messages.php?userId=${userId}&limit=60`);
    msgs.innerHTML = '';
    (r.messages || []).forEach(m => appendMsg(m.from_id == me.id, m.content, m.created_at, true, m.id, m.image_url, !!m.read_at));
    msgs.scrollTop = msgs.scrollHeight;
  } catch {
    msgs.innerHTML = '<div class="msg-date-divider">Could not load messages</div>';
  }
  document.getElementById('chat-in').focus();
}

function appendMsg(isMine, content, ts, skipScroll, msgId, imageUrl, isRead) {
  const msgs = document.getElementById('chat-msgs');
  if (!msgs) return;
  const d = document.createElement('div');
  d.className = 'msg ' + (isMine ? 'me' : 'them');
  d.dataset.ts = ts;
  if (msgId) d.dataset.id = msgId;
  const time = ts ? new Date(ts).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'}) : '';
  let inner = '';
  if (imageUrl) {
    inner += `<img class="msg-img" src="${esc(imageUrl)}" onclick="window.open('${esc(imageUrl)}','_blank')" loading="lazy">`;
  }
  if (content) inner += `<div class="bubble">${esc(content)}</div>`;
  inner += `<div class="msg-ts">${time}</div>`;
  if (isMine) inner += `<div class="msg-read" id="read-${msgId}">${isRead ? '✓✓ Seen' : '✓ Sent'}</div>`;
  d.innerHTML = inner;
  msgs.appendChild(d);
  if (!skipScroll) msgs.scrollTop = msgs.scrollHeight;
}

function updateReadReceipts(messages) {
  messages.forEach(m => {
    if (m.from_id == me.id && m.read_at) {
      const el = document.getElementById('read-' + m.id);
      if (el && el.textContent !== '✓✓ Seen') el.textContent = '✓✓ Seen';
    }
  });
}

let typingTimer = null;
async function onTyping() {
  if (!activeChat) return;
  clearTimeout(typingTimer);
  try { await api(`/api/messages.php?action=typing&userId=${activeChat.id}`, { method:'POST', body: JSON.stringify({}) }); } catch {}
  typingTimer = setTimeout(() => {}, 3000);
}

async function handleImageSelect(event) {
  const file = event.target.files[0];
  if (!file || !activeChat) return;
  if (file.size > 5 * 1024 * 1024) { toast('Image must be under 5MB', 'er'); return; }
  const reader = new FileReader();
  reader.onload = async (e) => {
    const dataUrl = e.target.result;
    const msgs = document.getElementById('chat-msgs');
    const d = document.createElement('div');
    d.className = 'msg me';
    d.dataset.ts = new Date().toISOString();
    d.innerHTML = `<img class="msg-img" src="${dataUrl}"><div class="msg-ts">${new Date().toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}</div><div class="msg-read">✓ Sending…</div>`;
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
    try {
      const r = await api(`/api/messages.php?userId=${activeChat.id}`, { method:'POST', body: JSON.stringify({ content: '', imageUrl: dataUrl.substring(0, 500) }) });
      if (r.message) { d.dataset.id = r.message.id; d.querySelector('.msg-read').textContent = '✓ Sent'; }
    } catch { d.style.opacity = '0.5'; toast('Image send failed', 'er'); }
  };
  reader.readAsDataURL(file);
  event.target.value = '';
}

async function sendMsg() {
  if (!activeChat) return;
  const input = document.getElementById('chat-in');
  const content = input.value.trim();
  if (!content) return;
  input.value = ''; input.style.height = '';
  const msgs = document.getElementById('chat-msgs');
  const d = document.createElement('div');
  d.className = 'msg me';
  d.dataset.ts = new Date().toISOString();
  const time = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
  d.innerHTML = `<div class="bubble">${esc(content)}</div><div class="msg-ts">${time}</div><div class="msg-read">✓ Sending…</div>`;
  msgs.appendChild(d);
  msgs.scrollTop = msgs.scrollHeight;
  try {
    const r = await api(`/api/messages.php?userId=${activeChat.id}`, { method:'POST', body: JSON.stringify({content}) });
    if (r.message) { d.dataset.id = r.message.id; d.querySelector('.msg-read').textContent = '✓ Sent'; }
  } catch { d.style.opacity = '0.5'; toast('Failed to send', 'er'); }
}

function onChatKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
}

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function renderRequests() {
  const list = document.getElementById('requests-list');
  if (!requests.length) {
    list.innerHTML = '<div style="text-align:center;padding:24px 0;color:var(--w3);font-size:13px">No pending message requests</div>';
  } else {
    list.innerHTML = requests.map(r => `
    <div class="req-card">
      <div class="req-av">${(r.username||'?')[0].toUpperCase()}</div>
      <div class="req-body">
        <div class="req-from">${esc(r.username)}</div>
        <div class="req-msg">${esc(r.content)}</div>
        <div class="req-time">${new Date(r.created_at).toLocaleDateString()}</div>
      </div>
      <div class="req-actions">
        <button class="btn btn-green btn-sm" onclick="acceptRequest(${r.id},${r.from_id})">Accept</button>
        <button class="btn btn-red btn-sm" onclick="declineRequest(${r.id})">Decline</button>
      </div>
    </div>`).join('');
  }
  renderPendingIncoming(incomingFriendReqs);
}

async function acceptRequest(rid, fromId) {
  try {
    await api('/api/requests.php?action=accept', { method:'POST', body: JSON.stringify({requestId: rid}) });
    toast('Friend added!');
    await loadAll();
    renderRequests();
    renderSidebar();
  } catch { toast('Failed','er'); }
}

async function declineRequest(rid) {
  try {
    await api('/api/requests.php?action=decline', { method:'POST', body: JSON.stringify({requestId: rid}) });
    requests = requests.filter(r => r.id !== rid);
    toast('Request declined');
    renderRequests();
    updateReqBadge();
  } catch { toast('Failed','er'); }
}

function renderContacts() {
  const grid = document.getElementById('contacts-grid');
  if (!contacts.length) {
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:48px 0;color:var(--w3);font-size:13px">No contacts saved yet.<br>Open a chat and click the save icon to add someone.</div>';
    return;
  }
  grid.innerHTML = contacts.map(c => `
    <div class="contact-card" onclick="openChat(${c.id})">
      <div class="contact-card-top">
        <div class="cav">${(c.displayName||c.username||'?')[0].toUpperCase()}</div>
        <div>
          <div class="contact-name">${esc(c.displayName||c.username)}</div>
          <div class="contact-username">@${esc(c.username)}</div>
          ${c.nickname ? `<div class="contact-nick-badge">Nickname: ${esc(c.nickname)}</div>` : ''}
        </div>
      </div>
      ${c.notes ? `<div style="font-size:12px;color:var(--w2);line-height:1.5;margin-bottom:10px">${esc(c.notes)}</div>` : ''}
      <div class="contact-actions">
        <button class="btn btn-line btn-sm" onclick="event.stopPropagation();editContact(${c.id})">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        <button class="btn btn-line btn-sm" onclick="event.stopPropagation();openChat(${c.id})">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Message
        </button>
      </div>
    </div>`).join('');
}

function editContact(uid) {
  const c = contacts.find(x => x.id === uid);
  if (!c) return;
  document.getElementById('c-uid').value = uid;
  document.getElementById('c-nick').value = c.nickname || '';
  document.getElementById('c-notes').value = c.notes || '';
  openOv('ov-contact');
}

let _srchTimer = null;
function openAddModal() {
  if (activeTab === 'contacts') { openMsgRequestModal(); return; }
  document.getElementById('add-mod-title').textContent = 'Add Friend';
  document.getElementById('add-mod-sub').textContent = 'Search by username to send a friend request.';
  document.getElementById('add-q').value = '';
  document.getElementById('add-results').innerHTML = '';
  openOv('ov-add');
}

function searchUsers() {
  clearTimeout(_srchTimer);
  _srchTimer = setTimeout(async () => {
    const q = document.getElementById('add-q').value.trim();
    const res = document.getElementById('add-results');
    if (q.length < 2) { res.innerHTML = ''; return; }
    try {
      const r = await api(`/api/user.php?action=search&q=${encodeURIComponent(q)}`);
      res.innerHTML = (r.users||[]).map(u => `
        <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;background:var(--s3);border:1px solid var(--line);border-radius:9px;margin-top:6px">
          <div class="sav" style="width:28px;height:28px;font-size:11px">${(u.username||'?')[0].toUpperCase()}</div>
          <div style="flex:1;font-size:13px;font-weight:600">${esc(u.username)}</div>
          ${!u.allowFriendRequests ? '<span class="tag tag-am" style="font-size:9px">Requests off</span>' : ''}
          <button class="btn btn-line btn-sm" id="af-${u.id}" onclick="sendFriendReq('${esc(u.username)}',${u.id})">${u.allowFriendRequests?'Add':'Can\'t Add'}</button>
        </div>`).join('') || '<div style="padding:12px;text-align:center;color:var(--w3);font-size:13px">No users found</div>';
    } catch {}
  }, 280);
}

async function sendFriendReq(username, uid) {
  try {
    const r = await api('/api/friends.php?action=request', { method:'POST', body: JSON.stringify({username}) });
    if (r.error) { toast(r.error,'er'); return; }
    const btn = document.getElementById('af-'+uid);
    if (btn) { btn.textContent = 'Sent ✓'; btn.disabled = true; }
    toast('Friend request sent to ' + username);
  } catch { toast('Failed to send','er'); }
}

async function removeFriend() {
  if (!activeChat || !confirm(`Remove ${activeChat.nickname||activeChat.username} as a friend?`)) return;
  try {
    await api(`/api/friends.php?action=remove&userId=${activeChat.id}`, { method:'DELETE' });
    toast('Friend removed');
    activeChat = null;
    await loadFriends();
    renderSidebar();
    document.getElementById('chat-view').style.display = 'none';
    document.getElementById('empty-state').style.display = 'flex';
  } catch { toast('Failed','er'); }
}

function openMsgRequestModal() {
  document.getElementById('mr-user').value = '';
  document.getElementById('mr-content').value = '';
  document.getElementById('mr-err').style.display = 'none';
  openOv('ov-msgreq');
}

async function sendMsgRequest() {
  const username = document.getElementById('mr-user').value.trim();
  const content  = document.getElementById('mr-content').value.trim();
  const err      = document.getElementById('mr-err');
  err.style.display = 'none';
  if (!username || !content) { err.textContent = 'Fill in all fields'; err.style.display = 'block'; return; }
  try {
    const r = await api('/api/requests.php?action=send', { method:'POST', body: JSON.stringify({username,content}) });
    if (r.error) { err.textContent = r.error; err.style.display = 'block'; return; }
    closeOv('ov-msgreq');
    toast('Message request sent to ' + username);
  } catch { err.textContent = 'Could not connect'; err.style.display = 'block'; }
}

function openNicknameModal() {
  if (!activeChat) return;
  const existing = contacts.find(c => c.id === activeChat.id);
  document.getElementById('c-uid').value   = activeChat.id;
  document.getElementById('c-nick').value  = existing?.nickname || activeChat.nickname || '';
  document.getElementById('c-notes').value = existing?.notes || '';
  document.getElementById('c-err').style.display = 'none';
  openOv('ov-contact');
}

function openContactModal() { openNicknameModal(); }

async function saveContact() {
  const uid   = parseInt(document.getElementById('c-uid').value);
  const nick  = document.getElementById('c-nick').value.trim();
  const notes = document.getElementById('c-notes').value.trim();
  const err   = document.getElementById('c-err');
  err.style.display = 'none';
  try {
    const r = await api('/api/contacts.php?action=save', { method:'POST', body: JSON.stringify({userId:uid,nickname:nick,notes}) });
    if (r.error) { err.textContent = r.error; err.style.display = 'block'; return; }
    closeOv('ov-contact');
    await loadContacts();
    await loadFriends();
    renderSidebar();
    toast('Contact saved');
    if (activeChat && activeChat.id === uid && nick) {
      document.getElementById('chat-name').textContent = nick;
    }
  } catch { err.textContent = 'Failed to save'; err.style.display = 'block'; }
}

async function removeContact() {
  const uid = parseInt(document.getElementById('c-uid').value);
  if (!confirm('Remove this contact? The nickname will be deleted.')) return;
  try {
    await api(`/api/contacts.php?action=remove&userId=${uid}`, { method:'DELETE' });
    closeOv('ov-contact');
    await loadContacts();
    renderSidebar();
    toast('Contact removed');
  } catch { toast('Failed','er'); }
}

function setToggleUI(id, on) {
  const el = document.getElementById(id);
  if (!el) return;
  if (on) el.classList.add('on'); else el.classList.remove('on');
}

async function toggleSetting(key, btn) {
  const nowOn = !btn.classList.contains('on');
  if (nowOn) btn.classList.add('on'); else btn.classList.remove('on');
  try {
    const payload = {}; payload[key] = nowOn;
    const r = await api('/api/user.php?action=settings', { method:'PATCH', body: JSON.stringify(payload) });
    if (r.user) me = r.user;
  } catch { toast('Failed to save setting','er'); if(nowOn) btn.classList.remove('on'); else btn.classList.add('on'); }
}

function startPoll() {
  if (pollTimer) clearInterval(pollTimer);

  let fastTick = 0;
  pollTimer = setInterval(async () => {
    if (!token) return;
    fastTick++;

    if (activeChat) {
      try {
        const msgs = document.getElementById('chat-msgs');
        const existing = msgs?.querySelectorAll('.msg[data-id]');
        const lastId = existing?.length ? parseInt(existing[existing.length-1].dataset.id, 10) : 0;
        const r = await api(`/api/messages.php?userId=${activeChat.id}&limit=20${lastId ? '&after='+lastId : ''}`);
        (r.messages||[]).forEach(m => {
          if (!lastId || m.id > lastId) {
            if (m.from_id == me.id) {
              const pending = msgs?.querySelector('.msg.me:not([data-id])');
              if (pending) { pending.dataset.id = m.id; const rd = pending.querySelector('.msg-read'); if(rd) rd.textContent='\u2713 Sent'; return; }
            }
            appendMsg(m.from_id == me.id, m.content, m.created_at, false, m.id, m.image_url, !!m.read_at);
          }
        });
        if (r.messages?.length) updateReadReceipts(r.messages);
      } catch {}

      try {
        const t = await api(`/api/messages.php?action=typing&userId=${activeChat.id}`);
        const ind = document.getElementById('typing-indicator');
        const nm  = document.getElementById('typing-name');
        if (ind && nm) { nm.textContent = activeChat.nickname || activeChat.username; ind.classList.toggle('on', !!t.typing); }
      } catch {}
    }

    if (fastTick % 5 !== 0) return;

    try {
      const r = await api('/api/friends.php?action=list');
      const unreadMap = {};
      friends.forEach(f => { if(f._unread) unreadMap[f.id]=f._unread; });
      friends = r.friends || [];
      friends.forEach(f => { if(unreadMap[f.id]) f._unread=unreadMap[f.id]; });
      const newIncoming = r.incoming || [];
      if (newIncoming.length > incomingFriendReqs.length) toast('New friend request!');
      incomingFriendReqs = newIncoming;
      if (activeTab === 'friends') renderSidebar();
      if (activeTab === 'requests') renderRequests();
      updateReqBadge();
    } catch {}

    try {
      const r = await api('/api/requests.php?action=list');
      const newReqs = r.requests || [];
      if (newReqs.length > requests.length) toast('New message request!');
      requests = newReqs;
      updateReqBadge();
    } catch {}

  }, 1000);
}

async function api(path, opts={}) {
  const r = await fetch(API+path, {
    headers:{'Content-Type':'application/json',...(token?{Authorization:'Bearer '+token}:{})},
    ...opts
  });
  return r.json();
}

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function openOv(id) { document.getElementById(id).classList.add('on'); }
function closeOv(id) { document.getElementById(id).classList.remove('on'); }

document.querySelectorAll('.ov').forEach(o => o.addEventListener('click', e => { if(e.target===o) o.classList.remove('on'); }));
document.addEventListener('keydown', e => { if(e.key==='Escape') document.querySelectorAll('.ov.on').forEach(o=>o.classList.remove('on')); });

let _tt;
function toast(m, t='ok') {
  const el=document.getElementById('toast');
  el.textContent=m; el.className='on '+t;
  clearTimeout(_tt); _tt=setTimeout(()=>el.className='',3500);
}

init();
</script>
</body>
</html>
