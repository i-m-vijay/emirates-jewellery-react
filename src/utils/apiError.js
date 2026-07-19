/**
 * ApiError — thrown by apiService on non-2xx responses.
 * Carries both a human-readable message and per-field validation errors.
 *
 * Usage in catch blocks:
 *   } catch (err) {
 *     if (err instanceof ApiError && err.fieldErrors) setErrors(err.fieldErrors);
 *     setServerMsg(err.message);
 *   }
 */
export class ApiError extends Error {
  /** @param {string} message  @param {Record<string,string>} fieldErrors */
  constructor(message, fieldErrors = {}) {
    super(message);
    this.name = 'ApiError';
    this.fieldErrors = fieldErrors;
  }
}

/**
 * Flatten the Laravel-style errors object.
 * { email: ["msg1", "msg2"], name: ["msg"] }  →  { email: "msg1", name: "msg" }
 */
export function normalizeFieldErrors(errors = {}) {
  const result = {};
  for (const [field, msgs] of Object.entries(errors)) {
    result[field] = Array.isArray(msgs) ? msgs[0] : String(msgs);
  }
  return result;
}

/**
 * Reads a failed fetch Response and returns an ApiError with
 * the message and any field-level errors from the body.
 */
export async function parseApiError(response) {
  let body = {};
  try { body = await response.json(); } catch { /* non-JSON body */ }

  const message = body.message || `Request failed (${response.status})`;
  const fieldErrors = body.errors ? normalizeFieldErrors(body.errors) : {};
  return new ApiError(message, fieldErrors);
}

/* ─── Client-side email validation ─── */
const EMAIL_RE = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;

/** Returns true if value looks like a valid email address */
export const isValidEmail = (value) => EMAIL_RE.test((value ?? '').trim());

/** Returns an error string, or null if the email is valid */
export const validateEmail = (value) => {
  if (!value?.trim()) return 'Email is required';
  if (!isValidEmail(value)) return 'Enter a valid email address (e.g. name@domain.com)';
  return null;
};
