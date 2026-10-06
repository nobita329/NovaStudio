<?php
$title = 'Admin Dashboard';
$breadcrumb = 'Admin / Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Administrative Overview</h1>
        <p class="page-description">Manage infrastructure, nodes, deployments, and global system health.</p>
      </div>

      <!-- Quick Actions -->
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/admin/servers/new" class="btn btn-primary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          Create Server
        </a>
        <a href="/admin/users/new" class="btn btn-secondary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
          Create User
        </a>
        <a href="/admin/nodes/new" class="btn btn-secondary btn-sm">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
          Create Node
        </a>
      </div>
    </div>

    <!-- 01. ADMIN DASHBOARD STATS -->
    <div class="grid-cols-4">
      <div class="stat-card">
        <div class="stat-icon blue">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['users']) ?></span>
          <span class="stat-label">Total Users</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon green">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['servers']) ?></span>
          <span class="stat-label">Total Servers</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon amber">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['nodes']) ?></span>
          <span class="stat-label">Active Nodes</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon purple">
          <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </div>
        <div class="stat-content">
          <span class="stat-value"><?= e($stats['locations']) ?></span>
          <span class="stat-label">Locations</span>
        </div>
      </div>
    </div>

    <!-- System Overview -->
    <div style="margin-top: 1.5rem;">
      <h2 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 1rem;">System Overview</h2>
      <div class="grid-cols-3">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Host CPU</h3>
            <span style="font-family: var(--font-mono); color: var(--accent-blue); font-weight: 700;"><?= e($system['cpu']['load']) ?>%</span>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-muted);"><?= e($system['cpu']['model']) ?> (<?= e($system['cpu']['cores']) ?> Cores)</p>
          <div style="background: var(--bg-surface); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 1rem;">
            <div style="background: var(--accent-blue); height: 100%; width: <?= min(100, $system['cpu']['load']) ?>%;"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Host Memory</h3>
            <span style="font-family: var(--font-mono); color: var(--accent-success); font-weight: 700;"><?= e($system['memory']['percent']) ?>%</span>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-muted);"><?= e($system['memory']['used']) ?> MB / <?= e($system['memory']['total']) ?> MB</p>
          <div style="background: var(--bg-surface); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 1rem;">
            <div style="background: var(--accent-success); height: 100%; width: <?= min(100, $system['memory']['percent']) ?>%;"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Host Disk</h3>
            <span style="font-family: var(--font-mono); color: var(--accent-warning); font-weight: 700;"><?= e($system['disk']['percent']) ?>%</span>
          </div>
          <p style="font-size: 0.85rem; color: var(--text-muted);"><?= e($system['disk']['used']) ?> GB / <?= e($system['disk']['total']) ?> GB</p>
          <div style="background: var(--bg-surface); height: 8px; border-radius: 4px; overflow: hidden; margin-top: 1rem;">
            <div style="background: var(--accent-warning); height: 100%; width: <?= min(100, $system['disk']['percent']) ?>%;"></div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
