<?php
define("ROOT", dirname(__DIR__));
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
try {
    require_once ROOT . '/src/Core/Router.php';
    require_once ROOT . '/src/Actions/actions.php';

    $router = new Router();

    // --- API: Auth ---
    $router->addRoute('POST', '/api/v0/auth/register', new PostRegisterAction());
    $router->addRoute('POST', '/api/v0/auth/login', new PostLoginAction());
    $router->addRoute('GET', '/api/v0/auth/refresh', new GetRefreshAction());
    $router->addRoute('DELETE', '/api/v0/auth/logout', new DeleteLogoutAction());

    // --- API: Map ---
    $router->addRoute('GET', '/api/v0/map', new GetMapDataAction());

    // --- API: Statistics ---
    $router->addRoute('GET', '/api/v0/statistics', new GetStatisticsAction());

    // --- API: Report / Download ---
    $router->addRoute('GET', '/api/v0/report', new GetReportDataAction());
    $router->addRoute('GET', '/api/v0/report/file', new GetReportFileAction());

    // --- API: Upload / Admin ---
    $router->addRoute('POST', '/api/v0/admin/accident', new PostAccidentAction());
    $router->addRoute('POST', '/api/v0/admin/accident/file', new PostAccidentFileAction());
    $router->addRoute('PUT',  '/api/v0/admin/accident/file', new PutAccidentFileAction());

    // --- API: Profile ---
    $router->addRoute('GET', '/api/v0/profile', new GetProfileAction());
    $router->addRoute('PATCH', '/api/v0/profile', new PatchProfileAction());

    // --- API: RSS ---
    $router->addRoute('GET', '/rss', new GetRssAction());

    // --- API: NLP ---
    $router->addRoute('QUERY', '/api/v0/accidents/ask', new QueryNlpAction());

    // --- Pages ---
    $router->addRoute('GET', '/', new ViewIndexAction());
    $router->addRoute('GET', '/about', new ViewAboutAction());
    $router->addRoute('GET', '/home', new ViewHomeAction());
    $router->addRoute('GET', '/login', new ViewLoginAction());
    $router->addRoute('GET', '/register', new ViewRegisterAction());
    $router->addRoute('GET', '/account', new ViewAccountAction());
    $router->addRoute('GET', '/admin', new ViewAdminAction());

    // Get the request
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    // Dispatch the request to the appropriate action
    $router->dispatch($method, $requestUri);
} catch (Throwable $e) {
    (new ErrorResponder())->send(500, $e->getMessage());
}