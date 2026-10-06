<?php
$title = 'Servers';
$breadcrumb = 'Servers';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">All Servers</h1>
        <p class="page-description">Managed instances under your jurisdiction.</p>
      </div>
      <?php if (!empty($user['root_admin'])): ?>
        <a href="/admin/servers/new" class="btn btn-primary">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Deploy Server
        </a>
      <?php endif; ?>
    </div>

    <div class="servers-view-container">
      <?php if (empty($servers)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
          <svg width="48" height="48" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" style="margin: 0 auto 1rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
          <h3 style="font-size: 1.1rem; color: var(--text-main); margin-bottom: 0.5rem;">No servers available</h3>
          <p style="font-size: 0.875rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 1.5rem;">There are currently no active servers under your management.</p>
          <?php if (!empty($user['root_admin'])): ?>
            <a href="/admin/servers/new" class="btn btn-primary">Create Your First Server</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="server-cards-grid">
          <?php foreach ($servers as $srv): ?>
            <?php require __DIR__ . '/partials/server_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
