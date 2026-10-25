<?php

final class CookieSessionHandler implements SessionHandlerInterface
{
    private const COOKIE_NAME = 'LEDGERSESSID_DATA';
    private const CIPHER = 'aes-256-gcm';
    private string $key;

    public function __construct(string $secret, private bool $secureCookie)
    {
        $this->key = hash('sha256', "ledger-session-v1\0" . $secret, true);
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $encoded = $_COOKIE[self::COOKIE_NAME] ?? '';
        if (!is_string($encoded) || !preg_match('/^[A-Za-z0-9_-]+$/D', $encoded)) {
            return '';
        }

        $encoded = strtr($encoded, '-_', '+/');
        $encoded .= str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        $payload = base64_decode($encoded, true);
        if ($payload === false || strlen($payload) < 28) {
            return '';
        }

        $data = openssl_decrypt(
            substr($payload, 28),
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            substr($payload, 0, 12),
            substr($payload, 12, 16),
            'ledger-session-v1'
        );

        return $data === false ? '' : $data;
    }

    public function write(string $id, string $data): bool
    {
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $data,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            'ledger-session-v1'
        );
        if ($ciphertext === false) {
            return false;
        }

        $value = rtrim(strtr(base64_encode($nonce . $tag . $ciphertext), '+/', '-_'), '=');

        return setcookie(self::COOKIE_NAME, $value, [
            'expires' => time() + (int)ini_get('session.gc_maxlifetime'),
            'path' => '/',
            'secure' => $this->secureCookie,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public function destroy(string $id): bool
    {
        return setcookie(self::COOKIE_NAME, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => $this->secureCookie,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }
}
