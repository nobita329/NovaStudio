<?php
$title = 'Files — ' . $server['name'];
$breadcrumb = $server['name'] . ' / File Manager';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';

$currentDir = $currentDir ?? '/';
$pathParts = array_filter(explode('/', trim($currentDir, '/')));
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">File Manager</h1>
        <p class="page-description">Inspect, modify, and transfer files and directories within your container filesystem.</p>
      </div>

      <!-- Action Buttons Toolbar -->
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.Nova.openModal('modal-new-file')">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          New File
        </button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.Nova.openModal('modal-new-folder')">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
          New Folder
        </button>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('file-upload-input').click()">
          <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
          Upload
        </button>
        <!-- Hidden file upload form -->
        <form id="upload-form" action="/server/<?= e($server['id']) ?>/files/upload?dir=<?= urlencode($currentDir) ?>" method="POST" enctype="multipart/form-data" style="display:none;">
          <input type="file" id="file-upload-input" name="file" onchange="document.getElementById('upload-form').submit()">
        </form>
      </div>
    </div>

    <!-- Directory Path Navigation Bar -->
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 0.65rem 1rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.45rem; font-family: var(--font-mono); font-size: 0.85rem; overflow-x: auto;">
      <a href="/server/<?= e($server['id']) ?>/files?dir=/" style="color: var(--accent-blue); display: flex; align-items: center; gap: 0.35rem; font-weight: 600;">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        /home/container
      </a>
      <?php
      $accumulated = '';
      foreach ($pathParts as $part):
        $accumulated .= '/' . $part;
      ?>
        <span style="color: var(--text-dim);">/</span>
        <a href="/server/<?= e($server['id']) ?>/files?dir=<?= urlencode($accumulated) ?>" style="color: var(--text-main); font-weight: 500;">
          <?= e($part) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Files Table -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Size</th>
            <th>Last Modified</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($currentDir !== '/' && $currentDir !== ''): ?>
            <?php
            $parentDir = dirname($currentDir);
            if ($parentDir === '.') $parentDir = '/';
            ?>
            <tr>
              <td colspan="4" style="background: rgba(255, 255, 255, 0.015);">
                <a href="/server/<?= e($server['id']) ?>/files?dir=<?= urlencode($parentDir) ?>" style="display: flex; align-items: center; gap: 0.5rem; color: var(--accent-blue); font-weight: 600; font-size: 0.875rem;">
                  <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                  .. (Up one directory)
                </a>
              </td>
            </tr>
          <?php endif; ?>

          <?php if (empty($items)): ?>
            <tr>
              <td colspan="4">
                <div class="nova-empty-state" style="padding: 3rem 1rem;">
                  <div class="nova-empty-icon" style="width: 52px; height: 52px; margin-bottom: 0.85rem;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                  </div>
                  <div class="nova-empty-title" style="font-size: 1rem;">This directory is empty</div>
                  <div class="nova-empty-desc" style="font-size: 0.825rem; margin-bottom: 1rem;">Upload files or create folders to get started.</div>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($items as $item): ?>
              <tr>
                <td>
                  <?php if ($item['isDirectory']): ?>
                    <a href="/server/<?= e($server['id']) ?>/files?dir=<?= urlencode(rtrim($currentDir, '/') . '/' . $item['name']) ?>" style="display: flex; align-items: center; gap: 0.65rem; color: #fff; font-weight: 600;">
                      <svg width="19" height="19" fill="#f59e0b" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                      <span><?= e($item['name']) ?></span>
                    </a>
                  <?php else: ?>
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                      <svg width="18" height="18" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                      <span style="color: var(--text-main); font-weight: 500; cursor: pointer; transition: color 0.15s ease;" onmouseover="this.style.color='#38bdf8'" onmouseout="this.style.color='var(--text-main)'" onclick="openEditor('<?= e(rtrim($currentDir, '/') . '/' . $item['name']) ?>')">
                        <?= e($item['name']) ?>
                      </span>
                    </div>
                  <?php endif; ?>
                </td>
                <td style="font-family: var(--font-mono); color: var(--text-muted); font-size: 0.825rem;">
                  <?= is_numeric($item['size']) ? bytesToSize($item['size']) : '-' ?>
                </td>
                <td style="color: var(--text-dim); font-size: 0.825rem;">
                  <?= date('M d, Y H:i', strtotime($item['updatedAt'])) ?>
                </td>
                <td style="text-align: right;">
                  <div style="display: inline-flex; gap: 0.35rem;">
                    <?php if (!$item['isDirectory']): ?>
                      <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.55rem;" onclick="openEditor('<?= e(rtrim($currentDir, '/') . '/' . $item['name']) ?>')" title="Edit text file">
                        Edit
                      </button>
                      <a href="/server/<?= e($server['id']) ?>/files/download?file=<?= urlencode(rtrim($currentDir, '/') . '/' . $item['name']) ?>" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.55rem;" title="Download file">
                        Download
                      </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary btn-sm" style="padding: 0.25rem 0.55rem; color: var(--accent-danger);" onclick="deleteItem('<?= e($item['name']) ?>')" title="Delete item">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Code Editor Modal -->
    <div id="modal-editor" class="nova-modal-overlay">
      <div class="nova-modal-dialog" style="max-width: 960px; height: 82vh;">
        <div class="nova-modal-header">
          <div style="display: flex; align-items: center; gap: 0.6rem;">
            <svg width="18" height="18" fill="none" stroke="var(--accent-blue)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            <span id="editor-filename" style="font-family: var(--font-mono); font-weight: 600; color: #fff; font-size: 0.95rem;"></span>
          </div>
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveFileContent()">
              <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
              Save Changes
            </button>
            <button type="button" class="nova-modal-close" onclick="window.Nova.closeModal('modal-editor')">
              <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
        </div>
        <textarea id="editor-textarea" style="flex: 1; width: 100%; background: #070a10; color: #f1f5f9; font-family: var(--font-mono); font-size: 0.875rem; padding: 1.25rem; border: none; outline: none; resize: none; line-height: 1.5;"></textarea>
      </div>
    </div>

    <!-- New File Modal -->
    <div id="modal-new-file" class="nova-modal-overlay">
      <div class="nova-modal-dialog" style="max-width: 440px;">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Create New File
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/files/create" method="POST">
          <div class="nova-modal-body">
            <input type="hidden" name="dir" value="<?= e($currentDir) ?>">
            <input type="hidden" name="is_directory" value="0">
            <div class="form-group">
              <label class="form-label">File Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. server.properties, config.yml" required autofocus>
              <div class="form-text">File will be created inside: <?= e($currentDir) ?></div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create File</button>
          </div>
        </form>
      </div>
    </div>

    <!-- New Folder Modal -->
    <div id="modal-new-folder" class="nova-modal-overlay">
      <div class="nova-modal-dialog" style="max-width: 440px;">
        <div class="nova-modal-header">
          <h3 class="nova-modal-title">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
            Create New Folder
          </h3>
          <button type="button" class="nova-modal-close" data-modal-close aria-label="Close">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <form action="/server/<?= e($server['id']) ?>/files/create" method="POST">
          <div class="nova-modal-body">
            <input type="hidden" name="dir" value="<?= e($currentDir) ?>">
            <input type="hidden" name="is_directory" value="1">
            <div class="form-group">
              <label class="form-label">Folder Name</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. plugins, mods, backups" required autofocus>
              <div class="form-text">Folder will be created inside: <?= e($currentDir) ?></div>
            </div>
          </div>
          <div class="nova-modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-modal-close>Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create Folder</button>
          </div>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
let currentEditingPath = '';

async function openEditor(filePath) {
  currentEditingPath = filePath;
  try {
    const res = await fetch(`/server/<?= e($server['id']) ?>/files/content?file=${encodeURIComponent(filePath)}`);
    const data = await res.json();
    if (data.error) throw new Error(data.error);

    document.getElementById('editor-filename').textContent = data.fileName;
    document.getElementById('editor-textarea').value = data.content;
    window.Nova.openModal('modal-editor');
  } catch (err) {
    window.Nova.toast('Failed to load file: ' + err.message, 'error');
  }
}

async function saveFileContent() {
  const content = document.getElementById('editor-textarea').value;
  try {
    const res = await fetch(`/server/<?= e($server['id']) ?>/files/save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ file: currentEditingPath, content })
    });
    const data = await res.json();
    if (data.success) {
      window.Nova.toast('File saved successfully.', 'success');
    } else {
      throw new Error(data.error || 'Save error');
    }
  } catch (err) {
    window.Nova.toast('Error saving: ' + err.message, 'error');
  }
}

async function deleteItem(name) {
  if (!confirm(`Are you sure you want to delete "${name}"?`)) return;
  try {
    const res = await fetch(`/server/<?= e($server['id']) ?>/files/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ dir: '<?= e($currentDir) ?>', name })
    });
    const data = await res.json();
    if (data.success) {
      window.Nova.toast('Item deleted.', 'success');
      window.location.reload();
    } else {
      throw new Error(data.error || 'Failed to delete');
    }
  } catch (err) {
    window.Nova.toast('Failed to delete item.', 'error');
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
