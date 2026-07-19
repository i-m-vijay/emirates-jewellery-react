import { lazy, Suspense } from 'react';
import { BrowserRouter, Routes, Route, useLocation } from 'react-router-dom';
import './styles/main.css';
import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';
import { WishlistProvider } from './context/WishlistContext';
import { CartProvider } from './context/CartContext';
import { ProductProvider } from './context/ProductContext';
import Header from './components/Header';
import Footer from './components/Footer';

// Route-level code splitting — each page is a separate chunk loaded on demand
const Home             = lazy(() => import('./pages/Home'));
const Subcategory      = lazy(() => import('./pages/Subcategory'));
const CategoryProducts = lazy(() => import('./pages/CategoryProducts'));
const AllJewelleryPage   = lazy(() => import('./pages/AllJewelleryPage'));
const PriceFilteredPage  = lazy(() => import('./pages/PriceFilteredPage'));
const ProductsByGender   = lazy(() => import('./pages/ProductsByGender'));
const OffersProducts     = lazy(() => import('./pages/OffersProducts'));
const SearchResultsPage  = lazy(() => import('./pages/SearchResultsPage'));
const ProductDetail    = lazy(() => import('./pages/ProductDetail'));
const Cart             = lazy(() => import('./pages/Cart'));
const Checkout         = lazy(() => import('./pages/Checkout'));
const Wishlist         = lazy(() => import('./pages/Wishlist'));
const ContactUs        = lazy(() => import('./pages/ContactUs'));
const ShippingPolicy   = lazy(() => import('./pages/ShippingPolicy'));
const DisclaimerPolicy = lazy(() => import('./pages/DisclaimerPolicy'));
const PrivacyPolicy    = lazy(() => import('./pages/PrivacyPolicy'));
const TermsAndConditions = lazy(() => import('./pages/TermsAndConditions'));
const ReturnPolicy     = lazy(() => import('./pages/ReturnPolicy'));
const CancellationPolicy = lazy(() => import('./pages/CancellationPolicy'));
const EthicsAndPolicies = lazy(() => import('./pages/EthicsAndPolicies'));
const CopyrightPolicy  = lazy(() => import('./pages/CopyrightPolicy'));
const SanctionsCompliancePolicy = lazy(() => import('./pages/SanctionsCompliancePolicy'));

function PageLoader() {
  return (
    <div className="page-loader-wrap">
      <span className="page-loader" />
    </div>
  );
}

function AppShell() {
  const { pathname } = useLocation();
  const isCheckout = pathname.startsWith('/checkout');

  return (
    <>
      {!isCheckout && <Header />}
      <main>
        <Suspense fallback={<PageLoader />}>
          <Routes>
            <Route path="/"                element={<Home />} />
            <Route path="/category/rings"    element={<Subcategory />} />
            <Route path="/category/earrings" element={<Subcategory />} />
            <Route path="/category/:slug"    element={<CategoryProducts />} />
            <Route path="/jewellery/by-price"  element={<PriceFilteredPage />} />
            <Route path="/jewellery/by-gender" element={<ProductsByGender />} />
            <Route path="/jewellery/offers"    element={<OffersProducts />} />
            <Route path="/jewellery"          element={<AllJewelleryPage />} />
            <Route path="/search"          element={<SearchResultsPage />} />
            <Route path="/product/:id"     element={<ProductDetail />} />
            <Route path="/cart"            element={<Cart />} />
            <Route path="/wishlist"        element={<Wishlist />} />
            <Route path="/checkout"        element={<Checkout />} />
            <Route path="/contact-us"      element={<ContactUs />} />
            <Route path="/shipping-policy" element={<ShippingPolicy />} />
            <Route path="/disclaimer-policy" element={<DisclaimerPolicy />} />
            <Route path="/privacy-policy" element={<PrivacyPolicy />} />
            <Route path="/terms-and-condition" element={<TermsAndConditions />} />
            <Route path="/exchange-return-policy" element={<ReturnPolicy />} />
            <Route path="/cancellation-policy" element={<CancellationPolicy />} />
            <Route path="/ethics-and-policies" element={<EthicsAndPolicies />} />
            <Route path="/copyright-policy" element={<CopyrightPolicy />} />
            <Route path="/sanctions-compliance-policy" element={<SanctionsCompliancePolicy />} />
          </Routes>
        </Suspense>
      </main>
      {!isCheckout && <Footer />}
    </>
  );
}

function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <ToastProvider>
          <WishlistProvider>
            <CartProvider>
              <ProductProvider>
                <AppShell />
              </ProductProvider>
            </CartProvider>
          </WishlistProvider>
        </ToastProvider>
      </AuthProvider>
    </BrowserRouter>
  );
}

export default App;
