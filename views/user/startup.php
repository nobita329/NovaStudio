<?php
$title = 'Startup — ' . $server['name'];
$breadcrumb = $server['name'] . ' / Startup';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar_user.php';

// Prepare list of Java image options or runtime options
$isMinecraft = !isset($egg) || ($egg['nest_id'] ?? 1) == 1 || (strpos($server['startup_command'] ?? '', 'java') !== false);
$isNode = isset($egg) && ($egg['nest_id'] ?? 0) == 2;
$isPython = isset($egg) && ($egg['nest_id'] ?? 0) == 3;

$dockerOptions = [];
if ($isMinecraft) {
  $dockerOptions = [
    'ghcr.io/pterodactyl/yolks:java_26' => 'Java 26',
    'ghcr.io/pterodactyl/yolks:java_25' => 'Java 25',
    'ghcr.io/pterodactyl/yolks:java_21' => 'Java 21 (Recommended)',
    'ghcr.io/pterodactyl/yolks:java_17' => 'Java 17',
    'ghcr.io/pterodactyl/yolks:java_16' => 'Java 16',
    'ghcr.io/pterodactyl/yolks:java_11' => 'Java 11',
    'ghcr.io/pterodactyl/yolks:java_8'  => 'Java 8',
  ];
} elseif ($isNode) {
  $dockerOptions = [
    'ghcr.io/parkervcp/yolks:nodejs_24' => 'Nodejs 24',
    'ghcr.io/parkervcp/yolks:nodejs_23' => 'Nodejs 23',
    'ghcr.io/parkervcp/yolks:nodejs_22' => 'Nodejs 22',
    'ghcr.io/parkervcp/yolks:nodejs_21' => 'Nodejs 21',
    'ghcr.io/parkervcp/yolks:nodejs_20' => 'Nodejs 20 (Recommended)',
    'ghcr.io/parkervcp/yolks:nodejs_19' => 'Nodejs 19',
    'ghcr.io/parkervcp/yolks:nodejs_18' => 'Nodejs 18',
    'ghcr.io/parkervcp/yolks:nodejs_17' => 'Nodejs 17',
    'ghcr.io/parkervcp/yolks:nodejs_16' => 'Nodejs 16',
    'ghcr.io/parkervcp/yolks:nodejs_14' => 'Nodejs 14',
    'ghcr.io/parkervcp/yolks:nodejs_12' => 'Nodejs 12',
  ];
} elseif ($isPython) {
  $dockerOptions = [
    'ghcr.io/parkervcp/yolks:python_3.12' => 'Python 3.12 (Recommended)',
    'ghcr.io/parkervcp/yolks:python_3.11' => 'Python 3.11',
    'ghcr.io/parkervcp/yolks:python_3.10' => 'Python 3.10',
    'ghcr.io/parkervcp/yolks:python_3.9'  => 'Python 3.9',
    'ghcr.io/parkervcp/yolks:python_3.8'  => 'Python 3.8',
    'ghcr.io/parkervcp/yolks:python_3.7'  => 'Python 3.7',
    'ghcr.io/parkervcp/yolks:python_2.7'  => 'Python 2.7',
  ];
} else {
  $dockerOptions = [
    $server['docker_image'] => $server['docker_image']
  ];
}
?>
<link rel="stylesheet" href="/assets/css/nova-console.css">

<style>
.startup-top-grid {
  display: grid;
  grid-template-columns: 1.8fr 1fr;
  gap: 1.5rem;
  align-items: stretch;
  margin-bottom: 2rem;
}

@media (max-width: 960px) {
  .startup-top-grid {
    grid-template-columns: 1fr;
  }
}

.startup-card {
  background: rgb(var(--gray700));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.startup-card-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: rgb(var(--gray200));
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 1rem;
  font-family: var(--font-header);
}

.startup-code-box {
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  padding: 0.85rem 1rem;
  color: var(--text-main);
  font-family: var(--font-mono);
  font-size: 0.875rem;
  line-height: 1.5;
  width: 100%;
  resize: vertical;
  min-height: 60px;
  outline: none;
  transition: var(--transition);
}

.startup-code-box:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}

.startup-select {
  width: 100%;
  background: rgb(var(--gray800));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusInput);
  color: var(--text-main);
  padding: 0.65rem 0.85rem;
  font-size: 0.875rem;
  outline: none;
  cursor: pointer;
  margin-bottom: 0.5rem;
  transition: var(--transition);
}

.startup-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}

.startup-desc-text {
  font-size: 0.8rem;
  color: rgb(var(--gray300));
  line-height: 1.45;
  margin-top: 0.35rem;
}

.section-variables-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--text-main);
  margin-bottom: 1.25rem;
  letter-spacing: -0.01em;
}

.variables-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 900px) {
  .variables-grid {
    grid-template-columns: 1fr;
  }
}

.variable-card {
  background: rgb(var(--gray700));
  border: 1px solid var(--border-color);
  border-radius: var(--radiusBox);
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
}

.variable-card-title {
  font-size: 0.8rem;
  font-weight: 700;
  color: rgb(var(--gray200));
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.85rem;
  font-family: var(--font-header);
}

.variable-input {
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
}

.variable-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.2);
}

.variable-desc {
  font-size: 0.8rem;
  color: rgb(var(--gray300));
  line-height: 1.45;
  margin-top: 0.55rem;
}

.save-bar {
  margin-top: 2rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-blue-save {
  background: var(--primary);
  color: #ffffff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.65rem 1.75rem;
  border-radius: var(--radiusInput);
  border: none;
  cursor: pointer;
  transition: var(--transition);
}

.btn-blue-save:hover {
  background: var(--primary-hover);
  box-shadow: 0 4px 14px rgba(74, 53, 207, 0.4);
}

.btn-amber-rebuild {
  background: #f59e0b;
  color: #0b0f17;
  font-weight: 700;
  font-size: 0.85rem;
  padding: 0.6rem 1.5rem;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-amber-rebuild:hover {
  background: #fbbf24;
}

.toast-save-feedback {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #10b981;
  color: #fff;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s ease;
  z-index: 100;
}
.toast-save-feedback.show {
  opacity: 1;
}
</style>

<div class="main-wrapper">
  <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

  <main class="content-area" style="padding: 1.5rem 1.75rem;">
    <!-- Unified Server Header & Sub-Nav Tabs -->
    <?php require __DIR__ . '/partials/server_subpage_header.php'; ?>

    <form action="/server/<?= e($server['id']) ?>/startup" method="POST" id="startupForm">
      <!-- Top Grid: Startup Command & Full Command Preview (Left) & Docker Image (Right) -->
      <div class="startup-top-grid">
        <!-- 1. STARTUP COMMAND & FULL INVOCATION PREVIEW -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
          <div class="startup-card">
            <div class="startup-card-title">STARTUP COMMAND</div>
            <textarea name="startup_command" id="startup_command" class="startup-code-box" rows="2" oninput="updateFullCommandPreview()"><?= e($server['startup_command']) ?></textarea>
            <div class="startup-desc-text" style="margin-top: 0.5rem; font-size: 0.75rem; color: rgb(var(--gray300));">
              Template command used to launch the server. Template tags like <code>{{SERVER_JARFILE}}</code> and <code>{{SERVER_MEMORY}}</code> are substituted automatically.
            </div>
          </div>

          <div class="startup-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
              <div class="startup-card-title" style="margin-bottom: 0;">FULL / EXECUTED COMMAND</div>
              <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.7rem; color: #4ade80; background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); padding: 2px 8px; border-radius: 4px; font-weight: 600;">FULL INVOCATION</span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="copyFullCommand()" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; cursor: pointer;">Copy</button>
              </div>
            </div>
            <pre id="full_command_preview" class="startup-code-box" style="background: rgb(var(--gray900)); color: #38bdf8; font-family: var(--font-mono); font-size: 0.85rem; user-select: all; cursor: text; white-space: pre-wrap; word-break: break-all; margin: 0; min-height: unset; border: 1px solid rgba(56, 189, 248, 0.25);"><?= e($fullCommand ?? $server['startup_command']) ?></pre>
          </div>
        </div>

        <!-- 2. DOCKER IMAGE -->
        <div class="startup-card">
          <div class="startup-card-title">DOCKER IMAGE</div>
          <select name="docker_image" id="docker_image" class="startup-select" onchange="autoSaveField('docker_image', this.value)">
            <?php foreach ($dockerOptions as $imgTag => $imgLabel): ?>
              <option value="<?= e($imgTag) ?>" <?= $server['docker_image'] === $imgTag ? 'selected' : '' ?>>
                <?= e($imgLabel) ?>
              </option>
            <?php endforeach; ?>
            <?php if (!isset($dockerOptions[$server['docker_image']])): ?>
              <option value="<?= e($server['docker_image']) ?>" selected>
                <?= e($server['docker_image']) ?> (Custom)
              </option>
            <?php endif; ?>
          </select>
          <div class="startup-desc-text">
            This is an advanced feature allowing you to select a Docker image to use when running this server instance.
          </div>
        </div>
      </div>

      <!-- Variables Header -->
      <h2 class="section-variables-title">Variables</h2>

      <!-- Variables Grid: 2 Columns -->
      <div class="variables-grid">
        <?php foreach ($variablesList as $v): ?>
          <div class="variable-card">
            <div class="variable-card-title"><?= e($v['name']) ?></div>
            <input type="hidden" name="env_keys[]" value="<?= e($v['key']) ?>">
            <input type="text" 
                   name="env_vals[]" 
                   class="variable-input" 
                   value="<?= e($v['val']) ?>" 
                   placeholder="<?= e($v['defaultVal'] ?? '') ?>"
                   oninput="updateFullCommandPreview()"
                   onchange="autoSaveVariable('<?= e($v['key']) ?>', this.value); updateFullCommandPreview()">
            <div class="variable-desc"><?= e($v['desc']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Save Bar -->
      <div class="save-bar">
        <button type="submit" name="rebuild" value="0" class="btn-blue-save">
          Save Changes
        </button>
        <button type="submit" name="rebuild" value="1" class="btn-amber-rebuild" onclick="return confirm('Save and rebuild container now? This will briefly recreate the server container with the updated image and command.');">
          Save &amp; Rebuild Container
        </button>
      </div>
    </form>
  </main>
</div>

<!-- Inline Auto-Save Feedback Notification -->
<div id="saveFeedbackToast" class="toast-save-feedback">✓ Setting updated successfully</div>

<script>
function showFeedbackToast(msg = '✓ Setting updated successfully') {
  const toast = document.getElementById('saveFeedbackToast');
  if (!toast) return;
  toast.textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 2200);
}

function updateFullCommandPreview() {
  const tpl = document.getElementById('startup_command')?.value || '';
  const mem = '<?= e($server['memory'] ?? 1024) ?>';
  const port = '<?= e($alloc['port'] ?? 25565) ?>';

  let full = tpl.replace(/{{SERVER_MEMORY}}/gi, mem)
                .replace(/{{SERVER_PORT}}/gi, port)
                .replace(/{{SERVER_IP}}/gi, '0.0.0.0');

  const inputs = document.querySelectorAll('input[name="env_vals[]"]');
  const keys = document.querySelectorAll('input[name="env_keys[]"]');
  const vars = {};
  for (let i = 0; i < keys.length; i++) {
    vars[keys[i].value] = inputs[i] ? inputs[i].value : '';
  }

  const jar = vars['SERVER_JARFILE'] || vars['SERVER.JAR'] || vars['SERVER_JAR'] || 'server.jar';
  full = full.replace(/{{SERVER_JARFILE}}/gi, jar)
             .replace(/{{SERVER\.JAR}}/gi, jar)
             .replace(/{{SERVER_JAR}}/gi, jar);

  for (const [k, v] of Object.entries(vars)) {
    const esc = k.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    full = full.replace(new RegExp('{{' + esc + '}}', 'gi'), v);
  }

  const preview = document.getElementById('full_command_preview');
  if (preview) preview.textContent = full;
}

function copyFullCommand() {
  const text = document.getElementById('full_command_preview')?.textContent || '';
  navigator.clipboard.writeText(text).then(() => {
    showFeedbackToast('✓ Copied Full Command to clipboard');
  }).catch(() => {
    showFeedbackToast('Copied');
  });
}

function autoSaveVariable(key, val) {
  fetch('/server/<?= e($server['id']) ?>/startup/variable', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ key, val })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showFeedbackToast(`✓ ${key} updated`);
    }
  })
  .catch(() => {});
}

function autoSaveField(field, val) {
  showFeedbackToast('✓ Selected image: ' + val);
}

document.addEventListener('DOMContentLoaded', () => {
  updateFullCommandPreview();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
