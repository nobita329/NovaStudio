<?php
$title = '403 Forbidden';
require_once __DIR__ . '/../layouts/header.php';
?>
<div style="min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-main);">
  <div style="text-align: center; max-width: 480px;">
    <h1 style="font-size: 5rem; font-weight: 900; color: var(--accent-danger); line-height: 1;">403</h1>
    <h2 style="font-size: 1.5rem; color: #fff; margin: 1rem 0 0.5rem;">Access Forbidden</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
      <?= e($message ?? 'You do not have the required permissions to access this area or resource.') ?>
    </p>
    <a href="/dashboard" class="btn btn-primary">Return to Dashboard</a>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
