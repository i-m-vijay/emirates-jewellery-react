import { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { motion } from 'framer-motion';
import {
  Heart, ShoppingBag, ChevronRight, ChevronDown, ChevronUp,
  ShieldCheck, Truck, RefreshCw, Star,
} from 'lucide-react';
import { useProduct } from '../context/ProductContext';
import { useWishlist } from '../context/WishlistContext';
import { useCart } from '../context/CartContext';
import { useAuthGuard } from '../guards/AuthGuard';
import { fetchProductDetail } from '../api/productApi';

function parseImages(images) {
  if (!images) return [];
  return images.split(',').map((s) => s.trim()).filter(Boolean);
}

/** Parses meta_gemhub_video_urls (comma-separated) and returns embed info for the first URL. */
function parseVideoUrl(raw) {
  if (!raw) return null;
  const url = raw.split(',')[0].trim();
  if (!url) return null;

  // YouTube (watch, embed, shorts, youtu.be)
  const ytMatch = url.match(
    /(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/
  );
  if (ytMatch) {
    return {
      type: 'youtube',
      embedSrc: `https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1&rel=0&modestbranding=1`,
    };
  }

  // Vimeo
  const vimeoMatch = url.match(/vimeo\.com\/(\d+)/);
  if (vimeoMatch) {
    return {
      type: 'vimeo',
      embedSrc: `https://player.vimeo.com/video/${vimeoMatch[1]}?autoplay=1`,
    };
  }

  // Direct video file (.mp4 / .webm / .ogg / .mov)
  if (/\.(mp4|webm|ogg|mov)(\?.*)?$/i.test(url)) {
    return { type: 'direct', src: url };
  }

  // Fallback — embed anything else in an iframe
  return { type: 'iframe', embedSrc: url };
}

function VideoModal({ videoInfo, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [onClose]);

  return (
    <div className="pdp__video-overlay" onClick={onClose}>
      <div className="pdp__video-modal" onClick={(e) => e.stopPropagation()}>
        <button className="pdp__video-close" onClick={onClose} aria-label="Close video">✕</button>
        <div className="pdp__video-frame">
          {videoInfo.type === 'direct' ? (
            <video
              src={videoInfo.src}
              controls
              autoPlay
              className="pdp__video-el"
            />
          ) : (
            <iframe
              src={videoInfo.embedSrc}
              title="Product Video"
              allow="autoplay; encrypted-media; fullscreen; picture-in-picture"
              allowFullScreen
              className="pdp__video-iframe"
            />
          )}
        </div>
      </div>
    </div>
  );
}

function Accordion({ title, defaultOpen = true, children }) {
  const [open, setOpen] = useState(defaultOpen);
  return (
    <div className="pdp__accordion">
      <button className="pdp__accordion-btn" onClick={() => setOpen((o) => !o)}>
        <span>{title}</span>
        {open ? <ChevronUp size={18} /> : <ChevronDown size={18} />}
      </button>
      {open && <div className="pdp__accordion-body">{children}</div>}
    </div>
  );
}

function ProductDescription({ text }) {
  const [expanded, setExpanded] = useState(false);
  const [overflowing, setOverflowing] = useState(false);
  const ref = useRef(null);

  useEffect(() => {
    setExpanded(false);
    const el = ref.current;
    if (!el) return;
    // Measured against the 3-line clamped height, before any expansion
    setOverflowing(el.scrollHeight > el.clientHeight + 1);
  }, [text]);

  if (!text) return null;

  return (
    <div className="pdp__desc-wrap">
      <p ref={ref} className={`pdp__desc${expanded ? ' pdp__desc--expanded' : ''}`}>{text}</p>
      {overflowing && (
        <button
          type="button"
          className="pdp__desc-toggle"
          onClick={() => setExpanded((e) => !e)}
        >
          {expanded ? 'Read Less' : 'Read More'}
        </button>
      )}
    </div>
  );
}

function DetailRow({ label, value }) {
  if (!value && value !== 0) return null;
  return (
    <div className="pdp__detail-row">
      <span className="pdp__detail-label">{label}</span>
      <span className="pdp__detail-value">{value}</span>
    </div>
  );
}

function SimilarCard({ product, onClick }) {
  const [wishlisted, setWishlisted] = useState(false);
  const image = product.images?.split(',')[0]?.trim();
  const hasDiscount = product.sale_price && parseFloat(product.sale_price) < parseFloat(product.regular_price);

  return (
    <motion.div className="pdp__similar-card" whileHover={{ y: -3 }} onClick={onClick}>
      <div className="pdp__similar-img-wrap">
        {image
          ? <img src={image} alt={product.name} loading="lazy" decoding="async" />
          : <div className="pdp__no-img">No Image</div>}
        <button
          className={`pdp__similar-wish ${wishlisted ? 'active' : ''}`}
          onClick={(e) => { e.stopPropagation(); setWishlisted((w) => !w); }}
          aria-label="Wishlist"
        >
          <Heart size={15} fill={wishlisted ? '#e84b5d' : 'none'} stroke={wishlisted ? '#e84b5d' : '#888'} />
        </button>
      </div>
      <div className="pdp__similar-info">
        {hasDiscount ? (
          <p className="pdp__similar-price">
            <strong>${parseFloat(product.sale_price).toLocaleString()}</strong>
            <span className="pdp__similar-struck">${parseFloat(product.regular_price).toLocaleString()}</span>
          </p>
        ) : product.regular_price ? (
          <p className="pdp__similar-price"><strong>${parseFloat(product.regular_price).toLocaleString()}</strong></p>
        ) : (
          <p className="pdp__similar-price"><em>Price on request</em></p>
        )}
        <p className="pdp__similar-name">{product.name}</p>
      </div>
    </motion.div>
  );
}

function ZoomableImage({ src, alt }) {
  const sourceRef = useRef(null);
  const [lens, setLens] = useState(null);
  const LENS_W = 90;
  const LENS_H = 90;

  const onMove = (e) => {
    const rect = sourceRef.current.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const xPct    = Math.max(0, Math.min(100, (x / rect.width) * 100));
    const yPct    = Math.max(0, Math.min(100, (y / rect.height) * 100));
    const lensLeft = Math.max(0, Math.min(rect.width  - LENS_W, x - LENS_W / 2));
    const lensTop  = Math.max(0, Math.min(rect.height - LENS_H, y - LENS_H / 2));
    setLens({ xPct, yPct, lensLeft, lensTop });
  };

  return (
    <div className="pdp__zoom-root">
      <div
        ref={sourceRef}
        className="pdp__zoom-source"
        onMouseMove={onMove}
        onMouseLeave={() => setLens(null)}
      >
        {/* Main product image — eager because it is the page's LCP element */}
        <img src={src} alt={alt} className="pdp__main-img" loading="eager" decoding="sync" />
        {lens && (
          <div
            className="pdp__zoom-lens"
            style={{ left: lens.lensLeft, top: lens.lensTop, width: LENS_W, height: LENS_H }}
          />
        )}
      </div>

      {lens && (
        <div
          className="pdp__zoom-result"
          style={{
            backgroundImage: `url(${src})`,
            backgroundPosition: `${lens.xPct}% ${lens.yPct}%`,
            backgroundSize: '300% 300%',
          }}
        />
      )}
    </div>
  );
}

function ProductDetail() {
  const { id } = useParams();
  const {
    selectedProduct: product,
    setSelectedProduct,
    categoryProducts,
    categoryName,
    categorySlug,
  } = useProduct();
  const navigate = useNavigate();
  const { toggleItem, isInWishlist } = useWishlist();
  const { addItem: addToCart, isInCart } = useCart();
  const guard = useAuthGuard();

  const [activeImg, setActiveImg] = useState(0);
  const [similarStart, setSimilarStart] = useState(0);
  const [fetching, setFetching] = useState(false);
  const [fetchError, setFetchError] = useState(null);
  const [videoOpen, setVideoOpen] = useState(false);

  // Fetch from API when:
  //  - no product in context/sessionStorage at all, OR
  //  - the stored product's id doesn't match the current URL param (user navigated
  //    directly to a different product URL or refreshed on a mismatched id)
  const needsFetch = !product || String(product.record_id) !== String(id);

  useEffect(() => {
    if (!needsFetch || !id) return;
    let stale = false;
    setFetching(true);
    setFetchError(null);
    fetchProductDetail(id)
      .then((res) => {
        if (stale) return;
        const p = res?.data ?? res;
        if (p) {
          setSelectedProduct(p);
        } else {
          setFetchError('Product not found.');
        }
      })
      .catch(() => {
        if (!stale) setFetchError('Could not load product. Please try again.');
      })
      .finally(() => { if (!stale) setFetching(false); });
    return () => { stale = true; };
  }, [id]); // eslint-disable-line react-hooks/exhaustive-deps

  // Reset gallery and scroll position when the viewed product changes
  useEffect(() => {
    setActiveImg(0);
    setSimilarStart(0);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }, [product?.record_id]);

  if (fetching) {
    return (
      <div className="pdp__not-found">
        <div className="page-loader-wrap"><span className="page-loader" /></div>
      </div>
    );
  }

  if (fetchError || !product) {
    return (
      <div className="pdp__not-found">
        <p>{fetchError || 'Product not found.'}</p>
        <Link to="/" className="pdp__back-link">Go to Home</Link>
      </div>
    );
  }

  const images = parseImages(product.images);
  const videoInfo = parseVideoUrl(product.meta_gemhub_video_urls);
  const hasDiscount  = product.sale_price && parseFloat(product.sale_price) < parseFloat(product.regular_price);
  const discountPct  = hasDiscount
    ? Math.round((1 - parseFloat(product.sale_price) / parseFloat(product.regular_price)) * 100)
    : null;
  const regularPrice = product.regular_price ? parseFloat(product.regular_price) : null;
  const salePrice    = product.sale_price    ? parseFloat(product.sale_price)    : null;
  const discount     = regularPrice && salePrice ? regularPrice - salePrice : 0;
  const finalPrice   = salePrice || regularPrice;

  const similar = categoryProducts.filter((p) => p.record_id !== product.record_id);
  const SIMILAR_PER_PAGE = 4;
  const visibleSimilar = similar.slice(similarStart, similarStart + SIMILAR_PER_PAGE);

  const handleSimilarClick = (p) => {
    setSelectedProduct(p);
    navigate(`/product/${p.record_id}`);
  };

  return (
    <div className="pdp">
      {/* Breadcrumb */}
      <nav className="pdp__breadcrumb">
        <Link to="/">Home</Link>
        <ChevronRight size={13} />
        {categorySlug && <Link to={`/category/${categorySlug}`}>{categoryName}</Link>}
        {categorySlug && <ChevronRight size={13} />}
        <span>{product.name}</span>
      </nav>

      {/* ── Main 2-column ── */}
      <div className="pdp__main">

        {/* Gallery */}
        <div className="pdp__gallery">
          <div className="pdp__thumbs">
            {/* Video thumb — shown first when a video URL is available */}
            {videoInfo && (
              <button
                className={`pdp__thumb pdp__video-thumb${videoOpen ? ' active' : ''}`}
                onClick={() => setVideoOpen(true)}
                aria-label="Watch product video"
              >
                <span className="pdp__video-thumb__play">
                  <svg viewBox="0 0 24 24" width="28" height="28">
                    <circle cx="12" cy="12" r="11" fill="rgba(255,255,255,0.15)" stroke="rgba(255,255,255,0.7)" strokeWidth="1.2"/>
                    <polygon points="10,8 18,12 10,16" fill="rgba(255,255,255,0.9)"/>
                  </svg>
                </span>
                <span className="pdp__video-thumb__label">Video</span>
              </button>
            )}
            {images.map((img, i) => (
              <button
                key={i}
                className={`pdp__thumb ${i === activeImg ? 'active' : ''}`}
                onClick={() => setActiveImg(i)}
              >
                <img src={img} alt={`view ${i + 1}`} loading="lazy" decoding="async" />
              </button>
            ))}
          </div>
          {images.length > 0 ? (
            <ZoomableImage src={images[activeImg]} alt={product.name} />
          ) : (
            <div className="pdp__main-img-wrap">
              <div className="pdp__no-img-lg">No Image Available</div>
            </div>
          )}
        </div>

        {/* Info panel */}
        <div className="pdp__info">
          <h1 className="pdp__name">{product.name}</h1>
          <p className="pdp__sku">SKU: {product.sku}</p>
          <ProductDescription text={product.description} />
          <a href="#pdp-details" className="pdp__see-details">See Product Details ↓</a>

          <div className="pdp__price-row">
            {hasDiscount ? (
              <>
                <span className="pdp__sale-price">${salePrice.toLocaleString()}</span>
                <span className="pdp__reg-price">${regularPrice.toLocaleString()}</span>
              </>
            ) : regularPrice ? (
              <span className="pdp__sale-price">${regularPrice.toLocaleString()}</span>
            ) : (
              <span className="pdp__price-na">Price on request</span>
            )}
            {regularPrice && (
              <span className="pdp__tax-note">*Local taxes excluded (where applicable)</span>
            )}
          </div>

          {discountPct && (
            <p className="pdp__offer">
              <strong>{discountPct}% Off</strong> <span>on Making Value</span>
            </p>
          )}

          {!product.in_stock && <p className="pdp__oos">Currently Out of Stock</p>}

          <div className="pdp__actions">
            <button
              className={`pdp__add-cart${isInCart(product.record_id) ? ' in-cart' : ''}`}
              disabled={!product.in_stock}
              onClick={() => addToCart(product)}
            >
              <ShoppingBag size={18} />
              {isInCart(product.record_id) ? 'Added to Cart ✓' : 'Add to Cart'}
            </button>
            <button
              className={`pdp__wish-btn ${isInWishlist(product.record_id) ? 'active' : ''}`}
              onClick={() => guard(() => toggleItem(product))}
              aria-label={isInWishlist(product.record_id) ? 'Remove from Wishlist' : 'Add to Wishlist'}
              title={isInWishlist(product.record_id) ? 'Remove from Wishlist' : 'Add to Wishlist'}
            >
              <Heart
                size={20}
                fill={isInWishlist(product.record_id) ? '#e84b5d' : 'none'}
                stroke={isInWishlist(product.record_id) ? '#e84b5d' : '#555'}
              />
            </button>
          </div>

          <div className="pdp__trust">
            <div><ShieldCheck size={22} /><span>Certified Jewellery</span></div>
            <div><Truck size={22} /><span>Insured Shipping</span></div>
            <div><RefreshCw size={22} /><span>15 Days Exchange</span></div>
          </div>
        </div>
      </div>

      {/* ── Product Details ── */}
      <div id="pdp-details" className="pdp__section">
        <Accordion title="Product Details">
          <div className="pdp__details-grid">
            <div className="pdp__detail-panel">
              <h4>Basic Information</h4>
              <DetailRow label="Metal"            value={product.meta_gold_type} />
              <DetailRow label="Metal Purity"     value={product.meta_gold_purity} />
              <DetailRow label="Gross Weight (g)" value={product.meta_gold_weight_grams} />
              <DetailRow label="Category"         value={product.categories} />
              <DetailRow label="In Stock"         value={product.in_stock ? 'Yes' : 'No'} />
            </div>

            <div className="pdp__detail-panel">
              <h4>Diamond Details</h4>
              <DetailRow label="Diamond Clarity" value={product.meta_diamond_clarity} />
              <DetailRow label="Diamond Color"   value={product.meta_diamond_color} />
              <DetailRow
                label="Diamond Weight"
                value={product.meta_total_diamond_weight ? `${product.meta_total_diamond_weight} ct` : null}
              />
            </div>

            <div className="pdp__detail-panel">
              <h4>Stone Information</h4>
              <DetailRow label="Stone Details" value="N/A" />
            </div>

            <div className="pdp__detail-panel">
              <h4>Other Information</h4>
              <DetailRow label="SKU"      value={product.sku} />
              <DetailRow label="Category" value={product.categories} />
            </div>
          </div>

          <div className="pdp__return-policy">
            <h4 className="pdp__return-title">Return Policy</h4>
            <DetailRow label="Return" value="15 Days Exchange" />
          </div>
        </Accordion>
      </div>

      {/* ── Price Breakup ── */}
      {regularPrice && (
        <div className="pdp__section">
          <Accordion title="Price Breakup" defaultOpen={false}>
            <div className="pdp__price-table-wrap">
              <table className="pdp__price-table">
                <thead>
                  <tr>
                    <th>Component</th><th>Rate</th><th>Weight</th>
                    <th>Value</th><th>Discount</th><th>Final Value</th>
                  </tr>
                </thead>
                <tbody>
                  {product.meta_gold_weight_grams && (
                    <>
                      <tr className="pdp__price-cat-row">
                        <td>Metal</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                      </tr>
                      <tr>
                        <td>{product.meta_gold_type || 'Gold'}</td>
                        <td>-</td>
                        <td>{product.meta_gold_weight_grams} g</td>
                        <td>-</td><td>-</td><td>-</td>
                      </tr>
                    </>
                  )}
                  {product.meta_total_diamond_weight && (
                    <>
                      <tr className="pdp__price-cat-row">
                        <td>Diamond</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                      </tr>
                      <tr>
                        <td>{[product.meta_diamond_clarity, product.meta_diamond_color].filter(Boolean).join(' ')}</td>
                        <td>-</td>
                        <td>{product.meta_total_diamond_weight} ct</td>
                        <td>-</td><td>-</td><td>-</td>
                      </tr>
                    </>
                  )}
                  {discountPct && (
                    <tr className="pdp__price-cat-row">
                      <td>Making Charges ({discountPct}% Off)</td>
                      <td>-</td><td>-</td><td>-</td>
                      <td>${discount.toFixed(2)}</td><td>-</td>
                    </tr>
                  )}
                  <tr className="pdp__price-cat-row pdp__price-subtotal">
                    <td>Sub Total</td><td>-</td><td>-</td>
                    <td>${regularPrice.toFixed(2)}</td>
                    <td>{discount ? `$${discount.toFixed(2)}` : '-'}</td>
                    <td>${finalPrice.toFixed(2)}</td>
                  </tr>
                  <tr className="pdp__price-cat-row pdp__price-grand">
                    <td>Grand Total</td><td>-</td><td>-</td>
                    <td>${regularPrice.toFixed(2)}</td>
                    <td>{discount ? `$${discount.toFixed(2)}` : '-'}</td>
                    <td>${finalPrice.toFixed(2)}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </Accordion>
        </div>
      )}

      {/* ── Similar Products ── */}
      {similar.length > 0 && (
        <div className="pdp__section pdp__similar-section">
          <h2 className="pdp__similar-heading">Similar Products</h2>
          <div className="pdp__similar-carousel-wrap">
            <button
              className="pdp__carousel-btn"
              disabled={similarStart === 0}
              onClick={() => setSimilarStart((s) => Math.max(0, s - 1))}
            >
              ‹
            </button>
            <div className="pdp__similar-grid">
              {visibleSimilar.map((p) => (
                <SimilarCard key={p.record_id} product={p} onClick={() => handleSimilarClick(p)} />
              ))}
            </div>
            <button
              className="pdp__carousel-btn"
              disabled={similarStart + SIMILAR_PER_PAGE >= similar.length}
              onClick={() => setSimilarStart((s) => s + 1)}
            >
              ›
            </button>
          </div>
        </div>
      )}

      {/* ── Video Modal ── */}
      {videoOpen && videoInfo && (
        <VideoModal videoInfo={videoInfo} onClose={() => setVideoOpen(false)} />
      )}

      {/* ── Product Review ── */}
      <div className="pdp__section">
        <h2 className="pdp__review-heading">Product Review</h2>
        <div className="pdp__review-grid">
          <div className="pdp__review-rating">
            <div className="pdp__stars">
              {[...Array(5)].map((_, i) => <Star key={i} size={22} color="#e84b5d" fill="none" />)}
            </div>
            <p className="pdp__rating-score">0/5</p>
            <p className="pdp__rating-base">Based on Reviews 0</p>
            <p className="pdp__rating-hint">Review this product<br />Share your thoughts with other customers</p>
            <button className="pdp__review-btn">Write a review</button>
          </div>

          <div className="pdp__review-empty">
            <p>No Reviews.</p>
            <p>YOU CAN BE A FIRST REVIEWER.</p>
          </div>

          <div className="pdp__rating-bars">
            <h4>Customer rating</h4>
            {[5, 4, 3, 2, 1].map((n) => (
              <div key={n} className="pdp__rating-bar-row">
                <div className="pdp__rating-bar-track">
                  <div className="pdp__rating-bar-fill" style={{ width: '0%' }} />
                </div>
                <span>{n} {n === 1 ? 'star' : 'stars'}</span>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

export default ProductDetail;
