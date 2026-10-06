<?php
$title = 'Users — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Users';
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
        <h1 class="page-title">Subusers & Permissions</h1>
        <p class="page-description">Grant trusted users granular access to manage this server instance.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-subuser')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        Add Subuser
      </button>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (empty($subusers)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div class="nova-empty-title">No Subusers Assigned</div>
        <div class="nova-empty-desc">You have not delegated permissions to any subusers yet. Invite co-administrators or moderators with customized access.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-subuser')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Invite First Subuser
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Permissions</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($subusers as $su): ?>
              <tr>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(var(--primary-rgb, 74, 53, 207), 0.2); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: #fff;">
                      <?= strtoupper(substr($su['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <span style="font-weight: 600; color: #fff;"><?= e($su['username']) ?></span>
                  </div>
                </td>
                <td style="color: var(--text-muted); font-size: 0.85rem;"><?= e($su['email']) ?></td>
                <td>
                  <span class="badge badge-info">Full Access</span>
                </td>
                <td style="text-align: right;">
                  <form action="/server/<?= e($server['id']) ?>/users/<?= e($su['id']) ?>/delete" method="POST" onsubmit="return confirm('Remove access for <?= e($su['username']) ?>?');">
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">
                      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      Revoke
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- New Subuser Modal -->
    <div id="modal-new-subuser" class="nova-modal-overlay">
      <div class="nova-modal-dialog">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Invite New Subuser
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/users" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">User Account Email</label>
              <input type="email" name="email" class="form-control" placeholder="user@domain.com" required autofocus>
              <div class="form-text">The user must already have a registered account on this panel.</div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Granted Permissions</label>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-top: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="console" checked>
                  Console Access
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="power" checked>
                  Power Actions
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="files" checked>
                  File Manager
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="backups" checked>
                  Backups
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="databases" checked>
                  Databases
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: #fff; cursor: pointer;">
                  <input type="checkbox" name="permissions[]" value="schedules" checked>
                  Schedules
                </label>
              </div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Add Subuser</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
