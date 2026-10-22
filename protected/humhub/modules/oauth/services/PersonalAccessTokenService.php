<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\services;

use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module;
use humhub\modules\user\models\User;
use yii\base\InvalidArgumentException;

/**
 * Personal access tokens: `hhpat_` + 32 random base62 characters + a 6-character base62
 * CRC32 of the random part. Only the SHA-256 of the whole token is stored.
 *
 * @since 1.20
 */
class PersonalAccessTokenService
{
    public const PREFIX = 'hhpat_';

    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    private const RANDOM_LENGTH = 32;
    private const CHECKSUM_LENGTH = 6;
    private const LIFETIMES = [7, 30, 90, 365];

    /**
     * @param string[] $scopeIds
     * @param int|null $lifetimeDays null = never expires (only when unlimited is allowed)
     * @return array{0: string, 1: Grant} the token (shown once) and its grant
     */
    public function create(User $user, string $name, array $scopeIds, ?int $lifetimeDays): array
    {
        if (!in_array($lifetimeDays, $this->getLifetimeOptions(), true)) {
            throw new InvalidArgumentException('Lifetime not allowed.');
        }

        $random = self::randomBase62(self::RANDOM_LENGTH);
        $token = self::PREFIX . $random . self::checksum($random);

        $grant = new Grant([
            'user_id' => $user->id,
            'type' => Grant::TYPE_PAT,
            'name' => mb_substr($name, 0, 100),
            'token_hash' => hash('sha256', $token),
            'token_hint' => substr($token, -4),
            'expires_at' => $lifetimeDays === null ? null : time() + $lifetimeDays * 86400,
        ]);
        $grant->setScopeIds($scopeIds);
        $grant->save(false);

        return [$token, $grant];
    }

    public function find(string $token): ?Grant
    {
        if (!self::isWellFormed($token)) {
            return null;
        }

        return Grant::findOne(['type' => Grant::TYPE_PAT, 'token_hash' => hash('sha256', $token)]);
    }

    /**
     * @return array<int|null> lifetimes in days the current settings allow; null = never
     */
    public function getLifetimeOptions(): array
    {
        $max = Module::instance()->getPatMaxLifetimeDays();
        if ($max === 0) {
            return [...self::LIFETIMES, null];
        }

        $options = array_values(array_filter(self::LIFETIMES, static fn(int $days) => $days <= $max));
        return $options !== [] ? $options : [$max];
    }

    public static function isWellFormed(string $token): bool
    {
        $length = strlen(self::PREFIX) + self::RANDOM_LENGTH + self::CHECKSUM_LENGTH;
        if (strlen($token) !== $length || !str_starts_with($token, self::PREFIX) || !ctype_alnum(substr($token, strlen(self::PREFIX)))) {
            return false;
        }

        $random = substr($token, strlen(self::PREFIX), self::RANDOM_LENGTH);
        return hash_equals(self::checksum($random), substr($token, -self::CHECKSUM_LENGTH));
    }

    private static function checksum(string $random): string
    {
        return str_pad(self::toBase62(crc32($random)), self::CHECKSUM_LENGTH, '0', STR_PAD_LEFT);
    }

    private static function randomBase62(int $length): string
    {
        $result = '';
        while (strlen($result) < $length) {
            $byte = ord(random_bytes(1));
            // rejection sampling keeps the distribution uniform
            if ($byte < 248) {
                $result .= self::ALPHABET[$byte % 62];
            }
        }
        return $result;
    }

    private static function toBase62(int $number): string
    {
        $result = '';
        do {
            $result = self::ALPHABET[$number % 62] . $result;
            $number = intdiv($number, 62);
        } while ($number > 0);
        return $result;
    }
}
