<?php
require_once __DIR__ . '/config.php';

class JWT {
    public static function encode(array $payload): string {
        $header  = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['exp'] = time() + JWT_EXPIRY;
        $body    = self::base64url(json_encode($payload));
        $sig     = self::base64url(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
        return "$header.$body.$sig";
    }

    public static function decode(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $body, $sig] = $parts;
        $expected = self::base64url(hash_hmac('sha256', "$header.$body", JWT_SECRET, true));
        if (!hash_equals($expected, $sig)) return null;
        $payload = json_decode(self::base64url_decode($body), true);
        if (!$payload || $payload['exp'] < time()) return null;
        return $payload;
    }

    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    private static function base64url_decode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
