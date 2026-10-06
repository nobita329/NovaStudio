<?php
$currentSubTab = $currentSubTab ?? 'details';
$srvId = $server['id'] ?? '';
?>
<style>
.admin-server-nav {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  background: #111520;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  padding: 0.35rem 0.5rem;
  margin-bottom: 1.5rem;
  overflow-x: auto;
}

.admin-server-nav a {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.85rem;
  font-size: 0.825rem;
  font-weight: 600;
  color: #94a3b8;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.admin-server-nav a:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

.admin-server-nav a.active {
  color: #ffffff;
  background: #2563eb;
}

.admin-server-nav a.tab-danger:hover {
  color: #f87171;
  background: rgba(239, 68, 68, 0.15);
}

.admin-server-nav a.tab-danger.active {
  background: #dc2626;
  color: #ffffff;
}

.admin-server-nav .tab-external {
  margin-left: auto;
  color: #38bdf8;
}
.admin-server-nav .tab-external:hover {
  background: rgba(56, 189, 248, 0.15);
}
</style>

<div class="admin-server-nav">
  <a href="/admin/servers/view/<?= e($srvId) ?>/details" class="<?= $currentSubTab === 'details' ? 'active' : '' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    Details
  </a>
  <a href="/admin/servers/view/<?= e($srvId) ?>/build" class="<?= $currentSubTab === 'build' ? 'active' : '' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
    Build Configuration
  </a>
  <a href="/admin/servers/view/<?= e($srvId) ?>/manage" class="<?= $currentSubTab === 'manage' ? 'active' : '' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
    Manage
  </a>
  <a href="/admin/servers/view/<?= e($srvId) ?>/delete" class="tab-danger <?= $currentSubTab === 'delete' ? 'active' : '' ?>">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
    Delete
  </a>
  <a href="/server/<?= e($srvId) ?>/console" target="_blank" class="tab-external" title="Open Client Console in New Tab">
    <span>Client Console</span>
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
  </a>
</div>
