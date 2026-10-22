<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use Defuse\Crypto\Crypto;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\Module;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Throwable;

/**
 * @since 1.20
 */
final class TokenResolver
{
    /**
     * Whether the string is a JWT whose `kid` marks it as issued by this server. Signature and
     * validity are not checked here.
     */
    public static function isOwnJwt(string $token): bool
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $header = json_decode((string)base64_decode(strtr($parts[0], '-_', '+/'), true), true);

        return is_array($header) && is_string($header['kid'] ?? null)
            && str_starts_with($header['kid'], KeyStore::KEY_ID_PREFIX);
    }

    /**
     * The grant an access or refresh token of this server belongs to, or `null`.
     */
    public static function resolveGrant(string $token): ?Grant
    {
        $row = self::isOwnJwt($token)
            ? AccessToken::findOne(['id' => self::verifiedJwtId($token)])
            : RefreshToken::findOne(['id' => self::refreshTokenId($token)]);

        return $row?->grant;
    }

    private static function verifiedJwtId(string $token): ?string
    {
        try {
            $keyStore = Module::instance()->getKeyStore();
            $configuration = Configuration::forAsymmetricSigner(
                new Sha256(),
                InMemory::plainText($keyStore->getPrivateKey()),
                InMemory::plainText($keyStore->getPublicKey()),
            );
            $parsed = $configuration->parser()->parse($token);
            if (!$parsed instanceof UnencryptedToken
                || !$configuration->validator()->validate($parsed, new SignedWith($configuration->signer(), $configuration->verificationKey()))) {
                return null;
            }
            return $parsed->claims()->get('jti');
        } catch (Throwable) {
            return null;
        }
    }

    private static function refreshTokenId(string $token): ?string
    {
        try {
            $payload = json_decode(Crypto::decrypt($token, Module::instance()->getKeyStore()->getEncryptionKey()), true);
            return is_array($payload) ? ($payload['refresh_token_id'] ?? null) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
