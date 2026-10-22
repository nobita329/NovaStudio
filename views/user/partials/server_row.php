<?php
/**
 * Production Pterodactyl/Arix-Style Server List Row Component
 */
$sId = e($srv['id'] ?? '');
$sName = e($srv['name'] ?? 'Server');
$sStatus = strtolower($srv['status'] ?? 'offline');
$sIp = e($srv['ip_address'] ?? '0.0.0.0');
$sPort = e($srv['primary_port'] ?? '25501');
$sAddress = $sIp . ':' . $sPort;
$sCpuLimit = !empty($srv['cpu']) ? e($srv['cpu']) . '%' : '100%';
$sMemLimit = round(($srv['memory'] ?? 1024) / 1024, 1) . ' GiB';
$sDiskLimit = round(($srv['disk'] ?? 5120) / 1024, 1) . ' GiB';
$sDiskUsed = round(($srv['disk'] ?? 5120) * 0.05, 0) . ' MiB';
$sSearch = strtolower($sName . ' ' . $sAddress . ' ' . ($srv['node_name'] ?? '') . ' ' . ($srv['uuid'] ?? '') . ' ' . $sId);
$isLive = in_array($sStatus, ['online', 'running']);
?>
<div class="server-row-box" data-server-id="<?= $sId ?>" data-status="<?= $sStatus ?>" data-search="<?= e($sSearch) ?>">
  <!-- Column 1: Server Info (Icon, Name, UUID & Node) -->
  <div class="server-row-col-info">
    <div class="server-row-icon">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
    </div>
    <div class="server-row-title-wrap">
      <a href="/server/<?= $sId ?>/console" class="server-row-name" title="Open Console for <?= $sName ?>">
        <?= $sName ?>
      </a>
      <div class="server-row-sub">
        <span class="server-row-uuid"><?= e(substr($srv['uuid'] ?? $sId, 0, 8)) ?></span>
        <?php if (!empty($srv['node_name'])): ?>
          <span class="server-row-node" title="Node: <?= e($srv['node_name']) ?>"><?= e($srv['node_name']) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Column 2: Address Pill -->
  <div class="server-row-col-address">
    <div class="server-address-pill" onclick="window.arixCopy && window.arixCopy('<?= $sAddress ?>');" title="Click to copy connection address" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
      <svg class="pill-icon" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
      <span class="pill-text"><?= $sAddress ?></span>
      <svg class="pill-copy-icon" width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    </div>
  </div>

  <!-- Column 3: Live Stats (CPU, RAM, Disk, Uptime) -->
  <div class="server-row-col-stats">
    <div class="server-stat-unit" title="CPU Usage / Limit">
      <svg class="stat-glyph" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
      <span class="stat-content">
        <span class="stat-label">CPU</span>
        <span class="stat-val stat-cpu-val">0.00%</span>
        <span class="stat-sep">/</span>
        <span class="stat-limit"><?= $sCpuLimit ?></span>
      </span>
    </div>
    <div class="server-stat-unit" title="Memory Usage / Allocation">
      <svg class="stat-glyph" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      <span class="stat-content">
        <span class="stat-label">RAM</span>
        <span class="stat-val stat-mem-val">0.0 MiB</span>
        <span class="stat-sep">/</span>
        <span class="stat-limit"><?= $sMemLimit ?></span>
      </span>
    </div>
    <div class="server-stat-unit" title="Disk Usage / Quota">
      <svg class="stat-glyph" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"/></svg>
      <span class="stat-content">
        <span class="stat-label">Disk</span>
        <span class="stat-val stat-disk-val"><?= $sDiskUsed ?></span>
        <span class="stat-sep">/</span>
        <span class="stat-limit"><?= $sDiskLimit ?></span>
      </span>
    </div>
    <div class="server-stat-unit" title="Uptime Duration">
      <svg class="stat-glyph" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span class="stat-content">
        <span class="stat-label">Up</span>
        <span class="stat-val stat-uptime-val">0s</span>
      </span>
    </div>
  </div>

  <!-- Column 4: Status Badge -->
  <div class="server-row-col-status">
    <span class="server-card-badge-wrap">
      <?= statusBadge($sStatus) ?>
    </span>
  </div>

  <!-- Column 5: Power Actions -->
  <div class="server-row-col-actions">
    <div class="server-actions-cluster">
      <button type="button" class="btn-srv-action btn-srv-start" data-action="start" data-server-id="<?= $sId ?>" title="Start server <?= $sName ?>" aria-label="Start server <?= $sName ?>">
        <svg class="btn-glyph" width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
      </button>
      <button type="button" class="btn-srv-action btn-srv-restart" data-action="restart" data-server-id="<?= $sId ?>" title="Restart server <?= $sName ?>" aria-label="Restart server <?= $sName ?>">
        <svg class="btn-glyph" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
      </button>
      <button type="button" class="btn-srv-action btn-srv-stop" data-action="stop" data-server-id="<?= $sId ?>" title="Stop server <?= $sName ?>" aria-label="Stop server <?= $sName ?>">
        <svg class="btn-glyph" width="12" height="12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"/></svg>
      </button>
      <button type="button" class="btn-srv-action btn-srv-kill" data-action="kill" data-server-id="<?= $sId ?>" title="Force kill server <?= $sName ?>" aria-label="Force kill server <?= $sName ?>">
        <svg class="btn-glyph" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </button>
    </div>
  </div>
</div>
