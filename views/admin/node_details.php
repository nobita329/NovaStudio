<?php
$title = 'Node Details — ' . $node['name'];
$breadcrumb = 'Admin / Nodes / ' . $node['name'];
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title"><?= e($node['name']) ?></h1>
        <p class="page-description">Node ID: <?= e($node['id']) ?> | Location: LOC<?= e($node['location_id']) ?></p>
      </div>

      <div style="display: flex; gap: 0.5rem;">
        <a href="/admin/nodes/<?= e($node['id']) ?>/allocations" class="btn btn-primary btn-sm">
          Manage Allocations
        </a>
      </div>
    </div>

    <div class="grid-cols-3" style="margin-bottom: 2rem;">
      <div class="card">
        <div class="card-header"><h3 class="card-title">Memory Allocation</h3></div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #fff;"><?= e($node['memory']) ?> MB</div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Max host capacity</div>
      </div>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Disk Storage</h3></div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #fff;"><?= round($node['disk'] / 1024, 1) ?> GB</div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Total filesystem limit</div>
      </div>

      <div class="card">
        <div class="card-header"><h3 class="card-title">Running Servers</h3></div>
        <div style="font-size: 1.5rem; font-weight: 700; color: #fff;"><?= e($serverCount) ?></div>
        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Assigned instances</div>
      </div>
    </div>

    <!-- Daemon Configuration Details -->
    <div class="card" style="max-width: 700px;">
      <div class="card-header">
        <h3 class="card-title">Connection Parameters</h3>
      </div>
      <table class="table" style="margin-top: 0.5rem;">
        <tr>
          <td style="color: var(--text-muted);">FQDN / Address:</td>
          <td style="font-family: var(--font-mono); color: #fff;"><?= e($node['fqdn']) ?></td>
        </tr>
        <tr>
          <td style="color: var(--text-muted);">Daemon Endpoint:</td>
          <td style="font-family: var(--font-mono); color: var(--accent-blue);"><?= e($node['scheme']) ?>://<?= e($node['fqdn']) ?>:<?= e($node['daemon_port']) ?></td>
        </tr>
        <tr>
          <td style="color: var(--text-muted);">SFTP Endpoint:</td>
          <td style="font-family: var(--font-mono); color: var(--text-main);"><?= e($node['fqdn']) ?>:<?= e($node['sftp_port']) ?></td>
        </tr>
        <tr>
          <td style="color: var(--text-muted);">Status:</td>
          <td><span class="status-badge badge-online"><span class="badge-dot"></span>Online</span></td>
        </tr>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
