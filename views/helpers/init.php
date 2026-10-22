<?php
// Nova Panel PHP Helper & Context Initializer
if (!isset($nova)) {
    $nova = [];
}

// Extract variables into current scope
foreach ($nova as $key => $value) {
    if (!isset($$key)) {
        $$key = $value;
    }
}

// Helpers
if (!function_exists('e')) {
    function e($val) {
        return htmlspecialchars((string)($val ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('bytesToSize')) {
    function bytesToSize($bytes) {
        if (!$bytes || $bytes <= 0) return '0 B';
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge($status) {
        $status = strtolower($status ?? 'offline');
        $classes = [
            'online' => 'badge-online',
            'offline' => 'badge-offline',
            'starting' => 'badge-starting',
            'stopping' => 'badge-stopping',
            'installing' => 'badge-installing',
            'suspended' => 'badge-suspended',
            'active' => 'badge-online',
            'disabled' => 'badge-offline'
        ];
        $cls = $classes[$status] ?? 'badge-offline';
        return '<span class="status-badge ' . $cls . '"><span class="badge-dot"></span>' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('route')) {
    function route($name, $params = null) {
        $routes = [
            'admin.index' => '/admin',
            'admin.dashboard' => '/admin',
            'admin.users' => '/admin/users',
            'admin.users.view' => '/admin/users/view/' . ($params ?? ''),
            'admin.servers' => '/admin/servers',
            'admin.servers.view' => '/admin/servers/view/' . ($params ?? ''),
            'admin.nodes' => '/admin/nodes',
            'admin.nodes.view' => '/admin/nodes/view/' . ($params ?? ''),
            'admin.statistics.agent-endpoints' => '/admin/statistics/agent-endpoints',
            'admin.statistics.live' => '/admin/statistics/live',
        ];
        if (isset($routes[$name])) {
            return $routes[$name];
        }
        $url = '/' . str_replace('.', '/', $name);
        if ($params !== null && $params !== '') {
            $url .= '/' . (is_array($params) ? implode('/', $params) : $params);
        }
        return $url;
    }
}
