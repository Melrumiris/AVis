<?php
define("ROOT", dirname( __DIR__));

$request = $_SERVER['REQUEST_URI'];
$request = parse_url($request, PHP_URL_PATH);

require ROOT . '/route/index.php';