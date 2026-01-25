<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace space\acceptance;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use space\AcceptanceTester;
use Yii;

class SpaceChooserCest
{
    public function testChooserTranslations(AcceptanceTester $I)
    {
        $I->wantTo('ensure space chooser messages match Yii::t translations.');

//        $this->ensureUserWithoutSpaces('User3');
        $I->amUser3();
        $I->amOnDashboard();

        $I->click('#space-menu');
        $I->waitForElementVisible('#space-menu-search');

        $notMemberMessage = Yii::t('SpaceModule.chooser', 'You are not a member of or following any Spaces.');
        $minCharsMessage = Yii::t(
            'SpaceModule.chooser',
            'Please enter at least {count} characters to search Spaces.',
            ['count' => 2],
        );

        $I->waitForText($notMemberMessage, 10, '#space-menu-remote-search');
        $I->see($minCharsMessage, '#space-menu-remote-search');

        $I->click('#space-menu-search');
        $I->pressKey('#space-menu-search', 'z');
        $I->pressKey('#space-menu-search', 'z');

        $noSpacesMessage = Yii::t('SpaceModule.chooser', 'No Spaces found.');
        $I->waitForText($noSpacesMessage, 10, '#space-menu-remote-search');
    }

    private function ensureUserWithoutSpaces(string $username): void
    {
        $user = User::findOne(['username' => $username]);
        if ($user === null) {
            throw new \RuntimeException('Missing test user: ' . $username);
        }

        Membership::deleteAll(['user_id' => $user->id]);
        Follow::deleteAll(['user_id' => $user->id, 'object_model' => Space::class]);
        Yii::$app->cache->delete(Membership::USER_SPACES_CACHE_KEY . $user->id);
        Yii::$app->cache->delete(Membership::USER_SPACEIDS_CACHE_KEY . $user->id);
    }
}
