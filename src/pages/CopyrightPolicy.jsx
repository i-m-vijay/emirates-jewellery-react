import { PolicyHero, PolicyNav } from '../components/PolicyPageHeader';

function CopyrightPolicy() {
  return (
    <div className="policy-page">
      <PolicyHero subtitle="Here’s some of our policies that defines our business approach and help us strive forward responsibly." />

      <PolicyNav active="Copyright" />

      {/* ── Content ── */}
      <section className="policy-content">
        <h2 className="policy-content__heading">Copyright and Restriction for use of Content</h2>
        <p>
          Emirates retains all intellectual property rights for the content on their website, including text and
          images. These rights are protected by international laws, including copyright and trademark laws.
        </p>
        <p>
          As a user you are allowed to view the website content and print a single copy for personal use. You can
          also save files on your computer and reference website documents for personal browsing. However, these
          permissions end if you violate the website&rsquo;s terms and conditions.
        </p>
        <p>
          Emirates strictly prohibit any other use of their website content without express written consent. This
          includes modifying, publishing, reproducing, creating derivative works, or incorporating the content
          into another website.
        </p>
        <p>
          Users can submit reviews, comments and other information (&ldquo;User Content&rdquo;) to the website. By
          doing so you grant Emirates a broad license to use this content in any way they choose without
          compensation. You must ensure your submissions are original and does not infringe on others rights.
        </p>
        <p>
          Emirates may use, modify and distribute any User Content without restriction or compensation. Emirates
          is not obligated to keep User Content confidential, pay for it, or respond to it. Users are responsible
          for their submissions and must indemnify Emirates against any resulting claims.
        </p>
        <p>
          While Emirates does not regularly review User Content they reserve the right to monitor, edit, or
          remove it. Users must provide accurate information and not impersonate others when submitting content.
          Emirates assumes no liability for any User Content.
        </p>
      </section>
    </div>
  );
}

export default CopyrightPolicy;
