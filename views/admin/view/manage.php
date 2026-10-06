<?php
$title = 'Server — ' . $server['name'] . ': Manage';
$breadcrumb = 'Admin / Servers / ' . $server['name'] . ' / Manage';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$currentSubTab = 'manage';
$nodes = $nodes ?? [];
$isSuspended = ($server['status'] ?? '') === 'suspended';
?>
<link rel="stylesheet" href="/assets/css/nova-console.css">

<style>
.admin-content-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.admin-header-title h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  display: flex;
  align-items: baseline;
  gap: 0.6rem;
}

.admin-header-title small {
  font-size: 0.85rem;
  font-weight: 400;
  color: #94a3b8;
}

.admin-breadcrumbs {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  list-style: none;
  font-size: 0.8rem;
  color: #64748b;
  margin: 0;
  padding: 0;
}

.admin-breadcrumbs a {
  color: #38bdf8;
  text-decoration: none;
}

.admin-box {
  background: #111520;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-top: 3px solid #64748b;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
}

.box-danger { border-top-color: #ef4444; }
.box-warning { border-top-color: #f59e0b; }
.box-success { border-top-color: #10b981; }
.box-primary { border-top-color: #3b82f6; }

.admin-box-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.admin-box-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
}

.admin-box-body {
  padding: 1.25rem;
  flex-grow: 1;
  color: #94a3b8;
  font-size: 0.85rem;
  line-height: 1.5;
}

.admin-box-footer {
  padding: 1rem 1.25rem;
  background: rgba(0, 0, 0, 0.15);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0 0 8px 8px;
}

.btn-act {
  font-size: 0.85rem;
  font-weight: 600;
  padding: 0.5rem 1.15rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-act-danger { background: #ef4444; color: #fff; }
.btn-act-danger:hover { background: #dc2626; }
.btn-act-warning { background: #f59e0b; color: #000; }
.btn-act-warning:hover { background: #d97706; }
.btn-act-success { background: #10b981; color: #fff; }
.btn-act-success:hover { background: #059669; }
.btn-act-primary { background: #2563eb; color: #fff; }
.btn-act-primary:hover { background: #1d4ed8; }
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>
          <?= e($server['name']) ?>
          <small>Additional actions to control this server.</small>
        </h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/servers">Servers</a></li>
        <li>/</li>
        <li><a href="/admin/servers/view/<?= e($server['id']) ?>"><?= e($server['name']) ?></a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Manage</li>
      </ol>
    </div>

    <?php require __DIR__ . '/../partials/server_navigation.php'; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
      <!-- 1. Reinstall Server -->
      <div class="admin-box box-danger">
        <div class="admin-box-header">
          <h3 class="admin-box-title">Reinstall Server</h3>
        </div>
        <div class="admin-box-body">
          <p>This will reinstall the server with the assigned service scripts. <strong style="color: #f87171;">Danger!</strong> This could overwrite server configuration and jar files.</p>
        </div>
        <div class="admin-box-footer">
          <form action="/admin/servers/view/<?= e($server['id']) ?>/manage/reinstall" method="POST" onsubmit="return confirm('Reinstall this server container now?');">
            <button type="submit" class="btn-act btn-act-danger">Reinstall Server</button>
          </form>
        </div>
      </div>

      <!-- 2. Install Status Toggle -->
      <div class="admin-box box-primary">
        <div class="admin-box-header">
          <h3 class="admin-box-title">Install Status</h3>
        </div>
        <div class="admin-box-body">
          <p>Toggle the server between Installed and Installing status to force setup completion or trigger setup hooks.</p>
        </div>
        <div class="admin-box-footer">
          <form action="/admin/servers/view/<?= e($server['id']) ?>/manage/toggle" method="POST">
            <button type="submit" class="btn-act btn-act-primary">Toggle Install Status</button>
          </form>
        </div>
      </div>

      <!-- 3. Suspend / Unsuspend -->
      <?php if (!$isSuspended): ?>
        <div class="admin-box box-warning">
          <div class="admin-box-header">
            <h3 class="admin-box-title">Suspend Server</h3>
          </div>
          <div class="admin-box-body">
            <p>This will suspend the server, stop running processes, and immediately block user file management and access.</p>
          </div>
          <div class="admin-box-footer">
            <form action="/admin/servers/view/<?= e($server['id']) ?>/manage/suspension" method="POST">
              <input type="hidden" name="action" value="suspend">
              <button type="submit" class="btn-act btn-act-warning">Suspend Server</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <div class="admin-box box-success">
          <div class="admin-box-header">
            <h3 class="admin-box-title">Unsuspend Server</h3>
          </div>
          <div class="admin-box-body">
            <p>This will unsuspend the server container and restore standard user control access.</p>
          </div>
          <div class="admin-box-footer">
            <form action="/admin/servers/view/<?= e($server['id']) ?>/manage/suspension" method="POST">
              <input type="hidden" name="action" value="unsuspend">
              <button type="submit" class="btn-act btn-act-success">Unsuspend Server</button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- 4. Transfer Server -->
      <div class="admin-box box-success">
        <div class="admin-box-header">
          <h3 class="admin-box-title">Transfer Server</h3>
        </div>
        <div class="admin-box-body">
          <p>Transfer this server to another node configured on your panel. Allocations are automatically linked.</p>
          <form action="/admin/servers/view/<?= e($server['id']) ?>/manage/transfer" method="POST" id="transferForm">
            <div style="margin-top: 0.75rem;">
              <select name="target_node_id" class="form-control" style="font-size: 0.825rem;" required>
                <option value="">— Select destination node —</option>
                <?php foreach ($nodes as $n): ?>
                  <?php if ($n['id'] != $server['node_id']): ?>
                    <option value="<?= e($n['id']) ?>"><?= e($n['name']) ?></option>
                  <?php endif; ?>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>
        <div class="admin-box-footer">
          <button type="submit" form="transferForm" class="btn-act btn-act-success">Transfer Server</button>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
