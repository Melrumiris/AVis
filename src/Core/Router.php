<?php

namespace src\Core;

use src\Actions\Action;
use src\Responders\ErrorResponder;

require_once ROOT . '/src/Core/RouteNode.php';

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $uri, Action $action): void
    {
        $uri = substr($uri, 1);
        $uri = explode('/', $uri, 2);
        if (isset($uri[1])) {
            if (!isset($this->routes[$method][$uri[0]])) {
                $this->routes[$method][$uri[0]] = new RouteNode($uri[1], $action);
            } else {
                $this->routes[$method][$uri[0]]->addRoute($uri[1], $action);
            }
        } else {
            $this->routes[$method][$uri[0]] = $action;
        }
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = substr($uri, 1);
        $uri = explode('/', $uri, 2);

        if (!isset($this->routes[$method])) new ErrorResponder()->send(501, 'Method not supported');
        if (!isset($this->routes[$method][$uri[0]]))
            if ($method != 'GET') new ErrorResponder()->send(405, 'Method not allowed for this URL');
            else new ErrorResponder()->send(404, 'Page not found');

        $this->routes[$method][$uri[0]]->execute($uri[1] ?? null);
    }
}