<?php
$title = 'Activity Log';
$breadcrumb = 'Admin / Activity';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">System Activity Log</h1>
        <p class="page-description">Complete audit trail of user and administrative actions across Nova.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Event</th>
            <th>User</th>
            <th>Action</th>
            <th>IP Address</th>
            <th>Server</th>
            <th>Date & Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 3rem;">No activity logged yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $log): ?>
              <tr>
                <td><span class="status-badge badge-online"><?= e($log['event']) ?></span></td>
                <td><strong style="color: #fff;"><?= e($log['username'] ?? 'System') ?></strong></td>
                <td><?= e($log['action']) ?></td>
                <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.8rem;"><?= e($log['ip_address']) ?></td>
                <td style="color: var(--accent-blue);"><?= e($log['server_name'] ?: '-') ?></td>
                <td style="color: var(--text-dim); font-size: 0.8rem;"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
