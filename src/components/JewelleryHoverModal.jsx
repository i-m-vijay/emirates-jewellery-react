import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { fetchCategoriesByMetal } from '../api/categoryApi';

const IMAGE_PATH = '/assets/images/';
const CATIMAGES_PATH = '/assets/images/catimages/';

// ── Slug → catimages filename map ────────────────────────────────────────────
const SLUG_TO_CATIMAGE = {
  'bands':             'bands.jpg',
  'bangles':           'bangles.jpg',
  'beaded-necklaces':  'beaded-necklaces.jpg',
  'cuban-chains':      'cuban-chains.jpg',
  'dangle-earrings':   'dangle-earrings.jpg',
  'diamond-ring':      'diamond-ring.jpg',
  'drop-earrings':     'drop-earrings.jpg',
  'earrings':          'earrings.jpg',
  'hoop-earrings':     'hoop-earrings.jpg',
  'kids-jewellery':    'Kids-Jewellery.jpg',
  'lockets':           'lockets.jpg',
  'rings':             'rings.jpg',
  'mens-jewellery':    "Men's-Jwellery.jpg",
  'womens-jewellery':  "Women's-Jwellery.jpg",
};

// ── nav label → metal_type param expected by the API ────────────────────────
const NAV_TO_METAL = {
  Gold:        'gold',
  Diamond:     'diamond',
  Rings:       'rings',
  Earrings:    'earrings',
  Necklaces:   'necklaces',
  Wedding:     'wedding',
  Collections: 'gold',
  Gifts:       'gold',
};

// ── Hardcoded categories for nav items that don't use the API ───────────────
const STATIC_ITEMS = {
  Collections: [
    { label: 'Rings',           slug: 'rings',           img: `${CATIMAGES_PATH}rings.jpg` },
    { label: 'Bands',           slug: 'bands',           img: `${CATIMAGES_PATH}bands.jpg` },
    { label: 'Bangles',         slug: 'bangles',         img: `${CATIMAGES_PATH}bangles.jpg` },
    { label: 'Beaded Necklaces', slug: 'beaded-necklaces', img: `${CATIMAGES_PATH}beaded-necklaces.jpg` },
  ],
  Gifts: [
    { label: 'Drop Earrings', slug: 'drop-earrings', img: `${CATIMAGES_PATH}drop-earrings.jpg` },
    { label: 'Hoop Earrings', slug: 'hoop-earrings', img: `${CATIMAGES_PATH}hoop-earrings.jpg` },
    { label: 'Bangles',       slug: 'bangles',       img: `${CATIMAGES_PATH}bangles.jpg` },
    { label: 'Rings',         slug: 'rings',         img: `${CATIMAGES_PATH}rings.jpg` },
  ],
};

// ── Fallback items shown when API returns nothing or fails ───────────────────
const FALLBACK_ITEMS = [
  { label: 'Earrings',            slug: 'earrings',  img: `${IMAGE_PATH}earrings.jpg` },
  { label: 'Pendants',            slug: 'lockets',   img: `${IMAGE_PATH}lockets.jpg` },
  { label: 'Rings',               slug: 'rings',     img: `${IMAGE_PATH}rings.jpg` },
  { label: 'Bangles & Bracelets', slug: 'bangles',   img: `${IMAGE_PATH}bangles.jpg` },
  { label: 'Jewelry Sets',        slug: 'bands',     img: `${IMAGE_PATH}bands.jpg` },
  { label: 'Mangalsutras',        slug: 'bands',     img: `${IMAGE_PATH}couple-rings.jpg` },
  { label: 'Necklaces',           slug: 'necklaces', img: `${IMAGE_PATH}beaded-necklace.jpg` },
  { label: 'Gold Coins',          slug: 'rings',     img: `${IMAGE_PATH}bridal-4.jpg` },
];

// ── Static sections ──────────────────────────────────────────────────────────
const PRICE_RANGES = [
  { label: '$500 - $1,000',   range: '500-1000'  },
  { label: '$1,000 - $2,000', range: '1000-2000' },
  { label: '$2,000 - $3,000', range: '2000-3000' },
  { label: '$3,000 - $5,000', range: '3000-5000' },
  { label: 'Above $5,000',    range: '5000-up'   },
];

const SHOP_FOR = [
  { label: 'For Him', gender: 'for_him' },
  { label: 'For Her', gender: 'for_her' },
  { label: 'Kids',    gender: 'kids' },
];

// ── Accent colour and promo image per nav label ──────────────────────────────
const NAV_ACCENTS = {
  Gold:        '#c5933a',
  Diamond:     '#5b8dd9',
  Rings:       '#8b1f1f',
  Earrings:    '#8b1f1f',
  Necklaces:   '#8b1f1f',
  Wedding:     '#c5933a',
  Collections: '#005a55',
  Gifts:       '#c5933a',
};

const NAV_PROMOS = {
  Gold:        `${IMAGE_PATH}luxury-jewellery.jpg`,
  Diamond:     `${IMAGE_PATH}best-seller-3.jpg`,
  Rings:       `${IMAGE_PATH}rings.jpg`,
  Earrings:    `${IMAGE_PATH}earrings.jpg`,
  Necklaces:   `${IMAGE_PATH}beaded-necklace.jpg`,
  Wedding:     `${IMAGE_PATH}bridal-2.jpg`,
  Collections: `${IMAGE_PATH}best-seller-1.jpg`,
  Gifts:       `${IMAGE_PATH}gift-1.jpg`,
};

// ── SVG icons for "Shop For" ─────────────────────────────────────────────────
function IconForHim() {
  return (
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="1.4">
      <circle cx="12" cy="12" r="8.5" />
      <circle cx="12" cy="12" r="3.5" />
    </svg>
  );
}
function IconForHer() {
  return (
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="1.4">
      <path d="M5 4.5 Q5 16 12 19.5 Q19 16 19 4.5" strokeLinecap="round" />
      <circle cx="12" cy="21" r="1.8" />
    </svg>
  );
}
function IconKids() {
  return (
    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" strokeWidth="1.4">
      <path d="M7.5 5 Q7.5 14.5 12 17.5 Q16.5 14.5 16.5 5" strokeLinecap="round" />
      <circle cx="12" cy="19.5" r="1.8" />
      <line x1="9.5" y1="15.5" x2="7"   y2="21" strokeLinecap="round" />
      <line x1="14.5" y1="15.5" x2="17" y2="21" strokeLinecap="round" />
    </svg>
  );
}
const SHOP_FOR_ICONS = [IconForHim, IconForHer, IconKids];

// ── Helpers ──────────────────────────────────────────────────────────────────
function toSlug(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function SkeletonItems() {
  return (
    <>
      {[...Array(8)].map((_, i) => (
        <div key={i} className="jwellery-mega__style-item">
          <div className="jwellery-mega__style-img jwellery-mega__skel-circle" />
          <div className="jwellery-mega__skel-label" />
        </div>
      ))}
    </>
  );
}

// ── Component ─────────────────────────────────────────────────────────────────
export default function JewelleryHoverModal({ item, onMouseEnter, onMouseLeave, onClose }) {
  const [styleItems, setStyleItems] = useState(FALLBACK_ITEMS);
  const [loading, setLoading] = useState(false);

  const accent    = NAV_ACCENTS[item] || '#8b1f1f';
  const promo     = NAV_PROMOS[item]  || NAV_PROMOS.Gold;
  const metalType = NAV_TO_METAL[item];

  useEffect(() => {
    if (STATIC_ITEMS[item]) {
      setStyleItems(STATIC_ITEMS[item]);
      return;
    }

    if (!metalType) return;

    let stale = false;
    setLoading(true);

    fetchCategoriesByMetal(metalType)
      .then((res) => {
        if (stale) return;
        if (res.success && Array.isArray(res.categories) && res.categories.length > 0) {
          setStyleItems(
            res.categories.slice(0, 12).map((cat) => {
              const name = cat.categories;
              const slug = cat.slug || toSlug(name);
              const catImage = SLUG_TO_CATIMAGE[slug]
                ? `${CATIMAGES_PATH}${SLUG_TO_CATIMAGE[slug]}`
                : `${CATIMAGES_PATH}${slug}.jpg`;
              const fallbackImg = cat.image
                ? cat.image.split(',')[0].trim()
                : `${IMAGE_PATH}${slug}.jpg`;
              return {
                label: name,
                slug,
                img: catImage,
                fallbackImg,
              };
            })
          );
        } else {
          setStyleItems(FALLBACK_ITEMS);
        }
      })
      .catch(() => {
        if (!stale) setStyleItems(FALLBACK_ITEMS);
      })
      .finally(() => { if (!stale) setLoading(false); });

    return () => { stale = true; };
  }, [item]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <div className="jwellery-mega" onMouseEnter={onMouseEnter} onMouseLeave={onMouseLeave}>

      {/* ── Col 1: Shop By Style (API-driven) ── */}
      <div className="jwellery-mega__col">
        <p className="jwellery-mega__title" style={{ '--mega-accent': accent }}>Shop By Style</p>
        <div className="jwellery-mega__style-grid">
          {loading
            ? <SkeletonItems />
            : styleItems.map((si) => (
                <Link
                    key={si.label}
                    to={`/category/${si.slug}?metal_type=${encodeURIComponent(metalType || '')}&cat=${encodeURIComponent(si.label)}`}
                    className="jwellery-mega__style-item"
                    onClick={onClose}
                  >
                  <img
                    src={si.img}
                    alt={si.label}
                    className="jwellery-mega__style-img"
                    loading="lazy"
                    data-fallback={si.fallbackImg || ''}
                    onError={(e) => {
                      const fb = e.currentTarget.dataset.fallback;
                      if (fb && e.currentTarget.src !== fb) {
                        e.currentTarget.src = fb;
                        e.currentTarget.dataset.fallback = '';
                      } else {
                        e.currentTarget.src = `${IMAGE_PATH}default-product.jpg`;
                      }
                    }}
                  />
                  <span className="jwellery-mega__style-label">{si.label}</span>
                </Link>
              ))
          }
        </div>
      </div>

      {/* ── Col 2: Shop By Price ── */}
      <div className="jwellery-mega__col">
        <p className="jwellery-mega__title" style={{ '--mega-accent': accent }}>Shop By Price</p>
        <ul className="jwellery-mega__list">
          {PRICE_RANGES.map((pr) => (
            <li key={pr.label}>
              <Link
                className="jwellery-mega__link"
                to={`/jewellery/by-price?metal=${metalType || ''}&range=${pr.range}`}
                onClick={onClose}
              >
                {pr.label}
              </Link>
            </li>
          ))}
        </ul>
      </div>

      {/* ── Col 3: Shop For ── */}
      <div className="jwellery-mega__col">
        <p className="jwellery-mega__title" style={{ '--mega-accent': accent }}>Shop For</p>
        <ul className="jwellery-mega__list">
          {SHOP_FOR.map((sf, i) => {
            const Icon = SHOP_FOR_ICONS[i];
            return (
              <li key={sf.label} className="jwellery-mega__for-item">
                <span className="jwellery-mega__for-icon"><Icon /></span>
                <Link
                  className="jwellery-mega__link"
                  to={`/jewellery/by-gender?metal=${metalType || ''}&gender=${sf.gender}`}
                  onClick={onClose}
                >
                  {sf.label}
                </Link>
              </li>
            );
          })}
        </ul>
      </div>

      {/* ── Col 4: Promo image ── */}
      <div className="jwellery-mega__col jwellery-mega__col--promo">
        <img
          src={promo}
          alt={`${item} collection`}
          className="jwellery-mega__promo-img"
          loading="lazy"
        />
      </div>

    </div>
  );
}
