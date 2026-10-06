<?php
$title = 'List Servers — Admin';
$breadcrumb = 'Admin / Servers';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_admin.php';

$servers = $servers ?? [];
$nodes = $nodes ?? [];
$filters = $filters ?? [];
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
  margin-bottom: 1.75rem;
  overflow: hidden;
}

.mass-actions-box {
  border-top-color: #0284c7;
}

.mass-actions-header {
  background: #0369a1;
  color: #ffffff;
  padding: 0.85rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.mass-actions-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.selected-counter {
  font-size: 0.75rem;
  font-weight: 600;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.2rem 0.65rem;
  border-radius: 4px;
}

.mass-actions-body {
  padding: 1.25rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}

.ma-col h4 {
  margin: 0 0 0.35rem 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.ma-col p {
  font-size: 0.775rem;
  color: #94a3b8;
  margin-bottom: 0.75rem;
  line-height: 1.4;
}

.btn-ma-action {
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.45rem 0.85rem;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: all 0.2s;
}

.btn-ma-action:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.filter-toolbar {
  padding: 0.85rem 1.25rem;
  background: rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.filter-form {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-input, .filter-select {
  background: #182030;
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #ffffff;
  padding: 0.42rem 0.75rem;
  font-size: 0.825rem;
  border-radius: 5px;
  outline: none;
}

.filter-input:focus, .filter-select:focus {
  border-color: #38bdf8;
}

.btn-filter-search {
  background: #1e293b;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #e2e8f0;
  padding: 0.42rem 0.85rem;
  border-radius: 5px;
  font-size: 0.825rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}
.btn-filter-search:hover { background: #334155; }

.btn-create-server {
  background: #2563eb;
  color: #ffffff;
  font-size: 0.825rem;
  font-weight: 600;
  padding: 0.45rem 1rem;
  border-radius: 6px;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  border: none;
  cursor: pointer;
  transition: background 0.2s;
}
.btn-create-server:hover { background: #1d4ed8; }

.table-servers {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.table-servers th {
  background: #0d121c;
  color: #94a3b8;
  font-weight: 600;
  text-align: left;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}

.table-servers th.text-center, .table-servers td.text-center {
  text-align: center;
}

.table-servers td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  color: #cbd5e1;
  vertical-align: middle;
}

.table-servers tbody tr:hover {
  background: rgba(255, 255, 255, 0.025);
}

.label-srv {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}
.label-srv-active { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.label-srv-suspended { background: rgba(159, 18, 57, 0.3); color: #fb7185; }
.label-srv-installing { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
.label-srv-offline { background: rgba(100, 116, 139, 0.2); color: #94a3b8; }
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 2rem;">
    <!-- Content Header -->
    <div class="admin-content-header">
      <div class="admin-header-title">
        <h1>Servers <small>All servers available on the system.</small></h1>
      </div>
      <ol class="admin-breadcrumbs">
        <li><a href="/admin">Admin</a></li>
        <li>/</li>
        <li style="color: #94a3b8;">Servers</li>
      </ol>
    </div>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success" style="margin-bottom: 1.25rem;"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" style="margin-bottom: 1.25rem;"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- MASS ACTIONS SECTION -->
    <div class="admin-box mass-actions-box">
      <div class="mass-actions-header">
        <h3>
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
          Mass Actions
        </h3>
        <span id="selectedCountBadge" class="selected-counter">0 servers selected</span>
      </div>
      <div class="mass-actions-body">
        <!-- 1. Selection helpers -->
        <div class="ma-col">
          <h4>Selection</h4>
          <p>Use checkboxes in the table below or the shortcuts here.</p>
          <div style="display: flex; gap: 0.4rem;">
            <button type="button" class="btn-filter-search" onclick="toggleSelectAll(true)">Select All</button>
            <button type="button" class="btn-filter-search" onclick="toggleSelectAll(false)">Deselect</button>
          </div>
        </div>

        <!-- 2. Suspension -->
        <div class="ma-col">
          <h4>Suspension</h4>
          <p>Toggle suspension status on selected servers.</p>
          <div style="display: flex; gap: 0.4rem;">
            <button type="button" id="btnBulkSuspend" class="btn-ma-action" style="background: #f59e0b; color: #000;" onclick="submitBulkAction('suspend')" disabled>
              Suspend
            </button>
            <button type="button" id="btnBulkUnsuspend" class="btn-ma-action" style="background: #10b981; color: #fff;" onclick="submitBulkAction('unsuspend')" disabled>
              Unsuspend
            </button>
          </div>
        </div>

        <!-- 3. Delete -->
        <div class="ma-col">
          <h4>Delete</h4>
          <p>Permanently delete selected servers.</p>
          <div style="margin-bottom: 0.4rem;">
            <label style="font-size: 0.75rem; color: #94a3b8; cursor: pointer;">
              <input type="checkbox" id="forceDeleteCheckbox" style="accent-color: #ef4444;"> Force delete
            </label>
          </div>
          <button type="button" id="btnBulkDelete" class="btn-ma-action" style="background: #ef4444; color: #fff;" onclick="submitBulkAction('delete')" disabled>
            Delete Selected
          </button>
        </div>

        <!-- 4. Transfer to Node -->
        <div class="ma-col">
          <h4>Transfer to Node</h4>
          <p>Move selected servers to a different node. Allocations are assigned automatically.</p>
          <div style="display: flex; flex-direction: column; gap: 0.4rem;">
            <select id="transferNodeSelect" class="filter-select" style="width: 100%;">
              <option value="">— Select destination node —</option>
              <?php foreach ($nodes as $n): ?>
                <option value="<?= e($n['id']) ?>"><?= e($n['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" id="btnBulkTransfer" class="btn-ma-action" style="background: #2563eb; color: #fff;" onclick="submitBulkAction('transfer')" disabled>
              Transfer Selected
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- SERVER LIST BOX -->
    <div class="admin-box">
      <!-- Filter Toolbar -->
      <div class="filter-toolbar">
        <form action="/admin/servers" method="GET" class="filter-form">
          <input type="text" name="search" class="filter-input" placeholder="Search..." value="<?= e($filters['search'] ?? '') ?>">

          <select name="node_id" class="filter-select">
            <option value="">All nodes</option>
            <?php foreach ($nodes as $n): ?>
              <option value="<?= e($n['id']) ?>" <?= ($filters['node_id'] ?? '') == $n['id'] ? 'selected' : '' ?>>
                <?= e($n['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="status" class="filter-select">
            <option value="">Any status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
            <option value="offline" <?= ($filters['status'] ?? '') === 'offline' ? 'selected' : '' ?>>Offline</option>
          </select>

          <button type="submit" class="btn-filter-search">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Filter
          </button>
        </form>

        <a href="/admin/servers/new" class="btn-create-server">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
          Create New
        </a>
      </div>

      <!-- Server Table -->
      <div class="table-responsive">
        <table class="table-servers">
          <thead>
            <tr>
              <th style="width: 38px; text-align: center;">
                <input type="checkbox" id="masterCheckbox" onclick="toggleSelectAll(this.checked)" style="accent-color: #38bdf8;">
              </th>
              <th>Server Name</th>
              <th>UUID</th>
              <th>Owner</th>
              <th>Node</th>
              <th>Connection</th>
              <th>Type</th>
              <th class="text-center">Status</th>
              <th class="text-center" style="width: 70px;">Manage</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($servers)): ?>
              <tr>
                <td colspan="9" style="text-align: center; color: #64748b; padding: 3rem;">
                  No servers currently match your search criteria.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($servers as $s): ?>
                <?php
                  $isSuspended = ($s['status'] ?? '') === 'suspended';
                  $isInstalling = ($s['status'] ?? '') === 'installing';
                  $isRunning = ($s['status'] ?? '') === 'running';
                ?>
                <tr>
                  <td class="text-center">
                    <input type="checkbox" 
                           class="srv-checkbox" 
                           value="<?= e($s['id']) ?>" 
                           onchange="updateSelectionState()" 
                           style="accent-color: #38bdf8;">
                  </td>
                  <td>
                    <a href="/admin/servers/view/<?= e($s['id']) ?>" style="color: #38bdf8; font-weight: 600; text-decoration: none;">
                      <?= e($s['name']) ?>
                    </a>
                  </td>
                  <td>
                    <code style="background: #090d16; border: 1px solid rgba(255,255,255,0.08); padding: 0.15rem 0.35rem; border-radius: 4px; font-size: 0.75rem;">
                      <?= e(substr($s['uuid'] ?? $s['id'], 0, 8)) ?>
                    </code>
                  </td>
                  <td>
                    <a href="/admin/users/<?= e($s['owner_id']) ?>" style="color: #cbd5e1; text-decoration: none;">
                      <?= e($s['owner_username'] ?? 'User #' . $s['owner_id']) ?>
                    </a>
                  </td>
                  <td><?= e($s['node_name'] ?? 'Local') ?></td>
                  <td>
                    <code><?= e(!empty($s['ip_alias']) ? $s['ip_alias'] : '0.0.0.0') ?>:<?= e($s['port'] ?? '25501') ?></code>
                  </td>
                  <td>
                    <span class="label-srv label-srv-offline">Standard</span>
                  </td>
                  <td class="text-center">
                    <?php if ($isSuspended): ?>
                      <span class="label-srv label-srv-suspended">Suspended</span>
                    <?php elseif ($isInstalling): ?>
                      <span class="label-srv label-srv-installing">Installing</span>
                    <?php elseif ($isRunning): ?>
                      <span class="label-srv label-srv-active">Active</span>
                    <?php else: ?>
                      <span class="label-srv label-srv-offline"><?= ucfirst($s['status'] ?? 'Offline') ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <a href="/admin/servers/view/<?= e($s['id']) ?>" class="btn-filter-search" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; text-decoration: none;" title="Manage Server">
                      ⚙️
                    </a>
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

<!-- Bulk Action Hidden Form -->
<form id="bulkActionForm" action="/admin/servers/bulk" method="POST" style="display: none;">
  <input type="hidden" name="action" id="bulkActionInput">
  <input type="hidden" name="force" id="bulkForceInput" value="0">
  <input type="hidden" name="target_node_id" id="bulkNodeInput">
  <div id="bulkServerIdsContainer"></div>
</form>

<script>
function getSelectedServerIds() {
  const checkboxes = document.querySelectorAll('.srv-checkbox:checked');
  return Array.from(checkboxes).map(cb => cb.value);
}

function updateSelectionState() {
  const selected = getSelectedServerIds();
  const count = selected.length;
  document.getElementById('selectedCountBadge').textContent = `${count} servers selected`;
  
  const disabled = count === 0;
  document.getElementById('btnBulkSuspend').disabled = disabled;
  document.getElementById('btnBulkUnsuspend').disabled = disabled;
  document.getElementById('btnBulkDelete').disabled = disabled;
  document.getElementById('btnBulkTransfer').disabled = disabled;
}

function toggleSelectAll(checked) {
  const checkboxes = document.querySelectorAll('.srv-checkbox');
  checkboxes.forEach(cb => cb.checked = checked);
  const master = document.getElementById('masterCheckbox');
  if (master) master.checked = checked;
  updateSelectionState();
}

function submitBulkAction(action) {
  const selected = getSelectedServerIds();
  if (selected.length === 0) return;

  if (action === 'delete') {
    if (!confirm(`Are you sure you want to permanently delete ${selected.length} servers? This cannot be undone.`)) {
      return;
    }
  }

  const form = document.getElementById('bulkActionForm');
  document.getElementById('bulkActionInput').value = action;
  document.getElementById('bulkForceInput').value = document.getElementById('forceDeleteCheckbox').checked ? '1' : '0';
  document.getElementById('bulkNodeInput').value = document.getElementById('transferNodeSelect').value;

  const container = document.getElementById('bulkServerIdsContainer');
  container.innerHTML = '';
  selected.forEach(id => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'server_ids[]';
    input.value = id;
    container.appendChild(input);
  });

  form.submit();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
