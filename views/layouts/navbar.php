<?php
$currentUser = $user ?? null;
?>
<header class="top-header">
  <div class="header-left">
    <button type="button" class="mobile-toggle" aria-label="Toggle Navigation">
      <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
    <div class="breadcrumb">
      <a href="/dashboard"><?= e($settings['panel_name'] ?? 'Nova') ?></a>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current"><?= e($breadcrumb ?? 'Console') ?></span>
    </div>
  </div>

  <div class="header-right nova-nav-actions ">
    <!-- Search Button -->
    <button type="button" class="nav-icon-btn" title="Search (Ctrl + K)">
      <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </button>

    <!-- Servers Switcher -->
    <a href="/servers" class="nav-icon-btn" title="Servers Switcher">
      <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    </a>

    <!-- Admin Area Badge Button -->
    <?php if (!empty($currentUser['root_admin'])): ?>
      <a href="/admin" class="btn-admin-area arix" title="Admin Control Area">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
        Admin Area
      </a>
    <?php endif; ?>

    <!-- Settings Icon -->
    <a href="/account" class="nav-icon-btn" title="Account Settings">
      <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
    </a>

    <!-- Theme Toggle (Arix Dark / Light / OLED) -->
    <button type="button" class="nav-icon-btn" onclick="toggleArixTheme()" title="Toggle Theme (Dark / Light / OLED)">
      <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
    </button>
    <script>
      function toggleArixTheme() {
        const current = localStorage.getItem('theme') || 'dark';
        let next = 'dark';
        if (current === 'dark') next = 'light';
        else if (current === 'light') next = 'oled';
        else next = 'dark';
        localStorage.setItem('theme', next);
        document.documentElement.classList.remove('lightmode', 'oled');
        if (next === 'light') document.documentElement.classList.add('lightmode');
        if (next === 'oled') document.documentElement.classList.add('oled');
      }
    </script>

    <?php if ($currentUser): ?>
      <!-- User Avatar Pill -->
      <a href="/account" class="user-avatar-pill" title="User Profile">
        <span class="user-avatar-letter"><?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?></span>
        <span class="user-pill-name"><?= e($currentUser['username'] ?? 'admin') ?></span>
        <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
      </a>

      <!-- Logout Button -->
      <a href="/auth/logout" class="nav-icon-btn logout-btn" title="Log Out">
        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
      </a>
    <?php endif; ?>
  </div>
</header>
