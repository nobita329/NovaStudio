<?php
$title = 'Create Database Host';
$breadcrumb = 'Admin / Databases / Create';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Add Database Host</h1>
        <p class="page-description">Configure connection parameters to a MariaDB/MySQL instance.</p>
      </div>
    </div>

    <div class="card" style="max-width: 650px;">
      <form action="/admin/database/new" method="POST">
        <div class="form-group">
          <label class="form-label">Host Display Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Local MariaDB 3005" required autofocus>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Host Address</label>
            <input type="text" name="host" class="form-control" placeholder="127.0.0.1" value="127.0.0.1" required>
          </div>
          <div class="form-group">
            <label class="form-label">Port</label>
            <input type="number" name="port" class="form-control" value="3005" required>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="root" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="YourStrongPassword" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Linked Node</label>
          <select name="node_id" class="form-control">
            <?php foreach ($nodes as $node): ?>
              <option value="<?= e($node['id']) ?>"><?= e($node['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
          <button type="submit" class="btn btn-primary">Create Database Host</button>
          <a href="/admin/database" class="btn btn-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
