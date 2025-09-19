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
     * @param bool $right
     * @return static
     * @throws Exception
     */
    public function setIcon($icon, $right = false)
    {
        $this->getLink()->icon($icon, $right);
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
     * Creates MenuEntry by old and deprecated array structure
     *
     * > Note: In the array icons must be provided in legacy html format.
     *
     * @param $item
     * @return MenuLink
     * @deprecated since 1.4
     */
    public static function createByArray($item)
    {
        $entry = new static();

        if (isset($item['id'])) {
            $entry->id = $item['id'];
        }

        if (isset($item['label'])) {
            $entry->setLabel($item['label']);
        }

        if (isset($item['icon'])) {
            $entry->setIcon($item['icon']);
        }

        if (isset($item['url'])) {
            $entry->setUrl($item['url']);
        }

        if (isset($item['sortOrder'])) {
            $entry->sortOrder = $item['sortOrder'];
        }

        if (isset($item['isActive'])) {
            $entry->isActive = $item['isActive'];
        }

        if (isset($item['isVisible'])) {
            $entry->isVisible = $item['isVisible'];
        }

        if (isset($item['htmlOptions'])) {
            $entry->setHtmlOptions($item['htmlOptions']);
        }

        return $entry;
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
     * Returns the MenuEntry as array structure
     *
     * @return array the menu entry array representation
     * @deprecated since 1.4
     */
    public function toArray()
    {
        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = '';
        }

        return [
            'label' => $this->getLabel(),
            'id' => $this->getId(),
            'icon' => $this->getIcon(),
            'url' => $this->getUrl(),
            'sortOrder' => $this->sortOrder,
            'isActive' => $this->isActive,
            'htmlOptions' => $this->getHtmlOptions(),
        ];
    }

}
