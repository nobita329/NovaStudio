const bcrypt = require('bcryptjs');

const TABLES = [
  `CREATE TABLE IF NOT EXISTS settings (
    \`key\` VARCHAR(100) PRIMARY KEY,
    \`value\` TEXT
  )`,

  `CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    root_admin INTEGER DEFAULT 0,
    two_factor_secret VARCHAR(100) DEFAULT NULL,
    two_factor_enabled INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS locations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    short_code VARCHAR(50) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS nodes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    location_id INTEGER DEFAULT 1,
    fqdn VARCHAR(255) DEFAULT 'localhost',
    scheme VARCHAR(10) DEFAULT 'http',
    daemon_port INTEGER DEFAULT 3003,
    sftp_port INTEGER DEFAULT 3004,
    memory INTEGER DEFAULT 8192,
    memory_overallocate INTEGER DEFAULT 0,
    disk INTEGER DEFAULT 51200,
    disk_overallocate INTEGER DEFAULT 0,
    behind_proxy INTEGER DEFAULT 0,
    maintenance_mode INTEGER DEFAULT 0,
    daemon_token VARCHAR(255),
    status VARCHAR(50) DEFAULT 'online',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS allocations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    node_id INTEGER NOT NULL,
    ip_address VARCHAR(100) DEFAULT '0.0.0.0',
    ip_alias VARCHAR(100) DEFAULT '',
    port INTEGER NOT NULL,
    server_id VARCHAR(50) DEFAULT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS nests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS eggs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nest_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    docker_image VARCHAR(255) NOT NULL,
    startup_command TEXT,
    environment_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS servers (
    id VARCHAR(50) PRIMARY KEY,
    uuid VARCHAR(64) UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    owner_id INTEGER NOT NULL,
    node_id INTEGER DEFAULT 1,
    allocation_id INTEGER DEFAULT NULL,
    nest_id INTEGER DEFAULT 1,
    egg_id INTEGER DEFAULT 1,
    docker_image VARCHAR(255) NOT NULL,
    startup_command TEXT,
    cpu INTEGER DEFAULT 100,
    memory INTEGER DEFAULT 1024,
    disk INTEGER DEFAULT 5120,
    swap INTEGER DEFAULT 0,
    io INTEGER DEFAULT 500,
    status VARCHAR(50) DEFAULT 'offline',
    container_id VARCHAR(100) DEFAULT NULL,
    database_limit INTEGER DEFAULT 1,
    backup_limit INTEGER DEFAULT 3,
    allocation_limit INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS server_variables (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id VARCHAR(50) NOT NULL,
    env_key VARCHAR(100) NOT NULL,
    env_value TEXT
  )`,

  `CREATE TABLE IF NOT EXISTS server_subusers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id VARCHAR(50) NOT NULL,
    user_id INTEGER NOT NULL,
    permissions_json TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS database_hosts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INTEGER DEFAULT 3005,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    node_id INTEGER DEFAULT 1,
    max_databases INTEGER DEFAULT 100,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS server_databases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id VARCHAR(50) NOT NULL,
    host_id INTEGER NOT NULL,
    database_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    server_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    cron_expression VARCHAR(100) NOT NULL,
    is_active INTEGER DEFAULT 1,
    last_run DATETIME DEFAULT NULL,
    next_run DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS schedule_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    schedule_id INTEGER NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    payload TEXT,
    time_offset INTEGER DEFAULT 0,
    sequence_order INTEGER DEFAULT 1
  )`,

  `CREATE TABLE IF NOT EXISTS backups (
    id VARCHAR(50) PRIMARY KEY,
    server_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    size_bytes INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS api_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    identifier VARCHAR(32) UNIQUE NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    description TEXT,
    permissions_json TEXT,
    last_used_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS ssh_keys (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    public_key TEXT NOT NULL,
    fingerprint VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS activity_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT NULL,
    server_id VARCHAR(50) DEFAULT NULL,
    event VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    ip_address VARCHAR(100) DEFAULT '127.0.0.1',
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`,

  `CREATE TABLE IF NOT EXISTS sessions (
    sid VARCHAR(191) PRIMARY KEY,
    sess TEXT NOT NULL,
    expired_at BIGINT NOT NULL
  )`
];

async function applySchema(db) {
  for (const tableSql of TABLES) {
    let sql = tableSql;
    if (db.type === 'mysql') {
      sql = sql.replace(/INTEGER PRIMARY KEY AUTOINCREMENT/gi, 'INT AUTO_INCREMENT PRIMARY KEY')
               .replace(/DATETIME DEFAULT CURRENT_TIMESTAMP/gi, 'DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
    await db.query(sql);
  }

  // Seed default settings
  const defaultSettings = [
    ['panel_name', 'Nova'],
    ['company_name', 'Nova Hosting'],
    ['panel_url', 'http://localhost:3001'],
    ['panel_logo', '/assets/img/logo.png'],
    ['panel_favicon', '/assets/img/favicon.png'],
    ['theme', 'dark'],
    ['default_language', 'en'],
    ['timezone', 'UTC'],
    ['maintenance_mode', '0'],
    ['maintenance_message', 'Nova Panel is currently undergoing scheduled maintenance.'],
    ['debug_mode', '0'],
    ['smtp_host', 'smtp.example.com'],
    ['smtp_port', '587'],
    ['smtp_user', 'no-reply@example.com'],
    ['smtp_pass', ''],
    ['smtp_encryption', 'tls'],
    ['smtp_from', 'no-reply@example.com'],
    ['smtp_from_name', 'Nova Panel']
  ];

  for (const [k, v] of defaultSettings) {
    const existing = await db.get('SELECT `key` FROM settings WHERE `key` = ?', [k]);
    if (!existing) {
      await db.query('INSERT INTO settings (`key`, `value`) VALUES (?, ?)', [k, v]);
    }
  }

  // Seed default location
  const loc = await db.get('SELECT id FROM locations LIMIT 1');
  if (!loc) {
    await db.query('INSERT INTO locations (short_code, description) VALUES (?, ?)', ['LOC1', 'Default Local Datacenter']);
  }

  // Seed default node
  const node = await db.get('SELECT id FROM nodes LIMIT 1');
  if (!node) {
    await db.query(
      `INSERT INTO nodes (name, description, location_id, fqdn, scheme, daemon_port, sftp_port, memory, disk, status) 
       VALUES (?, ?, 1, 'localhost', 'http', 3003, 3004, 16384, 102400, 'online')`,
      ['Local Node', 'Default Local Node running Nova Daemon']
    );
  }

  // Seed default allocations
  const allocs = await db.get('SELECT id FROM allocations LIMIT 1');
  if (!allocs) {
    const defaultPorts = [25501, 25502, 25503, 25504, 25505, 3000, 5000, 8080];
    for (const port of defaultPorts) {
      await db.query('INSERT INTO allocations (node_id, ip_address, port, notes) VALUES (1, ?, ?, ?)', ['0.0.0.0', port, 'Default Allocation']);
    }
  }

  // Seed default database host
  const dbHost = await db.get('SELECT id FROM database_hosts LIMIT 1');
  if (!dbHost) {
    await db.query(
      `INSERT INTO database_hosts (name, host, port, username, password, node_id, max_databases)
       VALUES (?, ?, ?, ?, ?, 1, 100)`,
      ['Local MariaDB / MySQL Host', '127.0.0.1', 3005, 'root', 'YourStrongPassword']
    );
  }

  // Seed default Nests & Eggs
  const nestCount = await db.get('SELECT count(*) as count FROM nests');
  if (!nestCount || nestCount.count === 0) {
    // 1. Minecraft
    const mcNest = await db.query('INSERT INTO nests (name, description) VALUES (?, ?)', [
      'Minecraft',
      'Minecraft Java Edition game servers'
    ]);
    const mcNestId = mcNest.insertId || 1;

    const mcEggs = [
      ['Minecraft Paper (Java 21)', 'ghcr.io/pterodactyl/yolks:java_21', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 26)', 'ghcr.io/pterodactyl/yolks:java_26', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 25)', 'ghcr.io/pterodactyl/yolks:java_25', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 17)', 'ghcr.io/pterodactyl/yolks:java_17', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 16)', 'ghcr.io/pterodactyl/yolks:java_16', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 11)', 'ghcr.io/pterodactyl/yolks:java_11', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui'],
      ['Minecraft Paper (Java 8)', 'ghcr.io/pterodactyl/yolks:java_8', 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui']
    ];
    for (const [name, img, cmd] of mcEggs) {
      await db.query('INSERT INTO eggs (nest_id, name, docker_image, startup_command, environment_json) VALUES (?, ?, ?, ?, ?)', [
        mcNestId, name, img, cmd, JSON.stringify({ SERVER_JARFILE: 'server.jar' })
      ]);
    }

    // 2. Node.js
    const nodeNest = await db.query('INSERT INTO nests (name, description) VALUES (?, ?)', [
      'Node.js',
      'JavaScript and Node.js applications and Discord/Telegram bots'
    ]);
    const nodeNestId = nodeNest.insertId || 2;

    const nodeEggs = [
      ['Node.js 20', 'ghcr.io/ptero-eggs/yolks:nodejs_20', 'sh -c "npm install && npm start"'],
      ['Node.js 22', 'ghcr.io/ptero-eggs/yolks:nodejs_22', 'sh -c "npm install && npm start"'],
      ['Node.js 24', 'ghcr.io/ptero-eggs/yolks:nodejs_24', 'sh -c "npm install && npm start"'],
      ['Node.js 25', 'ghcr.io/ptero-eggs/yolks:nodejs_25', 'sh -c "npm install && npm start"'],
      ['Node.js 18', 'ghcr.io/ptero-eggs/yolks:nodejs_18', 'sh -c "npm install && npm start"'],
      ['Node.js 16', 'ghcr.io/ptero-eggs/yolks:nodejs_16', 'sh -c "npm install && npm start"']
    ];
    for (const [name, img, cmd] of nodeEggs) {
      await db.query('INSERT INTO eggs (nest_id, name, docker_image, startup_command, environment_json) VALUES (?, ?, ?, ?, ?)', [
        nodeNestId, name, img, cmd, JSON.stringify({ MAIN_FILE: 'index.js' })
      ]);
    }

    // 3. Python
    const pyNest = await db.query('INSERT INTO nests (name, description) VALUES (?, ?)', [
      'Python',
      'Python applications, web servers, and automation bots'
    ]);
    const pyNestId = pyNest.insertId || 3;

    const pyEggs = [
      ['Python 3.12', 'ghcr.io/ptero-eggs/yolks:python_3.12', 'python3 app.py'],
      ['Python 3.13', 'ghcr.io/ptero-eggs/yolks:python_3.13', 'python3 app.py'],
      ['Python 3.11', 'ghcr.io/ptero-eggs/yolks:python_3.11', 'python3 app.py'],
      ['Python 3.10', 'ghcr.io/ptero-eggs/yolks:python_3.10', 'python3 app.py'],
      ['Python 3.9', 'ghcr.io/ptero-eggs/yolks:python_3.9', 'python3 app.py'],
      ['Python 2.7', 'ghcr.io/ptero-eggs/yolks:python_2.7', 'python app.py']
    ];
    for (const [name, img, cmd] of pyEggs) {
      await db.query('INSERT INTO eggs (nest_id, name, docker_image, startup_command, environment_json) VALUES (?, ?, ?, ?, ?)', [
        pyNestId, name, img, cmd, JSON.stringify({ MAIN_FILE: 'app.py' })
      ]);
    }
  }

  // Auto-create default admin account (admin / admin) if no users exist
  const adminUser = await db.get('SELECT id FROM users WHERE username = ?', ['admin']);
  if (!adminUser) {
    const defaultHash = await bcrypt.hash('admin', 10);
    await db.query(
      `INSERT INTO users (first_name, last_name, username, email, password, root_admin, status) 
       VALUES (?, ?, ?, ?, ?, 1, 'active')`,
      ['Nova', 'Administrator', 'admin', 'admin@nova.test', defaultHash]
    );
  }
}

module.exports = { applySchema };
