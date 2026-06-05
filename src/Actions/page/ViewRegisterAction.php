<?php

namespace src\Actions\page;

use src\Actions\Action;
use src\Responders\HtmlResponder;

require_once ROOT . '/src/Responders/HtmlResponder.php';
require_once ROOT . '/src/Responders/JsonResponder.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';
require_once ROOT . '/src/Responders/RedirectResponder.php';

class ViewRegisterAction implements Action
{

    public function execute(?string $param): void
    {
        $viewPath = ROOT . '/views/layouts/plain.php';
        $pageTemplate = ROOT . '/views/pages/register.php';
        new HtmlResponder()->send($viewPath, $pageTemplate, 'Register');
    }
}