<?php
$title = 'Create Node';
$breadcrumb = 'Admin / Nodes / Create';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Create Node</h1>
        <p class="page-description">Connect an infrastructure host to the Nova Panel.</p>
      </div>
    </div>

    <div class="card" style="max-width: 750px;">
      <form action="/admin/nodes/new" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Node Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. EU Node 01" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Location</label>
            <select name="location_id" class="form-control" required>
              <?php foreach ($locations as $l): ?>
                <option value="<?= e($l['id']) ?>"><?= e($l['short_code']) ?> (<?= e($l['description']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Optional host description..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">FQDN or Host IP</label>
            <input type="text" name="fqdn" class="form-control" placeholder="node1.domain.com or 127.0.0.1" value="localhost" required>
          </div>
          <div class="form-group">
            <label class="form-label">Daemon Port</label>
            <input type="number" name="daemon_port" class="form-control" value="3003" required>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">SFTP Port</label>
            <input type="number" name="sftp_port" class="form-control" value="3004" required>
          </div>
          <div class="form-group">
            <label class="form-label">Scheme</label>
            <select name="scheme" class="form-control">
              <option value="http">HTTP</option>
              <option value="https">HTTPS (SSL)</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Total Memory (MB)</label>
            <input type="number" name="memory" class="form-control" value="16384" required>
          </div>
          <div class="form-group">
            <label class="form-label">Total Disk Storage (MB)</label>
            <input type="number" name="disk" class="form-control" value="102400" required>
          </div>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary">Create Node</button>
          <a href="/admin/nodes" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
