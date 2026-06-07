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
    $router->addRoute('GET', '/api/v0/auth/refresh', new GetRefreshAction());

    // Map
    $router->addRoute('GET', '/api/v0/map', new GetMapDataAction());

    // Statistics
    $router->addRoute('GET', '/api/v0/statistics', new GetStatisticsAction());

    // Report / Download
    $router->addRoute('GET', '/api/v0/report', new GetReportDataAction());

    // Upload / Admin
    $router->addRoute('POST', '/api/v0/admin/accident', new PostAccidentAction());
    $router->addRoute('POST', '/api/v0/admin/accident/file', new PostAccidentFileAction());

    // Page: Auth
    $router->addRoute('GET', '/register', new ViewRegisterAction());
    $router->addRoute('GET', '/login', new ViewLoginAction());

    // Page: Main
    $router->addRoute('GET', '/', new ViewHomeAction());
    $router->addRoute('GET', '/home', new ViewHomeAction());
    $router->addRoute('GET', '/map', new ViewMapAction());
    $router->addRoute('GET', '/download', new ViewDownloadAction());
    $router->addRoute('GET', '/upload', new ViewUploadAction());

// Get the request
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

// Dispatch the request to the appropriate action
    $router->dispatch($method, $requestUri);
} catch (Throwable $e) {
    (new ErrorResponder())->send(500, $e->getMessage());
}