<?php

require_once ROOT . '/src/Core/JWT.php';
require_once ROOT . '/src/Core/JwtType.php';

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

    public function generate(string $UUID, string $role, JwtType $type): JWT
    {
        $payload = [
            "sub" => $UUID,
            "role" => $role,
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

    public function getRefreshExpiration(): int
    {
        return $this->refreshExpiry + time();
    }
}