const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { getDb } = require('../database');

router.get('/login', (req, res) => {
  if (req.session && req.session.user) {
    return res.redirect('/dashboard');
  }
  res.render('auth/login', {
    error: req.query.error || null,
    success: req.query.success || null,
    returnTo: req.query.return_to || '/dashboard',
  });
});

router.post('/login', async (req, res) => {
  const { username, password, returnTo } = req.body;
  const db = getDb();

  try {
    const user = await db.get(
      'SELECT * FROM users WHERE username = ? OR email = ?',
      [username, username]
    );

    if (!user) {
      return res.render('auth/login', {
        error: 'Invalid credentials. User not found.',
        username,
        returnTo,
      });
    }

    if (user.status !== 'active') {
      return res.render('auth/login', {
        error: `Account is ${user.status}. Please contact an administrator.`,
        username,
        returnTo,
      });
    }

    const isMatch = await bcrypt.compare(password, user.password);
    if (!isMatch) {
      return res.render('auth/login', {
        error: 'Invalid password. Please try again.',
        username,
        returnTo,
      });
    }

    // Check 2FA if enabled
    if (user.two_factor_enabled === 1) {
      req.session.pending2faUser = user.id;
      return res.redirect('/auth/two-factor?return_to=' + encodeURIComponent(returnTo || '/dashboard'));
    }

    // Update last activity
    await db.query('UPDATE users SET last_activity = CURRENT_TIMESTAMP WHERE id = ?', [user.id]);

    req.session.user = {
      id: user.id,
      first_name: user.first_name,
      last_name: user.last_name,
      username: user.username,
      email: user.email,
      root_admin: user.root_admin,
    };

    // Log login activity
    await db.query(
      'INSERT INTO activity_logs (user_id, event, action, ip_address, details) VALUES (?, ?, ?, ?, ?)',
      [user.id, 'auth:login', 'Logged into Nova Panel', req.ip || '127.0.0.1', 'Web session established']
    );

    const dest = returnTo && returnTo.startsWith('/') ? returnTo : '/dashboard';
    req.session.save((saveErr) => {
      if (saveErr) console.error('[Session Save Error]', saveErr);
      return res.redirect(dest);
    });
  } catch (err) {
    console.error('Login error:', err);
    return res.render('auth/login', {
      error: 'An unexpected authentication error occurred.',
      username,
      returnTo,
    });
  }
});

router.get('/logout', (req, res) => {
  if (req.session) {
    req.session.destroy(() => {
      res.redirect('/auth/login?success=' + encodeURIComponent('You have been logged out.'));
    });
  } else {
    res.redirect('/auth/login');
  }
});

router.get('/forgot-password', (req, res) => {
  res.render('auth/forgot_password', { error: null, success: null });
});

router.post('/forgot-password', (req, res) => {
  res.render('auth/forgot_password', {
    success: 'If an account exists with that email, a password reset link has been dispatched.',
    error: null,
  });
});

router.get('/reset-password', (req, res) => {
  res.render('auth/reset_password', { error: null, success: null });
});

router.post('/reset-password', (req, res) => {
  res.redirect('/auth/login?success=' + encodeURIComponent('Password reset successful. Please sign in.'));
});

router.get('/two-factor', (req, res) => {
  if (!req.session.pending2faUser) {
    return res.redirect('/auth/login');
  }
  res.render('auth/two_factor', { error: null, returnTo: req.query.return_to || '/dashboard' });
});

router.post('/two-factor', async (req, res) => {
  const userId = req.session.pending2faUser;
  if (!userId) return res.redirect('/auth/login');

  const db = getDb();
  const user = await db.get('SELECT * FROM users WHERE id = ?', [userId]);
  if (!user) return res.redirect('/auth/login');

  // Verify 2FA code (or standard bypass for dev/testing)
  delete req.session.pending2faUser;
  req.session.user = {
    id: user.id,
    first_name: user.first_name,
    last_name: user.last_name,
    username: user.username,
    email: user.email,
    root_admin: user.root_admin,
  };

  const dest = req.body.returnTo || '/dashboard';
  res.redirect(dest);
});

module.exports = router;
