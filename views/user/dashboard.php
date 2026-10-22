<?php
$title = 'Dashboard';
$breadcrumb = 'Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Welcome back, <?= e($user['first_name'] ?: $user['username']) ?></h1>
        <p class="page-description">Overview of your active game servers, bot instances, and system resources.</p>
      </div>
      <?php if (!empty($user['root_admin'])): ?>
        <a href="/admin/servers/new" class="btn btn-primary">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Deploy Server
        </a>
      <?php endif; ?>
    </div>

    <!-- Quick Stats -->
    <div class="grid-cols-4">
      <div class="stat-card">
        <div class="stat-icon blue">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['total'] ?? 0) ?></span>
          <span class="stat-label">Total Servers</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon green">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['online'] ?? 0) ?></span>
          <span class="stat-label">Online Instances</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= round(($stats['totalMemory'] ?? 0) / 1024, 1) ?> GB</span>
          <span class="stat-label">Allocated RAM</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= round(($stats['totalDisk'] ?? 0) / 1024, 1) ?> GB</span>
          <span class="stat-label">Allocated SSD</span>
        </div>
      </div>
    </div>

    <!-- Servers List / Cards Section -->
    <div class="dashboard-servers-section">
      <div class="dashboard-servers-header">
        <div class="dashboard-servers-title-wrap">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="servers-title-icon" style="flex-shrink: 0;"><rect x="3" y="4" width="18" height="3.5" rx="1.75" fill="#818cf8"/><rect x="3" y="10.25" width="18" height="3.5" rx="1.75" fill="#818cf8"/><rect x="3" y="16.5" width="18" height="3.5" rx="1.75" fill="#818cf8"/></svg>
          <h2 class="dashboard-servers-title">Your Servers</h2>
          <?php if (!empty($servers)): ?>
            <div class="view-toggle-group" role="group" aria-label="Server view mode">
              <button type="button" class="view-toggle-btn btn-view-card active" onclick="window.switchServerView('card')" title="Card View" aria-label="Card View">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                <span>Card</span>
              </button>
              <button type="button" class="view-toggle-btn btn-view-list" onclick="window.switchServerView('list')" title="List View" aria-label="List View">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                <span>List</span>
              </button>
            </div>
          <?php endif; ?>
        </div>

        <div class="dashboard-servers-header-right">
          <a href="/servers" class="dashboard-view-all-link" title="View all managed servers">
            <span>View All</span>
            <span class="view-all-arrow">→</span>
          </a>
        </div>
      </div>

      <?php if (empty($servers)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
          <svg width="48" height="48" fill="none" stroke="var(--text-dim)" viewBox="0 0 24 24" style="margin: 0 auto 1rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
          <h3 style="font-size: 1.1rem; color: var(--text-main); margin-bottom: 0.5rem;">No servers found</h3>
          <p style="font-size: 0.875rem; color: var(--text-muted); max-width: 400px; margin: 0 auto 1.5rem;">There are currently no server instances assigned to your account.</p>
          <?php if (!empty($user['root_admin'])): ?>
            <a href="/admin/servers/new" class="btn btn-primary">Create Your First Server</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <!-- Card View Grid -->
        <div class="server-cards-grid">
          <?php foreach ($servers as $srv): ?>
            <?php require __DIR__ . '/partials/server_card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- List View Rows -->
        <div class="server-list-view" style="display: none;">
          <?php foreach ($servers as $srv): ?>
            <?php require __DIR__ . '/partials/server_row.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
