import { useState, useEffect, useRef } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import { motion } from 'framer-motion';
import { Heart, ShoppingBag, ArrowUpDown, ChevronRight, Search } from 'lucide-react';
import { fetchSearchResults } from '../api/categoryApi';
import { useProduct } from '../context/ProductContext';
import { useWishlist } from '../context/WishlistContext';
import { useCart } from '../context/CartContext';
import { useAuthGuard } from '../guards/AuthGuard';
import { useAuth } from '../context/AuthContext';

const PER_PAGE = 20;

function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

const SORT_OPTIONS = [
  { label: 'Relevance',          value: 'default' },
  { label: 'Price: Low to High', value: 'price_asc' },
  { label: 'Price: High to Low', value: 'price_desc' },
  { label: 'Name: A to Z',       value: 'name_asc' },
];

function sortProducts(products, sort) {
  const list = [...products];
  if (sort === 'price_asc')  return list.sort((a, b) => (parseFloat(a.regular_price) || 0) - (parseFloat(b.regular_price) || 0));
  if (sort === 'price_desc') return list.sort((a, b) => (parseFloat(b.regular_price) || 0) - (parseFloat(a.regular_price) || 0));
  if (sort === 'name_asc')   return list.sort((a, b) => a.name.localeCompare(b.name));
  return list;
}

function extractPageData(res, pageNum) {
  let list = [];
  let lastPage = null;
  let total = null;

  if (Array.isArray(res)) {
    list = res;
  } else if (Array.isArray(res?.data)) {
    list     = res.data;
    lastPage = res.last_page ?? null;
    total    = res.total    ?? null;
  } else if (Array.isArray(res?.products)) {
    list  = res.products;
    total = res.total ?? null;
  }

  let hasMore = false;
  if (lastPage != null)   hasMore = pageNum < lastPage;
  else if (total != null) hasMore = pageNum * PER_PAGE < total;
  else                    hasMore = list.length === PER_PAGE;

  return { list, hasMore, total };
}

function getFirstImage(images) {
  if (!images) return null;
  return images.split(',')[0].trim();
}

function ProductCard({ product, onClick }) {
  const { toggleItem, isInWishlist } = useWishlist();
  const { addItem: addToCart, isInCart } = useCart();
  const guard = useAuthGuard();
  const { isAuthenticated, openAuth } = useAuth();

  const wishlisted = isInWishlist(product.record_id);
  const inCart     = isInCart(product.record_id);
  const image      = getFirstImage(product.images);
  const hasDiscount = product.sale_price && parseFloat(product.sale_price) < parseFloat(product.regular_price);
  const discountPct = hasDiscount
    ? Math.round((1 - parseFloat(product.sale_price) / parseFloat(product.regular_price)) * 100)
    : null;

  return (
    <motion.div className="product-card" whileHover={{ y: -4 }} onClick={onClick} style={{ cursor: 'pointer' }}>
      <div className="product-card__img-wrap">
        {image ? (
          <img src={image} alt={product.name} className="product-card__img"
            loading="lazy" decoding="async"
            onError={(e) => { e.target.style.display = 'none'; }} />
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
          onClick={(e) => {
            e.stopPropagation();
            if (!isAuthenticated) { openAuth({ product }); return; }
            addToCart(product);
          }}
          disabled={!product.in_stock}
        >
          <ShoppingBag size={14} />
          {inCart ? 'Added to Cart ✓' : 'Add to Cart'}
        </button>
      </div>
    </motion.div>
  );
}

export default function SearchResultsPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const query = searchParams.get('q') || '';
  const { setSelectedProduct, setCategoryProducts } = useProduct();

  const [products, setProducts]       = useState([]);
  const [total, setTotal]             = useState(null);
  const [page, setPage]               = useState(1);
  const [pagesLoaded, setPagesLoaded] = useState(0);
  const [hasMore, setHasMore]         = useState(false);
  const [loading, setLoading]         = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);
  const [error, setError]             = useState(null);
  const [sort, setSort]               = useState('default');
  const [sortOpen, setSortOpen]       = useState(false);
  const abortRef = useRef(null);

  function loadPage(q, pageNum, isInitial) {
    if (isInitial) { setLoading(true); setError(null); }
    else           { setLoadingMore(true); }

    fetchSearchResults(q, pageNum, PER_PAGE)
      .then((res) => {
        const { list: rawList, hasMore: more, total: resTotal } = extractPageData(res, pageNum);
        const list = rawList.filter((p) => getFirstImage(p.images));

        if (isInitial) {
          if (list.length === 0) { setError(`No results found for "${q}".`); return; }
          setProducts(list);
          setCategoryProducts(list);
          setPagesLoaded(1);
          if (resTotal != null) setTotal(resTotal);
        } else {
          setProducts((prev) => {
            const updated = [...prev, ...list];
            setCategoryProducts(updated);
            return updated;
          });
          setPagesLoaded((p) => p + 1);
        }

        setPage(pageNum);
        setHasMore(more);
      })
      .catch(() => { if (isInitial) setError('Failed to load results. Please try again.'); })
      .finally(() => {
        if (isInitial) setLoading(false);
        else           setLoadingMore(false);
      });
  }

  // Reset and fetch when query changes
  useEffect(() => {
    if (!query.trim()) return;
    setPage(1);
    setPagesLoaded(0);
    setProducts([]);
    setTotal(null);
    setHasMore(false);
    loadPage(query, 1, true);
  }, [query]); // eslint-disable-line react-hooks/exhaustive-deps

  function handleViewMore() { loadPage(query, page + 1, false); }

  function handleViewLess() {
    setProducts((prev) => {
      const trimmed = prev.slice(0, prev.length - PER_PAGE);
      setCategoryProducts(trimmed);
      return trimmed;
    });
    setPage((p) => p - 1);
    setPagesLoaded((p) => p - 1);
    setHasMore(true);
  }

  const handleProductClick = (product) => {
    setSelectedProduct(product);
    navigate(`/product/${product.record_id}`);
  };

  const sorted = sortProducts(products, sort);

  return (
    <div className="cat-page">
      <div className="cat-page__breadcrumb">
        <Link to="/">Home</Link>
        <ChevronRight size={13} />
        <span>Search</span>
        {query && <><ChevronRight size={13} /><span>"{query}"</span></>}
      </div>

      <div className="cat-page__header">
        <h1 className="cat-page__title">
          {query ? `Results for "${query}"` : 'Search'}
          {!loading && (total ?? products.length) > 0 && (
            <span className="cat-page__count"> ({total ?? products.length} Designs)</span>
          )}
        </h1>

        {products.length > 0 && (
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
        )}
      </div>

      {!query.trim() && (
        <div className="cat-page__error">
          <Search size={48} style={{ opacity: 0.3, display: 'block', margin: '0 auto 16px' }} />
          Enter a search term to find jewellery.
        </div>
      )}

      {loading && (
        <div className="cat-page__grid">
          {[...Array(PER_PAGE)].map((_, i) => (
            <div key={i} className="product-card product-card--skeleton" />
          ))}
        </div>
      )}

      {!loading && error && <div className="cat-page__error">{error}</div>}

      {!loading && !error && sorted.length > 0 && (
        <>
          <div className="cat-page__grid">
            {sorted.map((product) => (
              <ProductCard
                key={product.record_id}
                product={product}
                onClick={() => handleProductClick(product)}
              />
            ))}
          </div>

          <div className="cat-page__view-more-wrap">
            {pagesLoaded > 1 && (
              <button className="cat-page__view-less-btn" onClick={handleViewLess}>
                View Less
              </button>
            )}
            {hasMore && (
              <button
                className="cat-page__view-more-btn"
                onClick={handleViewMore}
                disabled={loadingMore}
              >
                {loadingMore ? 'Loading…' : `View More (Page ${page + 1})`}
              </button>
            )}
          </div>
        </>
      )}
    </div>
  );
}
