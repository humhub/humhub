<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\assets\CropImageAsset;
use humhub\components\View;
use humhub\widgets\CropImage;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidConfigException;
use yii\web\JsExpression;

/**
 * The crop widget renders the keyboard hint and starts `ui.cropImage` on the image with the given options
 */
class CropImageTest extends HumHubDbTestCase
{
    public function testStartsTheCropAreaOnTheImage()
    {
        $view = new View();

        $html = CropImage::widget([
            'id' => 'crop',
            'view' => $view,
            'selector' => '#crop-profile-image',
            'pluginOptions' => ['aspectRatio' => 2, 'onChange' => new JsExpression('function (c) {}')],
        ]);

        $this->assertMatchesRegularExpression('/<p id="crop-hint" class="form-text">[^<]+<\/p>/', $html);
        $this->assertArrayHasKey(CropImageAsset::class, $view->assetBundles);

        $js = implode("\n", $view->js[View::POS_READY]);
        $this->assertStringContainsString('humhub.require("ui.cropImage").create("#crop-profile-image", ', $js);
        $this->assertStringContainsString('"aspectRatio":2', $js);
        $this->assertStringContainsString('"onChange":function (c) {}', $js);
        $this->assertStringContainsString('"describedBy":"crop-hint"', $js);
        $this->assertStringContainsString('boxHeight', $js);
    }

    public function testHasNoHintWithoutKeyboardControl()
    {
        $html = CropImage::widget([
            'view' => new View(),
            'selector' => '#crop-profile-image',
            'pluginOptions' => ['keySupport' => false],
        ]);

        $this->assertSame('', $html);
    }

    public function testRequiresASelector()
    {
        $this->expectException(InvalidConfigException::class);

        CropImage::widget(['view' => new View()]);
    }
}
