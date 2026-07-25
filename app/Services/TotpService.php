<?php

namespace App\Services;

/**
 * Minimal RFC 6238 TOTP implementation (no external dependency) for account 2FA.
 * Compatible with standard authenticator apps (Google Authenticator, Authy, 1Password, etc).
 */
class TotpService
{
    protected const PERIOD = 30;

    protected const DIGITS = 6;

    public function generateSecret(int $length = 20): string
    {
        return $this->base32Encode(random_bytes($length));
    }

    public function provisioningUri(string $secret, string $email, string $issuer = 'Bitzlatoview'): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&period=%d&digits=%d',
            rawurlencode($issuer),
            rawurlencode($email),
            $secret,
            rawurlencode($issuer),
            self::PERIOD,
            self::DIGITS
        );
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = trim($code);
        if (! ctype_digit($code)) {
            return false;
        }

        $timeSlice = (int) floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->code($secret, $timeSlice + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    protected function code(string $secret, int $timeSlice): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0, $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = substr($hash, $offset, 4);
        $value = unpack('N', $truncated)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    protected function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(base_convert((string) ord($char), 10, 2), 8, '0', STR_PAD_LEFT);
        }

        $output = '';
        foreach (str_split($binary, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $output .= $alphabet[bindec($chunk)];
        }

        return $output;
    }

    protected function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $data = strtoupper(rtrim($data, '='));
        $binary = '';
        foreach (str_split($data) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) {
                continue;
            }
            $binary .= str_pad(base_convert((string) $pos, 10, 2), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $byte) {
            if (strlen($byte) < 8) {
                continue;
            }
            $bytes .= chr(bindec($byte));
        }

        return $bytes;
    }
}
