<?php

namespace humhub\tests\codeception\unit\widgets;

use humhub\helpers\Html;
use tests\codeception\_support\HumHubDbTestCase;

class HtmlDropDownListTest extends HumHubDbTestCase
{
    public function testEnhancesWithSearchFromFiveOptions()
    {
        $this->assertSame(['data-ui-picker' => true, 'data-picker-search' => 5], Html::getDropDownListOptions());
    }

    public function testPromptCountsNotAsOption()
    {
        // The picker counts the options without the prompt.
        $this->assertSame(5, Html::getDropDownListOptions(['prompt' => 'Select'])['data-picker-search']);
    }

    public function testMinimumResultsForSearchIsTranslated()
    {
        $options = Html::getDropDownListOptions(['minimumResultsForSearch' => -1]);
        $this->assertSame(-1, $options['data-picker-search']);
        $this->assertArrayNotHasKey('minimumResultsForSearch', $options);
    }

    public function testAllowNewAlwaysSearches()
    {
        $options = Html::getDropDownListOptions(['data-ui-select2-allow-new' => true, 'data-ui-select2-new-sign' => '(new)']);
        $this->assertSame(0, $options['data-picker-search']);
        $this->assertSame('true', $options['data-picker-allow-new']);
        $this->assertArrayNotHasKey('data-ui-select2-allow-new', $options);
        $this->assertArrayNotHasKey('data-ui-select2-new-sign', $options);
        $this->assertArrayNotHasKey('data-picker-new-prefix', $options, 'an enhanced select posts the plain text');
    }

    public function testFalseAllowNewDoesNotAllowNew()
    {
        foreach (['data-ui-select2-allow-new', 'data-picker-allow-new'] as $key) {
            $options = Html::getDropDownListOptions([$key => false]);
            $this->assertSame(5, $options['data-picker-search'], $key);
            $this->assertArrayNotHasKey('data-picker-allow-new', $options, $key);
        }
    }

    public function testSelect2OptOutIsKept()
    {
        $this->assertFalse(Html::getDropDownListOptions(['data-ui-select2' => false])['data-ui-picker']);
        $this->assertArrayNotHasKey('data-ui-select2', Html::getDropDownListOptions(['data-ui-select2' => true]));
    }

    public function testRendersTheAttributes()
    {
        $html = Html::dropDownList('size', 'm', ['s' => 'S', 'm' => 'M']);
        $this->assertStringContainsString('data-ui-picker', $html);
        $this->assertStringContainsString('data-picker-search="5"', $html);
        $this->assertStringNotContainsString('style="width:100%"', $html);
        $this->assertStringNotContainsString('select2', $html);
    }
}
