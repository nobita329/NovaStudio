<?php
$title = 'Advanced Settings';
$breadcrumb = 'Admin / Settings / Advanced';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$isMaintenance = ($settings['maintenance_mode'] ?? '0') === '1';
$isDebug = ($settings['debug_mode'] ?? '0') === '1';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Advanced Settings</h1>
        <p class="page-description">Maintenance modes, operational states, and diagnostics.</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">Advanced settings saved.</div>
    <?php endif; ?>

    <div class="card" style="max-width: 650px;">
      <form action="/admin/settings/advanced" method="POST">
        <!-- Maintenance Mode Toggle -->
        <div class="form-group" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
          <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
            <input type="checkbox" name="maintenance_mode" value="1" <?= $isMaintenance ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--accent-warning);">
            <span style="font-weight: 700; color: #fff;">Enable Maintenance Mode</span>
          </label>
          <div class="form-text">Blocks standard client traffic with a maintenance banner while allowing admins access.</div>
        </div>

        <div class="form-group">
          <label class="form-label">Maintenance Notice Message</label>
          <textarea name="maintenance_message" class="form-control" rows="3"><?= e($settings['maintenance_message'] ?? 'Nova Panel is currently undergoing maintenance. We will be back online shortly.') ?></textarea>
        </div>

        <!-- Debug Mode -->
        <div class="form-group" style="margin-top: 1.5rem;">
          <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
            <input type="checkbox" name="debug_mode" value="1" <?= $isDebug ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--accent-blue);">
            <span style="font-weight: 700; color: #fff;">Enable Debug Mode</span>
          </label>
          <div class="form-text">Verbose error traces and extended system logging.</div>
        </div>

        <div style="margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
