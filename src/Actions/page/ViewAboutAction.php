<?php

declare(strict_types=1);

require_once ROOT . '/src/Actions/Action.php';
require_once ROOT . '/src/Responders/HtmlResponder.php';

class ViewAboutAction implements Action
{
    public function execute(?string $param): void
    {
        $responder = new HtmlResponder();
        $responder->send(
            ROOT . '/views/layouts/main.php',
            ROOT . '/views/pages/about.php',
            'About'
        );
    }
}
