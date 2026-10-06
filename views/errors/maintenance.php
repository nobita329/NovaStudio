<?php
$title = 'Maintenance Mode';
require_once __DIR__ . '/../layouts/header.php';
$logoUrl = $settings['panel_logo'] ?? '/assets/img/logo.png';
$panelName = $settings['panel_name'] ?? 'Nova';
?>
<div style="min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-main);">
  <div style="text-align: center; max-width: 500px; background: var(--bg-card); padding: 3rem 2rem; border-radius: var(--radius-xl); border: 1px solid var(--border-color);">
    <img src="<?= e($logoUrl) ?>" alt="Logo" style="height: 54px; margin-bottom: 1.5rem; object-fit: contain;">
    <h1 style="font-size: 1.75rem; color: #fff; margin-bottom: 0.75rem;">Under Maintenance</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.75rem;">
      <?= e($message ?? 'Nova Panel is undergoing scheduled updates. Please check back shortly.') ?>
    </p>
    <a href="/auth/login" class="btn btn-secondary btn-sm">Administrator Sign In</a>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
