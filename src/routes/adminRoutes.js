const express = require('express');
const router = express.Router();
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');
const crypto = require('crypto');
const multer = require('multer');
const { getDb } = require('../database');
const config = require('../config');
const dockerService = require('../docker/dockerService');
const mcjarsService = require('../services/mcjarsService');
const systemService = require('../services/systemService');
const themeService = require('../services/themeService');

const uploadJson = multer({ storage: multer.memoryStorage(), limits: { fileSize: 10 * 1024 * 1024 } });

// Multer for Logo and Favicon upload
const logoStorage = multer.diskStorage({
  destination: (req, file, cb) => {
    const dir = path.join(config.paths.public, 'assets/img');
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    cb(null, dir);
  },
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname);
    if (file.fieldname === 'logo_file') cb(null, `logo-${Date.now()}${ext}`);
    else if (file.fieldname === 'favicon_file') cb(null, `favicon-${Date.now()}${ext}`);
    else cb(null, file.originalname);
  }
});
const uploadBranding = multer({ storage: logoStorage });

// 01. ADMIN DASHBOARD
router.get('/', async (req, res) => {
  const db = getDb();
  const [totalUsers, totalServers, totalNodes, totalLocations, totalDbHosts] = await Promise.all([
    db.get('SELECT COUNT(*) as count FROM users'),
    db.get('SELECT COUNT(*) as count FROM servers'),
    db.get('SELECT COUNT(*) as count FROM nodes'),
    db.get('SELECT COUNT(*) as count FROM locations'),
    db.get('SELECT COUNT(*) as count FROM database_hosts'),
  ]);

  const sysInfo = await systemService.getSystemOverview();

  res.render('admin/dashboard', {
    user: req.session.user,
    stats: {
      users: totalUsers?.count || 0,
      servers: totalServers?.count || 0,
      nodes: totalNodes?.count || 0,
      locations: totalLocations?.count || 0,
      dbHosts: totalDbHosts?.count || 0,
    },
    system: sysInfo,
  });
});

// 02. USERS
router.get('/users', async (req, res) => {
  const db = getDb();
  const search = req.query['filter[email]'] || (req.query.filter && req.query.filter.email) || req.query.search || '';
  
  let query = `
    SELECT u.*, 
      (SELECT COUNT(*) FROM servers WHERE owner_id = u.id) as server_count,
      (SELECT COUNT(*) FROM server_subusers WHERE user_id = u.id) as subuser_of_count
    FROM users u
  `;
  const params = [];
  if (search && String(search).trim().length > 0) {
    query += ` WHERE u.email LIKE ? OR u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?`;
    const s = `%${String(search).trim()}%`;
    params.push(s, s, s, s);
  }
  query += ` ORDER BY u.created_at DESC`;

  const users = await db.query(query, params);
  res.render('admin/users', { 
    user: req.session.user, 
    users: users || [], 
    search,
    success: req.query.success || null,
    error: req.query.error || null
  });
});

router.get('/users/new', (req, res) => {
  res.render('admin/user_create', { 
    user: req.session.user, 
    error: req.query.error || null,
    old: {}
  });
});

router.post('/users/new', async (req, res) => {
  const db = getDb();
  let { first_name, last_name, name_first, name_last, username, email, password, confirm_password, root_admin, language } = req.body;
  first_name = (first_name || name_first || '').trim();
  last_name = (last_name || name_last || '').trim();
  username = (username || '').trim();
  email = (email || '').trim();

  if (!email || !username) {
    return res.render('admin/user_create', { 
      user: req.session.user, 
      error: 'Username and email are required.',
      old: req.body 
    });
  }

  // Auto-generate password if empty
  if (!password || password.trim().length === 0) {
    password = Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-8);
  } else if (confirm_password && password !== confirm_password) {
    return res.render('admin/user_create', { 
      user: req.session.user, 
      error: 'Passwords do not match.',
      old: req.body 
    });
  }

  try {
    const hashed = await bcrypt.hash(password, 10);
    await db.query(
      `INSERT INTO users (first_name, last_name, username, email, password, root_admin, language, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, 'active')`,
      [first_name, last_name, username, email, hashed, root_admin ? 1 : 0, language || 'en']
    );
    res.redirect('/admin/users?success=User+created+successfully');
  } catch (err) {
    res.render('admin/user_create', { 
      user: req.session.user, 
      error: err.message,
      old: req.body 
    });
  }
});

// View / Edit User Handler Helper
const handleUserView = async (req, res) => {
  const db = getDb();
  const userId = req.params.user;
  const targetUser = await db.get('SELECT * FROM users WHERE id = ?', [userId]);
  if (!targetUser) return res.status(404).render('errors/404', { message: 'User not found' });

  const servers = await db.query(`
    SELECT s.*, n.name as node_name, a.port, a.ip_alias
    FROM servers s
    LEFT JOIN nodes n ON s.node_id = n.id
    LEFT JOIN allocations a ON s.allocation_id = a.id
    WHERE s.owner_id = ?
    ORDER BY s.created_at DESC
  `, [targetUser.id]);

  let activeSessions = [];
  try {
    const rawSessions = await db.query('SELECT sid, sess, expired_at FROM sessions ORDER BY expired_at DESC LIMIT 50');
    for (const rs of (rawSessions || [])) {
      try {
        const parsed = JSON.parse(rs.sess);
        if (parsed.user && parsed.user.id == targetUser.id) {
          activeSessions.push({
            session_id: rs.sid,
            device_type: 'Desktop',
            platform: 'Linux / Web',
            browser: 'Browser Session',
            ip_address: targetUser.last_login_ip || '127.0.0.1',
            city: 'Localhost',
            country: 'US',
            is_vpn: false,
            last_active_at: new Date(rs.expired_at - 86400000).toLocaleString()
          });
        }
      } catch(e) {}
    }
  } catch(e) {}

  let loginHistory = [];
  try {
    loginHistory = await db.query(`
      SELECT * FROM activity_logs 
      WHERE user_id = ? 
      ORDER BY created_at DESC LIMIT 50
    `, [targetUser.id]);
  } catch(e) {}

  res.render('admin/user_edit', { 
    user: req.session.user, 
    targetUser, 
    servers: servers || [],
    activeSessions,
    loginHistory: loginHistory || [],
    currency: '$',
    success: req.query.success || null, 
    error: req.query.error || null 
  });
};

router.get('/users/:user', handleUserView);
router.get('/users/view/:user', handleUserView);

// Update User
const handleUserUpdate = async (req, res) => {
  const db = getDb();
  const userId = req.params.user;
  let { 
    first_name, last_name, name_first, name_last, 
    username, email, root_admin, password, 
    language, country, address, zip_code, credit,
    is_banned, ban_reason, suspended_until, suspension_reason
  } = req.body;

  first_name = (first_name || name_first || '').trim();
  last_name = (last_name || name_last || '').trim();
  username = (username || '').trim();
  email = (email || '').trim();

  let newStatus = 'active';
  if (is_banned === '1' || is_banned === 1) {
    newStatus = 'banned';
  } else if (suspended_until && new Date(suspended_until).getTime() > Date.now()) {
    newStatus = 'suspended';
  }

  try {
    if (password && password.trim().length > 0) {
      const hashed = await bcrypt.hash(password.trim(), 10);
      await db.query(`
        UPDATE users SET 
          first_name = ?, last_name = ?, username = ?, email = ?, 
          root_admin = ?, password = ?, language = ?,
          country = ?, address = ?, zip_code = ?, credit = ?,
          is_banned = ?, ban_reason = ?, suspended_until = ?, suspension_reason = ?,
          status = ?
        WHERE id = ?
      `, [
        first_name, last_name, username, email,
        root_admin ? 1 : 0, hashed, language || 'en',
        country || '', address || '', zip_code || '', parseFloat(credit) || 0.00,
        is_banned ? 1 : 0, ban_reason || null, suspended_until || null, suspension_reason || null,
        newStatus, userId
      ]);
    } else {
      await db.query(`
        UPDATE users SET 
          first_name = ?, last_name = ?, username = ?, email = ?, 
          root_admin = ?, language = ?,
          country = ?, address = ?, zip_code = ?, credit = ?,
          is_banned = ?, ban_reason = ?, suspended_until = ?, suspension_reason = ?,
          status = ?
        WHERE id = ?
      `, [
        first_name, last_name, username, email,
        root_admin ? 1 : 0, language || 'en',
        country || '', address || '', zip_code || '', parseFloat(credit) || 0.00,
        is_banned ? 1 : 0, ban_reason || null, suspended_until || null, suspension_reason || null,
        newStatus, userId
      ]);
    }
    res.redirect(`/admin/users/${userId}?success=User+updated+successfully`);
  } catch (err) {
    res.redirect(`/admin/users/${userId}?error=${encodeURIComponent(err.message)}`);
  }
};

router.post('/users/:user', handleUserUpdate);
router.all('/users/view/:user', (req, res, next) => {
  if (req.method === 'POST' || req.method === 'PATCH') {
    return handleUserUpdate(req, res);
  }
  next();
});

// Impersonate User
router.post(['/users/:user/impersonate', '/users/impersonate/:user'], async (req, res) => {
  const db = getDb();
  const targetUser = await db.get('SELECT * FROM users WHERE id = ?', [req.params.user]);
  if (!targetUser) return res.redirect('/admin/users?error=User+not+found');

  req.session.original_admin_id = req.session.user.id;
  req.session.user = targetUser;
  res.redirect('/dashboard');
});

// Revoke All Sessions
router.post(['/users/:user/session/revoke_all', '/users/session/revoke_all/:user'], async (req, res) => {
  const db = getDb();
  const userId = req.params.user;
  try {
    const rawSessions = await db.query('SELECT sid, sess FROM sessions');
    for (const rs of (rawSessions || [])) {
      try {
        const parsed = JSON.parse(rs.sess);
        if (parsed.user && parsed.user.id == userId) {
          await db.query('DELETE FROM sessions WHERE sid = ?', [rs.sid]);
        }
      } catch(e) {}
    }
  } catch(e) {}
  res.redirect(`/admin/users/${userId}?success=All+sessions+revoked`);
});

// Revoke Single Session
router.post('/users/:user/session/revoke', async (req, res) => {
  const db = getDb();
  const { session_id } = req.body;
  if (session_id) {
    try {
      await db.query('DELETE FROM sessions WHERE sid = ?', [session_id]);
    } catch(e) {}
  }
  res.redirect(`/admin/users/${req.params.user}?success=Session+revoked`);
});

router.post('/users/:user/suspend', async (req, res) => {
  const db = getDb();
  await db.query("UPDATE users SET status = 'suspended' WHERE id = ?", [req.params.user]);
  res.redirect('/admin/users');
});

router.post('/users/:user/unsuspend', async (req, res) => {
  const db = getDb();
  await db.query("UPDATE users SET status = 'active' WHERE id = ?", [req.params.user]);
  res.redirect('/admin/users');
});

router.all(['/users/:user/delete', '/users/view/:user/delete'], async (req, res) => {
  const db = getDb();
  const userId = req.params.user;
  const serverCount = await db.get('SELECT COUNT(*) as cnt FROM servers WHERE owner_id = ?', [userId]);
  if (serverCount && serverCount.cnt > 0) {
    return res.redirect(`/admin/users/${userId}?error=There+must+be+no+servers+associated+with+this+account+in+order+for+it+to+be+deleted.`);
  }

  await db.query('DELETE FROM users WHERE id = ?', [userId]);
  res.redirect('/admin/users?success=User+deleted+successfully');
});

// 05. SERVERS
router.get('/servers', async (req, res) => {
  const db = getDb();
  const search = req.query.search || req.query['filter[*]'] || (req.query.filter && req.query.filter['*']) || '';
  const nodeId = req.query.node_id || req.query['filter[node_id]'] || (req.query.filter && req.query.filter.node_id) || '';
  const status = req.query.status || req.query['filter[status]'] || (req.query.filter && req.query.filter.status) || '';
  const ownerId = req.query.owner_id || req.query['filter[owner_id]'] || (req.query.filter && req.query.filter.owner_id) || '';

  let query = `
    SELECT s.*, u.username as owner_username, n.name as node_name, a.port, a.ip_alias
    FROM servers s
    LEFT JOIN users u ON s.owner_id = u.id
    LEFT JOIN nodes n ON s.node_id = n.id
    LEFT JOIN allocations a ON s.allocation_id = a.id
    WHERE 1=1
  `;
  const params = [];

  if (search && String(search).trim().length > 0) {
    query += ` AND (s.name LIKE ? OR s.id LIKE ? OR s.uuid LIKE ? OR u.username LIKE ?)`;
    const sTerm = `%${String(search).trim()}%`;
    params.push(sTerm, sTerm, sTerm, sTerm);
  }

  if (nodeId && String(nodeId).trim().length > 0) {
    query += ` AND s.node_id = ?`;
    params.push(nodeId);
  }

  if (status && String(status).trim().length > 0) {
    query += ` AND s.status = ?`;
    params.push(status);
  }

  if (ownerId && String(ownerId).trim().length > 0) {
    query += ` AND s.owner_id = ?`;
    params.push(ownerId);
  }

  query += ` ORDER BY s.created_at DESC`;

  const [servers, nodes] = await Promise.all([
    db.query(query, params),
    db.query('SELECT * FROM nodes ORDER BY name ASC')
  ]);

  res.render('admin/servers', { 
    user: req.session.user, 
    servers: servers || [], 
    nodes: nodes || [],
    filters: { search, node_id: nodeId, status, owner_id: ownerId },
    success: req.query.success || null,
    error: req.query.error || null
  });
});

// Bulk Mass Actions
router.post('/admin/servers/bulk', async (req, res) => {
  return handleBulkServers(req, res);
});
router.post('/servers/bulk', async (req, res) => {
  return handleBulkServers(req, res);
});

async function handleBulkServers(req, res) {
  const db = getDb();
  const { action, server_ids, force, target_node_id } = req.body;
  const ids = Array.isArray(server_ids) ? server_ids : (server_ids ? [server_ids] : []);

  if (ids.length === 0) {
    return res.redirect('/admin/servers?error=No+servers+selected');
  }

  try {
    if (action === 'suspend') {
      for (const sid of ids) {
        try { await dockerService.stopServer(sid); } catch(e) {}
        await db.query("UPDATE servers SET status = 'suspended' WHERE id = ?", [sid]);
      }
    } else if (action === 'unsuspend') {
      for (const sid of ids) {
        await db.query("UPDATE servers SET status = 'offline' WHERE id = ?", [sid]);
      }
    } else if (action === 'delete') {
      for (const sid of ids) {
        const srv = await db.get('SELECT * FROM servers WHERE id = ?', [sid]);
        if (srv) {
          try { await dockerService.deleteServerContainer(srv.id); } catch(e) {}
          try {
            const serverDir = path.resolve(config.paths.servers, String(srv.owner_id), srv.id);
            if (fs.existsSync(serverDir)) fs.rmSync(serverDir, { recursive: true, force: true });
          } catch(e) {}
          await db.query('UPDATE allocations SET server_id = NULL WHERE server_id = ?', [srv.id]);
          await db.query('DELETE FROM server_variables WHERE server_id = ?', [srv.id]);
          await db.query('DELETE FROM server_databases WHERE server_id = ?', [srv.id]);
          await db.query('DELETE FROM backups WHERE server_id = ?', [srv.id]);
          await db.query('DELETE FROM schedules WHERE server_id = ?', [srv.id]);
          await db.query('DELETE FROM servers WHERE id = ?', [srv.id]);
        }
      }
    } else if (action === 'transfer' && target_node_id) {
      for (const sid of ids) {
        await db.query('UPDATE servers SET node_id = ? WHERE id = ?', [target_node_id, sid]);
      }
    }
    res.redirect('/admin/servers?success=Mass+action+completed+successfully');
  } catch (err) {
    res.redirect('/admin/servers?error=' + encodeURIComponent(err.message));
  }
}

router.get('/servers/new', async (req, res) => {
  const db = getDb();
  const [users, nodes, nests, eggs, allocations, dbHosts] = await Promise.all([
    db.query('SELECT id, username, email FROM users ORDER BY username ASC'),
    db.query('SELECT * FROM nodes ORDER BY name ASC'),
    db.query('SELECT * FROM nests ORDER BY name ASC'),
    db.query('SELECT * FROM eggs ORDER BY name ASC'),
    db.query('SELECT * FROM allocations WHERE server_id IS NULL ORDER BY port ASC'),
    db.query('SELECT * FROM database_hosts ORDER BY name ASC'),
  ]);

  res.render('admin/server_create', {
    user: req.session.user,
    users,
    nodes,
    nests,
    eggs,
    allocations,
    dbHosts,
    error: null,
  });
});

router.post('/servers/new', async (req, res) => {
  const db = getDb();
  const {
    name, description, owner_id, user_id, node_id, allocation_id,
    nest_id, egg_id, docker_image, startup_command, startup_cmd,
    cpu, cpu_limit, memory, memory_val, memory_unit, disk, disk_val, disk_unit,
    swap, io, database_limit, backup_limit,
    server_type, mc_jar_type, mc_jar_version, mc_software, mc_version,
    env_keys, env_vals
  } = req.body;

  const serverId = 'srv-' + crypto.randomBytes(4).toString('hex');
  const serverUuid = crypto.randomUUID();

  const targetOwnerId = owner_id || user_id || 1;
  const targetNodeId = node_id ? parseInt(node_id, 10) : 1;

  // Resolve allocation: pick specified allocation, or next free allocation
  let targetAllocId = allocation_id ? parseInt(allocation_id, 10) : null;
  if (!targetAllocId) {
    const freeAlloc = await db.get(
      'SELECT id FROM allocations WHERE (server_id IS NULL OR server_id = "") AND node_id = ? LIMIT 1',
      [targetNodeId]
    );
    if (freeAlloc) targetAllocId = freeAlloc.id;
  }

  // Calculate memory
  let finalMemory = 1024;
  if (memory_val !== undefined) {
    const mVal = parseInt(memory_val, 10);
    finalMemory = memory_unit === 'GB' ? mVal * 1024 : mVal;
  } else if (memory !== undefined) {
    finalMemory = parseInt(memory, 10);
  }

  // Calculate CPU
  const finalCpu = cpu_limit ? parseInt(cpu_limit, 10) : (cpu ? parseInt(cpu, 10) : 100);

  // Calculate Disk
  let finalDisk = 5120;
  if (disk_val !== undefined) {
    const dVal = parseInt(disk_val, 10);
    finalDisk = disk_unit === 'GB' ? dVal * 1024 : dVal;
  } else if (disk !== undefined) {
    finalDisk = parseInt(disk, 10);
  }

  // Determine Nest, Egg, Image, Startup
  let targetNestId = nest_id ? parseInt(nest_id, 10) : 1;
  let targetEggId = egg_id ? parseInt(egg_id, 10) : 1;
  let selectedImage = docker_image;
  let selectedStartup = startup_cmd || startup_command;

  const type = (server_type || 'minecraft').toLowerCase();
  const mcType = mc_jar_type || mc_software || 'paper';
  const mcVer = mc_jar_version || mc_version || '1.21.4';

  if (server_type) {
    if (type === 'minecraft') {
      targetNestId = 1;
      let jImg = 'ghcr.io/pterodactyl/yolks:java_21';
      if (mcVer) {
        const v = String(mcVer).trim();
        if (v.startsWith('26') || v.startsWith('27') || v.startsWith('1.22') || v.startsWith('1.23')) {
          jImg = 'ghcr.io/pterodactyl/yolks:java_25';
        } else if (v.startsWith('1.8') || v.startsWith('1.9') || v.startsWith('1.10') || v.startsWith('1.11') || v.startsWith('1.12')) {
          jImg = 'ghcr.io/pterodactyl/yolks:java_8';
        } else if (v.startsWith('1.13') || v.startsWith('1.14') || v.startsWith('1.15')) {
          jImg = 'ghcr.io/pterodactyl/yolks:java_11';
        } else if (v.startsWith('1.16')) {
          jImg = 'ghcr.io/pterodactyl/yolks:java_16';
        } else if (v.startsWith('1.17') || v.startsWith('1.18') || v.startsWith('1.19') || v.startsWith('1.20')) {
          if (v === '1.20.5' || v === '1.20.6') {
            jImg = 'ghcr.io/pterodactyl/yolks:java_21';
          } else {
            jImg = 'ghcr.io/pterodactyl/yolks:java_17';
          }
        } else {
          jImg = 'ghcr.io/pterodactyl/yolks:java_21';
        }
      }
      selectedImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : jImg;
      selectedStartup = startup_cmd || startup_command || 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui';
      const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [selectedImage]);
      if (matchingEgg) targetEggId = matchingEgg.id;
    } else if (type === 'nodejs') {
      targetNestId = 2;
      targetEggId = 9;
      selectedImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : 'ghcr.io/ptero-eggs/yolks:nodejs_20';
      selectedStartup = startup_cmd || startup_command || 'if [ -f package.json ]; then npm install; fi; npm start';
      const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [selectedImage]);
      if (matchingEgg) targetEggId = matchingEgg.id;
    } else if (type === 'python') {
      targetNestId = 3;
      targetEggId = 15;
      selectedImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : 'ghcr.io/ptero-eggs/yolks:python_3.12';
      selectedStartup = startup_cmd || startup_command || 'if [ -f requirements.txt ]; then pip install -r requirements.txt; fi; python3 app.py';
      const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [selectedImage]);
      if (matchingEgg) targetEggId = matchingEgg.id;
    }
  }

  // Fallback defaults from Egg if image/command still empty
  if (!selectedImage || !selectedStartup) {
    const egg = await db.get('SELECT * FROM eggs WHERE id = ?', [targetEggId]);
    if (egg) {
      if (!selectedImage) selectedImage = egg.docker_image;
      if (!selectedStartup) selectedStartup = egg.startup_command;
    }
  }

  try {
    await db.query(`
      INSERT INTO servers (id, uuid, name, description, owner_id, node_id, allocation_id, nest_id, egg_id, docker_image, startup_command, cpu, memory, disk, swap, io, status, database_limit, backup_limit)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', ?, ?)
    `, [
      serverId, serverUuid, (name || 'New Server').trim(), description || '', targetOwnerId, targetNodeId, targetAllocId,
      targetNestId, targetEggId, selectedImage || 'ghcr.io/pterodactyl/yolks:java_21',
      selectedStartup || 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui',
      finalCpu, finalMemory, finalDisk, swap || 0, io || 500,
      database_limit || 1, backup_limit || 3
    ]);

    // Mark allocation as assigned
    if (targetAllocId) {
      await db.query('UPDATE allocations SET server_id = ? WHERE id = ?', [serverId, targetAllocId]);
    }

    // Save Environment Variables
    const envObj = {};
    if (Array.isArray(env_keys) && Array.isArray(env_vals)) {
      for (let i = 0; i < env_keys.length; i++) {
        const k = (env_keys[i] || '').trim();
        const v = (env_vals[i] || '').trim();
        if (k) {
          envObj[k] = v;
          await db.query(
            'INSERT INTO server_variables (server_id, env_key, env_value) VALUES (?, ?, ?)',
            [serverId, k, v]
          );
        }
      }
    }

    // Set up directories
    const serverDir = path.resolve(config.paths.servers, String(targetOwnerId), serverId);
    fs.mkdirSync(serverDir, { recursive: true });

    // Download jar if Minecraft
    if (type === 'minecraft' && mcType) {
      mcjarsService.downloadServerJar(mcType, mcVer, serverDir).catch((e) => {
        console.warn('[mcjars] Background jar download warning:', e.message);
      });
    }

    // Provision Docker container
    const newServer = await db.get('SELECT * FROM servers WHERE id = ?', [serverId]);
    const alloc = targetAllocId ? await db.get('SELECT * FROM allocations WHERE id = ?', [targetAllocId]) : null;
    const egg = await db.get('SELECT * FROM eggs WHERE id = ?', [targetEggId]);
    await dockerService.createServerContainer(newServer, alloc, egg, envObj);

    res.redirect(`/server/${serverId}/console?created=1`);
  } catch (err) {
    console.error('Server creation failed:', err);
    res.redirect('/admin/servers/new?error=' + encodeURIComponent(err.message));
  }
});

// Server View Routes
router.get(['/servers/:server', '/servers/view/:server'], async (req, res) => {
  return res.redirect(`/admin/servers/view/${req.params.server}/details`);
});

// 1. Details
router.get('/servers/view/:server/details', async (req, res) => {
  const db = getDb();
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [req.params.server]);
  if (!server) return res.status(404).render('errors/404', { message: 'Server not found' });

  const users = await db.query('SELECT id, username, email FROM users ORDER BY username ASC');
  res.render('admin/view/details', {
    user: req.session.user,
    server,
    users: users || [],
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

const handleServerDetailsUpdate = async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const { name, external_id, owner_id, description, exp_date } = req.body;
  try {
    await db.query(`
      UPDATE servers SET 
        name = ?, external_id = ?, owner_id = ?, description = ?, exp_date = ?
      WHERE id = ?
    `, [
      (name || '').trim(), external_id || null, owner_id ? parseInt(owner_id, 10) : 1,
      description || '', exp_date || null, sid
    ]);
    res.redirect(`/admin/servers/view/${sid}/details?success=Details+updated+successfully`);
  } catch (err) {
    res.redirect(`/admin/servers/view/${sid}/details?error=${encodeURIComponent(err.message)}`);
  }
};

router.post('/servers/view/:server/details', handleServerDetailsUpdate);
router.post('/servers/:server', handleServerDetailsUpdate);

// 2. Build Configuration
router.get('/servers/view/:server/build', async (req, res) => {
  const db = getDb();
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [req.params.server]);
  if (!server) return res.status(404).render('errors/404', { message: 'Server not found' });

  const allocations = await db.query(
    'SELECT * FROM allocations WHERE node_id = ? AND (server_id IS NULL OR server_id = ?) ORDER BY port ASC',
    [server.node_id, server.id]
  );

  res.render('admin/view/build', {
    user: req.session.user,
    server,
    allocations: allocations || [],
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

router.post('/servers/view/:server/build', async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const {
    cpu, threads, memory, swap, disk, io, oom_disabled,
    database_limit, allocation_limit, backup_limit, allocation_id
  } = req.body;

  try {
    const srv = await db.get('SELECT * FROM servers WHERE id = ?', [sid]);
    if (!srv) return res.status(404).render('errors/404', { message: 'Server not found' });

    await db.query(`
      UPDATE servers SET 
        cpu = ?, threads = ?, memory = ?, swap = ?, disk = ?, io = ?, oom_disabled = ?,
        database_limit = ?, allocation_limit = ?, backup_limit = ?, allocation_id = ?
      WHERE id = ?
    `, [
      parseInt(cpu, 10) || 100, threads || null, parseInt(memory, 10) || 1024,
      parseInt(swap, 10) || 0, parseInt(disk, 10) || 5120, parseInt(io, 10) || 500,
      oom_disabled === '1' ? 1 : 0, parseInt(database_limit, 10) || 1,
      parseInt(allocation_limit, 10) || 1, parseInt(backup_limit, 10) || 3,
      allocation_id ? parseInt(allocation_id, 10) : srv.allocation_id,
      sid
    ]);

    // If allocation changed, update allocation records
    if (allocation_id && parseInt(allocation_id, 10) !== srv.allocation_id) {
      await db.query('UPDATE allocations SET server_id = NULL WHERE id = ?', [srv.allocation_id]);
      await db.query('UPDATE allocations SET server_id = ? WHERE id = ?', [sid, parseInt(allocation_id, 10)]);
    }

    res.redirect(`/admin/servers/view/${sid}/build?success=Build+configuration+updated+successfully`);
  } catch (err) {
    res.redirect(`/admin/servers/view/${sid}/build?error=${encodeURIComponent(err.message)}`);
  }
});

// 3. Manage
router.get('/servers/view/:server/manage', async (req, res) => {
  const db = getDb();
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [req.params.server]);
  if (!server) return res.status(404).render('errors/404', { message: 'Server not found' });

  const nodes = await db.query('SELECT * FROM nodes ORDER BY name ASC');
  res.render('admin/view/manage', {
    user: req.session.user,
    server,
    nodes: nodes || [],
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

router.post('/servers/view/:server/manage/reinstall', async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  try {
    await dockerService.deleteServerContainer(sid);
    await dockerService.startServer(sid, db);
    res.redirect(`/admin/servers/view/${sid}/manage?success=Server+reinstalled+successfully`);
  } catch (err) {
    res.redirect(`/admin/servers/view/${sid}/manage?error=${encodeURIComponent(err.message)}`);
  }
});

router.post('/servers/view/:server/manage/toggle', async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const srv = await db.get('SELECT installed FROM servers WHERE id = ?', [sid]);
  const newInst = (srv && srv.installed) ? 0 : 1;
  await db.query('UPDATE servers SET installed = ? WHERE id = ?', [newInst, sid]);
  res.redirect(`/admin/servers/view/${sid}/manage?success=Install+status+toggled`);
});

router.post('/servers/view/:server/manage/suspension', async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const { action } = req.body;
  if (action === 'suspend') {
    try { await dockerService.stopServer(sid); } catch(e) {}
    await db.query("UPDATE servers SET status = 'suspended' WHERE id = ?", [sid]);
  } else {
    await db.query("UPDATE servers SET status = 'offline' WHERE id = ?", [sid]);
  }
  res.redirect(`/admin/servers/view/${sid}/manage?success=Suspension+status+updated`);
});

router.post('/servers/view/:server/manage/transfer', async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const { target_node_id } = req.body;
  if (target_node_id) {
    await db.query('UPDATE servers SET node_id = ? WHERE id = ?', [parseInt(target_node_id, 10), sid]);
  }
  res.redirect(`/admin/servers/view/${sid}/manage?success=Server+transferred+to+node`);
});

// 4. Delete
router.get('/servers/view/:server/delete', async (req, res) => {
  const db = getDb();
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [req.params.server]);
  if (!server) return res.status(404).render('errors/404', { message: 'Server not found' });

  res.render('admin/view/delete', {
    user: req.session.user,
    server,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

const handleServerDelete = async (req, res) => {
  const db = getDb();
  const sid = req.params.server;
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [sid]);
  if (server) {
    try {
      await dockerService.deleteServerContainer(server.id);
    } catch (e) {
      console.warn('[Docker] Delete error:', e.message);
    }

    try {
      const serverDir = path.resolve(config.paths.servers, String(server.owner_id), server.id);
      if (fs.existsSync(serverDir)) {
        fs.rmSync(serverDir, { recursive: true, force: true });
      }
    } catch (e) {
      console.warn('[Disk] Delete error:', e.message);
    }

    await db.query('UPDATE allocations SET server_id = NULL WHERE server_id = ?', [server.id]);
    await db.query('DELETE FROM server_variables WHERE server_id = ?', [server.id]);
    await db.query('DELETE FROM server_databases WHERE server_id = ?', [server.id]);
    await db.query('DELETE FROM backups WHERE server_id = ?', [server.id]);
    await db.query('DELETE FROM schedules WHERE server_id = ?', [server.id]);
    await db.query('DELETE FROM servers WHERE id = ?', [server.id]);
  }
  res.redirect('/admin/servers?success=Server+deleted+successfully');
};

router.post('/servers/view/:server/delete', handleServerDelete);
router.post('/servers/:server/delete', handleServerDelete);

// 08. NODES
router.get('/nodes', async (req, res) => {
  const db = getDb();
  const nodes = await db.query(`
    SELECT n.*, l.short_code as location_code,
           (SELECT COUNT(*) FROM servers WHERE node_id = n.id) as server_count,
           (SELECT COUNT(*) FROM allocations WHERE node_id = n.id) as allocation_count
    FROM nodes n
    LEFT JOIN locations l ON n.location_id = l.id
    ORDER BY n.id ASC
  `);
  res.render('admin/nodes', { user: req.session.user, nodes });
});

router.get('/nodes/new', async (req, res) => {
  const db = getDb();
  const locations = await db.query('SELECT * FROM locations ORDER BY short_code ASC');
  res.render('admin/node_create', { user: req.session.user, locations });
});

router.post('/nodes/new', async (req, res) => {
  const db = getDb();
  const { name, description, location_id, fqdn, scheme, daemon_port, sftp_port, memory, disk } = req.body;
  await db.query(`
    INSERT INTO nodes (name, description, location_id, fqdn, scheme, daemon_port, sftp_port, memory, disk, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'online')
  `, [name, description, location_id || 1, fqdn || 'localhost', scheme || 'http', daemon_port || 3003, sftp_port || 3004, memory || 8192, disk || 51200]);
  res.redirect('/admin/nodes');
});

router.get('/nodes/:node', async (req, res) => {
  const db = getDb();
  const node = await db.get('SELECT * FROM nodes WHERE id = ?', [req.params.node]);
  const locations = await db.query('SELECT * FROM locations');
  const serverCount = await db.get('SELECT COUNT(*) as count FROM servers WHERE node_id = ?', [req.params.node]);
  const sysInfo = await systemService.getSystemOverview();

  res.render('admin/node_details', {
    user: req.session.user,
    node,
    locations,
    serverCount: serverCount?.count || 0,
    system: sysInfo,
  });
});

router.get('/nodes/:node/allocations', async (req, res) => {
  const db = getDb();
  const node = await db.get('SELECT * FROM nodes WHERE id = ?', [req.params.node]);
  const allocations = await db.query(`
    SELECT a.*, s.name as server_name
    FROM allocations a
    LEFT JOIN servers s ON a.server_id = s.id
    WHERE a.node_id = ?
    ORDER BY a.port ASC
  `, [req.params.node]);

  res.render('admin/node_allocations', {
    user: req.session.user,
    node,
    allocations,
  });
});

router.post('/nodes/:node/allocations', async (req, res) => {
  const db = getDb();
  const { ip_address, port, ip_alias } = req.body;
  await db.query(
    'INSERT INTO allocations (node_id, ip_address, ip_alias, port) VALUES (?, ?, ?, ?)',
    [req.params.node, ip_address || '0.0.0.0', ip_alias || '', port]
  );
  res.redirect(`/admin/nodes/${req.params.node}/allocations`);
});

router.post('/nodes/:node/allocations/bulk', async (req, res) => {
  const db = getDb();
  const { ip_address, port_start, port_end } = req.body;
  const start = parseInt(port_start, 10);
  const end = parseInt(port_end, 10);

  if (start > 0 && end >= start) {
    for (let p = start; p <= Math.min(end, start + 100); p++) {
      const exists = await db.get('SELECT id FROM allocations WHERE node_id = ? AND port = ?', [req.params.node, p]);
      if (!exists) {
        await db.query(
          'INSERT INTO allocations (node_id, ip_address, port) VALUES (?, ?, ?)',
          [req.params.node, ip_address || '0.0.0.0', p]
        );
      }
    }
  }
  res.redirect(`/admin/nodes/${req.params.node}/allocations`);
});

router.post('/allocations/:id/delete', async (req, res) => {
  const db = getDb();
  const alloc = await db.get('SELECT * FROM allocations WHERE id = ?', [req.params.id]);
  if (alloc) {
    await db.query('DELETE FROM allocations WHERE id = ?', [req.params.id]);
    return res.redirect(`/admin/nodes/${alloc.node_id}/allocations`);
  }
  res.redirect('/admin/nodes');
});

// 12. LOCATIONS
router.get('/locations', async (req, res) => {
  const db = getDb();
  const locations = await db.query(`
    SELECT l.*, (SELECT COUNT(*) FROM nodes WHERE location_id = l.id) as node_count
    FROM locations l
    ORDER BY l.id ASC
  `);
  res.render('admin/locations', { user: req.session.user, locations });
});

router.post('/locations', async (req, res) => {
  const db = getDb();
  const { short_code, description } = req.body;
  await db.query('INSERT INTO locations (short_code, description) VALUES (?, ?)', [short_code, description]);
  res.redirect('/admin/locations');
});

router.post('/locations/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM locations WHERE id = ?', [req.params.id]);
  res.redirect('/admin/locations');
});

// NESTS & EGGS
router.get('/nests', async (req, res) => {
  const db = getDb();
  const nests = await db.query(`
    SELECT n.*, (SELECT COUNT(*) FROM eggs WHERE nest_id = n.id) as egg_count
    FROM nests n
    ORDER BY n.id ASC
  `);
  res.render('admin/nests', { user: req.session.user, nests });
});

router.post('/nests', async (req, res) => {
  const db = getDb();
  await db.query('INSERT INTO nests (name, description) VALUES (?, ?)', [req.body.name, req.body.description]);
  res.redirect('/admin/nests');
});

router.get('/nests/:nest/eggs', async (req, res) => {
  const db = getDb();
  const nest = await db.get('SELECT * FROM nests WHERE id = ?', [req.params.nest]);
  const eggs = await db.query('SELECT * FROM eggs WHERE nest_id = ? ORDER BY id ASC', [req.params.nest]);
  res.render('admin/eggs', { user: req.session.user, nest, eggs });
});

router.post('/nests/:nest/eggs', async (req, res) => {
  const db = getDb();
  const { name, docker_image, startup_command } = req.body;
  await db.query(
    'INSERT INTO eggs (nest_id, name, docker_image, startup_command) VALUES (?, ?, ?, ?)',
    [req.params.nest, name, docker_image, startup_command]
  );
  res.redirect(`/admin/nests/${req.params.nest}/eggs`);
});

// 17. DATABASE HOSTS
router.get('/database', async (req, res) => {
  const db = getDb();
  const hosts = await db.query(`
    SELECT dh.*, n.name as node_name,
           (SELECT COUNT(*) FROM server_databases WHERE host_id = dh.id) as database_count
    FROM database_hosts dh
    LEFT JOIN nodes n ON dh.node_id = n.id
    ORDER BY dh.id ASC
  `);
  res.render('admin/database', { user: req.session.user, hosts });
});

router.get('/database/new', async (req, res) => {
  const db = getDb();
  const nodes = await db.query('SELECT * FROM nodes');
  res.render('admin/database_create', { user: req.session.user, nodes });
});

router.post('/database/new', async (req, res) => {
  const db = getDb();
  const { name, host, port, username, password, node_id, max_databases } = req.body;
  await db.query(
    `INSERT INTO database_hosts (name, host, port, username, password, node_id, max_databases)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [name, host, port || 3005, username, password, node_id || 1, max_databases || 100]
  );
  res.redirect('/admin/database');
});

router.post('/database/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM database_hosts WHERE id = ?', [req.params.id]);
  res.redirect('/admin/database');
});

// 19. APPLICATION API
router.get('/api', async (req, res) => {
  const db = getDb();
  const keys = await db.query(`
    SELECT a.*, u.username
    FROM api_keys a
    JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
  `);
  res.render('admin/api', { user: req.session.user, keys, newKey: req.query.key });
});

router.post('/api', async (req, res) => {
  const db = getDb();
  const { description } = req.body;
  const identifier = crypto.randomBytes(8).toString('hex');
  const secret = 'nova_admin_' + crypto.randomBytes(24).toString('hex');
  const hashed = await bcrypt.hash(secret, 10);

  await db.query(
    'INSERT INTO api_keys (user_id, identifier, token_hash, description, permissions_json) VALUES (?, ?, ?, ?, ?)',
    [req.session.user.id, identifier, hashed, description || 'Admin API Key', JSON.stringify(['admin:*'])]
  );

  res.redirect('/admin/api?key=' + encodeURIComponent(secret));
});

router.post('/api/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM api_keys WHERE id = ?', [req.params.id]);
  res.redirect('/admin/api');
});

// 20. ACTIVITY LOG
router.get('/activity', async (req, res) => {
  const db = getDb();
  const logs = await db.query(`
    SELECT a.*, u.username, s.name as server_name
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    LEFT JOIN servers s ON a.server_id = s.id
    ORDER BY a.created_at DESC
    LIMIT 100
  `);
  res.render('admin/activity', { user: req.session.user, logs });
});

// 21. PANEL SETTINGS (General, Branding with Logo upload/URL live preview, Favicon upload/URL live preview)
router.get('/settings', (req, res) => {
  res.render('admin/settings', { user: req.session.user, success: req.query.success });
});

router.post('/settings', uploadBranding.fields([{ name: 'logo_file', maxCount: 1 }, { name: 'favicon_file', maxCount: 1 }]), async (req, res) => {
  const db = getDb();
  const { panel_name, company_name, panel_url, panel_logo_url, panel_favicon_url, timezone, default_language } = req.body;

  let logoVal = panel_logo_url;
  if (req.files && req.files.logo_file && req.files.logo_file[0]) {
    logoVal = '/assets/img/' + req.files.logo_file[0].filename;
  }

  let favVal = panel_favicon_url;
  if (req.files && req.files.favicon_file && req.files.favicon_file[0]) {
    favVal = '/assets/img/' + req.files.favicon_file[0].filename;
  }

  const updates = [
    ['panel_name', panel_name],
    ['company_name', company_name],
    ['panel_url', panel_url],
    ['timezone', timezone],
    ['default_language', default_language],
  ];

  if (logoVal) updates.push(['panel_logo', logoVal]);
  if (favVal) updates.push(['panel_favicon', favVal]);

  for (const [k, v] of updates) {
    if (v !== undefined) {
      await db.query('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)', [k, v]);
    }
  }

  res.redirect('/admin/settings?success=Saved');
});

// 22. MAIL SETTINGS
router.get('/settings/mail', (req, res) => {
  res.render('admin/settings_mail', { user: req.session.user, success: req.query.success });
});

router.post('/settings/mail', async (req, res) => {
  const db = getDb();
  const { smtp_host, smtp_port, smtp_user, smtp_pass, smtp_encryption, smtp_from, smtp_from_name } = req.body;

  const mailUpdates = [
    ['smtp_host', smtp_host],
    ['smtp_port', smtp_port],
    ['smtp_user', smtp_user],
    ['smtp_pass', smtp_pass],
    ['smtp_encryption', smtp_encryption],
    ['smtp_from', smtp_from],
    ['smtp_from_name', smtp_from_name],
  ];

  for (const [k, v] of mailUpdates) {
    await db.query('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)', [k, v]);
  }

  res.redirect('/admin/settings/mail?success=Saved');
});

// 23. ADVANCED SETTINGS
router.get('/settings/advanced', (req, res) => {
  res.render('admin/settings_advanced', { user: req.session.user, success: req.query.success });
});

router.post('/settings/advanced', async (req, res) => {
  const db = getDb();
  const { maintenance_mode, maintenance_message, debug_mode } = req.body;

  await db.query('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)', ['maintenance_mode', maintenance_mode ? '1' : '0']);
  await db.query('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)', ['maintenance_message', maintenance_message]);
  await db.query('REPLACE INTO settings (`key`, `value`) VALUES (?, ?)', ['debug_mode', debug_mode ? '1' : '0']);

  res.redirect('/admin/settings/advanced?success=Saved');
});

// 24. SYSTEM INFORMATION
router.get('/system', async (req, res) => {
  const sysInfo = await systemService.getSystemOverview();
  const db = getDb();

  res.render('admin/system', {
    user: req.session.user,
    system: sysInfo,
    dbType: db ? db.type : 'sqlite',
    panelVersion: '1.0.0',
    nodeVersion: process.version,
  });
});

// ==========================================
// 25. APPEARANCE & THEMES (ARIX v2.1.3)
// ==========================================

// Main Appearance Overview
router.get('/appearance', async (req, res) => {
  const themes = await themeService.listThemes();
  res.render('admin/appearance/index', {
    user: req.session.user,
    themes: themes || [],
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

// Themes Overview alias
router.get('/appearance/themes', async (req, res) => {
  res.redirect('/admin/appearance');
});

// Arix Theme Direct Route
router.get('/appearance/arix', async (req, res) => {
  const theme = await themeService.getTheme('arix');
  if (!theme) {
    return res.redirect('/admin/appearance?error=Arix+theme+not+found');
  }
  res.render('admin/appearance/editor', {
    user: req.session.user,
    theme,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

// Arix Settings Alias
router.get('/appearance/arix/settings', async (req, res) => {
  res.redirect('/admin/appearance/arix');
});

// Activate Theme
router.post('/appearance/activate', async (req, res) => {
  const { theme_key } = req.body;
  if (!theme_key) {
    return res.redirect('/admin/appearance?error=No+theme+selected');
  }
  await themeService.activateTheme(theme_key);
  res.redirect(`/admin/appearance?success=Theme+${encodeURIComponent(theme_key)}+activated+successfully`);
});

// Deactivate Theme (switches to default)
router.post('/appearance/disable', async (req, res) => {
  const { theme_key } = req.body;
  await themeService.deactivateTheme(theme_key || 'arix');
  res.redirect('/admin/appearance?success=Theme+deactivated+successfully');
});

// Arix Theme Editor
router.get('/appearance/arix/editor', async (req, res) => {
  const theme = await themeService.getTheme('arix');
  if (!theme) {
    return res.redirect('/admin/appearance?error=Arix+theme+not+found');
  }
  res.render('admin/appearance/editor', {
    user: req.session.user,
    theme,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

// Save Arix Theme Configuration
router.post('/appearance/arix/save', async (req, res) => {
  try {
    let newConfig = {};
    if (req.body.config && typeof req.body.config === 'object') {
      newConfig = req.body.config;
    } else {
      newConfig = {
        general: req.body.general || {},
        colors: req.body.colors || {},
        sidebar: {
          ...req.body.sidebar,
          enabled: req.body.sidebar?.enabled === '1' || req.body.sidebar?.enabled === true || req.body.sidebar?.enabled === 'true' || req.body.sidebar?.enabled === 'on',
          collapsed: req.body.sidebar?.collapsed === '1' || req.body.sidebar?.collapsed === true || req.body.sidebar?.collapsed === 'true' || req.body.sidebar?.collapsed === 'on',
        },
        header: {
          ...req.body.header,
          enabled: req.body.header?.enabled === '1' || req.body.header?.enabled === true || req.body.header?.enabled === 'true' || req.body.header?.enabled === 'on',
          user_menu: req.body.header?.user_menu === '1' || req.body.header?.user_menu === true || req.body.header?.user_menu === 'true' || req.body.header?.user_menu === 'on',
          notifications: req.body.header?.notifications === '1' || req.body.header?.notifications === true || req.body.header?.notifications === 'true' || req.body.header?.notifications === 'on',
          search: req.body.header?.search === '1' || req.body.header?.search === true || req.body.header?.search === 'true' || req.body.header?.search === 'on',
          mobile_menu: req.body.header?.mobile_menu === '1' || req.body.header?.mobile_menu === true || req.body.header?.mobile_menu === 'true' || req.body.header?.mobile_menu === 'on',
        },
        cards: {
          ...req.body.cards,
          hover_animation: req.body.cards?.hover_animation === '1' || req.body.cards?.hover_animation === true || req.body.cards?.hover_animation === 'true' || req.body.cards?.hover_animation === 'on',
        },
        buttons: req.body.buttons || {},
        typography: req.body.typography || {},
        layout: req.body.layout || {},
      };
    }

    await themeService.saveThemeConfig('arix', newConfig);

    if (req.xhr || req.headers.accept?.includes('application/json')) {
      return res.json({ success: true, message: 'Arix theme configuration saved.' });
    }
    res.redirect('/admin/appearance/arix/editor?success=Arix+theme+saved+successfully');
  } catch (err) {
    console.error('[Admin] Failed saving theme config:', err);
    if (req.xhr || req.headers.accept?.includes('application/json')) {
      return res.status(500).json({ success: false, error: err.message });
    }
    res.redirect(`/admin/appearance/arix/editor?error=${encodeURIComponent(err.message)}`);
  }
});

// Reset Arix Theme to v2.1.3 Defaults
router.post('/appearance/arix/reset', async (req, res) => {
  try {
    await themeService.resetTheme('arix');
    res.redirect('/admin/appearance?success=Arix+Theme+reset+to+original+v2.1.3+defaults+successfully');
  } catch (err) {
    console.error('[Admin] Failed resetting theme:', err);
    res.redirect(`/admin/appearance?error=${encodeURIComponent(err.message)}`);
  }
});

// Export Arix Theme Config
router.get('/appearance/arix/export', async (req, res) => {
  try {
    const exported = await themeService.exportTheme('arix');
    if (!exported) {
      return res.redirect('/admin/appearance?error=Theme+not+found');
    }
    res.setHeader('Content-Type', 'application/json');
    res.setHeader('Content-Disposition', 'attachment; filename="arix-theme-config.json"');
    res.send(JSON.stringify(exported, null, 2));
  } catch (err) {
    console.error('[Admin] Failed exporting theme:', err);
    res.redirect('/admin/appearance?error=Failed+to+export+theme');
  }
});

// Import Arix Theme Config
router.post('/appearance/arix/import', uploadJson.single('config_file'), async (req, res) => {
  try {
    if (!req.file || !req.file.buffer) {
      return res.redirect('/admin/appearance?error=No+JSON+file+uploaded');
    }
    const jsonStr = req.file.buffer.toString('utf8');
    const parsed = JSON.parse(jsonStr);
    await themeService.importTheme('arix', parsed);
    res.redirect('/admin/appearance?success=Arix+Theme+configuration+imported+successfully');
  } catch (err) {
    console.error('[Admin] Failed importing theme:', err);
    res.redirect(`/admin/appearance?error=${encodeURIComponent(err.message || 'Failed to import theme configuration')}`);
  }
});

// Page Editor
router.get('/appearance/arix/pages', async (req, res) => {
  const theme = await themeService.getTheme('arix');
  const availablePages = themeService.getAvailablePages();
  res.render('admin/appearance/pages', {
    user: req.session.user,
    theme,
    availablePages,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

// Save Page Customizations
router.post('/appearance/arix/pages/save', async (req, res) => {
  try {
    const pagesPayload = {};
    for (const [routePath, p] of Object.entries(req.body.pages || {})) {
      pagesPayload[routePath] = {
        ...p,
        enabled: p.enabled === '1' || p.enabled === true || p.enabled === 'true',
        show_header: p.show_header === '1' || p.show_header === true || p.show_header === 'true',
        show_sidebar: p.show_sidebar === '1' || p.show_sidebar === true || p.show_sidebar === 'true',
      };
    }
    await themeService.savePagesConfig('arix', pagesPayload);
    res.redirect('/admin/appearance/arix/pages?success=Page+settings+saved+successfully');
  } catch (err) {
    console.error('[Admin] Failed saving page configs:', err);
    res.redirect(`/admin/appearance/arix/pages?error=${encodeURIComponent(err.message)}`);
  }
});

// Reset Page Customizations
router.post('/appearance/arix/pages/reset', async (req, res) => {
  try {
    await themeService.savePagesConfig('arix', themeService.getDefaultPagesConfig());
    res.redirect('/admin/appearance/arix/pages?success=Page+customizations+reset+to+defaults');
  } catch (err) {
    console.error('[Admin] Failed resetting page configs:', err);
    res.redirect(`/admin/appearance/arix/pages?error=${encodeURIComponent(err.message)}`);
  }
});

// Component Editor
router.get('/appearance/arix/components', async (req, res) => {
  const theme = await themeService.getTheme('arix');
  res.render('admin/appearance/components', {
    user: req.session.user,
    theme,
    success: req.query.success || null,
    error: req.query.error || null,
  });
});

// Save Component Customizations
router.post('/appearance/arix/components/save', async (req, res) => {
  try {
    const rawComps = req.body.components || {};
    const parsedComps = {};
    for (const [k, v] of Object.entries(rawComps)) {
      parsedComps[k] = { ...v };
      for (const [subKey, subVal] of Object.entries(v)) {
        if (subVal === '1' || subVal === 'on' || subVal === 'true') {
          parsedComps[k][subKey] = true;
        }
      }
    }
    await themeService.saveComponentsConfig('arix', parsedComps);
    res.redirect('/admin/appearance/arix/components?success=Component+settings+saved+successfully');
  } catch (err) {
    console.error('[Admin] Failed saving component configs:', err);
    res.redirect(`/admin/appearance/arix/components?error=${encodeURIComponent(err.message)}`);
  }
});

// Reset Component Customizations
router.post('/appearance/arix/components/reset', async (req, res) => {
  try {
    await themeService.saveComponentsConfig('arix', themeService.getDefaultComponentsConfig());
    res.redirect('/admin/appearance/arix/components?success=Component+customizations+reset+to+defaults');
  } catch (err) {
    console.error('[Admin] Failed resetting component configs:', err);
    res.redirect(`/admin/appearance/arix/components?error=${encodeURIComponent(err.message)}`);
  }
});

module.exports = router;
