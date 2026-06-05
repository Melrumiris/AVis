<?php

namespace src\Core;
class Database
{
    static private PDO $conn;

    public static function getConnection(): PDO
    {
        if (isset(self::$conn)) return self::$conn;
        extract((require ROOT . '/config.php')['Database']);
        return self::$conn = new PDO("$driver:host=$host;port=$port;dbname=$name", $user, $password);
    }

}