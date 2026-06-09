<?php
class JWT
{
    public string $token;

    public static function base64UrlDecode(string $data): string
    {
        $data = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        return base64_decode($data);
    }

    public static function base64UrlEncode(string $data): string
    {
        $data = base64_encode($data);
        return str_replace(['+', '/', '='], ['-', '_', ''], $data);
    }

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function split(): ?array
    {
        $parts = explode('.', $this->token);
        if (count($parts) !== 3) {
            return null;
        }
        return [
            'headerEncoded' => $parts[0],
            'payloadEncoded' => $parts[1],
            'payload' => json_decode(self::base64UrlDecode($parts[1])),
            'signature' => self::base64UrlDecode($parts[2]),
        ];
    }

    public static function createToken(string $headerEncoded, array $payload, string $secret): JWT
    {
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);
        return new JWT("$headerEncoded.$payloadEncoded.$signatureEncoded");
    }

    public function toString(): string
    {
        return $this->token;
    }
}