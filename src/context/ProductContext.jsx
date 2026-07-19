import { createContext, useContext, useState, useMemo, useCallback } from 'react';

const ProductContext = createContext(null);

const SELECTED_KEY = 'selected_product';

function readStoredProduct() {
  try { return JSON.parse(sessionStorage.getItem(SELECTED_KEY) || 'null'); }
  catch { return null; }
}

export function ProductProvider({ children }) {
  // Initialise from sessionStorage so a page refresh restores the last viewed product
  const [selectedProduct, _setSelected] = useState(readStoredProduct);
  const [categoryProducts, setCategoryProducts] = useState([]);
  const [categoryName, setCategoryName] = useState('');
  const [categorySlug, setCategorySlug] = useState('');

  // Wrap setter so every update is also written to sessionStorage
  const setSelectedProduct = useCallback((product) => {
    _setSelected(product);
    if (product) {
      sessionStorage.setItem(SELECTED_KEY, JSON.stringify(product));
    } else {
      sessionStorage.removeItem(SELECTED_KEY);
    }
  }, []);

  const value = useMemo(() => ({
    selectedProduct,
    setSelectedProduct,
    categoryProducts,
    setCategoryProducts,
    categoryName,
    setCategoryName,
    categorySlug,
    setCategorySlug,
  }), [selectedProduct, setSelectedProduct, categoryProducts, categoryName, categorySlug]);

  return (
    <ProductContext.Provider value={value}>
      {children}
    </ProductContext.Provider>
  );
}

export const useProduct = () => useContext(ProductContext);
