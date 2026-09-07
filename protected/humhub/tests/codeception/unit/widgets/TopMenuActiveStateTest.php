<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\components\Event;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\TopMenu;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;
use yii\web\View;

/**
 * The top menu is outside the pjax container and is not re-rendered by a pjax navigation, so the
 * server tells the client which entry is active now - without it the previous page's entry keeps
 * the highlight (the dashboard stays marked after moving into a space).
 */
class TopMenuActiveStateTest extends HumHubDbTestCase
{
    public function testNamesTheEntryThatIsActive()
    {
        $handler = function ($event) {
            $event->sender->addEntry(new MenuLink([
                'id' => 'test-entry',
                'label' => 'Test',
                'url' => '/test-entry',
                'isActive' => true,
            ]));
        };

        Event::on(TopMenu::class, TopMenu::EVENT_INIT, $handler);

        try {
            TopMenu::registerActiveState();
        } finally {
            Event::off(TopMenu::class, TopMenu::EVENT_INIT, $handler);
        }

        $js = $this->getRegisteredJs();

        $this->assertStringContainsString('humhub.modules.ui.navigation.setActive("top-menu-nav"', $js);
        $this->assertStringContainsString('"id":"test-entry"', $js);
        $this->assertStringContainsString('test-entry', $js);
    }

    public function testClearsTheHighlightOnAPageWithoutAnActiveEntry()
    {
        TopMenu::registerActiveState();

        $this->assertStringContainsString(
            'humhub.modules.ui.navigation.setActive("top-menu-nav", null)',
            $this->getRegisteredJs(),
        );
    }

    private function getRegisteredJs(): string
    {
        $js = Yii::$app->view->js[View::POS_END]['active-top-menu-nav'] ?? null;

        $this->assertNotNull($js, 'The active state was not registered at all.');

        return $js;
    }
}
