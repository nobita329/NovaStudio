<?php
$title = 'Forgot Password';
require_once __DIR__ . '/../layouts/header.php';
$logoUrl = $settings['panel_logo'] ?? '/arix/Arix.png';
$panelName = $settings['panel_name'] ?? 'Nova';
?>
<div class="arix-auth-page">
  <div class="arix-auth-box">
    <div style="text-align: center; margin-bottom: 2rem;">
      <img src="<?= e($logoUrl) ?>" alt="Arix Logo" style="height: 52px; margin-bottom: 1rem; object-fit: contain;">
      <h1 style="font-size: 1.55rem; font-weight: 700; color: #ffffff;">Reset Password</h1>
      <p style="font-size: 0.85rem; color: rgb(var(--gray300)); margin-top: 0.35rem;">Enter your email to receive recovery instructions</p>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem; background: rgba(61, 143, 31, 0.3); border: 1px solid var(--successBorder); color: var(--successText); padding: 0.75rem 1rem; border-radius: var(--radiusInput); display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <form action="/auth/forgot-password" method="POST">
      <div class="form-group" style="margin-bottom: 1.5rem;">
        <label class="form-label" for="email" style="display: block; font-size: 0.825rem; font-weight: 500; color: rgb(var(--gray200)); margin-bottom: 0.4rem;">Account Email</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" required autofocus>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 600; box-shadow: 0 4px 18px rgba(74, 53, 207, 0.45);">
        Send Reset Link
      </button>

      <div style="text-align: center; margin-top: 1.5rem;">
        <a href="/auth/login" style="font-size: 0.85rem; color: rgb(var(--gray300)); text-decoration: none;">← Return to Sign In</a>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
