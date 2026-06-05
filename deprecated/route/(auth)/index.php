<?php
global $request;

switch ($request) {
    case '/login':
        require ROOT . '/route/(auth)/login.php'; break;
    case '/register':
        require ROOT . '/route/(auth)/register.php'; break;
}