<?php
$title = 'Databases — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Databases';
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
        <h1 class="page-title">Databases</h1>
        <p class="page-description">Provision and manage isolated SQL database instances for this server.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-db')">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
        New Database
      </button>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (empty($databases)): ?>
      <div class="card nova-empty-state">
        <div class="nova-empty-icon">
          <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m-16 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
        </div>
        <div class="nova-empty-title">No Databases Provisioned</div>
        <div class="nova-empty-desc">Create a MySQL/MariaDB database instance for plugins, mods, or application storage.</div>
        <button type="button" class="btn btn-primary" onclick="window.Nova.openModal('modal-new-db')">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"></path></svg>
          Create First Database
        </button>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Database</th>
              <th>Username</th>
              <th>Endpoint</th>
              <th>Password</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($databases as $db): ?>
              <tr>
                <td style="font-weight: 700; color: #fff; font-family: var(--font-mono); font-size: 0.9rem;">
                  <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="var(--accent-blue)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
                    <?= e($db['database_name']) ?>
                  </div>
                </td>
                <td style="font-family: var(--font-mono); color: var(--text-main); font-size: 0.85rem;"><?= e($db['username']) ?></td>
                <td>
                  <span style="font-family: var(--font-mono); color: var(--accent-blue); background: rgba(56, 189, 248, 0.1); padding: 0.25rem 0.55rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid rgba(56, 189, 248, 0.2);">
                    <?= e($db['host'] ?? '127.0.0.1') ?>:<?= e($db['port'] ?? 3005) ?>
                  </span>
                </td>
                <td>
                  <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #080b11; border: 1px solid var(--border-color); padding: 0.25rem 0.55rem; border-radius: 6px;">
                    <span id="db-pass-<?= e($db['id']) ?>" style="font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.05em;">••••••••••••</span>
                    <button type="button" class="btn-link" onclick="toggleDbPass('<?= e($db['id']) ?>', '<?= e($db['password']) ?>')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0;" title="Toggle Password">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                    <button type="button" class="btn-link" onclick="window.arixCopy && window.arixCopy('<?= e($db['password']) ?>')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0;" title="Copy Password">
                      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    </button>
                  </div>
                </td>
                <td style="text-align: right;">
                  <form action="/server/<?= e($server['id']) ?>/databases/<?= e($db['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete this database? All stored tables and data will be permanently destroyed.');">
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

    <!-- New Database Modal -->
    <div id="modal-new-db" class="nova-modal-overlay">
      <div class="nova-modal-dialog">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
            Create New Database
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/databases" method="POST">
          <div class="nova-modal-body">
            <div class="form-group">
              <label class="form-label">Database Name</label>
              <input type="text" name="database_name" class="form-control" placeholder="e.g. main, survival_db" required autofocus>
              <div class="form-text">Will be prefixed with server identifier for isolation.</div>
            </div>
            <div class="form-group">
              <label class="form-label">Database Host Node</label>
              <select name="host_id" class="form-control">
                <?php foreach (($hosts ?? []) as $h): ?>
                  <option value="<?= e($h['id']) ?>"><?= e($h['name']) ?> (<?= e($h['host']) ?>:<?= e($h['port']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Database</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
function toggleDbPass(id, actualPass) {
  const el = document.getElementById('db-pass-' + id);
  if (!el) return;
  if (el.textContent === '••••••••••••') {
    el.textContent = actualPass;
    el.style.color = '#38bdf8';
  } else {
    el.textContent = '••••••••••••';
    el.style.color = '';
  }
}
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
