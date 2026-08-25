import { PolicyHero, PolicyNav } from '../components/PolicyPageHeader';

function DisclaimerPolicy() {
  return (
    <div className="policy-page">
      <PolicyHero subtitle="Here’s some of our policies that defines our business approach and help us strive forward responsibly." />

      <PolicyNav active="Disclaimer" />

      {/* ── Content ── */}
      <section className="policy-content">
        <h2 className="policy-content__heading">Disclaimer Policy</h2>

        <h3>Website/Mobile Apps Disclaimer</h3>
        <p>
          The information provided on this website and mobile apps is for general informational purposes only.
          While Emirates strives to keep the information accurate and up to date, we make no representations or
          warranties of any kind, express or implied, regarding the price, completeness, accuracy, reliability,
          suitability, availability of the website, mobile apps, or any information, products, services, or
          related graphics contained herein. Any reliance on such information is at your own risk.
        </p>
        <p>
          We will not be liable for any loss or damage, including indirect or consequential loss or damage, or
          any loss arising from data or profits related to the use of this website or mobile application.
          Emirates copyright and the trademark protection for the content and materials on the website and
          mobile apps, if misused by any third party shall not attribute any responsibility on Emirates and the
          user shall verify the veracity of any such Intellectual Property published. If there are any user
          generated contents, comments, reviews which is relied upon, Emirates shall not be liable or
          responsibility for any such mention.
        </p>
        <p>
          Emirates shall not be responsible if any minors accessing the website or the mobile application or
          for any health related issues from the use of any product purchased. This website and mobile apps may
          contain external links to other sites that are not under the control of Emirates. We have no control
          over the content or availability of these external sites or the third party content that may appear
          on the website or mobile applications. The inclusion of any links does not imply endorsement of the
          views expressed on those sites.
        </p>
        <p>Emirates reserves the right to modify the disclaimer and other terms of use at any time without prior notice.</p>
        <p>
          We make every effort to ensure that the website and mobile apps function smoothly. However, Emirates
          does not accept responsibility for, and will not be liable for, the website or mobile apps being
          temporarily unavailable due to technical issues beyond our control.
        </p>
        <p>By using our website and/or mobile application, you are agreeing to all the terms and conditions published on Emirates website.</p>

        <div className="policy-info-table">
          <div className="policy-info-table__row">
            <div className="policy-info-table__label">ADDRESS :</div>
            <div className="policy-info-table__value">
              Emirates Gold &amp; Diamonds US Inc, 1665 Oak Tree Rd<br />Suite # 270, Edison, NJ 08820
            </div>
          </div>
          <div className="policy-info-table__row">
            <div className="policy-info-table__label">PHONE :</div>
            <div className="policy-info-table__value">+1 732 372 7357</div>
          </div>
          <div className="policy-info-table__row">
            <div className="policy-info-table__label">E-MAIL :</div>
            <div className="policy-info-table__value">
              <a href="mailto:customercare.us@emirates.com">customercare.us@emirates.com</a>
            </div>
          </div>
          <div className="policy-info-table__row">
            <div className="policy-info-table__label">WEBSITE :</div>
            <div className="policy-info-table__value">
              <a href="/">www.emirates.com/us/</a>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}

export default DisclaimerPolicy;
