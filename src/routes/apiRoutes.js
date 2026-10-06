const express = require('express');
const router = express.Router();
const crypto = require('crypto');
const path = require('path');
const fs = require('fs');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

const config = require('../config');
const { getDb } = require('../database');
const dockerService = require('../docker/dockerService');
const mcjarsService = require('../services/mcjarsService');
const systemService = require('../services/systemService');

// 1. Public Settings
router.get('/admin/settings/public', async (req, res) => {
  const db = getDb();
  try {
    const rows = await db.query('SELECT `key`, `value` FROM settings');
    const settings = {};
    if (rows) {
      for (const r of rows) settings[r.key] = r.value;
    }
    res.json({ success: true, settings });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// 2. Auth Login (JWT Token)
router.post('/auth/login', async (req, res) => {
  const { username, password } = req.body;
  const db = getDb();
  try {
    const user = await db.get(
      'SELECT * FROM users WHERE username = ? OR email = ?',
      [username, username]
    );
    if (!user) {
      return res.status(401).json({ success: false, error: 'Invalid username or email.' });
    }

    const isMatch = await bcrypt.compare(password, user.password);
    if (!isMatch) {
      return res.status(401).json({ success: false, error: 'Incorrect password.' });
    }

    const token = jwt.sign(
      { id: user.id, username: user.username, email: user.email, root_admin: user.root_admin },
      config.security.jwtSecret,
      { expiresIn: '7d' }
    );

    res.json({
      success: true,
      token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        root_admin: user.root_admin
      }
    });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// 3. Admin Users
router.get('/admin/users', async (req, res) => {
  const db = getDb();
  try {
    const users = await db.query('SELECT id, first_name, last_name, username, email, root_admin, status FROM users');
    res.json({ success: true, users });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// 4. Admin Nodes
router.get('/admin/nodes', async (req, res) => {
  const db = getDb();
  try {
    const nodes = await db.query('SELECT * FROM nodes');
    res.json({ success: true, nodes });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// 5. Admin Network Allocations
router.get('/admin/network', async (req, res) => {
  const db = getDb();
  try {
    const rows = await db.query(`
      SELECT id, node_id, ip_address as ip, port, notes,
             (CASE WHEN server_id IS NOT NULL AND server_id != '' THEN 1 ELSE 0 END) as assigned
      FROM allocations
      ORDER BY id ASC
    `);
    res.json({ success: true, allocations: rows });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// 6. System overview API
router.get('/system', async (req, res) => {
  const stats = await systemService.getSystemOverview();
  res.json({ success: true, data: stats });
});

// 7. mcjars.app Software types
router.get('/mcjars/types', async (req, res) => {
  try {
    const types = await mcjarsService.getTypes();
    res.json({ success: true, data: types });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// 8. mcjars.app Builds and versions
router.get('/mcjars/builds/:type', async (req, res) => {
  try {
    const builds = await mcjarsService.getBuilds(req.params.type);
    res.json({ success: true, data: builds });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// 9. Server list
router.get('/servers', async (req, res) => {
  const db = getDb();
  const servers = await db.query('SELECT id, name, status, cpu, memory, disk, node_id, owner_id FROM servers');
  res.json({ success: true, data: servers });
});

// 10. Server details
router.get('/servers/:id', async (req, res) => {
  const db = getDb();
  const server = await db.get('SELECT * FROM servers WHERE id = ?', [req.params.id]);
  if (!server) return res.status(404).json({ error: 'Server not found' });
  res.json({ success: true, data: server });
});

// 11. Deploy Server Instance
router.post('/servers', async (req, res) => {
  const db = getDb();
  const {
    name, description, server_type,
    memory_mb, cpu_limit, disk_mb,
    node_id, allocation_id, user_id,
    docker_image, startup_cmd,
    env_vars, mc_jar_type, mc_jar_version
  } = req.body;

  if (!name || !name.trim()) {
    return res.status(400).json({ success: false, error: 'Server name is required.' });
  }

  const serverId = 'srv-' + crypto.randomBytes(4).toString('hex');
  const serverUuid = crypto.randomUUID();
  const targetNodeId = node_id ? parseInt(node_id, 10) : 1;
  const targetOwnerId = user_id ? parseInt(user_id, 10) : 1;

  // Resolve allocation: pick specified allocation, or next free allocation
  let targetAllocId = allocation_id ? parseInt(allocation_id, 10) : null;
  if (!targetAllocId) {
    const freeAlloc = await db.get(
      'SELECT id FROM allocations WHERE (server_id IS NULL OR server_id = "") AND node_id = ? LIMIT 1',
      [targetNodeId]
    );
    if (freeAlloc) targetAllocId = freeAlloc.id;
  }

  // Determine Egg and Platform settings
  let targetNestId = 1;
  let targetEggId = 1;
  let finalImage = docker_image;
  let finalStartup = startup_cmd;

  const type = (server_type || 'minecraft').toLowerCase();
  if (type === 'minecraft') {
    targetNestId = 1;
    let jImg = 'ghcr.io/pterodactyl/yolks:java_21';
    if (mc_jar_version) {
      const v = String(mc_jar_version).trim();
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
    finalImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : jImg;
    finalStartup = startup_cmd || 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui';

    const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [finalImage]);
    if (matchingEgg) targetEggId = matchingEgg.id;
  } else if (type === 'nodejs') {
    targetNestId = 2;
    targetEggId = 9;
    finalImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : 'ghcr.io/ptero-eggs/yolks:nodejs_20';
    finalStartup = startup_cmd || 'if [ -f package.json ]; then npm install; fi; npm start';
    const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [finalImage]);
    if (matchingEgg) targetEggId = matchingEgg.id;
  } else if (type === 'python') {
    targetNestId = 3;
    targetEggId = 15;
    finalImage = (docker_image && String(docker_image).trim()) ? String(docker_image).trim() : 'ghcr.io/ptero-eggs/yolks:python_3.12';
    finalStartup = startup_cmd || 'if [ -f requirements.txt ]; then pip install -r requirements.txt; fi; python3 app.py';
    const matchingEgg = await db.get('SELECT id FROM eggs WHERE docker_image = ? LIMIT 1', [finalImage]);
    if (matchingEgg) targetEggId = matchingEgg.id;
  }

  const mem = memory_mb ? parseInt(memory_mb, 10) : 2048;
  const cpu = cpu_limit ? parseInt(cpu_limit, 10) : 100;
  const disk = disk_mb ? parseInt(disk_mb, 10) : 10240;

  try {
    await db.query(`
      INSERT INTO servers (id, uuid, name, description, owner_id, node_id, allocation_id, nest_id, egg_id, docker_image, startup_command, cpu, memory, disk, swap, io, status, database_limit, backup_limit)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 500, 'offline', 1, 3)
    `, [
      serverId, serverUuid, name.trim(), description || '', targetOwnerId, targetNodeId, targetAllocId,
      targetNestId, targetEggId, finalImage, finalStartup, cpu, mem, disk
    ]);

    if (targetAllocId) {
      await db.query('UPDATE allocations SET server_id = ? WHERE id = ?', [serverId, targetAllocId]);
    }

    // Save Environment Variables
    if (env_vars && typeof env_vars === 'object') {
      for (const [ek, ev] of Object.entries(env_vars)) {
        await db.query(
          'INSERT INTO server_variables (server_id, env_key, env_value) VALUES (?, ?, ?)',
          [serverId, ek, String(ev)]
        );
      }
    }

    // Set up server filesystem directory
    const serverDir = path.resolve(config.paths.servers, String(targetOwnerId), serverId);
    fs.mkdirSync(serverDir, { recursive: true });

    // Minecraft auto download
    if (type === 'minecraft') {
      const jarType = mc_jar_type || 'paper';
      const jarVer = mc_jar_version || 'latest';
      mcjarsService.downloadServerJar(jarType, jarVer, serverDir).catch((e) => {
        console.warn('[mcjars] Background jar download warning:', e.message);
      });
    }

    // Provision Docker container
    const newServer = await db.get('SELECT * FROM servers WHERE id = ?', [serverId]);
    const alloc = targetAllocId ? await db.get('SELECT * FROM allocations WHERE id = ?', [targetAllocId]) : null;
    const egg = await db.get('SELECT * FROM eggs WHERE id = ?', [targetEggId]);
    await dockerService.createServerContainer(newServer, alloc, egg, env_vars || {});

    res.json({
      success: true,
      serverId,
      uuid: serverUuid,
      message: 'Server deployed successfully.'
    });
  } catch (err) {
    console.error('Server deployment error:', err);
    res.status(500).json({ success: false, error: err.message });
  }
});

// 12. Live Stats
router.get('/servers/:id/stats', async (req, res) => {
  try {
    const stats = await dockerService.getServerStats(req.params.id);
    res.json({ success: true, data: stats });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// 13. Power Actions
router.post('/servers/:id/power', async (req, res) => {
  const { action } = req.body;
  const db = getDb();
  try {
    let result = null;
    if (action === 'start') result = await dockerService.startServer(req.params.id, db);
    else if (action === 'stop') result = await dockerService.stopServer(req.params.id, db);
    else if (action === 'restart') result = await dockerService.restartServer(req.params.id, db);
    else if (action === 'kill') result = await dockerService.killServer(req.params.id, db);
    else return res.status(400).json({ error: 'Invalid power action' });

    res.json({ success: true, data: result });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// 14. Send Command
router.post('/servers/:id/command', async (req, res) => {
  const { command } = req.body;
  if (!command) return res.status(400).json({ error: 'Command required' });
  try {
    const output = await dockerService.sendCommand(req.params.id, command);
    res.json({ success: true, data: { output } });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
