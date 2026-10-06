<?php
$title = 'Manage User: ' . ($targetUser['username'] ?? 'User');
$breadcrumb = 'Admin / Users / ' . ($targetUser['username'] ?? 'User');
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$languages = [
  'en' => 'English',
  'es' => 'Spanish',
  'de' => 'German',
  'fr' => 'French',
  'it' => 'Italian',
  'pt' => 'Portuguese',
  'ru' => 'Russian',
  'zh' => 'Chinese',
  'ja' => 'Japanese'
];

$currency = $currency ?? '$';
$isBanned = !empty($targetUser['is_banned']) || ($targetUser['status'] ?? '') === 'banned';
$isSuspended = !$isBanned && (($targetUser['status'] ?? '') === 'suspended' || (!empty($targetUser['suspended_until']) && strtotime($targetUser['suspended_until']) > time()));
$servers = $servers ?? [];
$activeSessions = $activeSessions ?? [];
$loginHistory = $loginHistory ?? [];
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
  overflow: hidden;
}

.box-primary { border-top-color: #3b82f6; }
.box-info { border-top-color: #06b6d4; }
.box-warning { border-top-color: #f59e0b; }
.box-danger { border-top-color: #ef4444; }

.admin-box-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
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
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-group {
  margin-bottom: 1.15rem;
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

textarea.form-control {
  min-height: 70px;
  resize: vertical;
}

.input-group {
  display: flex;
  align-items: stretch;
}

.input-group-addon {
  background: #0d121c;
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-right: none;
  border-radius: 6px 0 0 6px;
  color: #94a3b8;
  padding: 0.65rem 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
}

.input-group .form-control {
  border-radius: 0 6px 6px 0;
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

.btn-success-xs {
  background: #10b981;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.75rem;
  padding: 0.35rem 0.75rem;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  text-decoration: none;
  transition: background 0.2s;
}
.btn-success-xs:hover { background: #059669; }

.btn-danger-xs {
  background: #ef4444;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.75rem;
  padding: 0.35rem 0.75rem;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-danger-xs:hover { background: #dc2626; }

.btn-danger-sm {
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
.btn-danger-sm:hover { background: #dc2626; }
.btn-danger-sm:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.label {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}
.label-success { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }
.label-warning { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.label-danger { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
.label-info { background: rgba(6, 182, 212, 0.2); color: #38bdf8; border: 1px solid rgba(6, 182, 212, 0.4); }
.label-default { background: rgba(100, 116, 139, 0.2); color: #cbd5e1; border: 1px solid rgba(100, 116, 139, 0.4); }

.table-admin-nested {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.table-admin-nested th {
  background: #0d121c;
  color: #94a3b8;
  font-weight: 600;
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.table-admin-nested td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  color: #cbd5e1;
  vertical-align: middle;
}
.table-admin-nested tbody tr:hover {
  background: rgba(255, 255, 255, 0.025);
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>
          <?= e($targetUser['first_name'] . ' ' . $targetUser['last_name']) ?>
          <small><?= e($targetUser['username']) ?></small>
        </h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/users">Users</a></li>
        <li>/</li>
        <li style="color: #94a3b8;"><?= e($targetUser['username']) ?></li>
      </ol>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Impersonation Form -->
    <?php if (empty($targetUser['root_admin'])): ?>
      <form id="impersonateForm" action="/admin/users/<?= e($targetUser['id']) ?>/impersonate" method="POST" style="display: none;">
      </form>
    <?php endif; ?>

    <!-- Main Edit Form -->
    <form action="/admin/users/<?= e($targetUser['id']) ?>" method="POST">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start; margin-bottom: 1.5rem;">
        
        <!-- LEFT COLUMN: Identity & Billing -->
        <div>
          <!-- Box: Identity -->
          <div class="admin-box box-primary">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Identity</h3>
              <?php if (empty($targetUser['root_admin'])): ?>
                <button form="impersonateForm" type="submit" class="btn-success-xs">
                  <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                  Login as User
                </button>
              <?php endif; ?>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label for="email" class="control-label">Email</label>
                <input type="email" name="email" id="email" value="<?= e($targetUser['email']) ?>" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="username" class="control-label">Username</label>
                <input type="text" name="username" id="username" value="<?= e($targetUser['username']) ?>" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="name_first" class="control-label">Client First Name</label>
                <input type="text" name="name_first" id="name_first" value="<?= e($targetUser['first_name']) ?>" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="name_last" class="control-label">Client Last Name</label>
                <input type="text" name="name_last" id="name_last" value="<?= e($targetUser['last_name']) ?>" class="form-control" required>
              </div>

              <div class="form-group">
                <label for="language" class="control-label">Default Language</label>
                <select name="language" id="language" class="form-control">
                  <?php foreach ($languages as $key => $name): ?>
                    <option value="<?= e($key) ?>" <?= ($targetUser['language'] ?? 'en') === $key ? 'selected' : '' ?>>
                      <?= e($name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="help-text">The default language to use when rendering the Panel for this user.</p>
              </div>
            </div>
            <div class="admin-box-footer">
              <button type="submit" class="btn-primary-sm">Update User</button>
            </div>
          </div>

          <!-- Box: Billing Information -->
          <div class="admin-box box-primary">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Billing Information</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label for="country" class="control-label">Country</label>
                <input type="text" name="country" id="country" value="<?= e($targetUser['country'] ?? '') ?>" class="form-control">
              </div>

              <div class="form-group">
                <label for="address" class="control-label">Address</label>
                <input type="text" name="address" id="address" value="<?= e($targetUser['address'] ?? '') ?>" class="form-control">
              </div>

              <div class="form-group">
                <label for="zip_code" class="control-label">Zip Code</label>
                <input type="text" name="zip_code" id="zip_code" value="<?= e($targetUser['zip_code'] ?? '') ?>" class="form-control">
              </div>

              <div class="form-group">
                <label for="credit" class="control-label">Credit Balance</label>
                <div class="input-group">
                  <span class="input-group-addon"><?= e($currency) ?></span>
                  <input type="number" step="0.01" name="credit" id="credit" value="<?= e($targetUser['credit'] ?? '0.00') ?>" class="form-control">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Password, Permissions, Account Status -->
        <div>
          <!-- Box: Password -->
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Password</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group" style="margin-bottom: 0;">
                <label for="password" class="control-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
                <p class="help-text">Leave blank to keep this user's password the same. User will not receive any notification if password is changed.</p>
              </div>
            </div>
          </div>

          <!-- Box: Permissions -->
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Permissions</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group" style="margin-bottom: 0;">
                <label for="root_admin" class="control-label">Administrator</label>
                <select name="root_admin" id="root_admin" class="form-control">
                  <option value="0" <?= empty($targetUser['root_admin']) ? 'selected' : '' ?>>No</option>
                  <option value="1" <?= !empty($targetUser['root_admin']) ? 'selected' : '' ?>>Yes</option>
                </select>
                <p class="help-text">Setting this to 'Yes' gives a user full administrative access.</p>
              </div>
            </div>
          </div>

          <!-- Box: Account Status -->
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Account Status</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label class="control-label">Current Status</label>
                <div>
                  <?php if ($isBanned): ?>
                    <span class="label label-danger">Banned</span>
                  <?php elseif ($isSuspended): ?>
                    <span class="label label-warning">Suspended until <?= date('Y-m-d H:i', strtotime($targetUser['suspended_until'])) ?></span>
                  <?php else: ?>
                    <span class="label label-success">Active</span>
                  <?php endif; ?>
                </div>
                <?php if ($isBanned && !empty($targetUser['ban_reason'])): ?>
                  <p class="help-text" style="margin-top: 0.5rem;">Reason: <?= e($targetUser['ban_reason']) ?></p>
                <?php endif; ?>
                <?php if ($isSuspended && !empty($targetUser['suspension_reason'])): ?>
                  <p class="help-text" style="margin-top: 0.5rem;">Reason: <?= e($targetUser['suspension_reason']) ?></p>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <label for="is_banned" class="control-label">Ban Account</label>
                <select name="is_banned" id="is_banned" class="form-control">
                  <option value="0" <?= !$isBanned ? 'selected' : '' ?>>No</option>
                  <option value="1" <?= $isBanned ? 'selected' : '' ?>>Yes</option>
                </select>
                <p class="help-text">Ban the user from logging in. This will suspend all servers owned by them.</p>
              </div>

              <div class="form-group">
                <label for="ban_reason" class="control-label">Ban Reason</label>
                <textarea name="ban_reason" id="ban_reason" class="form-control"><?= e($targetUser['ban_reason'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label for="suspended_until" class="control-label">Suspend Until</label>
                <input type="datetime-local" 
                       name="suspended_until" 
                       id="suspended_until" 
                       value="<?= !empty($targetUser['suspended_until']) ? date('Y-m-d\TH:i', strtotime($targetUser['suspended_until'])) : '' ?>" 
                       class="form-control">
                <p class="help-text">Leave blank to remove the suspension.</p>
              </div>

              <div class="form-group" style="margin-bottom: 0;">
                <label for="suspension_reason" class="control-label">Suspension Reason</label>
                <textarea name="suspension_reason" id="suspension_reason" class="form-control"><?= e($targetUser['suspension_reason'] ?? '') ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>

    <!-- FULL WIDTH: Servers Table -->
    <div class="admin-box box-primary">
      <div class="admin-box-header">
        <h3 class="admin-box-title">Servers (<?= count($servers) ?>)</h3>
      </div>
      <div class="table-responsive">
        <table class="table-admin-nested">
          <thead>
            <tr>
              <th>Name</th>
              <th>Identifier</th>
              <th>Node</th>
              <th>Type</th>
              <th>Connection</th>
              <th>Status</th>
              <th style="text-align: right;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($servers)): ?>
              <tr>
                <td colspan="7" style="text-align: center; color: #64748b; padding: 2.5rem;">
                  This user has no associated server instances.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($servers as $srv): ?>
                <tr>
                  <td>
                    <a href="/admin/servers/<?= e($srv['id']) ?>" style="font-weight: 600; color: #38bdf8; text-decoration: none;">
                      <?= e($srv['name']) ?>
                    </a>
                  </td>
                  <td><code><?= e(substr($srv['id'], 0, 8)) ?></code></td>
                  <td><a href="/admin/nodes" style="color: #cbd5e1;"><?= e($srv['node_name'] ?? 'Local') ?></a></td>
                  <td>
                    <span class="label label-default">Standard</span>
                  </td>
                  <td>
                    <code><?= e(!empty($srv['ip_alias']) ? $srv['ip_alias'] : '0.0.0.0') ?>:<?= e($srv['port'] ?? '25501') ?></code>
                  </td>
                  <td>
                    <?php if (($srv['status'] ?? '') === 'suspended'): ?>
                      <span class="label label-danger">Suspended</span>
                    <?php elseif (($srv['status'] ?? '') === 'running'): ?>
                      <span class="label label-success">Active</span>
                    <?php else: ?>
                      <span class="label label-warning"><?= ucfirst($srv['status'] ?? 'Offline') ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align: right;">
                    <a href="/admin/servers/<?= e($srv['id']) ?>" class="btn-primary-sm" style="font-size: 0.75rem; padding: 0.35rem 0.75rem; text-decoration: none;">
                      Manage
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- FULL WIDTH: Active Sessions -->
    <div class="admin-box box-info">
      <div class="admin-box-header">
        <h3 class="admin-box-title">Active Sessions</h3>
        <form action="/admin/users/<?= e($targetUser['id']) ?>/session/revoke_all" method="POST">
          <button type="submit" class="btn-danger-xs">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Revoke All
          </button>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table-admin-nested">
          <thead>
            <tr>
              <th>Device</th>
              <th>Platform</th>
              <th>Browser</th>
              <th>IP Address</th>
              <th>Location</th>
              <th>VPN</th>
              <th>Last Active</th>
              <th style="text-align: right;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($activeSessions)): ?>
              <tr>
                <td colspan="8" style="text-align: center; color: #64748b; padding: 2rem;">
                  No active web sessions detected for this user.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($activeSessions as $ses): ?>
                <tr>
                  <td><?= e($ses['device_type'] ?? 'Desktop') ?></td>
                  <td><?= e($ses['platform'] ?? 'Linux') ?></td>
                  <td><?= e($ses['browser'] ?? 'Browser') ?></td>
                  <td><code><?= e($ses['ip_address'] ?? '127.0.0.1') ?></code></td>
                  <td><?= e(($ses['city'] ?? 'Local') . ', ' . ($ses['country'] ?? 'US')) ?></td>
                  <td>
                    <?php if (!empty($ses['is_vpn'])): ?>
                      <span class="label label-danger">Yes</span>
                    <?php else: ?>
                      <span class="label label-success">No</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e($ses['last_active_at'] ?? 'Just now') ?></td>
                  <td style="text-align: right;">
                    <form action="/admin/users/<?= e($targetUser['id']) ?>/session/revoke" method="POST" style="display:inline;">
                      <input type="hidden" name="session_id" value="<?= e($ses['session_id']) ?>">
                      <button type="submit" class="btn-danger-xs">Revoke</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- FULL WIDTH: Login History -->
    <div class="admin-box box-warning">
      <div class="admin-box-header">
        <h3 class="admin-box-title">Login History (Last 50)</h3>
      </div>
      <div class="table-responsive">
        <table class="table-admin-nested">
          <thead>
            <tr>
              <th>Logged In</th>
              <th>Device</th>
              <th>Platform</th>
              <th>Browser</th>
              <th>IP Address</th>
              <th>Location</th>
              <th>VPN</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($loginHistory)): ?>
              <tr>
                <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
                  No historical sign-in logs recorded.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($loginHistory as $lh): ?>
                <tr>
                  <td><?= e($lh['created_at']) ?></td>
                  <td>Desktop</td>
                  <td>Linux / Windows</td>
                  <td>Chrome / Firefox</td>
                  <td><code><?= e($lh['ip_address'] ?? '127.0.0.1') ?></code></td>
                  <td>Localhost (US)</td>
                  <td><span class="label label-success">No</span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- FULL WIDTH: Delete User -->
    <div class="admin-box box-danger">
      <div class="admin-box-header">
        <h3 class="admin-box-title" style="color: #f87171;">Delete User</h3>
      </div>
      <div class="admin-box-body">
        <p style="color: #94a3b8; font-size: 0.85rem; margin: 0;">
          There must be no servers associated with this account in order for it to be deleted.
        </p>
      </div>
      <div class="admin-box-footer" style="justify-content: flex-end;">
        <form action="/admin/users/<?= e($targetUser['id']) ?>/delete" method="POST" onsubmit="return confirm('Permanently delete this user account?');">
          <button type="submit" 
                  class="btn-danger-sm" 
                  <?= count($servers) > 0 ? 'disabled title="Delete servers first"' : '' ?>>
            Delete User
          </button>
        </form>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
