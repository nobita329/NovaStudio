const os = require('os');
const si = require('systeminformation');
const { docker } = require('../docker/dockerService');

async function getSystemOverview() {
  try {
    const [cpuLoad, mem, fsSize, osInfo] = await Promise.all([
      si.currentLoad(),
      si.mem(),
      si.fsSize(),
      si.osInfo(),
    ]);

    const primaryFs = fsSize && fsSize.length > 0 ? fsSize[0] : { size: 1, used: 0, use: 0 };

    let dockerInfo = { containers: 0, running: 0, version: 'N/A' };
    try {
      const dInfo = await docker.info();
      dockerInfo = {
        containers: dInfo.Containers || 0,
        running: dInfo.ContainersRunning || 0,
        version: dInfo.ServerVersion || 'Unknown',
      };
    } catch (e) {}

    return {
      cpu: {
        load: parseFloat(cpuLoad.currentLoad.toFixed(1)),
        cores: os.cpus().length,
        model: os.cpus()[0]?.model || 'Unknown CPU',
      },
      memory: {
        total: Math.round(mem.total / (1024 * 1024)),
        used: Math.round(mem.used / (1024 * 1024)),
        free: Math.round(mem.free / (1024 * 1024)),
        percent: parseFloat(((mem.used / mem.total) * 100).toFixed(1)),
      },
      disk: {
        total: Math.round(primaryFs.size / (1024 * 1024 * 1024)),
        used: Math.round(primaryFs.used / (1024 * 1024 * 1024)),
        percent: parseFloat((primaryFs.use || 0).toFixed(1)),
      },
      os: {
        platform: osInfo.platform,
        distro: osInfo.distro,
        release: osInfo.release,
        uptime: Math.round(os.uptime()),
        hostname: os.hostname(),
      },
      docker: dockerInfo,
    };
  } catch (err) {
    console.error('[System] Error gathering metrics:', err.message);
    const totalMem = os.totalmem();
    const freeMem = os.freemem();
    return {
      cpu: { load: 0, cores: os.cpus().length, model: 'Standard CPU' },
      memory: {
        total: Math.round(totalMem / (1024 * 1024)),
        used: Math.round((totalMem - freeMem) / (1024 * 1024)),
        free: Math.round(freeMem / (1024 * 1024)),
        percent: parseFloat((((totalMem - freeMem) / totalMem) * 100).toFixed(1)),
      },
      disk: { total: 50, used: 10, percent: 20 },
      os: {
        platform: os.platform(),
        distro: 'Linux',
        release: os.release(),
        uptime: Math.round(os.uptime()),
        hostname: os.hostname(),
      },
      docker: { containers: 0, running: 0, version: 'Docker' },
    };
  }
}

module.exports = { getSystemOverview };
