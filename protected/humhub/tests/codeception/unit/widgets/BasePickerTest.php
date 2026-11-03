<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\form\BasePicker;
use humhub\widgets\form\MultiSelect;
use tests\codeception\_support\HumHubDbTestCase;

class BasePickerTestPicker extends BasePicker
{
    public $url = '/test/search';

    protected function getItemText($item)
    {
        return $item->name;
    }

    protected function getItemImage($item)
    {
        return $item->image ?? null;
    }

    protected function getItemSubText($item): ?string
    {
        return $item->sub ?? null;
    }
}

class BasePickerTestNamedPicker extends BasePickerTestPicker
{
    public $url = null;

    public $defaultRoute = '/test/search';

    public function getSource(): ?array
    {
        return ['source' => 'test', 'params' => ['spaceId' => 3]];
    }

    protected function getData()
    {
        $data = parent::getData();
        $data['placeholder'] = 'Overridden';
        $data['no-result'] = 'Nobody.';
        $data['custom-key'] = 'kept';
        return $data;
    }
}

class BasePickerTestJsonDefaultsPicker extends BasePickerTestPicker
{
    public array $jsonDefaults = [];

    protected function getData()
    {
        $data = parent::getData();
        $data['default-results'] = $this->jsonDefaults;
        return $data;
    }
}

class BasePickerTest extends HumHubDbTestCase
{
    private function item(int $id, string $name, $image = null, ?string $sub = null): object
    {
        return (object)['id' => $id, 'name' => $name, 'image' => $image, 'sub' => $sub];
    }

    private function config(string $html): array
    {
        // Yii renders array attributes as single-quoted Json::htmlEncode() output
        $this->assertSame(1, preg_match("/data-picker-config='([^']*)'/", $html, $match), $html);
        return json_decode($match[1], true);
    }

    public function testRendersSelectWithPickerAttributes()
    {
        $html = BasePickerTestPicker::widget(['name' => 'items', 'selection' => [$this->item(1, 'One')]]);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="items[]"', $html);
        $this->assertStringContainsString('multiple', $html);
        $this->assertStringContainsString('data-ui-picker', $html);
        $this->assertStringContainsString('data-picker-url="/test/search"', $html);
        $this->assertStringContainsString('data-picker-max-selection="50"', $html);
        $this->assertStringContainsString('data-picker-min-input="3"', $html);
        $this->assertStringNotContainsString('data-ui-widget', $html);
    }

    public function testRendersSelectedOptionsWithTextSubTextAndImage()
    {
        $html = BasePickerTestPicker::widget(['name' => 'items', 'selection' => [
            $this->item(1, 'One', '/one.jpg', 'First'),
            $this->item(2, 'Two', ['type' => 'icon', 'name' => 'star']),
        ]]);

        $this->assertMatchesRegularExpression('/<option value="1" selected[^>]*data-text="One"[^>]*data-image="\/one.jpg"[^>]*data-sub-text="First"/', $html);
        $this->assertStringContainsString('data-image=\'{"type":"icon","name":"star"}\'', $html);
    }

    public function testLockedAndDisabledItemsGoIntoTheConfig()
    {
        $html = BasePickerTestPicker::widget([
            'name' => 'items',
            'selection' => [$this->item(2, 'Two'), $this->item(1, 'One')],
            'lockedItems' => [1],
            'disabledItems' => [5],
        ]);
        $config = $this->config($html);

        $this->assertSame(['1'], $config['lockedIds']);
        $this->assertSame(['5'], $config['disabledIds']);
        $this->assertArrayHasKey('locked', $config['messages']);
        $this->assertLessThan(strpos($html, 'value="2"'), strpos($html, 'value="1"'), 'locked items first');
        $this->assertMatchesRegularExpression('/<option value="1" selected[^>]*data-locked/', $html);
    }

    public function testNamedSourceLegacyKeysAndPassThrough()
    {
        $html = BasePickerTestNamedPicker::widget(['name' => 'items']);
        $config = $this->config($html);

        $this->assertStringContainsString('data-picker-source="test"', $html);
        $this->assertStringNotContainsString('data-picker-url', $html);
        $this->assertSame(['spaceId' => 3], $config['sourceParams']);
        $this->assertStringContainsString('data-picker-placeholder="Overridden"', $html);
        $this->assertSame('Nobody.', $config['messages']['noResults']);
        $this->assertStringContainsString('data-custom-key="kept"', $html);
    }

    public function testNewPropertiesAndFlags()
    {
        $html = BasePickerTestPicker::widget([
            'name' => 'items', 'listed' => true, 'suggested' => 3, 'maxResults' => 6,
            'addOptions' => true, 'disabled' => true, 'focus' => true,
        ]);

        $this->assertStringContainsString('data-picker-listed="true"', $html);
        $this->assertStringContainsString('data-picker-suggested="3"', $html);
        $this->assertStringContainsString('data-picker-max-results="6"', $html);
        $this->assertStringContainsString('data-picker-allow-new="true"', $html);
        $this->assertStringContainsString('data-picker-new-prefix="_add:"', $html);
        $this->assertMatchesRegularExpression('/<select[^>]* disabled/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]* autofocus/', $html);
    }

    public function testLegacySelect2AttributesOfTheOptionsBecomePickerAttributes()
    {
        $html = BasePickerTestPicker::widget(['name' => 'items', 'options' => [
            'data-placeholder' => 'Select Groups',
            'data-placeholder-more' => 'Add Groups...',
            'data-maximum-selection-length' => 3,
            'data-tags' => 'false',
        ]]);

        $this->assertStringContainsString('data-picker-placeholder="Select Groups"', $html);
        $this->assertStringContainsString('data-picker-placeholder-more="Add Groups..."', $html);
        $this->assertStringContainsString('data-picker-max-selection="3"', $html);
        $this->assertStringNotContainsString('data-placeholder=', $html);
        $this->assertStringNotContainsString('data-tags', $html);
    }

    public function testNoNewPrefixWithoutAddOptions()
    {
        $this->assertStringNotContainsString('data-picker-new-prefix', BasePickerTestPicker::widget(['name' => 'items']));
    }

    public function testDefaultResultsBecomeItems()
    {
        $html = BasePickerTestPicker::widget(['name' => 'items', 'defaultResults' => [$this->item(7, 'Seven', '#ff0000')]]);

        $this->assertSame([['id' => '7', 'text' => 'Seven', 'subText' => null, 'image' => '#ff0000']], $this->config($html)['defaultResults']);
    }

    public function testDefaultResultsAcceptTheJsonShape()
    {
        $image = new class () implements \Stringable {
            public function __toString(): string
            {
                return '/img.png';
            }
        };
        $html = BasePickerTestJsonDefaultsPicker::widget(['name' => 'items', 'jsonDefaults' => [
            ['id' => 8, 'text' => 'Eight', 'subText' => 'Sub', 'image' => $image],
            ['data-id' => 9, 'data-text' => 'Nine'],
        ]]);

        $this->assertSame([
            ['id' => '8', 'text' => 'Eight', 'subText' => 'Sub', 'image' => '/img.png'],
            ['id' => '9', 'text' => 'Nine', 'subText' => null, 'image' => null],
        ], $this->config($html)['defaultResults']);
    }

    public function testJsWidgetDefaultsApply()
    {
        $html = BasePickerTestPicker::widget(['name' => 'items', 'events' => ['change' => 'my.handler'], 'visible' => false]);

        $this->assertStringContainsString('data-widget-action-change="my.handler"', $html);
        $this->assertMatchesRegularExpression('/<select[^>]* style="[^"]*display:none/', $html);
        $this->assertStringNotContainsString('data-ui-widget', $html);
        $this->assertStringNotContainsString('data-ui-init', $html);
    }

    public function testMultiSelectIsStatic()
    {
        $html = MultiSelect::widget(['name' => 'levels', 'items' => [1 => 'Error', 2 => 'Warning'], 'selection' => [2]]);

        $this->assertStringNotContainsString('data-picker-url', $html);
        $this->assertStringNotContainsString('data-picker-source', $html);
        $this->assertMatchesRegularExpression('/<option value="1"[^>]*data-text="Error"/', $html);
        $this->assertMatchesRegularExpression('/<option value="2" selected/', $html);
    }
}
