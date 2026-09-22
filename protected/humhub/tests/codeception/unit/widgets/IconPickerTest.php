<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\IconPicker;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class IconPickerTest extends HumHubDbTestCase
{
    public function testNamesAreHandedToSelect2AsJson()
    {
        $html = IconPicker::widget(['name' => 'icon', 'value' => 'pencil']);

        $this->assertStringContainsString('<option value="pencil" selected>', $html);
        $this->assertStringNotContainsString('<option value="star">', $html);

        $js = implode("\n", array_merge(...array_values(Yii::$app->view->js ?: [[]])));
        $this->assertStringContainsString('window.humhubIconNames', $js);
        $this->assertStringContainsString('"star-filled"', $js);
    }

    public function testLegacyValueIsPreselectedAsTablerName()
    {
        $html = IconPicker::widget(['name' => 'icon', 'value' => 'fa-cog']);

        $this->assertStringContainsString('<option value="settings" selected>', $html);
    }
}
