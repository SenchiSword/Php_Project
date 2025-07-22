<?php
require 'config.php';

$start = microtime(true);
$mem_start = memory_get_usage();

header('Content-Type: application/json');

try {
    // Test DB
    $stmt = $pdo->query("SELECT 1");
    $db_test = $stmt->fetchColumn() === '1';
    
    // Test sessions
    $_SESSION['perf_test'] = 'ok';
    $session_test = ($_SESSION['perf_test'] ?? null) === 'ok';
    
    echo json_encode([
        'status' => 'ok',
        'db' => $db_test ? 'ok' : 'fail',
        'session' => $session_test ? 'ok' : 'fail',
        'memory' => round((memory_get_peak_usage() - $mem_start)/1024/1024, 2).'MB',
        'time' => round((microtime(true)-$start)*1000).'ms',
        'php_version' => PHP_VERSION,
        'opcache' => function_exists('opcache_get_status') ? 'enabled' : 'disabled'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}