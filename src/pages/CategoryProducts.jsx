import { useState, useEffect } from 'react';
import { useParams, useSearchParams, Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Heart, ShoppingBag, ArrowUpDown, ChevronRight } from 'lucide-react';
import { fetchCategoryProducts, fetchCategoryProductsByMetal } from '../api/categoryApi';
import { STORAGE_URL } from '../api/apiUrls';
import { useProduct } from '../context/ProductContext';
import { useWishlist } from '../context/WishlistContext';
import { useCart } from '../context/CartContext';
import { useAuthGuard } from '../guards/AuthGuard';

const SORT_OPTIONS = [
  { label: 'Position',           value: 'default' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z',       value: 'name_asc' },
];

function resolveImageUrl(raw) {
  if (!raw) return null;
  const url = String(raw).trim();
  if (!url) return null;
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url;
  return `${STORAGE_URL}/${url}`;
}

function getFirstImage(product) {
  // images field: comma-separated string or array
  if (product.images) {
    const raw = Array.isArray(product.images)
      ? product.images[0]
      : product.images.split(',')[0];
    const url = resolveImageUrl(raw);
    if (url) return url;
  }
  // fallback field names some APIs use
  if (product.image)     return resolveImageUrl(product.image);
  if (product.image_url) return resolveImageUrl(product.image_url);
  if (product.thumbnail) return resolveImageUrl(product.thumbnail);
  return null;
}

function sortProducts(products, sort) {
  const list = [...products];
  if (sort === 'price_asc')  return list.sort((a, b) => (parseFloat(a.regular_price) || 0) - (parseFloat(b.regular_price) || 0));
  if (sort === 'price_desc') return list.sort((a, b) => (parseFloat(b.regular_price) || 0) - (parseFloat(a.regular_price) || 0));
  if (sort === 'name_asc')   return list.sort((a, b) => a.name.localeCompare(b.name));
  return list;
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function ProductCard({ product, onClick }) {
  const { toggleItem, isInWishlist } = useWishlist();
  const { addItem: addToCart, isInCart } = useCart();
  const guard = useAuthGuard();

  const wishlisted = isInWishlist(product.record_id);
  const inCart     = isInCart(product.record_id);
  const image      = getFirstImage(product);
  const hasDiscount = product.sale_price && parseFloat(product.sale_price) < parseFloat(product.regular_price);
  const discountPct = hasDiscount
    ? Math.round((1 - parseFloat(product.sale_price) / parseFloat(product.regular_price)) * 100)
    : null;

  return (
    <motion.div className="product-card" whileHover={{ y: -4 }} onClick={onClick} style={{ cursor: 'pointer' }}>
      <div className="product-card__img-wrap">
        {image ? (
          <img
            src={image}
            alt={product.name}
            className="product-card__img"
            loading="lazy"
            decoding="async"
            onError={(e) => { e.target.style.display = 'none'; }}
          />
        ) : (
          <div className="product-card__no-img">No Image</div>
        )}
        <button
          className={`product-card__wish ${wishlisted ? 'active' : ''}`}
          onClick={(e) => { e.stopPropagation(); guard(() => toggleItem(product)); }}
          aria-label={wishlisted ? 'Remove from Wishlist' : 'Add to Wishlist'}
        >
          <Heart size={17} fill={wishlisted ? '#e84b5d' : 'none'} stroke={wishlisted ? '#e84b5d' : '#555'} />
        </button>
        {!product.in_stock && <span className="product-card__badge product-card__badge--oos">Out of Stock</span>}
        {discountPct && <span className="product-card__badge product-card__badge--sale">{discountPct}% Off</span>}
      </div>

      <div className="product-card__info">
        <p className="product-card__name">{product.name}</p>
        <div className="product-card__pricing">
          {hasDiscount ? (
            <>
              <span className="product-card__sale-price">${parseFloat(product.sale_price).toLocaleString()}</span>
              <span className="product-card__regular-price struck">${parseFloat(product.regular_price).toLocaleString()}</span>
            </>
          ) : product.regular_price ? (
            <span className="product-card__sale-price">${parseFloat(product.regular_price).toLocaleString()}</span>
          ) : (
            <span className="product-card__price-na">Price on request</span>
          )}
        </div>
        {discountPct && <p className="product-card__offer-label">{discountPct}% Off on Making Value</p>}
        {product.description && <p className="product-card__desc">{product.description}</p>}
        <button
          className={`product-card__cart-btn${inCart ? ' in-cart' : ''}`}
          onClick={(e) => { e.stopPropagation(); addToCart(product); }}
          disabled={!product.in_stock}
        >
          <ShoppingBag size={14} />
          {inCart ? 'Added to Cart ✓' : 'Add to Cart'}
        </button>
      </div>
    </motion.div>
  );
}

function CategoryProducts() {
  const { slug } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const {
    setSelectedProduct,
    setCategoryProducts,
    setCategoryName: setContextCategoryName,
    setCategorySlug,
  } = useProduct();

  const metalType   = searchParams.get('metal_type') || '';
  const catParam    = searchParams.get('cat') || '';
  const parentSlug  = searchParams.get('parent') || '';
  const parentName  = searchParams.get('parent_name') || '';

  const [categoryName, setCategoryName] = useState('');
  const [products, setProducts]         = useState([]);
  const [total, setTotal]               = useState(null);
  const [loading, setLoading]           = useState(true);
  const [error, setError]               = useState(null);
  const [sort, setSort]                 = useState('default');
  const [sortOpen, setSortOpen]         = useState(false);
  const [visibleCount, setVisibleCount] = useState(20);

  useEffect(() => { setVisibleCount(20); }, [slug, metalType, catParam, sort]);

  useEffect(() => {
    let stale = false;
    setLoading(true);
    setError(null);

    const request = catParam
      ? fetchCategoryProductsByMetal(catParam, metalType || '')
      : fetchCategoryProducts(slug);

    request
      .then((response) => {
        if (stale) return;
        const responseTotal = response?.total ?? null;
        const data = response?.data ?? response;
        if (data && (data.products || Array.isArray(data))) {
          const rawList = Array.isArray(data) ? data : (data.products ?? []);
          const productList = rawList.filter((p) => getFirstImage(p));
          const name = data.filters?.category || data.category || catParam || slug;
          setCategoryName(name);
          setProducts(productList);
          setTotal(responseTotal ?? data.total ?? null);
          setCategoryProducts(productList);
          setContextCategoryName(name);
          setCategorySlug(slug);
        } else {
          setError('No products found for this category.');
        }
      })
      .catch(() => {
        if (!stale) setError('Failed to load products. Please try again.');
      })
      .finally(() => {
        if (!stale) setLoading(false);
      });

    return () => { stale = true; };
  }, [slug, metalType, catParam]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleProductClick = (product) => {
    setSelectedProduct(product);
    navigate(`/product/${product.record_id}`);
  };

  const sorted  = sortProducts(products, sort);
  const visible = sorted.slice(0, visibleCount);
  const hasMore = visibleCount < sorted.length;

  return (
    <div className="cat-page">
      <div className="cat-page__breadcrumb">
        <Link to="/">Home</Link>
        {parentSlug && (
          <>
            <ChevronRight size={13} />
            <Link to={`/category/${parentSlug}`}>{parentName || capitalize(parentSlug)}</Link>
          </>
        )}
        {metalType && (
          <>
            <ChevronRight size={13} />
            <span>{capitalize(metalType)}</span>
          </>
        )}
        <ChevronRight size={13} />
        <span>{categoryName || catParam || slug}</span>
      </div>

      <div className="cat-page__header">
        <h1 className="cat-page__title">
          {categoryName || catParam || slug}
          {!loading && (total ?? products.length) > 0 && (
            <span className="cat-page__count"> ({total ?? products.length} Designs)</span>
          )}
        </h1>

        <div className="cat-page__controls">
          <div className="cat-page__sort-wrap">
            <button className="cat-page__ctrl-btn" onClick={() => setSortOpen((o) => !o)}>
              <ArrowUpDown size={16} />
              SORT &nbsp;
              <span className="cat-page__sort-label">
                {SORT_OPTIONS.find((o) => o.value === sort)?.label}
              </span>
            </button>
            {sortOpen && (
              <ul className="cat-page__sort-dropdown">
                {SORT_OPTIONS.map((opt) => (
                  <li
                    key={opt.value}
                    className={opt.value === sort ? 'active' : ''}
                    onClick={() => { setSort(opt.value); setSortOpen(false); }}
                  >
                    {opt.label}
                  </li>
                ))}
              </ul>
            )}
          </div>
        </div>
      </div>

      {loading && (
        <div className="cat-page__grid">
          {[...Array(8)].map((_, i) => (
            <div key={i} className="product-card product-card--skeleton" />
          ))}
        </div>
      )}

      {!loading && error && <div className="cat-page__error">{error}</div>}

      {!loading && !error && sorted.length === 0 && (
        <div className="cat-page__error">No products found in this category.</div>
      )}

      {!loading && !error && sorted.length > 0 && (
        <>
          <div className="cat-page__grid">
            {visible.map((product) => (
              <ProductCard
                key={product.record_id}
                product={product}
                onClick={() => handleProductClick(product)}
              />
            ))}
          </div>
          {hasMore && (
            <div className="cat-page__view-more-wrap">
              <button
                className="cat-page__view-more-btn"
                onClick={() => setVisibleCount((c) => c + 20)}
              >
                View More
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}

export default CategoryProducts;
