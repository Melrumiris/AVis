<?php
global $request;

if (get_user_rank() == Rank::GUEST) {
    header('Location: /login');
}
switch($request) {
    case '/home':
        require ROOT . '/route/(main)/home.php'; break;
}
