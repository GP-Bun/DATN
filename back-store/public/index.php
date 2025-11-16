<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// --------------- THÊM ĐOẠN NÀY ---------------
echo "<h1>PHP đang chạy</h1>";
flush();
// ---------------------------------------------

require __DIR__.'/../vendor/autoload.php';

// --------------- THÊM ĐOẠN NÀY ---------------
try {
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo "<p>App load OK</p>";
    flush();
} catch (Throwable $e) {
    echo "<pre style='color:red'>";
    echo "LỖI APP: " . $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>";
    die();
}
// ---------------------------------------------

$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
