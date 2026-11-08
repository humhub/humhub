<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace admin\functional;

use admin\FunctionalTester;
use humhub\components\Appearance;
use humhub\modules\content\Module as ContentModule;
use PHPUnit\Framework\Assert;
use Yii;

class AppearanceCest
{
    public function testTabs(FunctionalTester $I)
    {
        $I->amAdmin();

        foreach ([
            'index' => 'General',
            'images' => 'Logo & Images',
            'theme' => 'Theme',
            'dark-mode' => 'Dark Mode',
            'accessibility' => 'Accessibility',
            'custom-scss' => 'Custom SCSS',
        ] as $action => $title) {
            $I->amOnRoute('/admin/appearance/' . $action);
            $I->seeResponseCodeIs(200);
            $I->see($title, '.content-card .card-title');
        }
    }

    public function testOldRouteRedirects(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/setting/design');
        $I->seeInCurrentUrl('appearance%2Ftheme');
    }

    public function testSaveGeneral(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/appearance/index');
        $I->submitForm('.content-card form', [
            'AppearanceGeneralForm[name]' => 'Appearance Test',
        ]);
        $I->seeResponseCodeIs(200);

        Assert::assertSame('Appearance Test', Yii::$app->settings->get('name'));
    }

    public function testSaveDarkModeAndAccessibility(FunctionalTester $I)
    {
        $I->amAdmin();

        $I->amOnRoute('/admin/appearance/dark-mode');
        $I->submitForm('.content-card form', [
            'DarkModeSettingsForm[allowDark]' => 1,
            'DarkModeSettingsForm[defaultColorMode]' => 'system',
        ]);
        Assert::assertTrue(Yii::$app->appearance->isDarkModeAllowed());
        Assert::assertSame(Appearance::SYSTEM, Yii::$app->appearance->getDefault(Appearance::COLOR_MODE));

        $I->amOnRoute('/admin/appearance/accessibility');
        $I->submitForm('.content-card form', [
            'AccessibilitySettingsForm[contrast]' => 'on',
            'AccessibilitySettingsForm[motion]' => 'system',
            'AccessibilitySettingsForm[font]' => 1,
            'AccessibilitySettingsForm[imageAltText]' => 'required',
        ]);
        Assert::assertSame(Appearance::ON, Yii::$app->appearance->getDefault(Appearance::CONTRAST));
        Assert::assertSame(Appearance::ON, Yii::$app->appearance->getDefault(Appearance::FONT));
        Assert::assertSame(ContentModule::IMAGE_ALT_TEXT_REQUIRED, Yii::$app->getModule('content')->getImageAltTextMode());

        // The editor is told on every page
        $I->amOnRoute('/admin/appearance/accessibility');
        $I->seeInSource('"image":{"altText":"required"}');
    }

    public function testGeneralSettingsKeepTheFormerAppearanceFields(FunctionalTester $I)
    {
        $I->amAdmin();
        $I->amOnRoute('/admin/setting/basic');
        $I->seeElement('[name="BasicSettingsForm[paginationSize]"]');
        $I->seeElement('[name="BasicSettingsForm[postTitleMode]"]');
        $I->dontSeeElement('[name="BasicSettingsForm[name]"]');
    }
}
