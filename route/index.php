<?php
global $request;
require_once ROOT . '/src/utils/authAPI.php';

//$type = Rank::ADMIN; //For temporary tests

switch ($request) {
    //Index
    case '/':
        switch (get_user_rank()) {
            case Rank::GUEST:
                require ROOT . '/route/(public)/about.php'; break;
            case Rank::USER:
                require ROOT . '/route/(main)/home.php'; break;
            case Rank::ADMIN:
                require ROOT . '/route/(admin)/dashboard.php'; break;
        }
        break;
    //Public
    case '/about':
        require ROOT . '/route/(public)/index.php'; break;
    //Auth
    case '/register':
    case '/login':
        require ROOT . '/route/(auth)/index.php'; break;
    //Main
    case '/home':
        require ROOT . '/route/(main)/index.php'; break;
    //Admin
    case '/dashboard':
        require ROOT . '/route/(admin)/index.php'; break;
    //404
    default:
        http_response_code(404);
        require ROOT . '/src/sections/meta/screens/404Screen.php';
}