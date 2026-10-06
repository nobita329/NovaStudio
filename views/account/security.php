<?php
$title = 'Security';
$breadcrumb = 'Account / Security';
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
        <h1 class="page-title">Security & Credentials</h1>
        <p class="page-description">Protect your account with robust credentials and multi-factor authentication.</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="grid-cols-2">
      <!-- Change Password Card -->
      <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.25rem;">
          <h3 class="card-title">Change Account Password</h3>
        </div>
        <form action="/account/security/password" method="POST" onsubmit="const p1 = document.getElementById('new_pwd').value; const p2 = document.getElementById('conf_pwd').value; if(p1 !== p2) { alert('New passwords do not match.'); return false; }">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input type="password" name="current_password" class="form-control" required placeholder="••••••••••••">
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" id="new_pwd" name="new_password" class="form-control" required placeholder="Minimum 8 characters" minlength="8">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input type="password" id="conf_pwd" name="confirm_password" class="form-control" required placeholder="Re-type new password" minlength="8">
          </div>
          <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Update Password
            </button>
          </div>
        </form>
      </div>

      <!-- Two-Factor Authentication Card -->
      <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.25rem;">
          <h3 class="card-title">Two-Factor Authentication</h3>
        </div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem; line-height: 1.5;">
          Two-factor authentication adds an essential layer of defense by requiring an OTP code from Google Authenticator, Authy, or compatible app whenever you log in.
        </p>

        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; color: #34d399;">
              <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
              <div style="font-weight: 600; color: #fff; font-size: 0.9rem;">Multi-Factor Status</div>
              <div style="font-size: 0.775rem; color: var(--text-dim);">TOTP Authenticator Protection</div>
            </div>
          </div>
          <div>
            <span class="status-badge <?= !empty($user['two_factor_enabled']) ? 'badge-online' : 'badge-offline' ?>">
              <span class="badge-dot"></span>
              <?= !empty($user['two_factor_enabled']) ? '2FA Enabled' : '2FA Disabled' ?>
            </span>
          </div>
        </div>

        <div style="font-size: 0.8rem; color: var(--text-dim); line-height: 1.45;">
          Note: If you lose access to your authenticator application, please reach out to the panel root administrator to generate a reset bypass link.
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
