/* Justice Launcher — Frontend JS */
'use strict';

// ── State ────────────────────────────────────────────────────────────────────
let token = localStorage.getItem('jl_token') || null;
let currentUser = null;

// ── On load ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  restoreSession();
  initScrollAnimations();
  initModalDismiss();
});

// ── Auth ──────────────────────────────────────────────────────────────────────
async function restoreSession() {
  if (!token) return;
  try {
    const r = await apiFetch('/api/user.php?action=me');
    if (r.user) setLoggedIn(r.user);
    else clearSession();
  } catch { clearSession(); }
}

function setLoggedIn(user) {
  currentUser = user;
  const pill = document.getElementById('user-pill');
  pill.classList.add('visible');
  document.getElementById('user-pill-av').textContent = (user.username || '?')[0].toUpperCase();
  document.getElementById('user-pill-name').textContent = user.username;
  document.getElementById('btn-login').style.display    = 'none';
  document.getElementById('btn-register').style.display = 'none';
  hideModals();
}

function clearSession() {
  token = null; currentUser = null;
  localStorage.removeItem('jl_token');
  document.getElementById('user-pill').classList.remove('visible');
  document.getElementById('btn-login').style.display    = '';
  document.getElementById('btn-register').style.display = '';
}

function doLogout() {
  clearSession();
  toast('Signed out successfully');
}

// ── Modals ────────────────────────────────────────────────────────────────────
function showModal(which) {
  hideModals();
  document.getElementById('overlay-' + which).classList.add('open');
  const firstInput = document.querySelector('#overlay-' + which + ' input');
  if (firstInput) setTimeout(() => firstInput.focus(), 80);
}

function hideModals() {
  document.querySelectorAll('.overlay').forEach(o => o.classList.remove('open'));
}

function initModalDismiss() {
  document.querySelectorAll('.overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) hideModals();
    });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') hideModals();
  });
}

// ── Login form ────────────────────────────────────────────────────────────────
async function submitLogin() {
  const login    = document.getElementById('li-login').value.trim();
  const password = document.getElementById('li-pass').value;
  const err      = document.getElementById('li-err');
  const btn      = document.getElementById('li-btn');
  err.style.display = 'none';

  if (!login || !password) { showErr(err, 'Please fill in all fields'); return; }

  btn.disabled = true; btn.textContent = 'Logging in…';
  try {
    const r = await apiFetch('/api/login.php', {
      method: 'POST',
      body: JSON.stringify({ login, password }),
    });
    if (r.error) { showErr(err, r.error); return; }
    token = r.token;
    localStorage.setItem('jl_token', token);
    setLoggedIn(r.user);
    toast('Welcome back, ' + r.user.username + '! 👋');
  } catch {
    showErr(err, 'Could not connect. Please try again.');
  } finally {
    btn.disabled = false; btn.textContent = 'Log In';
  }
}

// ── Register form ─────────────────────────────────────────────────────────────
async function submitRegister() {
  const username = document.getElementById('re-user').value.trim();
  const email    = document.getElementById('re-email').value.trim();
  const password = document.getElementById('re-pass').value;
  const err      = document.getElementById('re-err');
  const btn      = document.getElementById('re-btn');
  err.style.display = 'none';

  if (!username || !email || !password) { showErr(err, 'Please fill in all fields'); return; }

  btn.disabled = true; btn.textContent = 'Creating account…';
  try {
    const r = await apiFetch('/api/register.php', {
      method: 'POST',
      body: JSON.stringify({ username, email, password }),
    });
    if (r.error) { showErr(err, r.error); return; }
    token = r.token;
    localStorage.setItem('jl_token', token);
    setLoggedIn(r.user);
    toast('Account created! Welcome, ' + r.user.username + ' 🎉');
  } catch {
    showErr(err, 'Could not connect. Please try again.');
  } finally {
    btn.disabled = false; btn.textContent = 'Create Account';
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
async function apiFetch(path, opts = {}) {
  const headers = { 'Content-Type': 'application/json' };
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const r = await fetch(API_BASE + path, { headers, ...opts });
  if (!r.ok && r.status !== 400 && r.status !== 401 && r.status !== 409) {
    throw new Error('HTTP ' + r.status);
  }
  return r.json();
}

function showErr(el, msg) {
  el.textContent = msg;
  el.style.display = 'block';
}

let toastTimer;
function toast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => { t.className = ''; }, 3800);
}

// ── Scroll animations ─────────────────────────────────────────────────────────
function initScrollAnimations() {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
}
