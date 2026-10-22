const { initDb, getDb } = require('../src/database');

const PY_STARTUP = 'if [[ -d .git ]] && [[ "{{AUTO_UPDATE}}" == "1" ]]; then git pull; fi; if [[ ! -z "{{PY_PACKAGES}}" ]]; then pip install -U --prefix .local {{PY_PACKAGES}}; fi; if [[ -f /home/container/${REQUIREMENTS_FILE} ]]; then pip install -U --prefix .local -r ${REQUIREMENTS_FILE}; fi; /usr/local/bin/python /home/container/{{PY_FILE}}';

const PY_ENV = JSON.stringify({
  GIT_ADDRESS: '',
  BRANCH: '',
  USER_UPLOAD: '0',
  AUTO_UPDATE: '0',
  PY_FILE: 'app.py',
  PY_PACKAGES: '',
  USERNAME: '',
  ACCESS_TOKEN: '',
  REQUIREMENTS_FILE: 'requirements.txt'
});

const NODE_STARTUP = 'clear; echo -e "---------------[ This server is powered by www.RyzeHost.xyz ]---------------\\n"; [ ! -d node_modules ]&&npm install||echo "Using existing node_modules, delete to reinstall."; {{STARTUPSCRIPT}}';

const NODE_ENV = JSON.stringify({
  startupscript: 'node main.js'
});

const PY_IMAGES = [
  ['python generic', 'ghcr.io/parkervcp/yolks:python_3.12'],
  ['Python 3.12', 'ghcr.io/parkervcp/yolks:python_3.12'],
  ['Python 3.11', 'ghcr.io/parkervcp/yolks:python_3.11'],
  ['Python 3.10', 'ghcr.io/parkervcp/yolks:python_3.10'],
  ['Python 3.9', 'ghcr.io/parkervcp/yolks:python_3.9'],
  ['Python 3.8', 'ghcr.io/parkervcp/yolks:python_3.8'],
  ['Python 3.7', 'ghcr.io/parkervcp/yolks:python_3.7'],
  ['Python 2.7', 'ghcr.io/parkervcp/yolks:python_2.7']
];

const NODE_IMAGES = [
  ['RyzeHost Node.js Egg', 'ghcr.io/parkervcp/yolks:nodejs_20'],
  ['Nodejs 24', 'ghcr.io/parkervcp/yolks:nodejs_24'],
  ['Nodejs 23', 'ghcr.io/parkervcp/yolks:nodejs_23'],
  ['Nodejs 22', 'ghcr.io/parkervcp/yolks:nodejs_22'],
  ['Nodejs 21', 'ghcr.io/parkervcp/yolks:nodejs_21'],
  ['Nodejs 20', 'ghcr.io/parkervcp/yolks:nodejs_20'],
  ['Nodejs 19', 'ghcr.io/parkervcp/yolks:nodejs_19'],
  ['Nodejs 18', 'ghcr.io/parkervcp/yolks:nodejs_18'],
  ['Nodejs 17', 'ghcr.io/parkervcp/yolks:nodejs_17'],
  ['Nodejs 16', 'ghcr.io/parkervcp/yolks:nodejs_16'],
  ['Nodejs 14', 'ghcr.io/parkervcp/yolks:nodejs_14'],
  ['Nodejs 12', 'ghcr.io/parkervcp/yolks:nodejs_12']
];

async function updateDb() {
  await initDb();
  const db = getDb();
  if (!db) {
    console.error('Failed to get database handle');
    process.exit(1);
  }

  console.log('[Migration] Updating Node.js and Python eggs...');

  // Ensure nests exist
  let nodeNest = await db.get("SELECT id FROM nests WHERE id = 2 OR name = 'Node.js'");
  if (!nodeNest) {
    const r = await db.query("INSERT INTO nests (id, name, description) VALUES (2, 'Node.js', 'JavaScript and Node.js applications and Discord/Telegram bots')");
    nodeNest = { id: r.insertId || 2 };
  }
  const nodeNestId = nodeNest.id;

  let pyNest = await db.get("SELECT id FROM nests WHERE id = 3 OR name = 'Python'");
  if (!pyNest) {
    const r = await db.query("INSERT INTO nests (id, name, description) VALUES (3, 'Python', 'Python applications, web servers, and automation bots')");
    pyNest = { id: r.insertId || 3 };
  }
  const pyNestId = pyNest.id;

  // Update or insert Node.js eggs
  for (const [name, img] of NODE_IMAGES) {
    const existing = await db.get('SELECT id FROM eggs WHERE nest_id = ? AND (name = ? OR name = ?)', [nodeNestId, name, name.replace('Nodejs', 'Node.js')]);
    if (existing) {
      await db.query(
        'UPDATE eggs SET name = ?, docker_image = ?, startup_command = ?, environment_json = ? WHERE id = ?',
        [name, img, NODE_STARTUP, NODE_ENV, existing.id]
      );
    } else {
      await db.query(
        'INSERT INTO eggs (nest_id, name, docker_image, startup_command, environment_json) VALUES (?, ?, ?, ?, ?)',
        [nodeNestId, name, img, NODE_STARTUP, NODE_ENV]
      );
    }
  }

  // Update or insert Python eggs
  for (const [name, img] of PY_IMAGES) {
    const existing = await db.get('SELECT id FROM eggs WHERE nest_id = ? AND name = ?', [pyNestId, name]);
    if (existing) {
      await db.query(
        'UPDATE eggs SET docker_image = ?, startup_command = ?, environment_json = ? WHERE id = ?',
        [img, PY_STARTUP, PY_ENV, existing.id]
      );
    } else {
      await db.query(
        'INSERT INTO eggs (nest_id, name, docker_image, startup_command, environment_json) VALUES (?, ?, ?, ?, ?)',
        [pyNestId, name, img, PY_STARTUP, PY_ENV]
      );
    }
  }

  // Update existing server srv-012e90f9 (Node.js)
  const nodeServer = await db.get("SELECT * FROM servers WHERE id = 'srv-012e90f9'");
  if (nodeServer) {
    console.log('[Migration] Updating srv-012e90f9 (Node.js)...');
    await db.query(
      `UPDATE servers SET name = 'Node.js Express App #775', docker_image = 'ghcr.io/parkervcp/yolks:nodejs_20', startup_command = ? WHERE id = 'srv-012e90f9'`,
      [NODE_STARTUP]
    );
    await db.query("DELETE FROM server_variables WHERE server_id = 'srv-012e90f9'");
    await db.query("INSERT INTO server_variables (server_id, env_key, env_value) VALUES ('srv-012e90f9', 'startupscript', 'node main.js')");
  }

  // Update existing server srv-213b6e3a (Python)
  const pyServer = await db.get("SELECT * FROM servers WHERE id = 'srv-213b6e3a'");
  if (pyServer) {
    console.log('[Migration] Updating srv-213b6e3a (Python)...');
    await db.query(
      `UPDATE servers SET name = 'Python App #775', docker_image = 'ghcr.io/parkervcp/yolks:python_3.12', startup_command = ? WHERE id = 'srv-213b6e3a'`,
      [PY_STARTUP]
    );
    await db.query("DELETE FROM server_variables WHERE server_id = 'srv-213b6e3a'");
    const pyVars = [
      ['PY_FILE', 'app.py'],
      ['REQUIREMENTS_FILE', 'requirements.txt'],
      ['AUTO_UPDATE', '0'],
      ['USER_UPLOAD', '0'],
      ['PY_PACKAGES', ''],
      ['GIT_ADDRESS', ''],
      ['BRANCH', ''],
      ['USERNAME', ''],
      ['ACCESS_TOKEN', '']
    ];
    for (const [k, v] of pyVars) {
      await db.query("INSERT INTO server_variables (server_id, env_key, env_value) VALUES ('srv-213b6e3a', ?, ?)", [k, v]);
    }
  }

  console.log('✅ Eggs and servers successfully updated in database!');
  process.exit(0);
}

updateDb().catch(err => {
  console.error('Error updating eggs:', err);
  process.exit(1);
});

