<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\widgets\VueWidget;
use tests\codeception\_support\HumHubDbTestCase;

class VueWidgetTest extends HumHubDbTestCase
{
    public function testRendersTheDeclaredComponentWithPropsOptionsAndPlaceholder()
    {
        $html = TestIslandWidget::widget(['count' => 3]);

        $this->assertStringContainsString('<test-island', $html);
        $this->assertStringContainsString('count="3"', $html);
        $this->assertStringContainsString('id="test-island"', $html);
        $this->assertStringContainsString('class="island"', $html);
        $this->assertStringContainsString('<span>loading</span>', $html);
        $this->assertStringContainsString('</test-island>', $html);
    }

    public function testRendersNothingWhenBeforeRunDeclines()
    {
        $this->assertSame('', TestIslandWidget::widget(['count' => 3, 'hidden' => true]));
    }
}

/**
 * The minimal shape of a widget built on the base class: the component, what the server hands
 * over, and a guard.
 */
class TestIslandWidget extends VueWidget
{
    public int $count = 0;

    public bool $hidden = false;

    protected string $component = 'TestIsland';

    public function beforeRun()
    {
        if ($this->hidden) {
            return false;
        }

        return parent::beforeRun();
    }

    protected function getProps(): array
    {
        return ['count' => $this->count];
    }

    protected function getOptions(): array
    {
        return ['id' => 'test-island', 'class' => 'island'];
    }

    protected function getPlaceholder(): string
    {
        return '<span>loading</span>';
    }
}
