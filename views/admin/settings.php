<?php
$title = 'Panel Settings';
$breadcrumb = 'Admin / Settings';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$logoSrc = $settings['panel_logo'] ?? '/arix/Arix.png';
$faviconSrc = $settings['panel_favicon'] ?? '/arix/Arix.png';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Panel Settings</h1>
        <p class="page-description">Configure general metadata, brand customization, and identity.</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">Settings have been saved successfully.</div>
    <?php endif; ?>

    <form action="/admin/settings" method="POST" enctype="multipart/form-data">
      <div class="grid-cols-2">
        <!-- General Settings -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">General Settings</h3>
          </div>

          <div class="form-group">
            <label class="form-label">Panel Name</label>
            <input type="text" name="panel_name" class="form-control" value="<?= e($settings['panel_name'] ?? 'Nova') ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Company Name</label>
            <input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name'] ?? 'Nova Hosting') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Panel URL</label>
            <input type="text" name="panel_url" class="form-control" value="<?= e($settings['panel_url'] ?? 'http://localhost:3001') ?>" required>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Default Language</label>
              <select name="default_language" class="form-control">
                <option value="en" selected>English</option>
                <option value="ur">Urdu</option>
                <option value="hi">Hindi</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Timezone</label>
              <select name="timezone" class="form-control">
                <option value="UTC" selected>UTC</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Europe/London">Europe/London</option>
                <option value="Asia/Karachi">Asia/Karachi</option>
                <option value="Asia/Kolkata">Asia/Kolkata</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Branding Settings: Logo & Favicon Upload + URL + Live Preview -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Branding & Logo Management</h3>
          </div>

          <!-- Logo Management -->
          <div class="form-group" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
            <label class="form-label" style="font-weight: 700; color: #fff;">Panel Logo</label>
            <div style="display: flex; gap: 1.25rem; align-items: flex-start;">
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 0.8rem; color: var(--text-muted);">Logo Image Upload</label>
                <input type="file" name="logo_file" id="logo_file_input" class="form-control" accept="image/*">
                
                <label class="form-label" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.75rem;">Or Enter Logo URL</label>
                <input type="text" name="panel_logo_url" id="logo_url_input" class="form-control" value="<?= e($logoSrc) ?>" placeholder="https://example.com/logo.png">
              </div>

              <!-- Live Preview Box -->
              <div>
                <span style="font-size: 0.75rem; color: var(--text-dim); display: block; margin-bottom: 0.25rem;">Live Preview</span>
                <div class="preview-box">
                  <img id="logo_preview_img" src="<?= e($logoSrc) ?>" alt="Logo Preview">
                </div>
              </div>
            </div>
          </div>

          <!-- Favicon Management -->
          <div class="form-group">
            <label class="form-label" style="font-weight: 700; color: #fff;">Panel Favicon</label>
            <div style="display: flex; gap: 1.25rem; align-items: flex-start;">
              <div style="flex: 1;">
                <label class="form-label" style="font-size: 0.8rem; color: var(--text-muted);">Favicon File Upload</label>
                <input type="file" name="favicon_file" id="favicon_file_input" class="form-control" accept="image/*">

                <label class="form-label" style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.75rem;">Or Enter Favicon URL</label>
                <input type="text" name="panel_favicon_url" id="favicon_url_input" class="form-control" value="<?= e($faviconSrc) ?>" placeholder="https://example.com/favicon.png">
              </div>

              <!-- Live Preview Box -->
              <div>
                <span style="font-size: 0.75rem; color: var(--text-dim); display: block; margin-bottom: 0.25rem;">Live Preview</span>
                <div class="preview-box">
                  <img id="favicon_preview_img" src="<?= e($faviconSrc) ?>" alt="Favicon Preview">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div style="margin-top: 1.5rem;">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem;">
          Save All Settings
        </button>
      </div>
    </form>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
