<?php

namespace App\Libraries;

use Exception;

class JWT
{
    /**
     * Menghasilkan token JWT baru
     * 
     * @param array  $payload       Data yang ingin disimpan dalam token
     * @param string $secret        Kunci rahasia untuk tanda tangan
     * @param int    $expirySeconds Waktu kedaluwarsa token dalam detik
     * @return string
     */
    public static function generate(array $payload, string $secret, int $expirySeconds = 3600): string
    {
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);

        $payload['iat'] = time();
        $payload['exp'] = time() + $expirySeconds;
        $payloadJson = json_encode($payload);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Mendekode dan memvalidasi token JWT
     * 
     * @param string $token  Token JWT
     * @param string $secret Kunci rahasia
     * @return array|false   Payload jika valid, false jika tidak valid/kedaluwarsa
     */
    public static function decode(string $token, string $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Validasi Tanda Tangan
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($expectedSignature, $base64UrlSignature)) {
            return false; // Signature mismatch
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        if (!$payload) {
            return false;
        }

        // Periksa Kedaluwarsa
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false; // Token expired
        }

        return $payload;
    }

    private static function base64UrlEncode(string $text): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
    }

    private static function base64UrlDecode(string $text): string
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $text);
        $len = strlen($base64) % 4;
        if ($len) {
            $base64 .= str_repeat('=', 4 - $len);
        }
        return base64_decode($base64);
    }
}
