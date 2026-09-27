/* Shared API helpers for the Contact Manager frontend.
   Auth is stateless: store user_id in localStorage after login and
   send it on every contact API request (SwaggerHub-friendly). */

const API_BASE = '/api';

async function apiRequest(path, options = {}) {
  const config = {
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {})
    },
    ...options
  };

  const response = await fetch(`${API_BASE}${path}`, config);
  const text = await response.text();

  let data;
  try {
    data = text ? JSON.parse(text) : {};
  } catch (err) {
    throw new Error('Server returned invalid JSON');
  }

  return data;
}

function apiPost(path, body) {
  return apiRequest(path, {
    method: 'POST',
    body: JSON.stringify(body)
  });
}

function apiGet(path) {
  return apiRequest(path, { method: 'GET' });
}

function getUserId() {
  const raw = localStorage.getItem('user_id');
  const id = Number(raw);
  return Number.isInteger(id) && id > 0 ? id : null;
}

function setUserId(userId) {
  localStorage.setItem('user_id', String(userId));
}

function clearAuth() {
  localStorage.removeItem('user_id');
  localStorage.removeItem('user_label');
}

function logout() {
  clearAuth();
  window.location.href = 'logIn.html';
}

function withUserId(body) {
  const payload = body && typeof body === 'object' ? Object.assign({}, body) : {};
  payload.user_id = getUserId();
  return payload;
}

function initials(firstName, lastName) {
  const a = (firstName || '').trim().charAt(0);
  const b = (lastName || '').trim().charAt(0);
  return (a + b).toUpperCase() || '?';
}

function setUserBadge() {
  const el = document.getElementById('user-name');
  if (!el) return;

  const label = localStorage.getItem('user_label');
  if (label) {
    el.textContent = label;
  }
}

function requireLoginRedirect() {
  window.location.href = 'logIn.html';
}

function requireAuth() {
  if (!getUserId()) {
    requireLoginRedirect();
    return false;
  }
  return true;
}

function isAuthError(data) {
  const message = (data && data.message ? data.message : '').toLowerCase();
  return message.includes('missing user_id') || message.includes('not logged in') || message.includes('unauth');
}

function showMessage(el, message, isError) {
  if (!el) {
    if (message) alert(message);
    return;
  }
  el.textContent = message || '';
  el.style.color = isError ? '#a33' : '#2e6b4f';
}
