<?php
$title = 'Console — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Console';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<link rel="stylesheet" href="/assets/css/nova-console.css">

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Authentic Arix v2.1.3 Information Bar -->
    <div class="arix-info-bar">
      <div class="arix-server-info">
        <div class="arix-server-name">
          <span><?= e($server['name']) ?></span>
          <span class="arix-status-indicator">
            <?= statusBadge($server['status']) ?>
          </span>
        </div>
        <div class="arix-server-stats-row">
          <!-- Allocation with audio copy trigger -->
          <div class="arix-stat-item arix-server-alloc" onclick="window.arixCopy && window.arixCopy('<?= e($allocation['ip_address'] ?? '127.0.0.1') ?>:<?= e($allocation['port'] ?? '25565') ?>')" title="Click to copy address">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
            <span id="stat-address"><?= e($allocation['ip_address'] ?? '127.0.0.1') ?>:<?= e($allocation['port'] ?? '25565') ?></span>
          </div>

          <div class="arix-stat-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
            <span id="metric-cpu">0.00%</span>
            <span style="color: var(--text-dim);">/ <?= !empty($server['cpu']) ? e($server['cpu']) . '%' : '∞' ?></span>
          </div>

          <div class="arix-stat-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span id="metric-mem">0.0 MiB</span>
            <span style="color: var(--text-dim);">/ <?= round(($server['memory'] ?? 1024) / 1024, 1) ?> GiB</span>
          </div>

          <div class="arix-stat-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
            <span id="metric-disk"><?= round(($server['disk'] ?? 5120) * 0.05, 0) ?> MiB</span>
            <span style="color: var(--text-dim);">/ <?= round(($server['disk'] ?? 5120) / 1024, 1) ?> GiB</span>
          </div>

          <div class="arix-stat-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span id="stat-uptime">0s</span>
          </div>
        </div>
      </div>

      <!-- Arix Power Controls -->
      <div class="arix-power-group">
        <button type="button" class="btn-power btn-power-start" data-power="start" title="Start Server">
          <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
          Start
        </button>
        <button type="button" class="btn-power btn-power-restart" data-power="restart" title="Restart Server">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          Restart
        </button>
        <button type="button" class="btn-power btn-power-stop" data-power="stop" title="Stop Server">
          <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path></svg>
          Stop
        </button>
        <button type="button" class="btn-power btn-power-kill" data-power="kill" title="Force Kill Instance">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          Kill
        </button>
      </div>
    </div>

    <!-- Middle Section: Terminal Window (Left) + Metric Cards Stack (Right) -->
    <div class="nova-middle-grid ">
      <!-- Terminal Window -->
      <div class="nova-terminal-window " id="nova-term-container">
        <!-- Window Titlebar with Mac dots and controls -->
        <div class="terminal-topbar">
          <div class="terminal-title-area">
            <div class="mac-dots">
              <span class="mac-dot dot-red"></span>
              <span class="mac-dot dot-yellow"></span>
              <span class="mac-dot dot-green"></span>
            </div>
            <span class="terminal-heading">Terminal &mdash; <?= e($server['name']) ?></span>
          </div>

          <div class="terminal-actions">
            <button type="button" id="btn-autofit" class="term-btn" title="Toggle Terminal Size">
              <svg width="13" height="13" fill="none" stroke="#f59e0b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
              Auto Fit
            </button>
            <button type="button" id="btn-font-down" class="term-btn" title="Decrease Font Size">A-</button>
            <span id="font-size-label" class="term-size-badge">11px</span>
            <button type="button" id="btn-font-up" class="term-btn" title="Increase Font Size">A+</button>
            <button type="button" id="btn-clear-console" class="term-btn" title="Clear Console History">
              <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
              Clear
            </button>
          </div>
        </div>

        <!-- Terminal Output View -->
        <?php $srvOffline = strtolower($server['status'] ?? 'offline') === 'offline'; ?>
        <div id="terminal-output" class="terminal-screen <?= $srvOffline ? 'is-offline' : '' ?>">
          <?php if ($srvOffline): ?>
            <div class="terminal-offline-placeholder">
              <div class="offline-icon-circle">
                <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m2.828-15.536a9 9 0 0112.728 0M3 3l18 18"></path></svg>
              </div>
              <div class="offline-title">Server is offline</div>
              <div class="offline-subtitle">Start the server to view console output.</div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Terminal Input Bar -->
        <div class="terminal-input-bar <?= $srvOffline ? 'is-disabled' : '' ?>">
          <span class="term-prompt-chevron">&gt;&gt;</span>
          <input type="text" id="terminal-input" class="term-input-field" placeholder="<?= $srvOffline ? 'Server is offline — start the server to send commands' : 'Type a command...' ?>" autocomplete="off" <?= $srvOffline ? 'disabled' : '' ?>>
        </div>
      </div>

      <!-- Right 7 Metric Cards Stack -->
      <div class="nova-metrics-stack ">
        <!-- 1. Address -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Address</div>
            <div class="metric-value-text" id="stat-address"><?= e($allocation['ip_address'] ?? '127.0.0.1') ?>:<?= e($allocation['port'] ?? '25565') ?></div>
          </div>
        </div>

        <!-- 2. Uptime -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Uptime</div>
            <div class="metric-value-text" id="stat-uptime">0s</div>
          </div>
        </div>

        <!-- 3. CPU Load -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">CPU Load</div>
            <div class="metric-value-text">
              <span id="metric-cpu">0.00%</span>
              <span class="metric-limit-dim">/ <?= !empty($server['cpu']) ? e($server['cpu']) . '%' : '∞' ?></span>
            </div>
          </div>
        </div>

        <!-- 4. Memory -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Memory</div>
            <div class="metric-value-text">
              <span id="metric-mem">0.0 MiB</span>
              <span class="metric-limit-dim">/ <?= round(($server['memory'] ?? 1024) / 1024, 1) ?> GiB</span>
            </div>
          </div>
        </div>

        <!-- 5. Disk -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Disk</div>
            <div class="metric-value-text">
              <span id="metric-disk"><?= round(($server['disk'] ?? 5120) * 0.05, 0) ?> MiB</span>
              <span class="metric-limit-dim">/ <?= round(($server['disk'] ?? 5120) / 1024, 1) ?> GiB</span>
            </div>
          </div>
        </div>

        <!-- 6. Network (Inbound) -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Network (Inbound)</div>
            <div class="metric-value-text" id="metric-net-in">0.00 KiB</div>
          </div>
        </div>

        <!-- 7. Network (Outbound) -->
        <div class="nova-metric-card ">
          <div class="metric-icon-wrap">
            <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Network (Outbound)</div>
            <div class="metric-value-text" id="metric-net-out">0.00 KiB</div>
          </div>
        </div>

        <!-- 8. Region (Pterodactyl Region) -->
        <div class="nova-metric-card" id="card-region" title="Click to copy Region info" style="cursor: pointer;">
          <div class="metric-icon-wrap" id="region-flag-wrap">
            <svg id="region-default-icon" width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div class="metric-details">
            <div class="metric-label">Region</div>
            <div class="metric-value-text" id="stat-region" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              <span id="region-country" style="font-weight: 600; color: #ffffff;">Detecting...</span><span class="metric-limit-dim" id="region-city"></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Section: 3 Real-Time Charts -->
    <div class="nova-charts-grid ">
      <!-- 1. CPU Load Chart -->
      <div class="nova-chart-card ">
        <div class="chart-header">
          <span class="chart-title">CPU Load</span>
          <span class="chart-header-value" id="chart-cpu-val">0.00%</span>
        </div>
        <div class="chart-canvas-wrap">
          <canvas id="chart-cpu" class="sparkline-canvas"></canvas>
        </div>
      </div>

      <!-- 2. Memory Chart -->
      <div class="nova-chart-card ">
        <div class="chart-header">
          <span class="chart-title">Memory</span>
          <span class="chart-header-value" id="chart-mem-val">0.0 MiB</span>
        </div>
        <div class="chart-canvas-wrap">
          <canvas id="chart-mem" class="sparkline-canvas"></canvas>
        </div>
      </div>

      <!-- 3. Network Chart -->
      <div class="nova-chart-card ">
        <div class="chart-header">
          <span class="chart-title">
            Network
            <span style="display:inline-flex; gap: 4px; margin-left: 4px;">
              <span style="width: 7px; height: 7px; border-radius: 50%; background: #f43f5e; display: inline-block;" title="Inbound"></span>
              <span style="width: 7px; height: 7px; border-radius: 50%; background: #f59e0b; display: inline-block;" title="Outbound"></span>
            </span>
          </span>
          <span class="chart-header-value" id="chart-net-val" style="color: #f43f5e;">0 B/s</span>
        </div>
        <div class="chart-canvas-wrap">
          <canvas id="chart-net" class="sparkline-canvas"></canvas>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Hidden Status Element for compatibility -->
<span id="server-status-badge" style="display: none;"></span>

<script src="/assets/js/console.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  window.novaCli = new NovaConsole('<?= e($server['id']) ?>', '<?= strtolower($server['status'] ?? 'offline') ?>');
});

// Pterodactyl Region Addon Detector
(function initPterodactylRegion() {
  const serverId = '<?= e($server['id']) ?>';
  const serverIp = '<?= e($allocation['ip_alias'] ?? $allocation['ip_address'] ?? '127.0.0.1') ?>';
  const nodeFqdn = '<?= e($node['fqdn'] ?? '') ?>';
  const ipApiChoice = '<?= e($settings['region-api:ip'] ?? 'ipwho.is') ?>';
  const dnsApiChoice = '<?= e($settings['region-api:dns'] ?? 'DNS-Google') ?>';

  const flagWrap = document.getElementById('region-flag-wrap');
  const countryEl = document.getElementById('region-country');
  const cityEl = document.getElementById('region-city');
  const cardRegion = document.getElementById('card-region');

  if (!flagWrap || !countryEl) return;

  let currentRegionText = '';

  if (cardRegion) {
    cardRegion.addEventListener('click', () => {
      if (currentRegionText) {
        navigator.clipboard.writeText(currentRegionText).then(() => {
          if (typeof window.showNotification === 'function') {
            window.showNotification('Copied', `Region copied: ${currentRegionText}`, 'success');
          }
        }).catch(() => {});
      }
    });
  }

  const cacheKey = `pterodactyl_region_${serverId}_${serverIp}`;
  const cached = sessionStorage.getItem(cacheKey);
  if (cached) {
    try {
      const data = JSON.parse(cached);
      applyRegion(data);
      return;
    } catch(e) {}
  }

  function isLocalIP(ip) {
    if (!ip) return true;
    const patterns = [
      /^127\./,
      /^10\./,
      /^192\.168\./,
      /^172\.(1[6-9]|2\d|3[0-1])\./,
      /^::1$/,
      /^fe80:/,
      /^0\./,
      /^localhost$/i
    ];
    return patterns.some(p => p.test(ip));
  }

  const IPAPIs = {
    'ipwho.is': {
      buildUrl: (ip) => ip ? `https://ipwho.is/${ip}` : 'https://ipwho.is/',
      parse: (d) => (d && d.success !== false && (d.country || d.country_code)) ? {
        country_name: d.country || d.country_code,
        country_code: (d.country_code || '').toUpperCase(),
        city: d.city || ''
      } : null
    },
    'ipapi.co': {
      buildUrl: (ip) => ip ? `https://ipapi.co/${ip}/json/` : 'https://ipapi.co/json/',
      parse: (d) => (d && (d.country_name || d.country_code)) ? {
        country_name: d.country_name || d.country_code,
        country_code: (d.country_code || '').toUpperCase(),
        city: d.city || ''
      } : null
    },
    'ipapi.is': {
      buildUrl: (ip) => ip ? `https://api.ipapi.is/?q=${ip}` : 'https://api.ipapi.is/',
      parse: (d) => (d && d.location) ? {
        country_name: d.location.country || d.location.country_code,
        country_code: (d.location.country_code || '').toUpperCase(),
        city: d.location.city || ''
      } : null
    },
    'geoiplookup.io': {
      buildUrl: (ip) => ip ? `https://json.geoiplookup.io/${ip}` : 'https://json.geoiplookup.io/',
      parse: (d) => (d && (d.country_name || d.country_code)) ? {
        country_name: d.country_name || d.country_code,
        country_code: (d.country_code || '').toUpperCase(),
        city: d.city || ''
      } : null
    }
  };

  const DNSAPIs = {
    'DNS-Cloudflare': async (hostname) => {
      try {
        const res = await fetch(`https://cloudflare-dns.com/dns-query?name=${encodeURIComponent(hostname)}&type=A`, {
          headers: { 'Accept': 'application/dns-json' }
        });
        const data = await res.json();
        const a = data?.Answer?.find(ans => ans.type === 1);
        return a ? a.data : null;
      } catch(e) { return null; }
    },
    'DNS-Google': async (hostname) => {
      try {
        const res = await fetch(`https://dns.google/resolve?name=${encodeURIComponent(hostname)}`);
        const data = await res.json();
        return (data && data.Status === 0 && data.Answer && data.Answer[0]) ? data.Answer[0].data : null;
      } catch(e) { return null; }
    }
  };

  function applyRegion(info) {
    if (!info) return;
    const country = info.country_name || 'Unknown';
    const city = info.city || '';
    currentRegionText = city ? `${country}, ${city}` : country;

    if (countryEl) countryEl.textContent = country;
    if (cityEl) {
      cityEl.textContent = city ? `, ${city}` : '';
    }
    if (cardRegion) {
      cardRegion.title = `Region: ${currentRegionText} (Click to copy)`;
    }

    if (flagWrap && info.country_code) {
      const code = info.country_code.toUpperCase();
      const flagImg = document.createElement('img');
      flagImg.src = `/assets/regions/${code}.svg`;
      flagImg.alt = code;
      flagImg.style.width = '20px';
      flagImg.style.height = '20px';
      flagImg.style.objectFit = 'contain';
      flagImg.style.borderRadius = '3px';
      flagImg.onerror = function() {
        flagWrap.innerHTML = '<svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
      };
      flagWrap.innerHTML = '';
      flagWrap.appendChild(flagImg);
    }
  }

  async function resolveAndFetch() {
    let targetIp = serverIp;
    const ipMatcher = /^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/;

    // 1. If local IP, check node FQDN or browser hostname
    if (isLocalIP(targetIp)) {
      if (nodeFqdn && !isLocalIP(nodeFqdn)) {
        targetIp = nodeFqdn;
      } else {
        const myHost = window.location.hostname;
        if (!isLocalIP(myHost)) {
          targetIp = myHost;
        }
      }
    }

    // 2. If target is a hostname, resolve to IP via DNS API
    let lookupIp = targetIp;
    if (!isLocalIP(targetIp) && !ipMatcher.test(targetIp)) {
      const dnsResolver = DNSAPIs[dnsApiChoice] || DNSAPIs['DNS-Google'];
      const resolved = await dnsResolver(targetIp);
      if (resolved) lookupIp = resolved;
    }

    // If still local, pass empty string to lookup requesting public IP
    const queryIp = isLocalIP(lookupIp) ? '' : lookupIp;

    const primaryApi = IPAPIs[ipApiChoice] || IPAPIs['ipwho.is'];
    const apisToTry = [primaryApi, IPAPIs['ipwho.is'], IPAPIs['ipapi.co'], IPAPIs['geoiplookup.io']];

    for (const api of apisToTry) {
      try {
        const url = api.buildUrl(queryIp);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (res.ok) {
          const data = await res.json();
          const info = api.parse(data);
          if (info && info.country_name) {
            sessionStorage.setItem(cacheKey, JSON.stringify(info));
            applyRegion(info);
            return;
          }
        }
      } catch(err) {
        // try fallback
      }
    }

    // Default fallback
    applyRegion({
      country_name: 'Local Node',
      country_code: '',
      city: 'Localhost'
    });
  }

  resolveAndFetch();
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
