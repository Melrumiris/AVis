<?php

declare(strict_types=1);

require_once ROOT . '/src/Core/JWT.php';
require_once ROOT . '/src/Core/JwtType.php';
require_once ROOT . '/src/Core/UserRole.php';
require_once ROOT . '/src/Responders/ErrorResponder.php';

class JwtAuth
{
    private string $header;
    private string $key;
    private int $accessExpiry;
    private int $refreshExpiry;

    public function __construct()
    {
        $config = (require ROOT . '/config.php')['JWT'];
        extract($config);
        $this->key = $secretKey;
        $this->accessExpiry = $accessExpiry;
        $this->refreshExpiry = $refreshExpiry;
        $header = [
            'alg' => $algorithm,
            'typ' => 'JWT'
        ];
        $this->header = JWT::base64UrlEncode(json_encode($header));
    }

    public function generate(string $UUID, UserRole $role, JwtType $type): JWT
    {
        $payload = [
            "sub" => $UUID,
            "role" => $role->value,
            "iat" => time(),
            "exp" => time() + ($type === JwtType::Access ? $this->accessExpiry : $this->refreshExpiry)
        ];
        return JWT::createToken($this->header, $payload, $this->key);
    }

    public function verify(JWT $token): bool
    {
        $parts = $token->split();
        if ($parts === null) return false;
        if ($parts['payload']->exp < time()) return false;
        $signature = hash_hmac('sha256', "{$parts['headerEncoded']}.{$parts['payloadEncoded']}", $this->key, true);
        return hash_equals($signature, $parts['signature']);
    }

    /**
     * Validates the Authorization bearer token from the current request.
     * Returns the decoded JWT payload on success.
     * Sends a 401 error response and exits on failure.
     */
    public function authenticateApiRequest(): object
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $authHeader = $headers['Authorization'];
            } elseif (isset($headers['authorization'])) {
                $authHeader = $headers['authorization'];
            }
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            (new ErrorResponder())->send(401, 'Missing or malformed Authorization header');
        }

        $rawToken = substr($authHeader, 7);
        if (!$this->verify(new JWT($rawToken))) {
            (new ErrorResponder())->send(401, 'Invalid or expired access token');
        }

        $parts = (new JWT($rawToken))->split();
        return $parts['payload'];
    }

    /**
     * Validates bearer token and ensures the user has admin role.
     * Returns the decoded JWT payload on success.
     * Sends 401/403 error response and exits on failure.
     */
    public function authenticateAdminRequest(): object
    {
        $payload = $this->authenticateApiRequest();

        if (($payload->role ?? '') !== UserRole::Admin->value) {
            (new ErrorResponder())->send(403, 'Forbidden: admin access required');
        }

        return $payload;
    }

    public function getRefreshExpiration(): int
    {
        return $this->refreshExpiry + time();
    }
}