const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function Footer() {
  return (
    <footer className="footer">
      <div className="footer-grid">
        <div>
          <img src={logo} className="footer-logo" alt="Emirates" />
          <p>Premium gold and diamond jewellery for every precious moment.</p>
        </div>

        <div>
          <h4>Customer Service</h4>
          <p>Contact Us</p>
          <p>Shipping Policy</p>
          <p>Return Policy</p>
        </div>

        <div>
          <h4>Collections</h4>
          <p>Gold Jewellery</p>
          <p>Diamond Jewellery</p>
          <p>Bridal Jewellery</p>
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