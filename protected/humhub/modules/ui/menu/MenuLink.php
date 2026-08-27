<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use Exception;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\widgets\bootstrap\Button;

/**
 * Class MenuLink
 *
 * Used to render menu link entries.
 *
 * @since 1.4
 * @property $icon
 * @see Menu
 */
class MenuLink extends MenuEntry
{
    /**
     * @var string|array the url or route
     */
    protected $url;
    /**
     * @var Button
     */
    protected $link;

    public function setEncodeLabel(bool $encodeLabel)
    {
        $this->getLink()->encodeLabel($encodeLabel);
        return $this;
    }

    /**
     * @return Button
     */
    public function getLink()
    {
        if (!$this->link) {
            $this->link = Button::none();
        }

        return $this->link;
    }

    /**
     * Renders the link tag for this menu entry
     *
     * @param array $extraHtmlOptions
     * @return string the Html link
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        // Set default HTML options and merge with extra options
        $this->getHtmlOptions($extraHtmlOptions);
        return $this->getLink()->asString();
    }

    public function getHtmlOptions($extraOptions = [])
    {
        if ($this->isActive) {
            $this->getLink()->cssClass('active');
        }

        if ($this->getId()) {
            $this->getLink()->options(['data-menu-id' => $this->getId()]);
        }

        $this->getLink()->options($extraOptions);

        // Add sort order for better debugging
        $this->getLink()->options(['data-sort-order' => $this->getSortOrder()]);


        return $this->getLink()->options;
    }

    public function compare(MenuEntry $entry)
    {
        return parent::compare($entry) || ($entry instanceof self && $this->getUrl() === $entry->getUrl());
    }

    /**
     * @param $label string the label
     * @return static
     */
    public function setLabel($label)
    {
        $this->getLink()->setLabel($label);
        return $this;
    }

    /**
     * @param Button $link Button the label
     * @return static
     */
    public function setLink(Button $link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string the label
     */
    public function getLabel()
    {
        return $this->getLink()->label;
    }

    /**
     * @return Icon the icon
     */
    public function getIcon()
    {
        return $this->getLink()->icon;
    }

    /**
     * @param $icon Icon|string the icon instance or icon name
     * @return static
     * @throws Exception
     */
    public function setIcon($icon)
    {
        $this->getLink()->icon($icon);
        return $this;
    }

    /**
     * Sets the URL
     *
     * @param $url array|string
     * @return static
     */
    public function setUrl($url)
    {
        // we save the raw url
        $this->url = $url;
        $this->getLink()->link($url);
        return $this;
    }


    /**
     * Returns the URL
     *
     * @param bool $asString return the URL as string
     * @return array|string
     */
    public function getUrl($asString = true)
    {
        if ($asString) {
            return $this->getLink()->getHref();
        }

        return $this->url;
    }

    /**
     * @return bool
     */
    public function isPjaxEnabled()
    {
        return $this->getLink()->isPjaxEnabled();
    }

    /**
     * @param bool $pjaxEnabled
     * @return static
     */
    public function setPjaxEnabled($pjaxEnabled)
    {
        $this->getLink()->pjax($pjaxEnabled);
        return $this;
    }

    /**
     * @param array $htmlOptions
     * @return static
     */
    public function setHtmlOptions($htmlOptions)
    {
        $this->getLink()->options($htmlOptions);
        return $this;
    }

    /**
     * @inheritdoc
     *
     * A link entry is data through and through — label, icon, url and html options are all
     * it is — so it describes itself losslessly. The html options are the ones the rendered
     * anchor would have carried, which is what keeps a legacy `data-action-click` entry
     * working after a client rather than the server renders the anchor: the delegated
     * document handler in `humhub.action.js` reads the attribute off the DOM either way.
     *
     * @since 1.20
     */
    public function describe(): ?array
    {
        return [
            'id' => $this->getId(),
            'label' => (string)$this->getLabel(),
            'icon' => static::describeIcon($this->getIcon()),
            'sortOrder' => $this->getSortOrder(),
            'url' => $this->getUrl(),
            'htmlOptions' => $this->getHtmlOptions(),
        ];
    }

    /**
     * Reduces an icon to the plain name a client needs (`pencil`), accepting the shapes an
     * icon can arrive in: an {@see Icon} instance, a bare name, or a `fa-` prefixed name —
     * {@see Icon::run()} strips that prefix at render time, which a described entry never
     * reaches.
     *
     * @param Icon|string|null $icon
     * @since 1.20
     */
    public static function describeIcon($icon): ?string
    {
        if ($icon instanceof Icon) {
            $icon = $icon->name;
        }

        if (!is_string($icon) || $icon === '') {
            return null;
        }

        return str_starts_with($icon, 'fa-') ? substr($icon, 3) : $icon;
    }
}
