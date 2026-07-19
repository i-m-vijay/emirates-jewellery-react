import { useState, useRef, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  Search, User, Heart, ShoppingBag, Menu, X, ChevronRight,
  MapPin, Gift, BadgePercent, HelpCircle, Package, Gem, ChevronDown, LogOut,
} from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useWishlist } from '../context/WishlistContext';
import { useCart } from '../context/CartContext';
import { logoutUser } from '../api/authApi';
import AuthModal from './AuthModal';
import CartDropdown from './CartDropdown';
import JewelleryHoverModal from './JewelleryHoverModal';
import OffcanvasSubDrawer from './OffcanvasSubDrawer';

// maps offcanvas label → metal_type param (empty string = no filter / All)
const OFFCANVAS_METAL = {
  'Gold':        'gold',
  'Diamond':     'diamond',
  'All jewelry': '',
  'Collections': 'collections',
  'Gifting':     'gifts',
  'Promotions':  'promotions',
};

const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function UserAvatar({ name }) {
  const initials = name ? name.charAt(0).toUpperCase() : '?';
  return <span className="header-avatar">{initials}</span>;
}

function Header() {
  const navigate = useNavigate();
  const { isAuthenticated, user, logout, openAuth, authModalOpen, closeAuth, pendingAuthPayload, clearPendingAuthPayload } = useAuth();
  const { count: wishlistCount } = useWishlist();
  const { count: cartCount } = useCart();
  const { addItem } = useCart();
  const [menuOpen, setMenuOpen]                       = useState(false);
  const [activeOffcanvasItem, setActiveOffcanvasItem] = useState(null);
  const [searchQuery, setSearchQuery]                 = useState('');
  const debounceRef = useRef(null);
  const [accountOpen, setAccountOpen] = useState(false);
  const [cartOpen, setCartOpen] = useState(false);
  const [activeNav, setActiveNav] = useState(null);
  const accountRef = useRef(null);
  const cartRef    = useRef(null);
  const cartTimer  = useRef(null);
  const navTimer   = useRef(null);

  const openNav  = (item) => { clearTimeout(navTimer.current); setActiveNav(item); };
  const closeNav = ()     => { navTimer.current = setTimeout(() => setActiveNav(null), 150); };

  const openCart  = () => { clearTimeout(cartTimer.current); setCartOpen(true); };
  const closeCart = () => { cartTimer.current = setTimeout(() => setCartOpen(false), 180); };

  // Close auth panel when clicking outside
  useEffect(() => {
    if (!accountOpen) return;
    const handler = (e) => {
      if (accountRef.current && !accountRef.current.contains(e.target)) {
        setAccountOpen(false);
      }
    };
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, [accountOpen]);

  // When user logs in, check for a pending add-to-cart action set via `openAuth` and execute it
  useEffect(() => {
    if (!isAuthenticated) return;
    try {
      const payload = pendingAuthPayload;
      if (!payload) return;
      if (payload.product) {
        addItem(payload.product, payload.qty || 1);
      }
      clearPendingAuthPayload();
    } catch (e) {
      // ignore
      clearPendingAuthPayload();
    }
  }, [isAuthenticated, addItem, pendingAuthPayload, clearPendingAuthPayload]);

  const handleLogout = async () => {
    try { await logoutUser(); } catch { /* ignore API error, still clear session */ }
    logout();
    setAuthOpen(false);
  };

  return (
    <>
      <div className="top-sale-bar">
        Exclusive Online Offers | Free Shipping Across USA
      </div>

      <header className="main-header">
        <div className="header-row">
          <button className="menu-toggle" onClick={() => setMenuOpen(true)}>
            <Menu />
          </button>

          <a href="/">
            <img src={logo} className="brand-logo" alt="Emirates Gold & Diamonds" />
          </a>

          <div className="search-field">
            <Search
              size={17}
              style={{ cursor: 'pointer' }}
              onClick={() => { if (searchQuery.trim()) navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`); }}
            />
            <input
              placeholder="Search for gold, diamond, rings…"
              value={searchQuery}
              onChange={(e) => {
                const val = e.target.value;
                setSearchQuery(val);
                clearTimeout(debounceRef.current);
                if (val.trim().length >= 2) {
                  debounceRef.current = setTimeout(() => {
                    navigate(`/search?q=${encodeURIComponent(val.trim())}`);
                  }, 400);
                } else if (val === '') {
                  navigate(-1);
                }
              }}
              onKeyDown={(e) => {
                if (e.key === 'Enter' && searchQuery.trim()) {
                  clearTimeout(debounceRef.current);
                  navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
                }
              }}
            />
            {searchQuery && (
              <X
                size={15}
                style={{ cursor: 'pointer', flexShrink: 0 }}
                onClick={() => {
                  clearTimeout(debounceRef.current);
                  setSearchQuery('');
                  navigate(-1);
                }}
              />
            )}
          </div>

          <div className="header-actions">
            <div className="account-wrap" ref={accountRef}>
              <button
                className="header-icon-btn user-dropdown-btn"
                onClick={() => { if (isAuthenticated) setAccountOpen((o) => !o); else openAuth(); }}
                aria-label="Account"
              >
                {isAuthenticated
                  ? <UserAvatar name={user?.first_name} />
                  : <User size={24} />}
                <ChevronDown size={16} />
              </button>

              {accountOpen && isAuthenticated && (
                <div className="account-dropdown auth-user-panel">
                  <p className="auth-user-panel__greeting">
                    Hello, <strong>{user?.first_name} {user?.last_name}</strong>
                  </p>
                  <p className="auth-user-panel__email">{user?.email}</p>
                  <hr className="auth-user-panel__divider" />
                  <a className="auth-user-panel__link"><Package size={18} /> My Orders</a>
                  <a className="auth-user-panel__link"><User size={18} /> My Profile</a>
                  <button className="auth-user-panel__logout" onClick={handleLogout}>
                    <LogOut size={18} /> Logout
                  </button>
                </div>
              )}

              {authModalOpen && <AuthModal onClose={closeAuth} />}
            </div>

            <div className="header-badge-wrap" onClick={() => navigate('/wishlist')} style={{ cursor: 'pointer' }}>
              <Heart size={24} className="header-icon-btn" />
              {wishlistCount > 0 && (
                <span className="header-badge">{wishlistCount > 99 ? '99+' : wishlistCount}</span>
              )}
            </div>
            <div
              className="header-badge-wrap"
              ref={cartRef}
              onMouseEnter={openCart}
              onMouseLeave={closeCart}
            >
              <ShoppingBag size={24} style={{ cursor: 'pointer', color: '#005a55' }} />
              {cartCount > 0 && (
                <span className="header-badge">{cartCount > 99 ? '99+' : cartCount}</span>
              )}
              {cartOpen && (
                <div onMouseEnter={openCart} onMouseLeave={closeCart}>
                  <CartDropdown />
                </div>
              )}
            </div>
          </div>
        </div>

        <nav className="nav">
          {['Gold','Diamond','Rings','Earrings','Necklaces','Wedding','Collections','Gifts'].map((item) => (
            <a
              key={item}
              className={activeNav === item ? 'nav-active' : ''}
              onMouseEnter={() => openNav(item)}
              onMouseLeave={closeNav}
            >
              {item}
            </a>
          ))}
        </nav>

        {activeNav && (
          <JewelleryHoverModal
            item={activeNav}
            onMouseEnter={() => clearTimeout(navTimer.current)}
            onMouseLeave={closeNav}
            onClose={() => setActiveNav(null)}
          />
        )}
      </header>

      {menuOpen && <div className="overlay" onClick={() => { setMenuOpen(false); setActiveOffcanvasItem(null); }} />}

      <aside className={`offcanvas ${menuOpen ? 'active' : ''}`}>
        {activeOffcanvasItem ? (
          <OffcanvasSubDrawer
            item={activeOffcanvasItem.title}
            metalType={activeOffcanvasItem.metalType}
            staticItems={activeOffcanvasItem.staticItems}
            onBack={() => setActiveOffcanvasItem(null)}
            onClose={() => { setMenuOpen(false); setActiveOffcanvasItem(null); }}
          />
        ) : (
          <>
            <div className="offcanvas-head">
              <div>
                {isAuthenticated
                  ? <><UserAvatar name={user?.first_name} /><span>{user?.first_name}</span></>
                  : <><User size={22} /><span>Account</span></>}
              </div>
              <button onClick={() => setMenuOpen(false)}><X size={22} /></button>
            </div>

            <div className="offcanvas-featured">
              <div
                style={{ cursor: 'pointer' }}
                onClick={() => { setMenuOpen(false); setActiveOffcanvasItem(null); navigate('/jewellery?jewellery_type=gold'); }}
              >
                <img src={`${IMAGE_PATH}offcanvas-gold.jpg`} alt="Gold" /><p>Gold Jewelry</p>
              </div>
              <div
                style={{ cursor: 'pointer' }}
                onClick={() => { setMenuOpen(false); setActiveOffcanvasItem(null); navigate('/jewellery?jewellery_type=diamond'); }}
              >
                <img src={`${IMAGE_PATH}offcanvas-diamond.jpg`} alt="Diamond" /><p>Diamond Jewelry</p>
              </div>
              <div
                style={{ cursor: 'pointer' }}
                onClick={() => { setMenuOpen(false); setActiveOffcanvasItem(null); navigate('/jewellery'); }}
              >
                <img src={`${IMAGE_PATH}offcanvas-all.jpg`} alt="All" /><p>All Jewelry</p>
              </div>
            </div>

            <div className="offcanvas-menu">
              {[['Gold', Gem], ['Diamond', Gem], ['All jewelry', Gem],
                ['Collections', Gem], ['Gifting', Gift], ['Promotions', BadgePercent]
              ].map(([title, Icon]) => (
                <a
                  key={title}
                  style={{ cursor: 'pointer' }}
                  onClick={() => {
                    if (title === 'All jewelry') {
                      setMenuOpen(false);
                      setActiveOffcanvasItem(null);
                      navigate('/jewellery');
                    } else if (title === 'Collections') {
                      setActiveOffcanvasItem({
                        title,
                        metalType: '',
                        staticItems: [
                          { label: 'Rings',           slug: 'rings',           img: `${IMAGE_PATH}rings.jpg` },
                          { label: 'Bands',           slug: 'bands',           img: `${IMAGE_PATH}bands.jpg` },
                          { label: 'Bangles',         slug: 'bangles',         img: `${IMAGE_PATH}bangles.jpg` },
                          { label: 'Beaded Necklaces', slug: 'beaded-necklaces', img: `${IMAGE_PATH}beaded-necklace.jpg` },
                        ],
                      });
                    } else if (title === 'Gifting') {
                      setActiveOffcanvasItem({
                        title,
                        metalType: '',
                        staticItems: [
                          { label: 'Drop Earrings', slug: 'drop-earrings', img: `${IMAGE_PATH}earrings.jpg` },
                          { label: 'Hoop Earrings', slug: 'hoop-earrings', img: `${IMAGE_PATH}earrings.jpg` },
                          { label: 'Bangles',       slug: 'bangles',       img: `${IMAGE_PATH}bangles.jpg` },
                          { label: 'Rings',         slug: 'rings',         img: `${IMAGE_PATH}rings.jpg` },
                        ],
                      });
                    } else {
                      setActiveOffcanvasItem({ title, metalType: OFFCANVAS_METAL[title] ?? '' });
                    }
                  }}
                >
                  <span><Icon size={22} /> {title}</span>
                  <ChevronRight size={20} />
                </a>
              ))}
            </div>

            <div className="offcanvas-bottom">
              <button><Gem size={17} /> Gold Rate</button>
              <button><MapPin size={17} /> Store Locator</button>
            </div>
          </>
        )}
      </aside>
    </>
  );
}

export default Header;
