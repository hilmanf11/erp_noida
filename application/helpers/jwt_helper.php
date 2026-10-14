<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt {
    // Fungsi untuk encode (Membuat Token)
    public static function encode($payload, $key) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        
        // Base64Url Encode Header & Payload
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Membuat Signature
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    // Fungsi untuk decode (Memverifikasi Token)
    public static function decode($token, $key) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($header, $payload, $signature) = $parts;

        // Cek ulang signature
        $validSignature = hash_hmac('sha256', $header . "." . $payload, $key, true);
        if (self::base64UrlEncode($validSignature) !== $signature) return false;

        $data = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        
        // Cek apakah token kadaluwarsa
        if (isset($data['exp']) && time() > $data['exp']) return false;

        return $data;
    }

    // Fungsi helper Base64Url (standar JWT)
    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}