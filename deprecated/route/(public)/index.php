<?php
global $request;

switch ($request) {
    case '/about':
        require ROOT . '/route/(public)/about.php'; break;
}
