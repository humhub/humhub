<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\components\Event;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\modules\user\widgets\PeopleHeadingButtons;
use humhub\widgets\FooterMenu;
use humhub\widgets\TopMenu;
use tests\codeception\_support\HumHubDbTestCase;

/**
 * Menu entry labels (e.g. custom page titles) must be HTML-encoded by the menu templates,
 * unless the entry explicitly allows HTML via `encodeLabel => false`.
 */
class MenuLabelEncodingTest extends HumHubDbTestCase
{
    protected $fixtureConfig = ['default'];

    private const LABEL = '<img src=x id=xss>';

    public static function menuProvider(): array
    {
        return [
            'top menu' => [TopMenu::class, []],
            'footer full page' => [FooterMenu::class, ['location' => FooterMenu::LOCATION_FULL_PAGE]],
            'footer sidebar' => [FooterMenu::class, ['location' => FooterMenu::LOCATION_SIDEBAR]],
            'footer login' => [FooterMenu::class, ['location' => FooterMenu::LOCATION_LOGIN]],
            'footer email' => [FooterMenu::class, ['location' => FooterMenu::LOCATION_EMAIL]],
            'footer account menu' => [FooterMenu::class, ['location' => FooterMenu::LOCATION_ACCOUNT_MENU]],
            'people heading buttons' => [PeopleHeadingButtons::class, []],
        ];
    }

    /**
     * @dataProvider menuProvider
     */
    public function testLabelIsEncoded(string $menuClass, array $config)
    {
        $html = $this->renderMenuWith($menuClass, $config, ['label' => self::LABEL]);

        $this->assertStringNotContainsString(self::LABEL, $html);
        $this->assertStringContainsString('&lt;img src=x id=xss&gt;', $html);
    }

    /**
     * @dataProvider menuProvider
     */
    public function testLabelIsNotEncodedWhenDisabled(string $menuClass, array $config)
    {
        $html = $this->renderMenuWith($menuClass, $config, ['label' => '<b>Bold</b>', 'encodeLabel' => false]);

        $this->assertStringContainsString('<b>Bold</b>', $html);
    }

    /**
     * @param class-string<Menu> $menuClass
     */
    private function renderMenuWith(string $menuClass, array $config, array $entryConfig): string
    {
        $this->becomeUser('Admin');

        $handler = function ($event) use ($entryConfig) {
            $event->sender->addEntry(new MenuLink(array_merge([
                'id' => 'label-encoding-test',
                'url' => '/label-encoding-test',
            ], $entryConfig)));
        };

        Event::on($menuClass, Menu::EVENT_INIT, $handler);

        try {
            return $menuClass::widget($config);
        } finally {
            Event::off($menuClass, Menu::EVENT_INIT, $handler);
        }
    }
}
