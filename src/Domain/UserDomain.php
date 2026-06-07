<?php

require_once ROOT . '/src/Core/Database.php';

class UserDomain
{
    private PDO $db;
    private $insert;
    private $select;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->insert = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $this->select = $this->db->prepare("SELECT * FROM users WHERE username = :username");
    }

    public function createUser(string $username, string $password): string|false
    {
        $password = password_hash($password, PASSWORD_DEFAULT);
        if (!$this->insert->execute(['username' => $username, 'password' => $password])) {
            return false;
        }
        return $this->insert->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function verifyUser($username, $password): string|false
    {
        $this->select->execute(['username' => $username]);
        $user = $this->select->fetch(PDO::FETCH_ASSOC);
        if (empty($user) || !password_verify($password, $user['password'])) {
            return false;
        }
        return $user['id'];
    }
}