import { useState } from 'react';
import {
  Search,
  User,
  Heart,
  ShoppingBag,
  Menu,
  X,
  ChevronRight,
  MapPin,
  Gift,
  BadgePercent,
  HelpCircle,
  Package,
  Gem,
  ChevronDown,
} from 'lucide-react';

const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function Header() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [accountOpen, setAccountOpen] = useState(false);

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

          <img src={logo} className="brand-logo" alt="Emirates Gold & Diamonds" />

          <div className="search-field">
            <Search size={17} />
            <input placeholder="Search for gold, diamond, rings..." />
          </div>

          <div className="header-actions">
            <div className="account-wrap">
              <button
                className="header-icon-btn user-dropdown-btn"
                onClick={() => setAccountOpen(!accountOpen)}
              >
                <User size={24} />
                <ChevronDown size={16} />
              </button>

              {accountOpen && (
                <div className="account-dropdown">
                  <a><User size={20} /> About Us</a>
                  <a><BadgePercent size={20} /> Promotions</a>
                  <a><Package size={20} /> Track My Order</a>
                  <a><HelpCircle size={20} /> FAQ&apos;s</a>
                  <a><Gem size={20} /> Gold Rate</a>
                </div>
              )}
            </div>

            <Heart />
            <ShoppingBag />
          </div>
        </div>

        <nav className="nav">
          <a>Gold</a>
          <a>Diamond</a>
          <a>Rings</a>
          <a>Earrings</a>
          <a>Necklaces</a>
          <a>Wedding</a>
          <a>Collections</a>
          <a>Gifts</a>
        </nav>
      </header>

      {menuOpen && (
        <div className="overlay" onClick={() => setMenuOpen(false)}></div>
      )}

      <aside className={`offcanvas ${menuOpen ? 'active' : ''}`}>
        <div className="offcanvas-head">
          <div>
            <User size={22} />
            <span>Account</span>
          </div>

          <button onClick={() => setMenuOpen(false)}>
            <X size={22} />
          </button>
        </div>

        <div className="offcanvas-featured">
          <div>
            <img src={`${IMAGE_PATH}offcanvas-gold.jpg`} alt="Gold Jewellery" />
            <p>Gold Jewelry</p>
          </div>

          <div>
            <img src={`${IMAGE_PATH}offcanvas-diamond.jpg`} alt="Diamond Jewellery" />
            <p>Diamond Jewelry</p>
          </div>

          <div>
            <img src={`${IMAGE_PATH}offcanvas-all.jpg`} alt="All Jewellery" />
            <p>All Jewelry</p>
          </div>
        </div>

        <div className="offcanvas-menu">
          {[
            ['Gold', Gem],
            ['Diamond', Gem],
            ['All jewelry', Gem],
            ['Collections', Gem],
            ['Gifting', Gift],
            ['Promotions', BadgePercent],
          ].map(([title, Icon]) => (
            <a key={title}>
              <span>
                <Icon size={22} /> {title}
              </span>
              <ChevronRight size={20} />
            </a>
          ))}
        </div>

        <div className="offcanvas-bottom">
          <button><Gem size={17} /> Gold Rate</button>
          <button><MapPin size={17} /> Store Locator</button>
        </div>
      </aside>
    </>
  );
}

export default Header;