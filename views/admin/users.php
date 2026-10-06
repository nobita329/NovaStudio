<?php
$title = 'List Users — Admin';
$breadcrumb = 'Admin / Users';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

function timeAgoUser($datetime) {
  if (empty($datetime)) return 'Never';
  $timestamp = strtotime($datetime);
  if (!$timestamp) return 'Never';
  $diff = time() - $timestamp;
  if ($diff < 60) return 'Just now';
  if ($diff < 3600) return floor($diff / 60) . ' mins ago';
  if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
  if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
  return date('M d, Y', $timestamp);
}
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
  margin-bottom: 2rem;
  overflow: hidden;
}

.admin-box-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.admin-box-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
}

.admin-box-tools {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.search-box-wrap {
  display: flex;
  align-items: stretch;
  background: #182030;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  overflow: hidden;
}

.search-input {
  background: transparent;
  border: none;
  color: #ffffff;
  padding: 0.45rem 0.75rem;
  font-size: 0.825rem;
  outline: none;
  width: 200px;
}

.search-btn {
  background: rgba(255, 255, 255, 0.05);
  border: none;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  padding: 0 0.75rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.search-btn:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.btn-create-new {
  background: #2563eb;
  color: #ffffff;
  font-size: 0.825rem;
  font-weight: 600;
  padding: 0.48rem 1rem;
  border-radius: 6px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-create-new:hover {
  background: #1d4ed8;
}

.table-users {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.table-users th {
  background: #0d121c;
  color: #94a3b8;
  font-weight: 600;
  text-align: left;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}

.table-users th.text-center, .table-users td.text-center {
  text-align: center;
}

.table-users td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  color: #cbd5e1;
  vertical-align: middle;
}

.table-users tbody tr:hover {
  background: rgba(255, 255, 255, 0.025);
}

.user-id-code {
  background: #090d16;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 4px;
  padding: 0.2rem 0.4rem;
  font-family: var(--font-mono);
  font-size: 0.8rem;
  color: #e2e8f0;
}

.user-email-link {
  color: #38bdf8;
  font-weight: 600;
  text-decoration: none;
}
.user-email-link:hover {
  text-decoration: underline;
}

.badge-star-admin {
  color: #eab308;
  font-size: 0.9rem;
  margin-left: 0.3rem;
  display: inline-block;
}

.dot-status-user {
  display: inline-block;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-left: 0.3rem;
}
.dot-banned { background: #ef4444; }
.dot-suspended { background: #f59e0b; }

.icon-2fa-enabled { color: #10b981; }
.icon-2fa-disabled { color: #ef4444; }

.gravatar-circle {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.link-servers-count {
  background: rgba(56, 189, 248, 0.12);
  color: #38bdf8;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-weight: 700;
  text-decoration: none;
  font-family: var(--font-mono);
}
.link-servers-count:hover {
  background: rgba(56, 189, 248, 0.25);
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>Users <small>All registered users on the system.</small></h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Users</li>
      </ol>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- User List Box -->
    <div class="admin-box">
      <div class="admin-box-header">
        <h3 class="admin-box-title">User List</h3>
        <div class="admin-box-tools">
          <form action="/admin/users" method="GET" style="display: inline-block;">
            <div class="search-box-wrap">
              <input type="text" 
                     name="filter[email]" 
                     class="search-input" 
                     value="<?= e($search ?? '') ?>" 
                     placeholder="Search...">
              <button type="submit" class="search-btn" title="Search">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              </button>
            </div>
          </form>
          <a href="/admin/users/new" class="btn-create-new">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New
          </a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table-users">
          <thead>
            <tr>
              <th style="width: 60px;">ID</th>
              <th>Email</th>
              <th>Client Name</th>
              <th>Username</th>
              <th class="text-center" style="width: 70px;">2FA</th>
              <th class="text-center" style="width: 120px;" title="Servers that this user is marked as the owner of.">Servers Owned</th>
              <th class="text-center" style="width: 100px;" title="Servers that this user can access because they are marked as a subuser.">Can Access</th>
              <th class="text-center" style="width: 160px;">Last Seen</th>
              <th class="text-center" style="width: 50px;"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($users)): ?>
              <tr>
                <td colspan="9" style="text-align: center; color: #64748b; padding: 3rem;">
                  No users found in the system matching your query.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($users as $u): ?>
                <?php
                  $isRoot = !empty($u['root_admin']);
                  $isBanned = !empty($u['is_banned']) || ($u['status'] ?? '') === 'banned';
                  $isSuspended = !$isBanned && (($u['status'] ?? '') === 'suspended' || (!empty($u['suspended_until']) && strtotime($u['suspended_until']) > time()));
                  $has2fa = !empty($u['two_factor_enabled']);
                  $fullName = trim(($u['last_name'] ?? '') . ', ' . ($u['first_name'] ?? ''));
                  if ($fullName === ',') $fullName = $u['username'];
                  $avatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($u['email'] ?? ''))) . '?s=100&d=mp';
                ?>
                <tr>
                  <td><code class="user-id-code"><?= e($u['id']) ?></code></td>
                  <td>
                    <a href="/admin/users/<?= e($u['id']) ?>" class="user-email-link">
                      <?= e($u['email']) ?>
                    </a>
                    <?php if ($isRoot): ?>
                      <span class="badge-star-admin" title="Administrator">★</span>
                    <?php endif; ?>
                    <?php if ($isBanned): ?>
                      <span class="dot-status-user dot-banned" title="Banned"></span>
                    <?php elseif ($isSuspended): ?>
                      <span class="dot-status-user dot-suspended" title="Suspended"></span>
                    <?php endif; ?>
                  </td>
                  <td><?= e($fullName) ?></td>
                  <td style="font-weight: 500; color: #f1f5f9;"><?= e($u['username']) ?></td>
                  <td class="text-center">
                    <?php if ($has2fa): ?>
                      <svg class="icon-2fa-enabled" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <?php else: ?>
                      <svg class="icon-2fa-disabled" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="/admin/servers?owner_id=<?= e($u['id']) ?>" class="link-servers-count">
                      <?= intval($u['server_count'] ?? 0) ?>
                    </a>
                  </td>
                  <td class="text-center" style="font-family: var(--font-mono); color: #94a3b8;">
                    <?= intval($u['subuser_of_count'] ?? 0) ?>
                  </td>
                  <td class="text-center">
                    <?php if (!empty($u['last_login_at'])): ?>
                      <span title="<?= e($u['last_login_at']) ?>"><?= timeAgoUser($u['last_login_at']) ?></span>
                      <?php if (!empty($u['last_login_ip'])): ?>
                        <br><small style="color: #64748b; font-size: 0.75rem;"><?= e($u['last_login_ip']) ?></small>
                      <?php endif; ?>
                    <?php else: ?>
                      <span style="color: #64748b;">Never</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <img src="<?= e($avatarUrl) ?>" alt="Avatar" class="gravatar-circle">
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
