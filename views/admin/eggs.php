<?php
$title = 'Eggs — ' . $nest['name'];
$breadcrumb = 'Admin / Nests / ' . $nest['name'] . ' / Eggs';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Eggs in Nest: <?= e($nest['name']) ?></h1>
        <p class="page-description">Container definitions, Docker images, and startup templates.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-new-egg').style.display='flex'">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Egg
      </button>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Egg Name</th>
            <th>Docker Image</th>
            <th>Default Startup Command</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($eggs)): ?>
            <tr>
              <td colspan="4" style="text-align: center; color: var(--text-dim); padding: 3rem;">No eggs configured in this nest.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($eggs as $egg): ?>
              <tr>
                <td><strong style="color: #fff;"><?= e($egg['name']) ?></strong></td>
                <td><code style="font-family: var(--font-mono); color: var(--accent-blue); font-size: 0.8rem;"><?= e($egg['docker_image']) ?></code></td>
                <td><code style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.8rem;"><?= e($egg['startup_command']) ?></code></td>
                <td style="color: var(--text-dim); font-size: 0.8rem;"><?= date('M d, Y', strtotime($egg['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal New Egg -->
    <div id="modal-new-egg" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 520px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Add New Egg to <?= e($nest['name']) ?></h3>
        <form action="/admin/nests/<?= e($nest['id']) ?>/eggs" method="POST">
          <div class="form-group">
            <label class="form-label">Egg Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Node.js 22 LTS" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Docker Image</label>
            <input type="text" name="docker_image" class="form-control" placeholder="e.g. ghcr.io/ptero-eggs/yolks:nodejs_22" required>
          </div>
          <div class="form-group">
            <label class="form-label">Startup Command</label>
            <input type="text" name="startup_command" class="form-control" placeholder="e.g. sh -c 'npm install && npm start'" required>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-new-egg').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Egg</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
