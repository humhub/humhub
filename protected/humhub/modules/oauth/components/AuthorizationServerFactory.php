<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components;

use DateInterval;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\repositories\AccessTokenRepository;
use humhub\modules\oauth\repositories\AuthCodeRepository;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\repositories\RefreshTokenRepository;
use humhub\modules\oauth\repositories\ScopeRepository;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\ResourceServer;

/**
 * Builds the league servers from the module configuration.
 *
 * @since 1.20
 */
final class AuthorizationServerFactory
{
    public static function createAuthorizationServer(): AuthorizationServer
    {
        $module = Module::instance();
        $keyStore = $module->getKeyStore();

        $server = new AuthorizationServer(
            new ClientRepository(),
            new AccessTokenRepository(),
            new ScopeRepository(),
            new CryptKey($keyStore->getPrivateKey(), null, false),
            $keyStore->getEncryptionKey(),
        );

        $accessTokenTtl = new DateInterval('PT' . $module->accessTokenLifetime . 'S');
        $refreshTokenTtl = new DateInterval('P' . $module->getRefreshTokenLifetimeDays() . 'D');

        $authCodeGrant = new AuthCodeGrant(
            new AuthCodeRepository(),
            new RefreshTokenRepository(),
            new DateInterval('PT' . $module->authCodeLifetime . 'S'),
        );
        $authCodeGrant->setRefreshTokenTTL($refreshTokenTtl);
        $server->enableGrantType($authCodeGrant, $accessTokenTtl);

        $refreshTokenGrant = new RefreshTokenGrant(new RefreshTokenRepository());
        $refreshTokenGrant->setRefreshTokenTTL($refreshTokenTtl);
        $server->enableGrantType($refreshTokenGrant, $accessTokenTtl);

        return $server;
    }

    public static function createResourceServer(): ResourceServer
    {
        return new ResourceServer(
            new AccessTokenRepository(),
            new CryptKey(Module::instance()->getKeyStore()->getPublicKey(), null, false),
        );
    }
}
