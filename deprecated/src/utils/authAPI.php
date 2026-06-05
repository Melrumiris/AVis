<?php
ini_set('display_errors', 1); error_reporting(-1);
enum Rank {
    case GUEST;
    case USER;
    case ADMIN;
}
function get_user_rank(){
    global $type;
    switch($type){
        case Rank::GUEST: return Rank::GUEST;
        case Rank::USER: return Rank::USER;
        case Rank::ADMIN: return Rank::ADMIN;
    }
}