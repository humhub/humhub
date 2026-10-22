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
use humhub\modules\oauth\models\AuthCode;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\repositories\AccessTokenRepository;
use humhub\modules\oauth\repositories\AuthCodeRepository;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\repositories\RefreshTokenRepository;
use humhub\modules\oauth\repositories\ScopeRepository;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\user\models\User;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use tests\codeception\_support\HumHubDbTestCase;

class RepositoriesTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();

        // The settings fixture is reset per test and drops the keys the migration stored
        Module::instance()->getKeyStore()->generateIfMissing();
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
        Module::instance()->settings->set('allowClients', true);

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
        $jti = $this->persistAccessToken($grant);

        $refresh = new RefreshToken(['id' => 'rt-1', 'grant_id' => $grant->id, 'access_token_id' => $jti, 'expires_at' => time() + 3600, 'is_revoked' => 0]);
        $refresh->save(false);
        $repository = new RefreshTokenRepository();

        $this->assertFalse($repository->isRefreshTokenRevoked('rt-1'));

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

        $this->expectException(OAuthServerException::class);
        $repository->finalizeScopes([new ScopeEntity('full')], 'authorization_code', $clientEntity, 'no-such-grant');
    }

    public function testAccessTokenCarriesIssuerAndKeyId()
    {
        $module = Module::instance();
        $grant = $this->systemGrant();
        $token = (new AccessTokenRepository())->getNewToken(new ClientEntity($grant->client), [new ScopeEntity('full')], $grant->guid);
        $token->setIdentifier(bin2hex(random_bytes(20)));
        $token->setExpiryDateTime(new DateTimeImmutable('+1 hour'));
        $token->setPrivateKey(new CryptKey($module->getKeyStore()->getPrivateKey(), null, false));

        [$header, $payload] = array_map(
            fn($part) => json_decode(base64_decode(strtr($part, '-_', '+/')), true),
            array_slice(explode('.', $token->toString()), 0, 2),
        );

        $this->assertStringStartsWith('hh-', $header['kid']);
        $this->assertSame($module->getKeyStore()->getKeyId(), $header['kid']);
        $this->assertSame($module->getIssuer(), $payload['iss']);
        $this->assertSame($grant->guid, $payload['sub']);
    }

    public function testReusingAnAuthCodeRevokesTheGrant()
    {
        $grant = $this->systemGrant();
        (new AuthCode(['id' => 'ac-1', 'grant_id' => $grant->id, 'expires_at' => time() + 60, 'is_revoked' => 0]))->save(false);
        $repository = new AuthCodeRepository();

        $this->assertFalse($repository->isAuthCodeRevoked('ac-1'));
        $this->assertTrue($repository->isAuthCodeRevoked('ac-1'));
        $this->assertNull(Grant::findOne(['id' => $grant->id]));
    }

    public function testFinalizeScopesNarrowsToTheEffectiveScopes()
    {
        $client = new Client([
            'client_id' => 'narrow-test',
            'type' => Client::TYPE_ADMIN,
            'name' => 'Narrow',
            'redirect_uris' => json_encode(['https://example.org/cb']),
            'scopes' => json_encode(['content:read']),
            'is_active' => 1,
            'created_at' => time(),
        ]);
        $client->save(false);
        Module::instance()->settings->set('allowClients', true);
        $grant = (new GrantService())->createOAuthGrant(User::findOne(['id' => 1]), $client, ['content:read', 'content:write']);
        $repository = new ScopeRepository();
        $entity = new ClientEntity($client);

        $scopes = $repository->finalizeScopes([new ScopeEntity('content:read'), new ScopeEntity('content:write')], 'authorization_code', $entity, $grant->guid);
        $this->assertSame(['content:read'], array_map(fn($s) => $s->getIdentifier(), $scopes));

        $this->expectException(OAuthServerException::class);
        $repository->finalizeScopes([new ScopeEntity('content:write')], 'authorization_code', $entity, $grant->guid);
    }

    public function testExpiredAccessTokenIsRevoked()
    {
        $jti = $this->persistAccessToken($this->systemGrant());
        AccessToken::updateAll(['expires_at' => time() - 10], ['id' => $jti]);

        $this->assertTrue((new AccessTokenRepository())->isAccessTokenRevoked($jti));
    }

    public function testClientEntityRejectsUnsupportedGrantTypes()
    {
        $entity = new ClientEntity(Client::findByClientId(Client::SYSTEM_CLIENT_ID));

        $this->assertTrue($entity->supportsGrantType('authorization_code'));
        $this->assertTrue($entity->supportsGrantType('refresh_token'));
        $this->assertFalse($entity->supportsGrantType('client_credentials'));
    }
}
