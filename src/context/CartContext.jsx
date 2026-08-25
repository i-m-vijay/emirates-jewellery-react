import { createContext, useContext, useState, useCallback } from 'react';

const CartContext = createContext(null);
const CART_KEY = 'cart_items';

function readCart() {
  try { return JSON.parse(sessionStorage.getItem(CART_KEY) || '[]'); }
  catch { return []; }
}

function writeCart(items) {
  sessionStorage.setItem(CART_KEY, JSON.stringify(items));
}

export function CartProvider({ children }) {
  const [items, setItems] = useState(readCart);

  const update = useCallback((updater) => {
    setItems((current) => {
      const next = updater(current);
      writeCart(next);
      return next;
    });
  }, []);

  const addItem = useCallback((product, qty = 1) => {
    update((current) => {
      const existing = current.find((i) => i.product.record_id === product.record_id);
      if (existing) {
        return current.map((i) =>
          i.product.record_id === product.record_id ? { ...i, qty: i.qty + qty } : i
        );
      }
      return [...current, { product, qty }];
    });
  }, [update]);

  const removeItem = useCallback((recordId) => {
    update((current) => current.filter((i) => i.product.record_id !== recordId));
  }, [update]);

  const updateQty = useCallback((recordId, qty) => {
    if (qty < 1) return;
    update((current) =>
      current.map((i) => i.product.record_id === recordId ? { ...i, qty } : i)
    );
  }, [update]);

  const clearCart = useCallback(() => update(() => []), [update]);

  const isInCart = useCallback(
    (recordId) => items.some((i) => i.product.record_id === recordId),
    [items]
  );

  const count = items.reduce((s, i) => s + i.qty, 0);
  const subtotal = items.reduce((s, i) => {
    const price = parseFloat(i.product.sale_price || i.product.regular_price || 0);
    return s + price * i.qty;
  }, 0);

  return (
    <CartContext.Provider value={{ items, count, subtotal, addItem, removeItem, updateQty, clearCart, isInCart }}>
      {children}
    </CartContext.Provider>
  );
}

export const useCart = () => useContext(CartContext);
