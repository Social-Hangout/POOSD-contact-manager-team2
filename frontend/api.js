/* Shared API helpers for the Contact Manager frontend.
   Uses session cookies (credentials: 'include') so login persists across pages.
   Serve the site from the project root, e.g. php -S localhost:8000
   then open /frontend/logIn.html — paths assume /api is one level up. */

const API_BASE = '../api';

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

function initials(firstName, lastName) {
  const a = (firstName || '').trim().charAt(0);
  const b = (lastName || '').trim().charAt(0);
  return (a + b).toUpperCase() || '?';
}

function setUserBadge() {
  const el = document.getElementById('user-name');
  if (!el) return;

  const label = sessionStorage.getItem('user_label');
  if (label) {
    el.textContent = label;
  }
}

function requireLoginRedirect() {
  // Soft check: if list fails with not logged in, send to login.
  // Pages call this after a failed auth response.
  window.location.href = 'logIn.html';
}

function showMessage(el, message, isError) {
  if (!el) {
    if (message) alert(message);
    return;
  }
  el.textContent = message || '';
  el.style.color = isError ? '#a33' : '#2e6b4f';
}
