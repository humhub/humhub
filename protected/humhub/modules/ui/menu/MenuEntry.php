<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\menu;

use humhub\modules\ui\menu\widgets\Menu;
use Yii;
use yii\base\BaseObject;
use yii\bootstrap\Html;

/**
 * Class MenuEntry
 *
 * An abstract menu entry class. Subclasses need to extend the [[render()]] function.
 *
 * @since 1.4
 * @see Menu
 */
abstract class MenuEntry extends BaseObject
{
    /**
     * @var string menu entry identifier (optional)
     */
    protected $id;

    /**
     * @var int the sort order
     */
    protected $sortOrder;

    /**
     * @var array additional html options for the link HTML tag
     */
    protected $htmlOptions = [];

    /**
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var bool mark this entry as active
     */
    protected $isActive = false;

    /**
     * Renders the entry html, this template function should respect [[htmlOptions]] array by calling [[getHtmlOptions()]] and passing
     * the $extraHtmlOptions array as for example:
     *
     * ```php
     *
     * return Html::a($label, $url, $this->getHtmlOptions($extraHtmlOptions));
     *
     * ```
     *
     * @param array $extraHtmlOptions
     * @return string the Html link
     */
    abstract protected function renderEntry($extraHtmlOptions = []);

    /**
     * Public accessible render function responsible for rendering this entry.
     *
     * @param array $extraHtmlOptions
     * @return string
     */
    public function render($extraHtmlOptions = []) {
        if(!$this->isVisible) {
            return '';
        }

        return $this->renderEntry($extraHtmlOptions);
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
     * @return static
     */
    public function setIsActive($state)
    {
        $this->isActive = $state;
        return $this;
    }

    /**
     * Activates this MenuEntry in case the given moduleId, controllerId and actionId matches the current request.
     * @param string $moduleId controller module id
     * @param array|string $controllerIds controller id
     * @param array|string $actionIds action id
     * @return static
     */
    public function setIsActiveState($moduleId, $controllerIds = [], $actionIds = [])
    {
        $this->isActive = static::isActiveState($moduleId,$controllerIds,$actionIds);
        return $this;
    }

    public static function isActiveState($moduleId = null, $controllerIds = [], $actionIds = [])
    {
         if($moduleId && (!Yii::$app->controller->module || Yii::$app->controller->module->id !== $moduleId)) {
            return false;
        }

        if(empty($controllerIds) && empty($actionIds)) {
            return true;
        }

        if($controllerIds && !is_array($controllerIds)) {
            $controllerIds = [$controllerIds];
        }

        if(!empty($controllerIds) && !in_array(Yii::$app->controller->id, $controllerIds)) {
            return false;
        }

        if($actionIds && !is_array($actionIds)) {
            $actionIds = [$actionIds];
        }

        if(!empty($actionIds) && !in_array(Yii::$app->controller->action->id, $actionIds)) {
            return false;
        }

        return true;
    }


    /**
     * @param $id string the id
     * @return static
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string the id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Compares this entry with the given entry
     * @param MenuEntry $entry
     * @return bool
     */
    public function compare(MenuEntry $entry)
    {
        return !empty($this->getId()) && $this->getId() === $entry->getId();
    }

    /**
     * Returns the Html options for the menu entry link tag.
     *
     * @return array
     */
    public function getHtmlOptions($extraOptions = [])
    {
        $options = $this->htmlOptions;

        if(isset($extraOptions['class'])) {
            Html::addCssClass($options, $extraOptions['class']);
        }

        if(isset($extraOptions['style'])) {
            Html::addCssStyle($options, $extraOptions['style']);
        }

        if ($this->isActive) {
            Html::addCssClass($options, 'active');
        }

        if($this->getId()) {
            $options['data-menu-id'] = $this->id;
        }

        return array_merge($extraOptions, $options);
    }

    /**
     * @param array $htmlOptions
     * @return static
     */
    public function setHtmlOptions($htmlOptions)
    {
        $this->htmlOptions = $htmlOptions;
        return $this;
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
     * @return static
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;
        return $this;
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
     * @return static
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }
}
