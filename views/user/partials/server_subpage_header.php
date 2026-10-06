<?php
$srvId = $server['id'] ?? '';
$srvName = $server['name'] ?? 'Server';
$srvStatus = $server['status'] ?? 'offline';

$allocAddress = '';
if (!empty($allocation['ip_address']) && !empty($allocation['port'])) {
  $allocAddress = $allocation['ip_address'] . ':' . $allocation['port'];
} elseif (!empty($server['ip_address']) && !empty($server['primary_port'])) {
  $allocAddress = $server['ip_address'] . ':' . $server['primary_port'];
} else {
  $allocAddress = '0.0.0.0:' . ($server['port'] ?? '25565');
}
?>
<!-- Unified Server Context Header -->
<div class="server-subpage-header">
  <div class="server-subpage-title-wrap">
    <div style="display: flex; align-items: center; gap: 0.65rem;">
      <a href="/servers" style="color: var(--text-muted); display: flex; align-items: center;" title="Back to All Servers">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
      </a>
      <span class="server-subpage-name"><?= e($srvName) ?></span>
    </div>
    
    <span class="server-status-pill">
      <?= statusBadge($srvStatus) ?>
    </span>

    <div class="server-subpage-alloc" onclick="window.arixCopy && window.arixCopy('<?= e($allocAddress) ?>')" title="Click to copy connection address">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
      <span><?= e($allocAddress) ?></span>
    </div>
  </div>

  <!-- Quick Server Power Controls -->
  <div class="arix-power-group">
    <button type="button" class="btn-power btn-power-start" data-subpage-power="start" data-server-id="<?= e($srvId) ?>" title="Start Server">
      <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
      Start
    </button>
    <button type="button" class="btn-power btn-power-restart" data-subpage-power="restart" data-server-id="<?= e($srvId) ?>" title="Restart Server">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
      Restart
    </button>
    <button type="button" class="btn-power btn-power-stop" data-subpage-power="stop" data-server-id="<?= e($srvId) ?>" title="Stop Server">
      <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd"></path></svg>
      Stop
    </button>
    <button type="button" class="btn-power btn-power-kill" data-subpage-power="kill" data-server-id="<?= e($srvId) ?>" title="Force Kill Instance">
      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
      Kill
    </button>
  </div>
</div>

<!-- Server Sub-Navigation Tabs Bar -->
<?php require __DIR__ . '/server_subnav.php'; ?>
