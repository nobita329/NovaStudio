<?php
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

$filePath = $argv[1] ?? '';
if (!$filePath || !file_exists($filePath)) {
    http_response_code(404);
    echo "View not found: " . htmlspecialchars($filePath);
    exit(1);
}

$rawInput = file_get_contents('php://stdin');
$nova = json_decode($rawInput, true) ?: [];

$initPath = dirname(__DIR__, 2) . '/views/helpers/init.php';
if (file_exists($initPath)) {
    require_once $initPath;
}

extract($nova, EXTR_SKIP);
require $filePath;
