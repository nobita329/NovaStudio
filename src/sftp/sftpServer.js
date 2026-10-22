const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const ssh2 = require('ssh2');
const bcrypt = require('bcryptjs');
const config = require('../config');
const { getDb } = require('../database');

const OPEN_MODE = ssh2.utils.sftp.OPEN_MODE;
const STATUS_CODE = ssh2.utils.sftp.STATUS_CODE;

// Ensure host key exists
function getOrCreateHostKey() {
  const keyPath = path.join(config.paths.root, 'data/sftp_host_rsa');
  if (fs.existsSync(keyPath)) {
    return fs.readFileSync(keyPath);
  }
  const { privateKey } = crypto.generateKeyPairSync('rsa', {
    modulusLength: 2048,
    publicKeyEncoding: { type: 'pkcs1', format: 'pem' },
    privateKeyEncoding: { type: 'pkcs1', format: 'pem' },
  });
  fs.writeFileSync(keyPath, privateKey);
  return privateKey;
}

function safePath(baseDir, requestedPath) {
  const resolved = path.resolve(baseDir, '.' + path.normalize(requestedPath));
  if (!resolved.startsWith(baseDir)) {
    return baseDir;
  }
  return resolved;
}

function startSftpServer() {
  const hostKey = getOrCreateHostKey();

  const server = new ssh2.Server(
    {
      hostKeys: [hostKey],
    },
    (client) => {
      let authenticatedUser = null;
      let targetServer = null;

      client.on('authentication', async (ctx) => {
        if (ctx.method !== 'password') {
          return ctx.reject(['password']);
        }

        try {
          const db = getDb();
          let inputUsername = ctx.username;
          let targetServerId = null;

          // Support username.serverid format (like Pterodactyl: user.8a9bf)
          if (inputUsername.includes('.')) {
            const parts = inputUsername.split('.');
            inputUsername = parts[0];
            targetServerId = parts[1];
          }

          const user = await db.get('SELECT * FROM users WHERE username = ? OR email = ?', [
            inputUsername,
            inputUsername,
          ]);

          if (!user || user.status !== 'active') {
            return ctx.reject();
          }

          const match = await bcrypt.compare(ctx.password, user.password);
          if (!match) {
            return ctx.reject();
          }

          authenticatedUser = user;

          // If a specific server was targeted, find it; otherwise pick first owned server
          if (targetServerId) {
            targetServer = await db.get(
              'SELECT * FROM servers WHERE (id = ? OR id LIKE ?) AND (owner_id = ? OR ? = 1)',
              [targetServerId, `${targetServerId}%`, user.id, user.root_admin]
            );
          } else {
            targetServer = await db.get(
              'SELECT * FROM servers WHERE owner_id = ? OR ? = 1 ORDER BY created_at DESC LIMIT 1',
              [user.id, user.root_admin]
            );
          }

          ctx.accept();
        } catch (err) {
          console.error('[SFTP] Auth error:', err);
          ctx.reject();
        }
      });

      client.on('ready', () => {
        client.on('session', (accept) => {
          const session = accept();

          session.on('sftp', (acceptSftp) => {
            const sftp = acceptSftp();
            const openFiles = new Map();
            let handleCounter = 0;

            if (!targetServer) {
              return sftp.end();
            }

            const baseDir = path.resolve(
              config.paths.servers,
              String(targetServer.owner_id),
              String(targetServer.id)
            );

            if (!fs.existsSync(baseDir)) {
              fs.mkdirSync(baseDir, { recursive: true });
            }

            sftp.on('REALPATH', (reqid, reqPath) => {
              sftp.name(reqid, [{ filename: '/' + path.relative(baseDir, safePath(baseDir, reqPath)) || '/' }]);
            });

            sftp.on('STAT', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.stat(fullPath, (err, stat) => {
                if (err) return sftp.status(reqid, STATUS_CODE.NO_SUCH_FILE);
                sftp.attrs(reqid, stat);
              });
            });

            sftp.on('LSTAT', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.lstat(fullPath, (err, stat) => {
                if (err) return sftp.status(reqid, STATUS_CODE.NO_SUCH_FILE);
                sftp.attrs(reqid, stat);
              });
            });

            sftp.on('OPENDIR', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.opendir(fullPath, (err, dir) => {
                if (err) return sftp.status(reqid, STATUS_CODE.NO_SUCH_FILE);
                const handle = Buffer.from(String(++handleCounter));
                openFiles.set(handle.toString(), { type: 'dir', dir, path: fullPath });
                sftp.handle(reqid, handle);
              });
            });

            sftp.on('READDIR', (reqid, handle) => {
              const entry = openFiles.get(handle.toString());
              if (!entry || entry.type !== 'dir') return sftp.status(reqid, STATUS_CODE.FAILURE);

              fs.readdir(entry.path, { withFileTypes: true }, (err, files) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                if (entry.readDone) return sftp.status(reqid, STATUS_CODE.EOF);
                entry.readDone = true;

                const results = files.map((f) => {
                  const stat = fs.statSync(path.join(entry.path, f.name));
                  return {
                    filename: f.name,
                    longname: `${f.isDirectory() ? 'd' : '-'}rwxr-xr-x 1 nova nova ${stat.size} ${f.name}`,
                    attrs: stat,
                  };
                });
                sftp.name(reqid, results);
              });
            });

            sftp.on('OPEN', (reqid, reqPath, flags) => {
              const fullPath = safePath(baseDir, reqPath);
              let nodeFlags = 'r';
              if ((flags & OPEN_MODE.WRITE) && (flags & OPEN_MODE.READ)) nodeFlags = 'r+';
              else if (flags & OPEN_MODE.WRITE) nodeFlags = 'w';
              if (flags & OPEN_MODE.APPEND) nodeFlags = 'a';
              if (flags & OPEN_MODE.CREAT) nodeFlags += (nodeFlags === 'w' ? '' : 'w');

              fs.open(fullPath, nodeFlags, (err, fd) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                const handle = Buffer.from(String(++handleCounter));
                openFiles.set(handle.toString(), { type: 'file', fd, path: fullPath });
                sftp.handle(reqid, handle);
              });
            });

            sftp.on('READ', (reqid, handle, offset, length) => {
              const entry = openFiles.get(handle.toString());
              if (!entry || entry.type !== 'file') return sftp.status(reqid, STATUS_CODE.FAILURE);

              const buf = Buffer.alloc(length);
              fs.read(entry.fd, buf, 0, length, offset, (err, bytesRead) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                if (bytesRead === 0) return sftp.status(reqid, STATUS_CODE.EOF);
                sftp.data(reqid, buf.subarray(0, bytesRead));
              });
            });

            sftp.on('WRITE', (reqid, handle, offset, data) => {
              const entry = openFiles.get(handle.toString());
              if (!entry || entry.type !== 'file') return sftp.status(reqid, STATUS_CODE.FAILURE);

              fs.write(entry.fd, data, 0, data.length, offset, (err) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                sftp.status(reqid, STATUS_CODE.OK);
              });
            });

            sftp.on('CLOSE', (reqid, handle) => {
              const entry = openFiles.get(handle.toString());
              if (entry) {
                if (entry.type === 'file') {
                  fs.close(entry.fd, () => {});
                }
                openFiles.delete(handle.toString());
              }
              sftp.status(reqid, STATUS_CODE.OK);
            });

            sftp.on('REMOVE', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.unlink(fullPath, (err) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                sftp.status(reqid, STATUS_CODE.OK);
              });
            });

            sftp.on('RMDIR', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.rm(fullPath, { recursive: true, force: true }, (err) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                sftp.status(reqid, STATUS_CODE.OK);
              });
            });

            sftp.on('MKDIR', (reqid, reqPath) => {
              const fullPath = safePath(baseDir, reqPath);
              fs.mkdir(fullPath, { recursive: true }, (err) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                sftp.status(reqid, STATUS_CODE.OK);
              });
            });

            sftp.on('RENAME', (reqid, oldPath, newPath) => {
              const oldFullPath = safePath(baseDir, oldPath);
              const newFullPath = safePath(baseDir, newPath);
              fs.rename(oldFullPath, newFullPath, (err) => {
                if (err) return sftp.status(reqid, STATUS_CODE.FAILURE);
                sftp.status(reqid, STATUS_CODE.OK);
              });
            });
          });
        });
      });
    }
  );

  server.on('error', (err) => {
    console.error('[Nova SFTP] SFTP Server error:', err.message);
  });

  server.listen(config.sftp.port, config.sftp.host, () => {
    console.log(`[Nova SFTP] Server listening on ${config.sftp.host}:${config.sftp.port}`);
  });

  return server;
}

module.exports = { startSftpServer };
