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
     * @return array{id: string, username: string, email: string, role: string, bio: string, profile_pic: ?string, user_lat: ?float, user_lng: ?float}|false
     */
    public function getProfile(string $userId): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, email, role, bio, profile_pic, user_lat, user_lng FROM users WHERE id = :id"
        );
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: false;
    }

    /**
     * Updates the user's username and bio fields.
     */
    public function updateProfile(string $userId, string $username, string $bio): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET username = :username, bio = :bio WHERE id = :id"
        );

        return $stmt->execute([
            ':username' => $username,
            ':bio'      => $bio,
            ':id'       => $userId,
        ]);
    }

    /**
     * Partially updates the user profile — only writes non-null fields.
     */
    public function updateProfilePartial(
        string $userId,
        ?string $username,
        ?string $bio,
        ?string $profilePic = null,
        ?float $userLat = null,
        ?float $userLng = null
    ): bool {
        $sets   = [];
        $params = [':id' => $userId];

        if ($username !== null) {
            $sets[]              = 'username = :username';
            $params[':username'] = $username;
        }
        if ($bio !== null) {
            $sets[]          = 'bio = :bio';
            $params[':bio']  = $bio;
        }
        if ($profilePic !== null) {
            $sets[]                 = 'profile_pic = :profile_pic';
            $params[':profile_pic'] = $profilePic;
        }
        if ($userLat !== null) {
            $sets[]              = 'user_lat = :user_lat';
            $params[':user_lat'] = $userLat;
        }
        if ($userLng !== null) {
            $sets[]              = 'user_lng = :user_lng';
            $params[':user_lng'] = $userLng;
        }

        if (empty($sets)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id'
        );

        return $stmt->execute($params);
    }
}