<?php

require_once ROOT . '/src/Actions/Action.php';

class RouteNode implements Action
{
    private array $routes = [];

    public function __construct(?string $uri, Action $action)
    {
        if ($uri === null) return;

        $uri = explode('/', $uri, 2);
        if (isset($uri[1]))
            $this->routes[$uri[0]] = new RouteNode($uri[1], $action);
        else
            $this->routes[$uri[0]] = $action;
    }

    public function addRoute(string $uri, Action $action): void
    {
        $uri = explode('/', $uri, 2);
        if (isset($uri[1])) {
            if (!isset($this->routes[$uri[0]])) {
                $this->routes[$uri[0]] = new RouteNode($uri[1], $action);
            } else {
                // Append to existing node
                $this->routes[$uri[0]]->addRoute($uri[1], $action);
            }
        } else {
            $this->routes[$uri[0]] = $action;
        }
    }

    public function execute(?string $param): void
    {
        $param = explode('/', $param, 2);

        if (!isset($this->routes[$param[0]])) (new ErrorResponder())->send(404, 'Source not found');

        $this->routes[$param[0]]->execute($param[1] ?? null);
    }
}