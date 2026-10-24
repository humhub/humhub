<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\tests\codeception\unit;

use humhub\modules\oauth\components\TokenResolver;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\oauth\components\KeyStore;
use humhub\modules\oauth\Module;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use tests\codeception\_support\HumHubDbTestCase;

class TokenResolverTest extends HumHubDbTestCase
{
    protected function _before()
    {
        parent::_before();
        Module::instance()->getKeyStore()->generateIfMissing();
    }

    public function testAccessAndRefreshTokensResolveToTheirGrant()
    {
        $tokens = OAuthTestHelper::issueTokens(1);

        $this->assertTrue(TokenResolver::isOwnJwt($tokens['access_token']));
        $this->assertFalse(TokenResolver::isOwnJwt($tokens['refresh_token']));

        $byAccess = TokenResolver::resolveGrant($tokens['access_token']);
        $byRefresh = TokenResolver::resolveGrant($tokens['refresh_token']);

        $this->assertInstanceOf(Grant::class, $byAccess);
        $this->assertSame($byAccess->id, $byRefresh->id);
        $this->assertSame(1, $byAccess->user_id);
    }

    public function testForeignTokensResolveToNothing()
    {
        $this->assertFalse(TokenResolver::isOwnJwt('eyJhbGciOiJIUzUxMiJ9.eyJ1aWQiOjF9.sig'));
        $this->assertNull(TokenResolver::resolveGrant('not-a-token'));
    }

    public function testForgedAndTamperedTokensResolveToNothing()
    {
        $tokens = OAuthTestHelper::issueTokens(1);
        $jti = (new \Lcobucci\JWT\Token\Parser(new \Lcobucci\JWT\Encoding\JoseEncoder()))
            ->parse($tokens['access_token'])->claims()->get('jti');

        [$privateKey, $publicKey] = KeyStore::createKeyPair();
        $configuration = Configuration::forAsymmetricSigner(new Sha256(), InMemory::plainText($privateKey), InMemory::plainText($publicKey));
        $forged = $configuration->builder()
            ->identifiedBy($jti)
            ->withHeader('kid', 'hh-deadbeef')
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();

        $this->assertTrue(TokenResolver::isOwnJwt($forged));
        $this->assertNull(TokenResolver::resolveGrant($forged));

        $refresh = $tokens['refresh_token'];
        $tampered = substr($refresh, 0, -2) . ($refresh[-2] === '0' ? '1' : '0') . substr($refresh, -1);
        $this->assertNull(TokenResolver::resolveGrant($tampered));
    }
}
