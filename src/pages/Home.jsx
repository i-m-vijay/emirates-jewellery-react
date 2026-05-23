import { useState } from 'react';
import { motion } from 'framer-motion';
import {
  Search,
  User,
  Heart,
  ShoppingBag,
  Menu,
  X,
  ChevronRight,
  MapPin,
  Truck,
  ShieldCheck,
  Gift,
  BadgePercent,
  HelpCircle,
  Package,
  Gem,
  ChevronDown,
} from 'lucide-react';

const IMAGE_PATH = '/assets/images/';

const logo = `${IMAGE_PATH}emirates-logo.png`;

const heroSlides = [
  `${IMAGE_PATH}banner_03.jpg`,
  `${IMAGE_PATH}banner_01.jpg`,
  `${IMAGE_PATH}banner_02.jpg`,
];

const shopCategory = [
  { title: 'Rings', image: `${IMAGE_PATH}rings.jpg` },
  { title: 'Earrings', image: `${IMAGE_PATH}earrings.jpg` },
  { title: 'Bangles', image: `${IMAGE_PATH}bangles.jpg` },
  { title: 'Bands', image: `${IMAGE_PATH}bands.jpg` },
  { title: 'Lockets', image: `${IMAGE_PATH}lockets.jpg` },
  { title: 'Necklace', image: `${IMAGE_PATH}beaded-necklace.jpg` },
];

const collections = [
  { title: 'The Bridal Edit', image: `${IMAGE_PATH}best-seller-1.jpg` },
  { title: 'Festive Wear', image: `${IMAGE_PATH}best-seller-2.jpg` },
  { title: 'Diamond Picks', image: `${IMAGE_PATH}best-seller-3.jpg` },
];

const gift = [
  { title: 'Birthday Gifts', image: `${IMAGE_PATH}gift-1.jpg` },
  { title: 'Anniversary Gifts', image: `${IMAGE_PATH}gift-2.jpg` },
  { title: 'Wedding Gifts', image: `${IMAGE_PATH}gift-3.jpg` },
];

const bridal = [
  { title: 'Mehendi Look', image: `${IMAGE_PATH}bridal-1.jpg` },
  { title: 'Wedding Look', image: `${IMAGE_PATH}bridal-2.jpg` },
  { title: 'Reception Look', image: `${IMAGE_PATH}bridal-3.jpg` },
  { title: 'Royal Bridal', image: `${IMAGE_PATH}bridal-4.jpg` },
  { title: 'Temple Bridal', image: `${IMAGE_PATH}bridal-5.jpg` },
];

const shopByGender = [
  { title: 'For Her', image: `${IMAGE_PATH}for-her.jpg` },
  { title: 'Couple Rings', image: `${IMAGE_PATH}couple-rings.jpg` },
  { title: 'Kids Jewellery', image: `${IMAGE_PATH}kids-jewellery.jpg` },
];

const social = [
  { title: 'Instagram Styles', image: `${IMAGE_PATH}social-1.jpg` },
  { title: 'Diamond Trends', image: `${IMAGE_PATH}social-2.jpg` },
  { title: 'New Arrivals', image: `${IMAGE_PATH}social-3.jpg` },
  { title: 'Emirates Picks', image: `${IMAGE_PATH}social-4.jpg` },
];

const blogs = [
  { title: 'Latest Jewellery Trends', image: `${IMAGE_PATH}blog-1.jpg` },
  { title: 'How To Pick Bridal Jewellery', image: `${IMAGE_PATH}blog-2.jpg` },
  { title: 'Visit Our Store', image: `${IMAGE_PATH}blog-3.jpg` },
];

function SectionTitle({ title, subtitle }) {
  return (
    <div className="section-title">
      <span>{subtitle}</span>
      <h2>{title}</h2>
    </div>
  );
}

function ImageCard({ title, image, className = '' }) {
  return (
    <motion.div className={`image-card ${className}`.trim()} whileHover={{ y: -6 }}>
      <img src={image} alt={title} />
      <h3>{title}</h3>
    </motion.div>
  );
}

function Home() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [accountOpen, setAccountOpen] = useState(false);

  return (
    <>


      <section className="hero-slider">
        <div className="hero-track">
          {heroSlides.map((slide, index) => (
            <div className="hero-slide" key={slide}>
              <img src={slide} alt={`Banner ${index + 1}`} />
            </div>
          ))}
        </div>
      </section>

      <section className="section shop-by-cat">
        <img src={`${IMAGE_PATH}heading-vector.svg`} alt="" className="heading-vector" />
        <SectionTitle title="Shop By Category" subtitle="Explore our finest jewellery range" />

        <div className="grid grid-3">
          {shopCategory.map((item) => (
            <ImageCard key={item.title} title={item.title} image={item.image} />
          ))}
        </div>

        <button className="outline-btn">View All Categories</button>
      </section>

      <section className="section cream">
        <SectionTitle title="The Best Sellers" subtitle="Our most loved pieces" />

        <div className="grid grid-3 portrait">
          {collections.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="seller-card"
            />
          ))}
        </div>

        <button className="outline-btn">View More</button>
      </section>

      <section className="section">
        <SectionTitle title="Gift By Occasion" subtitle="Celebrate every special moment" />

        <div className="grid grid-3 portrait">
          {gift.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="gift-card"
            />
          ))}
        </div>
      </section>

      <section className="wide-offer">
        <div>
          <span>Gold & Diamond Savings</span>
          <h2>Exclusive Jewellery Offers</h2>
          <p>Discover premium pieces with special seasonal benefits.</p>
          <button>Explore Offers</button>
        </div>
      </section>

      <section className="section">
        <SectionTitle title="Bridal Collection" subtitle="Inspired by royal celebrations" />

        <div className="bridal-grid">
          {bridal.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="bridal-card-item"
            />
          ))}
        </div>
      </section>

      <section className="section cream ">
        <SectionTitle title="Shop By Gender" subtitle="Jewellery for everyone" />

        <div className="grid grid-3 portrait">
          {shopByGender.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="gender-card"
            />
          ))}
        </div>
      </section>

      <section className="video-section">
        <div className="play-circle">▶</div>
      </section>

      <section className="features">
        <div><ShieldCheck /> Certified Jewellery</div>
        <div><Truck /> Free & Insured Shipping</div>
        <div><MapPin /> Store Assistance</div>
      </section>

      <section className="section-full">
        <SectionTitle
          title="Customize Jewellery at Our Store"
          subtitle="Get in touch with us for a complete jewellery shopping experience!"
        />

        <div className="map-banner-wrap">
          <img
            src={`${IMAGE_PATH}emirates-map-banner.png`}
            alt="Emirates Gold & Diamonds worldwide locations"
            className="map-banner-img"
          />
        </div>
      </section>

      <section className="section">
        <SectionTitle title="Stay Connected" subtitle="Follow our latest collections" />

        <div className="social-grid">
          {social.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="social-card-item"
            />
          ))}
        </div>
      </section>

      <section className="split-section">
        <img src={`${IMAGE_PATH}luxury-jewellery.jpg`} alt="Luxury Jewellery" />

        <div>
          <span>Crafted With Passion</span>
          <h2>Luxury Jewellery Made For You</h2>
          <p>
            Explore elegant gold and diamond jewellery crafted for weddings,
            festive occasions, gifting and daily wear.
          </p>
          <button>Discover More</button>
        </div>
      </section>

      <section className="section cream">
        <SectionTitle title="Our Latest Blogs" subtitle="Jewellery guides and inspiration" />

        <div className="grid grid-3">
          {blogs.map((item) => (
            <ImageCard
              key={item.title}
              title={item.title}
              image={item.image}
              className="blog-card"
            />
          ))}
        </div>
      </section>

     
    </>
  );
}

export default Home;
