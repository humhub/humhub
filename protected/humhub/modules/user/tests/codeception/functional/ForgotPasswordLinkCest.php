<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\functional;

use tests\codeception\_pages\LoginPage;
use user\FunctionalTester;
use Yii;

class ForgotPasswordLinkCest
{
    public function testDefaultForgotPasswordLink(FunctionalTester $I)
    {
        $I->wantTo('ensure that default forgot password link works');
        LoginPage::openBy($I);
        $I->see('Forgot your password?');
        $I->click('Forgot your password?');
        $I->see('Password recovery');

    }

    public function testDisabledForgotPasswordLink(FunctionalTester $I)
    {
        $I->wantTo('ensure that forgot password link missing when disabled');
        Yii::$app->getModule('user')->passwordRecoveryRoute = null;
        LoginPage::openBy($I);
        $I->dontSee('Forgot your password?');
    }

    public function testExternalForgotPasswordLink(FunctionalTester $I)
    {
        $I->wantTo('ensure that external forgot password link works');
        Yii::$app->getModule('user')->passwordRecoveryRoute = 'https://some.external.link';
        LoginPage::openBy($I);
        $I->seeLink('Forgot your password?', 'https://some.external.link');
    }
}
