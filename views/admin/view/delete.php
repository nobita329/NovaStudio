<?php
$title = 'Server — ' . $server['name'] . ': Delete';
$breadcrumb = 'Admin / Servers / ' . $server['name'] . ' / Delete';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$currentSubTab = 'delete';
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

.btn-danger-act {
  background: #ef4444;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.55rem 1.25rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-danger-act:hover { background: #dc2626; }
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>
          <?= e($server['name']) ?>
          <small>Delete this server from the panel.</small>
        </h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/servers">Servers</a></li>
        <li>/</li>
        <li><a href="/admin/servers/view/<?= e($server['id']) ?>"><?= e($server['name']) ?></a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Delete</li>
      </ol>
    </div>

    <?php require __DIR__ . '/../partials/server_navigation.php'; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: stretch;">
      <!-- 1. Safely Delete Server -->
      <div class="admin-box">
        <div class="admin-box-header">
          <h3 class="admin-box-title">Safely Delete Server</h3>
        </div>
        <div class="admin-box-body">
          <p>This action will attempt to delete the server from both the panel and the container virtualization engine. If either reports an error the action will be cancelled.</p>
          <p style="color: #f87171; font-size: 0.8rem; margin-top: 0.75rem;">
            Deleting a server is an irreversible action. <strong>All server data</strong> (including files, databases, and variables) will be removed immediately.
          </p>
        </div>
        <div class="admin-box-footer">
          <form action="/admin/servers/view/<?= e($server['id']) ?>/delete" method="POST" onsubmit="return confirm('Permanently and safely delete this server?');">
            <button type="submit" class="btn-danger-act">Safely Delete This Server</button>
          </form>
        </div>
      </div>

      <!-- 2. Force Delete Server -->
      <div class="admin-box box-danger">
        <div class="admin-box-header">
          <h3 class="admin-box-title" style="color: #f87171;">Force Delete Server</h3>
        </div>
        <div class="admin-box-body">
          <p>This action will attempt to forcibly delete the server from both the panel and Docker engine. Even if Docker daemon encounters an error, panel deletion proceeds.</p>
          <p style="color: #f87171; font-size: 0.8rem; margin-top: 0.75rem;">
            Deleting a server is an irreversible action. <strong>All database and configuration records</strong> will be removed from the system.
          </p>
        </div>
        <div class="admin-box-footer">
          <form action="/admin/servers/view/<?= e($server['id']) ?>/delete" method="POST" onsubmit="return confirm('FORCE DELETE THIS SERVER? Warning: This bypasses normal checks!');">
            <input type="hidden" name="force_delete" value="1">
            <button type="submit" class="btn-danger-act">Forcibly Delete This Server</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
