<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
  .card { background: #fff; max-width: 480px; margin: 40px auto; border-radius: 8px;
          padding: 36px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
  .logo { font-size: 22px; font-weight: 700; color: #b8000e; margin-bottom: 24px; }
  h2 { font-size: 18px; color: #333; margin: 0 0 8px; }
  p  { color: #555; font-size: 14px; line-height: 1.6; margin: 0 0 16px; }
  .otp-box { background: #fff8f8; border: 2px dashed #b8000e; border-radius: 6px;
             text-align: center; padding: 18px; margin: 24px 0; }
  .otp-code { font-size: 40px; font-weight: 700; letter-spacing: 10px; color: #b8000e; }
  .expiry { font-size: 13px; color: #888; margin-top: 8px; }
  .footer { margin-top: 32px; font-size: 12px; color: #aaa; border-top: 1px solid #eee;
            padding-top: 16px; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">Emirates Jewellery</div>

  <h2>
    @if($purpose === 'registration')   Verify Your Email
    @elseif($purpose === 'login')      Your Login OTP
    @else                              Password Reset OTP
    @endif
  </h2>

  <p>
    @if($purpose === 'registration')
      Thank you for registering. Use the OTP below to verify your email address.
    @elseif($purpose === 'login')
      Use the OTP below to complete your login.
    @else
      Use the OTP below to reset your password.
    @endif
  </p>

  <div class="otp-box">
    <div class="otp-code">{{ $otp }}</div>
    <div class="expiry">Expires in {{ $expiryMinutes }} minutes</div>
  </div>

  <p>If you did not request this OTP, please ignore this email.</p>

  <div class="footer">
    &copy; {{ date('Y') }} Emirates Jewellery. Do not reply to this email.
  </div>
</div>
</body>
</html>
