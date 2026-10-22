<?php
$title = 'Pterodactyl Region Settings';
$breadcrumb = 'Admin / Settings / Region';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$ip_api = $settings['region-api:ip'] ?? 'ipwho.is';
$dns_api = $settings['region-api:dns'] ?? 'DNS-Google';
$version = '2.1';
$discord = 'https://dsc.gg/mrlg';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Pterodactyl Region Settings</h1>
        <p class="page-description">Configure geolocation and DNS resolution APIs for the Pterodactyl Region addon.</p>
      </div>
      <div>
        <a href="<?= e($discord) ?>" target="_blank" class="btn btn-secondary btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
          <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.894.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
          Discord Community
        </a>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success">Pterodactyl Region settings have been successfully updated.</div>
    <?php endif; ?>

    <div class="card" style="max-width: 680px;">
      <form action="/admin/settings/pterodactyl-region" method="POST">
        <div class="card-header" style="margin-bottom: 1.25rem;">
          <h3 class="card-title">API Settings</h3>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">API IP Connect</label>
            <select name="region_api_ip" class="form-control">
              <option value="ipwho.is" <?= $ip_api === 'ipwho.is' ? 'selected' : '' ?>>ipwho.is (Recommended)</option>
              <option value="ipapi.is" <?= $ip_api === 'ipapi.is' ? 'selected' : '' ?>>ipapi.is</option>
              <option value="ipapi.co" <?= $ip_api === 'ipapi.co' ? 'selected' : '' ?>>ipapi.co</option>
              <option value="geoiplookup.io" <?= $ip_api === 'geoiplookup.io' ? 'selected' : '' ?>>geoiplookup.io</option>
            </select>
            <div class="form-text">Primary IP Geolocation API provider.</div>
          </div>

          <div class="form-group">
            <label class="form-label">DNS IP Connect</label>
            <select name="region_api_dns" class="form-control">
              <option value="DNS-Google" <?= $dns_api === 'DNS-Google' ? 'selected' : '' ?>>DNS Google</option>
              <option value="DNS-Cloudflare" <?= $dns_api === 'DNS-Cloudflare' ? 'selected' : '' ?>>DNS Cloudflare</option>
            </select>
            <div class="form-text">DNS resolver used for node hostnames and FQDNs.</div>
          </div>
        </div>

        <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); padding: 1rem; border-radius: 8px; margin: 1.5rem 0;">
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="status-badge badge-online"><span class="badge-dot"></span> Active</span>
            <span style="font-size: 0.85rem; color: #e2e8f0;">
              You are running Pterodactyl Region version <code style="color:#38bdf8;"><?= e($version) ?></code>. Your addon is up-to-date with 90 supported region flags.
            </span>
          </div>
        </div>

        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
