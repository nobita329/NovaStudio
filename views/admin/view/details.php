<?php
$title = 'Server — ' . $server['name'] . ': Details';
$breadcrumb = 'Admin / Servers / ' . $server['name'] . ' / Details';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$currentSubTab = 'details';
$users = $users ?? [];
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
  border-top: 3px solid #3b82f6;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  margin-bottom: 1.5rem;
}

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
}

.admin-box-footer {
  padding: 1rem 1.25rem;
  background: rgba(0, 0, 0, 0.15);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0 0 8px 8px;
}

.form-group {
  margin-bottom: 1.25rem;
}

.control-label {
  display: block;
  font-size: 0.8rem;
  font-weight: 600;
  color: #cbd5e1;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 0.4rem;
}

.form-control {
  width: 100%;
  background: #182030;
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 6px;
  color: #ffffff;
  padding: 0.65rem 0.85rem;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-control:focus {
  border-color: #38bdf8;
}

.help-text {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 0.35rem;
  line-height: 1.4;
}

.btn-primary-sm {
  background: #2563eb;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.55rem 1.25rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-primary-sm:hover { background: #1d4ed8; }
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>
          <?= e($server['name']) ?>
          <small>Edit details for this server including owner and container.</small>
        </h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/servers">Servers</a></li>
        <li>/</li>
        <li><a href="/admin/servers/view/<?= e($server['id']) ?>"><?= e($server['name']) ?></a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Details</li>
      </ol>
    </div>

    <?php require __DIR__ . '/../partials/server_navigation.php'; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="admin-box">
      <div class="admin-box-header">
        <h3 class="admin-box-title">Base Information</h3>
      </div>
      <form action="/admin/servers/view/<?= e($server['id']) ?>/details" method="POST">
        <div class="admin-box-body">
          <div class="form-group">
            <label for="name" class="control-label">Server Name</label>
            <input type="text" name="name" id="name" value="<?= e($server['name']) ?>" class="form-control" required>
            <p class="help-text">Character limits: <code>a-zA-Z0-9_-</code> and <code>[Space]</code>.</p>
          </div>

          <div class="form-group">
            <label for="external_id" class="control-label">External Identifier</label>
            <input type="text" name="external_id" id="external_id" value="<?= e($server['external_id'] ?? '') ?>" class="form-control">
            <p class="help-text">Leave empty to not assign an external identifier for this server.</p>
          </div>

          <div class="form-group">
            <label for="owner_id" class="control-label">Server Owner</label>
            <select name="owner_id" id="owner_id" class="form-control" required>
              <?php foreach ($users as $u): ?>
                <option value="<?= e($u['id']) ?>" <?= $server['owner_id'] == $u['id'] ? 'selected' : '' ?>>
                  <?= e($u['username']) ?> (<?= e($u['email']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <p class="help-text">You can change the owner of this server by selecting another user.</p>
          </div>

          <div class="form-group">
            <label for="description" class="control-label">Server Description</label>
            <textarea name="description" id="description" rows="3" class="form-control"><?= e($server['description'] ?? '') ?></textarea>
            <p class="help-text">A brief description of this server.</p>
          </div>

          <div class="form-group">
            <label for="exp_date" class="control-label">Server Expiry Date</label>
            <input type="datetime-local" 
                   name="exp_date" 
                   id="exp_date" 
                   value="<?= !empty($server['exp_date']) ? date('Y-m-d\TH:i', strtotime($server['exp_date'])) : '' ?>" 
                   class="form-control">
            <p class="help-text">Set the expiry date for this server. Leave blank for no expiry.</p>
          </div>
        </div>
        <div class="admin-box-footer">
          <button type="submit" class="btn-primary-sm">Update Details</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
