<?php
$title = 'Sign In';
require_once __DIR__ . '/../layouts/header.php';
$logoUrl = $settings['panel_logo'] ?? '/arix/Arix.png';
$panelName = $settings['panel_name'] ?? 'Nova';
?>
<div class="arix-auth-page">
  <div class="arix-auth-box">
    <div style="text-align: center; margin-bottom: 2rem;">
      <img src="<?= e($logoUrl) ?>" alt="Arix Logo" style="height: 52px; margin-bottom: 1rem; object-fit: contain;">
      <h1 style="font-size: 1.55rem; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">Welcome to <?= e($panelName) ?></h1>
      <p style="font-size: 0.85rem; color: rgb(var(--gray300)); margin-top: 0.35rem;">Enter your credentials to access the panel</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem; background: rgba(143, 31, 32, 0.3); border: 1px solid var(--dangerBorder); color: var(--dangerText); padding: 0.75rem 1rem; border-radius: var(--radiusInput); display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem; background: rgba(61, 143, 31, 0.3); border: 1px solid var(--successBorder); color: var(--successText); padding: 0.75rem 1rem; border-radius: var(--radiusInput); display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <?= e($success) ?>
      </div>
    <?php endif; ?>

    <form action="/auth/login" method="POST">
      <input type="hidden" name="returnTo" value="<?= e($returnTo ?? '/dashboard') ?>">
      
      <div class="form-group" style="margin-bottom: 1.25rem;">
        <label class="form-label" for="username" style="display: block; font-size: 0.825rem; font-weight: 500; color: rgb(var(--gray200)); margin-bottom: 0.4rem;">Username or Email</label>
        <input type="text" class="form-control" id="username" name="username" value="<?= e($username ?? '') ?>" placeholder="admin or user@domain.com" required autofocus>
      </div>

      <div class="form-group" style="margin-bottom: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
          <label class="form-label" for="password" style="font-size: 0.825rem; font-weight: 500; color: rgb(var(--gray200)); margin-bottom: 0;">Password</label>
          <a href="/auth/forgot-password" style="font-size: 0.8rem; color: var(--primary-border);">Forgot password?</a>
        </div>
        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••••••" required>
      </div>

      <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; margin-bottom: 1.5rem;">
        <input type="checkbox" id="remember" name="remember" style="accent-color: var(--primary); width: 16px; height: 16px; cursor: pointer;">
        <label for="remember" style="font-size: 0.85rem; color: rgb(var(--gray300)); cursor: pointer;">Remember this session</label>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.95rem; font-weight: 600; box-shadow: 0 4px 18px rgba(74, 53, 207, 0.45);">
        Sign In to <?= e($panelName) ?>
      </button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
