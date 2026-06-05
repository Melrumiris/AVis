<?php

use src\Actions\api\GetRefreshAction;
use src\Actions\api\PostLoginAction;
use src\Actions\api\PostRegisterAction;
use src\Actions\page\ViewHomeAction;
use src\Actions\page\ViewLoginAction;
use src\Actions\page\ViewRegisterAction;
use src\Core\Router;
use src\Responders\ErrorResponder;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define("ROOT", dirname(__DIR__));
require_once ROOT . '/src/Responders/ErrorResponder.php';
try {
    require_once ROOT . '/src/Core/Router.php';
    require_once ROOT . '/src/Actions/actions.php';

    $router = new Router();
// Mapping the Actions
    $router->addRoute('POST', '/api/auth/register', new PostRegisterAction());
    $router->addRoute('POST', '/api/auth/login', new PostLoginAction());
    $router->addRoute('GET', '/api/refresh', new GetRefreshAction());

    $router->addRoute('GET', '/register', new ViewRegisterAction());
    $router->addRoute('GET', '/login', new ViewLoginAction());
    $router->addRoute('GET', '/', new ViewHomeAction());

// Get the request
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

// Dispatch the request to the appropriate action
    $router->dispatch($method, $requestUri);
} catch (Throwable $e) {
    new ErrorResponder()->send(500, $e->getMessage());
}