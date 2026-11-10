<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\api;

use ApiTester;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\Module as OAuthModule;
use humhub\modules\oauth\tests\codeception\_support\OAuthTestHelper;
use humhub\modules\user\components\SudoMode;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The administration's endpoints behind the sudo mode, when the setting says so
 * (docs/develop/concept-sudo-mode.md): the browser session needs it like the administration's pages, a token does not.
 */
class AdminSudoApiCest
{
    public function _before(): void
    {
        Yii::$app->getModule('user')->settings->set('auth.sudoAdmin', 1);
    }

    public function _after(): void
    {
        Yii::$app->getModule('user')->settings->delete('auth.sudoAdmin');
        // Rows an API Cest writes outlive it
        Grant::deleteAll(['user_id' => 1]);
        User::updateAll(['status' => User::STATUS_ENABLED], ['id' => 2]);
    }

    private function withCsrf(ApiTester $I): void
    {
        $rawToken = Yii::$app->security->generateRandomString();
        $I->setCookie('_csrf', $rawToken);
        $I->haveHttpHeader('X-CSRF-Token', Yii::$app->security->maskToken($rawToken));
    }

    public function testTheSessionWithoutTheSudoModeGetsA401WithTheConfirmUrl(ApiTester $I)
    {
        // A programmatic login is no interactive one: the sudo mode is not active
        $I->amLoggedInAs(1);
        $this->withCsrf($I);

        $I->sendGet('admin/user');
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(['sudo' => true]);
        Assert::assertMatchesRegularExpression('~sudo(/|%2F)confirm~', (string)$I->grabHttpHeader('X-Sudo-Required'));

        $I->sendPost('admin/user/2/disable');
        $I->seeResponseCodeIs(401);
        Assert::assertEquals(User::STATUS_ENABLED, User::findOne(2)->status);

        $I->sendPost('marketplace/licence-key', ['licenceKey' => 'x']);
        $I->seeResponseCodeIs(401);
    }

    public function testTheSessionInTheSudoModePasses(ApiTester $I)
    {
        $I->amLoggedInAs(1);
        SudoMode::forCurrentUser()->touch();
        $this->withCsrf($I);

        $I->sendGet('admin/user');
        $I->seeResponseCodeIs(200);

        $I->sendPost('admin/user/2/disable');
        $I->seeResponseCodeIs(200);
        Assert::assertEquals(User::STATUS_DEACTIVATED, User::findOne(2)->status);
    }

    public function testATokenIsNotAffected(ApiTester $I)
    {
        OAuthModule::instance()->getKeyStore()->generateIfMissing();
        $tokens = OAuthTestHelper::issueTokens(1);

        $I->haveHttpHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        $I->sendGet('admin/user');
        $I->seeResponseCodeIs(200);
    }
}
