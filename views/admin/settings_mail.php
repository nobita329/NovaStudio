<?php
$title = 'Mail Settings';
$breadcrumb = 'Admin / Settings / Mail';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Mail Settings</h1>
        <p class="page-description">Configure outbound SMTP mail server for password resets and notifications.</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">Mail configuration updated successfully.</div>
    <?php endif; ?>

    <div class="card" style="max-width: 680px;">
      <form action="/admin/settings/mail" method="POST">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" value="<?= e($settings['smtp_host'] ?? 'smtp.example.com') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">SMTP Port</label>
            <input type="number" name="smtp_port" class="form-control" value="<?= e($settings['smtp_port'] ?? '587') ?>" required>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="smtp_user" class="form-control" value="<?= e($settings['smtp_user'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="smtp_pass" class="form-control" value="<?= e($settings['smtp_pass'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Encryption Protocol</label>
          <select name="smtp_encryption" class="form-control">
            <option value="tls" <?= ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
          </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">From Address</label>
            <input type="email" name="smtp_from" class="form-control" value="<?= e($settings['smtp_from'] ?? 'no-reply@nova.test') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">From Name</label>
            <input type="text" name="smtp_from_name" class="form-control" value="<?= e($settings['smtp_from_name'] ?? 'Nova Panel') ?>" required>
          </div>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary">Save Mail Configuration</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
