<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace admin\functional;

use admin\FunctionalTester;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use Yii;

/**
 * The administration behind the sudo mode, when the setting says so (docs/develop/concept-sudo-mode.md)
 */
class SudoAdminCest
{
    public function _after(FunctionalTester $I)
    {
        $settings = Yii::$app->getModule('user')->settings;
        $settings->delete('auth.sudoAdmin');
        $settings->delete('auth.sudoIdleMinutes');
    }

    public function testTheAdministrationAsksForTheConfirmationOnlyWhenEnabled(FunctionalTester $I)
    {
        $settings = Yii::$app->getModule('user')->settings;

        // A programmatic login: the sudo mode is not active
        $I->amLoggedInAs(User::findOne(['id' => 1]));
        $I->amOnRoute('/admin/user');
        $I->seeCurrentUrlMatches('~admin%2Fuser~');
        $I->see('Users', '.page-header-title');

        $settings->set('auth.sudoAdmin', 1);
        $I->amOnRoute('/admin/user');
        $I->seeCurrentUrlMatches('~user%2Fsudo%2Fconfirm~');
        $I->see('Confirm your password');

        $I->submitForm('#sudo-confirm-form', ['SudoConfirmForm[password]' => 'admin&humhub@PASS%worD!']);
        $I->seeCurrentUrlMatches('~admin%2Fuser~');
        $I->see('Users', '.page-header-title');

        // The window slides with every admin page
        $I->amOnRoute('/admin/space');
        $I->see('Spaces', '.page-header-title');
    }

    public function testTheSettingsFormSavesTheSudoOptions(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/authentication');
        $I->seeElement('input[name="AuthenticationSettingsForm[sudoAdmin]"]');
        $I->seeInField('AuthenticationSettingsForm[sudoIdleMinutes]', '15');

        $I->submitForm('form[action*="authentication"]', [
            'AuthenticationSettingsForm[sudoAdmin]' => '1',
            'AuthenticationSettingsForm[sudoIdleMinutes]' => '30',
        ]);

        $settings = Yii::$app->getModule('user')->settings;
        Assert::assertSame('1', (string)$settings->get('auth.sudoAdmin'));
        Assert::assertSame('30', (string)$settings->get('auth.sudoIdleMinutes'));
    }
}
