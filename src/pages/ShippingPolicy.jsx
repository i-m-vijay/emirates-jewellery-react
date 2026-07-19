import { PolicyHero, PolicyNav } from '../components/PolicyPageHeader';

function ShippingPolicy() {
  return (
    <div className="policy-page">
      <PolicyHero subtitle="Here’s some of our policies that defines our business approach and help us strive forward responsibly." />

      <PolicyNav active="Shipping Policy" />

      {/* ── Content ── */}
      <section className="policy-content">
        <h2 className="policy-content__heading">Shipping Policy</h2>
        <p>
          Emirates provides free delivery/shipping for orders above $750 &amp; the merchandise below $750 will be
          charged $45 as shipment charges per shipment. The delivery will be made within serviceable zip code and
          address within the United States of America (USA). Our delivery duration depends on the product selected
          and service availability. Goods will not be shipped outside the USA.
        </p>

        <h3>Processing</h3>
        <p>
          Online orders are processed and/or shipped from Monday to Friday (weekdays). We do not process or ship
          orders on Saturdays, Sundays or Public Holidays. Orders placed and received on weekdays will be processed
          and/or shipped on the next business day.
        </p>
        <p>
          Orders placed after 3:00 PM EST on Monday to Friday (excluding Public Holidays) will be processed on the
          following business day. If an item is not in stock, you will be notified by our client service team
          within 24 business hours of your online purchase.
        </p>
        <p>
          Your item(s) will be individually packaged in Emirates jewelry boxes along with security wrapping to
          protect the jewelry. A purchase invoice that reflects the details of your order will be enclosed inside
          your package.
        </p>
        <p>
          Emirates shall not be held responsible for the refund or return of any customized product once the same
          is purchased. The same shall apply to any product which is ordered by the customer specifically
          requesting the model, pattern or any souvenirs sold on occasions including idols.
        </p>

        <h3>Shipping</h3>
        <p>
          All packages are shipped through Brinks/FedEx/Shipping Partner delivery services. Emirates Jewelers do
          not ship any consignment to Post Office Boxes (P.O. Box).
        </p>
        <p>
          Once your package is shipped, you will be notified via email with your package(s) information including
          your Brinks/FedEx/Shipping Partner tracking number.
        </p>

        <h3>Processing</h3>
        <p>
          We offer safe, secure, shipping on all orders. Each consignment out for delivery is fully insured to the
          maximum value of the product. Estimated arrival time for packages may depend on the courier service
          provider and may be affected by external factors like Public Holidays, Extreme Weather Conditions,
          Disruptions experience by the shipping/vendor companies, Force majeure etc. for which Emirates shall not
          be held responsible. Emirates shall not be liable for any delay or failure to perform due to
          circumstances beyond our reasonable control, including but not limited to acts of God, natural
          disasters, pandemics, government actions, or civil unrest.
        </p>
        <p>
          Additionally, specific client preferences on orders may also affect the estimated delivery times for
          ordered items. Preferences include transit insurance for the products.
        </p>
        <p>
          Emirates&rsquo; liability is limited to the acts of its own employees and does not extend to the acts or
          omissions of third party carriers.
        </p>

        <h3>Privacy and data protection</h3>
        <p>
          The customer data collected for shipping purposes will be used and protected, in compliance with
          relevant data protection laws of the concerned jurisdiction.
        </p>

        <h3>Delivery Process</h3>
        <p>
          Customer shall provide the correct details of Recipient Name (as stated in the photo identification)
          with complete Address, nearby landmark, zip code and contact number for hassle free delivery.
        </p>
        <p>
          At the time of delivery, the recipient shall provide any of the below mentioned identity proofs &amp; a
          recipient signature to collect the product, if requested by the Shipping Partner. Customers are expected
          to cooperate with them to ensure safe delivery:
        </p>
        <p>* Valid Passport / Driving Licence</p>
        <p>
          If the package is found to be tampered, the customers must reject the same and report it to us through
          customercare.us@emirates.com immediately. No claims shall be entertained after the acceptance of the
          consignment. When the recipient is not available at the address during the first delivery attempt, the
          Shipping Partner shall store the item at the nearby Shipping Partner facility and customers may collect
          the item from the Shipping Partner facility intimated. The item will be stored in the courier facility
          for 2 days. In case the recipient fails to collect the item/s within the given timeframe, the
          consignment will be returned to the consigner. The customer shall reschedule the shipment, contacting
          the customer care, with an additional shipping cost of $45.
        </p>

        <h3>Change in Delivery Address:</h3>
        <p>
          Prior to the dispatch of the product/consignment, customers may change the delivery address by writing
          to customercare.us@emirates.com or by calling our customer care team at +1 732 372 7357. After the
          dispatch Emirates shall not be responsible for delivering the consignment to the new address provided
          there is an additional cost paid by the customer for an amount of $45.
        </p>
        <p>
          Emirates reserves the right to modify these shipping policy terms at any time. Changes will be effective
          immediately upon posting on our website. Customers are responsible for reviewing the terms before each
          purchase. By placing the order the customer is deemed to have agreed with all the terms and conditions
          of the shipping policy.
        </p>
        <p>
          Emirates Jewelry is currently registered to collect sales tax in states in the U.S. that imposes a sales
          tax. Sales tax will be automatically applied to your order based on applicable state and local sales tax
          laws and shipping destination as per Emirates registration.
        </p>
        <p>
          The prices displayed for the Products sold on this website are exclusive of taxes. Applicable taxes will
          be shown at the point of checkout prior to payment.
        </p>
        <p>The terms contained herein are subject to change as per the tax regulations implemented by the States from time to time.</p>
        <p>
          Any disputes arising shall be discussed and settled mutually between the parties and if failed to settle
          any disputes the same shall be referred to the laws and jurisdiction of New Jersey courts.
        </p>
      </section>
    </div>
  );
}

export default ShippingPolicy;
