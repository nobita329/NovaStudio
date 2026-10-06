<?php
$logoUrl = $settings['panel_logo'] ?? '/arix/Arix.png';
$panelName = $settings['panel_name'] ?? 'Nova';
$displayName = $panelName ?: 'NOVA';
$currentPath = $currentPath ?? '';
$activeServer = $server ?? null;
?>
<aside class="sidebar floating-sidebar">
  <div class="sidebar-header">
    <a href="/dashboard" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; overflow: hidden;">
      <img src="<?= e($logoUrl) ?>" alt="Panel Logo" class="sidebar-logo">
      <div class="brand-text-wrap" style="display: flex; flex-direction: column; overflow: hidden;">
        <span class="sidebar-title"><?= e($displayName) ?></span>
        <span style="font-size: 0.72rem; color: var(--text-dim); letter-spacing: 0.04em;">CLIENT PANEL</span>
      </div>
    </a>
  </div>

  <?php if ($activeServer): ?>
    <!-- Active Server Switcher Pill -->
    <div class="sidebar-server-box">
      <div class="sidebar-server-box-left">
        <span class="sidebar-server-status-dot <?= in_array(strtolower($activeServer['status'] ?? ''), ['online', 'running']) ? 'dot-online' : '' ?>"></span>
        <div class="sidebar-server-info-col">
          <span class="sidebar-server-name-label" title="<?= e($activeServer['name']) ?>"><?= e($activeServer['name']) ?></span>
          <span class="sidebar-server-sub-label"><?= e(substr($activeServer['uuid'] ?? $activeServer['id'], 0, 8)) ?></span>
        </div>
      </div>
      <a href="/servers" class="sidebar-switch-server-btn" title="Switch server / All servers">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
      </a>
    </div>
  <?php endif; ?>

  <nav class="sidebar-nav">
    <?php if ($activeServer): ?>
      <!-- Server Specific Menu -->
      <div class="nav-group">
        <div class="nav-group-title">Server Management</div>
        <ul class="nav-list">
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/console" class="nav-link <?= strpos($currentPath, '/console') !== false ? 'active active-gold' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              Console
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/files" class="nav-link <?= strpos($currentPath, '/files') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
              Files
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/databases" class="nav-link <?= strpos($currentPath, '/databases') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m-16 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
              Databases
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/schedules" class="nav-link <?= strpos($currentPath, '/schedules') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Schedules
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/users" class="nav-link <?= strpos($currentPath, '/users') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              Users
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/backups" class="nav-link <?= strpos($currentPath, '/backups') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
              Backups
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/network" class="nav-link <?= strpos($currentPath, '/network') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
              Network
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/startup" class="nav-link <?= strpos($currentPath, '/startup') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
              Startup
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/settings" class="nav-link <?= strpos($currentPath, '/settings') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
              Settings
            </a>
          </li>
          <li>
            <a href="/server/<?= e($activeServer['id']) ?>/activity" class="nav-link <?= strpos($currentPath, '/activity') !== false ? 'active' : '' ?>">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Activity
            </a>
          </li>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Main Navigation Overview -->
    <div class="nav-group">
      <div class="nav-group-title">Overview</div>
      <ul class="nav-list">
        <li>
          <a href="/dashboard" class="nav-link <?= $currentPath === '/dashboard' || $currentPath === '/' ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            Dashboard
          </a>
        </li>
        <li>
          <a href="/servers" class="nav-link <?= $currentPath === '/servers' ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
            Servers
          </a>
        </li>
      </ul>
    </div>

    <!-- Account Navigation -->
    <div class="nav-group">
      <div class="nav-group-title">Account</div>
      <ul class="nav-list">
        <li>
          <a href="/account" class="nav-link <?= $currentPath === '/account' ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Profile
          </a>
        </li>
        <li>
          <a href="/account/security" class="nav-link <?= strpos($currentPath, '/account/security') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            Security
          </a>
        </li>
        <li>
          <a href="/account/api" class="nav-link <?= strpos($currentPath, '/account/api') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            API Credentials
          </a>
        </li>
        <li>
          <a href="/account/ssh-keys" class="nav-link <?= strpos($currentPath, '/account/ssh-keys') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            SSH Keys
          </a>
        </li>
        <li>
          <a href="/account/activity" class="nav-link <?= strpos($currentPath, '/account/activity') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            Activity Logs
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- System Engine Widget -->
  <div class="sidebar-engine-card">
    <div class="engine-header">
      <span>System Engine</span>
      <span class="engine-status-dot"></span>
    </div>
    <div class="engine-row">
      <span class="engine-label">Web UI</span>
      <span class="port-orange">3001</span>
    </div>
    <div class="engine-row">
      <span class="engine-label">SFTP Server</span>
      <span class="port-cyan">3004</span>
    </div>
  </div>
</aside>
