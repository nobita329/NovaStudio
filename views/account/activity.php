<?php
$title = 'Account Activity';
$breadcrumb = 'Account / Activity';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Account Subnavigation Tabs -->
    <?php require __DIR__ . '/partials/account_tabs.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Login & Activity Logs</h1>
        <p class="page-description">Audit history of recent authentication events, security changes, and credential usage on your user account.</p>
      </div>
    </div>

    <?php if (empty($logs)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        </div>
        <div class="nova-empty-title">No Account Activity</div>
        <div class="nova-empty-desc">No authentication or account security modifications have been registered yet.</div>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Event</th>
              <th>Action Details</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log): 
              $ev = strtolower($log['event'] ?? '');
              $badgeClass = 'badge-suspended';
              if (strpos($ev, 'auth') !== false || strpos($ev, 'login') !== false) {
                $badgeClass = 'badge-online';
              } elseif (strpos($ev, 'fail') !== false || strpos($ev, 'deny') !== false) {
                $badgeClass = 'badge-offline';
              } elseif (strpos($ev, 'password') !== false || strpos($ev, 'security') !== false) {
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
                <td style="font-family: var(--font-mono); color: var(--accent-blue); font-size: 0.825rem;"><?= e($log['ip_address'] ?: 'Web Session') ?></td>
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
