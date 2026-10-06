<?php
$title = 'Two-Factor Authentication';
require_once __DIR__ . '/../layouts/header.php';
$logoUrl = $settings['panel_logo'] ?? '/arix/Arix.png';
$panelName = $settings['panel_name'] ?? 'Nova';
?>
<div class="arix-auth-page">
  <div class="arix-auth-box">
    <div style="text-align: center; margin-bottom: 2rem;">
      <img src="<?= e($logoUrl) ?>" alt="Arix Logo" style="height: 52px; margin-bottom: 1rem; object-fit: contain;">
      <h1 style="font-size: 1.55rem; font-weight: 700; color: #ffffff;">Two-Factor Challenge</h1>
      <p style="font-size: 0.85rem; color: rgb(var(--gray300)); margin-top: 0.35rem;">Enter the 6-digit verification code from your authenticator app</p>
    </div>

    <form action="/auth/two-factor" method="POST">
      <input type="hidden" name="returnTo" value="<?= e($returnTo ?? '/dashboard') ?>">
      <div class="form-group" style="margin-bottom: 1.5rem;">
        <label class="form-label" for="code" style="display: block; font-size: 0.825rem; font-weight: 500; color: rgb(var(--gray200)); margin-bottom: 0.4rem;">Authentication Code</label>
        <input type="text" class="form-control" id="code" name="code" placeholder="123456" maxlength="6" style="text-align: center; font-size: 1.35rem; letter-spacing: 0.35em; font-family: var(--font-mono);" required autofocus>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 600; box-shadow: 0 4px 18px rgba(74, 53, 207, 0.45);">
        Verify & Continue
      </button>

      <div style="text-align: center; margin-top: 1.5rem;">
        <a href="/auth/login" style="font-size: 0.85rem; color: rgb(var(--gray300)); text-decoration: none;">← Back to Sign In</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
