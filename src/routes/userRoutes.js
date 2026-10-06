const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const { getDb } = require('../database');
const dockerService = require('../docker/dockerService');

router.get('/dashboard', async (req, res) => {
  const db = getDb();
  const userId = req.session.user.id;
  const isAdmin = req.session.user.root_admin === 1;

  let servers = [];
  if (isAdmin) {
    servers = await db.query(`
      SELECT s.*, n.name as node_name, a.port as primary_port, a.ip_address, u.username as owner_username
      FROM servers s
      LEFT JOIN nodes n ON s.node_id = n.id
      LEFT JOIN allocations a ON s.allocation_id = a.id
      LEFT JOIN users u ON s.owner_id = u.id
      ORDER BY s.created_at DESC
    `);
  } else {
    servers = await db.query(`
      SELECT s.*, n.name as node_name, a.port as primary_port, a.ip_address, u.username as owner_username
      FROM servers s
      LEFT JOIN nodes n ON s.node_id = n.id
      LEFT JOIN allocations a ON s.allocation_id = a.id
      LEFT JOIN users u ON s.owner_id = u.id
      WHERE s.owner_id = ? OR s.id IN (SELECT server_id FROM server_subusers WHERE user_id = ?)
      ORDER BY s.created_at DESC
    `, [userId, userId]);
  }

  // Get live stats for top servers
  let onlineCount = 0;
  let offlineCount = 0;
  let totalCpu = 0;
  let totalMemory = 0;
  let totalDisk = 0;

  for (const s of servers) {
    if (s.status === 'online') {
      onlineCount++;
    } else {
      offlineCount++;
    }
    totalCpu += (s.cpu || 100);
    totalMemory += (s.memory || 1024);
    totalDisk += (s.disk || 5120);
  }

  res.render('user/dashboard', {
    user: req.session.user,
    servers,
    stats: {
      total: servers.length,
      online: onlineCount,
      offline: offlineCount,
      totalCpu,
      totalMemory,
      totalDisk,
    },
    message: req.query.msg || null,
  });
});

router.get('/servers', async (req, res) => {
  const db = getDb();
  const userId = req.session.user.id;
  const isAdmin = req.session.user.root_admin === 1;

  let servers = [];
  if (isAdmin) {
    servers = await db.query(`
      SELECT s.*, n.name as node_name, a.port as primary_port, a.ip_address, u.username as owner_username
      FROM servers s
      LEFT JOIN nodes n ON s.node_id = n.id
      LEFT JOIN allocations a ON s.allocation_id = a.id
      LEFT JOIN users u ON s.owner_id = u.id
      ORDER BY s.created_at DESC
    `);
  } else {
    servers = await db.query(`
      SELECT s.*, n.name as node_name, a.port as primary_port, a.ip_address, u.username as owner_username
      FROM servers s
      LEFT JOIN nodes n ON s.node_id = n.id
      LEFT JOIN allocations a ON s.allocation_id = a.id
      LEFT JOIN users u ON s.owner_id = u.id
      WHERE s.owner_id = ? OR s.id IN (SELECT server_id FROM server_subusers WHERE user_id = ?)
      ORDER BY s.created_at DESC
    `, [userId, userId]);
  }

  res.render('user/servers', {
    user: req.session.user,
    servers,
  });
});

// Account Profile
router.get('/account', async (req, res) => {
  const db = getDb();
  const user = await db.get('SELECT * FROM users WHERE id = ?', [req.session.user.id]);
  res.render('account/profile', {
    user,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

router.post('/account', async (req, res) => {
  const { first_name, last_name, email } = req.body;
  const db = getDb();
  try {
    await db.query(
      'UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?',
      [first_name, last_name, email, req.session.user.id]
    );
    req.session.user.first_name = first_name;
    req.session.user.last_name = last_name;
    req.session.user.email = email;
    res.redirect('/account?success=' + encodeURIComponent('Profile details updated successfully.'));
  } catch (err) {
    res.redirect('/account?error=' + encodeURIComponent('Failed to update profile: ' + err.message));
  }
});

// Account Security
router.get('/account/security', async (req, res) => {
  const db = getDb();
  const user = await db.get('SELECT * FROM users WHERE id = ?', [req.session.user.id]);
  res.render('account/security', {
    user,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

router.post('/account/security/password', async (req, res) => {
  const { current_password, new_password, confirm_password } = req.body;
  if (new_password !== confirm_password) {
    return res.redirect('/account/security?error=' + encodeURIComponent('Passwords do not match.'));
  }

  const db = getDb();
  const user = await db.get('SELECT * FROM users WHERE id = ?', [req.session.user.id]);
  const match = await bcrypt.compare(current_password, user.password);
  if (!match) {
    return res.redirect('/account/security?error=' + encodeURIComponent('Current password is incorrect.'));
  }

  const hashed = await bcrypt.hash(new_password, 10);
  await db.query('UPDATE users SET password = ? WHERE id = ?', [hashed, user.id]);
  res.redirect('/account/security?success=' + encodeURIComponent('Password updated successfully.'));
});

// API Keys
router.get('/account/api', async (req, res) => {
  const db = getDb();
  const keys = await db.query('SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC', [req.session.user.id]);
  res.render('account/api', {
    user: req.session.user,
    keys,
    newKeySecret: req.query.key || null,
  });
});

router.post('/account/api', async (req, res) => {
  const { description } = req.body;
  const db = getDb();
  const identifier = crypto.randomBytes(8).toString('hex');
  const secret = 'nova_' + crypto.randomBytes(24).toString('hex');
  const hashed = await bcrypt.hash(secret, 10);

  await db.query(
    'INSERT INTO api_keys (user_id, identifier, token_hash, description, permissions_json) VALUES (?, ?, ?, ?, ?)',
    [req.session.user.id, identifier, hashed, description || 'Personal API Token', JSON.stringify(['*'])]
  );

  res.redirect('/account/api?key=' + encodeURIComponent(secret));
});

router.post('/account/api/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM api_keys WHERE id = ? AND user_id = ?', [req.params.id, req.session.user.id]);
  res.redirect('/account/api');
});

// SSH Keys
router.get('/account/ssh-keys', async (req, res) => {
  const db = getDb();
  const keys = await db.query('SELECT * FROM ssh_keys WHERE user_id = ? ORDER BY created_at DESC', [req.session.user.id]);
  res.render('account/ssh_keys', {
    user: req.session.user,
    keys,
  });
});

router.post('/account/ssh-keys', async (req, res) => {
  const { name, public_key } = req.body;
  const db = getDb();
  const fingerprint = crypto.createHash('md5').update(public_key.trim()).digest('hex').match(/.{1,2}/g).join(':');

  await db.query(
    'INSERT INTO ssh_keys (user_id, name, public_key, fingerprint) VALUES (?, ?, ?, ?)',
    [req.session.user.id, name || 'SSH Key', public_key.trim(), fingerprint]
  );

  res.redirect('/account/ssh-keys');
});

router.post('/account/ssh-keys/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM ssh_keys WHERE id = ? AND user_id = ?', [req.params.id, req.session.user.id]);
  res.redirect('/account/ssh-keys');
});

// Account Activity
router.get('/account/activity', async (req, res) => {
  const db = getDb();
  const logs = await db.query(
    'SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 50',
    [req.session.user.id]
  );
  res.render('account/activity', {
    user: req.session.user,
    logs,
  });
});

module.exports = router;
