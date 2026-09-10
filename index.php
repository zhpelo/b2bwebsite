<?php

/**
 * SHOPAGG B2B Website - Entry Point
 * @author    SHOPAGG 
 * @link      https://www.shopagg.com
 * @package   SHOPAGG B2B Website
 * @copyright Copyright (c) 2015–2026 SHOPAGG. All rights reserved.
 * @license   MIT License
 */

declare(strict_types=1);

// 标记入口点，防止直接访问 app 目录文件
define('APP_ENTRY_POINT', true);
define('APP_VERSION', '1.3.1');
// 应用安全响应头
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// 运行环境
session_start();
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// 根目录与二级目录（子目录部署）
define('APP_ROOT', rtrim(str_replace('\\', '/', realpath(__DIR__)), '/'));

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = rtrim(dirname($scriptName), '/');
define('APP_BASE_PATH', ($basePath === '' || $basePath === '/') ? '' : '/' . ltrim($basePath, '/'));

// Debug 模式：根目录下创建 .env 文件并写入 APP_DEBUG=true 即可开启
$envFile = APP_ROOT . '/.env';
$debugMode = false;
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (stripos($line, 'APP_DEBUG') === 0) {
            $debugMode = strtolower(trim(explode('=', $line, 2)[1] ?? '')) === 'true';
        }
    }
}
define('APP_DEBUG', $debugMode);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('log_errors', '1');
    ini_set('error_log', APP_ROOT . '/uploads/logs/error.log');
}

set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    $label = match ($errno) {
        E_WARNING, E_USER_WARNING => 'Warning',
        E_NOTICE, E_USER_NOTICE   => 'Notice',
        default                    => 'Error',
    };
    $message = "[{$label}] {$errstr} in {$errfile}:{$errline}";
    if (APP_DEBUG) {
        error_log($message);
    }
    if ($errno === E_USER_ERROR) {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    return true;
});

set_exception_handler(function (\Throwable $e): void {
    $code = $e->getCode() ?: 500;
    if (!headers_sent()) {
        http_response_code((int)$code >= 400 && (int)$code < 600 ? (int)$code : 500);
    }
    if (APP_DEBUG) {
        echo '<div style="font-family:monospace;padding:2rem;max-width:960px;margin:2rem auto">';
        echo '<h1 style="color:#dc2626;margin-bottom:1rem">⚠ Debug Error</h1>';
        echo '<p style="font-size:1.25rem;color:#111"><strong>' . htmlspecialchars(get_class($e)) . '</strong>: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p style="color:#666;margin:.5rem 0">File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre style="background:#1e293b;color:#e2e8f0;padding:1.5rem;border-radius:.75rem;overflow-x:auto;font-size:.875rem;line-height:1.6;margin-top:1rem">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '</div>';
    } else {
        error_log('[Exception] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        if (!headers_sent()) {
            header('Location: ' . (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/404');
        }
    }
    exit(1);
});

// PSR-4 自动加载
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

require APP_ROOT . '/app/Helpers/Helpers.php';

use App\Core\Router;
use App\Core\Database;
use App\Plugins\PluginRuntime;

// 数据库（首次访问时初始化 schema）
Database::getInstance();

// 插件运行时仅加载已编译的 PHP 注册表，不扫描插件目录或解析 Manifest。
$pluginRuntime = PluginRuntime::boot();

// 路由
$router = new Router();
require APP_ROOT . '/app/routes.php';
register_routes($router);
$pluginRuntime->registerRoutes($router);
$pluginRuntime->events()->dispatch('system.booted', ['version' => APP_VERSION]);
$pluginRuntime->events()->dispatch('system.request.started', ['method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 'uri' => $_SERVER['REQUEST_URI'] ?? '/']);
$router->run();
$pluginRuntime->events()->dispatch('system.response.generated', ['status' => http_response_code()]);
