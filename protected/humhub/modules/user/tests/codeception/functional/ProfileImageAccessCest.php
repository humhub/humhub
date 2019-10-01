<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace user\functional;

use humhub\modules\user\models\User;
use user\FunctionalTester;
use Yii;
use yii\helpers\Url;

class ProfileImageAccessCest
{
    public function testUploadAccessForCurrentUser(FunctionalTester $I)
    {
        $I->wantTo('ensure that system admins can access profile image upload if adminCanChangeUserProfileImages is active');

        $I->amUser2();

        $user2 = User::findOne(['id' => 3]);

        $I->amOnRoute('user/profile', ['cguid' => $user2->guid]);
        $I->seeElement('.image-upload-buttons');

        $I->amOnRoute('user/image/upload', ['cguid' => $user2->guid]);
        $I->seeResponseCodeIs(200);
    }

    public function testUploadAccessForOtherUser(FunctionalTester $I)
    {
        $I->wantTo('ensure that other users can not access profile image upload');

        $I->amUser1();

        $user2 = User::findOne(['id' => 3]);

        $I->amOnRoute('user/profile', ['cguid' => $user2->guid]);
        $I->dontSeeElement('.image-upload-buttons');

        $I->amOnRoute('user/image/upload', ['cguid' => $user2->guid]);
        $I->seeInCurrentUrl('login');
    }

    public function testUploadAccessForSystemAdminWithGlobalEditActive(FunctionalTester $I)
    {
        $I->wantTo('ensure that system admins can access profile image upload if adminCanChangeUserProfileImages is active');

        Yii::$app->getModule('user')->adminCanChangeUserProfileImages = true;

        $I->amAdmin();

        $user2 = User::findOne(['id' => 3]);

        $I->amOnRoute('user/profile', ['cguid' => $user2->guid]);
        $I->seeElement('.image-upload-buttons');

        $I->amOnRoute('user/image/upload', ['cguid' => $user2->guid]);
        $I->seeResponseCodeIs(200);
    }

    public function testUploadAccessForSystemAdminWithGlobalEditInactive(FunctionalTester $I)
    {
        $I->wantTo('ensure that system admins can not access profile image upload if adminCanChangeUserProfileImages is not active');

        Yii::$app->getModule('user')->adminCanChangeUserProfileImages = false;

        $I->amAdmin();

        $user2 = User::findOne(['id' => 3]);

        $I->amOnRoute('user/profile', ['cguid' => $user2->guid]);
        $I->dontSeeElement('.image-upload-buttons');

        $I->amOnRoute('user/image/upload', ['cguid' => $user2->guid]);
        $I->seeInCurrentUrl('login');
    }
}
