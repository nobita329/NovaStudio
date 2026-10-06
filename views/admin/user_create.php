<?php
$title = 'Create User — Admin';
$breadcrumb = 'Admin / Users / Create';
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
$old = $old ?? [];
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
  border-radius: 0 0 8px 8px;
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

.help-text {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 0.35rem;
  line-height: 1.4;
}

.alert-info-box {
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.25);
  border-radius: 6px;
  color: #38bdf8;
  padding: 0.85rem 1rem;
  font-size: 0.825rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.btn-success-sm {
  background: #10b981;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.55rem 1.25rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-success-sm:hover {
  background: #059669;
}

.btn-gen-pass {
  background: #1e293b;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #38bdf8;
  font-size: 0.775rem;
  font-weight: 600;
  padding: 0.35rem 0.75rem;
  border-radius: 5px;
  cursor: pointer;
  margin-bottom: 0.5rem;
  transition: background 0.2s;
}
.btn-gen-pass:hover {
  background: #334155;
}

.pass-preview-pill {
  display: none;
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34d399;
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
  font-family: var(--font-mono);
  font-size: 0.825rem;
  margin-bottom: 0.75rem;
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>Create User <small>Add a new user to the system.</small></h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li><a href="/admin/users">Users</a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Create</li>
      </ol>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/users/new">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; align-items: start;">
        <!-- Left Column: Identity -->
        <div>
          <div class="admin-box">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Identity</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label for="email" class="control-label">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       autocomplete="off" 
                       value="<?= e($old['email'] ?? '') ?>" 
                       class="form-control" 
                       required>
              </div>

              <div class="form-group">
                <label for="username" class="control-label">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       autocomplete="off" 
                       value="<?= e($old['username'] ?? '') ?>" 
                       class="form-control" 
                       required>
              </div>

              <div class="form-group">
                <label for="name_first" class="control-label">Client First Name</label>
                <input type="text" 
                       id="name_first" 
                       name="name_first" 
                       autocomplete="off" 
                       value="<?= e($old['name_first'] ?? '') ?>" 
                       class="form-control" 
                       required>
              </div>

              <div class="form-group">
                <label for="name_last" class="control-label">Client Last Name</label>
                <input type="text" 
                       id="name_last" 
                       name="name_last" 
                       autocomplete="off" 
                       value="<?= e($old['name_last'] ?? '') ?>" 
                       class="form-control" 
                       required>
              </div>

              <div class="form-group">
                <label for="language" class="control-label">Default Language</label>
                <select name="language" id="language" class="form-control">
                  <?php foreach ($languages as $key => $name): ?>
                    <option value="<?= e($key) ?>" <?= ($old['language'] ?? 'en') === $key ? 'selected' : '' ?>>
                      <?= e($name) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <p class="help-text">The default language to use when rendering the Panel for this user.</p>
              </div>
            </div>
            <div class="admin-box-footer">
              <button type="submit" class="btn-success-sm">Create User</button>
            </div>
          </div>
        </div>

        <!-- Right Column: Permissions & Password -->
        <div>
          <!-- Box: Permissions -->
          <div class="admin-box" style="border-top-color: #64748b;">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Permissions</h3>
            </div>
            <div class="admin-box-body">
              <div class="form-group">
                <label for="root_admin" class="control-label">Administrator</label>
                <select name="root_admin" id="root_admin" class="form-control">
                  <option value="0" <?= empty($old['root_admin']) ? 'selected' : '' ?>>No</option>
                  <option value="1" <?= !empty($old['root_admin']) ? 'selected' : '' ?>>Yes</option>
                </select>
                <p class="help-text">Setting this to 'Yes' gives a user full administrative access.</p>
              </div>
            </div>
          </div>

          <!-- Box: Password -->
          <div class="admin-box" style="border-top-color: #64748b;">
            <div class="admin-box-header">
              <h3 class="admin-box-title">Password</h3>
            </div>
            <div class="admin-box-body">
              <div class="alert-info-box">
                Providing a user password is optional. New user emails prompt users to create a password the first time they login. If a password is provided here you will need to find a different method of providing it to the user.
              </div>

              <div id="genPassAlert" class="pass-preview-pill"></div>

              <button type="button" class="btn-gen-pass" id="gen_pass_bttn" onclick="generateRandomPass()">
                ⚡ Generate Random Password
              </button>

              <div class="form-group">
                <label for="pass" class="control-label">Password</label>
                <input type="password" id="pass" name="password" class="form-control" autocomplete="new-password">
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </main>
</div>

<script>
function generateRandomPass() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+';
  let password = '';
  for (let i = 0; i < 16; i++) {
    password += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  const passInput = document.getElementById('pass');
  const alertBox = document.getElementById('genPassAlert');
  if (passInput) passInput.value = password;
  if (alertBox) {
    alertBox.innerHTML = '<strong>Generated Password:</strong> ' + password;
    alertBox.style.display = 'block';
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
