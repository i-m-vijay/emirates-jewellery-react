import { Link } from 'react-router-dom';
import { Trash2, ShoppingBag } from 'lucide-react';
import { useCart } from '../context/CartContext';

function fmt(n) {
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function CartDropdown() {
  const { items, count, subtotal, removeItem } = useCart();

  return (
    <div className="cart-dropdown">
      <div className="cart-dropdown__header">
        <span>{count} {count === 1 ? 'Item' : 'Items'}</span>
        <span>Subtotal: ${fmt(subtotal)}</span>
      </div>

      {items.length === 0 ? (
        <p className="cart-dropdown__empty">Your cart is empty</p>
      ) : (
        <div className="cart-dropdown__list">
          {items.map(({ product, qty }) => {
            const image = product.images?.split(',')[0]?.trim();
            const price = parseFloat(product.sale_price || product.regular_price || 0);
            return (
              <div key={product.record_id} className="cart-dropdown__item">
                <div className="cart-dropdown__thumb">
                  {image
                    ? <img src={image} alt={product.name} />
                    : <div className="cart-dropdown__no-img" />}
                </div>
                <div className="cart-dropdown__meta">
                  <p className="cart-dropdown__name">{product.name}</p>
                  <p className="cart-dropdown__qty">Qty: {qty}</p>
                  <p className="cart-dropdown__price">${fmt(price * qty)}</p>
                </div>
                <button
                  className="cart-dropdown__del"
                  onClick={() => removeItem(product.record_id)}
                  aria-label="Remove"
                >
                  <Trash2 size={14} />
                </button>
              </div>
            );
          })}
        </div>
      )}

      <Link to="/checkout" className="cart-dropdown__checkout">
        <ShoppingBag size={15} /> CHECKOUT
      </Link>
      <Link to="/cart" className="cart-dropdown__edit">
        Edit Shopping Bag
      </Link>
    </div>
  );
}

export default CartDropdown;
