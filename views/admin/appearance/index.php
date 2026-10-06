<?php
$title = 'Appearance & Themes';
$breadcrumb = 'Admin / Appearance';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$arixTheme = null;
$defaultTheme = null;
if (!empty($themes) && is_array($themes)) {
    foreach ($themes as $t) {
        if ($t['theme_key'] === 'arix') $arixTheme = $t;
        if ($t['theme_key'] === 'default') $defaultTheme = $t;
    }
}
$isArixActive = $arixTheme && !empty($arixTheme['is_active']);
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Themes & Appearance</h1>
        <p class="page-description">Manage installed panel themes, customize visual parameters with Arix Theme Editor, and configure layouts.</p>
      </div>

      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/admin/appearance/arix" class="btn btn-primary btn-sm">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          Open Arix Editor
        </a>
        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-import-theme').style.display='flex'">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
          Import Theme Config
        </button>
        <a href="/admin/appearance/arix/export" class="btn btn-secondary btn-sm">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
          Export Arix Config
        </a>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.5rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Themes Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.5rem; margin-top: 1rem;">

      <!-- Arix Theme v2.1.3 Card -->
      <div class="card" style="position: relative; overflow: hidden; border: <?= $isArixActive ? '2px solid var(--primary)' : '1px solid var(--border-color)' ?>; display: flex; flex-direction: column;">
        <?php if ($isArixActive): ?>
          <div style="position: absolute; top: 12px; right: 12px; z-index: 10;">
            <span class="status-badge badge-online"><span class="badge-dot"></span>Active Theme</span>
          </div>
        <?php endif; ?>

        <!-- Theme Header Artwork -->
        <div style="height: 140px; background: linear-gradient(135deg, #040519 0%, #1D1D37 50%, #4A35CF 100%); border-radius: 6px; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
          <div style="position: absolute; inset: 0; opacity: 0.15; background: radial-gradient(circle at 70% 30%, #7562f0, transparent 60%);"></div>
          <img src="/arix/Arix.png" alt="Arix Theme Logo" style="height: 48px; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.6)); z-index: 2;">
          <span style="position: absolute; bottom: 10px; right: 10px; font-size: 0.7rem; background: rgba(0,0,0,0.5); padding: 2px 8px; border-radius: 4px; color: #fff; font-weight: 600;">v2.1.3 OFFICIAL</span>
        </div>

        <div style="flex: 1;">
          <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #ffffff;">Arix Theme</h2>
            <span style="font-size: 0.775rem; color: rgb(var(--gray300)); font-family: var(--font-mono);">v2.1.3</span>
          </div>
          <p style="font-size: 0.85rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.25rem;">
            Official authentic Arix v2.1.3 theme integration. Complete with floating sidebar, tri-mode color engine, sound feedback, live telemetry, and visual Arix Editor.
          </p>

          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; font-size: 0.75rem;">
            <span style="background: rgb(var(--gray800)); border: 1px solid var(--border-color); padding: 0.2rem 0.6rem; border-radius: 4px; color: var(--text-dim);">By Arix Team</span>
            <span style="background: rgb(var(--gray800)); border: 1px solid var(--border-color); padding: 0.2rem 0.6rem; border-radius: 4px; color: var(--text-dim);">Dark • Light • OLED</span>
            <span style="background: rgb(var(--gray800)); border: 1px solid var(--border-color); padding: 0.2rem 0.6rem; border-radius: 4px; color: var(--text-dim);">Visual Theme Editor</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; flex-direction: column; gap: 0.5rem; border-top: 1px solid rgba(var(--gray600), 0.5); padding-top: 1.25rem; margin-top: auto;">
          <div style="display: flex; gap: 0.5rem;">
            <a href="/admin/appearance/arix/editor" class="btn btn-primary" style="flex: 1; text-align: center;">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
              Arix Theme Editor
            </a>
            <?php if (!$isArixActive): ?>
              <form action="/admin/appearance/activate" method="POST" style="margin: 0;">
                <input type="hidden" name="theme_key" value="arix">
                <button type="submit" class="btn btn-secondary">Activate</button>
              </form>
            <?php else: ?>
              <form action="/admin/appearance/disable" method="POST" style="margin: 0;">
                <input type="hidden" name="theme_key" value="arix">
                <button type="submit" class="btn btn-secondary" title="Switch back to default theme">Deactivate</button>
              </form>
            <?php endif; ?>
          </div>

          <div style="display: flex; gap: 0.5rem;">
            <a href="/admin/appearance/arix/pages" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center;">
              Pages Editor
            </a>
            <a href="/admin/appearance/arix/components" class="btn btn-secondary btn-sm" style="flex: 1; text-align: center;">
              Components Editor
            </a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-reset-theme').style.display='flex'" style="color: var(--accent-danger);" title="Restore Arix v2.1.3 Defaults">
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Nova Default Fallback Theme Card -->
      <div class="card" style="position: relative; overflow: hidden; border: <?= !$isArixActive ? '2px solid var(--primary)' : '1px solid var(--border-color)' ?>; display: flex; flex-direction: column;">
        <?php if (!$isArixActive): ?>
          <div style="position: absolute; top: 12px; right: 12px; z-index: 10;">
            <span class="status-badge badge-online"><span class="badge-dot"></span>Active Theme</span>
          </div>
        <?php endif; ?>

        <div style="height: 140px; background: linear-gradient(135deg, #090d16 0%, #151e32 100%); border-radius: 6px; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: center; position: relative;">
          <span style="font-size: 1.5rem; font-weight: 800; letter-spacing: 0.1em; color: #38bdf8;">NOVA</span>
        </div>

        <div style="flex: 1;">
          <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.35rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #ffffff;">Nova Default</h2>
            <span style="font-size: 0.775rem; color: rgb(var(--gray300)); font-family: var(--font-mono);">v1.0.0</span>
          </div>
          <p style="font-size: 0.85rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.25rem;">
            Default baseline interface for the panel. Minimalist design for testing and standard installations.
          </p>
        </div>

        <div style="border-top: 1px solid rgba(var(--gray600), 0.5); padding-top: 1.25rem; margin-top: auto;">
          <?php if ($isArixActive): ?>
            <form action="/admin/appearance/activate" method="POST" style="margin: 0;">
              <input type="hidden" name="theme_key" value="default">
              <button type="submit" class="btn btn-secondary" style="width: 100%;">Switch to Default</button>
            </form>
          <?php else: ?>
            <button type="button" class="btn btn-secondary" style="width: 100%; opacity: 0.6; cursor: default;" disabled>Currently Active</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Reset Arix Theme -->
<div id="modal-reset-theme" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
  <div style="background: rgb(var(--gray700)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); max-width: 480px; width: 100%; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
    <h3 style="font-size: 1.25rem; color: #ffffff; margin-bottom: 0.75rem;">Reset Arix Theme?</h3>
    <p style="font-size: 0.875rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.5rem;">
      Are you sure you want to restore the original <strong>Arix Theme v2.1.3 defaults</strong>? Any custom colors, borders, or layout settings will be reset.
    </p>
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reset-theme').style.display='none'">Cancel</button>
      <form action="/admin/appearance/arix/reset" method="POST" style="margin: 0;">
        <button type="submit" class="btn btn-danger">Confirm Reset</button>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Import Theme Config -->
<div id="modal-import-theme" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
  <div style="background: rgb(var(--gray700)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); max-width: 500px; width: 100%; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
    <h3 style="font-size: 1.25rem; color: #ffffff; margin-bottom: 0.75rem;">Import Arix Theme Configuration</h3>
    <p style="font-size: 0.875rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.5rem;">
      Upload an <code>arix-theme-config.json</code> file to restore or apply custom settings.
    </p>
    <form action="/admin/appearance/arix/import" method="POST" enctype="multipart/form-data">
      <div class="form-group" style="margin-bottom: 1.5rem;">
        <label class="form-label">Theme Config File (.json)</label>
        <input type="file" name="config_file" class="form-control" accept=".json,application/json" required>
      </div>
      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-import-theme').style.display='none'">Cancel</button>
        <button type="submit" class="btn btn-primary">Import & Apply</button>
      </div>
    </form>
  </div>
</div>

<script>
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const m1 = document.getElementById('modal-reset-theme');
    const m2 = document.getElementById('modal-import-theme');
    if (m1) m1.style.display = 'none';
    if (m2) m2.style.display = 'none';
  }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
