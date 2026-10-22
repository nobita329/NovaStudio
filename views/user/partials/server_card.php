<?php
/**
 * Production Pterodactyl/VPS-Style Server Card Component
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
$nodeName = !empty($srv['node_name']) ? e($srv['node_name']) : 'Local Node';
?>
<div class="server-card-box" data-server-id="<?= $sId ?>" data-status="<?= $sStatus ?>" data-search="<?= e($sSearch) ?>">
  <!-- 1. Top Row: Avatar, Title, Status Badge, and Node Badge -->
  <div class="server-card-top">
    <div class="server-card-title-group">
      <div class="server-avatar-icon">
        <img src="/assets/img/minecraft-block.png" alt="Minecraft" class="server-avatar-img" onerror="this.src='/assets/img/minecraft-grass.svg'">
      </div>
      <div class="server-card-heading-cluster">
        <a href="/server/<?= $sId ?>/console" class="server-card-title-link" title="Open Console for <?= $sName ?>">
          <?= $sName ?>
        </a>
        <span class="server-card-badge-wrap">
          <span class="status-badge <?= $isLive ? 'badge-online' : 'badge-offline' ?>">
            <span class="badge-dot"></span>
            <?= $isLive ? 'Online' : 'Offline' ?>
          </span>
        </span>
      </div>
    </div>

    <div class="server-card-top-badges">
      <span class="server-card-node-badge" title="Node Location: <?= $nodeName ?>">
        <svg class="node-badge-icon" width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
        <span><?= $nodeName ?></span>
      </span>
    </div>
  </div>

  <!-- 2. Middle Row: Clean Horizontal Layout (IP Address, CPU, RAM, DISK, UPTIME) -->
  <div class="server-card-mid">
    <!-- Address Pill -->
    <div class="server-card-address-col">
      <div class="server-address-pill" onclick="window.arixCopy && window.arixCopy('<?= $sAddress ?>');" title="Click to copy connection address" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();this.click();}">
        <svg class="pill-icon" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
        <span class="pill-text"><?= $sAddress ?></span>
      </div>
    </div>

    <!-- Divider 1 -->
    <div class="server-card-vsep"></div>

    <!-- CPU Section -->
    <div class="server-stat-cell server-stat-cpu" title="CPU Usage / Limit">
      <div class="stat-cell-top">
        <div class="stat-cell-label-wrap">
          <svg class="stat-glyph glyph-cpu" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
          <span class="stat-cell-label">CPU</span>
        </div>
        <div class="stat-cell-value stat-cpu-val">0.00%</div>
      </div>
      <div class="stat-cell-bottom">
        <div class="stat-progress-track">
          <div class="stat-progress-bar stat-cpu-bar" style="width: 0%;"></div>
        </div>
        <span class="stat-cell-limit stat-cpu-limit"><?= $sCpuLimit ?></span>
      </div>
    </div>

    <!-- Divider 2 -->
    <div class="server-card-vsep"></div>

    <!-- RAM Section -->
    <div class="server-stat-cell server-stat-ram" title="Memory Usage / Allocation">
      <div class="stat-cell-top">
        <div class="stat-cell-label-wrap">
          <svg class="stat-glyph glyph-ram" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 19v-3"/><path d="M10 19v-3"/><path d="M14 19v-3"/><path d="M18 19v-3"/><path d="M4 16h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z"/><path d="M6 8h4v4H6z"/><path d="M14 8h4v4h-4z"/></svg>
          <span class="stat-cell-label">RAM</span>
        </div>
        <div class="stat-cell-value stat-mem-val">0.0 MiB</div>
      </div>
      <div class="stat-cell-bottom">
        <div class="stat-progress-track">
          <div class="stat-progress-bar stat-mem-bar" style="width: 0%;"></div>
        </div>
        <span class="stat-cell-limit stat-mem-limit"><?= $sMemLimit ?></span>
      </div>
    </div>

    <!-- Divider 3 -->
    <div class="server-card-vsep"></div>

    <!-- DISK Section -->
    <div class="server-stat-cell server-stat-disk" title="Disk Usage / Quota">
      <div class="stat-cell-top">
        <div class="stat-cell-label-wrap">
          <svg class="stat-glyph glyph-disk" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m-16 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
          <span class="stat-cell-label">DISK</span>
        </div>
        <div class="stat-cell-value stat-disk-val"><?= $sDiskUsed ?></div>
      </div>
      <div class="stat-cell-bottom">
        <div class="stat-progress-track">
          <div class="stat-progress-bar stat-disk-bar" style="width: 5%;"></div>
        </div>
        <span class="stat-cell-limit stat-disk-limit"><?= $sDiskLimit ?></span>
      </div>
    </div>

    <!-- Divider 4 -->
    <div class="server-card-vsep"></div>

    <!-- UPTIME Section -->
    <div class="server-stat-cell server-stat-uptime" title="Uptime Duration">
      <div class="stat-cell-top">
        <div class="stat-cell-label-wrap">
          <svg class="stat-glyph glyph-uptime" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <span class="stat-cell-label">UPTIME</span>
        </div>
        <div class="stat-cell-value stat-uptime-val">0s</div>
      </div>
      <div class="stat-cell-bottom stat-uptime-sub">
        <span class="stat-uptime-dot <?= $isLive ? 'is-live' : '' ?>"></span>
        <span class="stat-uptime-status"><?= $isLive ? 'Active' : 'Offline' ?></span>
      </div>
    </div>
  </div>

  <!-- 3. Bottom Row: Action Buttons (Start, Restart, Stop, Kill) -->
  <div class="server-card-bottom">
    <div class="server-actions-cluster">
      <!-- Start (Primary Amber) -->
      <button type="button" class="btn-srv-action btn-srv-start" data-action="start" data-server-id="<?= $sId ?>" title="Start server <?= $sName ?>" aria-label="Start server <?= $sName ?>">
        <svg class="btn-glyph" width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
        <span>Start</span>
      </button>

      <!-- Restart (Secondary Neutral Dark) -->
      <button type="button" class="btn-srv-action btn-srv-restart" data-action="restart" data-server-id="<?= $sId ?>" title="Restart server <?= $sName ?>" aria-label="Restart server <?= $sName ?>">
        <svg class="btn-glyph" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        <span>Restart</span>
      </button>

      <!-- Stop (Danger Crimson) -->
      <button type="button" class="btn-srv-action btn-srv-stop" data-action="stop" data-server-id="<?= $sId ?>" title="Stop server <?= $sName ?>" aria-label="Stop server <?= $sName ?>">
        <svg class="btn-glyph" width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 5a1 1 0 011-1h10a1 1 0 011 1v10a1 1 0 01-1 1H5a1 1 0 01-1-1V5z" clip-rule="evenodd"></path></svg>
        <span>Stop</span>
      </button>

      <!-- Kill (Destructive Maroon / Translucent Red) -->
      <button type="button" class="btn-srv-action btn-srv-kill" data-action="kill" data-server-id="<?= $sId ?>" title="Force kill server <?= $sName ?>" aria-label="Force kill server <?= $sName ?>">
        <svg class="btn-glyph" width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 3.84 2.16 7.18 5.35 8.87.23.12.5.07.67-.12l.98-1.12c.16-.18.17-.45.02-.65-.48-.64-.81-1.38-.97-2.18-.04-.21.08-.42.28-.48.24-.07.49-.07.74-.01.21.05.35.24.33.46-.02.26.01.53.07.78.05.21.23.36.45.36h4.16c.22 0 .4-.15.45-.36.06-.25.09-.52.07-.78-.02-.22.12-.41.33-.46.25-.06.5-.06.74.01.2.06.32.27.28.48-.16.8-.49 1.54-.97 2.18-.15.2-.14.47.02.65l.98 1.12c.17.19.44.24.67.12C19.84 19.18 22 15.84 22 12c0-5.52-4.48-10-10-10zm-3 8c-.83 0-1.5-.67-1.5-1.5S8.17 7 9 7s1.5.67 1.5 1.5S9.83 10 9 10zm6 0c-.83 0-1.5-.67-1.5-1.5S14.17 7 15 7s1.5.67 1.5 1.5S15.83 10 15 10zm-3 7c-1.1 0-2-.9-2-2h4c0 1.1-.9 2-2 2z"/></svg>
        <span>Kill</span>
      </button>
    </div>
  </div>
</div>
