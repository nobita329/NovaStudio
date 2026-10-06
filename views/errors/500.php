<?php
$title = '500 Server Error';
require_once __DIR__ . '/../layouts/header.php';
?>
<div style="min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; padding: 2rem; background: var(--bg-main);">
  <div style="text-align: center; max-width: 520px;">
    <h1 style="font-size: 5rem; font-weight: 900; color: var(--accent-warning); line-height: 1;">500</h1>
    <h2 style="font-size: 1.5rem; color: #fff; margin: 1rem 0 0.5rem;">Internal Error</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
      <?= e($message ?? 'An unexpected error occurred while processing your request.') ?>
    </p>
    <a href="/dashboard" class="btn btn-primary">Return to Dashboard</a>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
