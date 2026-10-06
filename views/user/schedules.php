<?php
$title = 'Schedules — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Schedules';
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
        <h1 class="page-title">Cron Schedules</h1>
        <p class="page-description">Automate server commands, backups, and restarts on recurring schedules.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-schedule')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        New Schedule
      </button>
    </div>

    <?php if (empty($schedules)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="nova-empty-title">No Schedules Configured</div>
        <div class="nova-empty-desc">Create automated cron triggers to dispatch console commands or initiate server restarts.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-schedule')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Create First Schedule
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Schedule Name</th>
              <th>Cron Expression</th>
              <th>Last Executed</th>
              <th>Status</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($schedules as $s): ?>
              <tr>
                <td>
                  <strong style="color: #fff; font-size: 0.925rem;"><?= e($s['name']) ?></strong>
                  <?php if (!empty($s['tasks'])): ?>
                    <div style="font-size: 0.775rem; color: var(--text-dim); margin-top: 0.25rem; display: flex; align-items: center; gap: 0.4rem;">
                      <span class="badge badge-info" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">
                        <?= count($s['tasks']) ?> <?= count($s['tasks']) === 1 ? 'task' : 'tasks' ?>
                      </span>
                      <span><?= e($s['tasks'][0]['action_type']) ?>: <?= e($s['tasks'][0]['payload']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-family: var(--font-mono); color: var(--accent-blue); background: rgba(56, 189, 248, 0.1); padding: 0.25rem 0.6rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid rgba(56, 189, 248, 0.2);">
                    <?= e($s['cron_expression']) ?>
                  </span>
                </td>
                <td style="color: var(--text-muted); font-size: 0.825rem;"><?= $s['last_run'] ? date('M d, H:i', strtotime($s['last_run'])) : 'Never' ?></td>
                <td>
                  <span class="status-badge <?= $s['is_active'] ? 'badge-online' : 'badge-offline' ?>">
                    <span class="badge-dot"></span><?= $s['is_active'] ? 'Active' : 'Disabled' ?>
                  </span>
                </td>
                <td style="text-align: right;">
                  <form action="/server/<?= e($server['id']) ?>/schedules/<?= e($s['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete this schedule?');">
                    <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">
                      <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <!-- New Schedule Modal -->
    <div id="modal-new-schedule" class="nova-modal-overlay">
      <div class="nova-modal-dialog">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Create New Schedule
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/schedules" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">Schedule Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Daily Restart at Midnight" required autofocus>
            </div>
            
            <div class="form-group">
              <label class="form-label">Common Presets</label>
              <select class="form-control" onchange="if(this.value) document.getElementById('cron-input').value = this.value;">
                <option value="">Choose a preset or type custom expression below...</option>
                <option value="0 0 * * *">Daily at Midnight (0 0 * * *)</option>
                <option value="0 */6 * * *">Every 6 Hours (0 */6 * * *)</option>
                <option value="0 */12 * * *">Every 12 Hours (0 */12 * * *)</option>
                <option value="0 0 * * 0">Weekly on Sunday (0 0 * * 0)</option>
                <option value="*/30 * * * *">Every 30 Minutes (*/30 * * * *)</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Cron Expression</label>
              <input type="text" id="cron-input" name="cron_expression" class="form-control" placeholder="0 0 * * *" required style="font-family: var(--font-mono);">
              <div class="form-text">Standard 5-part syntax: minute (0-59), hour (0-23), day of month (1-31), month (1-12), day of week (0-6).</div>
            </div>

            <div class="form-group">
              <label class="form-label">Task Action Type</label>
              <select name="action_type" class="form-control">
                <option value="command">Send Console Command</option>
                <option value="power">Trigger Power Action (start / restart / stop)</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Payload / Command</label>
              <input type="text" name="payload" class="form-control" placeholder="e.g. say Server restarting in 5 minutes! OR restart" required>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Schedule</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
