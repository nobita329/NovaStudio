const { spawn } = require('child_process');
const path = require('path');

function cleanOptions(options) {
  const safe = {};
  if (options._locals) {
    for (const [k, v] of Object.entries(options._locals)) {
      if (k === 'settings') {
        safe.settings = v;
        continue;
      }
      try {
        JSON.stringify(v);
        safe[k] = v;
      } catch (e) {}
    }
  }
  for (const [key, val] of Object.entries(options)) {
    if (['settings', '_locals', 'cache'].includes(key)) continue;
    try {
      JSON.stringify(val);
      safe[key] = val;
    } catch (e) {}
  }
  return safe;
}

function renderPhp(filePath, options, callback) {
  try {
    const safeData = cleanOptions(options);
    const runnerPath = path.join(__dirname, 'runner.php');

    const phpProc = spawn('php', ['-d', 'xdebug.mode=off', runnerPath, filePath], {
      cwd: path.dirname(filePath),
      env: process.env,
    });

    let stdout = '';
    let stderr = '';

    phpProc.stdout.on('data', (chunk) => {
      stdout += chunk;
    });

    phpProc.stderr.on('data', (chunk) => {
      stderr += chunk;
    });

    phpProc.on('close', (code) => {
      if (code !== 0 && !stdout) {
        return callback(new Error(`PHP rendering error (${code}): ${stderr || 'Unknown error'}`));
      }
      callback(null, stdout);
    });

    phpProc.stdin.write(JSON.stringify(safeData));
    phpProc.stdin.end();
  } catch (err) {
    callback(err);
  }
}

module.exports = { renderPhp };
