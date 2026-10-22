<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\Module;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\modules\user\models\User;

/**
 * Personal access tokens on `/api/v2`.
 */
class OAuthPersonalAccessTokenCest
{
    public function _before()
    {
        Module::instance()->settings->set('allowPersonalAccessTokens', true);
    }

    public function _after()
    {
        Module::instance()->settings->delete('allowPersonalAccessTokens');
        Module::instance()->settings->delete('patScopes');
    }

    public function testTokenReachesItsScopes(ApiTester $I)
    {
        [$token, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 1]), 'x', ['content:read'], 30);

        try {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(200);
            $I->sendGet('account');
            $I->seeResponseCodeIs(403);
        } finally {
            $grant->delete();
        }
    }

    public function testDisabledTokensMalformedAndUnknownTokensFail(ApiTester $I)
    {
        [$token, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 1]), 'x', ['content:read'], 30);

        try {
            Module::instance()->settings->set('allowPersonalAccessTokens', false);
            $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(401);

            $I->haveHttpHeader('Authorization', 'Bearer hhpat_' . str_repeat('A', 38));
            $I->sendGet('content/1/comments');
            $I->seeResponseCodeIs(401);
        } finally {
            $grant->delete();
        }
    }

    public function testFullOnlyWhenTheAdministratorAllowsIt(ApiTester $I)
    {
        [$token, $grant] = (new PersonalAccessTokenService())->create(User::findOne(['id' => 1]), 'x', ['full'], 30);

        try {
            $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
            $I->sendGet('account');
            $I->seeResponseCodeIs(403);

            Module::instance()->settings->set('patScopes', json_encode(['full', 'content:read']));
            $I->sendGet('account');
            $I->seeResponseCodeIs(200);
        } finally {
            $grant->delete();
        }
    }
}
