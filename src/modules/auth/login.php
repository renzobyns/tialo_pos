<?php


if (isset($_SESSION['user_id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>
<?php
$page_title = 'Login - Tialo Japan Surplus POS';
$page_styles = <<<EOT
<style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      :root {
        --color-primary: #BA181B;
        --color-primary-hover: #A4161A;
        --color-primary-active: #660708;
        --color-surface: #FFFFFF;
        --color-bg: #F5F3F4;
        --color-text-strong: #161A1D;
        --color-text-muted: #B1A7A6;
        --color-border: #D3D3D3;
        --color-accent: #E5383B;
      }

      html, body {
        height: 100%;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      }

      body {
        background: linear-gradient(135deg, var(--color-bg) 0%, #EAE5E4 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
      }

      .login-container {
        width: 100%;
        max-width: 380px;
      }

      .login-card {
        background: var(--color-surface);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--color-border);
      }

      .login-brand {
        background: linear-gradient(135deg, #0B090A 0%, #161A1D 100%);
        color: white;
        padding: 32px 24px;
        text-align: center;
      }

      .login-brand-circle {
        width: 60px;
        height: 60px;
        background: var(--color-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 28px;
        font-weight: 700;
      }

      .login-brand h1 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 4px;
      }

      .login-brand p {
        font-size: 13px;
        opacity: 0.7;
      }

      .login-form-section {
        padding: 28px 24px;
      }

      .login-error {
        background-color: #FEE;
        border: 1px solid #FCC;
        color: var(--color-accent);
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 16px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
      }

      .login-error-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        margin-top: 2px;
      }

      .form-group {
        margin-bottom: 14px;
      }

      .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--color-text-strong);
        margin-bottom: 5px;
      }

      .form-group input {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        transition: all 0.2s;
      }

      .form-group input:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(186, 24, 27, 0.1);
      }

      .form-group input::placeholder {
        color: var(--color-text-muted);
      }

      .login-submit {
        width: 100%;
        padding: 9px 16px;
        background-color: var(--color-primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 4px;
      }

      .login-submit:hover {
        background-color: var(--color-primary-hover);
      }

      .login-submit:active {
        background-color: var(--color-primary-active);
        transform: scale(0.98);
      }

      .login-footer {
        border-top: 1px solid var(--color-border);
        padding: 16px 24px;
        background-color: #F9F8F8;
      }

      .login-footer-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--color-text-strong);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
      }

      .login-credentials {
        display: flex;
        flex-direction: column;
        gap: 6px;
      }

      .login-credentials-item {
        font-size: 12px;
        color: var(--color-text-strong);
        padding: 6px 8px;
        background: var(--color-surface);
        border-radius: 6px;
        border: 1px solid var(--color-border);
        font-family: 'Monaco', 'Menlo', monospace;
      }
    </style>
EOT;
include __DIR__ . '/../../includes/page_header.php';
?>
<body>
  <div class="login-container">
    <div class="login-card">
      <!-- Brand Section -->
      <div class="login-brand">
        <div class="login-brand-circle">T</div>
        <h1>Tialo Japan Surplus</h1>
        <p>POS System</p>
      </div>

      <!-- Form Section -->
      <div class="login-form-section">
        <?php if ($error): ?>
          <div class="login-error">
            <svg class="login-error-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <div><?php echo htmlspecialchars($error); ?></div>
          </div>
        <?php endif; ?>

        <form method="POST" action="/index.php?page=auth/login_process">
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="admin@tialo.com" required autofocus>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
          </div>
          <button type="submit" class="login-submit">Sign In</button>
        </form>
      </div>

      <!-- Footer Section -->
      <div class="login-footer">
        <div class="login-footer-label">Demo Credentials</div>
        <div class="login-credentials">
          <div class="login-credentials-item">admin@tialo.com</div>
          <div class="login-credentials-item">admin123</div>
        </div>
      </div>
    </div>
  </div>
<?php include __DIR__ . '/../../includes/page_footer.php'; ?>
