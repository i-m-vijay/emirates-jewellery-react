import { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ChevronLeft, ShieldCheck, Eye, EyeOff } from 'lucide-react';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import { loginUser, registerUser } from '../api/authApi';
import { ApiError, isValidEmail, validateEmail } from '../utils/apiError';

const IMAGE_PATH = '/assets/images/';
const logo = `${IMAGE_PATH}emirates-logo.png`;

function fmt(n) {
  return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/* ── Checkout header ── */
function CheckoutHeader() {
  return (
    <header className="co-header">
      <img src={logo} alt="Emirates Gold & Diamonds" className="co-header__logo" />

      <div className="co-steps">
        <span className="co-step co-step--active">Cart</span>
        <span className="co-step-line" />
        <span className="co-step">Shipping</span>
        <span className="co-step-line" />
        <span className="co-step">Payment</span>
      </div>

      <div className="co-header__secure">
        <ShieldCheck size={18} />
        <span>100% SECURE</span>
      </div>
    </header>
  );
}

/* ── Shared field helpers ── */
function FieldError({ msg }) {
  if (!msg) return null;
  return <p className="auth-form__field-error">{msg}</p>;
}

function PasswordInput({ id, label, value, onChange, error }) {
  const [show, setShow] = useState(false);
  return (
    <div className="auth-form__group">
      <label className="auth-form__label" htmlFor={id}>{label}</label>
      <div className="auth-form__pw-wrap">
        <input
          id={id}
          type={show ? 'text' : 'password'}
          value={value}
          onChange={onChange}
          className={`auth-form__input${error ? ' error' : ''}`}
        />
        <button type="button" className="auth-form__pw-toggle" onClick={() => setShow(s => !s)}>
          {show ? <EyeOff size={17} /> : <Eye size={17} />}
        </button>
      </div>
      <FieldError msg={error} />
    </div>
  );
}

/* ── Login form ── */
function CheckoutLoginForm({ onSuccess, onOtp }) {
  const [fields, setFields] = useState({ contact: '', password: '' });
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState('');
  const [loading, setLoading] = useState(false);

  const set = k => e => { setFields(f => ({ ...f, [k]: e.target.value })); if (errors[k]) setErrors(p => ({ ...p, [k]: undefined })); };

  const handleContactBlur = () => {
    if (fields.contact.includes('@') && !isValidEmail(fields.contact))
      setErrors(p => ({ ...p, contact: 'Enter a valid email address' }));
  };

  const validate = () => {
    const e = {};
    if (!fields.contact.trim()) e.contact = 'Email or phone is required';
    else if (fields.contact.includes('@') && !isValidEmail(fields.contact)) e.contact = 'Enter a valid email address';
    if (!fields.password) e.password = 'Password is required';
    return e;
  };

  const handleSubmit = async ev => {
    ev.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }
    setErrors({}); setLoading(true); setServerMsg('');
    try {
      const body = fields.contact.includes('@')
        ? { email: fields.contact, password: fields.password }
        : { mobile_no: fields.contact, password: fields.password };
      const res = await loginUser(body);
      if (res.success && res.token) { onSuccess(res.token, res.data); }
      else setServerMsg(res.message || 'Login failed. Please try again.');
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        const mapped = {};
        if (err.fieldErrors.email || err.fieldErrors.mobile_no) mapped.contact = err.fieldErrors.email || err.fieldErrors.mobile_no;
        if (Object.keys(mapped).length) setErrors(p => ({ ...p, ...mapped }));
      }
      setServerMsg(err.message || 'Login failed. Please try again.');
    } finally { setLoading(false); }
  };

  return (
    <form onSubmit={handleSubmit} noValidate className="co-form">
      <h3 className="co-form__title">Sign In Already Registered User</h3>

      <div className="auth-form__group">
        <label className="auth-form__label" htmlFor="co-contact">Enter Phone/Email *</label>
        <input id="co-contact" type="text" value={fields.contact}
          onChange={set('contact')} onBlur={handleContactBlur}
          className={`auth-form__input${errors.contact ? ' error' : ''}`} />
        <FieldError msg={errors.contact} />
      </div>

      <PasswordInput id="co-pw" label="Password" value={fields.password} onChange={set('password')} error={errors.password} />

      <div className="auth-form__links" style={{ marginBottom: 16 }}>
        <button type="button" className="auth-form__link">Forgotten your password?</button>
        <button type="button" className="auth-form__link" onClick={onOtp}>Request OTP!</button>
      </div>

      {serverMsg && <p className="co-form__server-error">{serverMsg}</p>}

      <button type="submit" className="auth-form__submit" disabled={loading}>
        {loading ? 'Signing in…' : 'Login'}
      </button>
    </form>
  );
}

/* ── Register form ── */
function CheckoutRegisterForm({ onSuccess }) {
  const [fields, setFields] = useState({
    first_name: '', last_name: '', email: '', mobile_no: '',
    gender: '', date_of_birth: '', password: '', password_confirmation: '',
  });
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState({ text: '', type: '' });
  const [loading, setLoading] = useState(false);

  const set = k => e => { setFields(f => ({ ...f, [k]: e.target.value })); if (errors[k]) setErrors(p => ({ ...p, [k]: undefined })); };

  const handleEmailBlur = () => {
    const err = validateEmail(fields.email);
    setErrors(p => ({ ...p, email: err || undefined }));
  };

  const validate = () => {
    const e = {};
    if (!fields.first_name.trim())  e.first_name = 'Required';
    if (!fields.last_name.trim())   e.last_name  = 'Required';
    const emailErr = validateEmail(fields.email);
    if (emailErr) e.email = emailErr;
    if (!fields.mobile_no.trim())   e.mobile_no  = 'Required';
    else if (!/^\d{10}$/.test(fields.mobile_no)) e.mobile_no = '10 digits';
    if (!fields.gender)             e.gender     = 'Required';
    if (!fields.date_of_birth)      e.date_of_birth = 'Required';
    if (!fields.password)           e.password   = 'Required';
    else if (fields.password.length < 6) e.password = 'Min 6 chars';
    if (fields.password_confirmation !== fields.password) e.password_confirmation = 'Passwords do not match';
    return e;
  };

  const handleSubmit = async ev => {
    ev.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }
    setErrors({}); setLoading(true); setServerMsg({ text: '', type: '' });
    try {
      const res = await registerUser(fields);
      if (res.success) {
        setServerMsg({ text: res.message || 'Registration successful! Please sign in.', type: 'success' });
        setTimeout(() => onSuccess(), 2000);
      } else {
        setServerMsg({ text: res.message || 'Registration failed.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) setErrors(p => ({ ...p, ...err.fieldErrors }));
      setServerMsg({ text: err.message || 'Registration failed.', type: 'error' });
    } finally { setLoading(false); }
  };

  return (
    <form onSubmit={handleSubmit} noValidate className="co-form">
      <h3 className="co-form__title">New User? Register Here!</h3>

      {serverMsg.text && (
        <div className={serverMsg.type === 'success' ? 'auth-form__server-success' : 'auth-form__server-error'} style={{ marginBottom: 14 }}>
          {serverMsg.text}
        </div>
      )}

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">First Name</label>
          <input type="text" value={fields.first_name} onChange={set('first_name')} className={`auth-form__input${errors.first_name ? ' error' : ''}`} />
          <FieldError msg={errors.first_name} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Last Name</label>
          <input type="text" value={fields.last_name} onChange={set('last_name')} className={`auth-form__input${errors.last_name ? ' error' : ''}`} />
          <FieldError msg={errors.last_name} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">Email</label>
          <input type="email" value={fields.email} onChange={set('email')} onBlur={handleEmailBlur} className={`auth-form__input${errors.email ? ' error' : ''}`} />
          <FieldError msg={errors.email} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Mobile No</label>
          <input type="tel" value={fields.mobile_no} onChange={set('mobile_no')} className={`auth-form__input${errors.mobile_no ? ' error' : ''}`} />
          <FieldError msg={errors.mobile_no} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">Gender</label>
          <select value={fields.gender} onChange={set('gender')} className={`auth-form__select${errors.gender ? ' error' : ''}`}>
            <option value="">select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
          <FieldError msg={errors.gender} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Date of Birth</label>
          <input type="date" value={fields.date_of_birth} onChange={set('date_of_birth')} className={`auth-form__input${errors.date_of_birth ? ' error' : ''}`} />
          <FieldError msg={errors.date_of_birth} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <PasswordInput id="co-reg-pw" label="Enter Password" value={fields.password} onChange={set('password')} error={errors.password} />
        <PasswordInput id="co-reg-cpw" label="Confirm Password" value={fields.password_confirmation} onChange={set('password_confirmation')} error={errors.password_confirmation} />
      </div>

      <button type="submit" className="auth-form__submit" disabled={loading}>
        {loading ? 'Registering…' : 'Sign Up'}
      </button>
    </form>
  );
}

/* ── Order Summary sidebar ── */
function OrderSummary() {
  const { items, subtotal } = useCart();
  return (
    <aside className="co-summary">
      <h3 className="co-summary__title">Order Summary</h3>

      <div className="co-summary__items">
        {items.map(({ product, qty }) => {
          const image = product.images?.split(',')[0]?.trim();
          const price = parseFloat(product.sale_price || product.regular_price || 0);
          return (
            <div key={product.record_id} className="co-summary__item">
              <div className="co-summary__thumb">
                {image ? <img src={image} alt={product.name} /> : <div className="co-summary__no-img" />}
              </div>
              <div className="co-summary__meta">
                <p className="co-summary__name">{product.name}</p>
                <p className="co-summary__code">Product Code :{product.sku}</p>
                <p className="co-summary__price">${fmt(price)}</p>
                <p className="co-summary__qty">Qty : {qty}</p>
              </div>
            </div>
          );
        })}
      </div>

      <div className="co-summary__secure">
        <ShieldCheck size={15} />
        <span>Secure Shipping And Transit Insurance</span>
      </div>

      <div className="co-summary__price-rows">
        <div className="co-summary__price-row">
          <span>Actual Price</span>
          <span>${fmt(subtotal)}</span>
        </div>
        <div className="co-summary__price-row co-summary__price-row--total">
          <span>Total</span>
          <strong>${fmt(subtotal)}</strong>
        </div>
        <p className="co-summary__note">Shipping fees and taxes are based on the address selected</p>
      </div>
    </aside>
  );
}

/* ── Main Checkout page ── */
function Checkout() {
  const { isAuthenticated, user, login } = useAuth();
  const { items } = useCart();
  const navigate = useNavigate();
  const [authView, setAuthView] = useState('both'); // 'both' | 'login-only'

  useEffect(() => {
    if (items.length === 0) navigate('/cart');
  }, [items.length, navigate]);

  const handleLoginSuccess = (token, userData) => {
    login(token, userData);
  };

  const handleRegisterSuccess = () => {
    setAuthView('login-only');
  };

  return (
    <div className="co-page">
      <CheckoutHeader />

      <div className="co-body">
        {/* Left: Personal Information */}
        <div className="co-left">
          <Link to="/cart" className="co-back-link">
            <ChevronLeft size={16} /> Back
          </Link>
          <h2 className="co-left__heading">Personal Information</h2>

          {isAuthenticated ? (
            <div className="co-logged-in">
              <p className="co-logged-in__name">
                Welcome back, <strong>{user?.first_name} {user?.last_name}</strong>
              </p>
              <p className="co-logged-in__email">{user?.email}</p>
              <button className="co-logged-in__continue" onClick={() => navigate('/cart')}>
                Continue to Shipping →
              </button>
            </div>
          ) : authView === 'both' ? (
            <div className="co-forms-grid">
              <CheckoutLoginForm
                onSuccess={handleLoginSuccess}
                onOtp={() => {}}
              />
              <div className="co-forms-divider" />
              <CheckoutRegisterForm onSuccess={handleRegisterSuccess} />
            </div>
          ) : (
            <div className="co-forms-grid co-forms-grid--single">
              <CheckoutLoginForm onSuccess={handleLoginSuccess} onOtp={() => {}} />
            </div>
          )}
        </div>

        {/* Right: Order Summary */}
        <OrderSummary />
      </div>
    </div>
  );
}

export default Checkout;
