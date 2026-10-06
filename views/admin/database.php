<?php
$title = 'Database Hosts';
$breadcrumb = 'Admin / Databases';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Database Hosts</h1>
        <p class="page-description">MariaDB and MySQL database server daemons available for client server databases.</p>
      </div>
      <a href="/admin/database/new" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Database Host
      </a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Host Address</th>
            <th>Port</th>
            <th>Username</th>
            <th>Databases</th>
            <th>Node</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hosts as $h): ?>
            <tr>
              <td><strong style="color: #fff;"><?= e($h['name']) ?></strong></td>
              <td style="font-family: var(--font-mono); color: var(--accent-blue);"><?= e($h['host']) ?></td>
              <td style="font-family: var(--font-mono);"><?= e($h['port']) ?></td>
              <td style="font-family: var(--font-mono); color: var(--text-muted);"><?= e($h['username']) ?></td>
              <td><?= e($h['database_count'] ?? 0) ?> / <?= e($h['max_databases'] ?? 100) ?></td>
              <td><?= e($h['node_name'] ?? 'Local') ?></td>
              <td style="text-align: right;">
                <form action="/admin/database/<?= e($h['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete database host?')" style="display:inline;">
                  <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
