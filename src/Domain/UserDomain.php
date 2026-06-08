<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/Database.php';
require_once ROOT . '/src/Core/UserRole.php';

class UserDomain
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Creates a new user. Returns ['id' => uuid, 'role' => 'user'] on success, false if username exists.
     *
     * @return array{id: string, role: string}|false
     */
    public function createUser(string $username, string $password, string $email): array|false
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (username, password, email)
             VALUES (:username, :password, :email)
             ON CONFLICT (email) DO NOTHING
             RETURNING id, role"
        );

        $stmt->execute([
            ':username' => $username,
            ':password' => $hash,
            ':email'    => $email,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }

        return ['id' => $row['id'], 'role' => UserRole::from($row['role'])];
    }

    /**
     * Verifies user credentials. Returns ['id' => uuid, 'role' => role] on success, false on failure.
     *
     * @return array{id: string, role: string}|false
     */
    public function verifyUser(string $email, string $password): array|false
    {
        $stmt = $this->db->prepare("SELECT id, username, password, role FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($user) || !password_verify($password, $user['password'])) {
            return false;
        }

        return ['id' => $user['id'], 'role' => UserRole::from($user['role'])];
    }

    /**
     * Fetches the user profile by ID.
     *
     * @return array{id: string, username: string, email: string, role: string, bio: string}|false
     */
    public function getProfile(string $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, role, bio FROM users WHERE id = :id"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: false;
    }

    /**
     * Updates the user's email and bio fields.
     */
    public function updateProfile(string $userId, string $email, string $bio): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET email = :email, bio = :bio WHERE id = :id"
        );

        return $stmt->execute([
            ':email' => $email,
            ':bio'   => $bio,
            ':id'    => $userId,
        ]);
    }
}