<?php
$panelName = $settings['panel_name'] ?? 'Nova';
$panelFavicon = $settings['panel_favicon'] ?? '/arix/Arix.png';
$pageTitle = isset($title) ? $title . ' — ' . $panelName : $panelName . ' Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="icon" type="image/png" href="<?= e($panelFavicon ?: '/arix/Arix.png') ?>">
  <link rel="stylesheet" href="/assets/css/nova.css">
  <link rel="stylesheet" href="/assets/css/arix-theme.css">
  <?php if (!empty($themeCss)): ?>
    <style id="arix-theme-custom-styles"><?= $themeCss ?></style>
  <?php endif; ?>
  <?php if (!empty($themeConfig['general']['custom_js'])): ?>
    <script id="arix-custom-js"><?= $themeConfig['general']['custom_js'] ?></script>
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script>
    (function() {
      const theme = localStorage.getItem('theme');
      if (theme === 'light') {
        document.documentElement.classList.add('lightmode');
      } else if (theme === 'oled') {
        document.documentElement.classList.add('oled');
      } else if (theme === 'auto') {
        if (!window.matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.classList.add('lightmode');
        }
      }
    })();
  </script>
  <script src="/assets/js/nova.js"></script>
</head>
<body>
<div class="app-container">
