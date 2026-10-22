<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use Defuse\Crypto\Key;
use humhub\modules\oauth\Module;
use yii\base\InvalidConfigException;

/**
 * The signing key pair and the encryption key of the authorization server.
 *
 * Stored in the module settings so every node of a cluster shares them; module properties
 * (value or `file://` path) take precedence for deployments that manage secrets elsewhere.
 *
 * @since 1.20
 */
final class KeyStore
{
    public const SETTING_PRIVATE_KEY = 'privateKey';
    public const SETTING_PUBLIC_KEY = 'publicKey';
    public const SETTING_ENCRYPTION_KEY = 'encryptionKey';

    /**
     * Prefix of the `kid` header — how `TokenAuth` recognizes its own tokens.
     */
    public const KEY_ID_PREFIX = 'hh-';

    public function __construct(private readonly Module $module)
    {
    }

    public function getPrivateKey(): string
    {
        return $this->resolve($this->module->privateKey, self::SETTING_PRIVATE_KEY);
    }

    public function getPublicKey(): string
    {
        return $this->resolve($this->module->publicKey, self::SETTING_PUBLIC_KEY);
    }

    public function getEncryptionKey(): Key
    {
        return Key::loadFromAsciiSafeString(
            trim($this->resolve($this->module->encryptionKey, self::SETTING_ENCRYPTION_KEY)),
        );
    }

    public function getKeyId(): string
    {
        return self::KEY_ID_PREFIX . substr(hash('sha256', $this->getPublicKey()), 0, 8);
    }

    public function isOverridden(): bool
    {
        return $this->module->privateKey !== null || $this->module->publicKey !== null;
    }

    /**
     * Generates whatever key is not stored yet.
     */
    public function generateIfMissing(): void
    {
        $settings = $this->module->settings;

        if (empty($settings->get(self::SETTING_PRIVATE_KEY)) || empty($settings->get(self::SETTING_PUBLIC_KEY))) {
            $this->rotateKeyPair();
        }

        if (empty($settings->get(self::SETTING_ENCRYPTION_KEY))) {
            $settings->set(self::SETTING_ENCRYPTION_KEY, Key::createNewRandomKey()->saveToAsciiSafeString());
        }
    }

    /**
     * Replaces the stored key pair. Access tokens signed with the old key stop validating;
     * refresh tokens (encrypted, not signed) keep working.
     */
    public function rotateKeyPair(): void
    {
        [$privateKey, $publicKey] = self::createKeyPair();
        $this->module->settings->set(self::SETTING_PRIVATE_KEY, $privateKey);
        $this->module->settings->set(self::SETTING_PUBLIC_KEY, $publicKey);
    }

    /**
     * @return string[] PEM private key, PEM public key
     */
    public static function createKeyPair(): array
    {
        $resource = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        if ($resource === false || !openssl_pkey_export($resource, $privateKey)) {
            throw new InvalidConfigException('Could not generate the OAuth key pair: ' . openssl_error_string());
        }

        return [$privateKey, openssl_pkey_get_details($resource)['key']];
    }

    private function resolve(?string $override, string $setting): string
    {
        $value = $override ?? $this->module->settings->get($setting);

        if (is_string($value) && str_starts_with($value, 'file://')) {
            $content = @file_get_contents(substr($value, 7));
            if ($content === false) {
                throw new InvalidConfigException("The OAuth key file for '$setting' is not readable.");
            }
            $value = $content;
        }

        if (!is_string($value) || $value === '') {
            throw new InvalidConfigException("The OAuth key '$setting' is missing; run the migrations of the oauth module.");
        }

        return $value;
    }
}
