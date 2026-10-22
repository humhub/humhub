<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use DateTimeImmutable;
use humhub\modules\oauth\entities\ClientEntity;
use humhub\modules\oauth\entities\ScopeEntity;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\repositories\AccessTokenRepository;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\repositories\RefreshTokenRepository;
use humhub\modules\oauth\repositories\ScopeRepository;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class RepositoriesTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();

        // The settings fixture is reset per test and drops the keys the migration stored
        \humhub\modules\oauth\Module::instance()->getKeyStore()->generateIfMissing();
    }

    private function systemGrant(): Grant
    {
        return (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), Client::findByClientId(Client::SYSTEM_CLIENT_ID), ['full']);
    }

    private function persistAccessToken(Grant $grant): string
    {
        $repository = new AccessTokenRepository();
        $token = $repository->getNewToken(new ClientEntity($grant->client), [new ScopeEntity('full')], $grant->guid);
        $token->setIdentifier(bin2hex(random_bytes(20)));
        $token->setExpiryDateTime(new DateTimeImmutable('+1 hour'));
        $repository->persistNewAccessToken($token);
        return $token->getIdentifier();
    }

    public function testClientRepositoryValidatesPublicAndConfidentialClients()
    {
        $repository = new ClientRepository();

        $this->assertTrue($repository->validateClient(Client::SYSTEM_CLIENT_ID, null, 'authorization_code'));
        $this->assertFalse($repository->validateClient(Client::SYSTEM_CLIENT_ID, 'secret', 'authorization_code'));
        $this->assertFalse($repository->validateClient(Client::SYSTEM_CLIENT_ID, null, 'client_credentials'));
        $this->assertFalse($repository->validateClient('unknown', null, 'authorization_code'));

        $client = new Client([
            'client_id' => 'confidential-test',
            'type' => Client::TYPE_ADMIN,
            'name' => 'Confidential',
            'secret_hash' => hash('sha256', 's3cret'),
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode(['content:read']),
            'is_active' => 1,
            'created_at' => time(),
        ]);
        $client->save(false);
        \humhub\modules\oauth\Module::instance()->settings->set('allowClients', true);

        $this->assertTrue($repository->validateClient('confidential-test', 's3cret', 'refresh_token'));
        $this->assertFalse($repository->validateClient('confidential-test', 'wrong', 'refresh_token'));
        $this->assertFalse($repository->validateClient('confidential-test', null, 'refresh_token'));
    }

    public function testScopeRepositoryKnowsOnlyRegisteredScopes()
    {
        $repository = new ScopeRepository();

        $this->assertSame('content:read', $repository->getScopeEntityByIdentifier('content:read')->getIdentifier());
        $this->assertNull($repository->getScopeEntityByIdentifier('nope'));
    }

    public function testAccessTokenRevocationFollowsTheGrant()
    {
        $grant = $this->systemGrant();
        $repository = new AccessTokenRepository();
        $jti = $this->persistAccessToken($grant);

        $this->assertFalse($repository->isAccessTokenRevoked($jti));
        $this->assertTrue($repository->isAccessTokenRevoked('unknown-jti'));

        $repository->revokeAccessToken($jti);
        $this->assertTrue($repository->isAccessTokenRevoked($jti));

        $other = $this->persistAccessToken($grant);
        (new GrantService())->revoke($grant);
        $this->assertTrue($repository->isAccessTokenRevoked($other));
        $this->assertNull(AccessToken::findOne(['id' => $other]));
    }

    public function testReusingARevokedRefreshTokenRevokesTheGrant()
    {
        $grant = $this->systemGrant();
        $grant->refresh();
        $jti = $this->persistAccessToken($grant);

        $refresh = new RefreshToken(['id' => 'rt-1', 'grant_id' => $grant->id, 'access_token_id' => $jti, 'expires_at' => time() + 3600, 'is_revoked' => 0]);
        $refresh->save(false);
        $repository = new RefreshTokenRepository();

        $this->assertFalse($repository->isRefreshTokenRevoked('rt-1'));

        $repository->revokeRefreshToken('rt-1');
        $this->assertTrue($repository->isRefreshTokenRevoked('rt-1'));
        $this->assertNull(Grant::findOne(['id' => $grant->id]), 'a reused refresh token revokes its grant');
    }

    public function testFinalizeScopesRejectsUnusableGrants()
    {
        $grant = $this->systemGrant();
        $repository = new ScopeRepository();
        $clientEntity = new ClientEntity($grant->client);

        $scopes = $repository->finalizeScopes([new ScopeEntity('full')], 'authorization_code', $clientEntity, $grant->guid);
        $this->assertSame(['full'], array_map(fn($s) => $s->getIdentifier(), $scopes));

        $this->expectException(\League\OAuth2\Server\Exception\OAuthServerException::class);
        $repository->finalizeScopes([new ScopeEntity('full')], 'authorization_code', $clientEntity, 'no-such-grant');
    }

    public function testAccessTokenCarriesIssuerAndKeyId()
    {
        $module = \humhub\modules\oauth\Module::instance();
        $grant = $this->systemGrant();
        $token = (new AccessTokenRepository())->getNewToken(new ClientEntity($grant->client), [new ScopeEntity('full')], $grant->guid);
        $token->setIdentifier(bin2hex(random_bytes(20)));
        $token->setExpiryDateTime(new DateTimeImmutable('+1 hour'));
        $token->setPrivateKey(new \League\OAuth2\Server\CryptKey($module->getKeyStore()->getPrivateKey(), null, false));

        [$header, $payload] = array_map(
            fn($part) => json_decode(base64_decode(strtr($part, '-_', '+/')), true),
            array_slice(explode('.', $token->toString()), 0, 2),
        );

        $this->assertStringStartsWith('hh-', $header['kid']);
        $this->assertSame($module->getKeyStore()->getKeyId(), $header['kid']);
        $this->assertSame($module->getIssuer(), $payload['iss']);
        $this->assertSame($grant->guid, $payload['sub']);
    }
}
