<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\icon\widgets\Icon;
use yii\base\BaseObject;
use yii\bootstrap\Html;
use yii\helpers\Url;

/**
 * Class MenuEntry
 *
 * A menu entry represents a link inside a navigation.
 *
 * @since 1.4
 * @see Menu
 * @package humhub\modules\ui\widgets
 */
class MenuEntry extends BaseObject
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
     * @var bool mark this entry as active
     */
    protected $isActive = false;

    /**
     * @var Icon the icon
     */
    protected $icon;

    /**
     * @var int the sort order
     */
    protected $sortOrder;

    /**
     * @var string menu entry identifier (optional)
     */
    protected $id;

    /**
     * @var array additional html options for the link HTML tag
     */
    protected $htmlOptions = [];

    /**
     * @var bool use PJAX link if possible
     */
    protected $pjaxEnabled = true;

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var string optional badge (e.g. new item count) not supported by all templates
     */
    protected $badgeText;

    /**
     * Renders the link tag for this menu entry
     *
     * @param $htmlOptions array additional html options for the link tag
     * @return string the Html link
     */
    public function renderLinkTag($htmlOptions = [])
    {
        return Html::a(
            $this->getIcon() . ' ' . $this->getLabel(),
            $this->getUrl(),
            $this->getHtmlOptions($htmlOptions)
        );
    }

    /**
     * @param $label string the label
     */
    public function setLabel($label)
    {
        $this->label = $label;
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
     */
    public function setIcon($icon)
    {
        $this->icon = Icon::get($icon);
    }

    /**
     * Sets the URL
     *
     * @param $url array|string
     */
    public function setUrl($url)
    {
        $this->url = $url;
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
     * @return boolean is active
     */
    public function getIsActive()
    {
        if (is_callable($this->isActive)) {
            call_user_func($this->isActive);
        }

        if ($this->isActive) {
            return true;
        }

        return false;
    }

    /**
     * @param $state boolean
     */
    public function setIsActive($state)
    {
        $this->isActive = $state;
    }

    /**
     * @param $id string the id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string the id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the Html options for the menu entry link tag.
     *
     * @param $extraOptions array additional options to merge
     * @return array
     */
    public function getHtmlOptions($extraOptions = [])
    {
        $options = $this->htmlOptions;

        if (isset($extraOptions['class']) && isset($options['class'])) {
            Html::addCssClass($options, $extraOptions['class']);
        } elseif (isset($extraOptions['class'])) {
            $options['class'] = $extraOptions['class'];
        }

        if ($this->isActive) {
            Html::addCssClass($options, 'active');
        }

        return $options;
    }

    /**
     * @param array $htmlOptions
     */
    public function setHtmlOptions($htmlOptions)
    {
        $this->htmlOptions = $htmlOptions;
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
     */
    public function setPjaxEnabled($pjaxEnabled)
    {
        $this->pjaxEnabled = $pjaxEnabled;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->isVisible;
    }

    /**
     * @param bool $isVisible
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
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
     */
    public function setBadgeText($badgeText)
    {
        $this->badgeText = $badgeText;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Creates MenuEntry by old and deprecated array structure
     *
     * @deprecated since 1.4
     * @param $item
     * @return MenuEntry
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
