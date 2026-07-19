import { PolicyHero, PolicyNav } from '../components/PolicyPageHeader';

function CancellationPolicy() {
  return (
    <div className="policy-page">
      <PolicyHero subtitle="Here’s some of our policies that defines our business approach and help us strive forward responsibly." />

      <PolicyNav active="Cancellation Policy" />

      {/* ── Content ── */}
      <section className="policy-content">
        <h2 className="policy-content__heading">Cancellation Policy</h2>
        <p>
          In case you have any questions pertaining to the product and if it&rsquo;s related to cancellation,
          please reach out to our Support Service team using the address below:
          <br />
          Emirates, 1665 Oak Tree Rd, Suite # 270, Edison, NJ 08820 Tel: +1 732 372 7357 Email:{' '}
          <a href="mailto:customercare.us@emirates.com">customercare.us@emirates.com</a>
        </p>

        <h3>How to Cancel an Order through Email:</h3>
        <p>
          Please send a cancellation request from your registered email to{' '}
          <a href="mailto:customercare.us@emirates.com">customercare.us@emirates.com</a>.
        </p>

        <h3>Important:</h3>
        <p>Cancellation is not possible once a product has been shipped.</p>

        <h3>Cancellation:</h3>
        <p>
          Go to the My Account section on Emirates website or mobile app under Order History and check the boxes
          next to the selected orders that you wish to cancel.
        </p>

        <h3>Post-Cancellation Process:</h3>
        <p>
          Once we complete your request, you will receive a confirmation email at the registered email address.
          <br />
          The refund will be processed using the original payment method and will be sent out within 7-10
          business days.
        </p>

        <h3>Additional Information:</h3>
        <p>
          Emirates reserves the right for order cancellation anytime due to limited product availability or
          incorrect product or pricing information.
        </p>
        <p>
          The cancellation policy does not apply to: Ornaments in custom design gold and gold bars coins Items
          that undergone resizing or other alterations. Specific model, pattern or design items taken on order
          Souvenirs for celebrations including idols.
        </p>
        <p>
          Before proceeding to cancellation, feel free to contact our customer support team for assistance
          concerning any issues that require attention.
        </p>
        <p>
          Any disputes arising shall be discussed and settled mutually between the parties and if failed to
          settle any disputes the same shall be referred to the laws and jurisdiction of New Jersey Courts.
        </p>
      </section>
    </div>
  );
}

export default CancellationPolicy;
