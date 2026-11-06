<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace user\functional;

use humhub\assets\ReadableFontAsset;
use humhub\components\Appearance;
use humhub\modules\user\models\User;
use PHPUnit\Framework\Assert;
use user\FunctionalTester;
use Yii;

class AppearanceSettingsCest
{
    public function testColorModeOnlyWhenDarkModeIsAllowed(FunctionalTester $I)
    {
        $I->amUser1();

        $I->amOnRoute('/user/appearance/index');
        $I->see('Appearance & Accessibility');
        $I->see('Language');
        $I->dontSee('Colour mode');

        Yii::$app->getModule('admin')->settings->set(Appearance::SETTING_ALLOW_DARK, 1);
        $I->amOnRoute('/user/appearance/index');
        $I->see('Colour mode');
    }

    public function testSaveAppearance(FunctionalTester $I)
    {
        Yii::$app->getModule('admin')->settings->set(Appearance::SETTING_ALLOW_DARK, 1);
        $I->amUser1();

        $I->amOnRoute('/user/appearance/index');
        $I->submitForm('#appearance-settings-form', [
            'AppearanceSettings[colorMode]' => 'dark',
            'AppearanceSettings[timeZone]' => 'Europe/Berlin',
            'AppearanceSettings[markdownEditorMode]' => User::EDITOR_PLAIN,
        ]);
        $I->seeResponseCodeIs(200);

        $user = User::findOne(['username' => 'User1']);
        Assert::assertSame('Europe/Berlin', $user->time_zone);
        Assert::assertEquals(User::EDITOR_PLAIN, $user->settings->get('markdownEditorMode'));
        Assert::assertSame(Appearance::DARK, Yii::$app->appearance->get(Appearance::COLOR_MODE, $user));
    }

    public function testSaveAccessibility(FunctionalTester $I)
    {
        $I->amUser1();

        $I->amOnRoute('/user/appearance/accessibility');
        $I->see('High contrast');
        $I->submitForm('#accessibility-settings-form', [
            'AccessibilitySettings[contrast]' => 'on',
            'AccessibilitySettings[motion]' => 'off',
            'AccessibilitySettings[font]' => 1,
        ]);
        $I->seeResponseCodeIs(200);

        $user = User::findOne(['username' => 'User1']);
        $appearance = Yii::$app->appearance;
        Assert::assertSame(Appearance::ON, $appearance->get(Appearance::CONTRAST, $user));
        Assert::assertSame(Appearance::OFF, $appearance->get(Appearance::MOTION, $user));
        Assert::assertSame(Appearance::ON, $appearance->get(Appearance::FONT, $user));
    }

    public function testReadableFontIsLoaded(FunctionalTester $I)
    {
        $I->amUser1();
        $user = User::findOne(['username' => 'User1']);
        $fontCss = Yii::$app->assetManager->getBundle(ReadableFontAsset::class)->baseUrl . '/index.css';

        $I->amOnRoute('/dashboard/dashboard');
        $I->dontSeeInSource($fontCss);

        Yii::$app->appearance->set(Appearance::FONT, Appearance::ON, $user);
        $I->amOnRoute('/dashboard/dashboard');
        $I->seeInSource($fontCss);
    }
}
