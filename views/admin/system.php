<?php
$title = 'System Information';
$breadcrumb = 'Admin / System';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">System Information</h1>
        <p class="page-description">Core diagnostics, versions, runtime environment, and container subsystem.</p>
      </div>
    </div>

    <div class="grid-cols-2">
      <!-- Panel Software -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Software Stack</h3>
        </div>
        <table class="table">
          <tr>
            <td style="color: var(--text-muted);">Nova Panel Version:</td>
            <td style="font-weight: 700; color: var(--accent-blue);">1.0.0 Pro</td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">Node.js Runtime:</td>
            <td style="font-family: var(--font-mono); color: #fff;"><?= e($nodeVersion) ?></td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">PHP View Engine:</td>
            <td style="font-family: var(--font-mono); color: #fff;">PHP <?= e(phpversion()) ?></td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">Database Backend:</td>
            <td style="font-family: var(--font-mono); color: #fff; text-transform: uppercase;"><?= e($dbType) ?></td>
          </tr>
        </table>
      </div>

      <!-- Host Hardware -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Host Server Environment</h3>
        </div>
        <table class="table">
          <tr>
            <td style="color: var(--text-muted);">Operating System:</td>
            <td style="color: #fff;"><?= e($system['os']['distro']) ?> <?= e($system['os']['release']) ?></td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">Hostname:</td>
            <td style="font-family: var(--font-mono);"><?= e($system['os']['hostname']) ?></td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">Docker Daemon:</td>
            <td style="font-family: var(--font-mono); color: var(--accent-success);"><?= e($system['docker']['version']) ?> (<?= e($system['docker']['running']) ?> / <?= e($system['docker']['containers']) ?> Running)</td>
          </tr>
          <tr>
            <td style="color: var(--text-muted);">System Uptime:</td>
            <td><?= round($system['os']['uptime'] / 3600, 1) ?> Hours</td>
          </tr>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
