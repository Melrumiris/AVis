<?php
define("ROOT", dirname(__DIR__));
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
try {
    require_once ROOT . '/src/Core/Router.php';
    require_once ROOT . '/src/Actions/actions.php';

    $router = new Router();
// Mapping the Actions
    $router->addRoute('POST', '/api/v0/auth/register', new PostRegisterAction());
    $router->addRoute('POST', '/api/v0/auth/login', new PostLoginAction());
    $router->addRoute('GET', '/api/v0/refresh', new GetRefreshAction());

    $router->addRoute('GET', '/register', new ViewRegisterAction());
    $router->addRoute('GET', '/login', new ViewLoginAction());
    $router->addRoute('GET', '/', new ViewHomeAction());
    $router->addRoute('GET', '/home', new ViewHomeAction());

// Get the request
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

// Dispatch the request to the appropriate action
    $router->dispatch($method, $requestUri);
} catch (Throwable $e) {
    if (explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))[0] === 'api') {
        (new JsonResponder())->send([
                'success' => false,
                'error' => 'Database error',
        ], 500);
    } else
        (new ErrorResponder())->send(500, $e->getMessage());
}