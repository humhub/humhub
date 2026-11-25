<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\MenuEntry;
use humhub\widgets\menu\MenuLink;
use humhub\widgets\menu\SidebarCardMenu;
use Yii;
use yii\helpers\BaseInflector;

/**
 * A card in the sidebar of a page (the dashboard's, a space's or a profile's, `BaseSidebar`):
 * a title, a ⋮ menu with the option to collapse the card (remembered per browser) and the
 * content. Bootstrap's `.card` with the content card's look and the modifier `.sidebar-card`.
 * ```php
 * <?php SidebarCard::begin([
 *     'title' => Yii::t('SpaceModule.base', 'Space members'),
 *     'menu' => [new MenuLink(['label' => Yii::t('SpaceModule.base', 'Show as List'), 'url' => $url])],
 * ]) ?>
 *     …
 * <?php SidebarCard::end() ?>
 * ```
 * See `docs/ui/sidebar-card.md`.
 * @since 1.20
 */
class SidebarCard extends Widget
{
    /**
     * @var string the card's title
     */
    public string $title = '';

    /**
     * @var bool whether the title is HTML-encoded. A translation that still emphasises a word
     * (`<strong>Space</strong> members`) needs `false`; never pass user input unencoded then.
     */
    public bool $encodeTitle = true;

    /**
     * @var string the tag of the title
     */
    public string $titleTag = 'h2';

    /**
     * @var array|null the HTML attributes of a button around the title, for a title that opens
     * something (the list of a space's members); `null` for a plain title
     */
    public ?array $titleAction = null;

    /**
     * @var string|DropdownMenu|MenuEntry[]|null the entries of the ⋮ menu: a menu, a menu class or
     * its entries. The menu ends with the option to collapse the card.
     */
    public string|DropdownMenu|array|null $menu = null;

    /**
     * @var bool whether the card can be collapsed through its ⋮ menu
     */
    public bool $collapsible = true;

    /**
     * @var string|null the id of the collapsing body, which also keys its remembered state; by
     * default derived from the widget whose view renders the card. Two cards of the same widget
     * need ids of their own.
     */
    public ?string $collapseId = null;

    /**
     * @var array the HTML attributes of the card
     */
    public $options = [];

    /**
     * @var array the HTML attributes of the card's body
     */
    public array $bodyOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = ob_get_clean();
        $collapseId = $this->collapsible ? $this->getCollapseId() : null;

        return $this->render('sidebarCard', [
            'id' => $this->getId(),
            'title' => $this->encodeTitle ? Html::encode($this->title) : $this->title,
            'titleTag' => $this->titleTag,
            'titleAction' => $this->titleAction,
            'menu' => $this->renderMenu($collapseId),
            'collapseId' => $collapseId,
            'content' => $content,
            'options' => $this->options,
            'bodyOptions' => $this->bodyOptions,
        ]);
    }

    /**
     * The id of the collapsing body: as the former `PanelMenu` derived it from the widget whose view renders
     * the card, so that a remembered state survives the change from a panel to a sidebar card
     */
    private function getCollapseId(): string
    {
        if ($this->collapseId !== null) {
            return $this->collapseId;
        }

        $context = $this->view->context;

        return $context !== null ? BaseInflector::slug($context::class) : $this->getId() . '-body';
    }

    private function renderMenu(?string $collapseId): string
    {
        $menu = $this->menu;
        if (!$menu instanceof DropdownMenu) {
            $entries = is_array($menu) ? $menu : [];
            $menu = Yii::createObject(is_string($menu) ? $menu : SidebarCardMenu::class);
            foreach ($entries as $entry) {
                $menu->addEntry($entry);
            }
        }

        if ($collapseId !== null) {
            $menu->addEntry($this->getCollapseEntry($collapseId));
        }

        // The menu of a header: the ⋮ as a ghost icon button (`ContentCard`'s)
        $menu->template = '@humhub/widgets/menu/views/header-menu';

        return $menu->run();
    }

    /**
     * The entry that collapses and expands the card's body (Bootstrap's collapse); its label
     * follows the state (`.collapsed`, `_sidebar.scss`)
     */
    private function getCollapseEntry(string $collapseId): MenuLink
    {
        return new MenuLink([
            'label' => Html::tag('span', Icon::get('minus-square') . ' ' . Yii::t('base', 'Collapse'), ['class' => 'sidebar-card-collapse-hide'])
                . Html::tag('span', Icon::get('plus-square') . ' ' . Yii::t('base', 'Expand'), ['class' => 'sidebar-card-collapse-show']),
            'encodeLabel' => false,
            'url' => '#' . $collapseId,
            'sortOrder' => PHP_INT_MAX,
            'htmlOptions' => [
                'class' => 'sidebar-card-collapse',
                'data-bs-toggle' => 'collapse',
                'role' => 'button',
                'aria-controls' => $collapseId,
                'aria-expanded' => 'true',
            ],
        ]);
    }
}
