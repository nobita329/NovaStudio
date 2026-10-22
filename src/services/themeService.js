const fs = require('fs');
const path = require('path');
const { getDb } = require('../database');

const ARIX_MANIFEST_PATH = path.join(__dirname, '../../themes/arix/theme.json');

class ThemeService {
  constructor() {
    this.cachedActiveTheme = null;
    this.cacheTimestamp = 0;
    this.initialized = false;
  }

  async ensureInitialized() {
    if (!this.initialized) {
      await this.init();
      this.initialized = true;
    }
  }

  async init() {
    const db = getDb();
    if (!db) return;

    // 1. Ensure theme_settings table exists (compatible with both SQLite & MySQL)
    if (db.type === 'mysql') {
      await db.query(`
        CREATE TABLE IF NOT EXISTS theme_settings (
          theme_key VARCHAR(64) PRIMARY KEY,
          name VARCHAR(128) NOT NULL,
          version VARCHAR(32) NOT NULL,
          author VARCHAR(128) NOT NULL,
          is_active TINYINT(1) DEFAULT 0,
          config_json LONGTEXT NOT NULL,
          pages_json LONGTEXT,
          components_json LONGTEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
      `);
    } else {
      await db.query(`
        CREATE TABLE IF NOT EXISTS theme_settings (
          theme_key VARCHAR(64) PRIMARY KEY,
          name VARCHAR(128) NOT NULL,
          version VARCHAR(32) NOT NULL,
          author VARCHAR(128) NOT NULL,
          is_active INTEGER DEFAULT 0,
          config_json TEXT NOT NULL,
          pages_json TEXT,
          components_json TEXT,
          created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
      `);
    }

    const upsertSetting = async (key, val) => {
      const existing = await db.get('SELECT `key` FROM settings WHERE `key` = ?', [key]);
      if (existing) {
        await db.query('UPDATE settings SET `value` = ? WHERE `key` = ?', [val, key]);
      } else {
        await db.query('INSERT INTO settings (`key`, `value`) VALUES (?, ?)', [key, val]);
      }
    };

    // 2. Load authentic Arix manifest defaults
    let arixDefaults = {};
    let manifestData = {
      name: 'Arix Theme',
      version: '2.1.3',
      author: 'Arix Team'
    };

    try {
      if (fs.existsSync(ARIX_MANIFEST_PATH)) {
        const raw = fs.readFileSync(ARIX_MANIFEST_PATH, 'utf8').trim();
        if (raw) {
          const parsed = JSON.parse(raw);
          if (parsed.defaults) arixDefaults = parsed.defaults;
          if (parsed.name) manifestData.name = parsed.name;
          if (parsed.version) manifestData.version = parsed.version;
          if (parsed.author) manifestData.author = parsed.author;
        }
      }
    } catch (e) {
      console.error('[ThemeService] Failed reading arix manifest:', e);
    }

    if (!arixDefaults.colors) {
      arixDefaults = this.getFallbackArixDefaults();
    }

    // 3. Ensure arix theme record exists
    const existingArix = await db.get('SELECT theme_key, config_json FROM theme_settings WHERE theme_key = ?', ['arix']);
    if (!existingArix) {
      await db.query(`
        INSERT INTO theme_settings (theme_key, name, version, author, is_active, config_json, pages_json, components_json)
        VALUES (?, ?, ?, ?, 1, ?, ?, ?)
      `, [
        'arix',
        manifestData.name,
        manifestData.version,
        manifestData.author,
        JSON.stringify(arixDefaults),
        JSON.stringify(this.getDefaultPagesConfig()),
        JSON.stringify(this.getDefaultComponentsConfig()),
      ]);

      await upsertSetting('active_theme', 'arix');
    } else if (!existingArix.config_json || existingArix.config_json === '{}') {
      await db.query(`
        UPDATE theme_settings 
        SET config_json = ?, pages_json = ?, components_json = ? 
        WHERE theme_key = 'arix'
      `, [
        JSON.stringify(arixDefaults),
        JSON.stringify(this.getDefaultPagesConfig()),
        JSON.stringify(this.getDefaultComponentsConfig()),
      ]);
    }

    // 4. Ensure default theme record exists
    const existingDefault = await db.get('SELECT theme_key FROM theme_settings WHERE theme_key = ?', ['default']);
    if (!existingDefault) {
      await db.query(`
        INSERT INTO theme_settings (theme_key, name, version, author, is_active, config_json, pages_json, components_json)
        VALUES (?, ?, ?, ?, 0, ?, ?, ?)
      `, [
        'default',
        'Nova Default',
        '1.0.0',
        'Nova Team',
        '{}',
        '{}',
        '{}'
      ]);
    }
  }

  async listThemes() {
    const db = getDb();
    if (!db) return [];
    await this.ensureInitialized();
    const rows = await db.query('SELECT theme_key, name, version, author, is_active, updated_at FROM theme_settings ORDER BY theme_key ASC');
    return rows || [];
  }

  async getTheme(themeKey) {
    const db = getDb();
    if (!db) return null;
    await this.ensureInitialized();
    const row = await db.get('SELECT * FROM theme_settings WHERE theme_key = ?', [themeKey]);
    if (!row) return null;

    let config = {};
    let pages = {};
    let components = {};
    try { config = JSON.parse(row.config_json || '{}'); } catch (e) {}
    try { pages = JSON.parse(row.pages_json || '{}'); } catch (e) {}
    try { components = JSON.parse(row.components_json || '{}'); } catch (e) {}

    return {
      theme_key: row.theme_key,
      name: row.name,
      version: row.version,
      author: row.author,
      is_active: row.is_active === 1,
      config,
      pages,
      components,
      updated_at: row.updated_at
    };
  }

  async getActiveTheme() {
    const now = Date.now();
    if (this.cachedActiveTheme && now - this.cacheTimestamp < 3000) {
      return this.cachedActiveTheme;
    }

    const db = getDb();
    if (!db) return null;
    await this.ensureInitialized();

    let row = await db.get('SELECT * FROM theme_settings WHERE is_active = 1 LIMIT 1');
    if (!row) {
      row = await db.get('SELECT * FROM theme_settings WHERE theme_key = ?', ['arix']);
    }

    if (!row) return null;

    let config = {};
    let pages = {};
    let components = {};
    try { config = JSON.parse(row.config_json || '{}'); } catch (e) {}
    try { pages = JSON.parse(row.pages_json || '{}'); } catch (e) {}
    try { components = JSON.parse(row.components_json || '{}'); } catch (e) {}

    const compiledCss = this.compileCss(config);

    const result = {
      theme_key: row.theme_key,
      name: row.name,
      version: row.version,
      author: row.author,
      is_active: row.is_active === 1,
      config,
      pages,
      components,
      compiledCss,
      updated_at: row.updated_at
    };

    this.cachedActiveTheme = result;
    this.cacheTimestamp = now;
    return result;
  }

  async activateTheme(themeKey) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    await db.query('UPDATE theme_settings SET is_active = 0');
    await db.query('UPDATE theme_settings SET is_active = 1 WHERE theme_key = ?', [themeKey]);

    const existing = await db.get('SELECT `key` FROM settings WHERE `key` = ?', ['active_theme']);
    if (existing) {
      await db.query('UPDATE settings SET `value` = ? WHERE `key` = ?', [themeKey, 'active_theme']);
    } else {
      await db.query('INSERT INTO settings (`key`, `value`) VALUES (?, ?)', ['active_theme', themeKey]);
    }

    this.cachedActiveTheme = null;
    return true;
  }

  async deactivateTheme(themeKey) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    await db.query('UPDATE theme_settings SET is_active = 0 WHERE theme_key = ?', [themeKey]);
    await db.query('UPDATE theme_settings SET is_active = 1 WHERE theme_key = ?', ['default']);

    const existing = await db.get('SELECT `key` FROM settings WHERE `key` = ?', ['active_theme']);
    if (existing) {
      await db.query('UPDATE settings SET `value` = ? WHERE `key` = ?', ['default', 'active_theme']);
    } else {
      await db.query('INSERT INTO settings (`key`, `value`) VALUES (?, ?)', ['active_theme', 'default']);
    }

    this.cachedActiveTheme = null;
    return true;
  }

  async saveThemeConfig(themeKey, newConfig) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    const existing = await this.getTheme(themeKey);
    if (!existing) return false;

    // Deep merge sections
    const merged = { ...existing.config };
    for (const [sectionKey, sectionVal] of Object.entries(newConfig)) {
      if (sectionVal && typeof sectionVal === 'object' && !Array.isArray(sectionVal)) {
        merged[sectionKey] = { ...(merged[sectionKey] || {}), ...sectionVal };
      } else {
        merged[sectionKey] = sectionVal;
      }
    }

    await db.query(`
      UPDATE theme_settings 
      SET config_json = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE theme_key = ?
    `, [JSON.stringify(merged), themeKey]);

    this.cachedActiveTheme = null;
    return true;
  }

  async savePagesConfig(themeKey, pagesConfig) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    await db.query(`
      UPDATE theme_settings 
      SET pages_json = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE theme_key = ?
    `, [JSON.stringify(pagesConfig), themeKey]);

    this.cachedActiveTheme = null;
    return true;
  }

  async saveComponentsConfig(themeKey, componentsConfig) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    await db.query(`
      UPDATE theme_settings 
      SET components_json = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE theme_key = ?
    `, [JSON.stringify(componentsConfig), themeKey]);

    this.cachedActiveTheme = null;
    return true;
  }

  async resetTheme(themeKey) {
    const db = getDb();
    if (!db) return false;
    await this.ensureInitialized();

    let defaults = this.getFallbackArixDefaults();
    if (themeKey === 'arix' && fs.existsSync(ARIX_MANIFEST_PATH)) {
      try {
        const raw = fs.readFileSync(ARIX_MANIFEST_PATH, 'utf8').trim();
        if (raw) {
          const manifest = JSON.parse(raw);
          if (manifest.defaults) defaults = manifest.defaults;
        }
      } catch (e) {}
    }

    await db.query(`
      UPDATE theme_settings 
      SET config_json = ?, pages_json = ?, components_json = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE theme_key = ?
    `, [
      JSON.stringify(defaults),
      JSON.stringify(this.getDefaultPagesConfig()),
      JSON.stringify(this.getDefaultComponentsConfig()),
      themeKey
    ]);

    this.cachedActiveTheme = null;
    return true;
  }

  async exportTheme(themeKey) {
    const theme = await this.getTheme(themeKey);
    if (!theme) return null;

    return {
      manifest: {
        theme_key: theme.theme_key,
        name: theme.name,
        version: theme.version,
        author: theme.author,
        exported_at: new Date().toISOString()
      },
      config: theme.config,
      pages: theme.pages,
      components: theme.components
    };
  }

  async importTheme(themeKey, payload) {
    if (!payload || typeof payload !== 'object') {
      throw new Error('Invalid theme configuration format.');
    }

    const config = payload.config || {};
    const pages = payload.pages || this.getDefaultPagesConfig();
    const components = payload.components || this.getDefaultComponentsConfig();

    const db = getDb();
    if (!db) throw new Error('Database unavailable.');
    await this.ensureInitialized();

    await db.query(`
      UPDATE theme_settings 
      SET config_json = ?, pages_json = ?, components_json = ?, updated_at = CURRENT_TIMESTAMP 
      WHERE theme_key = ?
    `, [
      JSON.stringify(config),
      JSON.stringify(pages),
      JSON.stringify(components),
      themeKey
    ]);

    this.cachedActiveTheme = null;
    return true;
  }

  compileCss(config) {
    if (!config) return '';
    const colors = config.colors || {};
    const sidebar = config.sidebar || {};
    const header = config.header || {};
    const cards = config.cards || {};
    const buttons = config.buttons || {};
    const typo = config.typography || {};
    const layout = config.layout || {};

    const hexToRgb = (hex) => {
      if (!hex || typeof hex !== 'string') return null;
      let c = hex.replace('#', '');
      if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
      if (c.length !== 6) return null;
      const r = parseInt(c.substring(0, 2), 16);
      const g = parseInt(c.substring(2, 4), 16);
      const b = parseInt(c.substring(4, 6), 16);
      return `${r}, ${g}, ${b}`;
    };

    const primaryRgb = hexToRgb(colors.primary) || '74, 53, 207';

    let css = `
/* --- Arix Dynamic Theme Variables (From Arix Theme Editor) --- */
:root {
  --primary: ${colors.primary || '#4A35CF'};
  --primary-rgb: ${primaryRgb};
  --primary-border: ${colors.accent || '#7562f0'};
  --primary-hover: ${colors.primary ? colors.primary + 'ee' : '#5d48e0'};
  
  --bg-main: ${colors.background || '#040519'};
  --bg-surface: ${colors.surface || '#0B0D2A'};
  --bg-card: ${colors.card || '#1D1D37'};
  --border-color: ${colors.border || '#42425B'};
  --text-main: ${colors.text || '#F4F4F4'};
  --text-muted: ${colors.text_muted || '#8282A4'};
  
  --successBackground: ${colors.success || '#1f8f2b'};
  --successText: ${colors.success_text || '#E1FFD8'};
  --successBorder: ${colors.success_border || '#2bab32'};
  
  --warningBackground: ${colors.warning || '#B45309'};
  --warningText: ${colors.warning_text || '#FFF4D8'};
  --warningBorder: ${colors.warning_border || '#D97706'};
  
  --dangerBackground: ${colors.danger || '#8f1f37'};
  --dangerText: ${colors.danger_text || '#FFD8D8'};
  --dangerBorder: ${colors.danger_border || '#ab2b40'};
  
  --sidebar-width: ${sidebar.width ? sidebar.width + 'px' : '260px'};
  --header-height: ${header.height ? header.height + 'px' : '64px'};
  --radiusBox: ${cards.radius ? cards.radius + 'px' : '10px'};
  --radiusInput: ${buttons.radius ? buttons.radius + 'px' : '7px'};
}
`;

    if (sidebar.background) {
      css += `
.floating-sidebar, .sidebar {
  background: ${sidebar.background} !important;
  border-color: ${sidebar.border || 'rgba(66, 66, 91, 0.6)'} !important;
  width: ${sidebar.width ? sidebar.width + 'px' : '260px'} !important;
}
`;
    }

    if (header.background) {
      css += `
.top-header {
  background: ${header.background} !important;
  border-bottom: 1px solid ${header.border || 'rgba(66, 66, 91, 0.6)'} !important;
  height: ${header.height ? header.height + 'px' : '64px'} !important;
}
`;
    }

    if (cards.background || cards.radius) {
      css += `
.card, .arix-card, .stat-card, .arix-server-row {
  ${cards.background ? `background: ${cards.background} !important;` : ''}
  border-radius: ${cards.radius ? cards.radius + 'px' : '10px'} !important;
  ${cards.border ? `border-color: ${cards.border} !important;` : ''}
}
`;
    }

    if (buttons.primary) {
      css += `
.btn-primary {
  background: ${buttons.primary} !important;
  border-color: ${buttons.primary_border || buttons.primary} !important;
  border-radius: ${buttons.radius ? buttons.radius + 'px' : '7px'} !important;
}
`;
    }

    if (typo.font_family) {
      css += `
body {
  font-family: ${typo.font_family} !important;
}
`;
    }

    if (config.general && config.general.custom_css) {
      css += `\n/* --- Custom CSS from Arix Editor --- */\n${config.general.custom_css}\n`;
    }

    return css;
  }

  getFallbackArixDefaults() {
    return {
      general: {
        theme_name: 'Arix Theme',
        theme_version: '2.1.3',
        author: 'Arix Team',
        panel_name: 'Nova',
        logo: '/arix/Arix.png',
        favicon: '/arix/Arix.png',
        login_logo: '/arix/Arix.png',
        login_background: '/arix/background-login.png',
        footer_text: 'Powered by Nova & Arix Theme v2.1.3',
        page_title: 'Arix',
        default_mode: 'dark',
        custom_css: '',
        custom_js: ''
      },
      colors: {
        primary: '#4A35CF',
        secondary: '#2b2b48',
        accent: '#7562f0',
        background: '#040519',
        surface: '#0B0D2A',
        surface_secondary: '#15183d',
        card: '#1D1D37',
        border: '#42425B',
        text: '#F4F4F4',
        text_muted: '#8282A4',
        success: '#1f8f2b',
        warning: '#B45309',
        danger: '#8f1f37',
        info: '#0284c7',
        sidebar: '#080922',
        header: '#080922'
      },
      typography: {
        font_family: "'Inter', 'IBM Plex Sans', sans-serif",
        heading_font: "'Inter', sans-serif",
        body_font: "'Inter', sans-serif",
        font_size: '14',
        heading_size: '20',
        font_weight: '500',
        line_height: '1.5',
        letter_spacing: '0'
      },
      sidebar: {
        enabled: true,
        width: 260,
        collapsed_width: 72,
        background: '#080922',
        border: '#42425B',
        logo: '/arix/Arix.png',
        logo_width: 120,
        menu_spacing: 4,
        menu_font_size: 14,
        icon_size: 20,
        active_bg: '#4A35CF',
        active_text: '#ffffff',
        hover_bg: 'rgba(74, 53, 207, 0.15)',
        hover_text: '#ffffff',
        border_radius: 14,
        collapsed: false
      },
      header: {
        enabled: true,
        height: 64,
        background: 'rgba(8, 9, 34, 0.8)',
        border: '#42425B',
        logo: true,
        search: true,
        notifications: true,
        user_menu: true,
        mobile_menu: true,
        header_shadow: true,
        header_radius: 0
      },
      cards: {
        background: '#1D1D37',
        border: '#42425B',
        border_width: 1,
        radius: 10,
        shadow: '0 8px 24px rgba(0,0,0,0.4)',
        padding: 20,
        hover_effect: true,
        hover_transform: '-2px'
      },
      buttons: {
        radius: 7,
        height: 38,
        font_size: 14,
        primary: '#4A35CF',
        primary_text: '#ffffff',
        primary_border: '#7562f0',
        secondary: '#2b2b48',
        secondary_text: '#B2B2C1',
        success: '#1f8f2b',
        warning: '#B45309',
        danger: '#8f1f37',
        outline: 'transparent',
        ghost: 'transparent'
      },
      layout: {
        container_width: 1400,
        page_spacing: 24,
        sidebar_spacing: 16,
        card_spacing: 16,
        grid_columns: 3,
        border_radius: 10,
        shadows: true
      }
    };
  }

  getDefaultPagesConfig() {
    return {
      '/dashboard': { title: 'Dashboard', subtitle: 'Overview of your active servers and telemetry', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/servers': { title: 'All Servers', subtitle: 'Managed instances under your jurisdiction', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/console': { title: 'Server Console', subtitle: 'Interactive live terminal with hardware stats', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/files': { title: 'File Manager', subtitle: 'Browse, edit, and organize container files', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/databases': { title: 'Databases', subtitle: 'Managed SQL database instances', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/schedules': { title: 'Schedules', subtitle: 'Automate tasks with cron jobs', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/users': { title: 'Users & Subusers', subtitle: 'Delegate instance access and permissions', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/backups': { title: 'Backups', subtitle: 'Generate, restore, and download server archives', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/network': { title: 'Network', subtitle: 'IP address allocations and port forwards', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/startup': { title: 'Startup Configuration', subtitle: 'Docker images and environment parameters', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/server/:id/settings': { title: 'Server Settings', subtitle: 'SFTP details, rename, reinstall, and delete', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/auth/login': { title: 'Sign In', subtitle: 'Welcome to your server management portal', enabled: true, show_header: false, show_sidebar: false, bg_style: 'default', container_width: 480, custom_css: '', custom_js: '' },
      '/auth/forgot-password': { title: 'Reset Password', subtitle: 'Request password recovery email', enabled: true, show_header: false, show_sidebar: false, bg_style: 'default', container_width: 480, custom_css: '', custom_js: '' },
      '/account': { title: 'Account Profile', subtitle: 'Manage identity, credentials, and settings', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' },
      '/admin': { title: 'Admin Overview', subtitle: 'System administration and infrastructure health', enabled: true, show_header: true, show_sidebar: true, bg_style: 'default', container_width: 1400, custom_css: '', custom_js: '' }
    };
  }

  getDefaultComponentsConfig() {
    return {
      'sidebar': { name: 'Sidebar', style: 'floating', logo_glow: true, rounded: 14, blur: true, width: 260 },
      'header': { name: 'Header / Topbar', style: 'glass', show_breadcrumbs: true, show_search: true, show_theme_toggle: true, height: 64 },
      'navigation': { name: 'Navigation', pill_style: true, show_icons: true, active_glow: true },
      'user_menu': { name: 'User Profile Menu', show_avatar: true, show_role_badge: true, style: 'dropdown' },
      'server_card': { name: 'Server Card', style: 'arix-row', show_ip_pill: true, show_status_bar: true, live_stats: true, hover_lift: true },
      'server_status': { name: 'Server Status Badge', pulse_animation: true, show_text: true, pill_shape: true },
      'resource_card': { name: 'Resource Metric Cards', style: 'colored-icons', rounded: 10, shadow: true, show_sparkline: true },
      'table': { name: 'Data Tables', hover_highlight: true, striped: false, border_style: 'subtle', compact: false },
      'modal': { name: 'Dialogs & Modals', backdrop_blur: true, radius: 12, animation: 'fade-scale' },
      'button': { name: 'Action Buttons', hover_glow: true, radius: 7, font_weight: '600', uppercase: false },
      'input': { name: 'Form Inputs', radius: 7, focus_ring: true, dark_bg: true },
      'alert': { name: 'Alerts & Notices', rounded: 8, show_icon: true, border_accent: true },
      'notification': { name: 'Notifications / Toasts', position: 'top-right', sound_effects: true, duration: 4000 },
      'tabs': { name: 'Tabs Navigation', style: 'underline', animated_slider: true },
      'breadcrumb': { name: 'Breadcrumbs', show_home_icon: true, separator: 'chevron', uppercase: false },
      'footer': { name: 'Footer', visible: true, text: 'Powered by Nova & Arix Theme v2.1.3' }
    };
  }

  getAvailablePages() {
    return [
      { id: 'dashboard', path: '/dashboard', category: 'User' },
      { id: 'servers', path: '/servers', category: 'User' },
      { id: 'console', path: '/server/:id/console', category: 'Server' },
      { id: 'files', path: '/server/:id/files', category: 'Server' },
      { id: 'databases', path: '/server/:id/databases', category: 'Server' },
      { id: 'schedules', path: '/server/:id/schedules', category: 'Server' },
      { id: 'users', path: '/server/:id/users', category: 'Server' },
      { id: 'backups', path: '/server/:id/backups', category: 'Server' },
      { id: 'network', path: '/server/:id/network', category: 'Server' },
      { id: 'startup', path: '/server/:id/startup', category: 'Server' },
      { id: 'settings', path: '/server/:id/settings', category: 'Server' },
      { id: 'login', path: '/auth/login', category: 'Auth' },
      { id: 'forgot_password', path: '/auth/forgot-password', category: 'Auth' },
      { id: 'account_profile', path: '/account', category: 'Account' },
      { id: 'account_security', path: '/account/security', category: 'Account' },
      { id: 'account_api', path: '/account/api', category: 'Account' },
      { id: 'account_ssh', path: '/account/ssh-keys', category: 'Account' },
      { id: 'admin_dashboard', path: '/admin', category: 'Admin' },
      { id: 'admin_users', path: '/admin/users', category: 'Admin' },
      { id: 'admin_servers', path: '/admin/servers', category: 'Admin' },
      { id: 'admin_nodes', path: '/admin/nodes', category: 'Admin' },
      { id: 'admin_settings', path: '/admin/settings', category: 'Admin' },
      { id: 'admin_appearance', path: '/admin/appearance', category: 'Admin' },
    ];
  }
}

module.exports = new ThemeService();
