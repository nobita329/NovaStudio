const path = require('path');
const fs = require('fs');
const config = require('../config');

let dbInstance = null;
let dbType = 'sqlite'; // 'mysql' or 'sqlite'

// Ensure data folder exists for SQLite fallback
const dataDir = path.join(config.paths.root, 'data');
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}
const sqlitePath = path.join(dataDir, 'nova.sqlite');

let sqliteDb = null;
let mysqlPool = null;

async function tryConnectMariaDB() {
  const mysql = require('mysql2/promise');
  const pool = mysql.createPool({
    host: config.db.host,
    port: config.db.port,
    user: config.db.user,
    password: config.db.password,
    database: config.db.database,
    waitForConnections: true,
    connectionLimit: 20,
    connectTimeout: 5000,
    enableKeepAlive: true,
    keepAliveInitialDelay: 10000,
    charset: 'utf8mb4'
  });

  pool.on('error', (err) => {
    console.error('[Nova DB] MariaDB pool unexpected error:', err.message);
  });

  const conn = await pool.getConnection();
  await conn.ping();
  conn.release();
  return pool;
}

async function initDb() {
  if (dbInstance) return dbInstance;

  // Try MariaDB first with retry logic
  const maxRetries = 5;
  let retryCount = 0;
  let pool = null;

  while (retryCount < maxRetries && !pool) {
    try {
      pool = await tryConnectMariaDB();
    } catch (err) {
      retryCount++;
      if (retryCount < maxRetries) {
        console.log(`[Nova DB] Waiting for MariaDB at ${config.db.host}:${config.db.port} (attempt ${retryCount}/${maxRetries})...`);
        await new Promise((r) => setTimeout(r, 1500));
      } else {
        console.log(`[Nova DB] MariaDB connection failed (${err.message}). Using SQLite storage at ${sqlitePath}`);
      }
    }
  }

  if (pool) {
    mysqlPool = pool;
    dbType = 'mysql';
    console.log(`[Nova DB] Connected to MariaDB at ${config.db.host}:${config.db.port}`);
  } else {
    const Database = require('better-sqlite3');
    sqliteDb = new Database(sqlitePath);
    sqliteDb.pragma('journal_mode = WAL');
    dbType = 'sqlite';
  }

  dbInstance = {
    type: dbType,
    get rawPool() {
      return mysqlPool;
    },
    async query(sql, params = []) {
      const cleanParams = Array.isArray(params)
        ? params.map((p) => (p === undefined ? null : p))
        : params;

      if (dbType === 'mysql') {
        const [rows] = await mysqlPool.query(sql, cleanParams);
        return rows;
      } else {
        // SQLite
        const trimmed = sql.trim();
        if (/^(SELECT|PRAGMA|SHOW)/i.test(trimmed)) {
          const stmt = sqliteDb.prepare(sql);
          return stmt.all(...cleanParams);
        } else {
          const stmt = sqliteDb.prepare(sql);
          const info = stmt.run(...cleanParams);
          return {
            insertId: info.lastInsertRowid,
            affectedRows: info.changes,
            ...info
          };
        }
      }
    },
    async run(sql, params = []) {
      return this.query(sql, params);
    },
    async get(sql, params = []) {
      const rows = await this.query(sql, params);
      return Array.isArray(rows) && rows.length > 0 ? rows[0] : null;
    }
  };

  return dbInstance;
}

module.exports = {
  initDb,
  getDb: () => dbInstance
};
