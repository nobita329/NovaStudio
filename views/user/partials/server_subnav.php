<?php
$srvId = $server['id'] ?? '';
$curPath = $currentPath ?? '';
?>
<div class="server-subnav-bar">
  <a href="/server/<?= e($srvId) ?>/console" class="server-subnav-tab <?= strpos($curPath, '/console') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
    <span>Console</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/files" class="server-subnav-tab <?= strpos($curPath, '/files') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
    <span>Files</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/databases" class="server-subnav-tab <?= strpos($curPath, '/databases') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5-1 3.5-3M4 7c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3m-16 5c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3"></path></svg>
    <span>Databases</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/schedules" class="server-subnav-tab <?= strpos($curPath, '/schedules') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <span>Schedules</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/users" class="server-subnav-tab <?= strpos($curPath, '/users') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
    <span>Users</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/backups" class="server-subnav-tab <?= strpos($curPath, '/backups') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
    <span>Backups</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/network" class="server-subnav-tab <?= strpos($curPath, '/network') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
    <span>Network</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/startup" class="server-subnav-tab <?= strpos($curPath, '/startup') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
    <span>Startup</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/settings" class="server-subnav-tab <?= strpos($curPath, '/settings') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
    <span>Settings</span>
  </a>

  <a href="/server/<?= e($srvId) ?>/activity" class="server-subnav-tab <?= strpos($curPath, '/activity') !== false ? 'active' : '' ?>">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <span>Activity</span>
  </a>
</div>
