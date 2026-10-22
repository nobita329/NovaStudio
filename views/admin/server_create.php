<?php
/**
 * Nova Panel - Standalone Full-Page Server Deployment Application
 * File: server_create.php / deploy.php
 * 
 * Provides a dedicated, full-page interface for deploying game servers,
 * Node.js applications, Python bots, and game servers.
 * Connects directly to Nova's REST API and backend engine.
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// --- Configuration & Default Settings ---
$defaultPanelUrl = getenv('PANEL_URL') ?: (getenv('PANEL_URL') ?: 'http://localhost:3003');
$defaultApiKey = getenv('PANEL_API_KEY') ?: (getenv('PANEL_API_KEY') ?: '');

if (!isset($_SESSION['nova_url'])) {
    $_SESSION['nova_url'] = $defaultPanelUrl;
}
if (!isset($_SESSION['nova_token']) && $defaultApiKey) {
    $_SESSION['nova_token'] = $defaultApiKey;
}

// Auto-authenticate if user is already logged in as admin in Nova session
if (!empty($user) && empty($_SESSION['nova_user'])) {
    $_SESSION['nova_user'] = $user;
    if (empty($_SESSION['nova_token'])) {
        // Create internal token flag
        $_SESSION['nova_token'] = 'session_' . ($user['id'] ?? 1);
    }
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper: Make cURL Request to Nova API
function callNovaApi($endpoint, $method = 'GET', $data = null, $token = null, $baseUrl = null) {
    if (!$baseUrl) {
        $baseUrl = $_SESSION['nova_url'] ?? 'http://localhost:3003';
    }
    $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    if ($data !== null) {
        $jsonData = is_string($data) ? $data : json_encode($data);
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);
    
    if ($curlErr) {
        return ['success' => false, 'error' => 'Connection failed: ' . $curlErr];
    }
    
    $decoded = json_decode($response, true);
    if ($decoded === null && $httpCode >= 400) {
        return ['success' => false, 'error' => "HTTP $httpCode: Server error or invalid response."];
    }
    
    return $decoded ?: ['success' => false, 'error' => 'Empty or invalid JSON response'];
}

$notice = $notice ?? ($_GET['notice'] ?? null);
$error = $error ?? ($_GET['error'] ?? null);
$createdServer = $createdServer ?? null;

// --- Handle Authentication Actions ---
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid security token (CSRF). Please refresh and try again.';
    } elseif ($_POST['action'] === 'set_config') {
        $url = trim($_POST['nova_url'] ?? '');
        $token = trim($_POST['nova_token'] ?? '');
        if ($url) $_SESSION['nova_url'] = rtrim($url, '/');
        if ($token) $_SESSION['nova_token'] = $token;
        
        // Test connection
        $test = callNovaApi('/api/admin/settings/public', 'GET', null, null, $_SESSION['nova_url']);
        if (isset($test['success']) && $test['success']) {
            $notice = 'Successfully connected to Panel: ' . htmlspecialchars($test['settings']['panel_name'] ?? 'Nova');
        } else {
            $error = 'Connection test failed: ' . htmlspecialchars($test['error'] ?? 'Could not connect at ' . $url);
        }
    } elseif ($_POST['action'] === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $url = trim($_POST['nova_url'] ?? $_SESSION['nova_url']);
        if ($url) $_SESSION['nova_url'] = rtrim($url, '/');
        
        $loginRes = callNovaApi('/api/auth/login', 'POST', [
            'username' => $username,
            'password' => $password
        ], null, $_SESSION['nova_url']);
        
        if (!empty($loginRes['success']) && !empty($loginRes['token'])) {
            $_SESSION['nova_token'] = $loginRes['token'];
            $_SESSION['nova_user'] = $loginRes['user'] ?? null;
            $notice = 'Signed in successfully as ' . htmlspecialchars($loginRes['user']['username'] ?? 'Admin');
        } else {
            $error = 'Sign in failed: ' . htmlspecialchars($loginRes['error'] ?? 'Invalid credentials.');
        }
    } elseif ($_POST['action'] === 'logout') {
        unset($_SESSION['nova_token']);
        unset($_SESSION['nova_user']);
        $notice = 'Disconnected from Panel session.';
    } elseif ($_POST['action'] === 'deploy_server') {
        // --- Process Server Deployment ---
        $authToken = $_SESSION['nova_token'] ?? null;
        if (!$authToken) {
            $error = 'Authentication token is required to deploy a server.';
        } else {
            $srvName = trim($_POST['name'] ?? '');
            $srvType = trim($_POST['server_type'] ?? 'minecraft');
            $srvDesc = trim($_POST['description'] ?? '');
            
            $ramVal = intval($_POST['memory_val'] ?? 2048);
            $ramUnit = $_POST['memory_unit'] ?? 'MB';
            $memoryMb = ($ramUnit === 'GB') ? ($ramVal * 1024) : $ramVal;
            
            $cpuLimit = intval($_POST['cpu_limit'] ?? 100);
            
            $diskVal = intval($_POST['disk_val'] ?? 10);
            $diskUnit = $_POST['disk_unit'] ?? 'GB';
            $diskMb = ($diskUnit === 'GB') ? ($diskVal * 1024) : $diskVal;
            
            $userId = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
            $nodeId = !empty($_POST['node_id']) ? intval($_POST['node_id']) : 1;
            $allocId = !empty($_POST['allocation_id']) ? intval($_POST['allocation_id']) : null;
            
            $customDockerImage = trim($_POST['custom_docker_image'] ?? '');
            $dockerImage = $customDockerImage !== '' ? $customDockerImage : trim($_POST['docker_image'] ?? '');
            if ($srvType === 'nodejs' && empty($dockerImage)) {
                $dockerImage = 'ghcr.io/parkervcp/yolks:nodejs_20';
            } elseif ($srvType === 'python' && empty($dockerImage)) {
                $dockerImage = 'ghcr.io/parkervcp/yolks:python_3.12';
            }
            $startupCmd = trim($_POST['startup_cmd'] ?? '');
            
            // Engine specific options
            $mcJarType = trim($_POST['mc_jar_type'] ?? '');
            $mcJarVersion = trim($_POST['mc_jar_version'] ?? '');
            
            // Environment Variables JSON
            $envVars = [];
            if (!empty($_POST['env_keys']) && is_array($_POST['env_keys'])) {
                foreach ($_POST['env_keys'] as $idx => $key) {
                    $key = trim($key);
                    $val = trim($_POST['env_vals'][$idx] ?? '');
                    if ($key !== '') {
                        $envVars[$key] = $val;
                    }
                }
            }
            
            $payload = [
                'name' => $srvName,
                'description' => $srvDesc,
                'server_type' => $srvType,
                'memory_mb' => $memoryMb,
                'cpu_limit' => $cpuLimit,
                'disk_mb' => $diskMb,
                'node_id' => $nodeId,
                'allocation_id' => $allocId,
                'user_id' => $userId,
                'docker_image' => $dockerImage ?: null,
                'startup_cmd' => $startupCmd ?: null,
                'env_vars' => $envVars,
                'mc_jar_type' => $mcJarType ?: null,
                'mc_jar_version' => $mcJarVersion ?: null
            ];
            
            $res = callNovaApi('/api/servers', 'POST', $payload, $authToken);
            if (!empty($res['success'])) {
                $createdServer = [
                    'id' => $res['serverId'] ?? null,
                    'uuid' => $res['uuid'] ?? null,
                    'name' => $srvName,
                    'server_type' => $srvType,
                    'memory_mb' => $memoryMb,
                    'disk_mb' => $diskMb,
                    'cpu_limit' => $cpuLimit,
                    'console_url' => '/server/' . ($res['serverId'] ?? '') . '/console'
                ];
                $notice = 'Server instance deployed successfully!';
            } else {
                $error = 'Deployment failed: ' . htmlspecialchars($res['error'] ?? 'Unknown error occurred.');
            }
        }
    }
}

// Fetch Remote Data (Users, Nodes, Allocations) if token exists or local variables passed
$authToken = $_SESSION['nova_token'] ?? null;
$usersList = !empty($users) ? $users : [];
$nodesList = !empty($nodes) ? $nodes : [];
$freeAllocations = !empty($allocations) ? $allocations : [];
$publicSettings = !empty($settings) ? $settings : [];

if ($authToken && (empty($usersList) || empty($nodesList))) {
    // 1. Fetch public settings
    $settingsRes = callNovaApi('/api/admin/settings/public');
    if (!empty($settingsRes['settings'])) {
        $publicSettings = $settingsRes['settings'];
    }
    
    // 2. Fetch users list
    $uRes = callNovaApi('/api/admin/users', 'GET', null, $authToken);
    if (!empty($uRes['users'])) {
        $usersList = $uRes['users'];
    }
    
    // 3. Fetch nodes & allocations
    $nRes = callNovaApi('/api/admin/nodes', 'GET', null, $authToken);
    if (!empty($nRes['nodes'])) {
        $nodesList = $nRes['nodes'];
    }
    
    // 4. Fetch network allocations
    $netRes = callNovaApi('/api/admin/network', 'GET', null, $authToken);
    if (!empty($netRes['allocations'])) {
        $freeAllocations = array_filter($netRes['allocations'], function($a) {
            return empty($a['assigned']) || $a['assigned'] == 0;
        });
    }
}

$panelName = $publicSettings['panel_name'] ?? 'Nova';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Deploy New Server Instance &bull; <?= htmlspecialchars($panelName) ?></title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#ecfeff',
              400: '#22d3ee',
              500: '#06b6d4',
              600: '#0891b2',
              900: '#164e63'
            }
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            mono: ['JetBrains Mono', 'monospace']
          }
        }
      }
    }
  </script>
  
  <!-- Google Fonts & Lucide Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body {
      background: radial-gradient(circle at 10% 20%, rgba(15, 23, 42, 0.98) 0%, rgba(2, 6, 23, 1) 90%);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      color: #e2e8f0;
    }
    .glass-card {
      background: rgba(15, 23, 42, 0.75);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .glass-card:hover {
      border-color: rgba(6, 182, 212, 0.3);
    }
    .glass-input {
      background: rgba(2, 6, 23, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.12);
      color: #f8fafc;
      transition: all 0.2s ease;
    }
    .glass-input:focus {
      outline: none;
      border-color: #06b6d4;
      box-shadow: 0 0 15px rgba(6, 182, 212, 0.25);
    }
    .btn-cyber {
      background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
      color: #ffffff;
      font-weight: 700;
      transition: all 0.2s ease;
    }
    .btn-cyber:hover {
      box-shadow: 0 0 20px rgba(6, 182, 212, 0.45);
      transform: translateY(-1px);
    }
    .type-tab-active {
      background: rgba(6, 182, 212, 0.15) !important;
      border-color: #06b6d4 !important;
      color: #ffffff !important;
    }
  </style>
</head>
<body class="p-4 sm:p-6 lg:p-8">

  <!-- Top Navigation Bar -->
  <header class="max-w-6xl mx-auto mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 glass-card p-4 sm:p-5 rounded-2xl border border-white/10 shadow-2xl">
    <div class="flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-500 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20 shrink-0">
        <i data-lucide="server" class="w-6 h-6"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h1 class="text-lg font-black text-white"><?= htmlspecialchars($panelName) ?></h1>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
            Provisioning Suite
          </span>
        </div>
        <p class="text-xs text-slate-400">Deploy New Server Instance &bull; Full Page Provisioning Portal</p>
      </div>
    </div>

    <div class="flex items-center gap-2.5 flex-wrap self-end sm:self-center">
      <?php if (!empty($user)): ?>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-300 text-xs font-mono">
          <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
          <span>Admin: <?= htmlspecialchars($user['username'] ?? 'admin') ?></span>
        </div>
      <?php elseif ($authToken): ?>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-mono">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>Connected</span>
        </div>
        <form method="POST" class="inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 transition flex items-center gap-1.5">
            <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Disconnect
          </button>
        </form>
      <?php else: ?>
        <button onclick="document.getElementById('connection-modal').classList.remove('hidden')" class="btn-cyber px-4 py-1.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-md">
          <i data-lucide="key" class="w-3.5 h-3.5"></i> Connect / Authenticate
        </button>
      <?php endif; ?>
      <a href="/admin/servers" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition flex items-center gap-1.5">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-cyan-400"></i> Back to Servers
      </a>
      <a href="/dashboard" class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition flex items-center gap-1.5">
        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-cyan-400"></i> Dashboard
      </a>
    </div>
  </header>

  <main class="max-w-6xl mx-auto space-y-6">

    <!-- Notification Alert -->
    <?php if ($notice): ?>
      <div class="glass-card p-4 rounded-2xl border-emerald-500/40 bg-emerald-950/30 text-emerald-200 flex items-center gap-3 text-xs shadow-lg">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-400 shrink-0"></i>
        <span><?= htmlspecialchars($notice) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="glass-card p-4 rounded-2xl border-rose-500/40 bg-rose-950/30 text-rose-200 flex items-center gap-3 text-xs shadow-lg">
        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 shrink-0"></i>
        <span><?= htmlspecialchars($error) ?></span>
      </div>
    <?php endif; ?>

    <!-- Success Deployment Showcase -->
    <?php if ($createdServer): ?>
      <div class="glass-card p-6 sm:p-8 rounded-3xl border-cyan-500/50 bg-gradient-to-br from-cyan-950/40 via-slate-900/90 to-purple-950/40 shadow-2xl relative overflow-hidden space-y-5">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 flex items-center justify-center">
              <i data-lucide="sparkles" class="w-6 h-6"></i>
            </div>
            <div>
              <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400">Server Deployed Successfully</span>
              <h2 class="text-xl font-black text-white"><?= htmlspecialchars($createdServer['name']) ?></h2>
            </div>
          </div>
          <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
            ID #<?= htmlspecialchars($createdServer['id']) ?>
          </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs font-mono">
          <div class="bg-slate-900/80 p-3 rounded-xl border border-white/5">
            <span class="text-slate-400 text-[10px] block uppercase">Type</span>
            <span class="font-bold text-cyan-300"><?= htmlspecialchars(strtoupper($createdServer['server_type'])) ?></span>
          </div>
          <div class="bg-slate-900/80 p-3 rounded-xl border border-white/5">
            <span class="text-slate-400 text-[10px] block uppercase">Memory (RAM)</span>
            <span class="font-bold text-purple-300"><?= htmlspecialchars($createdServer['memory_mb']) ?> MB</span>
          </div>
          <div class="bg-slate-900/80 p-3 rounded-xl border border-white/5">
            <span class="text-slate-400 text-[10px] block uppercase">Disk Storage</span>
            <span class="font-bold text-amber-300"><?= htmlspecialchars($createdServer['disk_mb']) ?> MB</span>
          </div>
          <div class="bg-slate-900/80 p-3 rounded-xl border border-white/5">
            <span class="text-slate-400 text-[10px] block uppercase">UUID</span>
            <span class="font-bold text-slate-300 truncate block" title="<?= htmlspecialchars($createdServer['uuid']) ?>"><?= htmlspecialchars(substr($createdServer['uuid'] ?? '', 0, 13)) ?>...</span>
          </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
          <a href="<?= htmlspecialchars($createdServer['console_url']) ?>" class="btn-cyber px-6 py-2.5 rounded-xl text-xs font-bold flex items-center gap-2 shadow-lg">
            <i data-lucide="terminal" class="w-4 h-4"></i> Open Server Console
          </a>
          <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 transition">
            + Deploy Another Server
          </button>
        </div>
      </div>
    <?php endif; ?>

    <!-- Main Deployment Form -->
    <div class="glass-card p-6 sm:p-8 rounded-3xl border border-white/10 shadow-2xl">
      <form method="POST" action="/admin/servers/new" id="deploy-form" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="action" value="deploy_server">
        <input type="hidden" name="server_type" id="hidden_server_type" value="minecraft">

        <!-- 1. Server Engine / Type Selection Tabs -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center justify-between">
            <span>1. Choose Server Platform</span>
            <span class="text-[11px] text-cyan-400 font-mono" id="selected-type-badge">Selected: Minecraft</span>
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <!-- Minecraft -->
            <button type="button" onclick="setServerType('minecraft')" id="tab-minecraft" class="type-card type-tab-active p-3.5 rounded-2xl border border-white/10 glass-card flex flex-col items-center text-center gap-2 transition hover:scale-[1.02]">
              <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                <i data-lucide="box" class="w-5 h-5"></i>
              </div>
              <div>
                <span class="text-xs font-bold block text-white">Minecraft</span>
                <span class="text-[10px] text-slate-400">Paper, Purpur, Fabric, Forge</span>
              </div>
            </button>

            <!-- Node.js -->
            <button type="button" onclick="setServerType('nodejs')" id="tab-nodejs" class="type-card p-3.5 rounded-2xl border border-white/10 glass-card flex flex-col items-center text-center gap-2 transition hover:scale-[1.02]">
              <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center">
                <i data-lucide="cpu" class="w-5 h-5"></i>
              </div>
              <div>
                <span class="text-xs font-bold block text-white">Node.js</span>
                <span class="text-[10px] text-slate-400">v18 - v25 Apps & Bots</span>
              </div>
            </button>

            <!-- Python -->
            <button type="button" onclick="setServerType('python')" id="tab-python" class="type-card p-3.5 rounded-2xl border border-white/10 glass-card flex flex-col items-center text-center gap-2 transition hover:scale-[1.02]">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                <i data-lucide="layers" class="w-5 h-5"></i>
              </div>
              <div>
                <span class="text-xs font-bold block text-white">Python</span>
                <span class="text-[10px] text-slate-400">v3.8 - v3.13 Flask, FastAPI</span>
              </div>
            </button>
          </div>
        </div>

        <!-- 2. Server Identity & Description -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-semibold text-slate-300">Server Name</label>
              <button type="button" onclick="generateServerName()" class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                <i data-lucide="sparkles" class="w-3 h-3"></i> Auto Generate
              </button>
            </div>
            <input type="text" name="name" id="srv_name" required placeholder="e.g. Survival Craft SMP" class="w-full glass-input px-4 py-2.5 rounded-xl text-xs font-semibold">
          </div>

          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="text-xs font-semibold text-slate-300">Description</label>
              <button type="button" onclick="generateServerDesc()" class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                <i data-lucide="sparkles" class="w-3 h-3"></i> Auto Generate
              </button>
            </div>
            <input type="text" name="description" id="srv_desc" placeholder="e.g. High-performance gaming server" class="w-full glass-input px-4 py-2.5 rounded-xl text-xs">
          </div>
        </div>

        <!-- 3. Minecraft Platform Options (Visible when Minecraft is chosen) -->
        <div id="section-minecraft-options" class="glass-card p-5 rounded-2xl border border-cyan-500/20 bg-gradient-to-br from-cyan-950/20 to-slate-900/40 space-y-4">
          <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <h3 class="text-xs font-bold text-white flex items-center gap-2">
              <i data-lucide="cpu" class="w-4 h-4 text-cyan-400"></i> Minecraft Server Engine & Software Version
            </h3>
            <span class="text-[10px] font-mono text-cyan-400" id="java-match-badge">Automated Java Match</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-slate-300">Software Engine</label>
                <span class="text-[10px] font-mono text-cyan-400" id="engine-status-badge">Auto Engine</span>
              </div>
              <select name="mc_jar_type" id="mc_jar_type" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs" onchange="updateMinecraftVersions()">
                <optgroup label="Recommended Engines">
                  <option value="paper" selected>Paper (High Performance & Plugins)</option>
                  <option value="purpur">Purpur (Optimized Gameplay Tweaks)</option>
                  <option value="fabric">Fabric (Fast Lightweight Modding)</option>
                  <option value="forge">Forge (Classic Heavy Modpack Engine)</option>
                  <option value="neoforge">NeoForge (Modern Community Forge Fork)</option>
                  <option value="vanilla">Vanilla (Official Mojang Standard)</option>
                  <option value="velocity">Velocity (Next-Gen Ultra Proxy)</option>
                </optgroup>
                <optgroup label="High Performance & Hybrid">
                  <option value="folia">Folia (Multi-Threaded High CCU Engine)</option>
                  <option value="pufferfish">Pufferfish (High Performance Paper)</option>
                  <option value="sponge">Sponge (Modern Plugin Framework)</option>
                  <option value="spigot">Spigot (Classic Bukkit Framework)</option>
                  <option value="bungeecord">BungeeCord (Proxy Network Gateway)</option>
                  <option value="waterfall">Waterfall (Optimized BungeeCord)</option>
                  <option value="mohist">Mohist (Forge + Paper Hybrid)</option>
                  <option value="arclight">Arclight (Forge + Bukkit Hybrid)</option>
                </optgroup>
              </select>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-slate-300">Game Release Version</label>
                <div class="flex items-center gap-1.5">
                  <span id="version-loading-badge" class="text-[10px] font-mono text-cyan-400 hidden flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span> Detecting...
                  </span>
                  <span id="version-count-badge" class="text-[10px] font-mono text-slate-400"></span>
                </div>
              </div>
              <select name="mc_jar_version" id="mc_jar_version" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-mono" onchange="onMinecraftVersionSelect()">
                <option value="1.21.4" selected>Detecting available versions...</option>
              </select>
            </div>

            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-slate-300">Java Runtime Version</label>
                <span class="text-[10px] font-mono text-cyan-400" id="java-runtime-badge">Auto Matched</span>
              </div>
              <select name="docker_image" id="docker_image" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-mono" onchange="onJavaVersionSelect()">
                <option value="ghcr.io/pterodactyl/yolks:java_25">Java 25 (Paper 26.1+ / Modern)</option>
                <option value="ghcr.io/pterodactyl/yolks:java_21" selected>Java 21 (Minecraft 1.20.5 - 1.21.x)</option>
                <option value="ghcr.io/pterodactyl/yolks:java_17">Java 17 (Minecraft 1.17 - 1.20.4)</option>
                <option value="ghcr.io/pterodactyl/yolks:java_16">Java 16 (Minecraft 1.16.5)</option>
                <option value="ghcr.io/pterodactyl/yolks:java_11">Java 11 (Minecraft 1.13 - 1.15)</option>
                <option value="ghcr.io/pterodactyl/yolks:java_8">Java 8 (Minecraft 1.8 - 1.12.2)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- 4. Ownership, Node & Network Port Allocation -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- User Assignment -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
              <i data-lucide="user" class="w-3.5 h-3.5 text-cyan-400"></i> Assigned User Owner
            </label>
            <?php if (!empty($usersList)): ?>
              <select name="user_id" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-medium">
                <?php foreach ($usersList as $u): ?>
                  <option value="<?= htmlspecialchars($u['id']) ?>">
                    <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email'] ?? '') ?>) - #<?= htmlspecialchars($u['id']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input type="number" name="user_id" value="1" placeholder="User ID (e.g. 1)" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs">
            <?php endif; ?>
          </div>

          <!-- Target Node -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
              <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-cyan-400"></i> Target Node
            </label>
            <?php if (!empty($nodesList)): ?>
              <select name="node_id" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-medium">
                <?php foreach ($nodesList as $n): ?>
                  <option value="<?= htmlspecialchars($n['id']) ?>">
                    <?= htmlspecialchars($n['name']) ?> (<?= htmlspecialchars($n['fqdn'] ?? '127.0.0.1') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <select name="node_id" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs">
                <option value="1">Node 1 (Localhost / Primary)</option>
              </select>
            <?php endif; ?>
          </div>

          <!-- Port Allocation -->
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
              <i data-lucide="radio" class="w-3.5 h-3.5 text-cyan-400"></i> Port / Allocation
            </label>
            <select name="allocation_id" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-medium">
              <option value="">-- Auto-Allocate Next Free Port --</option>
              <?php if (!empty($freeAllocations)): ?>
                <?php foreach ($freeAllocations as $a): ?>
                  <option value="<?= htmlspecialchars($a['id']) ?>">
                    <?= htmlspecialchars($a['ip'] ?? ($a['ip_address'] ?? '0.0.0.0')) ?>:<?= htmlspecialchars($a['port']) ?> (ID #<?= htmlspecialchars($a['id']) ?>)
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <!-- 6. Hardware Resource Allocations -->
        <div class="glass-card p-5 rounded-2xl border border-white/10 space-y-4">
          <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4 text-cyan-400"></i> Hardware Resource Limits
          </h3>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- RAM -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-300">Memory (RAM)</span>
                <span id="ram-display" class="font-mono font-bold text-cyan-400">2048 MB (2 GB)</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="number" name="memory_val" id="memory_val" value="2" min="1" step="1" class="glass-input px-3 py-2 rounded-xl text-xs w-24 font-bold" oninput="updateRamDisplay()">
                <select name="memory_unit" id="memory_unit" class="glass-input px-3 py-2 rounded-xl text-xs" onchange="onMemoryUnitChange()">
                  <option value="GB" selected>GB</option>
                  <option value="MB">MB</option>
                </select>
              </div>
              <div class="flex gap-1.5 flex-wrap">
                <button type="button" onclick="setRam(1, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">1GB</button>
                <button type="button" onclick="setRam(2, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">2GB</button>
                <button type="button" onclick="setRam(4, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">4GB</button>
                <button type="button" onclick="setRam(8, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">8GB</button>
                <button type="button" onclick="setRam(16, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">16GB</button>
              </div>
            </div>

            <!-- CPU Limit -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-300">CPU Core Limit</span>
                <span id="cpu-display" class="font-mono font-bold text-purple-400">100% (1 Core)</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="number" name="cpu_limit" id="cpu_limit" value="100" min="10" max="1600" step="10" class="glass-input px-3 py-2 rounded-xl text-xs w-28 font-bold" oninput="updateCpuDisplay()">
                <span class="text-xs text-slate-400 font-mono">%</span>
              </div>
              <div class="flex gap-1.5 flex-wrap">
                <button type="button" onclick="setCpu(50)" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">50%</button>
                <button type="button" onclick="setCpu(100)" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">100%</button>
                <button type="button" onclick="setCpu(200)" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">200%</button>
                <button type="button" onclick="setCpu(400)" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">400%</button>
              </div>
            </div>

            <!-- SSD Storage -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-300">SSD Storage (Disk)</span>
                <span id="disk-display" class="font-mono font-bold text-amber-400">10240 MB (10 GB)</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="number" name="disk_val" id="disk_val" value="10" min="1" step="1" class="glass-input px-3 py-2 rounded-xl text-xs w-24 font-bold" oninput="updateDiskDisplay()">
                <select name="disk_unit" id="disk_unit" class="glass-input px-3 py-2 rounded-xl text-xs" onchange="onDiskUnitChange()">
                  <option value="GB" selected>GB</option>
                  <option value="MB">MB</option>
                </select>
              </div>
              <div class="flex gap-1.5 flex-wrap">
                <button type="button" onclick="setDisk(5, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">5GB</button>
                <button type="button" onclick="setDisk(10, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">10GB</button>
                <button type="button" onclick="setDisk(25, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">25GB</button>
                <button type="button" onclick="setDisk(50, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">50GB</button>
                <button type="button" onclick="setDisk(100, 'GB')" class="px-2 py-1 rounded-lg bg-white/5 hover:bg-white/10 text-[10px] font-mono border border-white/5">100GB</button>
              </div>
            </div>
          </div>
        </div>

        <!-- 7. Advanced Docker & Startup Configurations (Collapsible) -->
        <details class="glass-card p-5 rounded-2xl border border-white/10 space-y-4 group">
          <summary class="text-xs font-bold text-slate-300 cursor-pointer flex items-center justify-between list-none select-none">
            <span class="flex items-center gap-2">
              <i data-lucide="settings-2" class="w-4 h-4 text-cyan-400"></i> Advanced Container Runtime & Custom Images
            </span>
            <span class="text-cyan-400 font-mono text-[11px] group-open:rotate-180 transition-transform">▼</span>
          </summary>

          <div class="pt-4 space-y-4 border-t border-white/10 mt-3">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Custom Docker Image Override (Optional)</label>
              <input type="text" name="custom_docker_image" id="custom_docker_image" placeholder="Leave empty to use Java selector / runtime above" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-mono">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Startup Command Override (Optional)</label>
              <input type="text" name="startup_cmd" id="startup_cmd" placeholder="Leave empty for default launch script" class="w-full glass-input px-3.5 py-2.5 rounded-xl text-xs font-mono">
            </div>

            <!-- Custom Environment Variables -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-semibold text-slate-300">Custom Environment Variables</label>
                <button type="button" onclick="addEnvRow()" class="text-[10px] font-mono text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                  <i data-lucide="plus" class="w-3 h-3"></i> Add Variable
                </button>
              </div>
              <div id="env-container" class="space-y-2">
                <div class="flex items-center gap-2">
                  <input type="text" name="env_keys[]" placeholder="KEY (e.g. SERVER_PORT)" class="w-1/3 glass-input px-3 py-1.5 rounded-lg text-xs font-mono">
                  <input type="text" name="env_vals[]" placeholder="VALUE" class="flex-1 glass-input px-3 py-1.5 rounded-lg text-xs font-mono">
                  <button type="button" onclick="this.parentElement.remove()" class="text-slate-500 hover:text-rose-400 p-1.5">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </details>

        <!-- Submit Button -->
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-[11px] text-slate-400">
            Clicking deploy initiates instant container provisioning, port binding, and filesystem seeding.
          </p>
          <button type="submit" class="btn-cyber w-full sm:w-auto px-8 py-3 rounded-xl text-xs font-black uppercase tracking-wider flex items-center justify-center gap-2 shadow-xl cursor-pointer">
            <i data-lucide="rocket" class="w-4 h-4"></i> Deploy Server Instance Now
          </button>
        </div>
      </form>
    </div>
  </main>

  <!-- Connection / Authentication Modal -->
  <div id="connection-modal" class="<?= (!empty($user) || $authToken) ? 'hidden' : '' ?> fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
    <div class="glass-card w-full max-w-md p-6 sm:p-7 rounded-3xl border border-cyan-500/30 shadow-2xl space-y-5">
      <div class="flex items-center justify-between border-b border-white/10 pb-3">
        <h3 class="text-sm font-bold text-white flex items-center gap-2">
          <i data-lucide="shield-check" class="w-4 h-4 text-cyan-400"></i> Connect to Engine
        </h3>
        <?php if ($authToken): ?>
          <button onclick="document.getElementById('connection-modal').classList.add('hidden')" class="text-slate-400 hover:text-white">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        <?php endif; ?>
      </div>

      <!-- Option A: Login with Admin Username & Password -->
      <form method="POST" class="space-y-3.5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="action" value="login">
        
        <div>
          <label class="block text-[11px] font-semibold text-slate-300 mb-1">Base URL</label>
          <input type="url" name="nova_url" value="<?= htmlspecialchars($_SESSION['nova_url']) ?>" required placeholder="http://localhost:3003" class="w-full glass-input px-3 py-2 rounded-xl text-xs font-mono">
        </div>

        <div>
          <label class="block text-[11px] font-semibold text-slate-300 mb-1">Admin Username / Email</label>
          <input type="text" name="username" required placeholder="admin" class="w-full glass-input px-3 py-2 rounded-xl text-xs">
        </div>

        <div>
          <label class="block text-[11px] font-semibold text-slate-300 mb-1">Admin Password</label>
          <input type="password" name="password" required placeholder="••••••••" class="w-full glass-input px-3 py-2 rounded-xl text-xs">
        </div>

        <button type="submit" class="btn-cyber w-full py-2.5 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-lg">
          <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Sign In to Engine
        </button>
      </form>

      <div class="relative flex items-center justify-center my-2">
        <div class="border-t border-white/10 w-full"></div>
        <span class="bg-slate-900 px-3 text-[10px] text-slate-400 font-mono uppercase absolute">Or Use API Key</span>
      </div>

      <!-- Option B: Direct API Key Token -->
      <form method="POST" class="space-y-3.5">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="action" value="set_config">
        <input type="hidden" name="nova_url" value="<?= htmlspecialchars($_SESSION['nova_url']) ?>">
        
        <div>
          <label class="block text-[11px] font-semibold text-slate-300 mb-1">API Key (Token)</label>
          <input type="password" name="nova_token" placeholder="nova_xxxxxxxx or JWT Token" class="w-full glass-input px-3 py-2 rounded-xl text-xs font-mono">
        </div>

        <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 transition flex items-center justify-center gap-2">
          <i data-lucide="key" class="w-3.5 h-3.5 text-cyan-400"></i> Set API Key Directly
        </button>
      </form>
    </div>
  </div>

  <footer class="max-w-6xl mx-auto mt-8 text-center text-[11px] text-slate-500 font-mono">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($panelName) ?> &bull; Server Deployment Suite
  </footer>

  <script>
    if (window.lucide) {
      lucide.createIcons();
    }

    const typeConfigs = {
      minecraft: {
        defaultImage: 'ghcr.io/pterodactyl/yolks:java_21',
        defaultCmd: 'java -Xms128M -Xmx{{SERVER_MEMORY}}M -jar server.jar nogui',
        namePrefix: 'Minecraft SMP Server',
        desc: 'High-performance paper/purpur gaming instance'
      },
      nodejs: {
        defaultImage: 'ghcr.io/parkervcp/yolks:nodejs_20',
        defaultCmd: 'clear; echo -e "---------------[ This server is powered by www.RyzeHost.xyz ]---------------\\n"; [ ! -d node_modules ]&&npm install||echo "Using existing node_modules, delete to reinstall."; {{STARTUPSCRIPT}}',
        namePrefix: 'Node.js Express App',
        desc: 'Ultra-low latency Node.js microservice / Discord bot'
      },
      python: {
        defaultImage: 'ghcr.io/parkervcp/yolks:python_3.12',
        defaultCmd: 'if [[ -d .git ]] && [[ "{{AUTO_UPDATE}}" == "1" ]]; then git pull; fi; if [[ ! -z "{{PY_PACKAGES}}" ]]; then pip install -U --prefix .local {{PY_PACKAGES}}; fi; if [[ -f /home/container/${REQUIREMENTS_FILE} ]]; then pip install -U --prefix .local -r ${REQUIREMENTS_FILE}; fi; /usr/local/bin/python /home/container/{{PY_FILE}}',
        namePrefix: 'Python Flask App',
        desc: 'High-speed Python application container'
      }
    };

    function setServerType(type) {
      document.getElementById('hidden_server_type').value = type;
      
      // Update Tab Styles
      document.querySelectorAll('.type-card').forEach(el => el.classList.remove('type-tab-active'));
      const activeTab = document.getElementById('tab-' + type);
      if (activeTab) activeTab.classList.add('type-tab-active');

      const badge = document.getElementById('selected-type-badge');
      if (badge) badge.innerText = 'Selected: ' + type.charAt(0).toUpperCase() + type.slice(1);

      // Toggle sections
      const mcSection = document.getElementById('section-minecraft-options');
      if (mcSection) mcSection.classList.toggle('hidden', type !== 'minecraft');

      // Update placeholders
      const conf = typeConfigs[type];
      if (conf) {
        const customImg = document.getElementById('custom_docker_image');
        if (customImg) customImg.placeholder = 'Auto: ' + conf.defaultImage;
        const javaSelect = document.getElementById('docker_image');
        if (javaSelect) {
          javaSelect.disabled = (type !== 'minecraft');
        }
        document.getElementById('startup_cmd').placeholder = 'Auto: ' + conf.defaultCmd;
        const nameInput = document.getElementById('srv_name');
        if (!nameInput.value || nameInput.value.startsWith('Minecraft') || nameInput.value.startsWith('Node') || nameInput.value.startsWith('Python')) {
          generateServerName();
        }
        const descInput = document.getElementById('srv_desc');
        if (!descInput.value || descInput.value.includes('gaming') || descInput.value.includes('Node') || descInput.value.includes('Python')) {
          generateServerDesc();
        }
      }
    }

    function generateServerName() {
      const type = document.getElementById('hidden_server_type').value || 'minecraft';
      const conf = typeConfigs[type] || typeConfigs.minecraft;
      const num = Math.floor(100 + Math.random() * 900);
      document.getElementById('srv_name').value = `${conf.namePrefix} #${num}`;
    }

    function generateServerDesc() {
      const type = document.getElementById('hidden_server_type').value || 'minecraft';
      const conf = typeConfigs[type] || typeConfigs.minecraft;
      document.getElementById('srv_desc').value = conf.desc;
    }

    function setRam(val, unit) {
      const input = document.getElementById('memory_val');
      const unitSelect = document.getElementById('memory_unit');
      unitSelect.value = unit;
      input.value = val;
      adjustRamValidation(unit);
      updateRamDisplay();
    }

    function onMemoryUnitChange() {
      const input = document.getElementById('memory_val');
      const unit = document.getElementById('memory_unit').value;
      const currentVal = parseFloat(input.value || 0);
      if (unit === 'GB') {
        if (currentVal >= 128) {
          input.value = Math.max(1, Math.round(currentVal / 1024));
        }
      } else {
        if (currentVal < 128) {
          input.value = Math.max(128, Math.round(currentVal * 1024));
        }
      }
      adjustRamValidation(unit);
      updateRamDisplay();
    }

    function adjustRamValidation(unit) {
      const input = document.getElementById('memory_val');
      if (unit === 'GB') {
        input.min = '1';
        input.step = '1';
        input.max = '256';
      } else {
        input.min = '128';
        input.step = '128';
        input.max = '262144';
      }
    }

    function updateRamDisplay() {
      const input = document.getElementById('memory_val');
      const unit = document.getElementById('memory_unit').value;
      adjustRamValidation(unit);
      const val = parseFloat(input.value || 0);
      const mb = unit === 'GB' ? Math.round(val * 1024) : Math.round(val);
      const gb = unit === 'GB' ? val : (val / 1024).toFixed(1);
      document.getElementById('ram-display').innerText = `${mb} MB (${gb} GB)`;
    }

    function setCpu(val) {
      document.getElementById('cpu_limit').value = val;
      updateCpuDisplay();
    }

    function updateCpuDisplay() {
      const val = parseInt(document.getElementById('cpu_limit').value || 0, 10);
      const cores = (val / 100).toFixed(1);
      document.getElementById('cpu-display').innerText = `${val}% (${cores} ${cores == 1 ? 'Core' : 'Cores'})`;
    }

    function setDisk(val, unit) {
      const input = document.getElementById('disk_val');
      const unitSelect = document.getElementById('disk_unit');
      unitSelect.value = unit;
      input.value = val;
      adjustDiskValidation(unit);
      updateDiskDisplay();
    }

    function onDiskUnitChange() {
      const input = document.getElementById('disk_val');
      const unit = document.getElementById('disk_unit').value;
      const currentVal = parseFloat(input.value || 0);
      if (unit === 'GB') {
        if (currentVal >= 512) {
          input.value = Math.max(1, Math.round(currentVal / 1024));
        }
      } else {
        if (currentVal < 512) {
          input.value = Math.max(512, Math.round(currentVal * 1024));
        }
      }
      adjustDiskValidation(unit);
      updateDiskDisplay();
    }

    function adjustDiskValidation(unit) {
      const input = document.getElementById('disk_val');
      if (unit === 'GB') {
        input.min = '1';
        input.step = '1';
        input.max = '2000';
      } else {
        input.min = '512';
        input.step = '512';
        input.max = '2097152';
      }
    }

    function updateDiskDisplay() {
      const input = document.getElementById('disk_val');
      const unit = document.getElementById('disk_unit').value;
      adjustDiskValidation(unit);
      const val = parseFloat(input.value || 0);
      const mb = unit === 'GB' ? Math.round(val * 1024) : Math.round(val);
      const gb = unit === 'GB' ? val : (val / 1024).toFixed(1);
      document.getElementById('disk-display').innerText = `${mb} MB (${gb} GB)`;
    }

    function addEnvRow() {
      const container = document.getElementById('env-container');
      const div = document.createElement('div');
      div.className = 'flex items-center gap-2';
      div.innerHTML = `
        <input type="text" name="env_keys[]" placeholder="KEY (e.g. PORT)" class="w-1/3 glass-input px-3 py-1.5 rounded-lg text-xs font-mono">
        <input type="text" name="env_vals[]" placeholder="VALUE" class="flex-1 glass-input px-3 py-1.5 rounded-lg text-xs font-mono">
        <button type="button" onclick="this.parentElement.remove()" class="text-slate-500 hover:text-rose-400 p-1.5">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
      `;
      container.appendChild(div);
      if (window.lucide) lucide.createIcons();
    }

    // Cache for loaded versions to avoid re-fetching
    const mcVersionsCache = {};

    async function updateMinecraftVersions() {
      const jarTypeSelect = document.getElementById('mc_jar_type');
      if (!jarTypeSelect) return;
      const jarType = jarTypeSelect.value || 'paper';
      const versionSelect = document.getElementById('mc_jar_version');
      const loadingBadge = document.getElementById('version-loading-badge');
      const countBadge = document.getElementById('version-count-badge');
      const engineBadge = document.getElementById('engine-status-badge');

      if (engineBadge) {
        engineBadge.innerText = jarType.toUpperCase();
      }

      if (loadingBadge) loadingBadge.classList.remove('hidden');
      if (countBadge) countBadge.innerText = '';

      versionSelect.innerHTML = `<option value="" disabled selected>⏳ Detecting available versions for ${jarType.toUpperCase()}...</option>`;

      try {
        let buildsData = mcVersionsCache[jarType];
        if (!buildsData) {
          const res = await fetch(`/api/mcjars/builds/${encodeURIComponent(jarType)}`);
          const json = await res.json();
          buildsData = (json && json.data) ? json.data : {};
          mcVersionsCache[jarType] = buildsData;
        }

        const versionKeys = Object.keys(buildsData);
        if (!versionKeys || versionKeys.length === 0) {
          throw new Error('No builds detected');
        }

        // Sort versions semver descending: latest first
        versionKeys.sort((a, b) => {
          const pa = a.split(/[^0-9]+/).map(n => parseInt(n, 10) || 0);
          const pb = b.split(/[^0-9]+/).map(n => parseInt(n, 10) || 0);
          const len = Math.max(pa.length, pb.length);
          for (let i = 0; i < len; i++) {
            const na = pa[i] || 0;
            const nb = pb[i] || 0;
            if (na !== nb) return nb - na;
          }
          return b.localeCompare(a, undefined, { numeric: true });
        });

        // Separate into Official Releases and Snapshots/Pre-releases
        const releases = [];
        const snapshots = [];

        versionKeys.forEach(ver => {
          const item = buildsData[ver] || {};
          const isRelease = (item.type === 'RELEASE') || (!ver.includes('rc') && !ver.includes('pre') && !ver.includes('snapshot') && !ver.includes('SNAPSHOT'));
          if (isRelease) {
            releases.push({ ver, ...item });
          } else {
            snapshots.push({ ver, ...item });
          }
        });

        versionSelect.innerHTML = '';

        // Add Releases Optgroup
        if (releases.length > 0) {
          const grp = document.createElement('optgroup');
          grp.label = `Stable Releases (${releases.length})`;
          releases.forEach((r, idx) => {
            const opt = document.createElement('option');
            opt.value = r.ver;
            const javaVer = r.java || getRecommendedJavaVersion(r.ver);
            opt.dataset.java = javaVer;
            opt.innerText = `${r.ver} (Java ${javaVer}${idx === 0 ? ' • Latest Release' : ''})`;
            if (idx === 0) opt.selected = true;
            grp.appendChild(opt);
          });
          versionSelect.appendChild(grp);
        }

        // Add Snapshots Optgroup
        if (snapshots.length > 0) {
          const grp = document.createElement('optgroup');
          grp.label = `Snapshots & Pre-releases (${snapshots.length})`;
          snapshots.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.ver;
            const javaVer = s.java || getRecommendedJavaVersion(s.ver);
            opt.dataset.java = javaVer;
            opt.innerText = `${s.ver} (Java ${javaVer} • Preview)`;
            grp.appendChild(opt);
          });
          versionSelect.appendChild(grp);
        }

        if (countBadge) {
          countBadge.innerText = `${versionKeys.length} versions detected`;
        }
      } catch (err) {
        console.warn('[mcjars] Dynamic fetch fallback:', err);
        // Fallback comprehensive list if API is unreachable
        const fallbackVersions = [
          { ver: '1.21.4', java: 21 },
          { ver: '1.21.3', java: 21 },
          { ver: '1.21.1', java: 21 },
          { ver: '1.20.6', java: 21 },
          { ver: '1.20.4', java: 17 },
          { ver: '1.20.2', java: 17 },
          { ver: '1.20.1', java: 17 },
          { ver: '1.19.4', java: 17 },
          { ver: '1.18.2', java: 17 },
          { ver: '1.17.1', java: 17 },
          { ver: '1.16.5', java: 16 },
          { ver: '1.12.2', java: 8 },
          { ver: '1.8.8', java: 8 }
        ];
        versionSelect.innerHTML = '';
        const grp = document.createElement('optgroup');
        grp.label = 'Standard Supported Releases';
        fallbackVersions.forEach((f, idx) => {
          const opt = document.createElement('option');
          opt.value = f.ver;
          opt.dataset.java = f.java;
          opt.innerText = `${f.ver} (Java ${f.java}${idx === 0 ? ' • Latest' : ''})`;
          if (idx === 0) opt.selected = true;
          grp.appendChild(opt);
        });
        versionSelect.appendChild(grp);
        if (countBadge) {
          countBadge.innerText = `${fallbackVersions.length} versions (offline)`;
        }
      } finally {
        if (loadingBadge) loadingBadge.classList.add('hidden');
        onMinecraftVersionSelect();
      }
    }

    function getRecommendedJavaVersion(versionStr) {
      if (!versionStr) return 21;
      const v = String(versionStr).trim();
      if (v.startsWith('26') || v.startsWith('27') || v.startsWith('1.22') || v.startsWith('1.23')) return 25;
      if (v.startsWith('1.21') || v === '1.20.5' || v === '1.20.6') return 21;
      if (v.startsWith('1.17') || v.startsWith('1.18') || v.startsWith('1.19') || v.startsWith('1.20')) return 17;
      if (v.startsWith('1.16')) return 16;
      if (v.startsWith('1.13') || v.startsWith('1.14') || v.startsWith('1.15')) return 11;
      if (v.startsWith('1.8') || v.startsWith('1.9') || v.startsWith('1.10') || v.startsWith('1.11') || v.startsWith('1.12')) return 8;
      return 21;
    }

    let userManuallySelectedJava = false;

    function onJavaVersionSelect() {
      userManuallySelectedJava = true;
      const javaSelect = document.getElementById('docker_image');
      const badge = document.getElementById('java-runtime-badge');
      if (badge && javaSelect) {
        badge.innerText = 'User Selected';
        badge.className = 'text-[10px] font-mono text-amber-400 font-bold';
      }
    }

    function onMinecraftVersionSelect() {
      const versionSelect = document.getElementById('mc_jar_version');
      if (!versionSelect) return;
      const selectedOpt = versionSelect.options[versionSelect.selectedIndex];
      if (!selectedOpt) return;
      const ver = selectedOpt.value;
      const javaVer = parseInt(selectedOpt.dataset.java || getRecommendedJavaVersion(ver), 10);

      // Update Java match badge
      const javaBadge = document.getElementById('java-match-badge');
      if (javaBadge) {
        javaBadge.innerText = `Auto Matched: Java ${javaVer}`;
      }

      // Update Java select dropdown according to matched Java
      const javaSelect = document.getElementById('docker_image');
      if (javaSelect && !userManuallySelectedJava) {
        let autoImg = 'ghcr.io/pterodactyl/yolks:java_21';
        if (javaVer >= 25) autoImg = 'ghcr.io/pterodactyl/yolks:java_25';
        else if (javaVer >= 21) autoImg = 'ghcr.io/pterodactyl/yolks:java_21';
        else if (javaVer >= 17) autoImg = 'ghcr.io/pterodactyl/yolks:java_17';
        else if (javaVer >= 16) autoImg = 'ghcr.io/pterodactyl/yolks:java_16';
        else if (javaVer >= 11) autoImg = 'ghcr.io/pterodactyl/yolks:java_11';
        else if (javaVer <= 8) autoImg = 'ghcr.io/pterodactyl/yolks:java_8';

        javaSelect.value = autoImg;
        const runtimeBadge = document.getElementById('java-runtime-badge');
        if (runtimeBadge) {
          runtimeBadge.innerText = `Auto: Java ${javaVer}`;
          runtimeBadge.className = 'text-[10px] font-mono text-cyan-400';
        }
      }
    }

    // Initialize displays on load
    document.addEventListener('DOMContentLoaded', () => {
      if (!document.getElementById('srv_name').value) {
        generateServerName();
      }
      if (!document.getElementById('srv_desc').value) {
        generateServerDesc();
      }
      updateRamDisplay();
      updateCpuDisplay();
      updateDiskDisplay();
      updateMinecraftVersions();
    });
  </script>
</body>
</html>
