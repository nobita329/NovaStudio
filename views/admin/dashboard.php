<?php
$title = 'Statistics';
$breadcrumb = 'Admin / Statistics';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$adminTheme = $adminTheme ?? 'default';
$totalServers = $totalServers ?? 0;
$activeServers = $activeServers ?? 0;
$suspendedServers = $suspendedServers ?? 0;
$installingServers = $installingServers ?? 0;
$totalUsers = $totalUsers ?? 0;
$adminUsers = $adminUsers ?? 0;
$totalNodes = $totalNodes ?? 0;
$wingsAgentConfigured = $wingsAgentConfigured ?? 0;
$totalAllocations = $totalAllocations ?? 0;
$usedAllocations = $usedAllocations ?? 0;
$totalDatabases = $totalDatabases ?? 0;
$totalEggs = $totalEggs ?? 0;
$serverTotalCpu = $serverTotalCpu ?? 0;
$serverTotalMemory = $serverTotalMemory ?? 0;
$nodeTotalMemory = $nodeTotalMemory ?? 0;
$serverTotalDisk = $serverTotalDisk ?? 0;
$nodeTotalDisk = $nodeTotalDisk ?? 0;
$recentServers = $recentServers ?? [];

$memPct  = $nodeTotalMemory > 0 ? round($serverTotalMemory / $nodeTotalMemory * 100) : 0;
$diskPct = $nodeTotalDisk   > 0 ? round($serverTotalDisk   / $nodeTotalDisk   * 100) : 0;

$fmtMib = function(int $mib): string {
    if ($mib >= 1024 * 1024) return round($mib / 1024 / 1024, 1) . ' TiB';
    if ($mib >= 1024)        return round($mib / 1024, 1) . ' GiB';
    return $mib . ' MiB';
};
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
.admin-content-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.admin-header-title h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  display: flex;
  align-items: baseline;
  gap: 0.6rem;
}

.admin-header-title small {
  font-size: 0.85rem;
  font-weight: 400;
  color: #94a3b8;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  list-style: none;
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
  padding: 0;
}

.breadcrumb a {
  color: #38bdf8;
  text-decoration: none;
}

.breadcrumb .active {
  color: #94a3b8;
}

/* Grid & Info Box Base Layout */
.row {
  display: flex;
  flex-wrap: wrap;
  margin-left: -10px;
  margin-right: -10px;
}

.col-lg-3, .col-xs-6, .col-xs-12, .col-md-4, .col-lg-2 {
  position: relative;
  min-height: 1px;
  padding-left: 10px;
  padding-right: 10px;
  box-sizing: border-box;
}

.col-xs-12 { width: 100%; flex: 0 0 100%; }
.col-xs-6 { width: 50%; flex: 0 0 50%; }

@media (min-width: 768px) {
  .col-md-4 { width: 33.33333333%; flex: 0 0 33.33333333%; }
}

@media (min-width: 992px) {
  .col-lg-3 { width: 25%; flex: 0 0 25%; }
  .col-lg-2 { width: 16.66666667%; flex: 0 0 16.66666667%; }
}

@media (max-width: 640px) {
  .col-xs-6 { width: 100%; flex: 0 0 100%; }
}

.info-box {
  display: flex;
  min-height: 90px;
  background: #ffffff;
  width: 100%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
  border-radius: 6px;
  margin-bottom: 20px;
  border: 1px solid transparent;
  overflow: hidden;
  position: relative;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.info-box:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.info-box-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  min-width: 80px;
  font-size: 32px;
  line-height: 90px;
  text-align: center;
  background: rgba(0, 0, 0, 0.15);
}

.info-box-content {
  padding: 8px 14px;
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.info-box-text {
  display: block;
  font-size: 0.775rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 2px;
}

.info-box-number {
  display: block;
  font-weight: 700;
  font-size: 1.45rem;
  line-height: 1.2;
}

.info-box .progress {
  background: rgba(0, 0, 0, 0.08);
  margin: 5px 0;
  height: 3px;
  border-radius: 2px;
  overflow: hidden;
}

.info-box .progress .progress-bar {
  height: 100%;
}

.info-box .progress-description {
  display: block;
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  opacity: 0.85;
}

/* Box Panels */
.box {
  background: #111520;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-top: 3px solid #3b82f6;
  border-radius: 6px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
  margin-bottom: 20px;
  position: relative;
}

.box.box-primary { border-top-color: #3b82f6; }
.box.box-success { border-top-color: #10b981; }
.box.box-default { border-top-color: #64748b; }

.box-header {
  color: #ffffff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.box-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 8px;
}

.box-title small {
  font-size: 0.8rem;
  font-weight: 400;
  color: #94a3b8;
  margin-left: 6px;
}

.box-tools {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-box-tool {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 12px;
  border-radius: 4px;
  transition: all 0.15s ease;
}

.btn-box-tool:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.08);
}

.box-body {
  padding: 18px;
  color: #cbd5e1;
}

.box-body.no-padding {
  padding: 0;
}

/* Description Blocks for Live Stats */
.description-block {
  text-align: center;
  padding: 10px 5px;
}

.description-block.border-right {
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}

@media (max-width: 991px) {
  .description-block.border-right {
    border-right: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    margin-bottom: 8px;
    padding-bottom: 8px;
  }
}

.description-percentage {
  display: block;
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 2px;
  font-family: var(--font-mono, monospace);
}

.description-header {
  margin: 4px 0 2px 0;
  font-size: 0.85rem;
  font-weight: 600;
  color: #e2e8f0;
}

.description-text {
  font-size: 0.7rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

/* Colors and Progress */
.text-green { color: #10b981 !important; }
.text-aqua { color: #06b6d4 !important; }
.text-yellow { color: #f59e0b !important; }
.text-red { color: #ef4444 !important; }
.text-purple { color: #a855f7 !important; }
.text-muted { color: #94a3b8 !important; }

.badge {
  display: inline-block;
  padding: 4px 8px;
  font-size: 0.75rem;
  font-weight: 600;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 4px;
}
.badge.bg-green { background-color: #10b981 !important; color: #fff; }
.badge.bg-yellow { background-color: #f59e0b !important; color: #fff; }
.badge.bg-red { background-color: #ef4444 !important; color: #fff; }

.label {
  display: inline-block;
  padding: 0.25em 0.6em;
  font-size: 75%;
  font-weight: 700;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: baseline;
  border-radius: 4px;
}
.label-success { background-color: #10b981; color: #fff; }
.label-danger { background-color: #ef4444; color: #fff; }

.progress {
  background: rgba(255, 255, 255, 0.08);
  height: 8px;
  border-radius: 4px;
  overflow: hidden;
}

.progress-sm {
  height: 10px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 5px;
  overflow: hidden;
}

.progress-xs {
  height: 5px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar-info { background-color: #0ea5e9; }
.progress-bar-success { background-color: #10b981; }
.progress-bar-warning { background-color: #f59e0b; }
.progress-bar-danger { background-color: #ef4444; }

/* Callout */
.callout {
  border-radius: 6px;
  margin: 0 0 20px 0;
  padding: 15px 20px;
  border-left: 4px solid #eee;
}
.callout.callout-warning {
  background-color: rgba(245, 158, 11, 0.1);
  border-left-color: #f59e0b;
  color: #fbbf24;
}
.callout.callout-warning h4 {
  color: #fbbf24;
  margin-top: 0;
  margin-bottom: 6px;
  font-size: 0.95rem;
  font-weight: 600;
}
.callout p {
  margin: 0;
  font-size: 0.825rem;
  color: #d1d5db;
}

/* Tables */
.table {
  width: 100%;
  max-width: 100%;
  margin-bottom: 0;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.table th, .table td {
  padding: 10px 14px;
  line-height: 1.4;
  vertical-align: middle;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.table thead th {
  background: #0d121c;
  color: #94a3b8;
  font-weight: 600;
  font-size: 0.8rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}
.table-condensed th, .table-condensed td {
  padding: 8px 12px;
}
.table-hover tbody tr:hover {
  background-color: rgba(255, 255, 255, 0.025);
}
.table-responsive {
  min-height: .01%;
  overflow-x: auto;
}
code {
  padding: 2px 5px;
  font-size: 85%;
  color: #38bdf8;
  background-color: rgba(56, 189, 248, 0.1);
  border-radius: 4px;
  font-family: var(--font-mono, monospace);
}
</style>

<?php if ($adminTheme === 'default'): ?>
<style>
    .statistics-info-box.is-total-servers {
        background-color: #eff7ff;
        border-color: #bfdcff;
        color: #0d3b66;
    }

    .statistics-info-box.is-total-servers .info-box-icon {
        background-color: #1e88e5 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-total-users {
        background-color: #edf7f0;
        border-color: #bfe7ca;
        color: #1b4332;
    }

    .statistics-info-box.is-total-users .info-box-icon {
        background-color: #2e7d32 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-total-nodes {
        background-color: #fff8e8;
        border-color: #ffe1a8;
        color: #7c4a03;
    }

    .statistics-info-box.is-total-nodes .info-box-icon {
        background-color: #fb8c00 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-allocations {
        background-color: #f3edff;
        border-color: #d8c8ff;
        color: #4a2c89;
    }

    .statistics-info-box.is-allocations .info-box-icon {
        background-color: #7e57c2 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-databases {
        background-color: #fff1f2;
        border-color: #ffc9cf;
        color: #8b1e2d;
    }

    .statistics-info-box.is-databases .info-box-icon {
        background-color: #d81b60 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-eggs {
        background-color: #eaf8fb;
        border-color: #bfe7ef;
        color: #0a4f5e;
    }

    .statistics-info-box.is-eggs .info-box-icon {
        background-color: #00acc1 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-suspended {
        background-color: #fff0f0;
        border-color: #ffc5c5;
        color: #8a1c1c;
    }

    .statistics-info-box.is-suspended .info-box-icon {
        background-color: #e53935 !important;
        color: #ffffff !important;
    }

    .statistics-info-box.is-installing {
        background-color: #f5f6f8;
        border-color: #d8dde6;
        color: #2f3e4d;
    }

    .statistics-info-box.is-installing .info-box-icon {
        background-color: #546e7a !important;
        color: #ffffff !important;
    }

    .statistics-info-box .info-box-number,
    .statistics-info-box .info-box-text,
    .statistics-info-box .progress-description {
        color: inherit;
    }

    .statistics-info-box .progress {
        background: rgba(0, 0, 0, 0.08);
    }

    .statistics-info-box .progress .progress-bar {
        background: currentColor;
        opacity: 0.45;
    }
</style>
<?php endif; ?>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>Statistics <small>Overview of your panel's servers, users, and live node usage.</small></h1>
      </div>
      <ol class="breadcrumb">
        <li><a href="<?= route('admin.index') ?>">Admin</a></li>
        <li>/</li>
        <li class="active">Statistics</li>
      </ol>
    </div>

    <!-- 01. STATISTICS INFO BOXES -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-total-servers">
                <span class="info-box-icon"><i class="fa fa-server"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Servers</span>
                    <span class="info-box-number"><?= e($totalServers) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description">
                        <?= e($activeServers) ?> active &bull; <?= e($suspendedServers) ?> suspended &bull; <?= e($installingServers) ?> installing
                    </span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-total-users">
                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Users</span>
                    <span class="info-box-number"><?= e($totalUsers) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description"><?= e($adminUsers) ?> admin<?= $adminUsers === 1 ? '' : 's' ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-total-nodes">
                <span class="info-box-icon"><i class="fa fa-sitemap"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Nodes</span>
                    <span class="info-box-number"><?= e($totalNodes) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description"><?= e($wingsAgentConfigured) ?> with Wings Agent</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-allocations">
                <span class="info-box-icon"><i class="fa fa-link"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Allocations Used</span>
                    <span class="info-box-number"><?= e($usedAllocations) ?> / <?= e($totalAllocations) ?></span>
                    <div class="progress">
                        <div class="progress-bar" style="width:<?= $totalAllocations > 0 ? round($usedAllocations / $totalAllocations * 100) : 0 ?>%"></div>
                    </div>
                    <span class="progress-description"><?= $totalAllocations > 0 ? round($usedAllocations / $totalAllocations * 100) : 0 ?>% in use</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-databases">
                <span class="info-box-icon"><i class="fa fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Databases</span>
                    <span class="info-box-number"><?= e($totalDatabases) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description">server databases created</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-eggs">
                <span class="info-box-icon"><i class="fa fa-th-large"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Eggs Available</span>
                    <span class="info-box-number"><?= e($totalEggs) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description">server configuration eggs</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-suspended">
                <span class="info-box-icon"><i class="fa fa-lock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Suspended Servers</span>
                    <span class="info-box-number"><?= e($suspendedServers) ?></span>
                    <div class="progress">
                        <div class="progress-bar" style="width:<?= $totalServers > 0 ? round($suspendedServers / $totalServers * 100) : 0 ?>%"></div>
                    </div>
                    <span class="progress-description"><?= $totalServers > 0 ? round($suspendedServers / $totalServers * 100) : 0 ?>% of all servers</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-xs-6">
            <div class="info-box statistics-info-box is-installing">
                <span class="info-box-icon"><i class="fa fa-download"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Installing</span>
                    <span class="info-box-number"><?= e($installingServers) ?></span>
                    <div class="progress"><div class="progress-bar" style="width:100%"></div></div>
                    <span class="progress-description">servers currently installing</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 02. RESOURCE ALLOCATION OVERVIEW -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-bar-chart fa-fw"></i> Resource Allocation Overview</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong><i class="fa fa-microchip fa-fw text-yellow"></i> CPU Allocated to Servers</strong></p>
                            <div class="progress progress-sm">
                                <div class="progress-bar progress-bar-info" style="min-width:2em; width:100%"></div>
                            </div>
                            <p class="text-muted small" style="margin-top: 5px;">
                                <strong><?= number_format($serverTotalCpu) ?>%</strong> total CPU allocated across all servers
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong><i class="fa fa-server fa-fw text-aqua"></i> Memory Allocation</strong></p>
                            <div class="progress progress-sm" style="margin-bottom:4px;">
                                <div class="progress-bar progress-bar-<?= $memPct >= 90 ? 'danger' : ($memPct >= 70 ? 'warning' : 'success') ?>"
                                     style="width:<?= min($memPct, 100) ?>%"></div>
                            </div>
                            <p class="text-muted small" style="margin-top: 5px;">
                                <strong><?= $fmtMib($serverTotalMemory) ?></strong> / <strong><?= $fmtMib($nodeTotalMemory) ?></strong>
                                &mdash; <strong><?= $memPct ?>%</strong> allocated
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong><i class="fa fa-hdd-o fa-fw text-green"></i> Disk Allocation</strong></p>
                            <div class="progress progress-sm" style="margin-bottom:4px;">
                                <div class="progress-bar progress-bar-<?= $diskPct >= 90 ? 'danger' : ($diskPct >= 70 ? 'warning' : 'success') ?>"
                                     style="width:<?= min($diskPct, 100) ?>%"></div>
                            </div>
                            <p class="text-muted small" style="margin-top: 5px;">
                                <strong><?= $fmtMib($serverTotalDisk) ?></strong> / <strong><?= $fmtMib($nodeTotalDisk) ?></strong>
                                &mdash; <strong><?= $diskPct ?>%</strong> allocated
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 03. LIVE NODE USAGE -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success" id="live-stats-box">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        <i class="fa fa-circle fa-fw" id="live-indicator" style="color:#f39c12; font-size:10px; vertical-align:middle;"></i>
                        Live Node Usage
                        <small id="live-nodes-summary" style="margin-left:8px;"></small>
                    </h3>
                    <div class="box-tools pull-right">
                        <span class="badge bg-green" id="live-status-badge">Connecting…</span>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body" id="live-stats-body">
                    <div id="live-loading" class="text-center" style="padding:20px; text-align:center;">
                        <i class="fa fa-refresh fa-spin fa-2x"></i>
                        <p style="margin-top:10px; color:#888;">Fetching live data from Wings Agents…</p>
                    </div>

                    <div id="live-stats-content" style="display:none;">
                        <div class="row" id="live-totals-row">
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-green" id="lt-cpu">–</span>
                                    <h5 class="description-header">Total CPU</h5>
                                    <span class="description-text">across all nodes</span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-aqua" id="lt-mem">–</span>
                                    <h5 class="description-header">Memory Used</h5>
                                    <span class="description-text" id="lt-mem-total">–</span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-yellow" id="lt-disk">–</span>
                                    <h5 class="description-header">Disk Used</h5>
                                    <span class="description-text" id="lt-disk-total">–</span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-green" id="lt-netin">–</span>
                                    <h5 class="description-header">Network In</h5>
                                    <span class="description-text">bytes/s</span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block border-right">
                                    <span class="description-percentage text-red" id="lt-netout">–</span>
                                    <h5 class="description-header">Network Out</h5>
                                    <span class="description-text">bytes/s</span>
                                </div>
                            </div>
                            <div class="col-lg-2 col-xs-6">
                                <div class="description-block">
                                    <span class="description-percentage text-purple" id="lt-conns">–</span>
                                    <h5 class="description-header">Connections</h5>
                                    <span class="description-text">total open</span>
                                </div>
                            </div>
                        </div>

                        <hr style="margin:12px 0; border:0; border-top:1px solid rgba(255,255,255,0.06);">

                        <div class="table-responsive" style="margin-top:10px;">
                            <table class="table table-condensed table-hover" style="margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th>Node</th>
                                        <th>Status</th>
                                        <th>CPU</th>
                                        <th>Memory</th>
                                        <th>Disk</th>
                                        <th>Net In</th>
                                        <th>Net Out</th>
                                        <th>Conns</th>
                                        <th>Agent Version</th>
                                    </tr>
                                </thead>
                                <tbody id="live-nodes-table-body">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="live-unavailable" style="display:none;">
                        <div class="callout callout-warning">
                            <h4><i class="fa fa-warning"></i> Wings Agent Not Configured</h4>
                            <p>No Wings Agent endpoints are configured. Configure node endpoints in the Wings Agent Addon settings to see live node statistics here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 04. RECENTLY CREATED SERVERS -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-clock-o fa-fw"></i> Recently Created Servers <small>(last 7 days)</small></h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <?php if (empty($recentServers) || (is_object($recentServers) && method_exists($recentServers, 'isEmpty') && $recentServers->isEmpty())): ?>
                        <div class="box-body">
                            <p class="text-muted text-center" style="margin:20px 0; text-align:center;">No servers created in the last 7 days.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-condensed">
                                <thead>
                                    <tr>
                                        <th>Server Name</th>
                                        <th>UUID</th>
                                        <th>Owner</th>
                                        <th>Node</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentServers as $server): ?>
                                    <?php
                                        $sObj = is_array($server) ? (object)$server : $server;
                                        $sUser = !empty($sObj->user) ? (is_array($sObj->user) ? (object)$sObj->user : $sObj->user) : null;
                                        $sNode = !empty($sObj->node) ? (is_array($sObj->node) ? (object)$sObj->node : $sObj->node) : null;
                                        $createdStr = is_object($sObj->created_at) ? (string)$sObj->created_at : ($sObj->created_at ?? '');
                                        $timeDiff = '';
                                        if (!empty($createdStr)) {
                                            $ts = strtotime($createdStr);
                                            if ($ts) {
                                                $d = time() - $ts;
                                                if ($d < 60) $timeDiff = 'just now';
                                                elseif ($d < 3600) $timeDiff = floor($d / 60) . ' mins ago';
                                                elseif ($d < 86400) $timeDiff = floor($d / 3600) . ' hours ago';
                                                elseif ($d < 604800) $timeDiff = floor($d / 86400) . ' days ago';
                                                else $timeDiff = date('M d, Y', $ts);
                                            }
                                        }
                                        if (empty($timeDiff)) $timeDiff = $createdStr ?: '—';
                                    ?>
                                    <tr>
                                        <td><strong><?= e($sObj->name) ?></strong></td>
                                        <td><code><?= e(substr($sObj->uuid ?? '', 0, 8)) ?></code></td>
                                        <td>
                                            <?php if ($sUser): ?>
                                                <a href="<?= route('admin.users.view', $sUser->id) ?>" style="color:#38bdf8; text-decoration:none;">
                                                    <?= e($sUser->name_first ?? $sUser->first_name ?? '') ?> <?= e($sUser->name_last ?? $sUser->last_name ?? '') ?>
                                                    <small class="text-muted">(<?= e($sUser->username ?? '') ?>)</small>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($sNode->name ?? '—') ?></td>
                                        <td title="<?= e($createdStr) ?>">
                                            <?= e($timeDiff) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= route('admin.servers.view', $sObj->id) ?>" class="btn btn-xs btn-default" style="cursor:pointer; background:#1e293b; color:#e2e8f0; border:1px solid rgba(255,255,255,0.1); padding:3px 10px; border-radius:4px; font-size:11px; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
  </main>
</div>

<script>
(function () {
    var agentEndpointsUrl = '<?= route('admin.statistics.agent-endpoints') ?>';
    var liveStatsUrl = '<?= route('admin.statistics.live') ?>';
    var sources = {};       // node_id → EventSource
    var nodeStats = {};     // node_id → { online, cpu, memory_used, ... }
    var nodeNames = {};     // node_id → name
    var ticketExpiry = 0;
    var refreshTimer = null;

    // Box collapse toggle
    $(document).on('click', '[data-widget="collapse"]', function(e) {
        e.preventDefault();
        var box = $(this).closest('.box');
        var body = box.find('.box-body');
        body.slideToggle(200);
        var icon = $(this).find('i');
        icon.toggleClass('fa-minus fa-plus');
    });

    function fmtBytes(b) {
        if (b === null || b === undefined) return '—';
        if (b >= 1073741824) return (b / 1073741824).toFixed(1) + ' GB';
        if (b >= 1048576)    return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024)       return (b / 1024).toFixed(1) + ' KB';
        return b + ' B';
    }

    function fmtBytesPerSec(b) {
        return fmtBytes(b) + '/s';
    }

    function fmtCpu(v) {
        return parseFloat(v).toFixed(1) + '%';
    }

    function statusBadge(online) {
        if (online) {
            return '<span class="label label-success"><i class="fa fa-check-circle"></i> Online</span>';
        }
        return '<span class="label label-danger"><i class="fa fa-times-circle"></i> Offline</span>';
    }

    function parseAgentStats(raw) {
        var cpu = (raw.cpu && raw.cpu.usage_percent) || 0;
        var memUsed = ((raw.memory && raw.memory.used_mb) || 0) * 1048576;
        var memTotal = ((raw.memory && raw.memory.total_mb) || 0) * 1048576;
        var diskUsed = 0, diskTotal = 0;
        (raw.disks || []).forEach(function(d) {
            diskUsed  += (d.used_gb  || 0) * 1073741824;
            diskTotal += (d.total_gb || 0) * 1073741824;
        });
        var netIn = 0, netOut = 0;
        (raw.network || []).forEach(function(n) {
            netIn  += (n.speed_recv_kb || 0) * 1024;
            netOut += (n.speed_send_kb || 0) * 1024;
        });
        var conns = ((raw.connections && raw.connections.tcp_total) || 0) +
                    ((raw.connections && raw.connections.udp_total) || 0);
        return {
            online: true,
            cpu: cpu,
            memory_used: Math.round(memUsed),
            memory_total: Math.round(memTotal),
            disk_used: Math.round(diskUsed),
            disk_total: Math.round(diskTotal),
            net_in: Math.round(netIn),
            net_out: Math.round(netOut),
            connections: conns,
            version: raw.version || null,
        };
    }

    function renderStats() {
        $('#live-loading').hide();
        var nodeIds = Object.keys(nodeNames);
        if (nodeIds.length === 0) {
            $('#live-stats-content').hide();
            $('#live-unavailable').show();
            $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Not Configured');
            $('#live-indicator').css('color', '#f39c12');
            return;
        }

        $('#live-unavailable').hide();
        $('#live-stats-content').show();

        var totals = { cpu: 0, memory_used: 0, memory_total: 0, disk_used: 0, disk_total: 0, net_in: 0, net_out: 0, connections: 0 };
        var nodesOn = 0;

        nodeIds.forEach(function(id) {
            var n = nodeStats[id];
            if (n && n.online) {
                nodesOn++;
                totals.cpu          += n.cpu;
                totals.memory_used  += n.memory_used;
                totals.memory_total += n.memory_total;
                totals.disk_used    += n.disk_used;
                totals.disk_total   += n.disk_total;
                totals.net_in       += n.net_in;
                totals.net_out      += n.net_out;
                totals.connections  += n.connections;
            }
        });

        var nodesTotal = nodeIds.length;
        $('#lt-cpu').text(fmtCpu(totals.cpu));
        $('#lt-mem').text(fmtBytes(totals.memory_used));
        $('#lt-mem-total').text('of ' + fmtBytes(totals.memory_total));
        $('#lt-disk').text(fmtBytes(totals.disk_used));
        $('#lt-disk-total').text('of ' + fmtBytes(totals.disk_total));
        $('#lt-netin').text(fmtBytesPerSec(totals.net_in));
        $('#lt-netout').text(fmtBytesPerSec(totals.net_out));
        $('#lt-conns').text(totals.connections.toLocaleString());

        $('#live-nodes-summary').text(nodesOn + ' / ' + nodesTotal + ' nodes online');
        if (nodesOn === nodesTotal && nodesTotal > 0) {
            $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-green').text('All Online');
            $('#live-indicator').css('color', '#00a65a');
        } else if (nodesOn === 0) {
            $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-red').text('All Offline');
            $('#live-indicator').css('color', '#dd4b39');
        } else {
            $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Partial');
            $('#live-indicator').css('color', '#f39c12');
        }

        var tbody = $('#live-nodes-table-body');
        tbody.empty();
        if (nodeIds.length === 0) {
            tbody.append('<tr><td colspan="9" class="text-center text-muted" style="text-align:center;">No nodes data available.</td></tr>');
            return;
        }

        nodeIds.forEach(function(id) {
            var n = nodeStats[id] || { online: false };
            var name = nodeNames[id] || ('Node #' + id);
            var memTotal = n.memory_total || 0;
            var diskTotal = n.disk_total || 0;
            var memPct = memTotal > 0 ? Math.round((n.memory_used || 0) / memTotal * 100) : 0;
            var diskPct = diskTotal > 0 ? Math.round((n.disk_used || 0) / diskTotal * 100) : 0;
            var memBar = memPct >= 90 ? 'danger' : (memPct >= 70 ? 'warning' : 'success');
            var diskBar = diskPct >= 90 ? 'danger' : (diskPct >= 70 ? 'warning' : 'success');

            var row = '<tr>';
            row += '<td><strong>' + $('<span>').text(name).html() + '</strong></td>';
            row += '<td>' + statusBadge(n.online) + '</td>';

            if (n.online) {
                row += '<td>' + fmtCpu(n.cpu || 0) + '</td>';
                row += '<td>';
                row += '<div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + memBar + '" style="width:' + memPct + '%"></div></div>';
                row += fmtBytes(n.memory_used || 0) + ' / ' + fmtBytes(memTotal);
                row += '</td>';
                row += '<td>';
                row += '<div class="progress progress-xs" style="margin-bottom:2px;"><div class="progress-bar progress-bar-' + diskBar + '" style="width:' + diskPct + '%"></div></div>';
                row += fmtBytes(n.disk_used || 0) + ' / ' + fmtBytes(diskTotal);
                row += '</td>';
                row += '<td>' + fmtBytesPerSec(n.net_in || 0) + '</td>';
                row += '<td>' + fmtBytesPerSec(n.net_out || 0) + '</td>';
                row += '<td>' + (n.connections || 0) + '</td>';
                row += '<td>' + (n.version ? '<code>' + $('<span>').text(n.version).html() + '</code>' : '<span class="text-muted">—</span>') + '</td>';
            } else {
                row += '<td colspan="7" class="text-muted">Agent unreachable</td>';
            }

            row += '</tr>';
            tbody.append(row);
        });
    }

    function closeAllSources() {
        Object.keys(sources).forEach(function(id) {
            if (sources[id]) {
                sources[id].onmessage = null;
                sources[id].onerror = null;
                sources[id].close();
                sources[id] = null;
            }
        });
        sources = {};
    }

    function connectSSE(endpoints) {
        closeAllSources();
        endpoints.forEach(function(ep) {
            var id = ep.node_id;
            nodeNames[id] = ep.node_name;
            if (!nodeStats[id]) {
                nodeStats[id] = { online: false };
            }

            var sseUrl = ep.base_url + '/stats/stream?interval=5&ticket=' + encodeURIComponent(ep.ticket);
            try {
                var es = new EventSource(sseUrl);
                sources[id] = es;

                es.onmessage = function(event) {
                    try {
                        var raw = JSON.parse(event.data);
                        nodeStats[id] = parseAgentStats(raw);
                        renderStats();
                    } catch(e) { /* ignore parse errors */ }
                };

                es.onerror = function() {
                    nodeStats[id] = { online: false };
                    renderStats();
                    // EventSource auto-reconnects, but mark offline until next message
                };
            } catch(e) {
                nodeStats[id] = { online: false };
            }
        });
        // Initial render
        renderStats();
    }

    function startSSE() {
        $.ajax({
            url: agentEndpointsUrl,
            method: 'GET',
            timeout: 8000,
            success: function(data) {
                var endpoints = data.endpoints || [];
                if (endpoints.length === 0) {
                    // No SSE endpoints — fall back to panel polling
                    fallbackToPolling();
                    return;
                }
                ticketExpiry = Date.now() + 90 * 1000; // tickets valid ~120s, refresh at 90s
                connectSSE(endpoints);
                // Refresh tickets before they expire
                if (refreshTimer) clearInterval(refreshTimer);
                refreshTimer = setInterval(function() {
                    if (document.hidden) return;
                    startSSE(); // re-fetch tickets and reconnect
                }, 90000);
            },
            error: function() {
                fallbackToPolling();
            }
        });
    }

    // Fallback: poll the panel's /statistics/live
    var pollTimer = null;
    function fallbackToPolling() {
        closeAllSources();
        if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }

        function fetchLiveStats() {
            $.ajax({
                url: liveStatsUrl,
                method: 'GET',
                timeout: 8000,
                success: function(data) {
                    $('#live-loading').hide();
                    if (!data.available) {
                        $('#live-stats-content').hide();
                        $('#live-unavailable').show();
                        $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-yellow').text('Not Configured');
                        $('#live-indicator').css('color', '#f39c12');
                        return;
                    }
                    // Convert panel format to our internal format and render
                    nodeStats = {};
                    nodeNames = {};
                    (data.nodes || []).forEach(function(n) {
                        nodeNames[n.node_id] = n.node_name;
                        nodeStats[n.node_id] = {
                            online: n.online,
                            cpu: n.cpu || 0,
                            memory_used: n.memory_used || 0,
                            memory_total: n.memory_total || 0,
                            disk_used: n.disk_used || 0,
                            disk_total: n.disk_total || 0,
                            net_in: n.net_in || 0,
                            net_out: n.net_out || 0,
                            connections: n.connections || 0,
                            version: n.version || null,
                        };
                    });
                    renderStats();
                },
                error: function() {
                    $('#live-status-badge').removeClass('bg-green bg-yellow bg-red').addClass('bg-red').text('Error');
                    $('#live-indicator').css('color', '#dd4b39');
                }
            });
        }

        fetchLiveStats();
        pollTimer = setInterval(fetchLiveStats, 6000);
    }

    // Start with SSE (preferred), falls back to polling if agent endpoints unavailable
    startSSE();

    function stopLiveConnections() {
        closeAllSources();
        if (refreshTimer) { clearInterval(refreshTimer); refreshTimer = null; }
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopLiveConnections();
        } else {
            startSSE();
        }
    });

    window.addEventListener('beforeunload', stopLiveConnections);
    window.addEventListener('pagehide', stopLiveConnections);
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) startSSE();
    });
})();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
