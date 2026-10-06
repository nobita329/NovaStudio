<?php
$title = 'Arix Page Editor';
$breadcrumb = 'Admin / Appearance / Pages';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$pagesConfig = $theme['pages'] ?? [];
$availablePages = $availablePages ?? [];
?>
<style>
.page-editor-card {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  transition: var(--transition);
}
.page-editor-card:hover {
  border-color: rgba(74, 53, 207, 0.4);
}
.category-tab-btn {
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  color: rgb(var(--gray300));
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
}
.category-tab-btn:hover, .category-tab-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: #ffffff;
}
.switch-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: rgb(var(--gray200));
  cursor: pointer;
}

/* UI/UX Pro Max Accessibility & Interactions */
button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible, a:focus-visible {
  outline: 2px solid var(--primary-border, #7562f0) !important;
  outline-offset: 2px !important;
}

@media (prefers-reduced-motion: reduce) {
  *, ::before, ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Page-Level Customization</h1>
        <p class="page-description">Configure titles, descriptions, layout visibility, container width, and custom CSS/JS for each route in the panel.</p>
      </div>

      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/admin/appearance" class="btn btn-secondary btn-sm">← Back to Themes</a>
        <a href="/admin/appearance/arix/editor" class="btn btn-secondary btn-sm">Theme Editor</a>
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('modal-reset-pages').style.display='flex'">Reset Defaults</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('pages-form').submit()">Save All Pages</button>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.5rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Category Filter Tabs -->
    <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
      <button type="button" class="category-tab-btn active" onclick="filterCategory('all', this)">All Pages (<?= count($availablePages) ?>)</button>
      <button type="button" class="category-tab-btn" onclick="filterCategory('User', this)">User Pages</button>
      <button type="button" class="category-tab-btn" onclick="filterCategory('Server', this)">Server Management</button>
      <button type="button" class="category-tab-btn" onclick="filterCategory('Auth', this)">Authentication</button>
      <button type="button" class="category-tab-btn" onclick="filterCategory('Account', this)">Account</button>
      <button type="button" class="category-tab-btn" onclick="filterCategory('Admin', this)">Admin Area</button>
    </div>

    <form id="pages-form" action="/admin/appearance/arix/pages/save" method="POST">
      <div id="pages-container">
        <?php foreach ($availablePages as $p): 
          $path = $p['path'];
          $cfg = $pagesConfig[$path] ?? [
            'title' => ucwords(str_replace(['/', '-', ':id'], [' ', ' ', 'Server'], $path)),
            'subtitle' => 'Managed panel page',
            'enabled' => true,
            'show_header' => true,
            'show_sidebar' => true,
            'container_width' => 1400,
            'bg_style' => 'default',
            'custom_css' => '',
            'custom_js' => ''
          ];
          $isEnabled = !isset($cfg['enabled']) || $cfg['enabled'];
          $showHeader = !isset($cfg['show_header']) || $cfg['show_header'];
          $showSidebar = !isset($cfg['show_sidebar']) || $cfg['show_sidebar'];
          $bgStyle = $cfg['bg_style'] ?? 'default';
          $cWidth = $cfg['container_width'] ?? 1400;
          $customCss = $cfg['custom_css'] ?? '';
          $customJs = $cfg['custom_js'] ?? '';
        ?>
          <div class="page-editor-card page-card-item" data-category="<?= e($p['category']) ?>">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(var(--gray600), 0.3); padding-bottom: 0.75rem;">
              <div>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                  <span style="font-size: 1.05rem; font-weight: 700; color: #ffffff;"><?= e($cfg['title'] ?? $path) ?></span>
                  <code style="font-size: 0.75rem; background: rgb(var(--gray900)); padding: 0.2rem 0.5rem; border-radius: 4px; color: var(--primary-border);"><?= e($path) ?></code>
                  <span style="font-size: 0.7rem; background: rgb(var(--gray700)); padding: 0.15rem 0.45rem; border-radius: 4px; color: rgb(var(--gray300));"><?= e($p['category']) ?></span>
                </div>
                <div style="font-size: 0.8rem; color: rgb(var(--gray400)); margin-top: 0.25rem;">
                  <?= e($cfg['subtitle'] ?? '') ?>
                </div>
              </div>

              <div>
                <label class="switch-label">
                  <input type="checkbox" name="pages[<?= e($path) ?>][enabled]" value="1" <?= $isEnabled ? 'checked' : '' ?>>
                  <span>Custom Theme Enabled</span>
                </label>
              </div>
            </div>

            <!-- Page Configuration Fields -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
              <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="pages[<?= e($path) ?>][title]" class="form-control" value="<?= e($cfg['title'] ?? '') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Page Description / Subtitle</label>
                <input type="text" name="pages[<?= e($path) ?>][subtitle]" class="form-control" value="<?= e($cfg['subtitle'] ?? '') ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Background Style</label>
                <select name="pages[<?= e($path) ?>][bg_style]" class="form-control">
                  <option value="default" <?= $bgStyle === 'default' ? 'selected' : '' ?>>Arix Standard (#040519)</option>
                  <option value="surface" <?= $bgStyle === 'surface' ? 'selected' : '' ?>>Elevated Surface (#0B0D2A)</option>
                  <option value="oled" <?= $bgStyle === 'oled' ? 'selected' : '' ?>>OLED Pure Black (#000000)</option>
                  <option value="gradient" <?= $bgStyle === 'gradient' ? 'selected' : '' ?>>Gradient Accent</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Container Max Width (px)</label>
                <input type="number" name="pages[<?= e($path) ?>][container_width]" class="form-control" value="<?= e($cWidth) ?>" min="480" max="1920">
              </div>
            </div>

            <div style="display: flex; gap: 1.5rem; margin-bottom: 1rem; flex-wrap: wrap;">
              <label class="switch-label">
                <input type="checkbox" name="pages[<?= e($path) ?>][show_header]" value="1" <?= $showHeader ? 'checked' : '' ?>>
                <span>Show Header / Topbar</span>
              </label>

              <label class="switch-label">
                <input type="checkbox" name="pages[<?= e($path) ?>][show_sidebar]" value="1" <?= $showSidebar ? 'checked' : '' ?>>
                <span>Show Sidebar</span>
              </label>
            </div>

            <!-- Expandable Advanced Code (Custom CSS / JS) -->
            <details style="background: rgb(var(--gray900)); border-radius: 6px; padding: 0.5rem 0.75rem; border: 1px solid rgba(var(--gray600), 0.2);">
              <summary style="font-size: 0.8rem; font-weight: 600; color: rgb(var(--gray300)); cursor: pointer;">Page-Specific CSS & JS Overrides</summary>
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.75rem;">
                <div class="form-group">
                  <label class="form-label" style="font-size: 0.75rem;">Page Custom CSS</label>
                  <textarea name="pages[<?= e($path) ?>][custom_css]" class="form-control" rows="3" style="font-family: var(--font-mono); font-size: 0.75rem;" placeholder="/* CSS scoped to <?= e($path) ?> */"><?= e($customCss) ?></textarea>
                </div>
                <div class="form-group">
                  <label class="form-label" style="font-size: 0.75rem;">Page Custom JS</label>
                  <textarea name="pages[<?= e($path) ?>][custom_js]" class="form-control" rows="3" style="font-family: var(--font-mono); font-size: 0.75rem;" placeholder="// JavaScript executed on <?= e($path) ?>"><?= e($customJs) ?></textarea>
                </div>
              </div>
            </details>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="position: sticky; bottom: 20px; background: rgb(var(--gray800)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 50;">
        <span style="font-size: 0.85rem; color: rgb(var(--gray300));">Configuring <?= count($availablePages) ?> detected panel routes</span>
        <div style="display: flex; gap: 0.75rem;">
          <a href="/admin/appearance" class="btn btn-secondary btn-sm">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm">Save All Changes</button>
        </div>
      </div>
    </form>
  </main>
</div>

<!-- Modal: Reset Pages -->
<div id="modal-reset-pages" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
  <div style="background: rgb(var(--gray700)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); max-width: 480px; width: 100%; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
    <h3 style="font-size: 1.25rem; color: #ffffff; margin-bottom: 0.75rem;">Reset Page Customizations?</h3>
    <p style="font-size: 0.875rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.5rem;">
      Are you sure you want to restore all route titles, subtitles, visibility toggles, and scripts to original defaults?
    </p>
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reset-pages').style.display='none'">Cancel</button>
      <form action="/admin/appearance/arix/pages/reset" method="POST" style="margin: 0;">
        <button type="submit" class="btn btn-danger">Confirm Reset</button>
      </form>
    </div>
  </div>
</div>

<script>
function filterCategory(category, btn) {
  document.querySelectorAll('.category-tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const cards = document.querySelectorAll('.page-card-item');
  cards.forEach(card => {
    if (category === 'all' || card.getAttribute('data-category') === category) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// UI/UX Pro Max: Keyboard Escape to close modal
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const m = document.getElementById('modal-reset-pages');
    if (m) m.style.display = 'none';
  }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
