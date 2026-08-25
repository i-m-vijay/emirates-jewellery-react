import { PolicyHero, PolicyNav } from '../components/PolicyPageHeader';

function SanctionsCompliancePolicy() {
  return (
    <div className="policy-page">
      <PolicyHero subtitle="Here’s some of our policies that defines our business approach and help us strive forward responsibly." />

      <PolicyNav active="Sanctions Compliance Policy" />

      {/* ── Content ── */}
      <section className="policy-content">
        <h2 className="policy-content__heading">Sanctions Compliance Policy</h2>

        <h3>Definitions:</h3>
        <p>
          &ldquo;Sanctioned Country&rdquo; refers to any country, state, territory, or region subject to
          comprehensive sanctions imposed by the United Nations, European Union, United Kingdom, United States of
          America, or any other applicable competent authority or government. &ldquo;Sanctioned Party&rdquo;
          means any person, entity, body, or vessel designated by a sanctioning authority as subject to
          sanctions.
        </p>
        <p>
          The company reserves the right to interpret these definitions in accordance with its risk assessment
          and legal counsel&rsquo;s advice.
        </p>

        <h3>Prohibition of Transactions:</h3>
        <p>
          The company prohibits transactions with Sanctioned Countries or Parties to the extent required by
          applicable laws. The company shall not, directly or indirectly, sell, supply, transfer, or export any
          products, goods, or services to or for the benefit of any Sanctioned Country or Sanctioned Party, or for
          any use, purpose, or activity prohibited under applicable sanctions laws or regulations.
        </p>
        <p>We retain discretion to engage in permissible transactions where legally allowed and aligned with our business interests.</p>

        <h3>Compliance Procedures:</h3>
        <p>We implement risk-based compliance procedures, which may include:</p>
        <ul className="policy-list">
          <li>Screening customers and transactions against relevant sanctions lists</li>
          <li>Conducting appropriate due diligence</li>
          <li>Periodic review and update of compliance procedures</li>
          <li>Monitoring sanctions regime changes</li>
          <li>Risk assessments as deemed necessary</li>
          <li>Adapting processes based on business needs and risk profile</li>
        </ul>

        <h3>Reporting:</h3>
        <p>
          Employees are encouraged to report suspected compliance issues through appropriate channels. The
          company shall address such reports in accordance with internal policies and legal requirements and
          deny such purchases.
        </p>
        <p><strong>Ongoing Compliance:</strong></p>
        <p>
          We strive to comply with applicable sanctions laws, subject to permissible exceptions and
          interpretations that serve our business interests.
        </p>

        <h3>Certification and Audits:</h3>
        <p>
          Upon reasonable request and subject to confidentiality agreements, we may provide appropriate
          assurances of our sanctions compliance efforts. Any audits will be conducted in a manner that protects
          our proprietary information and business interests.
        </p>

        <h3>Supply Chain Due Diligence:</h3>
        <p>
          We exercise risk-based due diligence in our supply chain and business relationships, balancing
          compliance obligations with operational efficiency.
        </p>

        <h3>Record Keeping:</h3>
        <p>We maintain records as required by applicable laws, while preserving our right to determine the scope and format of such records.</p>

        <h3>Training:</h3>
        <p>Relevant employees receive appropriate training on sanctions compliance, tailored to their roles and our business needs.</p>
        <p>
          This policy is integral to our operations and shall survive any termination or expiration of any
          business agreements. This policy is subject to change at the company&rsquo;s discretion. It shall be
          interpreted in a manner that best protects the company&rsquo;s interests while maintaining compliance
          with mandatory legal requirements.
        </p>
      </section>
    </div>
  );
}

export default SanctionsCompliancePolicy;
