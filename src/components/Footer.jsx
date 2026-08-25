import { Link } from 'react-router-dom';

const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function Footer() {
  return (
    <>
      <footer className="footer">
        <div className="footer-grid">
          <div>
            <a href="/">
              <img src={logo} className="footer-logo" alt="Emirates" />
            </a>
            <p>Premium gold and diamond jewellery for every precious moment.</p>
          </div>

          <div>
            <h4>Customer Service</h4>
            <Link to="/contact-us" className="footer-link">Contact Us</Link>
            <Link to="/shipping-policy" className="footer-link">Shipping Policy</Link>
            <Link to="/exchange-return-policy" className="footer-link">Return Policy</Link>
          </div>

          <div>
            <h4>Collections</h4>
            <Link to="/jewellery?jewellery_type=gold" className="footer-link">
              Gold Jewellery
            </Link>
            <Link to="/jewellery?jewellery_type=diamond" className="footer-link">
              Diamond Jewellery
            </Link>
            <Link to="/jewellery?jewellery_type=gold" className="footer-link">
              Bridal Jewellery
            </Link>
          </div>

          <div>
            <h4>Newsletter</h4>
            <input placeholder="Enter email" />
            <button>Subscribe</button>
          </div>
        </div>
      </footer>

      {/* Fixed Bottom Buttons */}
    <div className="fixed-contact-buttons">
  <a
    href="tel:+17323727357"
    className="fixed-contact-btn"
  >
    <svg
      className="fixed-btn-icon"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L8 9.73a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z" />
    </svg>
    <span>Call Us</span>
  </a>

  <a
    href="https://maps.app.goo.gl/HAuRs3kwKMwWVwaaA"
    target="_blank"
    rel="noopener noreferrer"
    className="fixed-location-btn"
  >
    <svg
      className="fixed-btn-icon"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
      <circle cx="12" cy="10" r="2.5" />
    </svg>
    <span>Location</span>
  </a>
</div>
    </>
  );
}

export default Footer;