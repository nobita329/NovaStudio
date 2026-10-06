<?php
$title = 'Nodes Management';
$breadcrumb = 'Admin / Nodes';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Nodes</h1>
        <p class="page-description">Host systems that run the Nova Daemon and manage container instances.</p>
      </div>
      <a href="/admin/nodes/new" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Create Node
      </a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Location</th>
            <th>FQDN / Connection</th>
            <th>Memory</th>
            <th>Disk</th>
            <th>Servers</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($nodes as $n): ?>
            <tr>
              <td>
                <a href="/admin/nodes/<?= e($n['id']) ?>" style="font-weight: 600; color: #fff;">
                  <?= e($n['name']) ?>
                </a>
              </td>
              <td><?= e($n['location_code'] ?? 'LOC1') ?></td>
              <td style="font-family: var(--font-mono); color: var(--accent-blue);"><?= e($n['scheme']) ?>://<?= e($n['fqdn']) ?>:<?= e($n['daemon_port']) ?></td>
              <td><?= e($n['memory']) ?> MB</td>
              <td><?= e($n['disk']) ?> MB</td>
              <td style="font-weight: 600;"><?= e($n['server_count'] ?? 0) ?></td>
              <td>
                <span class="status-badge badge-online">
                  <span class="badge-dot"></span>Online
                </span>
              </td>
              <td style="text-align: right;">
                <div style="display: inline-flex; gap: 0.4rem;">
                  <a href="/admin/nodes/<?= e($n['id']) ?>/allocations" class="btn btn-secondary btn-sm" title="Allocations">
                    Allocations
                  </a>
                  <a href="/admin/nodes/<?= e($n['id']) ?>" class="btn btn-secondary btn-sm">
                    Details
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
