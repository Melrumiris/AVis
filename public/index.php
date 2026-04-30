<?php
define("ROOT", dirname( __DIR__));

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

$base_path = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($request, $base_path) === 0) {
    $request = substr($request, strlen($base_path));
}

if ($request === '') {
    $request = '/';
}

require ROOT . '/route/index.php';