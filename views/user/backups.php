<?php
$title = 'Backups — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Backups';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Backups</h1>
        <p class="page-description">Generate, restore, and download complete archives of your server files.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-backup')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        Create Backup
      </button>
    </div>

    <?php if (empty($backups)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
        </div>
        <div class="nova-empty-title">No Backups Created</div>
        <div class="nova-empty-desc">Create periodic backup snapshots of your container files to safeguard against unexpected data loss.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-backup')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Create First Backup
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Backup Name</th>
              <th>Archive Size</th>
              <th>Created Date</th>
              <th>Status</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($backups as $b): ?>
              <tr>
                <td>
                  <div style="font-weight: 600; color: #fff; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="var(--accent-blue)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                    <?= e($b['name']) ?>
                  </div>
                  <div style="font-size: 0.75rem; color: var(--text-dim); font-family: var(--font-mono); margin-top: 0.15rem;"><?= e($b['file_name']) ?></div>
                </td>
                <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.85rem;"><?= bytesToSize($b['size_bytes']) ?></td>
                <td style="color: var(--text-dim); font-size: 0.85rem;"><?= date('M d, Y H:i', strtotime($b['created_at'])) ?></td>
                <td>
                  <span class="status-badge <?= strtolower($b['status']) === 'completed' || strtolower($b['status']) === 'successful' ? 'badge-online' : 'badge-starting' ?>">
                    <span class="badge-dot"></span><?= ucfirst($b['status']) ?>
                  </span>
                </td>
                <td style="text-align: right;">
                  <div style="display: inline-flex; gap: 0.45rem;">
                    <a href="/server/<?= e($server['id']) ?>/backups/<?= e($b['id']) ?>/download" class="btn btn-secondary btn-sm" title="Download backup archive">
                      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                      Download
                    </a>
                    <form action="/server/<?= e($server['id']) ?>/backups/<?= e($b['id']) ?>/restore" method="POST" onsubmit="return confirm('Restore this backup archive? Existing server files may be overwritten.');" style="display:inline;">
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-warning);">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Restore
                      </button>
                    </form>
                    <form action="/server/<?= e($server['id']) ?>/backups/<?= e($b['id']) ?>/delete" method="POST" onsubmit="return confirm('Permanently delete this backup?');" style="display:inline;">
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- Create Backup Modal -->
    <div id="modal-new-backup" class="nova-modal-overlay">
      <div class="nova-modal-dialog">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
            Create Server Backup
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/backups" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">Backup Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Pre-Update Backup" required autofocus>
              <div class="form-text">Provide an easily identifiable name for this snapshot.</div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Start Backup</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
