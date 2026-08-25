import { BASE_URL } from '../api/apiUrls';
import { parseApiError } from '../utils/apiError';

const getAuthHeaders = () => {
  const token = sessionStorage.getItem('auth_token');
  return token ? { Authorization: `Bearer ${token}` } : {};
};

const buildUrl = (endpoint, params = {}) => {
  const url = new URL(`${BASE_URL}${endpoint}`);
  Object.entries(params).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      url.searchParams.append(key, value);
    }
  });
  return url.toString();
};

const handleResponse = async (response) => {
  if (!response.ok) {
    throw await parseApiError(response);
  }
  return response.json();
};

const defaultHeaders = {
  'Content-Type': 'application/json',
  Accept: 'application/json',
};

// ── In-memory GET cache: url → { data, at } ──
const cache = new Map();
// ── In-flight deduplication: url → Promise ──
const inFlight = new Map();

const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

function getCached(key) {
  const entry = cache.get(key);
  if (entry && Date.now() - entry.at < CACHE_TTL) return entry.data;
  cache.delete(key);
  return null;
}

function setCached(key, data) {
  cache.set(key, { data, at: Date.now() });
}

/** Call after mutations that should invalidate all cached data. */
export function invalidateCache() {
  cache.clear();
  inFlight.clear();
}

export const apiService = {
  /**
   * GET with:
   *  - 5-minute in-memory cache (avoids re-fetching on back-navigation)
   *  - in-flight deduplication (concurrent identical calls share one request)
   */
  get: (endpoint, params = {}) => {
    const url = buildUrl(endpoint, params);

    const cached = getCached(url);
    if (cached) return Promise.resolve(cached);

    if (inFlight.has(url)) return inFlight.get(url);

    const promise = fetch(url, {
      method: 'GET',
      headers: { ...defaultHeaders, ...getAuthHeaders() },
    })
      .then(handleResponse)
      .then((data) => {
        setCached(url, data);
        return data;
      })
      .finally(() => inFlight.delete(url));

    inFlight.set(url, promise);
    return promise;
  },

  post: (endpoint, body = {}) =>
    fetch(`${BASE_URL}${endpoint}`, {
      method: 'POST',
      headers: { ...defaultHeaders, ...getAuthHeaders() },
      body: JSON.stringify(body),
    }).then(handleResponse),

  put: (endpoint, body = {}) =>
    fetch(`${BASE_URL}${endpoint}`, {
      method: 'PUT',
      headers: { ...defaultHeaders, ...getAuthHeaders() },
      body: JSON.stringify(body),
    }).then(handleResponse),

  patch: (endpoint, body = {}) =>
    fetch(`${BASE_URL}${endpoint}`, {
      method: 'PATCH',
      headers: { ...defaultHeaders, ...getAuthHeaders() },
      body: JSON.stringify(body),
    }).then(handleResponse),

  delete: (endpoint) =>
    fetch(`${BASE_URL}${endpoint}`, {
      method: 'DELETE',
      headers: { ...defaultHeaders, ...getAuthHeaders() },
    }).then(handleResponse),
};
