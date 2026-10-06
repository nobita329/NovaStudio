const axios = require('axios');
const fs = require('fs');
const path = require('path');

const MCJARS_API = 'https://mcjars.app/api/v2';

let cachedTypes = null;
let lastTypesFetch = 0;

async function getTypes() {
  const now = Date.now();
  if (cachedTypes && now - lastTypesFetch < 3600000) {
    return cachedTypes;
  }
  try {
    const res = await axios.get(`${MCJARS_API}/types`, { timeout: 10000 });
    cachedTypes = res.data;
    lastTypesFetch = now;
    return cachedTypes;
  } catch (err) {
    console.error('[mcjars] Failed to fetch types:', err.message);
    return cachedTypes || {};
  }
}

async function getBuilds(type) {
  try {
    const upper = type.toUpperCase();
    const res = await axios.get(`${MCJARS_API}/builds/${upper}`, { timeout: 10000 });
    if (res.data && res.data.builds) {
      return res.data.builds;
    }
    return res.data || {};
  } catch (err) {
    console.error(`[mcjars] Failed to fetch builds for ${type}:`, err.message);
    return {};
  }
}

async function downloadServerJar(type, version, targetDir, onProgress = null) {
  try {
    if (!fs.existsSync(targetDir)) {
      fs.mkdirSync(targetDir, { recursive: true });
    }

    const buildsData = await getBuilds(type);
    let targetBuild = null;
    let javaVersion = 21;

    const versions = Object.keys(buildsData);
    if (version && version !== 'latest' && buildsData[version]) {
      targetBuild = buildsData[version].latest;
      javaVersion = buildsData[version].java || 21;
    } else if (versions.length > 0) {
      const chosen = versions[versions.length - 1];
      targetBuild = buildsData[chosen]?.latest;
      javaVersion = buildsData[chosen]?.java || 21;
    }

    if (!targetBuild || !targetBuild.jarUrl) {
      throw new Error(`No jar download URL found for ${type} version ${version}`);
    }

    const jarPath = path.join(targetDir, 'server.jar');
    console.log(`[mcjars] Downloading ${type} (${version || 'latest'}) from ${targetBuild.jarUrl}...`);

    const writer = fs.createWriteStream(jarPath);
    const response = await axios({
      url: targetBuild.jarUrl,
      method: 'GET',
      responseType: 'stream',
      timeout: 60000,
    });

    response.data.pipe(writer);

    await new Promise((resolve, reject) => {
      writer.on('finish', resolve);
      writer.on('error', reject);
    });

    // Auto-create eula.txt
    fs.writeFileSync(path.join(targetDir, 'eula.txt'), 'eula=true\n');

    console.log(`[mcjars] Successfully installed ${type} into ${jarPath}`);
    return {
      success: true,
      jarPath,
      javaVersion,
      fileSize: targetBuild.jarSize || fs.statSync(jarPath).size,
    };
  } catch (err) {
    console.error('[mcjars] Download failed:', err.message);
    throw err;
  }
}

module.exports = {
  getTypes,
  getBuilds,
  downloadServerJar,
};
