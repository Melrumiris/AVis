<?php

declare(strict_types=1);

require_once ROOT . '/src/Actions/Action.php';
require_once ROOT . '/src/Domain/AccidentDomain.php';
require_once ROOT . '/src/Responders/RssResponder.php';

class GetRssAction implements Action
{
    public function execute(?string $param = null): void
    {
        $domain = new AccidentDomain();
        $accidents = $domain->getRecentAccidents(100);

        $responder = new RssResponder();
        $responder->send($accidents);
    }
}
