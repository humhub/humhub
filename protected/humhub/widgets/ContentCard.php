<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\components\View;
use humhub\components\Widget;
use humhub\widgets\menu\CardHeaderActions;
use humhub\widgets\menu\DropdownMenu;
use humhub\widgets\menu\Menu;
use humhub\widgets\menu\PillTabs;
use Yii;

/**
 * The content card below a page header (Bootstrap's `.card`): an optional header with the
 * card's title (`h2`), a description, actions and ⋮ menu, optional secondary tabs below it, the
 * content as body, and an optional footer. Vue pages use `ContentCard.vue`, which renders the
 * same markup.
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
 * A layout that wraps its views in a legacy `.panel` leaves that out for a view that rendered a
 * content card ({@see self::isRendered()}, {@see self::markRendered()} for a Vue island).
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
     * @var string badges next to the title (HTML), e.g. a status like "Administrative group"
     * @since 1.20
     */
    public string $badges = '';

    /**
     * @var string|array|null the url of the back arrow before the title, on a sub-page (add,
     * edit) leading back to its overview
     * @since 1.20
     */
    public string|array|null $backUrl = null;

    /**
     * @var string|null the back arrow's label (tooltip, `aria-label`), e.g. "Back to overview";
     * "Back" if not set
     * @since 1.20
     */
    public ?string $backLabel = null;

    /**
     * @var string a short description below the title (HTML)
     */
    public string $description = '';

    /**
     * @var string|CardHeaderActions|null the actions, as menu or menu class
     */
    public string|CardHeaderActions|null $actions = null;

    /**
     * @var string buttons (HTML) before the actions, e.g. a secondary text button for an action
     * that is not a save ("Flush caches", "Send test e-mail")
     */
    public string $buttons = '';

    /**
     * @var string|DropdownMenu|null the ⋮ menu after the actions, as menu or menu class
     */
    public string|DropdownMenu|null $menu = null;

    /**
     * @var string|PillTabs|null secondary tabs below the header, as menu or menu class
     */
    public string|PillTabs|null $tabs = null;

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
        self::markRendered($this->view);
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
            'description' => $this->description,
            'badges' => $this->badges,
            'backUrl' => $this->backUrl,
            'backLabel' => $this->backLabel,
            'buttons' => $this->buttons,
            'actions' => $this->renderMenu($this->actions),
            'menu' => $this->renderMenu($this->menu, ['template' => '@humhub/widgets/menu/views/header-menu']),
            'tabs' => $this->renderMenu($this->tabs, ['variant' => PillTabs::VARIANT_SECONDARY]
                + ($this->title !== '' ? ['label' => $this->title] : [])),
            'content' => $content,
            'footer' => $this->footer,
            'options' => $this->options,
        ]);
    }

    /**
     * Whether a view rendered a content card, i.e. brings its own card instead of the panel of a
     * layout around it.
     */
    public static function isRendered(View $view): bool
    {
        return !empty($view->params[self::class]);
    }

    /**
     * Notes that the view brings its own card(s), e.g. a Vue island rendering `ContentCard.vue`.
     */
    public static function markRendered(View $view): void
    {
        $view->params[self::class] = true;
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
