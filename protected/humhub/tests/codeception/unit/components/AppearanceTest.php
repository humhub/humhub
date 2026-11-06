<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\components;

use humhub\components\Appearance;
use humhub\components\View;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * @since 1.20
 */
class AppearanceTest extends HumHubDbTestCase
{
    public $fixtureConfig = ['default'];

    public function testBuiltInDefaults()
    {
        $appearance = Yii::$app->appearance;

        $this->assertSame(Appearance::LIGHT, $appearance->getDefault(Appearance::COLOR_MODE));
        $this->assertSame(Appearance::SYSTEM, $appearance->getDefault(Appearance::CONTRAST));
        $this->assertSame(Appearance::SYSTEM, $appearance->getDefault(Appearance::MOTION));
        $this->assertSame(Appearance::OFF, $appearance->getDefault(Appearance::FONT));
    }

    public function testUserInheritsTheInstanceDefault()
    {
        $appearance = Yii::$app->appearance;
        $user = User::findOne(['username' => 'User1']);

        $appearance->setDefault(Appearance::CONTRAST, Appearance::ON);
        $this->assertSame(Appearance::ON, $appearance->get(Appearance::CONTRAST, $user));

        $appearance->set(Appearance::CONTRAST, Appearance::OFF, $user);
        $this->assertSame(Appearance::OFF, $appearance->get(Appearance::CONTRAST, $user));

        // A value equal to the default is not stored, so the user follows a later change of the default
        $appearance->set(Appearance::CONTRAST, Appearance::ON, $user);
        $this->assertNull(Yii::$app->getModule('user')->settings->contentContainer($user)->get(Appearance::CONTRAST));
        $appearance->setDefault(Appearance::CONTRAST, Appearance::SYSTEM);
        $this->assertSame(Appearance::SYSTEM, $appearance->get(Appearance::CONTRAST, $user));
    }

    public function testBuiltInDefaultIsNotStored()
    {
        Yii::$app->appearance->setDefault(Appearance::MOTION, Appearance::SYSTEM);

        $this->assertNull(Yii::$app->getModule('user')->settings->get(Appearance::MOTION));
    }

    public function testInvalidValueFallsBackToTheBuiltInDefault()
    {
        $user = User::findOne(['username' => 'User1']);
        Yii::$app->getModule('user')->settings->contentContainer($user)->set(Appearance::MOTION, 'sometimes');

        $this->assertSame(Appearance::SYSTEM, Yii::$app->appearance->get(Appearance::MOTION, $user));
    }

    public function testColorModeIsLightUnlessDarkModeIsAllowed()
    {
        $appearance = Yii::$app->appearance;
        $user = User::findOne(['username' => 'User1']);
        $appearance->set(Appearance::COLOR_MODE, Appearance::DARK, $user);

        $this->assertSame(Appearance::LIGHT, $appearance->getColorMode($user));

        Yii::$app->getModule('admin')->settings->set(Appearance::SETTING_ALLOW_DARK, 1);
        $this->assertSame(Appearance::DARK, $appearance->getColorMode($user));
    }

    public function testRegisterHead()
    {
        $this->becomeUser('User1');
        $user = User::findOne(['username' => 'User1']);
        Yii::$app->getModule('admin')->settings->set(Appearance::SETTING_ALLOW_DARK, 1);
        Yii::$app->appearance->set(Appearance::COLOR_MODE, Appearance::SYSTEM, $user);
        Yii::$app->appearance->set(Appearance::FONT, Appearance::ON, $user);

        $view = new View();
        Yii::$app->appearance->registerHead($view);

        $js = implode("\n", $view->js[View::POS_HEAD]);
        $this->assertStringContainsString('"colorMode":"system"', $js);
        $this->assertStringContainsString('"font":true', $js);
        $this->assertArrayHasKey(\humhub\assets\ReadableFontAsset::class, $view->assetBundles);
    }
}
