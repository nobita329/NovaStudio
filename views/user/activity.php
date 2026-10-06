<?php
$title = 'Activity — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Activity';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Activity Logs</h1>
        <p class="page-description">Security audits, lifecycle changes, and management actions recorded on this server.</p>
      </div>
    </div>

    <?php if (empty($logs)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="nova-empty-title">No Activity Recorded</div>
        <div class="nova-empty-desc">There are no operational events logged for this server instance yet.</div>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Action</th>
              <th>IP Address</th>
              <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): 
              $ev = strtolower($log['event'] ?? '');
              $badgeClass = 'badge-suspended';
              if (strpos($ev, 'start') !== false || strpos($ev, 'online') !== false) {
                $badgeClass = 'badge-online';
              } elseif (strpos($ev, 'stop') !== false || strpos($ev, 'kill') !== false) {
                $badgeClass = 'badge-offline';
              } elseif (strpos($ev, 'backup') !== false || strpos($ev, 'file') !== false || strpos($ev, 'database') !== false) {
                $badgeClass = 'badge-starting';
              }
            ?>
              <tr>
                <td>
                  <span class="status-badge <?= $badgeClass ?>">
                    <span class="badge-dot"></span>
                    <?= e($log['event']) ?>
                  </span>
                </td>
                <td style="font-weight: 500; color: #ffffff;"><?= e($log['action']) ?></td>
                <td style="font-family: var(--font-mono); color: var(--accent-blue); font-size: 0.825rem;"><?= e($log['ip_address'] ?: 'Internal System') ?></td>
                <td style="color: var(--text-dim); font-size: 0.825rem;"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
