<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/RouteNode.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class Router
{
    private array $routes = [];

    public function addRoute(string $method, string $uri, Action $action): void
    {
        $uri = substr($uri, 1);
        $uriParts = explode('/', $uri, 2);
        if (isset($uriParts[1])) {
            if (!isset($this->routes[$method][$uriParts[0]])) {
                $this->routes[$method][$uriParts[0]] = new RouteNode($uriParts[1], $action);
            } else {
                $this->routes[$method][$uriParts[0]]->addRoute($uriParts[1], $action);
            }
        } else {
            if (!isset($this->routes[$method][$uriParts[0]])) {
                $this->routes[$method][$uriParts[0]] = new RouteNode(null, $action);
            } else {
                $this->routes[$method][$uriParts[0]]->addRoute(null, $action);
            }
        }
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = substr($uri, 1);
        $uriParts = explode('/', $uri, 2);
        $first = $uriParts[0];
        $rest = $uriParts[1] ?? null;

        if (!isset($this->routes[$method])) {
            (new ErrorResponder())->send(501, 'Method not supported');
        }

        if (!isset($this->routes[$method][$first])) {
            if ($method !== 'GET') {
                (new ErrorResponder())->send(405, 'Method not allowed for this URL');
            } else {
                (new ErrorResponder())->send(404, 'Page not found');
            }
        }

        $this->routes[$method][$first]->execute($rest);
    }
}