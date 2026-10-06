<?php
$title = 'Server — ' . $server['name'] . ': Build Details';
$breadcrumb = 'Admin / Servers / ' . $server['name'] . ' / Build Configuration';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar_admin.php';

$currentSubTab = 'build';
$allocations = $allocations ?? [];
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
}

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
}

.admin-box-footer {
  padding: 1rem 1.25rem;
  background: rgba(0, 0, 0, 0.15);
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 0 0 8px 8px;
  display: flex;
  justify-content: flex-end;
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

.input-group {
  display: flex;
  align-items: stretch;
}

.input-group-addon {
  background: #0d121c;
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-left: none;
  border-radius: 0 6px 6px 0;
  color: #94a3b8;
  padding: 0.65rem 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.input-group .form-control {
  border-radius: 6px 0 0 6px;
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
          <small>Control allocations and system resources for this server.</small>
        </h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/servers">Servers</a></li>
        <li>/</li>
        <li><a href="/admin/servers/view/<?= e($server['id']) ?>"><?= e($server['name']) ?></a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Build Configuration</li>
      </ol>
    </div>

    <?php require __DIR__ . '/../partials/server_navigation.php'; ?>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="/admin/servers/view/<?= e($server['id']) ?>/build" method="POST">
      <div style="display: grid; grid-template-columns: 1fr 1.2fr; gap: 1.5rem; align-items: start;">
        
        <!-- Left Column: Resource Management -->
        <div class="admin-box box-primary">
          <div class="admin-box-header">
            <h3 class="admin-box-title">Resource Management</h3>
          </div>
          <div class="admin-box-body">
            <div class="form-group">
              <label for="cpu" class="control-label">CPU Limit</label>
              <div class="input-group">
                <input type="number" name="cpu" id="cpu" class="form-control" value="<?= e($server['cpu']) ?>" required>
                <span class="input-group-addon">%</span>
              </div>
              <p class="help-text">Each core is considered <code>100%</code>. Set to <code>0</code> for unlimited.</p>
            </div>

            <div class="form-group">
              <label for="threads" class="control-label">CPU Pinning</label>
              <input type="text" name="threads" id="threads" class="form-control" value="<?= e($server['threads'] ?? '') ?>" placeholder="e.g. 0,1">
              <p class="help-text">Specific CPU cores (comma separated or range). Leave blank for all cores.</p>
            </div>

            <div class="form-group">
              <label for="memory" class="control-label">Allocated Memory</label>
              <div class="input-group">
                <input type="number" name="memory" id="memory" class="form-control" value="<?= e($server['memory']) ?>" required>
                <span class="input-group-addon">MiB</span>
              </div>
              <p class="help-text">Maximum memory allowed for this container.</p>
            </div>

            <div class="form-group">
              <label for="swap" class="control-label">Allocated Swap</label>
              <div class="input-group">
                <input type="number" name="swap" id="swap" class="form-control" value="<?= e($server['swap'] ?? 0) ?>">
                <span class="input-group-addon">MiB</span>
              </div>
              <p class="help-text">Setting this to <code>0</code> disables swap.</p>
            </div>

            <div class="form-group">
              <label for="disk" class="control-label">Disk Space Limit</label>
              <div class="input-group">
                <input type="number" name="disk" id="disk" class="form-control" value="<?= e($server['disk']) ?>" required>
                <span class="input-group-addon">MiB</span>
              </div>
              <p class="help-text">Disk storage allowance for the server container.</p>
            </div>

            <div class="form-group">
              <label for="io" class="control-label">Block IO Proportion</label>
              <input type="number" name="io" id="io" class="form-control" value="<?= e($server['io'] ?? 500) ?>">
              <p class="help-text">Value should be between <code>10</code> and <code>1000</code>.</p>
            </div>

            <div class="form-group">
              <label class="control-label">OOM Killer</label>
              <div style="display: flex; gap: 1.5rem; margin-top: 0.35rem;">
                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.4rem; color: #cbd5e1;">
                  <input type="radio" name="oom_disabled" value="0" <?= empty($server['oom_disabled']) ? 'checked' : '' ?> style="accent-color: #ef4444;"> Enabled
                </label>
                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.4rem; color: #cbd5e1;">
                  <input type="radio" name="oom_disabled" value="1" <?= !empty($server['oom_disabled']) ? 'checked' : '' ?> style="accent-color: #10b981;"> Disabled
                </label>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Application Feature Limits & Allocation Management -->
        <div>
          <!-- Feature Limits -->
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Application Feature Limits</h3>
            </div>
            <div class="admin-box-body">
              <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                  <label for="database_limit" class="control-label">Database Limit</label>
                  <input type="number" name="database_limit" id="database_limit" class="form-control" value="<?= e($server['database_limit'] ?? 1) ?>">
                  <p class="help-text">Total databases allowed.</p>
                </div>

                <div class="form-group">
                  <label for="allocation_limit" class="control-label">Allocation Limit</label>
                  <input type="number" name="allocation_limit" id="allocation_limit" class="form-control" value="<?= e($server['allocation_limit'] ?? 1) ?>">
                  <p class="help-text">Total network ports allowed.</p>
                </div>

                <div class="form-group">
                  <label for="backup_limit" class="control-label">Backup Limit</label>
                  <input type="number" name="backup_limit" id="backup_limit" class="form-control" value="<?= e($server['backup_limit'] ?? 3) ?>">
                  <p class="help-text">Total backups allowed.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Allocation Management -->
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Allocation Management</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label for="allocation_id" class="control-label">Primary Game Port</label>
                <select name="allocation_id" id="allocation_id" class="form-control">
                  <?php foreach ($allocations as $a): ?>
                    <option value="<?= e($a['id']) ?>" <?= $server['allocation_id'] == $a['id'] ? 'selected' : '' ?>>
                      <?= e(!empty($a['ip_alias']) ? $a['ip_alias'] : $a['ip_address']) ?>:<?= e($a['port']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="help-text">The default connection address used by this game server.</p>
              </div>
            </div>
            <div class="admin-box-footer">
              <button type="submit" class="btn-primary-sm">Update Build Configuration</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </main>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
