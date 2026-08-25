import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { ShieldCheck, Truck, MapPin } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';
import { fetchJewelleryCategories } from '../api/categoryApi';

const IMAGE_PATH = '/assets/images/';
const CATIMAGE_PATH = '/assets/images/catimages/';

const heroSlides = [
  `${IMAGE_PATH}banner_03.jpg`,
  // `${IMAGE_PATH}banner_01.jpg`,
  `${IMAGE_PATH}banner_02.jpg`,
];

// Slugs that have a matching file in catimages/
const CATIMAGE_SLUGS = new Set([
  'bands', 'bangles', 'beaded-necklaces', 'cuban-chains',
  'dangle-earrings', 'diamond-ring', 'drop-earrings',
  'earrings', 'hoop-earrings', 'lockets', 'rings',
]);

// Ordered fallbacks for slugs without a matching catimage
const CATIMAGE_FALLBACKS = [
  'engage2.jpg', 'img4.jpg', 'img5.jpg', 'img6.jpg',
  'img7.jpg', 'img8.jpg', 'img13.jpg', 'img14.jpg', 'img17.jpg',
];

function getCatImage(slug, fallbackIdx = 0) {
  if (CATIMAGE_SLUGS.has(slug)) return `${CATIMAGE_PATH}${slug}.jpg`;
  return `${CATIMAGE_PATH}${CATIMAGE_FALLBACKS[fallbackIdx % CATIMAGE_FALLBACKS.length]}`;
}

const FALLBACK_CATEGORIES = [
  { title: 'Rings',            slug: 'rings',            image: getCatImage('rings') },
  { title: 'Bangles',          slug: 'bangles',          image: getCatImage('bangles') },
  { title: 'Bands',            slug: 'bands',            image: getCatImage('bands') },
  { title: 'Lockets',          slug: 'lockets',          image: getCatImage('lockets') },
  { title: 'Beaded Necklaces', slug: 'beaded-necklaces', image: getCatImage('beaded-necklaces') },
  { title: 'Earrings',         slug: 'earrings',         image: getCatImage('earrings') },
  { title: 'Hoop Earrings',    slug: 'hoop-earrings',    image: getCatImage('hoop-earrings') },
  { title: 'Cuban Chains',     slug: 'cuban-chains',     image: getCatImage('cuban-chains') },
  { title: 'Dangle Earrings',  slug: 'dangle-earrings',  image: getCatImage('dangle-earrings') },
  { title: 'Drop Earrings',    slug: 'drop-earrings',    image: getCatImage('drop-earrings') },
  { title: 'Solitaire Rings',  slug: 'solitaire-rings',  image: getCatImage('solitaire-rings', 1) },
];

// These four are hidden from the Shop By Category grid
const SHOP_BY_CAT_EXCLUDE = new Set([
  'solitaire rings', 'multi-stone rings', 'drop earrings', 'dangle earrings', 'hoop earrings',
]);

const SECTION_CATS = {
  bestSellers: ['Rings', 'Bands', 'Bangles'],
  gift:        ['Solitaire Rings', 'Bangles', 'Beaded Necklaces'],
  bridal:      ['Beaded Necklaces', 'Drop Earrings', 'Dangle Earrings', 'Bangles', 'Lockets'],
  social:      ['Beaded Necklaces', 'Solitaire Rings', 'Multi-Stone Rings'],
  blogs:       ['Rings', 'Beaded Necklaces', 'Dangle Earrings'],
};

const GENDER_ITEMS = [
  { title: "Men's Jwellery",   slug: 'mens-jewellery',   image: `${CATIMAGE_PATH}Men's-Jwellery.jpg` },
  { title: "Women's Jwellery", slug: 'womens-jewellery', image: `${CATIMAGE_PATH}Women's-Jwellery.jpg` },
  { title: 'Kids Jewellery',   slug: 'kids-jewellery',   image: `${CATIMAGE_PATH}Kids-Jewellery.jpg` },
];

const INITIAL_VISIBLE = 3;
const INITIAL_CATS_VISIBLE = 10;

function SectionTitle({ title, subtitle }) {
  return (
    <div className="section-title">
      <span>{subtitle}</span>
      <h2>{title}</h2>
    </div>
  );
}

function ImageCard({ title, image, className = '', eager = false }) {
  return (
    <motion.div className={`image-card ${className}`.trim()} whileHover={{ y: -6 }}>
      <img
        src={image}
        alt={title}
        loading={eager ? 'eager' : 'lazy'}
        decoding={eager ? 'sync' : 'async'}
      />
      <h3>{title}</h3>
    </motion.div>
  );
}

function ViewMoreBtn({ expanded, total, onClick }) {
  if (total <= INITIAL_VISIBLE) return null;
  return (
    <button className="outline-btn" onClick={onClick}>
      {expanded ? 'View Less' : 'View More'}
    </button>
  );
}

function Home() {
  const navigate = useNavigate();
  const [allCategories, setAllCategories] = useState(FALLBACK_CATEGORIES);
  const [videoUrl, setVideoUrl]           = useState(null);

  const [showAllCats,         setShowAllCats]         = useState(false);
  const [showMoreBestSellers, setShowMoreBestSellers] = useState(false);
  const [showMoreSocial,      setShowMoreSocial]      = useState(false);
  const [showMoreBlogs,       setShowMoreBlogs]       = useState(false);

  useEffect(() => {
    let stale = false;
    fetchJewelleryCategories(5)
      .then((response) => {
        if (stale) return;
        const list = response?.data ?? response;
        if (!Array.isArray(list) || list.length === 0) return;

        let fallbackIdx = 0;
        setAllCategories(
          list.map((cat) => {
            const image = CATIMAGE_SLUGS.has(cat.slug)
              ? `${CATIMAGE_PATH}${cat.slug}.jpg`
              : `${CATIMAGE_PATH}${CATIMAGE_FALLBACKS[fallbackIdx++ % CATIMAGE_FALLBACKS.length]}`;
            return { title: cat.category, slug: cat.slug, image };
          })
        );

        // Find first product with a video URL across all categories
        let foundVideo = null;
        outer: for (const cat of list) {
          for (const product of (cat.products ?? [])) {
            const raw = product.meta_gemhub_video_urls;
            if (raw) {
              const url = Array.isArray(raw)
                ? raw[0]
                : String(raw).split(',')[0].trim();
              if (url) { foundVideo = url; break outer; }
            }
          }
        }
        if (foundVideo) setVideoUrl(foundVideo);
      })
      .catch(() => {});
    return () => { stale = true; };
  }, []);

  // Returns API categories ordered by the specified names list
  const filterByNames = (names) => {
    const lower = names.map((n) => n.toLowerCase());
    const map = Object.fromEntries(
      allCategories
        .filter((cat) => lower.includes(cat.title.toLowerCase()))
        .map((cat) => [cat.title.toLowerCase(), cat])
    );
    return names.map((n) => map[n.toLowerCase()]).filter(Boolean);
  };

  const filteredForShopByCat = allCategories.filter(
    (cat) => !SHOP_BY_CAT_EXCLUDE.has(cat.title.toLowerCase())
  );
  const catDisplay  = showAllCats
    ? filteredForShopByCat
    : filteredForShopByCat.slice(0, INITIAL_CATS_VISIBLE);
  const bestSellers = filterByNames(SECTION_CATS.bestSellers);
  const gift        = filterByNames(SECTION_CATS.gift);
  const bridal      = filterByNames(SECTION_CATS.bridal);
  const social      = filterByNames(SECTION_CATS.social);
  const blogCats    = filterByNames(SECTION_CATS.blogs);

  return (
    <>
      {/* ── Hero Slider ── */}
      <section className="hero-slider">
        <div className="hero-track" data-slide-count={heroSlides.length}>
          {heroSlides.map((slide, index) => (
            <div className="hero-slide" key={slide}>
              <img
                src={slide}
                alt={`Banner ${index + 1}`}
                loading={index === 0 ? 'eager' : 'lazy'}
                decoding={index === 0 ? 'sync' : 'async'}
              />
            </div>
          ))}
        </div>
      </section>

      {/* ── Shop By Category — excludes Solitaire Rings, Multi-Stone Rings, Drop Earrings, Dangle Earrings ── */}
      <section className="section shop-by-cat">
        <img src={`${IMAGE_PATH}heading-vector.svg`} alt="" className="heading-vector" loading="lazy" />
        <SectionTitle title="Shop By Category" subtitle="Explore our finest jewellery range" />
        <div className="grid grid-3">
          {catDisplay.map((item, index) => {
            const shouldCenter = catDisplay.length % 3 === 1 && index === catDisplay.length - 1;

            return (<Link key={item.title} to={`/category/${item.slug}`} className={`category-card-link ${shouldCenter ? "col-start-2" : ""}`}>
              <ImageCard title={item.title} image={item.image} />
            </Link>)})}
        </div>
        {filteredForShopByCat.length > INITIAL_CATS_VISIBLE && (
          <button className="outline-btn" onClick={() => setShowAllCats(!showAllCats)}>
            {showAllCats ? 'View Less' : 'View All Categories'}
          </button>
        )}
      </section>

      {/* ── Best Sellers — Rings, Bands, Bangles ── */}
      <section className="section cream">
        <SectionTitle title="The Best Sellers" subtitle="Our most loved pieces" />
        <div className="grid grid-3 portrait">
          {(showMoreBestSellers ? bestSellers : bestSellers.slice(0, INITIAL_VISIBLE)).map((item) => (
            <Link key={item.title} to={`/category/${item.slug}`} className="category-card-link">
              <ImageCard title={item.title} image={item.image} className="seller-card" />
            </Link>
          ))}
        </div>
        <ViewMoreBtn
          expanded={showMoreBestSellers}
          total={bestSellers.length}
          onClick={() => setShowMoreBestSellers(!showMoreBestSellers)}
        />
      </section>

      {/* ── Gift By Occasion — Bands, Beaded Necklaces, Hoop Earrings ── */}
      <section className="section">
        <SectionTitle title="Gift By Occasion" subtitle="Celebrate every special moment" />
        <div className="grid grid-3 portrait">
          {gift.slice(0, 3).map((item, idx) => (
            <Link key={item.title} to={`/category/${item.slug}`} className="category-card-link">
              <ImageCard
                title={item.title}
                image={`${IMAGE_PATH}gift-${idx + 1}.jpg`}
                className="gift-card"
              />
            </Link>
          ))}
        </div>
      </section>

      <section className="wide-offer">
        <div>
          <span>Gold &amp; Diamond Savings</span>
          <h2>Exclusive Jewellery Offers</h2>
          <p>Discover premium pieces with special seasonal benefits.</p>
          <button onClick={() => navigate('/jewellery/offers')}>Explore Offers</button>
          </div>
      </section>

      {/* ── Bridal Collection — Beaded Necklaces, Drop Earrings, Dangle Earrings, Bangles, Lockets ── */}
      <section className="section">
        <SectionTitle title="Bridal Collection" subtitle="Inspired by royal celebrations" />
        <div className="bridal-grid">
          {bridal.slice(0, 5).map((item, idx) => (
            <Link key={item.title} to={`/category/${item.slug}`} className={`category-card-link bridal-item-${idx}`}>
              <ImageCard
                title={item.title}
                image={`${IMAGE_PATH}bridal-${idx + 1}.jpg`}
                className="bridal-card-item"
              />
            </Link>
          ))}
        </div>
      </section>

      {/* ── Shop By Gender — Men's, Women's, Kids ── */}
      <section className="section cream">
        <SectionTitle title="Shop By Gender" subtitle="Jewellery for everyone" />
        <div className="grid grid-3 portrait">
          {GENDER_ITEMS.map((item) => (
            <Link key={item.title} to={`/category/${item.slug}`} className="category-card-link">
              <ImageCard title={item.title} image={item.image} className="gender-card" />
            </Link>
          ))}
        </div>
      </section>

      {/* ── Video Section ── */}
      <section
        className="video-section"
        style={videoUrl ? { background: 'none' } : undefined}
      >
        {videoUrl ? (
          <video
            src={videoUrl}
            autoPlay
            muted
            loop
            playsInline
            controls
            style={{ width: '100%', height: '100%', objectFit: 'cover' }}
          />
        ) : (
          <div className="play-circle">▶</div>
        )}
      </section>

      <section className="features">
        <div><ShieldCheck /> Certified Jewellery</div>
        <div><Truck /> Free &amp; Insured Shipping</div>
        <div><MapPin /> Store Assistance</div>
      </section>

      {/* ── Map Banner ── */}
      <section className="section-full map-main-section">
        <SectionTitle
          title="Customize Jewellery at Our Store"
          subtitle="Get in touch with us for a complete jewellery shopping experience!"
        />
        <div className="map-banner-wrap">
          <img
            src={`${IMAGE_PATH}emirates-map-banner.png`}
            alt="Emirates Gold & Diamonds worldwide locations"
            className="map-banner-img"
            loading="lazy"
            decoding="async"
          />
        </div>
      </section>

      {/* ── Stay Connected — Solitaire Rings, Cuban Chains, Beaded Necklaces ── */}
      <section className="section">
        <SectionTitle title="Stay Connected" subtitle="Follow our latest collections" />
        <div className="grid grid-3 portrait">
          {(showMoreSocial ? social : social.slice(0, INITIAL_VISIBLE)).map((item, idx) => (
            <Link key={item.title} to={`/category/${item.slug}`} className="category-card-link">
              <ImageCard
                title={item.title}
                image={`${IMAGE_PATH}social-${idx + 1}.jpg`}
                className="gift-card"
              />
            </Link>
          ))}
        </div>
        <ViewMoreBtn
          expanded={showMoreSocial}
          total={social.length}
          onClick={() => setShowMoreSocial(!showMoreSocial)}
        />
      </section>

      {/* ── Split Section ── */}
      <section className="split-section">
        <img
          src={`${IMAGE_PATH}luxury-jewellery.jpg`}
          alt="Luxury Jewellery"
          loading="lazy"
          decoding="async"
        />
        <div>
          <span>Crafted With Passion</span>
          <h2>Luxury Jewellery Made For You</h2>
          <p>
            Explore elegant gold and diamond jewellery crafted for weddings,
            festive occasions, gifting and daily wear.
          </p>
          <button onClick={() => navigate('/jewellery')}>Discover More</button>
        </div>
      </section>

      {/* ── Our Latest Blogs — Beaded Necklaces, Rings, Dangle Earrings ── */}
      <section className="section cream">
        <SectionTitle title="Our Latest Blogs" subtitle="Jewellery guides and inspiration" />
        <div className="grid grid-3">
          {(showMoreBlogs ? blogCats : blogCats.slice(0, INITIAL_VISIBLE)).map((item, idx) => (
            <Link key={item.title} to={`/category/${item.slug}`} className="category-card-link">
              <ImageCard
                title={item.title}
                image={`${IMAGE_PATH}blog-${idx + 1}.jpg`}
                className="gift-card"
              />
            </Link>
          ))}
        </div>
        <ViewMoreBtn
          expanded={showMoreBlogs}
          total={blogCats.length}
          onClick={() => setShowMoreBlogs(!showMoreBlogs)}
        />
      </section>
    </>
  );
}

export default Home;
