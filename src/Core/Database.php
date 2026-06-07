<?php

class Database
{
    static private PDO $conn;

    public static function getConnection(): PDO
    {
        if (isset(self::$conn)) return self::$conn;
        $config = (require ROOT . '/config.php')['Database'];
        extract($config);
        return self::$conn = new PDO("$driver:host=$host;port=$port;dbname=$name", $user, $password);
    }

}