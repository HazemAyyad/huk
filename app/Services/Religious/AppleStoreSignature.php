<?php

namespace App\Services\Religious;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class AppleStoreSignature
{
    /** Validate a complete chain anchored in the bundled Apple root, then ES256. Never trust a device-decoded payload. */
    public function decode(string $value): array
    {
        $parts = explode('.', $value);
        if (count($parts) !== 3 || strlen($value) > 50000) {
            throw new RuntimeException('apple_signature');
        }
        $header = json_decode(JWT::urlsafeB64Decode($parts[0]), true, 16, JSON_THROW_ON_ERROR);
        $chain = $header['x5c'] ?? [];
        if (($header['alg'] ?? '') !== 'ES256' || count($chain) !== 3) {
            throw new RuntimeException('apple_signature');
        }
        $pem = [];
        foreach ($chain as $cert) {
            if (! is_string($cert) || base64_decode($cert, true) === false) {
                throw new RuntimeException('apple_certificate');
            }
            $pem[] = "-----BEGIN CERTIFICATE-----\n".chunk_split($cert, 64, "\n")."-----END CERTIFICATE-----\n";
        }
        $root = config('religious_assistant.ios.root_path');
        if (! is_string($root) || ! is_file($root) || openssl_x509_fingerprint($pem[2], 'sha256') !== openssl_x509_fingerprint(file_get_contents($root), 'sha256')) {
            throw new RuntimeException('apple_root');
        }
        $leaf = openssl_x509_parse($pem[0]);
        $intermediate = openssl_x509_parse($pem[1]);
        if (! isset($leaf['extensions']['1.2.840.113635.100.6.11.1'], $intermediate['extensions']['1.2.840.113635.100.6.2.1'])) {
            throw new RuntimeException('apple_certificate');
        }
        $untrusted = tempnam(sys_get_temp_dir(), 'haq-apple-chain-');
        try {
            if (! $untrusted || file_put_contents($untrusted, $pem[1]) === false || openssl_x509_checkpurpose($pem[0], X509_PURPOSE_ANY, [$root], $untrusted) !== true) {
                throw new RuntimeException('apple_chain');
            }
        } finally {
            if (is_string($untrusted) && is_file($untrusted)) {
                unlink($untrusted);
            }
        }
        $key = openssl_pkey_get_public($pem[0]);
        $details = $key ? openssl_pkey_get_details($key) : false;
        if (! $details || ($details['ec']['curve_name'] ?? '') !== 'prime256v1') {
            throw new RuntimeException('apple_key');
        }
        $data = (array) JWT::decode($value, new Key($details['key'], 'ES256'));
        if (! is_int($data['signedDate'] ?? null) || $data['signedDate'] > now()->getTimestampMs() + 60000) {
            throw new RuntimeException('apple_date');
        }

        return $data;
    }
}
