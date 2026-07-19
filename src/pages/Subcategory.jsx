import { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { motion } from 'framer-motion';
import { ChevronRight } from 'lucide-react';
import { fetchCategoryProducts } from '../api/categoryApi';
import { STORAGE_URL } from '../api/apiUrls';

function resolveImageUrl(raw) {
  if (!raw) return null;
  const url = String(raw).trim();
  if (!url) return null;
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url;
  return `${STORAGE_URL}/${url}`;
}

function getFirstImage(product) {
  if (!product) return null;
  if (product.images) {
    const raw = Array.isArray(product.images)
      ? product.images[0]
      : product.images.split(',')[0];
    const url = resolveImageUrl(raw);
    if (url) return url;
  }
  if (product.image)     return resolveImageUrl(product.image);
  if (product.image_url) return resolveImageUrl(product.image_url);
  if (product.thumbnail) return resolveImageUrl(product.thumbnail);
  return null;
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function Subcategory() {
  const { pathname }                      = useLocation();
  const slug                              = pathname.split('/').filter(Boolean).pop();
  const [subcategories, setSubcategories] = useState([]);
  const [loading, setLoading]             = useState(true);
  const [error, setError]                 = useState(null);

  useEffect(() => {
    let stale = false;
    setLoading(true);
    setError(null);
    setSubcategories([]);

    fetchCategoryProducts(slug)
      .then((response) => {
        if (stale) return;
        // API returns { success, data: [ { category, slug, products: [...] } ] }
        const list = Array.isArray(response?.data) ? response.data : [];

        if (list.length === 0) {
          setError('No subcategories found.');
          return;
        }

        setSubcategories(list.map((cat) => ({
          title: cat.category,
          slug:  cat.slug,
          image: getFirstImage(cat.products?.[0]) ?? null,
        })));
      })
      .catch(() => {
        if (!stale) setError('Failed to load subcategories. Please try again.');
      })
      .finally(() => { if (!stale) setLoading(false); });

    return () => { stale = true; };
  }, [slug]);

  const displayName = capitalize(slug);

  return (
    <div className="cat-page">
      <div className="cat-page__breadcrumb">
        <Link to="/">Home</Link>
        <ChevronRight size={13} />
        <span>{displayName}</span>
      </div>

      <div className="cat-page__header">
        <h1 className="cat-page__title">
          {displayName}
          {!loading && subcategories.length > 0 && (
            <span className="cat-page__count"> ({subcategories.length} Categories)</span>
          )}
        </h1>
      </div>

      {loading && (
        <div className="grid grid-3 shop-by-cat">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="product-card product-card--skeleton" />
          ))}
        </div>
      )}

      {!loading && error && (
        <div className="cat-page__error">{error}</div>
      )}

      {!loading && !error && (
        <div className="grid grid-3 shop-by-cat">
          {subcategories.map((item) => (
            <Link
              key={item.slug}
              to={`/category/${item.slug}?parent=${slug}&parent_name=${displayName}`}
              className="category-card-link"
            >
              <motion.div className="image-card" whileHover={{ y: -6 }}>
                {item.image ? (
                  <img
                    src={item.image}
                    alt={item.title}
                    loading="lazy"
                    decoding="async"
                    onError={(e) => { e.target.style.display = 'none'; }}
                  />
                ) : (
                  <div className="product-card__no-img">No Image</div>
                )}
                <h3>{item.title}</h3>
              </motion.div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}

export default Subcategory;
