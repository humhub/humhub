<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\components\icon\TablerIconProvider;
use humhub\widgets\form\IconPicker;
use humhub\widgets\Icon;
use ReflectionProperty;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

class IconPickerTest extends HumHubDbTestCase
{
    private function jsConfig(): array
    {
        return (new ReflectionProperty(Yii::$app->view, 'jsConfig'))->getValue(Yii::$app->view);
    }

    public function testRendersOnlyTheChosenIconOnTheIconSource()
    {
        $html = IconPicker::widget(['name' => 'icon', 'value' => 'pencil']);

        $this->assertStringContainsString('data-ui-picker', $html);
        $this->assertStringContainsString('data-picker-source="icon"', $html);
        $this->assertStringContainsString('"sourceParams":{"lib":"' . TablerIconProvider::ID . '"}', $html);
        $this->assertDoesNotMatchRegularExpression('/<select[^>]* multiple/', $html, 'single');
        $this->assertMatchesRegularExpression('/<option value="pencil" selected[^>]*data-image="ti-pencil"/', $html);
        $this->assertSame(1, substr_count($html, '<option value="pencil"'));
        $this->assertSame(2, substr_count($html, '<option'), 'the prompt and the chosen icon only');
        $count = count(Icon::getNames(TablerIconProvider::ID));
        $this->assertGreaterThan(1000, $count);
        $this->assertStringContainsString('data-picker-max-results="' . $count . '"', $html, 'all icons of the library');
        $this->assertStringNotContainsString('select2', $html);
    }

    public function testRegistersTheNamesOncePerLibrary()
    {
        IconPicker::widget(['name' => 'a', 'value' => 'pencil']);
        IconPicker::widget(['name' => 'b']);

        $names = $this->jsConfig()['ui.picker']['iconNames'][TablerIconProvider::ID];
        $list = explode(',', $names);
        $this->assertContains('star-filled', $list);
        $this->assertSame(count($list), count(array_unique($list)), 'no duplicates on a second picker');
    }

    public function testKeepsAnUnknownStoredValue()
    {
        $html = IconPicker::widget(['name' => 'icon', 'value' => 'no-such-icon-xyz']);

        $this->assertMatchesRegularExpression('/<option value="no-such-icon-xyz" selected/', $html);
    }

    public function testNormalizesLegacyNames()
    {
        $html = IconPicker::widget(['name' => 'icon', 'value' => 'fa-cog']);

        $this->assertMatchesRegularExpression('/<option value="settings" selected/', $html);
    }
}
