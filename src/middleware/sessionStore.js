const session = require('express-session');

class SqlSessionStore extends session.Store {
  constructor(getDb) {
    super();
    this.getDb = getDb;
  }

  async get(sid, callback) {
    try {
      const db = this.getDb();
      if (!db) return callback(null, null);

      const row = await db.get('SELECT sess, expired_at FROM sessions WHERE sid = ?', [sid]);
      if (!row) return callback(null, null);

      if (row.expired_at && Number(row.expired_at) < Date.now()) {
        await db.query('DELETE FROM sessions WHERE sid = ?', [sid]);
        return callback(null, null);
      }

      const data = JSON.parse(row.sess);
      return callback(null, data);
    } catch (err) {
      return callback(err);
    }
  }

  async set(sid, sess, callback) {
    try {
      const db = this.getDb();
      if (!db) {
        if (callback) callback(null);
        return;
      }

      const maxAge = sess && sess.cookie && sess.cookie.maxAge ? sess.cookie.maxAge : 1000 * 60 * 60 * 24 * 7;
      const expiredAt = Date.now() + maxAge;
      const sessJson = JSON.stringify(sess);

      if (db.type === 'mysql') {
        await db.query(
          `INSERT INTO sessions (sid, sess, expired_at) VALUES (?, ?, ?)
           ON DUPLICATE KEY UPDATE sess = VALUES(sess), expired_at = VALUES(expired_at)`,
          [sid, sessJson, expiredAt]
        );
      } else {
        await db.query(
          `INSERT OR REPLACE INTO sessions (sid, sess, expired_at) VALUES (?, ?, ?)`,
          [sid, sessJson, expiredAt]
        );
      }

      if (callback) callback(null);
    } catch (err) {
      if (callback) callback(err);
    }
  }

  async destroy(sid, callback) {
    try {
      const db = this.getDb();
      if (db) {
        await db.query('DELETE FROM sessions WHERE sid = ?', [sid]);
      }
      if (callback) callback(null);
    } catch (err) {
      if (callback) callback(err);
    }
  }

  async touch(sid, sess, callback) {
    try {
      const db = this.getDb();
      if (!db) {
        if (callback) callback(null);
        return;
      }

      const maxAge = sess && sess.cookie && sess.cookie.maxAge ? sess.cookie.maxAge : 1000 * 60 * 60 * 24 * 7;
      const expiredAt = Date.now() + maxAge;

      await db.query('UPDATE sessions SET expired_at = ? WHERE sid = ?', [expiredAt, sid]);
      if (callback) callback(null);
    } catch (err) {
      if (callback) callback(err);
    }
  }

  async length(callback) {
    try {
      const db = this.getDb();
      if (!db) return callback(null, 0);
      const row = await db.get('SELECT COUNT(*) as count FROM sessions WHERE expired_at > ?', [Date.now()]);
      return callback(null, row ? Number(row.count) : 0);
    } catch (err) {
      return callback(err);
    }
  }

  async clear(callback) {
    try {
      const db = this.getDb();
      if (db) {
        await db.query('DELETE FROM sessions');
      }
      if (callback) callback(null);
    } catch (err) {
      if (callback) callback(err);
    }
  }
}

module.exports = SqlSessionStore;
