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
        $this->ensureUserWithoutSpaces('User3');
        $I->wantTo('ensure space chooser js messages match Yii::t translations for language EN.');
        $this->checkChooserTranslations($I, 'en-US');
        $I->wantTo('ensure space chooser js messages match Yii::t translations for language DE.');
        $this->checkChooserTranslations($I, 'de');
    }

    private function checkChooserTranslations(AcceptanceTester $I, string $language): void
    {
        $this->setUserLanguage('User3', $language);

        $I->amUser3();
        $I->amOnDashboard();

        $I->waitForElementVisible('#space-menu');
        $I->click('#space-menu');
        $I->waitForElementVisible('#space-menu-search');

        $previousLanguage = Yii::$app->language;
        Yii::$app->language = $language;

        $notMemberMessage = Yii::t('SpaceModule.chooser', 'You are not a member of or following any Spaces.');
        $minCharsMessage = Yii::t(
            'SpaceModule.chooser',
            'Please enter at least {count} characters to search Spaces.',
            ['count' => 2],
        );

        // One list since 1.20 (the chooser island): what the legacy menu split into its own
        // list and a separate remote-search area now lives in `#space-menu-spaces`.
        $I->waitForText($notMemberMessage, 10, '#space-menu-spaces');

        $I->click('#space-menu-search');
        $I->pressKey('#space-menu-search', 'z');
        $I->waitForText($minCharsMessage, 10, '#space-menu-spaces');

        $I->pressKey('#space-menu-search', 'z');

        $noSpacesMessage = Yii::t('SpaceModule.chooser', 'No Spaces found.');
        $I->waitForText($noSpacesMessage, 10, '#space-menu-spaces');

        Yii::$app->language = $previousLanguage;

        $I->logout();
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

    private function setUserLanguage(string $username, string $language): void
    {
        $user = User::findOne(['username' => $username]);
        if ($user === null) {
            throw new \RuntimeException('Missing test user: ' . $username);
        }

        $user->setAttribute('language', $language);
        $user->save(false);
    }
}
