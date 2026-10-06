<?php
$title = '404 Not Found';
require_once __DIR__ . '/../layouts/header.php';
?>
<div style="min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-main);">
  <div style="text-align: center; max-width: 480px;">
    <h1 style="font-size: 5rem; font-weight: 900; color: var(--accent-blue); line-height: 1;">404</h1>
    <h2 style="font-size: 1.5rem; color: #fff; margin: 1rem 0 0.5rem;">Resource Not Found</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
      <?= e($message ?? 'The page or server container you are looking for does not exist.') ?>
    </p>
    <a href="/dashboard" class="btn btn-primary">Return to Dashboard</a>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
