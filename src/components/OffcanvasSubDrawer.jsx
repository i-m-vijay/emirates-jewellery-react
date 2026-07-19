import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { fetchCategoriesByMetal } from '../api/categoryApi';

const IMAGE_PATH = '/assets/images/';

const METAL_PROMOS = {
  gold:        `${IMAGE_PATH}luxury-jewellery.jpg`,
  diamond:     `${IMAGE_PATH}best-seller-3.jpg`,
  collections: `${IMAGE_PATH}best-seller-1.jpg`,
  gifts:       `${IMAGE_PATH}gift-1.jpg`,
  default:     `${IMAGE_PATH}offcanvas-all.jpg`,
};

const FALLBACK_ITEMS = [
  { label: 'Earrings',            slug: 'earrings', img: `${IMAGE_PATH}earrings.jpg` },
  { label: 'Pendants',            slug: 'lockets',  img: `${IMAGE_PATH}lockets.jpg` },
  { label: 'Rings',               slug: 'rings',    img: `${IMAGE_PATH}rings.jpg` },
  { label: 'Bangles & Bracelets', slug: 'bangles',  img: `${IMAGE_PATH}bangles.jpg` },
  { label: 'Jewelry Sets',        slug: 'bands',    img: `${IMAGE_PATH}bands.jpg` },
  { label: 'Mangalsutras',        slug: 'bands',    img: `${IMAGE_PATH}couple-rings.jpg` },
  { label: 'Necklaces',           slug: 'necklace', img: `${IMAGE_PATH}beaded-necklace.jpg` },
  { label: 'Gold Coins',          slug: 'rings',    img: `${IMAGE_PATH}bridal-4.jpg` },
];

const PRICE_RANGES = [
  'Below $500', '$500 - $1000', '$1000 - $2000',
  '$2000 - $3000', '$3000 - $5000', 'Above $5000',
];

const SHOP_FOR = ['For Him', 'For Her', 'Kids'];

function IconForHim() {
  return (
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" strokeWidth="1.4">
      <circle cx="12" cy="12" r="8.5" />
      <circle cx="12" cy="12" r="3.5" />
    </svg>
  );
}
function IconForHer() {
  return (
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" strokeWidth="1.4">
      <path d="M5 4.5 Q5 16 12 19.5 Q19 16 19 4.5" strokeLinecap="round" />
      <circle cx="12" cy="21" r="1.8" />
    </svg>
  );
}
function IconKids() {
  return (
    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" strokeWidth="1.4">
      <path d="M7.5 5 Q7.5 14.5 12 17.5 Q16.5 14.5 16.5 5" strokeLinecap="round" />
      <circle cx="12" cy="19.5" r="1.8" />
      <line x1="9.5" y1="15.5" x2="7"   y2="21" strokeLinecap="round" />
      <line x1="14.5" y1="15.5" x2="17" y2="21" strokeLinecap="round" />
    </svg>
  );
}
const SHOP_FOR_ICONS = [IconForHim, IconForHer, IconKids];

function toSlug(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

export default function OffcanvasSubDrawer({ item, metalType, staticItems, onBack, onClose }) {
  const [categories, setCategories] = useState(staticItems || FALLBACK_ITEMS);
  const [loading, setLoading]       = useState(false);

  const promo = METAL_PROMOS[metalType] || METAL_PROMOS.default;

  useEffect(() => {
    // If static items are supplied, use them directly — no API call needed
    if (staticItems) {
      setCategories(staticItems);
      return;
    }

    let stale = false;
    setLoading(true);

    fetchCategoriesByMetal(metalType)
      .then((res) => {
        if (stale) return;

        // normalise varying API shapes
        let list = [];
        if (Array.isArray(res))                                    list = res;
        else if (res.success && Array.isArray(res.categories))     list = res.categories;
        else if (Array.isArray(res.data))                          list = res.data;

        if (list.length > 0) {
          setCategories(
            list.slice(0, 12).map((cat) => {
              const name = cat.categories || cat.name || cat.category || 'Item';
              const slug = toSlug(name);
              const img  = cat.image
                ? cat.image.split(',')[0].trim()
                : `${IMAGE_PATH}${slug}.jpg`;
              return { label: name, slug, img };
            })
          );
        } else {
          setCategories(FALLBACK_ITEMS);
        }
      })
      .catch(() => { if (!stale) setCategories(FALLBACK_ITEMS); })
      .finally(() => { if (!stale) setLoading(false); });

    return () => { stale = true; };
  }, [metalType, staticItems]); // eslint-disable-line react-hooks/exhaustive-deps

  return (
    <div className="oc-sub">
      {/* ── Header ── */}
      <div className="oc-sub__head">
        <button className="oc-sub__back" onClick={onBack}>
          <ChevronLeft size={20} />
          {item}
        </button>
      </div>

      {/* ── Promo banner ── */}
      <img
        src={promo}
        alt={item}
        className="oc-sub__promo"
        onError={(e) => { e.target.style.display = 'none'; }}
      />

      {/* ── Shop all link ── */}
      <Link
        to={`/category/${toSlug(item)}?cat=${encodeURIComponent(item)}`}
        className="oc-sub__shop-all"
        onClick={onClose}
      >
        Shop All {item} Jewelry
        <ChevronRight size={17} />
      </Link>

      <hr className="oc-sub__divider" />

      {/* ── Shop By Style ── */}
      <section className="oc-sub__section">
        <p className="oc-sub__section-title">Shop By Style</p>
        <div className="oc-sub__grid">
          {loading
            ? [...Array(6)].map((_, i) => <div key={i} className="oc-sub__skel" />)
            : categories.map((cat) => (
                <Link
                  key={cat.label}
                  to={`/category/${cat.slug}?cat=${encodeURIComponent(cat.label)}`}
                  className="oc-sub__cat-item"
                  onClick={onClose}
                >
                  <img
                    src={cat.img}
                    alt={cat.label}
                    className="oc-sub__cat-img"
                    loading="lazy"
                    onError={(e) => { e.target.src = `${IMAGE_PATH}default-product.jpg`; }}
                  />
                  <span className="oc-sub__cat-label">{cat.label}</span>
                </Link>
              ))
          }
        </div>
      </section>

      <hr className="oc-sub__divider" />

      {/* ── Shop By Price ── */}
      <section className="oc-sub__section">
        <p className="oc-sub__section-title">Shop By Price</p>
        <div className="oc-sub__grid oc-sub__grid--price">
          {PRICE_RANGES.map((pr) => (
            <div key={pr} className="oc-sub__price-item">{pr}</div>
          ))}
        </div>
      </section>

      <hr className="oc-sub__divider" />

      {/* ── Shop For ── */}
      <section className="oc-sub__section">
        <p className="oc-sub__section-title">Shop for</p>
        <div className="oc-sub__grid oc-sub__grid--for">
          {SHOP_FOR.map((sf, i) => {
            const Icon = SHOP_FOR_ICONS[i];
            return (
              <div key={sf} className="oc-sub__for-item">
                <span className="oc-sub__for-icon"><Icon /></span>
                <span className="oc-sub__for-label">{sf}</span>
              </div>
            );
          })}
        </div>
      </section>
    </div>
  );
}
