const express = require('express');
const router = express.Router({ mergeParams: true });
const path = require('path');
const fs = require('fs');
const multer = require('multer');
const archiver = require('archiver');
const unzipper = require('unzipper');
const crypto = require('crypto');
const { getDb } = require('../database');
const config = require('../config');
const dockerService = require('../docker/dockerService');
const { registerJob, unregisterJob } = require('../services/cronService');

function createZipArchive(options = { zlib: { level: 6 } }) {
  if (typeof archiver === 'function') {
    return archiver('zip', options);
  }
  if (archiver.ZipArchive) {
    return new archiver.ZipArchive(options);
  }
  if (archiver.create) {
    return archiver.create('zip', options);
  }
  throw new Error('Unsupported archiver format');
}

function getServerDir(server) {
  const sDir = path.resolve(config.paths.servers, String(server.owner_id), String(server.id));
  if (!fs.existsSync(sDir)) {
    fs.mkdirSync(sDir, { recursive: true });
  }
  return sDir;
}

function getBackupDir(server) {
  const bDir = path.resolve(config.paths.backups, String(server.owner_id), String(server.id));
  if (!fs.existsSync(bDir)) {
    fs.mkdirSync(bDir, { recursive: true });
  }
  return bDir;
}

function resolveSafePath(baseDir, reqPath) {
  const full = path.resolve(baseDir, '.' + path.normalize(reqPath || '/'));
  if (!full.startsWith(baseDir)) {
    return baseDir;
  }
  return full;
}

// Multer upload to server directory
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const base = getServerDir(req.server);
    const target = resolveSafePath(base, req.query.dir || '/');
    cb(null, target);
  },
  filename: (req, file, cb) => {
    cb(null, file.originalname);
  }
});
const upload = multer({ storage });

// Root server redirect
router.get('/', (req, res) => {
  res.redirect(`/server/${req.server.id}/console`);
});

// Console
router.get('/console', async (req, res) => {
  const db = getDb();
  const server = req.server;
  const node = await db.get('SELECT * FROM nodes WHERE id = ?', [server.node_id]);
  const allocation = await db.get('SELECT * FROM allocations WHERE id = ?', [server.allocation_id]);

  const egg = server.egg_id ? await db.get('SELECT * FROM eggs WHERE id = ?', [server.egg_id]) : null;

  res.render('user/console', {
    user: req.session.user,
    server,
    node,
    allocation,
    egg,
  });
});

// Power action
router.post('/power', async (req, res) => {
  const { action } = req.body;
  const db = getDb();
  const serverId = req.server.id;

  try {
    if (action === 'start') await dockerService.startServer(serverId, db);
    else if (action === 'stop') await dockerService.stopServer(serverId, db);
    else if (action === 'restart') await dockerService.restartServer(serverId, db);
    else if (action === 'kill') await dockerService.killServer(serverId, db);

    res.json({ success: true, action });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Command
router.post('/command', async (req, res) => {
  const { command } = req.body;
  try {
    const out = await dockerService.sendCommand(req.server.id, command);
    res.json({ success: true, output: out });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// File Manager
router.get('/files', (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.query.dir || '/';
  const targetDir = resolveSafePath(baseDir, currentDir);

  let items = [];
  try {
    const list = fs.readdirSync(targetDir, { withFileTypes: true });
    items = list.map((item) => {
      const fullPath = path.join(targetDir, item.name);
      const stat = fs.statSync(fullPath);
      return {
        name: item.name,
        isDirectory: item.isDirectory(),
        size: item.isDirectory() ? '-' : stat.size,
        updatedAt: stat.mtime,
      };
    }).sort((a, b) => (b.isDirectory ? 1 : 0) - (a.isDirectory ? 1 : 0) || a.name.localeCompare(b.name));
  } catch (err) {
    console.error('Error reading dir:', err);
  }

  res.render('user/files', {
    user: req.session.user,
    server: req.server,
    currentDir,
    items,
  });
});

// Read file content for editor
router.get('/files/content', (req, res) => {
  const baseDir = getServerDir(req.server);
  const targetPath = resolveSafePath(baseDir, req.query.file || '');

  try {
    if (!fs.existsSync(targetPath) || fs.statSync(targetPath).isDirectory()) {
      return res.status(404).json({ error: 'File not found' });
    }
    const content = fs.readFileSync(targetPath, 'utf8');
    res.json({ content, fileName: path.basename(targetPath) });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Save file content
router.post('/files/save', (req, res) => {
  const baseDir = getServerDir(req.server);
  const targetPath = resolveSafePath(baseDir, req.body.file || '');

  try {
    fs.writeFileSync(targetPath, req.body.content || '');
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Create file / folder
router.post('/files/create', (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.body.dir || '/';
  const name = req.body.name;
  const isDirectory = req.body.is_directory === '1' || req.body.isDirectory;

  const targetPath = resolveSafePath(baseDir, path.join(currentDir, name));
  try {
    if (isDirectory) {
      fs.mkdirSync(targetPath, { recursive: true });
    } else {
      fs.writeFileSync(targetPath, '');
    }
    res.redirect(`/server/${req.server.id}/files?dir=` + encodeURIComponent(currentDir));
  } catch (err) {
    res.redirect(`/server/${req.server.id}/files?dir=` + encodeURIComponent(currentDir) + '&error=' + encodeURIComponent(err.message));
  }
});

// Delete file / folder
router.post('/files/delete', (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.body.dir || '/';
  const name = req.body.name;

  const targetPath = resolveSafePath(baseDir, path.join(currentDir, name));
  try {
    fs.rmSync(targetPath, { recursive: true, force: true });
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Rename file / folder
router.post('/files/rename', (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.body.dir || '/';
  const oldPath = resolveSafePath(baseDir, path.join(currentDir, req.body.oldName));
  const newPath = resolveSafePath(baseDir, path.join(currentDir, req.body.newName));

  try {
    fs.renameSync(oldPath, newPath);
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Upload file
router.post('/files/upload', upload.single('file'), (req, res) => {
  const currentDir = req.query.dir || '/';
  res.redirect(`/server/${req.server.id}/files?dir=` + encodeURIComponent(currentDir));
});

// Download file
router.get('/files/download', (req, res) => {
  const baseDir = getServerDir(req.server);
  const targetPath = resolveSafePath(baseDir, req.query.file || '');
  if (!fs.existsSync(targetPath) || fs.statSync(targetPath).isDirectory()) {
    return res.status(404).send('File not found');
  }
  res.download(targetPath);
});

// Archive (zip)
router.post('/files/archive', (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.body.dir || '/';
  const items = req.body.items || [];
  const archiveName = `archive-${Date.now()}.zip`;
  const archivePath = resolveSafePath(baseDir, path.join(currentDir, archiveName));

  const output = fs.createWriteStream(archivePath);
  const archive = createZipArchive({ zlib: { level: 9 } });

  output.on('close', () => {
    res.json({ success: true, archiveName });
  });

  archive.pipe(output);

  for (const item of items) {
    const full = resolveSafePath(baseDir, path.join(currentDir, item));
    if (fs.existsSync(full)) {
      if (fs.statSync(full).isDirectory()) {
        archive.directory(full, item);
      } else {
        archive.file(full, { name: item });
      }
    }
  }

  archive.finalize();
});

// Extract (unzip)
router.post('/files/extract', async (req, res) => {
  const baseDir = getServerDir(req.server);
  const currentDir = req.body.dir || '/';
  const file = req.body.file;
  const fullPath = resolveSafePath(baseDir, path.join(currentDir, file));
  const destDir = resolveSafePath(baseDir, currentDir);

  try {
    await fs.createReadStream(fullPath).pipe(unzipper.Extract({ path: destDir })).promise();
    res.json({ success: true });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// Databases
router.get('/databases', async (req, res) => {
  const db = getDb();
  const databases = await db.query(`
    SELECT sd.*, dh.host, dh.port, dh.name as host_name
    FROM server_databases sd
    LEFT JOIN database_hosts dh ON sd.host_id = dh.id
    WHERE sd.server_id = ?
    ORDER BY sd.id ASC
  `, [req.server.id]);
  const hosts = await db.query('SELECT * FROM database_hosts');

  res.render('user/databases', {
    user: req.session.user,
    server: req.server,
    databases,
    hosts,
    error: req.query.error || null,
  });
});

router.post('/databases', async (req, res) => {
  const db = getDb();
  const { database_name, host_id } = req.body;
  const targetHostId = host_id ? parseInt(host_id, 10) : 1;

  // Check database limit
  const countRow = await db.get('SELECT COUNT(*) as count FROM server_databases WHERE server_id = ?', [req.server.id]);
  const currentCount = countRow ? Number(countRow.count) : 0;
  const limit = req.server.database_limit !== undefined ? req.server.database_limit : 1;
  if (limit > 0 && currentCount >= limit) {
    return res.redirect(`/server/${req.server.id}/databases?error=` + encodeURIComponent('Database limit reached for this server.'));
  }

  const prefix = `s${req.server.id.substring(0, 4)}_`;
  const dbName = prefix + (database_name || 'db').replace(/[^a-zA-Z0-9_]/g, '');
  const username = prefix + crypto.randomBytes(3).toString('hex');
  const password = crypto.randomBytes(12).toString('hex');

  // Attempt live creation on the database host
  try {
    const hostRecord = await db.get('SELECT * FROM database_hosts WHERE id = ?', [targetHostId]);
    if (hostRecord) {
      const mysql = require('mysql2/promise');
      const conn = await mysql.createConnection({
        host: hostRecord.host,
        port: hostRecord.port,
        user: hostRecord.username,
        password: hostRecord.password,
        connectTimeout: 4000
      });
      await conn.query(`CREATE DATABASE IF NOT EXISTS \`${dbName}\``);
      await conn.query(`CREATE USER IF NOT EXISTS '${username}'@'%' IDENTIFIED BY '${password}'`);
      await conn.query(`GRANT ALL PRIVILEGES ON \`${dbName}\`.* TO '${username}'@'%'`);
      await conn.query(`FLUSH PRIVILEGES`);
      await conn.end();
    }
  } catch (err) {
    console.warn(`[Server DB] Note: Live host creation error (${err.message}). Database record created in panel.`);
  }

  await db.query(
    'INSERT INTO server_databases (server_id, host_id, database_name, username, password) VALUES (?, ?, ?, ?, ?)',
    [req.server.id, targetHostId, dbName, username, password]
  );

  res.redirect(`/server/${req.server.id}/databases`);
});

router.post('/databases/:id/delete', async (req, res) => {
  const db = getDb();
  const targetDb = await db.get('SELECT * FROM server_databases WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  if (targetDb) {
    try {
      const hostRecord = await db.get('SELECT * FROM database_hosts WHERE id = ?', [targetDb.host_id]);
      if (hostRecord) {
        const mysql = require('mysql2/promise');
        const conn = await mysql.createConnection({
          host: hostRecord.host,
          port: hostRecord.port,
          user: hostRecord.username,
          password: hostRecord.password,
          connectTimeout: 4000
        });
        await conn.query(`DROP DATABASE IF EXISTS \`${targetDb.database_name}\``);
        await conn.query(`DROP USER IF EXISTS '${targetDb.username}'@'%'`);
        await conn.query(`FLUSH PRIVILEGES`);
        await conn.end();
      }
    } catch (err) {
      console.warn(`[Server DB] Note: Live host drop error (${err.message}).`);
    }
    await db.query('DELETE FROM server_databases WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  }
  res.redirect(`/server/${req.server.id}/databases`);
});

// Schedules
router.get('/schedules', async (req, res) => {
  const db = getDb();
  const schedules = await db.query('SELECT * FROM schedules WHERE server_id = ?', [req.server.id]);
  for (const s of schedules) {
    s.tasks = await db.query('SELECT * FROM schedule_tasks WHERE schedule_id = ? ORDER BY sequence_order', [s.id]);
  }

  res.render('user/schedules', {
    user: req.session.user,
    server: req.server,
    schedules,
  });
});

router.post('/schedules', async (req, res) => {
  const db = getDb();
  const { name, cron_expression, action_type, payload } = req.body;

  const result = await db.query(
    'INSERT INTO schedules (server_id, name, cron_expression, is_active) VALUES (?, ?, ?, 1)',
    [req.server.id, name, cron_expression]
  );
  const scheduleId = result.insertId;

  if (action_type && payload) {
    await db.query(
      'INSERT INTO schedule_tasks (schedule_id, action_type, payload) VALUES (?, ?, ?)',
      [scheduleId, action_type, payload]
    );
  }

  const schedule = await db.get('SELECT * FROM schedules WHERE id = ?', [scheduleId]);
  registerJob(schedule);

  res.redirect(`/server/${req.server.id}/schedules`);
});

router.post('/schedules/:id/delete', async (req, res) => {
  const db = getDb();
  unregisterJob(req.params.id);
  await db.query('DELETE FROM schedule_tasks WHERE schedule_id = ?', [req.params.id]);
  await db.query('DELETE FROM schedules WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  res.redirect(`/server/${req.server.id}/schedules`);
});

// Backups
router.get('/backups', async (req, res) => {
  const db = getDb();
  const backups = await db.query('SELECT * FROM backups WHERE server_id = ? ORDER BY created_at DESC', [req.server.id]);
  res.render('user/backups', {
    user: req.session.user,
    server: req.server,
    backups,
  });
});

router.post('/backups', (req, res) => {
  const db = getDb();
  const server = req.server;
  const backupId = crypto.randomUUID().slice(0, 8);
  const backupName = req.body.name || `backup-${new Date().toISOString().slice(0, 10)}`;
  const fileName = `backup-${backupId}.zip`;

  const backupDir = getBackupDir(server);
  const targetZip = path.join(backupDir, fileName);
  const serverDir = getServerDir(server);

  const output = fs.createWriteStream(targetZip);
  const archive = createZipArchive({ zlib: { level: 6 } });

  archive.on('error', (err) => {
    console.error('[Backup Archive Error]', err);
    if (!res.headersSent) {
      res.redirect(`/server/${server.id}/backups?error=${encodeURIComponent(err.message)}`);
    }
  });

  output.on('close', async () => {
    const size = fs.statSync(targetZip).size;
    await db.query(
      'INSERT INTO backups (id, server_id, name, file_name, size_bytes, status) VALUES (?, ?, ?, ?, ?, ?)',
      [backupId, server.id, backupName, fileName, size, 'completed']
    );
    res.redirect(`/server/${server.id}/backups`);
  });

  archive.pipe(output);
  archive.directory(serverDir, false);
  archive.finalize();
});

router.get('/backups/:id/download', async (req, res) => {
  const db = getDb();
  const backup = await db.get('SELECT * FROM backups WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  if (!backup) return res.status(404).send('Backup not found');

  const file = path.join(getBackupDir(req.server), backup.file_name);
  if (!fs.existsSync(file)) return res.status(404).send('File on disk not found');
  res.download(file, `${backup.name}.zip`);
});

router.post('/backups/:id/restore', async (req, res) => {
  const db = getDb();
  const backup = await db.get('SELECT * FROM backups WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  if (!backup) return res.status(404).send('Backup not found');

  const zipFile = path.join(getBackupDir(req.server), backup.file_name);
  const serverDir = getServerDir(req.server);

  try {
    await fs.createReadStream(zipFile).pipe(unzipper.Extract({ path: serverDir })).promise();
    res.redirect(`/server/${req.server.id}/backups?success=Restored`);
  } catch (err) {
    res.redirect(`/server/${req.server.id}/backups?error=${encodeURIComponent(err.message)}`);
  }
});

router.post('/backups/:id/delete', async (req, res) => {
  const db = getDb();
  const backup = await db.get('SELECT * FROM backups WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  if (backup) {
    const file = path.join(getBackupDir(req.server), backup.file_name);
    if (fs.existsSync(file)) fs.unlinkSync(file);
    await db.query('DELETE FROM backups WHERE id = ?', [backup.id]);
  }
  res.redirect(`/server/${req.server.id}/backups`);
});

// Network
router.get('/network', async (req, res) => {
  const db = getDb();
  const allocations = await db.query(
    'SELECT a.*, (a.id = ?) as is_primary FROM allocations a WHERE a.server_id = ? OR a.id = ?',
    [req.server.allocation_id, req.server.id, req.server.allocation_id]
  );
  res.render('user/network', {
    user: req.session.user,
    server: req.server,
    allocations,
  });
});

// Startup
router.get('/startup', async (req, res) => {
  const db = getDb();
  const egg = req.server.egg_id ? await db.get('SELECT * FROM eggs WHERE id = ?', [req.server.egg_id]) : null;
  const allEggs = await db.query('SELECT * FROM eggs ORDER BY nest_id ASC, id ASC');
  const variables = await db.query('SELECT * FROM server_variables WHERE server_id = ?', [req.server.id]);

  const varMap = {};
  if (egg && egg.environment_json) {
    try {
      const parsed = JSON.parse(egg.environment_json);
      for (const [k, v] of Object.entries(parsed)) {
        varMap[k] = v;
      }
    } catch(e) {}
  }
  if (variables) {
    for (const v of variables) {
      varMap[v.env_key] = v.env_value;
    }
  }

  const isMc = !egg || egg.nest_id === 1 || (req.server.startup_command && req.server.startup_command.includes('java'));
  const isNode = egg && egg.nest_id === 2;
  const isPython = egg && egg.nest_id === 3;

  const variablesList = [];
  if (isMc) {
    variablesList.push({
      key: 'MINECRAFT_VERSION',
      name: 'MINECRAFT VERSION',
      desc: 'The version of minecraft to download. Leave at latest to always get the latest version. Invalid versions will default to latest.',
      defaultVal: 'latest'
    });
    variablesList.push({
      key: 'SERVER_JARFILE',
      name: 'SERVER JAR FILE',
      desc: 'The name of the server jarfile to run the server with.',
      defaultVal: 'server.jar'
    });
    variablesList.push({
      key: 'BUILD_NUMBER',
      name: 'BUILD NUMBER',
      desc: 'The build number for the paper release. Leave at latest to always get the latest version. Invalid versions will default to latest.',
      defaultVal: 'latest'
    });
  } else if (isNode) {
    variablesList.push({
      key: 'MAIN_FILE',
      name: 'MAIN FILE',
      desc: 'The entrypoint javascript file for your Node.js application.',
      defaultVal: 'index.js'
    });
    variablesList.push({
      key: 'USER_UPLOAD',
      name: 'INSTALL PACKAGES',
      desc: 'Automatically run npm install on startup.',
      defaultVal: 'true'
    });
  } else if (isPython) {
    variablesList.push({
      key: 'MAIN_FILE',
      name: 'MAIN FILE',
      desc: 'The entrypoint script for your Python application.',
      defaultVal: 'app.py'
    });
    variablesList.push({
      key: 'REQUIREMENTS',
      name: 'REQUIREMENTS FILE',
      desc: 'File containing dependencies to install via pip.',
      defaultVal: 'requirements.txt'
    });
  }

  for (const item of variablesList) {
    item.val = varMap[item.key] !== undefined ? varMap[item.key] : item.defaultVal;
  }

  for (const [k, v] of Object.entries(varMap)) {
    if (!variablesList.some(d => d.key === k)) {
      variablesList.push({
        key: k,
        name: k.replace(/_/g, ' '),
        desc: `Custom environment variable injected as ${k}.`,
        val: v
      });
    }
  }

  res.render('user/startup', {
    user: req.session.user,
    server: req.server,
    egg,
    allEggs: allEggs || [],
    variablesList,
    success: req.query.success || null,
  });
});

router.post('/startup/variable', async (req, res) => {
  const db = getDb();
  const { key, val } = req.body;
  if (!key) return res.status(400).json({ error: 'Key required' });

  const existing = await db.get(
    'SELECT id FROM server_variables WHERE server_id = ? AND env_key = ?',
    [req.server.id, key]
  );
  if (existing) {
    await db.query('UPDATE server_variables SET env_value = ? WHERE id = ?', [val, existing.id]);
  } else {
    await db.query(
      'INSERT INTO server_variables (server_id, env_key, env_value) VALUES (?, ?, ?)',
      [req.server.id, key, val]
    );
  }
  return res.json({ success: true, key, val });
});

router.post('/startup', async (req, res) => {
  const db = getDb();
  const { startup_command, docker_image, egg_id, env_keys, env_vals } = req.body;

  let updateQuery = 'UPDATE servers SET startup_command = ?, docker_image = ?';
  const params = [startup_command, docker_image];

  if (egg_id) {
    updateQuery += ', egg_id = ?';
    params.push(egg_id);
  }
  updateQuery += ' WHERE id = ?';
  params.push(req.server.id);

  await db.query(updateQuery, params);

  // Update or insert environment variables
  if (env_keys && env_vals) {
    const keys = Array.isArray(env_keys) ? env_keys : [env_keys];
    const vals = Array.isArray(env_vals) ? env_vals : [env_vals];

    for (let i = 0; i < keys.length; i++) {
      const key = String(keys[i]).trim();
      const val = String(vals[i] || '').trim();
      if (!key) continue;

      const existing = await db.get(
        'SELECT id FROM server_variables WHERE server_id = ? AND env_key = ?',
        [req.server.id, key]
      );
      if (existing) {
        await db.query('UPDATE server_variables SET env_value = ? WHERE id = ?', [val, existing.id]);
      } else {
        await db.query(
          'INSERT INTO server_variables (server_id, env_key, env_value) VALUES (?, ?, ?)',
          [req.server.id, key, val]
        );
      }
    }
  }

  if (req.body.rebuild === '1') {
    try {
      await dockerService.deleteServerContainer(req.server.id);
      await dockerService.startServer(req.server.id, db);
      return res.redirect(`/server/${req.server.id}/console?msg=Rebuilt`);
    } catch (e) {
      return res.redirect(`/server/${req.server.id}/startup?error=${encodeURIComponent(e.message)}`);
    }
  }

  res.redirect(`/server/${req.server.id}/startup?success=Saved`);
});

router.post('/startup/variable/delete', async (req, res) => {
  const db = getDb();
  const { key } = req.body;
  if (key) {
    await db.query('DELETE FROM server_variables WHERE server_id = ? AND env_key = ?', [req.server.id, key]);
  }
  res.redirect(`/server/${req.server.id}/startup?success=Deleted`);
});

// Subusers
router.get('/users', async (req, res) => {
  const db = getDb();
  const subusers = await db.query(`
    SELECT su.*, u.username, u.email 
    FROM server_subusers su
    JOIN users u ON su.user_id = u.id
    WHERE su.server_id = ?
  `, [req.server.id]);

  res.render('user/users', {
    user: req.session.user,
    server: req.server,
    subusers,
    error: req.query.error || null,
  });
});

router.post('/users', async (req, res) => {
  const db = getDb();
  const { email, permissions } = req.body;
  const targetUser = await db.get('SELECT id FROM users WHERE email = ?', [email]);
  if (!targetUser) {
    return res.redirect(`/server/${req.server.id}/users?error=User+not+found`);
  }

  const perms = Array.isArray(permissions) ? permissions : [permissions].filter(Boolean);
  await db.query(
    'INSERT INTO server_subusers (server_id, user_id, permissions_json) VALUES (?, ?, ?)',
    [req.server.id, targetUser.id, JSON.stringify(perms)]
  );

  res.redirect(`/server/${req.server.id}/users`);
});

router.post('/users/:id/delete', async (req, res) => {
  const db = getDb();
  await db.query('DELETE FROM server_subusers WHERE id = ? AND server_id = ?', [req.params.id, req.server.id]);
  res.redirect(`/server/${req.server.id}/users`);
});

// Server Settings
router.get('/settings', async (req, res) => {
  const db = getDb();
  const node = await db.get('SELECT * FROM nodes WHERE id = ?', [req.server.node_id]);
  res.render('user/settings', {
    user: req.session.user,
    server: req.server,
    node,
    sftp: {
      host: node ? node.fqdn : 'localhost',
      port: node ? node.sftp_port : 3004,
      user: `${req.session.user.username}.${req.server.id.substring(0, 6)}`,
    }
  });
});

router.post('/settings', async (req, res) => {
  const db = getDb();
  const { name, description } = req.body;
  await db.query('UPDATE servers SET name = ?, description = ? WHERE id = ?', [name, description, req.server.id]);
  res.redirect(`/server/${req.server.id}/settings?success=Saved`);
});

router.post('/reinstall', async (req, res) => {
  const db = getDb();
  const server = req.server;
  try {
    await dockerService.stopServer(server.id, db);
    const egg = await db.get('SELECT * FROM eggs WHERE id = ?', [server.egg_id]);
    const alloc = await db.get('SELECT * FROM allocations WHERE id = ?', [server.allocation_id]);
    const svRows = await db.query('SELECT env_key, env_value FROM server_variables WHERE server_id = ?', [server.id]);
    const envVars = {};
    if (svRows) {
      for (const r of svRows) envVars[r.env_key] = r.env_value;
    }
    await dockerService.deleteServerContainer(server.id);
    await dockerService.createServerContainer(server, alloc, egg, envVars);
    res.redirect(`/server/${server.id}/console?msg=Reinstalled`);
  } catch (err) {
    res.redirect(`/server/${server.id}/settings?error=${encodeURIComponent(err.message)}`);
  }
});

// Activity
router.get('/activity', async (req, res) => {
  const db = getDb();
  const logs = await db.query(
    'SELECT * FROM activity_logs WHERE server_id = ? ORDER BY created_at DESC LIMIT 50',
    [req.server.id]
  );
  res.render('user/activity', {
    user: req.session.user,
    server: req.server,
    logs,
  });
});

// Delete Server
router.post('/delete', async (req, res) => {
  const db = getDb();
  const server = req.server;
  if (!req.session.user || (req.session.user.root_admin !== 1 && server.owner_id !== req.session.user.id)) {
    return res.status(403).render('errors/403', { message: 'Unauthorized to delete this server.' });
  }

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

  res.redirect(req.session.user.root_admin === 1 ? '/admin/servers' : '/servers');
});

module.exports = router;
