<?php
$title = 'SSH Keys';
$breadcrumb = 'Account / SSH Keys';
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
        <h1 class="page-title">SSH Keys</h1>
        <p class="page-description">Manage public SSH cryptographic keys for automated, passwordless SFTP authentication.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-ssh')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        Add SSH Key
      </button>
    </div>

    <?php if (empty($keys)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div class="nova-empty-title">No SSH Keys Linked</div>
        <div class="nova-empty-desc">Attach public SSH keys (e.g. id_rsa.pub or id_ed25519.pub) to securely access server files via FileZilla, Cyberduck, or CLI tools.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-ssh')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Add First SSH Key
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Key Name</th>
              <th>Fingerprint</th>
              <th>Date Added</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($keys as $k): ?>
              <tr>
                <td style="font-weight: 600; color: #fff;">
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="var(--accent-blue)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    <?= e($k['name']) ?>
                  </div>
                </td>
                <td>
                  <span style="font-family: var(--font-mono); color: var(--accent-blue); background: rgba(56, 189, 248, 0.1); padding: 0.25rem 0.55rem; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.2); font-size: 0.8rem;">
                    <?= e($k['fingerprint']) ?>
                  </span>
                </td>
                <td style="color: var(--text-dim); font-size: 0.85rem;"><?= date('M d, Y', strtotime($k['created_at'])) ?></td>
                <td style="text-align: right;">
                  <form action="/account/ssh-keys/<?= e($k['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete this SSH key from your profile?');">
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">
                      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- Modal Add SSH Key -->
    <div id="modal-new-ssh" class="nova-modal-overlay">
      <div class="nova-modal-dialog" style="max-width: 520px;">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Add Public SSH Key
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/account/ssh-keys" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">Key Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Work MacBook, Home Desktop" required autofocus>
            </div>
            <div class="form-group">
              <label class="form-label">Public Key Data</label>
              <textarea name="public_key" class="form-control" rows="5" placeholder="ssh-ed25519 AAAAC3NzaC1lZDI1NTE5... user@host" required style="font-family: var(--font-mono); font-size: 0.8rem; line-height: 1.45;"></textarea>
              <div class="form-text">Paste the contents of your public key. Only OpenSSH-compatible formats are accepted.</div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Save Key</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
