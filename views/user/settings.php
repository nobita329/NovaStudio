<?php
$title = 'Settings — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Settings';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';

$sftpHost = !empty($sftp['host']) ? $sftp['host'] : '127.0.0.1';
$sftpPort = !empty($sftp['port']) ? $sftp['port'] : '3004';
$sftpUser = !empty($sftp['user']) ? $sftp['user'] : ($user['username'] . '.' . substr($server['uuid'] ?? $server['id'], 0, 8));
$sftpUrl = "sftp://{$sftpHost}:{$sftpPort}";
?>
<link rel="stylesheet" href="/assets/css/nova-console.css">

<style>
.settings-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 900px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}

.settings-card {
  background: rgb(var(--gray700));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
  margin-bottom: 1.5rem;
}

.settings-card-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: rgb(var(--gray200));
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1.25rem;
  font-family: var(--font-header);
}

.settings-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: rgb(var(--gray300));
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.45rem;
}

.settings-input {
  width: 100%;
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  color: var(--text-main);
  padding: 0.65rem 0.85rem;
  font-size: 0.875rem;
  font-family: var(--font-mono);
  outline: none;
  transition: var(--transition);
  margin-bottom: 1.15rem;
}

.settings-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}

.settings-textarea {
  width: 100%;
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  color: var(--text-main);
  padding: 0.65rem 0.85rem;
  font-size: 0.875rem;
  font-family: var(--font-sans);
  outline: none;
  min-height: 90px;
  resize: vertical;
  margin-bottom: 1.25rem;
  line-height: 1.5;
  transition: var(--transition);
}

.settings-textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}

.sftp-notice-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.25rem;
  gap: 1rem;
}

.sftp-notice-text {
  border-left: 2px solid var(--primary-border);
  padding-left: 0.75rem;
  font-size: 0.8rem;
  color: rgb(var(--gray300));
  line-height: 1.4;
  flex: 1;
}

.btn-launch-sftp {
  display: inline-flex;
  align-items: center;
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  transition: color 0.15s ease;
}

.btn-launch-sftp:hover {
  color: var(--primary-border);
}

.debug-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 0;
  border-bottom: 1px solid rgba(var(--gray600), 0.5);
}

.debug-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.debug-label {
  font-size: 0.825rem;
  color: rgb(var(--gray200));
  font-weight: 500;
}

.debug-pill-node {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  color: var(--text-main);
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.25rem 0.65rem;
  border-radius: var(--radiusInput);
}

.debug-pill-uuid {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  color: var(--text-main);
  font-family: var(--font-mono);
  font-size: 0.775rem;
  padding: 0.3rem 0.65rem;
  border-radius: var(--radiusInput);
  user-select: all;
  letter-spacing: -0.01em;
}

.reinstall-desc {
  font-size: 0.825rem;
  color: rgb(var(--gray300));
  line-height: 1.5;
  margin-bottom: 1.5rem;
}

.btn-blue-save {
  background: var(--primary);
  color: #ffffff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.6rem 1.6rem;
  border-radius: var(--radiusInput);
  border: none;
  cursor: pointer;
  transition: var(--transition);
}

.btn-blue-save:hover {
  background: var(--primary-hover);
  box-shadow: 0 4px 14px rgba(74, 53, 207, 0.4);
}

.btn-reinstall {
  background: rgb(var(--gray800));
  color: rgb(var(--gray100));
  border: 1px solid var(--border-color);
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.6rem 1.6rem;
  border-radius: var(--radiusInput);
  cursor: pointer;
  transition: var(--transition);
}

.btn-reinstall:hover {
  background: rgb(var(--gray600));
  border-color: rgba(var(--primary-rgb), 0.5);
  color: #ffffff;
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <div class="settings-grid">
      <!-- LEFT COLUMN -->
      <div>
        <!-- 1. SFTP DETAILS -->
        <div class="settings-card">
          <div class="settings-card-title">SFTP DETAILS</div>

          <label class="settings-label">SERVER ADDRESS</label>
          <input type="text" class="settings-input" value="<?= e($sftpUrl) ?>" readonly onclick="this.select();">

          <label class="settings-label">USERNAME</label>
          <input type="text" class="settings-input" value="<?= e($sftpUser) ?>" readonly onclick="this.select();">

          <div class="sftp-notice-bar">
            <div class="sftp-notice-text">
              Your SFTP password is the same as the password you use to access this panel.
            </div>
            <a href="sftp://<?= e($sftpUser) ?>@<?= e($sftpHost) ?>:<?= e($sftpPort) ?>" class="btn-launch-sftp">
              Launch SFTP
            </a>
          </div>
        </div>

        <!-- 2. DEBUG INFORMATION -->
        <div class="settings-card">
          <div class="settings-card-title">DEBUG INFORMATION</div>

          <div class="debug-row">
            <span class="debug-label">Node</span>
            <span class="debug-pill-node">Node - <?= e($node['id'] ?? '1') ?></span>
          </div>

          <div class="debug-row" style="margin-top: 0.5rem;">
            <span class="debug-label">Server ID</span>
            <span class="debug-pill-uuid"><?= e($server['uuid'] ?? $server['id']) ?></span>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div>
        <!-- 3. CHANGE SERVER DETAILS -->
        <div class="settings-card">
          <div class="settings-card-title">CHANGE SERVER DETAILS</div>

          <form action="/server/<?= e($server['id']) ?>/settings" method="POST">
            <label class="settings-label">SERVER NAME</label>
            <input type="text" name="name" class="settings-input" value="<?= e($server['name']) ?>" required>

            <label class="settings-label">SERVER DESCRIPTION</label>
            <textarea name="description" class="settings-textarea" placeholder="Optional description for your server instance..."><?= e($server['description']) ?></textarea>

            <div style="display: flex; justify-content: flex-end;">
              <button type="submit" class="btn-blue-save">Save</button>
            </div>
          </form>
        </div>

        <!-- 4. REINSTALL SERVER -->
        <div class="settings-card">
          <div class="settings-card-title">REINSTALL SERVER</div>

          <p class="reinstall-desc">
            Reinstalling your server will stop it, and then re-run the installation script that initially set it up. Some files may be deleted or modified during this process, please back up your data before continuing.
          </p>

          <div style="display: flex; justify-content: flex-end;">
            <form action="/server/<?= e($server['id']) ?>/reinstall" method="POST" onsubmit="return confirm('Are you sure you want to reinstall this server instance? Data in the server directory may be overwritten.');">
              <button type="submit" class="btn-reinstall">Reinstall Server</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
