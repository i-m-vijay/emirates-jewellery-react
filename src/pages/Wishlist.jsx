import { Link, useNavigate } from 'react-router-dom';
import { Heart, ShoppingBag, Trash2, ShoppingCart } from 'lucide-react';
import { useWishlist } from '../context/WishlistContext';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';

function fmt(n) {
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function Wishlist() {
  const { items, removeItem } = useWishlist();
  const { addItem, isInCart } = useCart();
  const { isAuthenticated, openAuth } = useAuth();
  const navigate = useNavigate();

  const handleAddToCart = (product) => {
    if (!isAuthenticated) { openAuth({ product }); return; }
    addItem(product);
  };

  /* ── Empty state ── */
  if (items.length === 0) {
    return (
      <div className="wishlist-page">
        <div className="cart-page__empty">
          <Heart size={52} strokeWidth={1.2} />
          <h2>Your Wishlist is Empty</h2>
          <p>Save items you love and find them here whenever you're ready.</p>
          <Link to="/" className="cart-page__continue-btn">Continue Shopping</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="wishlist-page">
      <span className="cart-page__label">
        My Wishlist &mdash; {items.length} {items.length === 1 ? 'item' : 'items'}
      </span>

      <div className="wishlist-page__items">
        {items.map((product) => {
          const image        = product.images?.split(',')[0]?.trim();
          const salePrice    = product.sale_price    ? parseFloat(product.sale_price)    : null;
          const regularPrice = product.regular_price ? parseFloat(product.regular_price) : null;
          const displayPrice = salePrice ?? regularPrice;
          const inCart       = isInCart(product.record_id);

          return (
            <div key={product.record_id} className="wishlist-item">

              {/* Image */}
              <div className="cart-item__img-wrap">
                {image
                  ? <img src={image} alt={product.name} loading="lazy" decoding="async" />
                  : <div className="cart-item__no-img" />}
              </div>

              {/* Details */}
              <div className="cart-item__body">
                <div className="cart-item__top">
                  <div className="cart-item__name-wrap">
                    <p className="cart-item__name">{product.name}</p>
                    <div className="cart-item__price-row">
                      {displayPrice != null ? (
                        <>
                          <span className="cart-item__sale">${fmt(displayPrice)}</span>
                          {salePrice && regularPrice && salePrice < regularPrice && (
                            <span className="cart-item__reg">${fmt(regularPrice)}</span>
                          )}
                        </>
                      ) : (
                        <span className="wishlist-item__price-na">Price on request</span>
                      )}
                    </div>
                  </div>
                  <p className="cart-item__sku">SKU: {product.sku}</p>
                </div>

                <div className="cart-item__footer">
                  <div className="wishlist-item__actions">
                    {inCart ? (
                      <button
                        className="wishlist-item__btn wishlist-item__btn--incart"
                        onClick={() => navigate('/cart')}
                      >
                        <ShoppingCart size={14} /> View in Cart
                      </button>
                    ) : (
                      <button
                        className="wishlist-item__btn wishlist-item__btn--add"
                        onClick={() => handleAddToCart(product)}
                      >
                        <ShoppingBag size={14} /> Add to Cart
                      </button>
                    )}

                    <button
                      className="wishlist-item__btn wishlist-item__btn--remove"
                      onClick={() => removeItem(product.record_id)}
                      title="Remove from wishlist"
                    >
                      <Trash2 size={14} /> Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* ── Footer actions ── */}
      <div className="wishlist-page__footer">
        <Link to="/" className="wishlist-footer__link">Continue Shopping</Link>
        <div className="wishlist-footer__ctas">
          <button className="wishlist-footer__cart-btn" onClick={() => navigate('/cart')}>
            <ShoppingCart size={16} /> Go to Cart
          </button>
          <button className="wishlist-footer__checkout-btn" onClick={() => navigate('/checkout')}>
            <ShoppingBag size={16} /> Proceed to Checkout
          </button>
        </div>
      </div>
    </div>
  );
}

export default Wishlist;
