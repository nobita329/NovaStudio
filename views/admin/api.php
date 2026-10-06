<?php
$title = 'Application API';
$breadcrumb = 'Admin / API Keys';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Application API Keys</h1>
        <p class="page-description">Manage administrator credentials for programmatic access to the Nova REST API.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-new-admin-api').style.display='flex'">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Generate Admin Key
      </button>
    </div>

    <?php if (!empty($newKey)): ?>
      <div class="alert alert-success" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
        <strong>New Admin API Secret:</strong>
        <p style="font-size: 0.8rem;">Copy this key now. It provides full root access to the Nova Panel REST API on port 3003 and will not be displayed again.</p>
        <code style="background: #080b11; padding: 0.5rem 0.75rem; border-radius: 6px; font-family: var(--font-mono); color: #fff; width: 100%; word-break: break-all;"><?= e($newKey) ?></code>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Identifier</th>
            <th>Description</th>
            <th>Created By</th>
            <th>Created Date</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($keys)): ?>
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 3rem;">No application API keys active.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($keys as $k): ?>
              <tr>
                <td style="font-family: var(--font-mono); color: var(--accent-blue); font-weight: 600;"><?= e($k['identifier']) ?></td>
                <td><?= e($k['description']) ?></td>
                <td><?= e($k['username']) ?></td>
                <td style="color: var(--text-dim); font-size: 0.85rem;"><?= date('M d, Y', strtotime($k['created_at'])) ?></td>
                <td style="text-align: right;">
                  <form action="/admin/api/<?= e($k['id']) ?>/delete" method="POST" onsubmit="return confirm('Revoke this key?')">
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">Revoke</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal New Key -->
    <div id="modal-new-admin-api" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 440px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Generate Admin API Key</h3>
        <form action="/admin/api" method="POST">
          <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. WHMCS / Billing Integration" required autofocus>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-new-admin-api').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Generate Key</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
