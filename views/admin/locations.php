<?php
$title = 'Locations';
$breadcrumb = 'Admin / Locations';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Datacenter Locations</h1>
        <p class="page-description">Geographic regions and datacenters where nodes reside.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-new-loc').style.display='flex'">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Create Location
      </button>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Short Code</th>
            <th>Description</th>
            <th>Nodes Assigned</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($locations as $l): ?>
            <tr>
              <td style="font-family: var(--font-mono); color: var(--text-dim);"><?= e($l['id']) ?></td>
              <td><strong style="color: var(--accent-blue); font-family: var(--font-mono);"><?= e($l['short_code']) ?></strong></td>
              <td><?= e($l['description']) ?></td>
              <td style="font-weight: 600;"><?= e($l['node_count'] ?? 0) ?></td>
              <td style="text-align: right;">
                <form action="/admin/locations/<?= e($l['id']) ?>/delete" method="POST" onsubmit="return confirm('Delete location?')" style="display:inline;">
                  <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--accent-danger);">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal Create Location -->
    <div id="modal-new-loc" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 420px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Create Location</h3>
        <form action="/admin/locations" method="POST">
          <div class="form-group">
            <label class="form-label">Short Code</label>
            <input type="text" name="short_code" class="form-control" placeholder="e.g. US1, EU-GER" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" placeholder="e.g. Frankfurt, Germany Datacenter" required>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-new-loc').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Save Location</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
