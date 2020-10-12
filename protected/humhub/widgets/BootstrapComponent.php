<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets;

use humhub\components\Widget;
use humhub\libs\Html;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * BootstrapComponent is an abstract class used to define bootstrap based ui components and provides common
 * features as sizing, color, text and alignment configuration.
 *
 * This class follows the builder pattern for instantiation and configuration. By default this class provides the following
 * static initializers:
 *
 *  - none
 *  - primary
 *  - defaultType
 *  - info
 *  - warn
 *  - danger
 *
 * Example:
 *
 * ```
 * // Set only text
 * BootstrapComponent::instance('My Label')->right();
 *
 * // Component with primary color and text
 * BootstrapComponent::primary('My Label');
 * ```
 *
 *
 *
 * @package humhub\widgets
 */
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
    public $encode = false;
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
        if ($right) {
            Html::removeCssClass($this->htmlOptions, 'pull-left');
            Html::addCssClass($this->htmlOptions, 'pull-right');
        } else {
            Html::removeCssClass($this->htmlOptions, 'pull-right');
        }

        return $this;
    }

    /**
     * @param bool $left
     * @return $this
     */
    public function left($left = true)
    {
        if ($left) {
            Html::removeCssClass($this->htmlOptions, 'pull-right');
            Html::addCssClass($this->htmlOptions, 'pull-left');
        } else {
            Html::removeCssClass($this->htmlOptions, 'pull-left');
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
     * Adds an html title attribute
     * @param $title
     * @return $this
     * @since 1.3
     */
    public function title($title)
    {
        return $this->options(['title' => $title]);
    }

    /**
     * Adds an title + tooltip behaviour class
     * @param $id
     * @return $this
     * @since 1.3
     */
    public function tooltip($title)
    {
        return $this->title($title)->cssClass('tt');
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
        if (isset($options['class'])) {
            $this->cssClass($options['class']);
            unset($options['class']);
        }

        if (isset($options['style'])) {
            $this->style($options['style']);
            unset($options['style']);
        }

        $this->htmlOptions = ArrayHelper::merge($this->htmlOptions, $options);

        return $this;
    }

    /**
     * @param $content
     * @param bool $right
     * @param bool $raw
     * @return $this
     * @throws \Exception
     */
    public function icon($content, $right = false, $raw = false)
    {
        if (!empty($content)) {
            if (!$raw) {
                $this->icon(Icon::get($content)->right($right)->asString(), $right, true);
            } else {
                $this->_icon = $content;
                $this->_iconRight = $right;
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->setCssClass();

        if($this->getId(false)) {
            $this->htmlOptions['id'] = $this->getId(false);
        }

        return $this->renderComponent();
    }

    public function color($color)
    {
        if ($color) {
            $this->style('background-color:' . $color);
        }
        return $this;
    }

    public function textColor($color)
    {
        $this->style('color:' . $color);
        return $this;
    }

    /**
     * @return string renders and returns the actual html element by means of the current settings
     */
    abstract public function renderComponent();

    /**
     * @return string the bootstrap css base class
     */
    abstract public function getComponentBaseClass();

    /**
     * @return string the bootstrap css class by $type
     */
    abstract public function getTypedClass($type);

    protected function setCssClass()
    {
        if ($this->type !== self::TYPE_NONE) {
            Html::addCssClass($this->htmlOptions, $this->getComponentBaseClass());
            Html::addCssClass($this->htmlOptions, $this->getTypedClass($this->type));
        }
    }

    protected function getText()
    {
        $text = ($this->encode) ? Html::encode($this->text) : $this->text;
        if ($this->_icon) {
            return ($this->_iconRight) ? $text.' '.$this->_icon : $this->_icon.' '.$text;
        }

        return $text;
    }

    public function visible($isVisible = true)
    {
        $this->_visible = $isVisible;

        return $this;
    }

    /**
     * @return string
     * @since 1.4
     */
    public function asString()
    {
        return (string) $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $result = $this::widget($this->getWidgetOptions());
            return $result ?: '';
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return '';
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
            'encode' => $this->encode,
            '_icon' => $this->_icon,
            '_iconRight' => $this->_iconRight,
            'render' => $this->_visible
        ];
    }
}
