import { Link } from 'react-router-dom';

const IMAGE_PATH = '/assets/images/';

// Maps a policy nav label to its route — only pages that exist get a working link.
const POLICY_ROUTES = {
  Disclaimer:       '/disclaimer-policy',
  'Privacy Policy': '/privacy-policy',
  'Shipping Policy': '/shipping-policy',
  'Terms and Condition': '/terms-and-condition',
  'Exchange & Return': '/exchange-return-policy',
  'Cancellation Policy': '/cancellation-policy',
  'Ethics and Policies': '/ethics-and-policies',
  Copyright: '/copyright-policy',
  'Sanctions Compliance Policy': '/sanctions-compliance-policy',
};

const POLICY_NAV = [
  'Disclaimer',
  'Privacy Policy',
  'Shipping Policy',
  'Terms and Condition',
  'Exchange & Return',
  'Cancellation Policy',
  'Ethics and Policies',
  'Copyright',
  'Sanctions Compliance Policy',
];

export function PolicyHero({ eyebrow = 'OUR', title = 'POLICIES', subtitle }) {
  return (
    <section className="policy-hero">
      <div className="policy-hero__info">
        <span className="policy-hero__eyebrow">{eyebrow}</span>
        <h1 className="policy-hero__title">{title}</h1>
        {subtitle && <p className="policy-hero__subtitle">{subtitle}</p>}
      </div>

      <div className="policy-hero__img-wrap">
        <img
          src={`${IMAGE_PATH}luxury-jewellery.jpg`}
          alt="Emirates Gold & Diamonds"
          loading="eager"
          decoding="async"
        />
      </div>
    </section>
  );
}

export function PolicyNav({ active }) {
  return (
    <div className="policy-nav">
      {POLICY_NAV.map((label) => {
        const to = POLICY_ROUTES[label];
        const className = [
          'policy-nav__item',
          label === active && 'policy-nav__item--active',
          to && 'policy-nav__item--linked',
        ].filter(Boolean).join(' ');

        const content = (
          <>
            <span className="policy-nav__diamond" />
            <span className="policy-nav__label">{label}</span>
          </>
        );

        return to ? (
          <Link key={label} to={to} className={className}>{content}</Link>
        ) : (
          <div key={label} className={className}>{content}</div>
        );
      })}
    </div>
  );
}
