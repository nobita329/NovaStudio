<?php
$title = 'Network — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Network';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';
?>
<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <div class="page-header" style="margin-bottom: 1.25rem;">
      <div>
        <h1 class="page-title">Network & Allocations</h1>
        <p class="page-description">IP addresses and port bindings assigned to this server instance.</p>
      </div>
    </div>

    <div class="table-responsive" style="margin-bottom: 1.5rem;">
      <table class="table">
        <thead>
          <tr>
            <th>IP Address</th>
            <th>Port</th>
            <th>Type</th>
            <th>Connection Address</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($allocations ?? []) as $a): 
            $isPrimary = ($a['id'] == $server['allocation_id']) || !empty($a['is_primary']);
            $connStr = $a['ip_address'] . ':' . $a['port'];
          ?>
            <tr>
              <td style="font-family: var(--font-mono); color: var(--text-main); font-weight: 500;">
                <?= e($a['ip_address']) ?>
              </td>
              <td>
                <span style="font-family: var(--font-mono); color: var(--accent-blue); font-weight: 700; background: rgba(56, 189, 248, 0.1); padding: 0.2rem 0.55rem; border-radius: 4px; border: 1px solid rgba(56, 189, 248, 0.2);">
                  <?= e($a['port']) ?>
                </span>
              </td>
              <td>
                <?php if ($isPrimary): ?>
                  <span class="status-badge badge-online">
                    <span class="badge-dot"></span>Primary Allocation
                  </span>
                <?php else: ?>
                  <span class="status-badge badge-suspended">Secondary</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="server-subpage-alloc" onclick="window.arixCopy && window.arixCopy('<?= e($connStr) ?>')" title="Click to copy">
                  <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                  <span><?= e($connStr) ?></span>
                </div>
              </td>
              <td style="color: var(--text-muted); font-size: 0.85rem;">
                <?= e($a['notes'] ?: ($isPrimary ? 'Default game / application port' : 'Additional port')) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Helpful Connection Info Card -->
    <div class="card" style="border-left: 4px solid var(--accent-blue);">
      <div style="display: flex; gap: 1rem; align-items: flex-start;">
        <svg width="24" height="24" fill="none" stroke="var(--accent-blue)" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 0.2rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
          <h4 style="color: #fff; font-size: 0.95rem; font-weight: 600; margin-bottom: 0.35rem;">Connecting to your server</h4>
          <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">
            To connect client software or players to your server, use the Primary Connection Address above. If you run plugins that require additional communication channels (such as query, vote listeners, or web maps), assign secondary allocations as needed.
          </p>
        </div>
      </div>
    </div>
  </main>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
