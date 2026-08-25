import { createContext, useContext, useState, useCallback, useRef } from 'react';
import { Info, CheckCircle, AlertCircle, X } from 'lucide-react';

/* ── Toast UI (rendered by provider) ── */
const ICONS = {
  auth:    <Info size={18} />,
  success: <CheckCircle size={18} />,
  error:   <AlertCircle size={18} />,
  info:    <Info size={18} />,
};

function ToastItem({ message, type = 'info', onClose }) {
  return (
    <div className={`toast toast--${type}`} role="alert">
      <span className="toast__icon">{ICONS[type] ?? ICONS.info}</span>
      <span className="toast__msg">{message}</span>
      <button className="toast__close" onClick={onClose} aria-label="Dismiss">
        <X size={15} />
      </button>
    </div>
  );
}

/* ── Context ── */
const ToastContext = createContext(null);

export function ToastProvider({ children }) {
  const [toast, setToast] = useState(null);
  const timerRef = useRef(null);

  const showToast = useCallback((message, type = 'info', duration = 3500) => {
    if (timerRef.current) clearTimeout(timerRef.current);
    setToast({ message, type, id: Date.now() });
    timerRef.current = setTimeout(() => setToast(null), duration);
  }, []);

  const hideToast = useCallback(() => {
    if (timerRef.current) clearTimeout(timerRef.current);
    setToast(null);
  }, []);

  return (
    <ToastContext.Provider value={{ showToast, hideToast }}>
      {children}
      {toast && (
        <div className="toast-container">
          <ToastItem key={toast.id} {...toast} onClose={hideToast} />
        </div>
      )}
    </ToastContext.Provider>
  );
}

export const useToast = () => useContext(ToastContext);
