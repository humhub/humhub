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
use tests\codeception\_support\HumHubDbTestCase;

class TokenResolverTest extends HumHubDbTestCase
{
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
}
