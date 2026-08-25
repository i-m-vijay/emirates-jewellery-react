import { createContext, useContext, useState, useCallback } from 'react';

const AuthContext = createContext(null);

const TOKEN_KEY = 'auth_token';
const USER_KEY  = 'auth_user';

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => sessionStorage.getItem(TOKEN_KEY));
  const [user, setUser] = useState(() => {
    const stored = sessionStorage.getItem(USER_KEY);
    try { return stored ? JSON.parse(stored) : null; } catch { return null; }
  });
  const [authModalOpen, setAuthModalOpen] = useState(false);
  const [pendingAuthPayload, setPendingAuthPayload] = useState(null);

  const login = useCallback((newToken, userData) => {
    sessionStorage.setItem(TOKEN_KEY, newToken);
    sessionStorage.setItem(USER_KEY, JSON.stringify(userData));
    setToken(newToken);
    setUser(userData);
  }, []);

  const openAuth = useCallback((payload = null) => {
    setPendingAuthPayload(payload);
    setAuthModalOpen(true);
  }, []);

  const closeAuth = useCallback(() => {
    setAuthModalOpen(false);
  }, []);

  const clearPendingAuthPayload = useCallback(() => setPendingAuthPayload(null), []);

  const logout = useCallback(() => {
    sessionStorage.removeItem(TOKEN_KEY);
    sessionStorage.removeItem(USER_KEY);
    setToken(null);
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ token, user, isAuthenticated: Boolean(token), login, logout, authModalOpen, openAuth, closeAuth, pendingAuthPayload, clearPendingAuthPayload, }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
