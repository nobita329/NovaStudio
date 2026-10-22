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
        <!-- Controls Bar: Search & Card/List Switcher -->
        <div class="servers-controls-bar">
          <div class="server-search-box">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" id="serverSearchInput" placeholder="Filter servers by name, address, or node..." oninput="window.filterServers(this.value)">
          </div>
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
        </div>

        <!-- 1. Card View Grid -->
        <div class="server-cards-grid" id="serverCardsGrid">
          <?php foreach ($servers as $srv): ?>
            <?php require __DIR__ . '/partials/server_card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- 2. List View Rows -->
        <div class="server-list-view" id="serverListView" style="display: none;">
          <?php foreach ($servers as $srv): ?>
            <?php require __DIR__ . '/partials/server_row.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- No Filter Results Notice -->
        <div id="serversNoResults" class="card" style="display: none; text-align: center; padding: 3rem 1.5rem; color: var(--text-muted); margin-top: 1rem;">
          <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 0.75rem; opacity: 0.5;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
          <div style="font-size: 0.95rem; color: #fff; margin-bottom: 0.25rem;">No matching servers found</div>
          <div style="font-size: 0.825rem;">Try adjusting your search query or clear the filter.</div>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
