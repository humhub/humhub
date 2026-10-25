<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\menu;

/**
 * Link tabs: a strip of pills (Bootstrap's `.nav.nav-pills`) switching between sibling pages,
 * e.g. the sub-pages of an administration section. In a page header they sit below the title
 * (see {@see \humhub\widgets\PageHeader::tabs()}). See `docs/ui/tabs.md`.
 *
 * @since 1.20
 */
abstract class PillTabs extends Menu
{
    /**
     * Pills directly on the page, e.g. in a page header.
     */
    public const VARIANT_PRIMARY = 'primary';

    /**
     * Pills inside a card, panel or dialog.
     */
    public const VARIANT_SECONDARY = 'secondary';

    /**
     * @inheritdoc
     */
    public $template = '@humhub/widgets/menu/views/pill-tabs.php';

    /**
     * @var string one of the `VARIANT_*` constants
     */
    public string $variant = self::VARIANT_PRIMARY;

    /**
     * @var string|null the accessible name of the strip (`aria-label` of its `<nav>`)
     */
    public ?string $label = null;

    /**
     * @var bool whether a strip with only one tab is rendered
     */
    public $renderSingleTab = false;

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        if (!$this->renderSingleTab && !$this->hasMultipleEntries(MenuLink::class)) {
            return '';
        }

        return parent::render($view, $params);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return array_filter([
            'class' => 'nav-scroller',
            'aria-label' => $this->label === null ? null : strip_tags($this->label),
        ]);
    }
}
