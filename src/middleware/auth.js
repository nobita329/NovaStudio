const { getDb } = require('../database');
const jwt = require('jsonwebtoken');
const config = require('../config');

const themeService = require('../services/themeService');

async function attachSettings(req, res, next) {
  try {
    const db = getDb();
    if (db) {
      const rows = await db.query('SELECT `key`, `value` FROM settings');
      const settings = {};
      if (rows) {
        for (const row of rows) {
          settings[row.key] = row.value;
        }
      }
      res.locals.settings = settings;
    }
  } catch (err) {
    res.locals.settings = {
      panel_name: 'Nova',
      panel_logo: '/arix/Arix.png',
      panel_favicon: '/arix/Arix.png',
    };
  }

  try {
    const activeTheme = await themeService.getActiveTheme();
    res.locals.activeTheme = activeTheme;
    res.locals.themeCss = activeTheme ? activeTheme.compiledCss : '';
    res.locals.themeConfig = activeTheme ? activeTheme.config : {};
  } catch (e) {
    res.locals.activeTheme = null;
    res.locals.themeCss = '';
    res.locals.themeConfig = {};
  }

  res.locals.user = req.session && req.session.user ? req.session.user : null;
  res.locals.currentPath = req.path;
  next();
}

function requireAuth(req, res, next) {
  if (req.session && req.session.user) {
    return next();
  }

  // Check Bearer Token for API calls
  const authHeader = req.headers.authorization;
  if (authHeader && authHeader.startsWith('Bearer ')) {
    const token = authHeader.substring(7);
    try {
      const decoded = jwt.verify(token, config.security.jwtSecret);
      req.user = decoded;
      return next();
    } catch (err) {
      return res.status(401).json({ error: 'Invalid or expired API token.' });
    }
  }

  if (req.path.startsWith('/api')) {
    return res.status(401).json({ error: 'Unauthenticated.' });
  }

  return res.redirect('/auth/login?return_to=' + encodeURIComponent(req.originalUrl));
}

function requireAdmin(req, res, next) {
  const user = (req.session && req.session.user) || req.user;
  if (!user) {
    if (req.path.startsWith('/api')) return res.status(401).json({ error: 'Unauthenticated.' });
    return res.redirect('/auth/login');
  }

  if (user.root_admin !== 1 && !user.is_admin) {
    if (req.path.startsWith('/api')) return res.status(403).json({ error: 'Forbidden: Admin access required.' });
    return res.status(403).render('errors/403', { message: 'You do not have permission to access the Nova Admin Panel.' });
  }

  next();
}

function requireServerAccess(permission = null) {
  return async (req, res, next) => {
    const serverId = req.params.server || req.params.serverId;
    const user = (req.session && req.session.user) || req.user;

    if (!user) {
      return res.redirect('/auth/login');
    }

    const db = getDb();
    const server = await db.get('SELECT * FROM servers WHERE id = ?', [serverId]);

    if (!server) {
      if (req.path.startsWith('/api')) return res.status(404).json({ error: 'Server not found.' });
      return res.status(404).render('errors/404', { message: 'Server not found.' });
    }

    // Root admin or owner has full access
    if (user.root_admin === 1 || server.owner_id === user.id) {
      req.server = server;
      res.locals.server = server;
      res.locals.activeServer = server;
      res.locals.currentPath = req.originalUrl || req.path;
      return next();
    }

    // Check subuser permissions
    const subuser = await db.get(
      'SELECT * FROM server_subusers WHERE server_id = ? AND user_id = ?',
      [serverId, user.id]
    );

    if (subuser) {
      if (!permission) {
        req.server = server;
        res.locals.server = server;
        res.locals.activeServer = server;
        res.locals.currentPath = req.originalUrl || req.path;
        return next();
      }
      try {
        const perms = JSON.parse(subuser.permissions_json || '[]');
        if (perms.includes(permission) || perms.includes('*')) {
          req.server = server;
          res.locals.server = server;
          res.locals.activeServer = server;
          res.locals.currentPath = req.originalUrl || req.path;
          return next();
        }
      } catch (e) {}
    }

    if (req.path.startsWith('/api')) {
      return res.status(403).json({ error: 'Forbidden: You do not have permission to manage this server.' });
    }
    return res.status(403).render('errors/403', { message: 'You do not have permission to access this server.' });
  };
}

module.exports = {
  attachSettings,
  requireAuth,
  requireAdmin,
  requireServerAccess,
};
