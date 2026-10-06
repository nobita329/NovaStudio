<?php
$title = 'Arix Component Editor';
$breadcrumb = 'Admin / Appearance / Components';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$components = $theme['components'] ?? [];
?>
<style>
.comp-card {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  transition: var(--transition);
}
.comp-card:hover {
  border-color: rgba(74, 53, 207, 0.4);
}
.comp-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  border-bottom: 1px solid rgba(var(--gray600), 0.3);
  padding-bottom: 0.75rem;
}
.comp-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}
.switch-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: rgb(var(--gray200));
  cursor: pointer;
}

/* UI/UX Pro Max Accessibility & Interaction tokens */
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
        <h1 class="page-title">Reusable Component Customization</h1>
        <p class="page-description">Fine-tune individual UI components, layouts, interactive states, and behavioral toggles across the Arix Theme.</p>
      </div>

      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/admin/appearance" class="btn btn-secondary btn-sm">← Back to Themes</a>
        <a href="/admin/appearance/arix/editor" class="btn btn-secondary btn-sm">Theme Editor</a>
        <a href="/admin/appearance/arix/pages" class="btn btn-secondary btn-sm">Pages Editor</a>
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('modal-reset-comp').style.display='flex'">Reset Defaults</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('comp-form').submit()">Save All Components</button>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.5rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <form id="comp-form" action="/admin/appearance/arix/components/save" method="POST">
      
      <!-- 1. SIDEBAR -->
      <?php $sb = $components['sidebar'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Sidebar Component</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Left navigation dock with signature Arix floating design</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Navigation Dock</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Sidebar Style</label>
            <select name="components[sidebar][style]" class="form-control">
              <option value="floating" <?= ($sb['style'] ?? '') === 'floating' ? 'selected' : '' ?>>Floating Island (Arix Original)</option>
              <option value="static" <?= ($sb['style'] ?? '') === 'static' ? 'selected' : '' ?>>Full Height Static</option>
              <option value="glass" <?= ($sb['style'] ?? '') === 'glass' ? 'selected' : '' ?>>Translucent Glass</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Width (px)</label>
            <input type="number" name="components[sidebar][width]" class="form-control" value="<?= e($sb['width'] ?? 260) ?>" min="180" max="400">
          </div>
          <div class="form-group">
            <label class="form-label">Border Radius (px)</label>
            <input type="number" name="components[sidebar][rounded]" class="form-control" value="<?= e($sb['rounded'] ?? 14) ?>" min="0" max="30">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[sidebar][logo_glow]" value="1" <?= !empty($sb['logo_glow']) ? 'checked' : '' ?>>
            <span>Logo Primary Glow</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[sidebar][blur]" value="1" <?= !empty($sb['blur']) ? 'checked' : '' ?>>
            <span>Backdrop Blur Filter</span>
          </label>
        </div>
      </div>

      <!-- 2. HEADER -->
      <?php $hd = $components['header'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Header / Topbar Component</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Top navigation, breadcrumbs, search, and user controls</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Top Bar</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Header Style</label>
            <select name="components[header][style]" class="form-control">
              <option value="glass" <?= ($hd['style'] ?? '') === 'glass' ? 'selected' : '' ?>>Glassmorphism Blur</option>
              <option value="solid" <?= ($hd['style'] ?? '') === 'solid' ? 'selected' : '' ?>>Solid Surface Color</option>
              <option value="transparent" <?= ($hd['style'] ?? '') === 'transparent' ? 'selected' : '' ?>>Fully Transparent</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Height (px)</label>
            <input type="number" name="components[header][height]" class="form-control" value="<?= e($hd['height'] ?? 64) ?>" min="48" max="100">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[header][show_breadcrumbs]" value="1" <?= !empty($hd['show_breadcrumbs']) ? 'checked' : '' ?>>
            <span>Show Breadcrumbs</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[header][show_search]" value="1" <?= !empty($hd['show_search']) ? 'checked' : '' ?>>
            <span>Show Global Search</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[header][show_theme_toggle]" value="1" <?= !empty($hd['show_theme_toggle']) ? 'checked' : '' ?>>
            <span>Show Theme Mode Toggle</span>
          </label>
        </div>
      </div>

      <!-- 3. NAVIGATION -->
      <?php $nav = $components['navigation'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Navigation Links</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Active indicator styles, hover animations, and icon states</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Menu Links</span>
        </div>
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[navigation][pill_style]" value="1" <?= !empty($nav['pill_style']) ? 'checked' : '' ?>>
            <span>Pill-Style Rounded Active State</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[navigation][show_icons]" value="1" <?= !empty($nav['show_icons']) ? 'checked' : '' ?>>
            <span>Display Category Icons</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[navigation][active_glow]" value="1" <?= !empty($nav['active_glow']) ? 'checked' : '' ?>>
            <span>Active Item Neon Glow</span>
          </label>
        </div>
      </div>

      <!-- 4. SERVER CARD -->
      <?php $sc = $components['server_card'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Server Card & Row Component</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Server display on main dashboard (Arix signature row vs grid)</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Server Presentation</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Layout Style</label>
            <select name="components[server_card][style]" class="form-control">
              <option value="arix-row" <?= ($sc['style'] ?? '') === 'arix-row' ? 'selected' : '' ?>>Arix Horizontal Box with Side Accent Bar</option>
              <option value="grid-card" <?= ($sc['style'] ?? '') === 'grid-card' ? 'selected' : '' ?>>Vertical Multi-Column Grid Cards</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[server_card][show_ip_pill]" value="1" <?= !empty($sc['show_ip_pill']) ? 'checked' : '' ?>>
            <span>Show IP:Port Pill</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[server_card][show_status_bar]" value="1" <?= !empty($sc['show_status_bar']) ? 'checked' : '' ?>>
            <span>Show Right-Edge Status Bar Indicator</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[server_card][live_stats]" value="1" <?= !empty($sc['live_stats']) ? 'checked' : '' ?>>
            <span>Live Real-time Telemetry (CPU/RAM)</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[server_card][hover_lift]" value="1" <?= !empty($sc['hover_lift']) ? 'checked' : '' ?>>
            <span>Subtle Hover Lift Animation</span>
          </label>
        </div>
      </div>

      <!-- 5. RESOURCE STAT CARDS -->
      <?php $rc = $components['resource_card'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Resource Metric Cards</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">CPU, RAM, Disk, and Network telemetry display widgets</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Hardware Stats</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Visual Style</label>
            <select name="components[resource_card][style]" class="form-control">
              <option value="colored-icons" <?= ($rc['style'] ?? '') === 'colored-icons' ? 'selected' : '' ?>>Colored Icon Boxes</option>
              <option value="gradient" <?= ($rc['style'] ?? '') === 'gradient' ? 'selected' : '' ?>>Subtle Gradient Background</option>
              <option value="minimal" <?= ($rc['style'] ?? '') === 'minimal' ? 'selected' : '' ?>>Minimal Flat Cards</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Border Radius (px)</label>
            <input type="number" name="components[resource_card][rounded]" class="form-control" value="<?= e($rc['rounded'] ?? 10) ?>" min="0" max="25">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[resource_card][shadow]" value="1" <?= !empty($rc['shadow']) ? 'checked' : '' ?>>
            <span>Elevated Drop Shadow</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[resource_card][show_sparkline]" value="1" <?= !empty($rc['show_sparkline']) ? 'checked' : '' ?>>
            <span>Display Historical Sparkline Graph</span>
          </label>
        </div>
      </div>

      <!-- 6. DATA TABLES -->
      <?php $tb = $components['table'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Data Tables</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Users, Nodes, Backups, Schedules and Database records</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Data Tables</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Border Style</label>
            <select name="components[table][border_style]" class="form-control">
              <option value="subtle" <?= ($tb['border_style'] ?? '') === 'subtle' ? 'selected' : '' ?>>Subtle Low-Contrast</option>
              <option value="crisp" <?= ($tb['border_style'] ?? '') === 'crisp' ? 'selected' : '' ?>>Crisp High-Contrast</option>
              <option value="none" <?= ($tb['border_style'] ?? '') === 'none' ? 'selected' : '' ?>>Border-less Clean</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[table][hover_highlight]" value="1" <?= !empty($tb['hover_highlight']) ? 'checked' : '' ?>>
            <span>Hover Highlight on Rows</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[table][striped]" value="1" <?= !empty($tb['striped']) ? 'checked' : '' ?>>
            <span>Alternating Row Stripes</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[table][compact]" value="1" <?= !empty($tb['compact']) ? 'checked' : '' ?>>
            <span>Compact Row Padding</span>
          </label>
        </div>
      </div>

      <!-- 7. MODALS & DIALOGS -->
      <?php $md = $components['modal'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Modals & Dialogs</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Overlay modals, confirmation dialogs, and popups</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Dialogs</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Open Animation</label>
            <select name="components[modal][animation]" class="form-control">
              <option value="fade-scale" <?= ($md['animation'] ?? '') === 'fade-scale' ? 'selected' : '' ?>>Fade & Scale In</option>
              <option value="slide-down" <?= ($md['animation'] ?? '') === 'slide-down' ? 'selected' : '' ?>>Slide Down From Top</option>
              <option value="instant" <?= ($md['animation'] ?? '') === 'instant' ? 'selected' : '' ?>>Instant Appearance</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Corner Radius (px)</label>
            <input type="number" name="components[modal][radius]" class="form-control" value="<?= e($md['radius'] ?? 12) ?>" min="4" max="30">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[modal][backdrop_blur]" value="1" <?= !empty($md['backdrop_blur']) ? 'checked' : '' ?>>
            <span>Glass Backdrop Blur</span>
          </label>
        </div>
      </div>

      <!-- 8. DROPDOWNS -->
      <?php $dd = $components['dropdown'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Dropdown Menus</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Flyout options, action menus, and filter dropdowns</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Dropdowns</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Corner Radius (px)</label>
            <input type="number" name="components[dropdown][radius]" class="form-control" value="<?= e($dd['radius'] ?? 8) ?>" min="0" max="20">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[dropdown][shadow]" value="1" <?= !isset($dd['shadow']) || !empty($dd['shadow']) ? 'checked' : '' ?>>
            <span>Elevated Floating Shadow</span>
          </label>
        </div>
      </div>

      <!-- 9. BUTTONS -->
      <?php $btn = $components['button'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Action Buttons</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Primary, secondary, danger, and action triggers</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Buttons</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Border Radius (px)</label>
            <input type="number" name="components[button][radius]" class="form-control" value="<?= e($btn['radius'] ?? 7) ?>" min="0" max="30">
          </div>
          <div class="form-group">
            <label class="form-label">Font Weight</label>
            <select name="components[button][font_weight]" class="form-control">
              <option value="500" <?= ($btn['font_weight'] ?? '') === '500' ? 'selected' : '' ?>>Medium (500)</option>
              <option value="600" <?= ($btn['font_weight'] ?? '') === '600' ? 'selected' : '' ?>>Semi-Bold (600)</option>
              <option value="700" <?= ($btn['font_weight'] ?? '') === '700' ? 'selected' : '' ?>>Bold (700)</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[button][hover_glow]" value="1" <?= !empty($btn['hover_glow']) ? 'checked' : '' ?>>
            <span>Primary Button Neon Hover Glow</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[button][uppercase]" value="1" <?= !empty($btn['uppercase']) ? 'checked' : '' ?>>
            <span>Uppercase Button Text</span>
          </label>
        </div>
      </div>

      <!-- 10. INPUTS -->
      <?php $inp = $components['input'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Form Inputs</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Text fields, textareas, selects, and checkboxes</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Form Controls</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Border Radius (px)</label>
            <input type="number" name="components[input][radius]" class="form-control" value="<?= e($inp['radius'] ?? 7) ?>" min="0" max="25">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[input][focus_ring]" value="1" <?= !empty($inp['focus_ring']) ? 'checked' : '' ?>>
            <span>Focus Primary Ring Indicator</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[input][dark_bg]" value="1" <?= !empty($inp['dark_bg']) ? 'checked' : '' ?>>
            <span>Dark Tinted Input Background</span>
          </label>
        </div>
      </div>

      <!-- 11. BADGES -->
      <?php $bg = $components['badge'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Status Badges</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Server state, user role, and information pills</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Badges</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Badge Shape</label>
            <select name="components[badge][shape]" class="form-control">
              <option value="pill" <?= ($bg['shape'] ?? '') === 'pill' ? 'selected' : '' ?>>Rounded Full Pill</option>
              <option value="square" <?= ($bg['shape'] ?? '') === 'square' ? 'selected' : '' ?>>Slightly Rounded Box</option>
            </select>
          </div>
        </div>
      </div>

      <!-- 12. ALERTS -->
      <?php $alt = $components['alert'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Alerts & Notices</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Success, danger, warning, and informational banners</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Alerts</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Border Radius (px)</label>
            <input type="number" name="components[alert][rounded]" class="form-control" value="<?= e($alt['rounded'] ?? 8) ?>" min="0" max="20">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[alert][show_icon]" value="1" <?= !empty($alt['show_icon']) ? 'checked' : '' ?>>
            <span>Show Status Icon</span>
          </label>
          <label class="switch-label">
            <input type="checkbox" name="components[alert][border_accent]" value="1" <?= !empty($alt['border_accent']) ? 'checked' : '' ?>>
            <span>Thick Left Accent Border</span>
          </label>
        </div>
      </div>

      <!-- 13. TOASTS & NOTIFICATIONS -->
      <?php $notif = $components['notification'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Notifications & Toasts</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Live toast feedback notifications with audio effects</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Toasts</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Toast Screen Position</label>
            <select name="components[notification][position]" class="form-control">
              <option value="top-right" <?= ($notif['position'] ?? '') === 'top-right' ? 'selected' : '' ?>>Top Right</option>
              <option value="top-center" <?= ($notif['position'] ?? '') === 'top-center' ? 'selected' : '' ?>>Top Center</option>
              <option value="bottom-right" <?= ($notif['position'] ?? '') === 'bottom-right' ? 'selected' : '' ?>>Bottom Right</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Duration (ms)</label>
            <input type="number" name="components[notification][duration]" class="form-control" value="<?= e($notif['duration'] ?? 4000) ?>" min="1000" max="15000">
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[notification][sound_effects]" value="1" <?= !empty($notif['sound_effects']) ? 'checked' : '' ?>>
            <span>Enable Arix Audio Sound Triggers</span>
          </label>
        </div>
      </div>

      <!-- 14. TABS -->
      <?php $tbs = $components['tabs'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Tabs Navigation</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Sub-navigation bars for server and settings sections</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Tabs</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Tab Visual Style</label>
            <select name="components[tabs][style]" class="form-control">
              <option value="underline" <?= ($tbs['style'] ?? '') === 'underline' ? 'selected' : '' ?>>Underline with Primary Indicator</option>
              <option value="pill" <?= ($tbs['style'] ?? '') === 'pill' ? 'selected' : '' ?>>Pill Buttons</option>
              <option value="segmented" <?= ($tbs['style'] ?? '') === 'segmented' ? 'selected' : '' ?>>Segmented Background Bar</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[tabs][animated_slider]" value="1" <?= !empty($tbs['animated_slider']) ? 'checked' : '' ?>>
            <span>Animated Active Slider Bar</span>
          </label>
        </div>
      </div>

      <!-- 15. BREADCRUMBS -->
      <?php $bc = $components['breadcrumb'] ?? []; ?>
      <div class="comp-card">
        <div class="comp-header">
          <div>
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff;">Breadcrumbs</h3>
            <span style="font-size: 0.8rem; color: rgb(var(--gray400));">Hierarchical navigation trail in the top header</span>
          </div>
          <span style="font-size: 0.75rem; background: rgba(74, 53, 207, 0.2); color: var(--primary-border); padding: 0.2rem 0.6rem; border-radius: 4px;">Breadcrumbs</span>
        </div>
        <div class="comp-grid">
          <div class="form-group">
            <label class="form-label">Separator Symbol</label>
            <select name="components[breadcrumb][separator]" class="form-control">
              <option value="chevron" <?= ($bc['separator'] ?? '') === 'chevron' ? 'selected' : '' ?>>Chevron (›)</option>
              <option value="slash" <?= ($bc['separator'] ?? '') === 'slash' ? 'selected' : '' ?>>Forward Slash (/)</option>
              <option value="arrow" <?= ($bc['separator'] ?? '') === 'arrow' ? 'selected' : '' ?>>Arrow (→)</option>
            </select>
          </div>
        </div>
        <div style="display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap;">
          <label class="switch-label">
            <input type="checkbox" name="components[breadcrumb][show_home_icon]" value="1" <?= !empty($bc['show_home_icon']) ? 'checked' : '' ?>>
            <span>Show Home Dashboard Icon</span>
          </label>
        </div>
      </div>

      <div style="position: sticky; bottom: 20px; background: rgb(var(--gray800)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 50;">
        <span style="font-size: 0.85rem; color: rgb(var(--gray300));">Configuring reusable theme components</span>
        <div style="display: flex; gap: 0.75rem;">
          <a href="/admin/appearance" class="btn btn-secondary btn-sm">Cancel</a>
          <button type="submit" class="btn btn-primary btn-sm">Save All Components</button>
        </div>
      </div>
    </form>
  </main>
</div>

<!-- Modal: Reset Components -->
<div id="modal-reset-comp" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 999; align-items: center; justify-content: center; padding: 1.5rem; backdrop-filter: blur(8px);">
  <div style="background: rgb(var(--gray700)); border: 1px solid var(--border-color); border-radius: var(--radiusBox); max-width: 480px; width: 100%; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
    <h3 style="font-size: 1.25rem; color: #ffffff; margin-bottom: 0.75rem;">Reset Component Configurations?</h3>
    <p style="font-size: 0.875rem; color: rgb(var(--gray300)); line-height: 1.5; margin-bottom: 1.5rem;">
      Are you sure you want to restore all component properties, styles, and options to original Arix defaults?
    </p>
    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-reset-comp').style.display='none'">Cancel</button>
      <form action="/admin/appearance/arix/components/reset" method="POST" style="margin: 0;">
        <button type="submit" class="btn btn-danger">Confirm Reset</button>
      </form>
    </div>
  </div>
<script>
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const m = document.getElementById('modal-reset-comp');
    if (m) m.style.display = 'none';
  }
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
