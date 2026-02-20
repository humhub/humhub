<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\helpers\ControllerHelper;
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
     * @deprecated since 1.19 use icon() instead
     */
    public function setIcon($icon, $right = false)
    {
        $this->getLink()->icon($icon, $right);
        return $this;
    }

    /**
     * Sets the icon
     *
     * @param $icon Icon|string the icon instance or icon name
     * @param bool $right
     * @return static
     * @since 1.19
     */
    public function icon(string|Icon $icon, bool $right = false, array $options = []): static
    {
        $this->getLink()->icon($icon, $right, $options);
        return $this;
    }

    /**
     * Sets the URL
     *
     * @param $url array|string
     * @return static
     */
    public function link(string|array $url, bool $pjax = true): static
    {
        // we save the raw url
        $this->url = $url;
        $this->getLink()->link($url, $pjax);
        return $this;
    }

    /**
     * @deprecated since 1.19 use link() instead
     */
    public function setUrl($url)
    {
        return $this->link($url);
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
     * Sets the ID
     *
     * @param string $id
     * @return static
     * @since 1.19
     */
    public function id(string $id): static
    {
        return $this->setId($id);
    }

    /**
     * Sets the sort order
     *
     * @param int $sortOrder
     * @return static
     * @since 1.19
     */
    public function sortOrder(int $sortOrder): static
    {
        return $this->setSortOrder($sortOrder);
    }

    /**
     * Sets the active state
     *
     * @param bool|string|null $moduleIdOrIsActive
     * @param string|array $controllerIds
     * @param string|array $actionIds
     * @param array $queryParams
     * @return static
     * @since 1.19
     */
    public function active(string|bool|null $moduleIdOrIsActive = null, string|array $controllerIds = [], string|array $actionIds = [], array $queryParams = []): static
    {
        if (!is_bool($moduleIdOrIsActive)) {
            $moduleIdOrIsActive = ControllerHelper::isActivePath($moduleIdOrIsActive, $controllerIds, $actionIds, $queryParams);
        }

        return $this->setIsActive($moduleIdOrIsActive);
    }

    /**
     * Sets the visible state
     *
     * @param bool $isVisible
     * @return static
     * @since 1.19
     */
    public function visible(bool $isVisible): static
    {
        return $this->setIsVisible($isVisible);
    }

    /**
     * Sets the CSS class
     *
     * @param array|string $cssClass
     * @return static
     * @since 1.19
     */
    public function cssClass(array|string $cssClass): static
    {
        $this->getLink()->cssClass($cssClass);
        return $this;
    }
}
