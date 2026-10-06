<?php
$title = 'Arix Theme Editor';
$breadcrumb = 'Admin / Appearance / Arix Theme Editor';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$config = $theme['config'] ?? [];
$gen = $config['general'] ?? [];
$col = $config['colors'] ?? [];
$typo = $config['typography'] ?? [];
$sb = $config['sidebar'] ?? [];
$hd = $config['header'] ?? [];
$cd = $config['cards'] ?? [];
$btn = $config['buttons'] ?? [];
$lay = $config['layout'] ?? [];
$pages = $theme['pages'] ?? [];
$components = $theme['components'] ?? [];

$isArixActive = !empty($theme['is_active']);
?>
<style>
/* Master Editor Layout */
.arix-editor-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-top: 0.5rem;
}

.arix-editor-container {
  display: grid;
  grid-template-columns: 240px 1fr 1.1fr;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 1400px) {
  .arix-editor-container {
    grid-template-columns: 220px 1.2fr 1fr;
  }
}

@media (max-width: 1100px) {
  .arix-editor-container {
    grid-template-columns: 1fr;
  }
}

/* Vertical Tab Navigation (Left Column) */
.editor-nav-column {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  position: sticky;
  top: 80px;
}

.editor-vtab-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-radius: var(--radiusInput);
  color: rgb(var(--gray300));
  font-weight: 600;
  font-size: 0.85rem;
  text-align: left;
  cursor: pointer;
  transition: var(--transition);
}

.editor-vtab-btn:hover {
  background: rgba(var(--gray700), 0.7);
  color: #ffffff;
}

.editor-vtab-btn.active {
  background: var(--primary);
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(74, 53, 207, 0.4);
}

.editor-vtab-btn svg {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

/* Middle Form Column */
.editor-form-column {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.editor-pane {
  display: none;
}
.editor-pane.active {
  display: block;
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}

.pane-section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
  margin-bottom: 0.35rem;
}
.pane-section-desc {
  font-size: 0.825rem;
  color: rgb(var(--gray300));
  margin-bottom: 1.5rem;
}

/* Color Row Control */
.color-field-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.65rem 0.75rem;
  background: rgb(var(--gray900));
  border: 1px solid rgba(var(--gray600), 0.3);
  border-radius: var(--radiusInput);
  margin-bottom: 0.65rem;
}

.color-field-info {
  flex: 1;
}
.color-field-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #ffffff;
  display: block;
}
.color-field-desc {
  font-size: 0.75rem;
  color: rgb(var(--gray400));
}

.color-field-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.color-picker-input {
  width: 36px;
  height: 36px;
  padding: 0;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  cursor: pointer;
  background: transparent;
  flex-shrink: 0;
}

.color-hex-text {
  width: 90px;
  font-family: var(--font-mono);
  font-size: 0.8rem;
  padding: 0.35rem 0.5rem;
}

.btn-reset-param {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: transparent;
  border: 1px solid rgba(var(--gray600), 0.4);
  color: rgb(var(--gray300));
  padding: 0.35rem 0.55rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
}
.btn-reset-param:hover {
  background: rgba(var(--gray700), 0.9);
  color: #ffffff;
  border-color: rgba(var(--gray500), 0.6);
}

/* UI/UX Pro Max Rule 1: Focus rings across all interactive controls */
button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible, a:focus-visible {
  outline: 2px solid var(--primary-border, #7562f0) !important;
  outline-offset: 2px !important;
}

/* UI/UX Pro Max Rule 2: Respect prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
  *, ::before, ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}

/* Right Live Preview Column */
.editor-preview-column {
  position: sticky;
  top: 80px;
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.25rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

.preview-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  border-bottom: 1px solid rgba(var(--gray600), 0.3);
  padding-bottom: 0.75rem;
}

.preview-devices {
  display: flex;
  gap: 0.25rem;
  background: rgb(var(--gray900));
  padding: 0.2rem;
  border-radius: 6px;
  border: 1px solid var(--border-color);
}

.preview-device-btn {
  padding: 0.3rem 0.65rem;
  background: transparent;
  border: none;
  border-radius: 4px;
  color: rgb(var(--gray300));
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
}
.preview-device-btn.active {
  background: var(--primary);
  color: #ffffff;
}

/* Live Mockup Screen */
.preview-viewport {
  width: 100%;
  transition: width 0.3s ease;
  margin: 0 auto;
}

.preview-viewport.mode-tablet {
  max-width: 480px;
}
.preview-viewport.mode-mobile {
  max-width: 340px;
}

.preview-screen {
  background: var(--preview-bg, #040519);
  border: 1px solid var(--preview-border, #42425B);
  border-radius: var(--preview-radius, 10px);
  padding: 1rem;
  min-height: 480px;
  font-family: var(--preview-font, 'Inter', sans-serif);
  color: var(--preview-text, #F4F4F4);
  box-shadow: inset 0 0 20px rgba(0,0,0,0.5);
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  overflow: hidden;
}

.preview-mock-header {
  background: var(--preview-header-bg, rgba(8, 9, 34, 0.8));
  border-bottom: 1px solid var(--preview-border, #42425B);
  height: var(--preview-header-height, 46px);
  padding: 0 0.85rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 6px;
}

.preview-mock-sidebar-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.45rem 0.75rem;
  border-radius: 6px;
  font-size: 0.775rem;
  font-weight: 600;
  color: var(--preview-text, #F4F4F4);
}
.preview-mock-sidebar-item.active {
  background: var(--preview-primary, #4A35CF);
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(74, 53, 207, 0.4);
}

.preview-mock-card {
  background: var(--preview-card, #1D1D37);
  border: 1px solid var(--preview-border, #42425B);
  border-radius: var(--preview-radius, 10px);
  padding: 0.85rem;
}

.preview-mock-server-row {
  background: var(--preview-card, #1D1D37);
  border: 1px solid var(--preview-border, #42425B);
  border-radius: var(--preview-radius, 10px);
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.preview-status-bar {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: #22c55e;
}

/* Code Editor Fake Display */
.code-editor-box {
  background: rgb(var(--gray900));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  padding: 0.75rem;
  font-family: var(--font-mono);
  font-size: 0.8rem;
  color: #38bdf8;
  width: 100%;
}

/* Status Indicator */
.save-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.65rem;
  border-radius: 4px;
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
  border: 1px solid rgba(34, 197, 94, 0.3);
}
.save-status-pill.unsaved {
  background: rgba(234, 179, 8, 0.15);
  color: #facc15;
  border-color: rgba(234, 179, 8, 0.3);
}
.save-status-pill.saving {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  border-color: rgba(59, 130, 246, 0.3);
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area">
    
    <!-- Top Action Bar -->
    <div class="page-header" style="margin-bottom: 1rem;">
      <div>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
          <h1 class="page-title" style="margin-bottom: 0;">Arix Theme Editor</h1>
          <span class="btn-admin-area arix" style="font-size: 0.7rem;">v2.1.3 OFFICIAL</span>
          <span id="save-status-badge" class="save-status-pill">Saved</span>
        </div>
        <p class="page-description" style="margin-top: 0.25rem;">
          Configure visual design tokens, sidebar, header, components, and responsive behavior with live real-time feedback.
        </p>
      </div>

      <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <a href="/admin/appearance" class="btn btn-secondary btn-sm">← Appearance</a>
        <?php if (!$isArixActive): ?>
          <form action="/admin/appearance/activate" method="POST" style="margin: 0;">
            <input type="hidden" name="theme_key" value="arix">
            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--primary-border);">Activate Arix</button>
          </form>
        <?php else: ?>
          <form action="/admin/appearance/disable" method="POST" style="margin: 0;">
            <input type="hidden" name="theme_key" value="arix">
            <button type="submit" class="btn btn-secondary btn-sm">Deactivate Arix</button>
          </form>
        <?php endif; ?>
        <a href="/admin/appearance/arix/export" class="btn btn-secondary btn-sm" title="Export current config">Export JSON</a>
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('modal-reset-theme').style.display='flex'">Reset Defaults</button>
        <button type="button" id="btn-save-master" class="btn btn-primary btn-sm" onclick="saveArixTheme()" style="min-width: 110px;">SAVE CHANGES</button>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Master 3-Column Editor Layout -->
    <div class="arix-editor-wrapper">
      <form id="arix-editor-form" action="/admin/appearance/arix/save" method="POST">
        <div class="arix-editor-container">

          <!-- 1. LEFT COLUMN: VERTICAL TABS -->
          <div class="editor-nav-column">
            <div style="font-size: 0.7rem; font-weight: 700; color: rgb(var(--gray400)); text-transform: uppercase; padding: 0.25rem 0.5rem; letter-spacing: 0.05em;">Sections</div>
            <button type="button" class="editor-vtab-btn active" onclick="switchTab('general', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
              GENERAL
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('colors', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 5 5 0 013-4.5 4 4 0 011.5-3.5 4 4 0 014-4 4 4 0 014 4 4 4 0 011.5 3.5 5 5 0 013 4.5 4 4 0 01-4 4H7z"></path></svg>
              COLORS
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('typography', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              TYPOGRAPHY
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('sidebar', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"></path></svg>
              SIDEBAR
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('header', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h14a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"></path></svg>
              HEADER
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('cards', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              CARDS
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('buttons', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
              BUTTONS
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('layout', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
              LAYOUT
            </button>
            <a href="/admin/appearance/arix/pages" class="editor-vtab-btn" style="text-decoration: none;">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              PAGES
            </a>
            <a href="/admin/appearance/arix/components" class="editor-vtab-btn" style="text-decoration: none;">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
              COMPONENTS
            </a>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('custom_css', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
              CUSTOM CSS
            </button>
            <button type="button" class="editor-vtab-btn" onclick="switchTab('custom_js', this)">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              CUSTOM JS
            </button>
          </div>

          <!-- 2. MIDDLE COLUMN: TAB PANES -->
          <div class="editor-form-column">

            <!-- TAB 1: GENERAL -->
            <div id="tab-general" class="editor-pane active">
              <h2 class="pane-section-title">General Brand & Identity</h2>
              <p class="pane-section-desc">Manage your panel name, logo assets, favicon, and login display properties.</p>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Panel Name</label>
                <input type="text" name="general[panel_name]" class="form-control" value="<?= e($gen['panel_name'] ?? 'Nova') ?>" oninput="markUnsaved(); updateLivePreview()">
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Theme Name</label>
                  <input type="text" name="general[theme_name]" class="form-control" value="<?= e($gen['theme_name'] ?? 'Arix Theme') ?>" oninput="markUnsaved()">
                </div>
                <div class="form-group">
                  <label class="form-label">Theme Version</label>
                  <input type="text" name="general[theme_version]" class="form-control" value="<?= e($gen['theme_version'] ?? '2.1.3') ?>" readonly>
                </div>
              </div>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Panel Logo Path / URL</label>
                <div style="display: flex; gap: 0.5rem;">
                  <input type="text" name="general[logo]" id="input-logo" class="form-control" value="<?= e($gen['logo'] ?? '/arix/Arix.png') ?>" oninput="markUnsaved(); updateLivePreview()">
                  <button type="button" class="btn-reset-param" onclick="resetParam('input-logo', '/arix/Arix.png')" title="Reset to Arix default" aria-label="Reset logo to default">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    <span>Reset</span>
                  </button>
                </div>
              </div>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Favicon Path / URL</label>
                <div style="display: flex; gap: 0.5rem;">
                  <input type="text" name="general[favicon]" id="input-favicon" class="form-control" value="<?= e($gen['favicon'] ?? '/arix/Arix.png') ?>" oninput="markUnsaved()">
                  <button type="button" class="btn-reset-param" onclick="resetParam('input-favicon', '/arix/Arix.png')" title="Reset to Arix default" aria-label="Reset favicon to default">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    <span>Reset</span>
                  </button>
                </div>
              </div>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Login Screen Background Image</label>
                <div style="display: flex; gap: 0.5rem;">
                  <input type="text" name="general[login_background]" id="input-login-bg" class="form-control" value="<?= e($gen['login_background'] ?? '/arix/background-login.png') ?>" oninput="markUnsaved()">
                  <button type="button" class="btn-reset-param" onclick="resetParam('input-login-bg', '/arix/background-login.png')" title="Reset to Arix default" aria-label="Reset login background to default">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                    <span>Reset</span>
                  </button>
                </div>
              </div>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Footer Attribution Text</label>
                <input type="text" name="general[footer_text]" class="form-control" value="<?= e($gen['footer_text'] ?? 'Powered by Nova & Arix Theme v2.1.3') ?>" oninput="markUnsaved()">
              </div>

              <div class="form-group">
                <label class="form-label">Default Theme Mode</label>
                <select name="general[default_mode]" class="form-control" onchange="markUnsaved()">
                  <option value="dark" <?= ($gen['default_mode'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark Mode (Signature)</option>
                  <option value="oled" <?= ($gen['default_mode'] ?? '') === 'oled' ? 'selected' : '' ?>>OLED Pure Black</option>
                  <option value="light" <?= ($gen['default_mode'] ?? '') === 'light' ? 'selected' : '' ?>>Light Mode</option>
                </select>
              </div>
            </div>

            <!-- TAB 2: COLORS -->
            <div id="tab-colors" class="editor-pane">
              <h2 class="pane-section-title">Theme Palette & Tokens</h2>
              <p class="pane-section-desc">Fine-tune authentic Arix v2.1.3 hex colors. Each variable updates live in the preview screen.</p>

              <?php
              $colorDefs = [
                ['primary', 'Primary Accent', 'Signature brand button & active indicator', $col['primary'] ?? '#4A35CF', '#4A35CF'],
                ['secondary', 'Secondary Background', 'Secondary buttons & neutral boxes', $col['secondary'] ?? '#2b2b48', '#2b2b48'],
                ['accent', 'Border Accent', 'High-contrast focused border highlight', $col['accent'] ?? '#7562f0', '#7562f0'],
                ['background', 'Main Background', 'Base viewport body background', $col['background'] ?? '#040519', '#040519'],
                ['surface', 'Surface Color', 'Sidebar dock & sub-containers', $col['surface'] ?? '#0B0D2A', '#0B0D2A'],
                ['surface_secondary', 'Secondary Surface', 'Elevated cards & drawer panels', $col['surface_secondary'] ?? '#15183d', '#15183d'],
                ['card', 'Card Background', 'Server rows, stat cards & table cells', $col['card'] ?? '#1D1D37', '#1D1D37'],
                ['border', 'Border Color', 'Default container & input divider stroke', $col['border'] ?? '#42425B', '#42425B'],
                ['text', 'Primary Text', 'Headings, active labels & main copy', $col['text'] ?? '#F4F4F4', '#F4F4F4'],
                ['text_muted', 'Muted Text', 'Subtitles, breadcrumbs & hints', $col['text_muted'] ?? '#8282A4', '#8282A4'],
                ['success', 'Success State', 'Online pulse, active status, badges', $col['success'] ?? '#1f8f2b', '#1f8f2b'],
                ['warning', 'Warning State', 'Suspended, restarting, cautionary notices', $col['warning'] ?? '#B45309', '#B45309'],
                ['danger', 'Danger State', 'Stopped, offline, error banners & kill', $col['danger'] ?? '#8f1f37', '#8f1f37'],
                ['info', 'Information State', 'Informational tags & update pills', $col['info'] ?? '#0284c7', '#0284c7'],
                ['sidebar', 'Sidebar Background', 'Dock island base color', $col['sidebar'] ?? '#080922', '#080922'],
                ['header', 'Header Background', 'Top bar backdrop color', $col['header'] ?? '#080922', '#080922'],
              ];

              foreach ($colorDefs as $cdItem):
                $key = $cdItem[0];
                $name = $cdItem[1];
                $desc = $cdItem[2];
                $val = $cdItem[3];
                $def = $cdItem[4];
              ?>
                <div class="color-field-row">
                  <div class="color-field-info">
                    <span class="color-field-label"><?= e($name) ?></span>
                    <span class="color-field-desc"><?= e($desc) ?></span>
                  </div>
                  <div class="color-field-controls">
                    <input type="color" id="pick-<?= $key ?>" class="color-picker-input" value="<?= e($val) ?>" oninput="syncColorInput('<?= $key ?>', this.value)" aria-label="<?= e($name) ?> Color Picker">
                    <input type="text" name="colors[<?= $key ?>]" id="hex-<?= $key ?>" class="form-control color-hex-text" value="<?= e($val) ?>" oninput="syncColorPicker('<?= $key ?>', this.value)" aria-label="<?= e($name) ?> HEX Code">
                    <button type="button" class="btn-reset-param" onclick="resetColorParam('<?= $key ?>', '<?= $def ?>')" title="Reset to Arix default" aria-label="Reset <?= e($name) ?> to default">
                      <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                      <span>Reset</span>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- TAB 3: TYPOGRAPHY -->
            <div id="tab-typography" class="editor-pane">
              <h2 class="pane-section-title">Typography & Fonts</h2>
              <p class="pane-section-desc">Manage web fonts, hierarchy scales, letter spacing, and reading weights.</p>

              <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">Font Family</label>
                <select name="typography[font_family]" id="input-font-family" class="form-control" onchange="markUnsaved(); updateLivePreview()">
                  <option value="'Inter', sans-serif" <?= strpos($typo['font_family'] ?? '', 'Inter') !== false ? 'selected' : '' ?>>Inter (Clean Modern UI)</option>
                  <option value="'IBM Plex Sans', sans-serif" <?= strpos($typo['font_family'] ?? '', 'IBM Plex') !== false ? 'selected' : '' ?>>IBM Plex Sans (Arix Signature)</option>
                  <option value="'Plus Jakarta Sans', sans-serif" <?= strpos($typo['font_family'] ?? '', 'Jakarta') !== false ? 'selected' : '' ?>>Plus Jakarta Sans</option>
                  <option value="'Poppins', sans-serif" <?= strpos($typo['font_family'] ?? '', 'Poppins') !== false ? 'selected' : '' ?>>Poppins</option>
                  <option value="system-ui, -apple-system, sans-serif" <?= strpos($typo['font_family'] ?? '', 'system-ui') !== false ? 'selected' : '' ?>>Native System Sans</option>
                </select>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Base Font Size (px)</label>
                  <input type="number" name="typography[font_size]" class="form-control" value="<?= e($typo['font_size'] ?? 14) ?>" min="12" max="18" oninput="markUnsaved()">
                </div>
                <div class="form-group">
                  <label class="form-label">Heading Font Size (px)</label>
                  <input type="number" name="typography[heading_size]" class="form-control" value="<?= e($typo['heading_size'] ?? 20) ?>" min="16" max="32" oninput="markUnsaved()">
                </div>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Font Weight</label>
                  <select name="typography[font_weight]" class="form-control" onchange="markUnsaved()">
                    <option value="400" <?= ($typo['font_weight'] ?? '') === '400' ? 'selected' : '' ?>>Regular (400)</option>
                    <option value="500" <?= ($typo['font_weight'] ?? '') === '500' ? 'selected' : '' ?>>Medium (500)</option>
                    <option value="600" <?= ($typo['font_weight'] ?? '') === '600' ? 'selected' : '' ?>>Semi-Bold (600)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Line Height</label>
                  <input type="text" name="typography[line_height]" class="form-control" value="<?= e($typo['line_height'] ?? '1.5') ?>" oninput="markUnsaved()">
                </div>
              </div>
            </div>

            <!-- TAB 4: SIDEBAR -->
            <div id="tab-sidebar" class="editor-pane">
              <h2 class="pane-section-title">Sidebar Dock</h2>
              <p class="pane-section-desc">Floating island sidebar layout, dimensions, and menu interaction tokens.</p>

              <div style="display: flex; gap: 1.5rem; margin-bottom: 1.25rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="sidebar[enabled]" value="1" <?= !isset($sb['enabled']) || !empty($sb['enabled']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">Sidebar Enabled</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="sidebar[collapsed]" value="1" <?= !empty($sb['collapsed']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">Start in Collapsed Mode</span>
                </label>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Sidebar Width (px)</label>
                  <div style="display: flex; gap: 0.5rem;">
                    <input type="number" name="sidebar[width]" id="input-sb-width" class="form-control" value="<?= e($sb['width'] ?? 260) ?>" min="180" max="380" oninput="markUnsaved(); updateLivePreview()">
                    <button type="button" class="btn-reset-param" onclick="resetParam('input-sb-width', 260)">↩</button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Border Radius (px)</label>
                  <input type="number" name="sidebar[border_radius]" class="form-control" value="<?= e($sb['border_radius'] ?? 14) ?>" min="0" max="30" oninput="markUnsaved()">
                </div>
              </div>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Menu Item Spacing (px)</label>
                  <input type="number" name="sidebar[menu_spacing]" class="form-control" value="<?= e($sb['menu_spacing'] ?? 4) ?>" min="0" max="16" oninput="markUnsaved()">
                </div>
                <div class="form-group">
                  <label class="form-label">Icon Size (px)</label>
                  <input type="number" name="sidebar[icon_size]" class="form-control" value="<?= e($sb['icon_size'] ?? 20) ?>" min="14" max="28" oninput="markUnsaved()">
                </div>
              </div>
            </div>

            <!-- TAB 5: HEADER -->
            <div id="tab-header" class="editor-pane">
              <h2 class="pane-section-title">Header / Topbar</h2>
              <p class="pane-section-desc">Top navigation bar height, glassmorphism, search, and action toggles.</p>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Header Height (px)</label>
                  <div style="display: flex; gap: 0.5rem;">
                    <input type="number" name="header[height]" id="input-hd-height" class="form-control" value="<?= e($hd['height'] ?? 64) ?>" min="48" max="96" oninput="markUnsaved(); updateLivePreview()">
                    <button type="button" class="btn-reset-param" onclick="resetParam('input-hd-height', 64)">↩</button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Shadow Elevation</label>
                  <select name="header[header_shadow]" class="form-control" onchange="markUnsaved()">
                    <option value="1" <?= !empty($hd['header_shadow']) ? 'selected' : '' ?>>Enabled</option>
                    <option value="0" <?= empty($hd['header_shadow']) ? 'selected' : '' ?>>Disabled</option>
                  </select>
                </div>
              </div>

              <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="header[search]" value="1" <?= !isset($hd['search']) || !empty($hd['search']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">Global Search Bar in Header</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="header[notifications]" value="1" <?= !isset($hd['notifications']) || !empty($hd['notifications']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">Notification Bell Icon</span>
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="header[user_menu]" value="1" <?= !isset($hd['user_menu']) || !empty($hd['user_menu']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">User Profile Dropdown</span>
                </label>
              </div>
            </div>

            <!-- TAB 6: CARDS -->
            <div id="tab-cards" class="editor-pane">
              <h2 class="pane-section-title">Cards & Containers</h2>
              <p class="pane-section-desc">Corner radius, padding, borders, and hover lift effects for card boxes.</p>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Border Radius Box (px)</label>
                  <div style="display: flex; gap: 0.5rem;">
                    <input type="number" name="cards[radius]" id="input-cd-radius" class="form-control" value="<?= e($cd['radius'] ?? 10) ?>" min="0" max="30" oninput="markUnsaved(); updateLivePreview()">
                    <button type="button" class="btn-reset-param" onclick="resetParam('input-cd-radius', 10)">↩</button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Inner Padding (px)</label>
                  <input type="number" name="cards[padding]" class="form-control" value="<?= e($cd['padding'] ?? 20) ?>" min="8" max="40" oninput="markUnsaved()">
                </div>
              </div>

              <div style="display: flex; gap: 1.5rem; margin-top: 1rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                  <input type="checkbox" name="cards[hover_effect]" value="1" <?= !empty($cd['hover_effect']) ? 'checked' : '' ?> onchange="markUnsaved()">
                  <span style="font-size: 0.85rem; color: #fff;">Subtle Hover Lift Animation</span>
                </label>
              </div>
            </div>

            <!-- TAB 7: BUTTONS -->
            <div id="tab-buttons" class="editor-pane">
              <h2 class="pane-section-title">Buttons & Interactive Triggers</h2>
              <p class="pane-section-desc">Corner radius, height, hover transitions, and action button borders.</p>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Button Radius (px)</label>
                  <div style="display: flex; gap: 0.5rem;">
                    <input type="number" name="buttons[radius]" id="input-btn-radius" class="form-control" value="<?= e($btn['radius'] ?? 7) ?>" min="0" max="25" oninput="markUnsaved(); updateLivePreview()">
                    <button type="button" class="btn-reset-param" onclick="resetParam('input-btn-radius', 7)">↩</button>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Button Height (px)</label>
                  <input type="number" name="buttons[height]" class="form-control" value="<?= e($btn['height'] ?? 38) ?>" min="28" max="52" oninput="markUnsaved()">
                </div>
              </div>
            </div>

            <!-- TAB 8: LAYOUT -->
            <div id="tab-layout" class="editor-pane">
              <h2 class="pane-section-title">Layout & Grid Spacing</h2>
              <p class="pane-section-desc">Container max-width, page margins, and server grid density.</p>

              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                  <label class="form-label">Container Max Width (px)</label>
                  <input type="number" name="layout[container_width]" class="form-control" value="<?= e($lay['container_width'] ?? 1400) ?>" min="1000" max="1920" oninput="markUnsaved()">
                </div>
                <div class="form-group">
                  <label class="form-label">Page Padding (px)</label>
                  <input type="number" name="layout[page_spacing]" class="form-control" value="<?= e($lay['page_spacing'] ?? 24) ?>" min="12" max="48" oninput="markUnsaved()">
                </div>
              </div>
            </div>

            <!-- TAB 9: CUSTOM CSS -->
            <div id="tab-custom_css" class="editor-pane">
              <h2 class="pane-section-title">Custom CSS Overrides</h2>
              <p class="pane-section-desc">Inject scoped CSS stylesheets loaded cleanly into the active Arix theme.</p>

              <div class="form-group">
                <textarea name="general[custom_css]" id="input-custom-css" class="code-editor-box" rows="12" placeholder="/* Custom Arix theme CSS rules */" oninput="markUnsaved()"><?= e($gen['custom_css'] ?? '') ?></textarea>
              </div>
            </div>

            <!-- TAB 10: CUSTOM JS -->
            <div id="tab-custom_js" class="editor-pane">
              <h2 class="pane-section-title">Custom Frontend JavaScript</h2>
              <p class="pane-section-desc">Client-side scripts for analytics, widgets, or interface hooks.</p>

              <div class="alert alert-warning" style="margin-bottom: 1rem; font-size: 0.8rem;">
                <strong>Notice:</strong> Custom scripts execute inside user sessions. Do not inject untrusted external code.
              </div>

              <div class="form-group">
                <textarea name="general[custom_js]" id="input-custom-js" class="code-editor-box" rows="10" placeholder="// Custom JavaScript hooks" oninput="markUnsaved()"><?= e($gen['custom_js'] ?? '') ?></textarea>
              </div>
            </div>

          </div>

          <!-- 3. RIGHT COLUMN: LIVE REAL-TIME PREVIEW -->
          <div class="editor-preview-column">
            <div class="preview-toolbar">
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.85rem; font-weight: 700; color: #fff;">LIVE PREVIEW</span>
                <span style="font-size: 0.7rem; color: #4ade80; background: rgba(34, 197, 94, 0.15); padding: 2px 6px; border-radius: 4px;">Real-time</span>
              </div>

              <!-- Device Switcher -->
              <div class="preview-devices">
                <button type="button" class="preview-device-btn active" onclick="setPreviewDevice('desktop', this)">Desktop</button>
                <button type="button" class="preview-device-btn" onclick="setPreviewDevice('tablet', this)">Tablet</button>
                <button type="button" class="preview-device-btn" onclick="setPreviewDevice('mobile', this)">Mobile</button>
              </div>
            </div>

            <!-- Preview Viewport -->
            <div id="preview-viewport" class="preview-viewport">
              <div id="preview-screen" class="preview-screen">

                <!-- Mock Top Header -->
                <div class="preview-mock-header">
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <img id="preview-logo" src="<?= e($gen['logo'] ?? '/arix/Arix.png') ?>" alt="Logo" style="height: 22px; object-fit: contain;">
                    <span id="preview-panel-name" style="font-size: 0.85rem; font-weight: 700; color: #fff;"><?= e($gen['panel_name'] ?? 'Nova') ?></span>
                  </div>

                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="background: rgba(255,255,255,0.06); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.7rem; color: var(--preview-text-muted, #8282A4);">Search...</div>
                    <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--preview-primary, #4A35CF); display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: #fff; font-weight: 700;">A</div>
                  </div>
                </div>

                <!-- Mock Navigation Dock Preview -->
                <div style="display: flex; gap: 0.35rem; overflow-x: auto;">
                  <div class="preview-mock-sidebar-item active">
                    <span>Dashboard</span>
                  </div>
                  <div class="preview-mock-sidebar-item">
                    <span>Servers</span>
                  </div>
                  <div class="preview-mock-sidebar-item">
                    <span>Nodes</span>
                  </div>
                  <div class="preview-mock-sidebar-item">
                    <span>Settings</span>
                  </div>
                </div>

                <!-- Mock Stat Metric Grid -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                  <div class="preview-mock-card">
                    <div style="font-size: 0.7rem; color: var(--preview-text-muted, #8282A4);">CPU Load</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-top: 0.15rem;">28.4%</div>
                  </div>
                  <div class="preview-mock-card">
                    <div style="font-size: 0.7rem; color: var(--preview-text-muted, #8282A4);">RAM Usage</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-top: 0.15rem;">3.8 / 16 GB</div>
                  </div>
                </div>

                <!-- Mock Server Row (Signature Arix Style) -->
                <div class="preview-mock-server-row">
                  <div class="preview-status-bar" id="preview-status-bar"></div>
                  <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                      <span style="font-size: 0.85rem; font-weight: 700; color: #fff;">Survival Production</span>
                      <span style="font-size: 0.65rem; background: rgba(34, 197, 94, 0.15); color: #4ade80; padding: 2px 6px; border-radius: 3px; font-weight: 600;">RUNNING</span>
                    </div>
                    <div style="font-size: 0.7rem; color: var(--preview-text-muted, #8282A4); margin-top: 0.2rem;">
                      127.0.0.1:25565 • Paper 1.20.4
                    </div>
                  </div>

                  <div style="display: flex; gap: 0.35rem;">
                    <button type="button" id="preview-btn" class="btn btn-primary" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">Manage</button>
                  </div>
                </div>

                <!-- Mock Secondary Card -->
                <div class="preview-mock-card" style="display: flex; justify-content: space-between; align-items: center;">
                  <span style="font-size: 0.75rem; color: var(--preview-text, #F4F4F4);">Interactive Button Styles</span>
                  <div style="display: flex; gap: 0.35rem;">
                    <button type="button" class="btn btn-secondary" style="padding: 0.3rem 0.5rem; font-size: 0.7rem;">Secondary</button>
                    <button type="button" class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.7rem;">Stop</button>
                  </div>
                </div>

              </div>
            </div>

            <!-- Preview Footer Info -->
            <div style="margin-top: 0.75rem; font-size: 0.725rem; color: rgb(var(--gray400)); text-align: center;">
              Changes made in any section instantly update CSS theme tokens in this preview box.
            </div>
          </div>

        </div>
      </form>
    </div>
  </main>
</div>

<!-- Modal: Reset Arix Theme Defaults -->
<div id="modal-reset-theme" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
  <div style="background: rgb(var(--gray700)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); max-width: 480px; width: 100%; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
    <h3 style="font-size: 1.25rem; color: #ffffff; margin-bottom: 0.75rem;">Reset to Arix v2.1.3 Defaults?</h3>
    <p style="font-size: 0.875rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.5rem;">
      Are you sure you want to restore the original <strong>Arix Theme v2.1.3 defaults</strong>? All custom colors, layout spacing, fonts, and component styles will be reset.
    </p>
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reset-theme').style.display='none'">Cancel</button>
      <form action="/admin/appearance/arix/reset" method="POST" style="margin: 0;">
        <button type="submit" class="btn btn-danger">Confirm Reset</button>
      </form>
    </div>
  </div>
</div>

<script>
let hasUnsavedChanges = false;

function markUnsaved() {
  hasUnsavedChanges = true;
  const badge = document.getElementById('save-status-badge');
  if (badge) {
    badge.className = 'save-status-pill unsaved';
    badge.textContent = 'Unsaved changes';
  }
}

function markSaved() {
  hasUnsavedChanges = false;
  const badge = document.getElementById('save-status-badge');
  if (badge) {
    badge.className = 'save-status-pill';
    badge.textContent = 'Saved successfully';
    setTimeout(() => {
      badge.textContent = 'Saved';
    }, 3000);
  }
}

window.addEventListener('beforeunload', (e) => {
  if (hasUnsavedChanges) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
  }
});

function switchTab(tabId, btn) {
  document.querySelectorAll('.editor-pane').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.editor-vtab-btn').forEach(el => el.classList.remove('active'));

  const targetPane = document.getElementById('tab-' + tabId);
  if (targetPane) targetPane.classList.add('active');
  if (btn) btn.classList.add('active');
}

function setPreviewDevice(mode, btn) {
  document.querySelectorAll('.preview-device-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const vp = document.getElementById('preview-viewport');
  vp.classList.remove('mode-tablet', 'mode-mobile');
  if (mode === 'tablet') vp.classList.add('mode-tablet');
  if (mode === 'mobile') vp.classList.add('mode-mobile');
}

function syncColorInput(key, val) {
  const hexInput = document.getElementById('hex-' + key);
  if (hexInput) hexInput.value = val;
  markUnsaved();
  updateLivePreview();
}

function syncColorPicker(key, val) {
  if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
    const pickInput = document.getElementById('pick-' + key);
    if (pickInput) pickInput.value = val;
    markUnsaved();
    updateLivePreview();
  }
}

function resetColorParam(key, defaultHex) {
  const pick = document.getElementById('pick-' + key);
  const hex = document.getElementById('hex-' + key);
  if (pick) pick.value = defaultHex;
  if (hex) hex.value = defaultHex;
  markUnsaved();
  updateLivePreview();
}

function resetParam(inputId, defaultVal) {
  const el = document.getElementById(inputId);
  if (el) {
    el.value = defaultVal;
    markUnsaved();
    updateLivePreview();
  }
}

function updateLivePreview() {
  const preview = document.getElementById('preview-screen');
  if (!preview) return;

  const primary = document.getElementById('hex-primary')?.value || '#4A35CF';
  const bg = document.getElementById('hex-background')?.value || '#040519';
  const card = document.getElementById('hex-card')?.value || '#1D1D37';
  const border = document.getElementById('hex-border')?.value || '#42425B';
  const text = document.getElementById('hex-text')?.value || '#F4F4F4';
  const textMuted = document.getElementById('hex-text_muted')?.value || '#8282A4';
  const headerBg = document.getElementById('hex-header')?.value || '#080922';
  const radius = (document.getElementById('input-cd-radius')?.value || '10') + 'px';
  const btnRadius = (document.getElementById('input-btn-radius')?.value || '7') + 'px';
  const font = document.getElementById('input-font-family')?.value || "'Inter', sans-serif";

  preview.style.setProperty('--preview-primary', primary);
  preview.style.setProperty('--preview-bg', bg);
  preview.style.setProperty('--preview-card', card);
  preview.style.setProperty('--preview-border', border);
  preview.style.setProperty('--preview-text', text);
  preview.style.setProperty('--preview-text-muted', textMuted);
  preview.style.setProperty('--preview-header-bg', headerBg);
  preview.style.setProperty('--preview-radius', radius);
  preview.style.setProperty('--preview-font', font);

  const previewBtn = document.getElementById('preview-btn');
  if (previewBtn) {
    previewBtn.style.background = primary;
    previewBtn.style.borderRadius = btnRadius;
    previewBtn.style.borderColor = primary;
  }

  const logoInput = document.getElementById('input-logo');
  const previewLogo = document.getElementById('preview-logo');
  if (logoInput && previewLogo && logoInput.value) {
    previewLogo.src = logoInput.value;
  }

  const panelNameInput = document.querySelector('input[name="general[panel_name]"]');
  const previewPanelName = document.getElementById('preview-panel-name');
  if (panelNameInput && previewPanelName) {
    previewPanelName.textContent = panelNameInput.value || 'Nova';
  }
}

async function saveArixTheme() {
  const form = document.getElementById('arix-editor-form');
  const formData = new FormData(form);
  const saveBtn = document.getElementById('btn-save-master');
  const badge = document.getElementById('save-status-badge');

  if (saveBtn) {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
  }
  if (badge) {
    badge.className = 'save-status-pill saving';
    badge.textContent = 'Saving...';
  }

  try {
    const res = await fetch('/admin/appearance/arix/save', {
      method: 'POST',
      body: new URLSearchParams(formData),
      headers: {
        'Accept': 'application/json'
      }
    });
    const data = await res.json();
    if (data.success) {
      markSaved();
      if (window.Nova && window.Nova.toast) {
        window.Nova.toast('Arix Theme configuration saved successfully!', 'success');
      }
    } else {
      throw new Error(data.error || 'Failed to save');
    }
  } catch (err) {
    console.error('AJAX save error, falling back to form submit:', err);
    hasUnsavedChanges = false;
    form.submit();
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
      saveBtn.textContent = 'SAVE CHANGES';
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  updateLivePreview();
});

// UI/UX Pro Max Rule: Keyboard shortcuts (Ctrl+S / Cmd+S to save, Esc to close modal)
window.addEventListener('keydown', (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
    e.preventDefault();
    saveArixTheme();
  }
  if (e.key === 'Escape') {
    const modal = document.getElementById('modal-reset-theme');
    if (modal) modal.style.display = 'none';
  }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
