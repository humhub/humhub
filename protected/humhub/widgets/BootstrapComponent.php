<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 21.07.2017
 * Time: 21:31
 */

namespace humhub\widgets;


use humhub\components\Widget;
use yii\helpers\ArrayHelper;
use humhub\libs\Html;

abstract class BootstrapComponent extends Widget
{
    const TYPE_PRIMARY = 'primary';
    const TYPE_DEFAULT = 'default';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';
    const TYPE_SUCCESS = 'success';
    const TYPE_NONE = 'none';

    public $type;
    public $htmlOptions = [];
    public $text;
    public $_icon;
    public $_iconRight;

    public $_visible = true;

    /**
     * @param string $text Button text
     * @return static
     */
    public static function instance($text = null)
    {
        return new static(['type' => self::TYPE_NONE, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function none($text = null)
    {
        return new static(['type' => self::TYPE_NONE, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function primary($text = null)
    {
        return new static(['type' => self::TYPE_PRIMARY, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function defaultType($text = null)
    {
        return new static(['type' => self::TYPE_DEFAULT, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function info($text = null)
    {
        return new static(['type' => self::TYPE_INFO, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function warning($text = null)
    {
        return new static(['type' => self::TYPE_WARNING, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function success($text = null)
    {
        return new static(['type' => self::TYPE_SUCCESS, 'text' => $text]);
    }

    /**
     * @param string $text Button text
     * @return static
     */
    public static function danger($text = null)
    {
        return new static(['type' => self::TYPE_DANGER, 'text' => $text]);
    }

    /**
     * @param $color
     * @param null $text
     * @return $this
     */
    public static function asColor($color, $text = null)
    {
        return static::info($text)->color($color);
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @param bool $right
     * @return $this
     */
    public function right($right = true)
    {
        if($right) {
            Html::removeCssClass($this->htmlOptions,'pull-left');
            Html::addCssClass($this->htmlOptions, 'pull-right');
        } else {
            Html::removeCssClass($this->htmlOptions,'pull-right');
        }

        return $this;
    }

    /**
     * @param bool $left
     * @return $this
     */
    public function left($left = true)
    {
        if($left) {
            Html::removeCssClass($this->htmlOptions,'pull-right');
            Html::addCssClass($this->htmlOptions, 'pull-left');
        } else {
            Html::removeCssClass($this->htmlOptions,'pull-left');
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function sm()
    {
        Html::addCssClass($this->htmlOptions, 'btn-sm');
        return $this;
    }

    /**
     * @return $this
     */
    public function lg()
    {
        Html::addCssClass($this->htmlOptions, 'btn-lg');
        return $this;
    }

    /**
     * @return $this
     */
    public function xs()
    {
        Html::addCssClass($this->htmlOptions, 'btn-xs');
        return $this;
    }

    /**
     * @param $style
     * @return $this
     */
    public function style($style)
    {
        Html::addCssStyle($this->htmlOptions, $style);
        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $cssClass
     * @return $this
     */
    public function cssClass($cssClass)
    {
        Html::addCssClass($this->htmlOptions, $cssClass);
        return $this;
    }

    /**
     * @param $options
     * @return $this
     */
    public function options($options)
    {
        if(isset($options['class'])) {
            $this->cssClass($options['class']);
            unset($options['class']);
        }

        if(isset($options['style'])) {
            $this->style($options['style']);
            unset($options['style']);
        }

        $this->htmlOptions = ArrayHelper::merge($this->htmlOptions, $options);
        return $this;
    }

    public function icon($content, $right = false, $raw = false)
    {
        if(!$raw) {
            $this->icon(Html::tag('i', '', ['class' => 'fa '.$content]), $right, true);
        } else {
            $this->_icon = $content;
            $this->_iconRight = $right;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->setCssClass();

        $this->htmlOptions['id'] = $this->getId(true);

        return $this->renderComponent();
    }

    public function color($color)
    {
        $this->style('background-color:'.$color);
        return $this;
    }

    public function textColor($color)
    {
        $this->style('color:'.$color);
        return $this;
    }

    /**
     * @return string renders and returns the actual html element by means of the current settings
     */
    public abstract function renderComponent();

    /**
     * @return string the bootstrap css base class
     */
    public abstract function getComponentBaseClass();

    /**
     * @return string the bootstrap css class by $type
     */
    public abstract function getTypedClass($type);

    protected function setCssClass()
    {
        if($this->type !== self::TYPE_NONE) {
            Html::addCssClass($this->htmlOptions, $this->getComponentBaseClass());
            Html::addCssClass($this->htmlOptions, $this->getTypedClass($this->type));
        }
    }

    protected function getText()
    {
        if($this->_icon) {
            return ($this->_iconRight) ? $this->text.' '.$this->_icon : $this->_icon.' '.$this->text;
        }

        return $this->text;
    }

    public function visible($isVisible = true) {
        $this->_visible = $isVisible;
        return $this;
    }

    public function __toString()
    {
        $result = $this::widget($this->getWidgetOptions());
        return $result ? $result : '';
    }

    /**
     * @return array all options required for rendering the widget
     */
    public function getWidgetOptions()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'htmlOptions' => $this->htmlOptions,
            '_icon' => $this->_icon,
            '_iconRight' => $this->_iconRight,
            'render' => $this->_visible
        ];
    }
}