const WebSocket = require('ws');
const { getDb } = require('../database');
const dockerService = require('../docker/dockerService');
const { docker } = dockerService;

/**
 * Robustly format Docker log chunks:
 * Supports both raw TTY streams (when Tty: true) and
 * Docker multiplexed stdout/stderr streams (when Tty: false).
 */
function formatLogChunk(chunk) {
  if (!chunk || chunk.length === 0) return '';

  // Check if buffer starts with Docker 8-byte multiplex header:
  // [1|2, 0, 0, 0, size_b3, size_b2, size_b1, size_b0]
  if (
    chunk.length >= 8 &&
    (chunk[0] === 1 || chunk[0] === 2) &&
    chunk[1] === 0 &&
    chunk[2] === 0 &&
    chunk[3] === 0
  ) {
    let result = '';
    let offset = 0;
    while (offset + 8 <= chunk.length) {
      const type = chunk[offset];
      const isHeader =
        (type === 1 || type === 2) &&
        chunk[offset + 1] === 0 &&
        chunk[offset + 2] === 0 &&
        chunk[offset + 3] === 0;

      if (!isHeader) {
        // Fallback: append remaining bytes as raw text
        result += chunk.subarray(offset).toString('utf8');
        break;
      }

      const frameLength = chunk.readUInt32BE(offset + 4);
      const frameStart = offset + 8;
      const frameEnd = Math.min(frameStart + frameLength, chunk.length);
      result += chunk.subarray(frameStart, frameEnd).toString('utf8');
      offset = frameStart + frameLength;
    }
    return result;
  }

  // Raw TTY stream (Tty: true)
  return chunk.toString('utf8');
}

function setupConsoleWs(serverInstance) {
  const wss = new WebSocket.Server({ noServer: true });

  serverInstance.on('upgrade', async (request, socket, head) => {
    const url = new URL(request.url, `http://${request.headers.host}`);
    const match = url.pathname.match(/^\/ws\/server\/([a-zA-Z0-9_-]+)/);

    if (!match) {
      socket.destroy();
      return;
    }

    const serverId = match[1];

    wss.handleUpgrade(request, socket, head, (ws) => {
      wss.emit('connection', ws, request, serverId);
    });
  });

  wss.on('connection', async (ws, req, serverId) => {
    console.log(`[Nova WS] Connected to terminal for server ${serverId}`);
    const db = getDb();
    const server = await db.get('SELECT * FROM servers WHERE id = ?', [serverId]);

    if (!server) {
      ws.send(JSON.stringify({ event: 'error', message: 'Server not found' }));
      return ws.close();
    }

    const containerName = `nova-${serverId}`;
    let logStream = null;
    let isAttaching = false;
    let statsInterval = null;

    /**
     * Dynamically attaches to the container logs for the ACTIVE session.
     * When online, follows live stdout/stderr for the current session only.
     * When offline, does NOT replay old/previous session logs.
     */
    async function attachLogStream(sinceTimestamp = null) {
      if (isAttaching || ws.readyState !== WebSocket.OPEN) return;
      isAttaching = true;

      // Close old stream if active
      if (logStream) {
        try {
          if (typeof logStream.destroy === 'function') logStream.destroy();
          else if (typeof logStream.end === 'function') logStream.end();
        } catch (e) {}
        logStream = null;
      }

      try {
        const container = docker.getContainer(containerName);
        const info = await container.inspect().catch(() => null);

        if (!info || !info.State || !info.State.Running) {
          // Server is offline: do not send any stale historical logs
          isAttaching = false;
          if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
          }
          return;
        }

        // Determine session start time so old previous session logs are never streamed
        let since = sinceTimestamp;
        if (!since && info.State.StartedAt) {
          since = Math.floor(new Date(info.State.StartedAt).getTime() / 1000);
        }

        const logOpts = {
          follow: true,
          stdout: true,
          stderr: true,
          timestamps: false,
        };

        if (since && since > 0) {
          logOpts.since = since;
        } else {
          logOpts.tail = 0;
        }

        const stream = await container.logs(logOpts);
        logStream = stream;

        stream.on('data', (chunk) => {
          if (ws.readyState !== WebSocket.OPEN) return;
          const text = formatLogChunk(chunk);
          if (text) {
            ws.send(JSON.stringify({ event: 'log', data: text }));
          }
        });

        stream.on('end', () => {
          logStream = null;
          if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
          }
        });

        stream.on('error', (err) => {
          logStream = null;
        });
      } catch (err) {
        logStream = null;
      } finally {
        isAttaching = false;
      }
    }

    // Initial check & log stream connection (only if running)
    await attachLogStream();

    // Live Stats Poller & Auto-Stream Reattachment (every 1.2s)
    statsInterval = setInterval(async () => {
      if (ws.readyState !== WebSocket.OPEN) return;
      try {
        const stats = await dockerService.getServerStats(serverId);
        ws.send(JSON.stringify({ event: 'stats', data: stats }));

        if (stats.status === 'offline') {
          if (logStream) {
            try { logStream.destroy(); } catch (e) {}
            logStream = null;
          }
        } else if (stats.status === 'online' && !logStream && !isAttaching) {
          attachLogStream();
        }
      } catch (e) {}
    }, 1200);

    // Client incoming commands & power signals
    ws.on('message', async (message) => {
      try {
        const msg = JSON.parse(message);
        if (msg.event === 'command' && msg.command) {
          console.log(`[Nova WS] Executing command on ${serverId}: ${msg.command}`);
          try {
            await dockerService.sendCommand(serverId, msg.command);
          } catch (cmdErr) {
            ws.send(
              JSON.stringify({
                event: 'log',
                data: `\x1b[31m[Nova Daemon] Command error: ${cmdErr.message}\x1b[0m\r\n`,
              })
            );
          }
        } else if (msg.event === 'power' && msg.signal) {
          if (msg.signal === 'start') {
            try {
              const startTs = Math.floor(Date.now() / 1000);
              await dockerService.startServer(serverId, db);
              ws.send(JSON.stringify({ event: 'status', status: 'online' }));
              setTimeout(() => attachLogStream(startTs), 400);
            } catch (pErr) {
              ws.send(
                JSON.stringify({
                  event: 'log',
                  data: `\x1b[31m[Nova Daemon] Start failed: ${pErr.message}\x1b[0m\r\n`,
                })
              );
              ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
            }
          } else if (msg.signal === 'stop') {
            if (logStream && typeof logStream.destroy === 'function') {
              logStream.destroy();
              logStream = null;
            }
            await dockerService.stopServer(serverId, db);
            ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
          } else if (msg.signal === 'restart') {
            try {
              if (logStream && typeof logStream.destroy === 'function') {
                logStream.destroy();
                logStream = null;
              }
              const restartTs = Math.floor(Date.now() / 1000);
              await dockerService.restartServer(serverId, db);
              ws.send(JSON.stringify({ event: 'status', status: 'online' }));
              setTimeout(() => attachLogStream(restartTs), 500);
            } catch (pErr) {
              ws.send(
                JSON.stringify({
                  event: 'log',
                  data: `\x1b[31m[Nova Daemon] Restart failed: ${pErr.message}\x1b[0m\r\n`,
                })
              );
              ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
            }
          } else if (msg.signal === 'kill') {
            if (logStream && typeof logStream.destroy === 'function') {
              logStream.destroy();
              logStream = null;
            }
            await dockerService.killServer(serverId, db);
            ws.send(JSON.stringify({ event: 'status', status: 'offline' }));
          }
        }
      } catch (err) {
        console.error('[Nova WS] Message parse error:', err.message);
      }
    });

    ws.on('close', () => {
      if (logStream && typeof logStream.destroy === 'function') {
        logStream.destroy();
        logStream = null;
      }
      if (statsInterval) clearInterval(statsInterval);
      console.log(`[Nova WS] Terminal disconnected for server ${serverId}`);
    });
  });

  return wss;
}

module.exports = { setupConsoleWs };
