<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\ui\menu\widgets\Menu;
use humhub\libs\Html;
use humhub\widgets\Link;
use Yii;
use yii\helpers\Url;

/**
 * Class MenuLink
 *
 * Used to render menu link entries.
 *
 * @since 1.4
 * @see Menu
 */
class MenuLink extends MenuEntry
{
    /**
     * @var string the label of the menu entry
     */
    protected $label;

    /**
     * @var string|array the url or route
     */
    protected $url;

    /**
     * @var Icon the icon
     */
    protected $icon;

    /**
     * @var bool use PJAX link if possible
     */
    protected $pjaxEnabled = true;

    /**
     * @var string optional badge (e.g. new item count) not supported by all templates
     */
    protected $badgeText;

    /**
     * @var Link
     */
    protected $link;

    /**
     * Renders the link tag for this menu entry
     *
     * @param array $extraHtmlOptions
     * @return string the Html link
     */
    public function renderEntry($extraHtmlOptions = [])
    {
        if($this->link) {
            return $this->link.'';
        }

        return Html::a(
            $this->getIcon() . ' ' . $this->getLabel(),
            $this->getUrl(),
            $this->getHtmlOptions($extraHtmlOptions)
        );
    }

    public function getHtmlOptions($extraOptions = [])
    {
        $options = parent::getHtmlOptions($extraOptions);

        if(!$this->pjaxEnabled) {
            Html::addPjaxPrevention($options);
        }

        return array_merge($extraOptions, $options);
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
        $this->label = $label;
        return $this;
    }

    /**
     * @return Link
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param $link Link the label
     * @return static
     */
    public function setLink(Link $link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string the label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return Icon the icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param $icon Icon|string the icon instance or icon name
     * * @return static
     */
    public function setIcon($icon)
    {
        $this->icon = Icon::get($icon);
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
        $this->url = $url;
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
            return Url::to($this->url);
        }

        return $this->url;
    }

    /**
     * @return bool
     */
    public function isPjaxEnabled()
    {
        return $this->pjaxEnabled;
    }

    /**
     * @param bool $pjaxEnabled
     * @return static
     */
    public function setPjaxEnabled($pjaxEnabled)
    {
        $this->pjaxEnabled = $pjaxEnabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getBadgeText()
    {
        return $this->badgeText;
    }

    /**
     * @param string $badgeText
     * @return static
     */
    public function setBadgeText($badgeText)
    {
        $this->badgeText = $badgeText;
        return $this;
    }

    /**
     * Creates MenuEntry by old and deprecated array structure
     *
     * @deprecated since 1.4
     * @param $item
     * @return MenuLink
     */
    public static function createByArray($item)
    {
        $entry = new static;

        if (isset($item['id'])) {
            $entry->id = $item['id'];
        }

        if (isset($item['label'])) {
            $entry->label = $item['label'];
        }

        if (isset($item['icon'])) {
            $entry->icon = $item['icon'];
        }

        if (isset($item['url'])) {
            $entry->url = $item['url'];
        }

        if (isset($item['sortOrder'])) {
            $entry->sortOrder = $item['sortOrder'];
        }

        if (isset($item['isActive'])) {
            $entry->isActive = $item['isActive'];
        }

        if (isset($item['htmlOptions'])) {
            $entry->isActive = $item['htmlOptions'];
        }

        return $entry;
    }

    /**
     * Returns the MenuEntry as array structure
     *
     * @deprecated since 1.4
     * @return array the menu entry array representation
     */
    public function toArray()
    {
        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = '';
        }

        return [
            'label' => $this->label,
            'id' => $this->id,
            'icon' => $this->icon,
            'url' => $this->url,
            'sortOrder' => $this->sortOrder,
            'isActive' => $this->isActive,
            'htmlOptions' => $this->htmlOptions
        ];
    }

}
