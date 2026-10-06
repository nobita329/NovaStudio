<?php
$title = 'Nests Management';
$breadcrumb = 'Admin / Nests';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area">
    <div class="page-header">
      <div>
        <h1 class="page-title">Nests (Image Categories)</h1>
        <p class="page-description">Organize game server engines, runtimes, and bot environments into Nests.</p>
      </div>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-new-nest').style.display='flex'">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        New Nest
      </button>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nest Name</th>
            <th>Description</th>
            <th>Eggs</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($nests as $nest): ?>
            <tr>
              <td style="font-family: var(--font-mono); color: var(--text-dim);"><?= e($nest['id']) ?></td>
              <td>
                <a href="/admin/nests/<?= e($nest['id']) ?>/eggs" style="font-weight: 600; color: #fff;">
                  <?= e($nest['name']) ?>
                </a>
              </td>
              <td><?= e($nest['description']) ?></td>
              <td style="font-weight: 600; color: var(--accent-blue);"><?= e($nest['egg_count'] ?? 0) ?> Eggs</td>
              <td style="text-align: right;">
                <a href="/admin/nests/<?= e($nest['id']) ?>/eggs" class="btn btn-secondary btn-sm">
                  View Eggs
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Modal New Nest -->
    <div id="modal-new-nest" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 1rem;">
      <div style="background: var(--bg-card); width: 100%; max-width: 440px; border-radius: var(--radius-lg); border: 1px solid var(--border-color); padding: 1.5rem;">
        <h3 style="margin-bottom: 1rem; color: #fff;">Create New Nest</h3>
        <form action="/admin/nests" method="POST">
          <div class="form-group">
            <label class="form-label">Nest Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Rust / Steam / Go" required autofocus>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Category description..."></textarea>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.25rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('modal-new-nest').style.display='none'">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Nest</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
