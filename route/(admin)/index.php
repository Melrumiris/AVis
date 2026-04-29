<?php
global $request;

if (get_user_rank() != Rank::ADMIN) {
    http_response_code(401);
    require ROOT . '/src/sections/meta/screens/401Screen.php';
    exit();
}

switch ($request) {
    case '/dashboard':
        require ROOT . '/route/(admin)/dashboard.php'; break;
}