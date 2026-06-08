<?php

declare(strict_types=1);

require_once ROOT . '/src/Actions/Action.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class RouteNode implements Action
{
    private array $routes = [];

    public function __construct(?string $uri, Action $action)
    {
        if ($uri === null || $uri === '') {
            $this->routes[''] = $action;
            return;
        }

        $uriParts = explode('/', $uri, 2);
        if (isset($uriParts[1])) {
            $this->routes[$uriParts[0]] = new RouteNode($uriParts[1], $action);
        } else {
            $this->routes[$uriParts[0]] = new RouteNode(null, $action);
        }
    }

    public function addRoute(?string $uri, Action $action): void
    {
        if ($uri === null || $uri === '') {
            $this->routes[''] = $action;
            return;
        }

        $uriParts = explode('/', $uri, 2);
        if (isset($uriParts[1])) {
            if (!isset($this->routes[$uriParts[0]])) {
                $this->routes[$uriParts[0]] = new RouteNode($uriParts[1], $action);
            } else {
                $this->routes[$uriParts[0]]->addRoute($uriParts[1], $action);
            }
        } else {
            if (!isset($this->routes[$uriParts[0]])) {
                $this->routes[$uriParts[0]] = new RouteNode(null, $action);
            } else {
                $this->routes[$uriParts[0]]->addRoute(null, $action);
            }
        }
    }

    public function execute(?string $param): void
    {
        if ($param === null || $param === '') {
            if (isset($this->routes[''])) {
                $this->routes['']->execute(null);
                return;
            }
            (new ErrorResponder())->send(404, 'Source not found');
        }

        $paramParts = explode('/', $param, 2);
        $first = $paramParts[0];
        $rest = $paramParts[1] ?? null;

        if (isset($this->routes[$first])) {
            $this->routes[$first]->execute($rest);
        } else {
            // In the case that no other node is found, it defaults to the [''] node but with the rest of the url as an argument.
            if (isset($this->routes[''])) {
                $this->routes['']->execute($param);
            } else {
                (new ErrorResponder())->send(404, 'Source not found');
            }
        }
    }
}