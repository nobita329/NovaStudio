<?php
$title = 'API Keys';
$breadcrumb = 'Account / API Keys';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Account Subnavigation Tabs -->
    <?php require __DIR__ . '/partials/account_tabs.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Account API Credentials</h1>
        <p class="page-description">Generate programmatic access tokens for interacting with Nova REST APIs.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-api')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        Generate API Key
      </button>
    </div>

    <?php if (!empty($newKeySecret)): ?>
      <div class="card" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); margin-bottom: 1.5rem; padding: 1.25rem 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem; color: #34d399; font-weight: 700; margin-bottom: 0.5rem;">
          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          API Token Generated Successfully
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.85rem;">
          Please copy and store this API secret securely now. For security purposes, it will never be displayed again.
        </p>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
          <code style="background: #080b11; border: 1px solid var(--border-color); padding: 0.65rem 0.95rem; border-radius: var(--radius-sm); font-family: var(--font-mono); color: #fff; flex: 1; word-break: break-all; font-size: 0.85rem; user-select: all;"><?= e($newKeySecret) ?></code>
          <button type="button" class="btn btn-primary btn-sm" onclick="window.arixCopy && window.arixCopy('<?= e($newKeySecret) ?>')">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            Copy
          </button>
        </div>
      </div>
    <?php endif; ?>

    <?php if (empty($keys)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <div class="nova-empty-title">No API Tokens Created</div>
        <div class="nova-empty-desc">Create API credentials to automate server deployment, query server statuses, and integrate external applications.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-api')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Generate First Token
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Token Identifier</th>
              <th>Description</th>
              <th>Created Date</th>
              <th>Last Used</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($keys as $k): ?>
              <tr>
                <td>
                  <span style="font-family: var(--font-mono); color: var(--accent-blue); font-weight: 600; background: rgba(56, 189, 248, 0.1); padding: 0.25rem 0.55rem; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.2); font-size: 0.8rem;">
                    <?= e($k['identifier']) ?>
                  </span>
                </td>
                <td style="color: #fff; font-weight: 500; font-size: 0.875rem;"><?= e($k['description']) ?></td>
                <td style="color: var(--text-dim); font-size: 0.825rem;"><?= date('M d, Y', strtotime($k['created_at'])) ?></td>
                <td style="color: var(--text-muted); font-size: 0.825rem;"><?= $k['last_used_at'] ? date('M d, Y H:i', strtotime($k['last_used_at'])) : 'Never' ?></td>
                <td style="text-align: right;">
                  <form action="/account/api/<?= e($k['id']) ?>/delete" method="POST" onsubmit="return confirm('Revoke this API token immediately? Any apps using it will lose access.');">
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

    <!-- Modal Generate Token -->
    <div id="modal-new-api" class="nova-modal-overlay">
      <div class="nova-modal-dialog" style="max-width: 440px;">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            Generate API Token
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/account/api" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">Key Description</label>
              <input type="text" name="description" class="form-control" placeholder="e.g. Discord Bot, CI/CD Deployer" required autofocus>
              <div class="form-text">A memorable name indicating what will consume this token.</div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Token</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
