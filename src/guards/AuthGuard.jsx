import { useCallback } from 'react';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../context/ToastContext';

const DEFAULT_MSG = 'Please sign-in to your Account to save items for later.';

/**
 * Hook — wraps any action with an auth check.
 * Usage: const guard = useAuthGuard();
 *        <button onClick={() => guard(() => doSomething())} />
 *
 * Returns false and shows a toast if not authenticated.
 * Returns true and runs the action if authenticated.
 */
export function useAuthGuard(message = DEFAULT_MSG) {
  const { isAuthenticated } = useAuth();
  const { showToast } = useToast();

  return useCallback(
    (action) => {
      if (!isAuthenticated) {
        showToast(message, 'auth');
        return false;
      }
      if (typeof action === 'function') action();
      return true;
    },
    [isAuthenticated, showToast, message]
  );
}

/**
 * Component middleware — renders children only when authenticated.
 * When not authenticated, renders `fallback` (default: null).
 *
 * Usage:
 *   <AuthGuard fallback={<p>Please log in</p>}>
 *     <ProtectedContent />
 *   </AuthGuard>
 */
export function AuthGuard({ children, fallback = null }) {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? children : fallback;
}
