<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use DateTimeImmutable;
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\entities\ClientEntity;
use humhub\modules\oauth\entities\ScopeEntity;
use humhub\modules\oauth\events\ScopesEvent;
use humhub\modules\oauth\models\AccessToken;
use humhub\modules\oauth\models\AuthCode;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\RefreshToken;
use humhub\modules\oauth\models\Scope;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\repositories\AccessTokenRepository;
use humhub\modules\oauth\repositories\AuthCodeRepository;
use humhub\modules\oauth\repositories\ClientRepository;
use humhub\modules\oauth\repositories\RefreshTokenRepository;
use humhub\modules\oauth\repositories\ScopeRepository;
use humhub\modules\oauth\services\GrantService;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\user\models\User;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

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

        $this->assertSame($client->id, $repository->authenticateClient('confidential-test', 's3cret')?->id);
        $this->assertNull($repository->authenticateClient('confidential-test', 'wrong'));
        $this->assertNull($repository->authenticateClient(Client::SYSTEM_CLIENT_ID, 'secret'));
    }

    public function testScopeRepositoryAcceptsEveryWellFormedScope()
    {
        $repository = new ScopeRepository();

        $this->assertSame('content:read', $repository->getScopeEntityByIdentifier('content:read')->getIdentifier());
        $this->assertSame('gone:read', $repository->getScopeEntityByIdentifier('gone:read')->getIdentifier());
        $this->assertNull($repository->getScopeEntityByIdentifier('Bad Scope'));
        $this->assertNull($repository->getScopeEntityByIdentifier(':read'));
        $this->assertNull($repository->getScopeEntityByIdentifier(str_repeat('a', 81)));
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

        // The grant decides, not the requested list
        $scopes = $repository->finalizeScopes([new ScopeEntity('content:write')], 'refresh_token', $entity, $grant->guid);
        $this->assertSame(['content:read'], array_map(fn($s) => $s->getIdentifier(), $scopes));

        $client->updateAttributes(['scopes' => json_encode([])]);
        $this->assertSame([], $repository->finalizeScopes([new ScopeEntity('content:read')], 'refresh_token', $entity, $grant->guid));
    }

    public function testRefreshSurvivesADisabledScopeModule()
    {
        $client = $this->adminClient(['content:read', 'gone:read']);
        $handler = static function (ScopesEvent $event) {
            $event->scopes[] = new Scope('gone:read', 'Read something');
        };
        Event::on(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, $handler);
        ScopeRegistry::reset();

        try {
            $tokens = OAuthTestHelper::issueTokens(1, $client->client_id, ['content:read', 'gone:read']);
        } finally {
            Event::off(ScopeRegistry::class, ScopeRegistry::EVENT_COLLECT_SCOPES, $handler);
            ScopeRegistry::reset();
        }
        $this->assertArrayHasKey('refresh_token', $tokens);

        $refreshed = OAuthTestHelper::refresh($tokens['refresh_token'], $client->client_id);

        $this->assertArrayHasKey('access_token', $refreshed, json_encode($refreshed));
        $this->assertSame(['content:read'], $this->jwtScopes($refreshed['access_token']));
        $this->assertSame(1, (int)Grant::find()->where(['client_id' => $client->id])->count());
    }

    public function testRefreshSurvivesClientScopesNarrowedToNone()
    {
        $client = $this->adminClient(['content:read']);
        $tokens = OAuthTestHelper::issueTokens(1, $client->client_id, ['content:read']);

        $client->updateAttributes(['scopes' => json_encode([])]);
        $refreshed = OAuthTestHelper::refresh($tokens['refresh_token'], $client->client_id);

        $this->assertArrayHasKey('access_token', $refreshed, json_encode($refreshed));
        $this->assertSame([], $this->jwtScopes($refreshed['access_token']));
        $this->assertSame(1, (int)Grant::find()->where(['client_id' => $client->id])->count(), 'the grant is kept');
    }

    private function adminClient(array $scopes): Client
    {
        $client = new Client([
            'client_id' => 'refresh-test-' . bin2hex(random_bytes(4)),
            'type' => Client::TYPE_ADMIN,
            'name' => 'Refresh',
            'redirect_uris' => json_encode([OAuthTestHelper::REDIRECT_URI]),
            'scopes' => json_encode($scopes),
            'is_active' => 1,
            'created_at' => time(),
        ]);
        $client->save(false);
        Module::instance()->settings->set('allowClients', true);

        return $client;
    }

    private function jwtScopes(string $jwt): array
    {
        $payload = json_decode(base64_decode(strtr(explode('.', $jwt)[1], '-_', '+/')), true);
        return $payload['scopes'];
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
