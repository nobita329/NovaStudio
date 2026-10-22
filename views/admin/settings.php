<?php
$title = 'Panel Settings';
$breadcrumb = 'Admin / Settings';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$logoSrc = $settings['panel_logo'] ?? '/arix/Arix.png';
$faviconSrc = $settings['panel_favicon'] ?? '/arix/Arix.png';
$ip_api = $settings['region-api:ip'] ?? 'ipwho.is';
$dns_api = $settings['region-api:dns'] ?? 'DNS-Google';
$regionVersion = '2.1';
$discordUrl = 'https://dsc.gg/mrlg';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Panel Settings</h1>
        <p class="page-description">Configure general metadata, brand customization, identity, and region detection.</p>
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

      <!-- Pterodactyl Region Settings (v2.1 Addon) -->
      <div class="card" id="region-settings" style="margin-top: 1.5rem;">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.25); display: flex; align-items: center; justify-content: center; color: #38bdf8;">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
              <h3 class="card-title" style="margin: 0; font-size: 1.05rem;">Pterodactyl Region Settings</h3>
              <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">Configure geolocation &amp; DNS resolution APIs for server location detection and flag cards.</p>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="status-badge badge-online" style="font-size: 0.75rem; padding: 0.25rem 0.65rem;">
              <span class="badge-dot"></span> v<?= e($regionVersion) ?> Active
            </span>
            <a href="<?= e($discordUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; padding: 0.3rem 0.65rem;">
              <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.894.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
              Discord Community
            </a>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem; margin-top: 1.25rem;">
          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-weight: 600; color: #fff;">API IP Connect</label>
            <select name="region_api_ip" class="form-control">
              <option value="ipwho.is" <?= $ip_api === 'ipwho.is' ? 'selected' : '' ?>>ipwho.is (Recommended - Fast &amp; Free)</option>
              <option value="ipapi.is" <?= $ip_api === 'ipapi.is' ? 'selected' : '' ?>>ipapi.is</option>
              <option value="ipapi.co" <?= $ip_api === 'ipapi.co' ? 'selected' : '' ?>>ipapi.co</option>
              <option value="geoiplookup.io" <?= $ip_api === 'geoiplookup.io' ? 'selected' : '' ?>>geoiplookup.io</option>
            </select>
            <div class="form-text" style="font-size: 0.78rem; color: var(--text-dim); margin-top: 0.35rem;">
              Primary IP Geolocation API provider used for server &amp; client location detection.
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="font-weight: 600; color: #fff;">DNS IP Connect</label>
            <select name="region_api_dns" class="form-control">
              <option value="DNS-Google" <?= $dns_api === 'DNS-Google' ? 'selected' : '' ?>>DNS Google (dns.google)</option>
              <option value="DNS-Cloudflare" <?= $dns_api === 'DNS-Cloudflare' ? 'selected' : '' ?>>DNS Cloudflare (cloudflare-dns.com)</option>
            </select>
            <div class="form-text" style="font-size: 0.78rem; color: var(--text-dim); margin-top: 0.35rem;">
              DNS over HTTPS (DoH) resolver used for node hostnames and FQDNs.
            </div>
          </div>
        </div>

        <!-- Addon Information & Preview Flags Banner -->
        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 8px; padding: 1rem 1.25rem; margin-top: 1.25rem; display: flex; flex-direction: column; gap: 0.75rem;">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 8px;">
              <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 8px #10b981;"></span>
              <span style="font-size: 0.85rem; color: #e2e8f0; font-weight: 500;">
                Region Module Active &bull; 90 SVG country flags installed
              </span>
            </div>
            <span style="font-size: 0.78rem; color: var(--text-muted);">
              Integrated in Server Console &bull; Instant Client Caching
            </span>
          </div>

          <!-- Sample Flags preview bar -->
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding-top: 0.6rem; border-top: 1px dashed rgba(255, 255, 255, 0.08);">
            <span style="font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px;">Sample Flags:</span>
            <?php
              $sampleFlags = ['US' => 'United States', 'DE' => 'Germany', 'GB' => 'United Kingdom', 'IN' => 'India', 'FR' => 'France', 'CA' => 'Canada', 'SG' => 'Singapore', 'JP' => 'Japan', 'NL' => 'Netherlands', 'BR' => 'Brazil', 'AU' => 'Australia'];
              foreach ($sampleFlags as $code => $country):
            ?>
              <span title="<?= e($country) ?> (<?= e($code) ?>)" style="display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; padding: 2px 7px; font-size: 0.72rem; color: #cbd5e1;">
                <img src="/assets/regions/<?= e($code) ?>.svg" alt="<?= e($code) ?>" style="width: 14px; height: 10px; object-fit: cover; border-radius: 1px;">
                <?= e($code) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div style="margin-top: 1.5rem; display: flex; align-items: center; justify-content: flex-start; gap: 1rem;">
        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem;">
          Save All Settings
        </button>
      </div>
    </form>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
