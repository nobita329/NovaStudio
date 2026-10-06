<?php
$title = 'Profile';
$breadcrumb = 'Account / Profile';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Account Subnavigation Tabs -->
    <?php require __DIR__ . '/partials/account_tabs.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Profile Settings</h1>
        <p class="page-description">Manage personal account details and communication preferences.</p>
      </div>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start;">
      <!-- Left Column: User Summary Card -->
      <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(var(--primary-rgb, 74, 53, 207), 0.25); border: 2px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; font-weight: 700; color: #fff; margin: 0 auto 1rem auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
          <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
        </div>
        <h3 style="color: #fff; font-size: 1.2rem; font-weight: 700; margin-bottom: 0.25rem;"><?= e($user['username']) ?></h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;"><?= e($user['email']) ?></p>

        <div style="margin-bottom: 1.25rem;">
          <?php if (!empty($user['root_admin'])): ?>
            <span class="badge badge-primary" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">Root Administrator</span>
          <?php else: ?>
            <span class="badge badge-info" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">Client Account</span>
          <?php endif; ?>
        </div>

        <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; font-size: 0.775rem; color: var(--text-dim); text-align: left;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 0.4rem;">
            <span>Member Since:</span>
            <strong style="color: var(--text-main);"><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>2FA Security:</span>
            <strong style="color: <?= !empty($user['two_factor_enabled']) ? '#34d399' : '#f87171' ?>;">
              <?= !empty($user['two_factor_enabled']) ? 'Active' : 'Disabled' ?>
            </strong>
          </div>
        </div>
      </div>

      <!-- Right Column: Profile Form -->
      <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1.5rem;">
          <h3 class="card-title">Personal Information</h3>
        </div>

        <form action="/account" method="POST">
          <div class="form-group">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled style="cursor: not-allowed; opacity: 0.75;">
            <div class="form-text">Usernames are permanently linked to server instances and cannot be modified.</div>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control" value="<?= e($user['first_name']) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control" value="<?= e($user['last_name']) ?>" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
            <div class="form-text">Used for critical security alerts, subuser invitations, and password resets.</div>
          </div>

          <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              Save Profile
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
