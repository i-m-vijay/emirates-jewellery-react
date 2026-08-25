import { useState } from 'react';
import { MapPin, Mail, Phone } from 'lucide-react';
import { useToast } from '../context/ToastContext';
import { validateEmail } from '../utils/apiError';

const IMAGE_PATH = '/assets/images/';

const FAQS = [
  {
    q: 'How do you shop at Emirates online? Do you need an Emirates account?',
    a: 'If you do not wish to create an account, you can use the guest checkout option. However, we recommend you create the account since it has multiple benefits.',
  },
  {
    q: "I've forgotten my password. How can I set it again?",
    a: 'Yes. Please click the forgot password button and follow the on screen instructions.',
  },
  {
    q: 'What are the benefits of creating the account?',
    a: 'Creating an account grants you access to a variety of Emirates services, including viewing your order history, making online payments for gold purchases, and receiving notifications about the latest product launches.',
  },
  {
    q: 'Would it be possible to request separate deliveries of different items from my bag?',
    a: 'Yes. You can do so by writing an email to customercare.us@emirates.com or by calling the customer care team at +1 732 372 7357.',
  },
];

function FieldError({ msg }) {
  if (!msg) return null;
  return <p className="auth-form__field-error">{msg}</p>;
}

const EMPTY_FIELDS = { first_name: '', last_name: '', mobile_no: '', email: '', subject: '', message: '' };

function ContactUs() {
  const { showToast } = useToast();
  const [fields, setFields]       = useState(EMPTY_FIELDS);
  const [errors, setErrors]       = useState({});
  const [submitting, setSubmitting] = useState(false);

  const set = (k) => (e) => {
    setFields((f) => ({ ...f, [k]: e.target.value }));
    if (errors[k]) setErrors((p) => ({ ...p, [k]: undefined }));
  };

  const validate = () => {
    const e = {};
    if (!fields.first_name.trim()) e.first_name = 'Required';
    if (!fields.last_name.trim())  e.last_name  = 'Required';
    if (!fields.mobile_no.trim())  e.mobile_no  = 'Required';
    else if (!/^\d{10}$/.test(fields.mobile_no)) e.mobile_no = 'Enter a 10 digit number';
    const emailErr = validateEmail(fields.email);
    if (emailErr) e.email = emailErr;
    if (!fields.subject.trim()) e.subject = 'Required';
    if (!fields.message.trim()) e.message = 'Required';
    return e;
  };

  const handleSubmit = (ev) => {
    ev.preventDefault();
    const errs = validate();
    if (Object.keys(errs).length) { setErrors(errs); return; }

    setErrors({});
    setSubmitting(true);
    // No backend endpoint for this form yet — acknowledge locally.
    setTimeout(() => {
      setSubmitting(false);
      setFields(EMPTY_FIELDS);
      showToast('Thanks for reaching out! Our team will get back to you shortly.', 'success');
    }, 400);
  };

  return (
    <div className="contact-page">
      {/* ── Hero ── */}
      <section className="contact-hero">
        <div className="contact-hero__info">
          <h1 className="contact-hero__title">GET IN<br />TOUCH</h1>

          <div className="contact-hero__office">
            <h4>Registered Office</h4>
            <p><MapPin size={15} /> Emirates Gold &amp; Diamonds US Inc, 1665 Oak Tree Rd, Suite # 270, Edison, NJ 08820</p>
            <p><Mail size={15} /> customercare.us@emirates.com</p>
            <p><Phone size={15} /> +1 732 372 7357</p>
          </div>

          <div className="contact-hero__hours">
            <strong>Help Desk Timing:</strong> Monday to Friday, 9:00 am to 5:00 pm
          </div>
        </div>

        <div className="contact-hero__img-wrap">
          <img
            src={`${IMAGE_PATH}luxury-jewellery.jpg`}
            alt="Emirates Gold & Diamonds"
            loading="eager"
            decoding="async"
          />
        </div>
      </section>

      {/* ── Contact form ── */}
      <section className="section">
        <h2 className="contact-section-heading">Contact Us</h2>

        <form className="contact-form" onSubmit={handleSubmit} noValidate>
          <div className="auth-form__grid-2">
            <div className="auth-form__group">
              <label className="auth-form__label">First Name</label>
              <input
                type="text"
                placeholder="Enter First Name"
                value={fields.first_name}
                onChange={set('first_name')}
                className={`auth-form__input${errors.first_name ? ' error' : ''}`}
              />
              <FieldError msg={errors.first_name} />
            </div>
            <div className="auth-form__group">
              <label className="auth-form__label">Last Name</label>
              <input
                type="text"
                placeholder="Enter Last Name"
                value={fields.last_name}
                onChange={set('last_name')}
                className={`auth-form__input${errors.last_name ? ' error' : ''}`}
              />
              <FieldError msg={errors.last_name} />
            </div>
          </div>

          <div className="auth-form__grid-2">
            <div className="auth-form__group">
              <label className="auth-form__label">Mobile Number</label>
              <input
                type="tel"
                placeholder="Enter Mobile Number"
                value={fields.mobile_no}
                onChange={set('mobile_no')}
                className={`auth-form__input${errors.mobile_no ? ' error' : ''}`}
              />
              <FieldError msg={errors.mobile_no} />
            </div>
            <div className="auth-form__group">
              <label className="auth-form__label">Email Id</label>
              <input
                type="email"
                placeholder="Enter Email Id"
                value={fields.email}
                onChange={set('email')}
                className={`auth-form__input${errors.email ? ' error' : ''}`}
              />
              <FieldError msg={errors.email} />
            </div>
          </div>

          <div className="auth-form__grid-2">
            <div className="auth-form__group">
              <label className="auth-form__label">Subject</label>
              <input
                type="text"
                placeholder="Enter Subject"
                value={fields.subject}
                onChange={set('subject')}
                className={`auth-form__input${errors.subject ? ' error' : ''}`}
              />
              <FieldError msg={errors.subject} />
            </div>
            <div className="auth-form__group">
              <label className="auth-form__label">Message</label>
              <input
                type="text"
                placeholder="Enter Comment"
                value={fields.message}
                onChange={set('message')}
                className={`auth-form__input${errors.message ? ' error' : ''}`}
              />
              <FieldError msg={errors.message} />
            </div>
          </div>

          <button type="submit" className="auth-form__submit contact-form__submit" disabled={submitting}>
            {submitting ? 'Submitting…' : 'Submit'}
          </button>
        </form>
      </section>

      {/* ── FAQs ── */}
      <section className="section cream">
        <h2 className="contact-section-heading">Frequent Searches</h2>

        <div className="contact-faq-grid">
          {FAQS.map((item) => (
            <div className="contact-faq-item" key={item.q}>
              <h3>{item.q}</h3>
              <p>{item.a}</p>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
}

export default ContactUs;
