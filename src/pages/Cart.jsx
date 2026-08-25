import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Minus, Plus, ShoppingBag, ShieldCheck, Heart } from 'lucide-react';
import { useCart } from '../context/CartContext';
import { useWishlist } from '../context/WishlistContext';
import { useToast } from '../context/ToastContext';

function fmt(n) {
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function Cart() {
  const { items, subtotal, removeItem, updateQty } = useCart();
  const navigate = useNavigate();
  const { addItem: addToWishlist, isInWishlist } = useWishlist();
  const { showToast } = useToast();
  const [coupon, setCoupon] = useState('');
  const [couponMsg, setCouponMsg] = useState('');
  const TAX_RATE = 0.13;

  const handleMoveToWishlist = (product) => {
    addToWishlist(product);
    removeItem(product.record_id);
  };

  const handleApplyCoupon = () => {
    if (!coupon.trim()) { setCouponMsg('Please enter a coupon code.'); return; }
    setCouponMsg('Invalid or expired coupon code.');
  };

  /* ── Empty state ── */
  if (items.length === 0) {
    return (
      <div className="cart-page">
        <div className="cart-page__empty">
          <ShoppingBag size={52} strokeWidth={1.2} />
          <h2>Your Shopping Bag is Empty</h2>
          <p>Add items to your bag to see them here.</p>
          <Link to="/" className="cart-page__continue-btn">Continue Shopping</Link>
        </div>
      </div>
    );
  }

  return (
    <div className="cart-page">
      <span className="cart-page__label">Shopping Cart</span>

      <div className="cart-page__layout">

        {/* ── Items list ── */}
        <div className="cart-page__items">
          {items.map(({ product, qty }) => {
            const image = product.images?.split(',')[0]?.trim();
            const salePrice    = product.sale_price    ? parseFloat(product.sale_price)    : null;
            const regularPrice = product.regular_price ? parseFloat(product.regular_price) : null;
            const displayPrice = salePrice || regularPrice || 0;
            const inWishlist   = isInWishlist(product.record_id);

            return (
              <div key={product.record_id} className="cart-item">
                {/* Image */}
                <div className="cart-item__img-wrap">
                  {image
                    ? <img src={image} alt={product.name} />
                    : <div className="cart-item__no-img" />}
                </div>

                {/* Details */}
                <div className="cart-item__body">
                  <div className="cart-item__top">
                    <div className="cart-item__name-wrap">
                      <p className="cart-item__name">{product.name}</p>
                      <div className="cart-item__price-row">
                        <span className="cart-item__sale">${fmt(displayPrice)}</span>
                        {salePrice && regularPrice && (
                          <span className="cart-item__reg">${fmt(regularPrice)}</span>
                        )}
                      </div>
                    </div>
                    <p className="cart-item__sku">SKU: {product.sku}</p>
                  </div>

                  {/* Qty + actions */}
                  <div className="cart-item__footer">
                    <div className="cart-item__qty-wrap">
                      <button
                        className="cart-item__qty-btn"
                        onClick={() => updateQty(product.record_id, qty - 1)}
                        disabled={qty <= 1}
                      >
                        <Minus size={12} />
                      </button>
                      <span className="cart-item__qty-val">{qty}</span>
                      <button
                        className="cart-item__qty-btn"
                        onClick={() => {
                          const stock = Math.floor(parseFloat(product.stock));
                          if (!isNaN(stock) && qty >= stock) {
                            showToast(`Maximum ${stock} item${stock === 1 ? '' : 's'} available in stock`, 'error');
                            return;
                          }
                          updateQty(product.record_id, qty + 1);
                        }}
                      >
                        <Plus size={12} />
                      </button>
                    </div>

                    <div className="cart-item__actions">
                      <button
                        className="cart-item__action-btn"
                        onClick={() => handleMoveToWishlist(product)}
                        title={inWishlist ? 'Already in Wishlist' : 'Move to Wishlist'}
                      >
                        <Heart size={13} fill={inWishlist ? '#8b1f1f' : 'none'} />
                        Move to Wishlist
                      </button>
                      <button
                        className="cart-item__action-btn cart-item__action-btn--remove"
                        onClick={() => removeItem(product.record_id)}
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {/* ── Order Summary ── */}
        <aside className="cart-page__summary">
          <h3 className="cart-summary__title">Order Summary</h3>

          {/* Coupon */}
          <div className="cart-summary__coupon-row">
            <input
              type="text"
              placeholder="Enter Code"
              value={coupon}
              onChange={(e) => { setCoupon(e.target.value); setCouponMsg(''); }}
              className="cart-summary__coupon-input"
            />
            <button className="cart-summary__coupon-btn" onClick={handleApplyCoupon}>
              Apply
            </button>
          </div>
          {couponMsg && <p className="cart-summary__coupon-msg">{couponMsg}</p>}

          <div className="cart-summary__secure">
            <ShieldCheck size={15} />
            <span>Secure Shipping And Transit Insurance</span>
          </div>

          <div className="cart-summary__rows">
            <div className="cart-summary__row">
              <span>Actual Price</span>
              <span>${fmt(subtotal)}</span>
            </div>
            <div className="cart-summary__row">
              <span>Tax ({TAX_RATE * 100}%)</span>
              <span>${fmt(subtotal * TAX_RATE)}</span>
            </div>
            <div className="cart-summary__row cart-summary__row--total">
              <span>You Pay a Total Of</span>
              <strong>${fmt(subtotal + (subtotal * TAX_RATE))}</strong>
            </div>
          </div>

          <p className="cart-summary__note">
            Shipping fees and taxes are based on the address selected
          </p>

          <button className="cart-summary__checkout-btn" onClick={() => navigate('/checkout')}>
            <ShoppingBag size={16} /> Proceed to Checkout
          </button>
        </aside>
      </div>
    </div>
  );
}

export default Cart;
