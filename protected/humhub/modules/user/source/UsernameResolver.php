<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2024 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\source;

use humhub\modules\user\models\User;

/**
 * UsernameResolver resolves or generates a username from user attributes
 * according to the configured UserSource strategy.
 *
 * @since 1.19
 */
class UsernameResolver
{
    /**
     * Maximum number of suffix attempts before giving up on auto-generation.
     */
    private const MAX_SUFFIX = 99;

    /**
     * Resolves a username according to the given strategy.
     *
     * Returns the resolved username string, or null if:
     * - strategy is USERNAME_REQUIRE and no username is in attributes
     * - strategy is USERNAME_AUTO_OR_ERROR and a unique name cannot be found
     */
    public function resolve(array $attributes, string $strategy): ?string
    {
        if (!empty($attributes['username'])) {
            return $this->ensureUnique($attributes['username'], $strategy);
        }

        return match ($strategy) {
            UserSourceInterface::USERNAME_REQUIRE => null,
            UserSourceInterface::USERNAME_AUTO_GENERATE => $this->generate($attributes),
            UserSourceInterface::USERNAME_AUTO_OR_ERROR => $this->generateOrFail($attributes),
            default => null,
        };
    }

    private function generate(array $attributes): string
    {
        $base = $this->deriveBase($attributes);
        return $this->makeUnique($base);
    }

    private function generateOrFail(array $attributes): ?string
    {
        $base = $this->deriveBase($attributes);
        $candidate = $this->makeUnique($base);
        // makeUnique returns null if MAX_SUFFIX exceeded
        return $candidate;
    }

    private function ensureUnique(string $username, string $strategy): ?string
    {
        if (!User::find()->where(['username' => $username])->exists()) {
            return $username;
        }

        if ($strategy === UserSourceInterface::USERNAME_AUTO_OR_ERROR) {
            return null;
        }

        return $this->makeUnique($username);
    }

    private function deriveBase(array $attributes): string
    {
        if (!empty($attributes['email'])) {
            $base = strstr($attributes['email'], '@', true);
        } elseif (!empty($attributes['firstname']) && !empty($attributes['lastname'])) {
            $base = strtolower($attributes['firstname'] . '.' . $attributes['lastname']);
        } elseif (!empty($attributes['firstname'])) {
            $base = strtolower($attributes['firstname']);
        } else {
            $base = 'user';
        }

        // Sanitize: keep only alphanumeric, dots, underscores, hyphens
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', $base);
        $base = trim($base, '._-');

        return $base ?: 'user';
    }

    private function makeUnique(string $base): ?string
    {
        if (!User::find()->where(['username' => $base])->exists()) {
            return $base;
        }

        for ($i = 2; $i <= self::MAX_SUFFIX; $i++) {
            $candidate = $base . '_' . $i;
            if (!User::find()->where(['username' => $candidate])->exists()) {
                return $candidate;
            }
        }

        return null;
    }
}
