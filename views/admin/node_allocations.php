<?php
$title = 'Allocations — ' . $node['name'];
$breadcrumb = 'Admin / Nodes / ' . $node['name'] . ' / Allocations';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Allocations for <?= e($node['name']) ?></h1>
        <p class="page-description">Manage IP addresses and port bindings available for server deployment.</p>
      </div>

      <div style="display: flex; gap: 0.5rem;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-single-alloc').style.display='flex'">
          Single Port
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('modal-bulk-alloc').style.display='flex'">
          Bulk Allocation Range
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>IP Address</th>
            <th>Port</th>
            <th>Alias</th>
            <th>Assigned Server</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($allocations)): ?>
            <tr>
              <td colspan="5" style="text-align: center; color: var(--text-dim); padding: 3rem;">No port allocations registered for this node.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($allocations as $a): ?>
              <tr>
                <td style="font-family: var(--font-mono); color: var(--text-main);"><?= e($a['ip_address']) ?></td>
                <td style="font-family: var(--font-mono); color: var(--accent-blue); font-weight: 600;"><?= e($a['port']) ?></td>
                <td><?= e($a['ip_alias'] ?: '-') ?></td>
                <td>
                  <?php if (!empty($a['server_id'])): ?>
                    <a href="/admin/servers/<?= e($a['server_id']) ?>" style="font-weight: 600; color: #fff;">
                      <?= e($a['server_name'] ?: $a['server_id']) ?>
                    </a>
                  <?php else: ?>
                    <span class="status-badge badge-online">Available</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: right;">
                  <?php if (empty($a['server_id'])): ?>
                    <form action="/admin/allocations/<?= e($a['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete this allocation?')">
                      <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">Delete</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal Single Allocation -->
    <div id="modal-single-alloc" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 420px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Add Single Port</h3>
        <form action="/admin/nodes/<?= e($node['id']) ?>/allocations" method="POST">
          <div class="form-group">
            <label class="form-label">IP Address</label>
            <input type="text" name="ip_address" class="form-control" value="0.0.0.0" required>
          </div>
          <div class="form-group">
            <label class="form-label">Port</label>
            <input type="number" name="port" class="form-control" placeholder="25565" required>
          </div>
          <div class="form-group">
            <label class="form-label">IP Alias (Optional)</label>
            <input type="text" name="ip_alias" class="form-control" placeholder="play.myserver.com">
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-single-alloc').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Allocation</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Bulk Allocation Range -->
    <div id="modal-bulk-alloc" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 450px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Bulk Port Allocations</h3>
        <form action="/admin/nodes/<?= e($node['id']) ?>/allocations/bulk" method="POST">
          <div class="form-group">
            <label class="form-label">IP Address</label>
            <input type="text" name="ip_address" class="form-control" value="0.0.0.0" required>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
              <label class="form-label">Start Port</label>
              <input type="number" name="port_start" class="form-control" placeholder="25510" required>
            </div>
            <div class="form-group">
              <label class="form-label">End Port</label>
              <input type="number" name="port_end" class="form-control" placeholder="25525" required>
            </div>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-bulk-alloc').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Generate Range</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
