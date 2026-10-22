const Docker = require('dockerode');
const path = require('path');
const fs = require('fs');
const config = require('../config');

const docker = new Docker({ socketPath: config.docker.socketPath });

// Store active stdin streams for web terminal command execution
const stdinStreams = new Map();

async function getStdinStream(container, serverId) {
  let stream = stdinStreams.get(serverId);
  if (stream && !stream.destroyed && stream.writable) {
    return stream;
  }
  stream = await container.attach({
    stream: true,
    stdin: true,
    stdout: false,
    stderr: false
  });
  stream.on('error', () => stdinStreams.delete(serverId));
  stream.on('end', () => stdinStreams.delete(serverId));
  stream.on('close', () => stdinStreams.delete(serverId));
  stdinStreams.set(serverId, stream);
  return stream;
}

function cleanupStdinStream(serverId) {
  const stream = stdinStreams.get(serverId);
  if (stream) {
    try {
      if (typeof stream.destroy === 'function') stream.destroy();
      else if (typeof stream.end === 'function') stream.end();
    } catch (e) {}
    stdinStreams.delete(serverId);
  }
}

async function isImageAvailable(imageName) {
  try {
    const images = await docker.listImages();
    return images.some((img) =>
      img.RepoTags && img.RepoTags.some((tag) => tag === imageName || tag.startsWith(imageName))
    );
  } catch (err) {
    return false;
  }
}

async function ensureImage(imageName) {
  const available = await isImageAvailable(imageName);
  if (available) return true;

  console.log(`[Docker] Pulling image ${imageName}...`);
  return new Promise((resolve, reject) => {
    docker.pull(imageName, (err, stream) => {
      if (err) return reject(err);
      docker.modem.followProgress(stream, (err, output) => {
        if (err) return reject(err);
        console.log(`[Docker] Pulled image ${imageName}`);
        resolve(true);
      });
    });
  });
}

function parseStartupCommand(command, server, allocation, envVars = {}) {
  let parsed = command || '';
  const port = allocation ? allocation.port : 25501;
  const mem = server ? (server.memory || 1024) : 1024;

  parsed = parsed.replace(/{{SERVER_MEMORY}}/gi, mem.toString());
  parsed = parsed.replace(/\${SERVER_MEMORY}/gi, mem.toString());
  parsed = parsed.replace(/{{SERVER_PORT}}/gi, port.toString());
  parsed = parsed.replace(/\${SERVER_PORT}/gi, port.toString());
  parsed = parsed.replace(/{{SERVER_IP}}/gi, '0.0.0.0');
  parsed = parsed.replace(/\${SERVER_IP}/gi, '0.0.0.0');

  const jarName = envVars['SERVER.JAR'] || envVars['SERVER_JARFILE'] || envVars['SERVER_JAR'] || 'server.jar';
  parsed = parsed.replace(/{{SERVER\.JAR}}/gi, jarName);
  parsed = parsed.replace(/{{SERVER_JARFILE}}/gi, jarName);
  parsed = parsed.replace(/{{SERVER_JAR}}/gi, jarName);

  for (const [key, val] of Object.entries(envVars)) {
    const esc = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    parsed = parsed.replace(new RegExp(`{{${esc}}}`, 'gi'), String(val));
    parsed = parsed.replace(new RegExp(`\\$\\{${esc}\\}`, 'gi'), String(val));
  }

  // Common egg variable fallbacks if not present in envVars
  const startupScriptVal = envVars['STARTUPSCRIPT'] || envVars['startupscript'] || 'node main.js';
  parsed = parsed.replace(/{{STARTUPSCRIPT}}/gi, startupScriptVal);
  parsed = parsed.replace(/\${STARTUPSCRIPT}/gi, startupScriptVal);
  parsed = parsed.replace(/{{startupscript}}/gi, startupScriptVal);
  parsed = parsed.replace(/\${startupscript}/gi, startupScriptVal);

  const pyFileVal = envVars['PY_FILE'] || envVars['MAIN_FILE'] || 'app.py';
  parsed = parsed.replace(/{{PY_FILE}}/gi, pyFileVal);
  parsed = parsed.replace(/\${PY_FILE}/gi, pyFileVal);

  const reqFileVal = envVars['REQUIREMENTS_FILE'] || 'requirements.txt';
  parsed = parsed.replace(/{{REQUIREMENTS_FILE}}/gi, reqFileVal);
  parsed = parsed.replace(/\${REQUIREMENTS_FILE}/gi, reqFileVal);

  const autoUpdateVal = envVars['AUTO_UPDATE'] || '0';
  parsed = parsed.replace(/{{AUTO_UPDATE}}/gi, autoUpdateVal);
  parsed = parsed.replace(/\${AUTO_UPDATE}/gi, autoUpdateVal);

  const pyPackagesVal = envVars['PY_PACKAGES'] || '';
  parsed = parsed.replace(/{{PY_PACKAGES}}/gi, pyPackagesVal);
  parsed = parsed.replace(/\${PY_PACKAGES}/gi, pyPackagesVal);

  return parsed;
}

function ensureStarterFiles(server, egg, serverDir, envVars = {}) {
  if (!fs.existsSync(serverDir)) {
    fs.mkdirSync(serverDir, { recursive: true });
  }

  const startupStr = server?.startup_command || egg?.startup_command || '';
  const nestId = server?.nest_id || egg?.nest_id;
  const dockerImg = server?.docker_image || egg?.docker_image || '';

  const isPython = nestId === 3 || startupStr.includes('python') || dockerImg.includes('python');
  const isNode = nestId === 2 || startupStr.includes('node') || startupStr.includes('npm') || dockerImg.includes('node');

  if (isPython) {
    const pyFile = envVars['PY_FILE'] || envVars['MAIN_FILE'] || 'app.py';
    const pyPath = path.join(serverDir, pyFile);
    if (!fs.existsSync(pyPath)) {
      const defaultPy = `# Nova Panel - Python Application Starter
import os
import sys
from http.server import HTTPServer, BaseHTTPRequestHandler

PORT = int(os.environ.get("SERVER_PORT", 8080))

class NovaHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header("Content-type", "text/html; charset=utf-8")
        self.end_headers()
        html = f"""<!DOCTYPE html>
<html>
<head><title>Nova Python Server</title><style>body {{ font-family:sans-serif; background:#0f172a; color:#f8fafc; padding:3rem; text-align:center; }} h1 {{ color:#38bdf8; }}</style></head>
<body>
  <h1>🐍 Python Server is Online!</h1>
  <p>Powered by Nova Panel with Generic Python Egg.</p>
  <p>Port: <code>{PORT}</code> | Python: <code>{sys.version.split()[0]}</code></p>
</body>
</html>"""
        self.wfile.write(html.encode("utf-8"))

    def log_message(self, format, *args):
        print(f"[{self.log_date_time_string()}] {args[0]} - {args[1]}", flush=True)

if __name__ == "__main__":
    print(f"[Nova] Starting Python Application on 0.0.0.0:{PORT}...", flush=True)
    server = HTTPServer(("0.0.0.0", PORT), NovaHandler)
    print(f"[Nova] Python HTTP Server listening at http://0.0.0.0:{PORT}", flush=True)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        print("\\n[Nova] Server stopped.", flush=True)
`;
      fs.writeFileSync(pyPath, defaultPy, 'utf8');
      console.log(`[Nova] Generated starter Python file at ${pyPath}`);
    }

    const reqFile = envVars['REQUIREMENTS_FILE'] || 'requirements.txt';
    const reqPath = path.join(serverDir, reqFile);
    if (!fs.existsSync(reqPath)) {
      fs.writeFileSync(reqPath, '# Add your Python dependencies here\n', 'utf8');
    }
  } else if (isNode) {
    const mainFile = (envVars['startupscript']?.includes('main.js') ? 'main.js' : (envVars['MAIN_FILE'] || 'main.js'));
    const mainPath = path.join(serverDir, mainFile);
    const indexPath = path.join(serverDir, 'index.js');
    const pkgPath = path.join(serverDir, 'package.json');

    if (!fs.existsSync(mainPath) && !fs.existsSync(indexPath)) {
      const defaultJs = `// Nova Panel - Node.js Application Starter (RyzeHost Node.js Egg)
const http = require('http');
const port = process.env.SERVER_PORT || process.env.PORT || 3000;

const server = http.createServer((req, res) => {
  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(\`<!DOCTYPE html>
<html>
<head><title>Nova Node.js Server</title><style>body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:3rem;text-align:center;}h1{color:#a855f7;}</style></head>
<body>
  <h1>⚡ Node.js Server is Online!</h1>
  <p>Powered by RyzeHost Node.js Egg on Nova Panel.</p>
  <p>Port: <code>\${port}</code> | Node: <code>\${process.version}</code></p>
</body>
</html>\`);
});

server.listen(port, '0.0.0.0', () => {
  console.log(\`[Nova] Node.js server listening at http://0.0.0.0:\${port}\`);
});
`;
      fs.writeFileSync(mainPath, defaultJs, 'utf8');
      if (!fs.existsSync(indexPath)) {
        fs.writeFileSync(indexPath, defaultJs, 'utf8');
      }
      console.log(`[Nova] Generated starter Node.js files at ${mainPath}`);
    }

    if (!fs.existsSync(pkgPath)) {
      const defaultPkg = {
        name: "nova-nodejs-app",
        version: "1.0.0",
        description: "Node.js Application powered by RyzeHost Egg on Nova Panel",
        main: mainFile,
        scripts: {
          start: `node ${mainFile}`
        },
        dependencies: {}
      };
      fs.writeFileSync(pkgPath, JSON.stringify(defaultPkg, null, 2), 'utf8');
      console.log(`[Nova] Generated starter package.json at ${pkgPath}`);
    }
  }
}

async function createServerContainer(server, allocation, egg, envVars = {}) {
  const serverDir = path.resolve(config.paths.servers, String(server.owner_id), String(server.id));
  ensureStarterFiles(server, egg, serverDir, envVars);

  const containerName = `nova-${server.id}`;

  let imageName = server.docker_image || (egg ? egg.docker_image : 'ghcr.io/pterodactyl/yolks:java_21');

  // Check if container already exists
  try {
    const existing = docker.getContainer(containerName);
    const info = await existing.inspect();
    if (info) {
      const currentImage = info.Config ? info.Config.Image : null;
      if (currentImage === imageName) {
        console.log(`[Docker] Container ${containerName} already exists with matching image (${imageName}).`);
        return existing;
      }
      console.log(`[Docker] Image mismatch for ${containerName}: currently ${currentImage}, required ${imageName}. Recreating container...`);
      if (info.State && info.State.Running) {
        await existing.stop({ t: 2 }).catch(() => {});
      }
      await existing.remove({ force: true }).catch(() => {});
    }
  } catch (e) {
    // container doesn't exist yet
  }
  try {
    await ensureImage(imageName);
  } catch (err) {
    console.warn(`[Docker] Warning: Could not pull ${imageName}: ${err.message}.`);
    const available = await isImageAvailable(imageName);
    if (!available) {
      console.warn(`[Docker] Falling back to ghcr.io/pterodactyl/yolks:java_21`);
      imageName = 'ghcr.io/pterodactyl/yolks:java_21';
      await ensureImage(imageName).catch(() => {});
    }
  }

  const port = allocation ? allocation.port : 25501;
  const portKey = `${port}/tcp`;

  const startupCmd = parseStartupCommand(
    server.startup_command || egg.startup_command,
    server,
    allocation,
    envVars
  );

  const envArray = [
    `SERVER_MEMORY=${server.memory}`,
    `SERVER_PORT=${port}`,
    `SERVER_IP=0.0.0.0`,
    `STARTUP=${startupCmd}`,
    `TZ=UTC`,
    ...Object.entries(envVars).map(([k, v]) => `${k}=${v}`)
  ];

  const memBytes = (server.memory || 1024) * 1024 * 1024;
  const swapBytes = (server.swap || 0) * 1024 * 1024;

  const createOptions = {
    name: containerName,
    Image: imageName,
    WorkingDir: '/home/container',
    Tty: true,
    OpenStdin: true,
    StdinOnce: false,
    AttachStdin: true,
    AttachStdout: true,
    AttachStderr: true,
    Env: envArray,
    Cmd: ['/bin/bash', '-c', startupCmd],
    HostConfig: {
      Binds: [`${serverDir}:/home/container`],
      PortBindings: {
        [portKey]: [{ HostPort: String(port) }]
      },
      Memory: memBytes,
      MemorySwap: memBytes + swapBytes,
      NanoCPUs: Math.floor((server.cpu || 100) * 10000000),
      RestartPolicy: { Name: 'no' }
    }
  };

  const container = await docker.createContainer(createOptions);
  console.log(`[Docker] Created container ${containerName} (${container.id.slice(0, 12)})`);
  return container;
}

async function startServer(serverId, db) {
  const containerName = `nova-${serverId}`;
  try {
    let server = null;
    let alloc = null;
    let egg = null;
    let envVars = {};

    if (db) {
      server = await db.get('SELECT * FROM servers WHERE id = ?', [serverId]);
      if (server) {
        alloc = await db.get('SELECT * FROM allocations WHERE id = ?', [server.allocation_id]);
        egg = await db.get('SELECT * FROM eggs WHERE id = ?', [server.egg_id]);

        if (egg && egg.environment_json) {
          try {
            const eggDefaults = JSON.parse(egg.environment_json);
            for (const [k, v] of Object.entries(eggDefaults)) {
              envVars[k] = v;
            }
          } catch (e) {}
        }

        const svRows = await db.query('SELECT env_key, env_value FROM server_variables WHERE server_id = ?', [serverId]);
        if (svRows) {
          for (const r of svRows) envVars[r.env_key] = r.env_value;
        }

        const serverDir = path.resolve(config.paths.servers, String(server.owner_id), String(server.id));
        ensureStarterFiles(server, egg, serverDir, envVars);

        const jarName = envVars['SERVER_JARFILE'] || envVars['SERVER.JAR'] || envVars['SERVER_JAR'] || 'server.jar';
        const jarPath = path.join(serverDir, jarName);
        const startupStr = server.startup_command || (egg ? egg.startup_command : '');
        if (startupStr && (startupStr.includes('.jar') || startupStr.includes('SERVER_JARFILE') || startupStr.includes('SERVER.JAR') || startupStr.includes('java')) && !fs.existsSync(jarPath)) {
          const mcjarsService = require('../services/mcjarsService');
          console.log(`[Nova] Auto-downloading ${jarName} for ${serverId}...`);
          try {
            await mcjarsService.downloadServerJar('PAPER', envVars['MINECRAFT_VERSION'] || 'latest', serverDir, null, jarName);
          } catch (e) {
            console.error('[Nova] Jar auto-download failed:', e.message);
          }
        }
      }
    }

    const desiredImage = server ? (server.docker_image || (egg ? egg.docker_image : 'ghcr.io/pterodactyl/yolks:java_21')) : null;
    const desiredCmd = server ? parseStartupCommand(server.startup_command || (egg ? egg.startup_command : ''), server, alloc, envVars) : null;

    // Check existing container image/command and recreate if mismatched
    try {
      const existing = docker.getContainer(containerName);
      const info = await existing.inspect();
      const currentImage = info && info.Config ? info.Config.Image : null;
      const currentCmd = info && info.Config && info.Config.Cmd ? info.Config.Cmd[2] : null;
      const currentShell = info && info.Config && info.Config.Cmd ? info.Config.Cmd[0] : null;

      const needsRecreate = info && (
        (desiredImage && currentImage !== desiredImage) ||
        (desiredCmd && currentCmd !== desiredCmd) ||
        (currentShell && currentShell !== '/bin/bash')
      );

      if (needsRecreate) {
        console.log(`[Docker] startServer: Config mismatch for ${containerName}. Recreating container...`);
        if (info.State && info.State.Running) {
          await existing.stop({ t: 2 }).catch(() => {});
        }
        await existing.remove({ force: true }).catch(() => {});
        if (server) {
          await createServerContainer(server, alloc, egg, envVars);
        }
      }
    } catch (e) {
      // Container might not exist yet; will be created below
    }

    const container = docker.getContainer(containerName);
    try {
      await container.start();
    } catch (startErr) {
      if (startErr.statusCode === 404) {
        // Container doesn't exist, recreate it
        if (server) {
          await createServerContainer(server, alloc, egg, envVars);
          const newCont = docker.getContainer(containerName);
          await newCont.start();
        } else {
          throw startErr;
        }
      } else if (startErr.statusCode !== 304) {
        throw startErr;
      }
    }
    if (db) {
      await db.query("UPDATE servers SET status = 'online' WHERE id = ?", [serverId]);
    }
    return { success: true, status: 'online' };
  } catch (err) {
    if (err.statusCode === 304) {
      if (db) await db.query("UPDATE servers SET status = 'online' WHERE id = ?", [serverId]);
      return { success: true, status: 'online' };
    }
    throw err;
  }
}

async function stopServer(serverId, db, timeout = 15) {
  cleanupStdinStream(serverId);
  const containerName = `nova-${serverId}`;
  try {
    const container = docker.getContainer(containerName);
    await container.stop({ t: timeout });
  } catch (err) {
    // 304 already stopped, 404 not found, 409 not running
    if (err.statusCode !== 304 && err.statusCode !== 404 && err.statusCode !== 409) {
      console.warn(`[Docker] Stop warning for ${serverId}:`, err.message);
    }
  }
  if (db) {
    await db.query("UPDATE servers SET status = 'offline' WHERE id = ?", [serverId]);
  }
  return { success: true, status: 'offline' };
}

async function restartServer(serverId, db) {
  cleanupStdinStream(serverId);
  const containerName = `nova-${serverId}`;
  try {
    const container = docker.getContainer(containerName);
    await container.restart({ t: 10 });
    if (db) {
      await db.query("UPDATE servers SET status = 'online' WHERE id = ?", [serverId]);
    }
    return { success: true, status: 'online' };
  } catch (err) {
    // If not running or not found, try normal start
    return await startServer(serverId, db);
  }
}

async function killServer(serverId, db) {
  cleanupStdinStream(serverId);
  const containerName = `nova-${serverId}`;
  try {
    const container = docker.getContainer(containerName);
    await container.kill();
  } catch (err) {
    // 409 not running, 404 not found
    if (err.statusCode !== 409 && err.statusCode !== 404) {
      console.warn(`[Docker] Kill warning for ${serverId}:`, err.message);
    }
  }
  if (db) {
    await db.query("UPDATE servers SET status = 'offline' WHERE id = ?", [serverId]);
  }
  return { success: true, status: 'offline' };
}

async function deleteServerContainer(serverId) {
  cleanupStdinStream(serverId);
  const containerName = `nova-${serverId}`;
  try {
    const container = docker.getContainer(containerName);
    try {
      await container.stop({ t: 5 });
    } catch (e) {}
    await container.remove({ force: true, v: false });
    console.log(`[Docker] Removed container ${containerName}`);
  } catch (e) {
    // Ignored if not exists
  }
}

const prevNetStats = new Map();

async function getServerStats(serverId) {
  const containerName = `nova-${serverId}`;
  try {
    const container = docker.getContainer(containerName);
    const info = await container.inspect();
    if (!info.State || !info.State.Running) {
      prevNetStats.delete(serverId);
      return {
        status: 'offline',
        cpu: 0,
        memory: 0,
        memoryLimit: 0,
        memoryPercent: 0,
        networkRx: 0,
        networkTx: 0,
        networkSpeedRx: 0,
        networkSpeedTx: 0,
        uptime: 0
      };
    }

    const stats = await container.stats({ stream: false });
    
    // Calculate CPU usage
    let cpuPercent = 0.0;
    if (stats.cpu_stats && stats.precpu_stats) {
      const cpuDelta = stats.cpu_stats.cpu_usage.total_usage - stats.precpu_stats.cpu_usage.total_usage;
      const systemDelta = (stats.cpu_stats.system_cpu_usage || 0) - (stats.precpu_stats.system_cpu_usage || 0);
      const onlineCpus = stats.cpu_stats.online_cpus || (stats.cpu_stats.cpu_usage.percpu_usage ? stats.cpu_stats.cpu_usage.percpu_usage.length : 1);
      if (systemDelta > 0 && cpuDelta > 0) {
        cpuPercent = ((cpuDelta / systemDelta) * onlineCpus * 100.0);
      }
    }

    // Memory usage
    const memoryUsage = stats.memory_stats ? (stats.memory_stats.usage || 0) : 0;
    const memoryLimit = stats.memory_stats ? (stats.memory_stats.limit || 1) : 1;
    const memoryPercent = ((memoryUsage / memoryLimit) * 100.0);

    // Network I/O
    let netRx = 0;
    let netTx = 0;
    if (stats.networks) {
      for (const net of Object.values(stats.networks)) {
        netRx += net.rx_bytes || 0;
        netTx += net.tx_bytes || 0;
      }
    }

    // Uptime
    let uptime = 0;
    if (info.State && info.State.StartedAt) {
      uptime = Math.max(0, Math.floor((Date.now() - new Date(info.State.StartedAt).getTime()) / 1000));
    }

    // Network speed calculation
    const prev = prevNetStats.get(serverId);
    const now = Date.now();
    let speedRx = 0;
    let speedTx = 0;
    if (prev) {
      const dt = Math.max(0.2, (now - prev.time) / 1000);
      speedRx = Math.max(0, Math.round((netRx - prev.rx) / dt));
      speedTx = Math.max(0, Math.round((netTx - prev.tx) / dt));
    }
    prevNetStats.set(serverId, { time: now, rx: netRx, tx: netTx });

    return {
      status: 'online',
      cpu: parseFloat(cpuPercent.toFixed(2)),
      memory: Math.round(memoryUsage / (1024 * 1024)),
      memoryLimit: Math.round(memoryLimit / (1024 * 1024)),
      memoryPercent: parseFloat(memoryPercent.toFixed(2)),
      networkRx: netRx,
      networkTx: netTx,
      networkSpeedRx: speedRx,
      networkSpeedTx: speedTx,
      uptime: uptime
    };
  } catch (err) {
    prevNetStats.delete(serverId);
    return {
      status: 'offline',
      cpu: 0,
      memory: 0,
      memoryLimit: 0,
      memoryPercent: 0,
      networkRx: 0,
      networkTx: 0,
      networkSpeedRx: 0,
      networkSpeedTx: 0,
      uptime: 0
    };
  }
}

async function sendCommand(serverId, command) {
  const containerName = `nova-${serverId}`;
  const container = docker.getContainer(containerName);

  const info = await container.inspect().catch(() => null);
  if (!info || !info.State || !info.State.Running) {
    throw new Error('Server container is not running');
  }

  const cleanCmd = String(command).trim();
  if (!cleanCmd) return true;

  // 1. Primary: Write directly to container stdin stream (standard for game consoles / CLI)
  try {
    const stream = await getStdinStream(container, serverId);
    stream.write(cleanCmd + '\n');
    return true;
  } catch (err) {
    console.warn(`[Docker] Stdin write failed for ${serverId}, attempting exec fallback:`, err.message);
    cleanupStdinStream(serverId);
  }

  // 2. Fallback: exec command inside container
  const exec = await container.exec({
    AttachStdin: false,
    AttachStdout: true,
    AttachStderr: true,
    Tty: true,
    Cmd: ['/bin/sh', '-c', cleanCmd]
  });

  return new Promise((resolve, reject) => {
    exec.start({ Detach: false, Tty: true }, (err, stream) => {
      if (err) return reject(err);
      let output = '';
      stream.on('data', (d) => { output += d.toString('utf8'); });
      stream.on('end', () => resolve(output));
    });
  });
}

module.exports = {
  docker,
  ensureImage,
  createServerContainer,
  startServer,
  stopServer,
  restartServer,
  killServer,
  deleteServerContainer,
  getServerStats,
  sendCommand,
};
