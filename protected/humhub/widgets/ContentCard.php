<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\Menu;
use Yii;

/**
 * The content card below a page header (Bootstrap's `.card`): an optional header with the
 * card's title (`h2`), actions and ⋮ menu, the content as body, and an optional footer. Vue pages
 * use `ContentCard.vue`, which renders the same markup.
 *
 * ```php
 * <?php ContentCard::begin([
 *     'title' => Yii::t('AdminModule.user', 'Users'),
 *     'actions' => AdminUserCardActions::class,
 * ]) ?>
 *     …
 * <?php ContentCard::end() ?>
 * ```
 *
 * See `docs/ui/card.md`.
 *
 * @since 1.20
 */
class ContentCard extends Widget
{
    /**
     * @var string the card's title; without title, actions and menu the card has no header
     */
    public string $title = '';

    /**
     * @var string the tag of the title
     */
    public string $titleTag = 'h2';

    /**
     * @var string|CardHeaderActions|null the actions, as menu or menu class
     */
    public string|CardHeaderActions|null $actions = null;

    /**
     * @var string|DropdownMenu|null the ⋮ menu after the actions, as menu or menu class
     */
    public string|DropdownMenu|null $menu = null;

    /**
     * @var string the footer's content (HTML), none if empty
     */
    public string $footer = '';

    /**
     * @var array the HTML attributes of the card
     */
    public $options = [];

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

        return $this->render('contentCard', [
            'id' => $this->getId(),
            'title' => $this->title,
            'titleTag' => $this->titleTag,
            'actions' => $this->renderMenu($this->actions),
            'menu' => $this->renderMenu($this->menu, ['template' => '@humhub/widgets/menu/views/header-menu']),
            'content' => $content,
            'footer' => $this->footer,
            'options' => $this->options,
        ]);
    }

    private function renderMenu(string|Menu|null $menu, array $config = []): string
    {
        if ($menu === null) {
            return '';
        }

        if (is_string($menu)) {
            return $menu::widget($config);
        }
        Yii::configure($menu, $config);

        return $menu->run();
    }
}
