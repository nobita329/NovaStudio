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
  parsed = parsed.replace(/{{SERVER_MEMORY}}/g, (server.memory || 1024).toString());
  parsed = parsed.replace(/{{SERVER_PORT}}/g, (allocation ? allocation.port : 25565).toString());
  parsed = parsed.replace(/{{SERVER_IP}}/g, '0.0.0.0');

  for (const [key, val] of Object.entries(envVars)) {
    parsed = parsed.replace(new RegExp(`{{${key}}}`, 'g'), val);
  }
  return parsed;
}

async function createServerContainer(server, allocation, egg, envVars = {}) {
  const serverDir = path.resolve(config.paths.servers, String(server.owner_id), String(server.id));
  if (!fs.existsSync(serverDir)) {
    fs.mkdirSync(serverDir, { recursive: true });
  }

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
    Cmd: ['/bin/sh', '-c', startupCmd],
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
        const svRows = await db.query('SELECT env_key, env_value FROM server_variables WHERE server_id = ?', [serverId]);
        if (svRows) {
          for (const r of svRows) envVars[r.env_key] = r.env_value;
        }

        const serverDir = path.resolve(config.paths.servers, String(server.owner_id), String(server.id));
        const jarPath = path.join(serverDir, 'server.jar');
        if (server.startup_command && server.startup_command.includes('server.jar') && !fs.existsSync(jarPath)) {
          const mcjarsService = require('../services/mcjarsService');
          console.log(`[Nova] Auto-downloading server.jar for ${serverId}...`);
          try {
            await mcjarsService.downloadServerJar('PAPER', 'latest', serverDir);
          } catch (e) {
            console.error('[Nova] Jar auto-download failed:', e.message);
          }
        }
      }
    }

    const desiredImage = server ? (server.docker_image || (egg ? egg.docker_image : 'ghcr.io/pterodactyl/yolks:java_21')) : null;

    // Check existing container image and recreate if mismatched
    try {
      const existing = docker.getContainer(containerName);
      const info = await existing.inspect();
      if (info && desiredImage && info.Config && info.Config.Image !== desiredImage) {
        console.log(`[Docker] startServer: Image mismatch for ${containerName} (${info.Config.Image} vs ${desiredImage}). Recreating container...`);
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
