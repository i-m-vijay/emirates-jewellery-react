import { Link } from 'react-router-dom';

const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function Footer() {
  return (
    <footer className="footer">
      <div className="footer-grid">
        <div>
          <a href="/"><img src={logo} className="footer-logo" alt="Emirates" /></a>
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
          <Link to="/jewellery?jewellery_type=gold" className="footer-link">Gold Jewellery</Link>
          <Link to="/jewellery?jewellery_type=diamond" className="footer-link">Diamond Jewellery</Link>
          <Link to="/jewellery?jewellery_type=gold" className="footer-link">Bridal Jewellery</Link>
        </div>

        <div>
          <h4>Newsletter</h4>
          <input placeholder="Enter email" />
          <button>Subscribe</button>
        </div>
      </div>
    </footer>
  );
}

export default Footer;