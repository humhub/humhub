<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\ColorInput;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\DynamicModel;

/**
 * The colour input renders the native text input, which carries the value, and the mount point of the
 * `ColorInput` component right after it
 */
class ColorInputTest extends HumHubDbTestCase
{
    public function testRendersTheInputAndTheMountPoint()
    {
        $model = new DynamicModel(['color' => '#435f6f']);

        $html = ColorInput::widget(['model' => $model, 'attribute' => 'color', 'default' => '#123456']);

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*id="dynamicmodel-color"[^>]*name="DynamicModel\[color\]"[^>]*value="#435f6f"/', $html);
        $this->assertStringContainsString('class="form-control"', $html);
        $this->assertStringContainsString('maxlength="7"', $html);
        $this->assertStringContainsString('data-color-default="#123456"', $html);
        $this->assertMatchesRegularExpression('/<color-input[^>]*input-id="dynamicmodel-color"/', $html);
        $this->assertGreaterThan(strpos($html, '<input'), strpos($html, '<color-input'));
    }

    public function testRendersWithoutAModel()
    {
        $html = ColorInput::widget(['name' => 'color', 'value' => '#abcdef']);

        $this->assertMatchesRegularExpression('/<input[^>]*name="color"[^>]*value="#abcdef"/', $html);
        $this->assertStringNotContainsString('data-color-default', $html);
    }
}
