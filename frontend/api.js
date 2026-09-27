/* Shared API helpers for the Contact Manager frontend.
   Auth uses a token from login (X-API-Key). The server derives user_id
   from the token — the client never sends user_id for contact ops. */

const API_BASE = '/api';

function getToken() {
  return localStorage.getItem('api_token') || '';
}

function setAuth(userId, token, label) {
  if (userId != null) {
    localStorage.setItem('user_id', String(userId));
  }
  if (token) {
    localStorage.setItem('api_token', token);
  }
  if (label) {
    localStorage.setItem('user_label', label);
  }
}

function clearAuth() {
  localStorage.removeItem('user_id');
  localStorage.removeItem('api_token');
  localStorage.removeItem('user_label');
}

function logout() {
  clearAuth();
  window.location.href = 'logIn.html';
}

async function apiRequest(path, options = {}) {
  const headers = {
    'Content-Type': 'application/json',
    ...(options.headers || {})
  };

  const token = getToken();
  if (token) {
    headers['X-API-Key'] = token;
  }

  const config = {
    credentials: 'include',
    ...options,
    headers
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
    body: JSON.stringify(body == null ? {} : body)
  });
}

function apiGet(path) {
  return apiRequest(path, { method: 'GET' });
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
  if (!getToken()) {
    requireLoginRedirect();
    return false;
  }
  return true;
}

function isAuthError(data) {
  const message = (data && data.message ? data.message : '').toLowerCase();
  return (
    message.includes('missing api token') ||
    message.includes('invalid api token') ||
    message.includes('missing user_id') ||
    message.includes('not logged in') ||
    message.includes('unauth')
  );
}

function showMessage(el, message, isError) {
  if (!el) {
    if (message) alert(message);
    return;
  }
  el.textContent = message || '';
  el.style.color = isError ? '#a33' : '#2e6b4f';
}
