import { createContext, useContext, useState, useCallback } from 'react';

const WishlistContext = createContext(null);
const STORAGE_KEY = 'wishlist_items';

function readStorage() {
  try { return JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]'); }
  catch { return []; }
}

function writeStorage(items) {
  sessionStorage.setItem(STORAGE_KEY, JSON.stringify(items));
}

export function WishlistProvider({ children }) {
  const [items, setItems] = useState(readStorage);

  const updateItems = useCallback((updater) => {
    setItems((current) => {
      const next = typeof updater === 'function' ? updater(current) : updater;
      writeStorage(next);
      return next;
    });
  }, []);

  const addItem = useCallback((product) => {
    updateItems((current) =>
      current.find((p) => p.record_id === product.record_id)
        ? current
        : [...current, product]
    );
  }, [updateItems]);

  const removeItem = useCallback((recordId) => {
    updateItems((current) => current.filter((p) => p.record_id !== recordId));
  }, [updateItems]);

  const toggleItem = useCallback((product) => {
    updateItems((current) =>
      current.find((p) => p.record_id === product.record_id)
        ? current.filter((p) => p.record_id !== product.record_id)
        : [...current, product]
    );
  }, [updateItems]);

  const isInWishlist = useCallback(
    (recordId) => items.some((p) => p.record_id === recordId),
    [items]
  );

  return (
    <WishlistContext.Provider value={{
      items,
      count: items.length,
      addItem,
      removeItem,
      toggleItem,
      isInWishlist,
    }}>
      {children}
    </WishlistContext.Provider>
  );
}

export const useWishlist = () => useContext(WishlistContext);
