import { useState, useEffect, useRef } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { loginUser, registerUser, requestOtp, verifyOtp } from '../api/authApi';
import { useAuth } from '../context/AuthContext';
import { ApiError, isValidEmail, validateEmail } from '../utils/apiError';

/* ── Shared tiny helpers ── */
function FieldError({ msg }) {
  if (!msg) return null;
  return <p className="auth-form__field-error">{msg}</p>;
}

function ServerMsg({ text, type }) {
  if (!text) return null;
  return (
    <div className={type === 'success' ? 'auth-form__server-success' : 'auth-form__server-error'}>
      {text}
    </div>
  );
}

function PasswordField({ label, id, value, onChange, error }) {
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
        <button type="button" className="auth-form__pw-toggle" onClick={() => setShow((s) => !s)}>
          {show ? <EyeOff size={18} /> : <Eye size={18} />}
        </button>
      </div>
      <FieldError msg={error} />
    </div>
  );
}

/** Merge API fieldErrors into local error state.
 *  Handles the Login case: API returns "email"/"mobile_no" → map to "contact". */
function applyFieldErrors(setErrors, fieldErrors, fieldMap = {}) {
  if (!fieldErrors || !Object.keys(fieldErrors).length) return;
  const mapped = {};
  for (const [apiKey, msg] of Object.entries(fieldErrors)) {
    const localKey = fieldMap[apiKey] ?? apiKey;
    mapped[localKey] = msg;
  }
  setErrors((prev) => ({ ...prev, ...mapped }));
}

/* ══════════════════════════════════════
   OTP VERIFY FORM (post-login / post-registration)
══════════════════════════════════════ */
const OTP_TIMER_SECONDS = 5 * 60;

function OtpVerifyForm({ email, purpose, onBack, onSuccess, onVerified }) {
  const [otp, setOtp] = useState('');
  const [timeLeft, setTimeLeft] = useState(OTP_TIMER_SECONDS);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState({ text: '', type: '' });
  const timerRef = useRef(null);

  const startTimer = () => {
    clearInterval(timerRef.current);
    setTimeLeft(OTP_TIMER_SECONDS);
    timerRef.current = setInterval(() => setTimeLeft((t) => (t > 0 ? t - 1 : 0)), 1000);
  };

  useEffect(() => {
    const sendInitial = async () => {
      try {
        const res = await requestOtp({ email, purpose });
        if (res.success) {
          startTimer();
          setServerMsg({ text: res.message || 'OTP sent to your email!', type: 'success' });
        } else {
          setServerMsg({ text: res.message || 'Failed to send OTP.', type: 'error' });
        }
      } catch (err) {
        setServerMsg({ text: err.message || 'Failed to send OTP.', type: 'error' });
      }
    };
    sendInitial();
    return () => clearInterval(timerRef.current);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const minutes = String(Math.floor(timeLeft / 60)).padStart(2, '0');
  const seconds = String(timeLeft % 60).padStart(2, '0');

  const handleResend = async () => {
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const res = await requestOtp({ email, purpose });
      if (res.success) {
        startTimer();
        setOtp('');
        setServerMsg({ text: res.message || 'OTP resent to your email!', type: 'success' });
      } else {
        setServerMsg({ text: res.message || 'Failed to resend OTP.', type: 'error' });
      }
    } catch (err) {
      setServerMsg({ text: err.message || 'Failed to resend OTP.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleConfirm = async (e) => {
    e.preventDefault();
    if (!otp.trim()) { setErrors({ otp: 'OTP is required' }); return; }
    if (timeLeft === 0) { setErrors({ otp: 'OTP has expired. Please resend.' }); return; }
    setErrors({});
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const res = await verifyOtp({ email, otp, purpose });
      if (res.success) {
        clearInterval(timerRef.current);
        if (res.token) {
          onSuccess(res.token, res.data);
        } else {
          onVerified();
        }
      } else {
        setServerMsg({ text: res.message || 'Invalid OTP. Please try again.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        applyFieldErrors(setErrors, err.fieldErrors);
      }
      setServerMsg({ text: err.message || 'OTP verification failed.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleConfirm} noValidate>
      <h2 className="auth-dropdown__title">OTP Verification</h2>
      <ServerMsg {...serverMsg} />

      <div className="auth-form__group">
        <label className="auth-form__label" htmlFor="otp-input">Enter Otp *</label>
        <input
          id="otp-input"
          type="text"
          inputMode="numeric"
          maxLength={6}
          value={otp}
          onChange={(e) => { setOtp(e.target.value); if (errors.otp) setErrors({}); }}
          className={`auth-form__input${errors.otp ? ' error' : ''}`}
          autoFocus
        />
        <FieldError msg={errors.otp} />
      </div>

      <div className="otp-timer">
        <div className="otp-timer__block">
          <div className="otp-timer__value">{minutes}</div>
          <div className="otp-timer__label">minute</div>
        </div>
        <div className="otp-timer__sep">:</div>
        <div className="otp-timer__block">
          <div className="otp-timer__value">{seconds}</div>
          <div className="otp-timer__label">second</div>
        </div>
      </div>

      <div className="otp-timer__actions">
        <button type="submit" className="auth-form__submit auth-form__submit--outline" disabled={loading || timeLeft === 0}>
          {loading ? '…' : 'Confirm'}
        </button>
        <button type="button" className="auth-form__submit auth-form__submit--outline" onClick={handleResend} disabled={loading}>
          Resend
        </button>
      </div>

      <p className="auth-form__footer">
        <button type="button" className="auth-form__link" onClick={onBack}>Back to login</button>
      </p>
    </form>
  );
}

/* ══════════════════════════════════════
   LOGIN FORM
══════════════════════════════════════ */
function LoginForm({ onRegister, onOtp, onSuccess, onNeedOtp, verifiedMsg, onClearVerifiedMsg }) {
  const [fields, setFields] = useState({ contact: '', password: '' });
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState({ text: '', type: '' });
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (verifiedMsg) {
      setServerMsg({ text: verifiedMsg, type: 'success' });
      onClearVerifiedMsg();
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [verifiedMsg]);

  const set = (k) => (e) => setFields((f) => ({ ...f, [k]: e.target.value }));

  /* Clear field error as user types */
  const clearErr = (k) => (e) => {
    setFields((f) => ({ ...f, [k]: e.target.value }));
    if (errors[k]) setErrors((prev) => ({ ...prev, [k]: undefined }));
  };

  const validate = () => {
    const e = {};
    if (!fields.contact.trim()) {
      e.contact = 'Email or phone is required';
    } else if (fields.contact.includes('@') && !isValidEmail(fields.contact)) {
      e.contact = 'Enter a valid email address (e.g. name@domain.com)';
    }
    if (!fields.password) e.password = 'Password is required';
    return e;
  };

  /* Validate email format on blur */
  const handleContactBlur = () => {
    if (fields.contact.includes('@') && !isValidEmail(fields.contact)) {
      setErrors((prev) => ({ ...prev, contact: 'Enter a valid email address (e.g. name@domain.com)' }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }
    setErrors({});
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const body = fields.contact.includes('@')
        ? { email: fields.contact, password: fields.password }
        : { mobile_no: fields.contact, password: fields.password };
      const res = await loginUser(body);
      if (res.success) {
        const email = fields.contact.includes('@')
          ? fields.contact
          : (res.data?.email || res.data?.user?.email || null);
        if (email) {
          onNeedOtp(email, 'login');
        } else if (res.token) {
          onSuccess(res.token, res.data);
        } else {
          setServerMsg({ text: res.message || 'Login failed. Please try again.', type: 'error' });
        }
      } else {
        setServerMsg({ text: res.message || 'Login failed. Please try again.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        /* map "email"/"mobile_no" → "contact" for the login form */
        applyFieldErrors(setErrors, err.fieldErrors, { email: 'contact', mobile_no: 'contact' });
      }
      setServerMsg({ text: err.message || 'Login failed. Please try again.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} noValidate>
      <h2 className="auth-dropdown__title">Sign In Already Registered User</h2>

      <div className="auth-form__group">
        <label className="auth-form__label" htmlFor="lc-contact">Enter Phone/Email *</label>
        <input
          id="lc-contact"
          type="text"
          value={fields.contact}
          onChange={clearErr('contact')}
          onBlur={handleContactBlur}
          className={`auth-form__input${errors.contact ? ' error' : ''}`}
        />
        <FieldError msg={errors.contact} />
      </div>

      <PasswordField
        label="Password"
        id="lc-password"
        value={fields.password}
        onChange={set('password')}
        error={errors.password}
      />

      <div className="auth-form__links">
        <button type="button" className="auth-form__link">Forgotten your password?</button>
        <button type="button" className="auth-form__link" onClick={onOtp}>Request OTP!</button>
      </div>

      <button type="submit" className="auth-form__submit" disabled={loading}>
        {loading ? 'Signing in…' : 'Login'}
      </button>

      <ServerMsg {...serverMsg} />

      <p className="auth-form__footer">
        Not a Member?&nbsp;
        <button type="button" onClick={onRegister}>Sign Up</button>
      </p>
    </form>
  );
}

/* ══════════════════════════════════════
   OTP FORM
══════════════════════════════════════ */
function OtpForm({ onBack, onSuccess }) {
  const [contact, setContact] = useState('');
  const [otp, setOtp] = useState('');
  const [otpSent, setOtpSent] = useState(false);
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState({ text: '', type: '' });

  const validateContact = () => {
    if (!contact.trim()) return 'Email or phone is required';
    if (contact.includes('@') && !isValidEmail(contact))
      return 'Enter a valid email address (e.g. name@domain.com)';
    return null;
  };

  const handleSend = async (e) => {
    e.preventDefault();
    const contactErr = validateContact();
    if (contactErr) { setErrors({ contact: contactErr }); return; }
    setErrors({});
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const body = contact.includes('@') ? { email: contact } : { mobile_no: contact };
      const res = await requestOtp(body);
      if (res.success) {
        setOtpSent(true);
        setServerMsg({ text: res.message || 'OTP sent to your contact!', type: 'success' });
      } else {
        setServerMsg({ text: res.message || 'Failed to send OTP.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        applyFieldErrors(setErrors, err.fieldErrors, { email: 'contact', mobile_no: 'contact' });
      }
      setServerMsg({ text: err.message || 'Failed to send OTP.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const handleVerify = async (e) => {
    e.preventDefault();
    if (!otp.trim()) { setErrors({ otp: 'OTP is required' }); return; }
    setErrors({});
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const body = contact.includes('@') ? { email: contact, otp } : { mobile_no: contact, otp };
      const res = await verifyOtp(body);
      if (res.success && res.token) {
        onSuccess(res.token, res.data);
      } else {
        setServerMsg({ text: res.message || 'Invalid OTP. Please try again.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        applyFieldErrors(setErrors, err.fieldErrors, { otp: 'otp' });
      }
      setServerMsg({ text: err.message || 'Invalid OTP.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={otpSent ? handleVerify : handleSend} noValidate>
      <h2 className="auth-dropdown__title">Login with OTP</h2>
      <ServerMsg {...serverMsg} />

      <div className="auth-form__group">
        <label className="auth-form__label">Enter Phone/Email *</label>
        <input
          type="text"
          value={contact}
          onChange={(e) => { setContact(e.target.value); if (errors.contact) setErrors({}); }}
          onBlur={() => {
            if (contact.includes('@') && !isValidEmail(contact))
              setErrors((p) => ({ ...p, contact: 'Enter a valid email address (e.g. name@domain.com)' }));
          }}
          disabled={otpSent}
          className={`auth-form__input${errors.contact ? ' error' : ''}`}
        />
        <FieldError msg={errors.contact} />
      </div>

      {otpSent && (
        <div className="auth-form__group">
          <label className="auth-form__label">Enter OTP *</label>
          <input
            type="text"
            inputMode="numeric"
            maxLength={6}
            value={otp}
            onChange={(e) => { setOtp(e.target.value); if (errors.otp) setErrors({}); }}
            placeholder="6-digit OTP"
            className={`auth-form__input${errors.otp ? ' error' : ''}`}
          />
          <FieldError msg={errors.otp} />
        </div>
      )}

      <button type="submit" className="auth-form__submit" disabled={loading}>
        {loading ? '…' : otpSent ? 'Verify & Login' : 'Send OTP'}
      </button>

      {otpSent && (
        <p className="auth-form__footer" style={{ marginBottom: 8 }}>
          <button type="button" className="auth-form__link"
            onClick={() => { setOtpSent(false); setOtp(''); setServerMsg({ text: '', type: '' }); }}>
            Resend OTP
          </button>
        </p>
      )}

      <p className="auth-form__footer">
        <button type="button" onClick={onBack}>← Back to Login</button>
      </p>
    </form>
  );
}

/* ══════════════════════════════════════
   REGISTER FORM
══════════════════════════════════════ */
function RegisterForm({ onLogin, onSuccess, onNeedOtp }) {
  const [fields, setFields] = useState({
    first_name: '', last_name: '', email: '', mobile_no: '',
    gender: '', date_of_birth: '', password: '', password_confirmation: '',
  });
  const [errors, setErrors] = useState({});
  const [serverMsg, setServerMsg] = useState({ text: '', type: '' });
  const [loading, setLoading] = useState(false);

  const set = (k) => (e) => {
    setFields((f) => ({ ...f, [k]: e.target.value }));
    if (errors[k]) setErrors((prev) => ({ ...prev, [k]: undefined }));
  };

  /* Real-time email validation on blur */
  const handleEmailBlur = () => {
    const emailErr = validateEmail(fields.email);
    if (emailErr) setErrors((prev) => ({ ...prev, email: emailErr }));
    else setErrors((prev) => ({ ...prev, email: undefined }));
  };

  const validate = () => {
    const e = {};
    if (!fields.first_name.trim())  e.first_name = 'Required';
    if (!fields.last_name.trim())   e.last_name  = 'Required';
    const emailErr = validateEmail(fields.email);
    if (emailErr) e.email = emailErr;
    if (!fields.mobile_no.trim())   e.mobile_no  = 'Required';
    else if (!/^\d{10}$/.test(fields.mobile_no)) e.mobile_no = '10 digits required';
    if (!fields.gender)             e.gender     = 'Required';
    if (!fields.date_of_birth)      e.date_of_birth = 'Required';
    if (!fields.password)           e.password   = 'Required';
    else if (fields.password.length < 6) e.password = 'Min 6 characters';
    if (fields.password_confirmation !== fields.password) e.password_confirmation = 'Passwords do not match';
    return e;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }
    setErrors({});
    setLoading(true);
    setServerMsg({ text: '', type: '' });
    try {
      const res = await registerUser(fields);
      if (res.success) {
        onNeedOtp(fields.email, 'registration');
      } else {
        setServerMsg({ text: res.message || 'Registration failed.', type: 'error' });
      }
    } catch (err) {
      if (err instanceof ApiError && err.fieldErrors) {
        /* API field names match form field names directly for registration */
        applyFieldErrors(setErrors, err.fieldErrors);
      }
      setServerMsg({ text: err.message || 'Registration failed.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} noValidate>
      <h2 className="auth-dropdown__title">New User? Register Here!</h2>
      <ServerMsg {...serverMsg} />

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">First Name</label>
          <input type="text" value={fields.first_name} onChange={set('first_name')}
            className={`auth-form__input${errors.first_name ? ' error' : ''}`} />
          <FieldError msg={errors.first_name} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Last Name</label>
          <input type="text" value={fields.last_name} onChange={set('last_name')}
            className={`auth-form__input${errors.last_name ? ' error' : ''}`} />
          <FieldError msg={errors.last_name} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">Email</label>
          <input
            type="email"
            value={fields.email}
            onChange={set('email')}
            onBlur={handleEmailBlur}
            className={`auth-form__input${errors.email ? ' error' : ''}`}
          />
          <FieldError msg={errors.email} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Mobile No</label>
          <input type="tel" value={fields.mobile_no} onChange={set('mobile_no')}
            className={`auth-form__input${errors.mobile_no ? ' error' : ''}`} />
          <FieldError msg={errors.mobile_no} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <div className="auth-form__group">
          <label className="auth-form__label">Gender</label>
          <select value={fields.gender} onChange={set('gender')}
            className={`auth-form__select${errors.gender ? ' error' : ''}`}>
            <option value="">select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
          <FieldError msg={errors.gender} />
        </div>
        <div className="auth-form__group">
          <label className="auth-form__label">Date of Birth</label>
          <input type="date" value={fields.date_of_birth} onChange={set('date_of_birth')}
            className={`auth-form__input${errors.date_of_birth ? ' error' : ''}`} />
          <FieldError msg={errors.date_of_birth} />
        </div>
      </div>

      <div className="auth-form__grid-2">
        <PasswordField label="Enter Password" id="rg-pw"
          value={fields.password} onChange={set('password')} error={errors.password} />
        <PasswordField label="Confirm Password" id="rg-cpw"
          value={fields.password_confirmation} onChange={set('password_confirmation')}
          error={errors.password_confirmation} />
      </div>

      <button type="submit" className="auth-form__submit" disabled={loading}>
        {loading ? 'Registering…' : 'Sign Up'}
      </button>

      <p className="auth-form__footer">
        Already registered?&nbsp;
        <button type="button" onClick={onLogin}>Sign In</button>
      </p>
    </form>
  );
}

/* ══════════════════════════════════════
   ROOT MODAL (orchestrates views)
══════════════════════════════════════ */
function AuthModal({ onClose }) {
  const { login } = useAuth();
  const [view, setView] = useState('login');
  const [pendingEmail, setPendingEmail] = useState('');
  const [pendingPurpose, setPendingPurpose] = useState('');
  const [verifiedMsg, setVerifiedMsg] = useState('');

  const handleSuccess = (token, userData) => {
    login(token, userData);
    onClose();
  };

  const handleNeedOtp = (email, purpose) => {
    setPendingEmail(email);
    setPendingPurpose(purpose);
    setView('otp-verify');
  };

  const handleOtpVerified = () => {
    setVerifiedMsg('Email verified! Please sign in.');
    setView('login');
  };

  return (
    <div className="auth-dropdown">
      {view === 'login' && (
        <LoginForm
          onRegister={() => setView('register')}
          onOtp={() => setView('otp')}
          onSuccess={handleSuccess}
          onNeedOtp={handleNeedOtp}
          verifiedMsg={verifiedMsg}
          onClearVerifiedMsg={() => setVerifiedMsg('')}
        />
      )}
      {view === 'otp' && <OtpForm onBack={() => setView('login')} onSuccess={handleSuccess} />}
      {view === 'register' && (
        <RegisterForm onLogin={() => setView('login')} onSuccess={handleSuccess} onNeedOtp={handleNeedOtp} />
      )}
      {view === 'otp-verify' && (
        <OtpVerifyForm
          email={pendingEmail}
          purpose={pendingPurpose}
          onBack={() => setView('login')}
          onSuccess={handleSuccess}
          onVerified={handleOtpVerified}
        />
      )}
    </div>
  );
}

export default AuthModal;
