<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\Menu;
use humhub\widgets\menu\PageHeaderActions;
use humhub\widgets\menu\PillTabs;
use Stringable;
use Yii;

/**
 * The card at the top of a page: the page's title (its `h1`), the page's actions, its ⋮ menu
 * and optionally its tabs. Vue pages use `PageHeader.vue`, which renders the same markup.
 *
 * ```php
 * <?= PageHeader::title(Yii::t('AdminModule.user', 'People'))
 *     ->actions(AdminUserHeaderActions::class)
 *     ->menu(AdminUserHeaderMenu::class)
 *     ->tabs(UserMenu::class) ?>
 * ```
 *
 * See `docs/ui/page-header.md`.
 *
 * @since 1.20
 */
class PageHeader extends Widget implements Stringable
{
    /**
     * @var string the page title
     */
    public string $title = '';

    /**
     * @var string the tag of the title
     */
    public string $titleTag = 'h1';

    /**
     * @var string|PageHeaderActions|null the actions, as menu or menu class
     */
    public string|PageHeaderActions|null $actions = null;

    /**
     * @var string|DropdownMenu|null the ⋮ menu after the actions, as menu or menu class
     */
    public string|DropdownMenu|null $menu = null;

    /**
     * @var string|Menu|null the tabs, as menu or menu class
     */
    public string|Menu|null $tabs = null;

    /**
     * @var string|array|null the url of the back arrow, shown while a surrounding master/detail
     * layout is folded
     */
    public string|array|null $backUrl = null;

    /**
     * @var string|null the back arrow's label (tooltip, `aria-label`), e.g. "Back to overview";
     * "Back" if not set
     */
    public ?string $backLabel = null;

    public static function title(string $title): static
    {
        return new static(['title' => $title]);
    }

    public function actions(string|PageHeaderActions|null $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

    public function menu(string|DropdownMenu|null $menu): static
    {
        $this->menu = $menu;
        return $this;
    }

    public function tabs(string|Menu|null $tabs): static
    {
        $this->tabs = $tabs;
        return $this;
    }

    public function backUrl(string|array|null $url, ?string $label = null): static
    {
        $this->backUrl = $url;
        $this->backLabel = $label;
        return $this;
    }

    public function titleTag(string $tag): static
    {
        $this->titleTag = $tag;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('pageHeader', [
            'id' => $this->getId(),
            'title' => $this->title,
            'titleTag' => $this->titleTag,
            'actions' => $this->renderMenu($this->actions),
            'menu' => $this->renderMenu($this->menu, menuConfig: ['template' => '@humhub/widgets/menu/views/header-menu']),
            'tabs' => $this->renderMenu($this->tabs, ['label' => $this->title]),
            'backUrl' => $this->backUrl,
            'backLabel' => $this->backLabel,
        ]);
    }

    public function __toString(): string
    {
        return (string)$this->run();
    }

    /**
     * @param array $tabsConfig what a tab strip without a label of its own gets
     * @param array $menuConfig what the menu is rendered with
     */
    private function renderMenu(string|Menu|null $menu, array $tabsConfig = [], array $menuConfig = []): string
    {
        if ($menu === null) {
            return '';
        }

        if (is_string($menu)) {
            return $menu::widget(array_merge(is_subclass_of($menu, PillTabs::class) ? $tabsConfig : [], $menuConfig));
        }

        if ($menu instanceof PillTabs && $menu->label === null) {
            $menu->label = $tabsConfig['label'] ?? null;
        }
        Yii::configure($menu, $menuConfig);

        return $menu->run();
    }
}
