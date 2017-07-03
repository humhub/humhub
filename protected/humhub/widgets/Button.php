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
 * Date: 13.06.2017
 * Time: 22:32
 */

namespace humhub\widgets;


use humhub\components\Widget;
use humhub\libs\Html;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Helper class for creating buttons.
 *
 *  e.g:
 *
 * `<?= Button::primary('Some Text')->actionClick('myHandler', [/some/url])->sm() ?>`
 *
 * @package humhub\widgets
 */
class Button extends Widget
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

    public $_loader = true;

    public $_link = false;

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
    public static function back($url, $text = null)
    {
        if(!$text) {
            $text = Yii::t('base', 'Back');
        }

        $instance = self::defaultType($text);
        return $instance->link($url)->icon('fa-arrow-left')->right();
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
     * @param string $href
     * @return static
     */
    public static function asLink($text = null, $href = '#')
    {
        return self::none($text)->link($href);
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
    public static function save($text = null)
    {
        if(!$text) {
            $text = Yii::t('base', 'Save');
        }

        return self::primary($text);
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
     * @param bool $active
     * @return $this
     */
    public function loader($active = true)
    {
        $this->_loader = $active;
        return $this;
    }

    /**
     * @param null $url
     * @return $this
     */
    public function link($url = null, $pjax = true)
    {
        $this->_link = true;
        $this->loader(false);
        $this->htmlOptions['href'] = Url::to($url);

        $this->pjax($pjax);

        return $this;
    }

    public function pjax($pjax = true)
    {
        if(!$pjax) {
            $this->options(['data-pjax-prevent' => true]);
        }
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
     * @return $this
     */
    public function submit()
    {
        $this->htmlOptions['type'] = 'submit';
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

    /**
     * @param $handler
     * @param null $url
     * @param null $target
     * @return Button
     */
    public function action($handler, $url = null, $target = null)
    {
        return $this->onAction('click', $handler, $url, $target);
    }

    public function onAction($event, $handler, $url = null, $target = null)
    {
        $this->htmlOptions['data-action-'.$event] = $handler;

        if($url) {
            $this->htmlOptions['data-action-'.$event.'-url'] = Url::to($url);
        }

        if($target) {
            $this->htmlOptions['data-action-'.$event.'-target'] = $target;
        }

        return $this;
    }

    public function confirm($title = null, $body = null, $confirmButtonText = null, $cancelButtonText = null)
    {
        if($title) {
            $this->htmlOptions['data-action-confirm-header'] = $title;
        }

        if($body) {
            $this->htmlOptions['data-action-confirm'] = $body;
        } else {
            $this->htmlOptions['data-action-confirm'] = '';
        }

        if($confirmButtonText) {
            $this->htmlOptions['data-action-confirm-text'] = $confirmButtonText;
        }

        if($cancelButtonText) {
            $this->htmlOptions['data-action-cancel-text'] = $cancelButtonText;
        }

        return $this;
    }

    public function icon($content, $raw = false)
    {
        if(!$raw) {
            $this->icon(Html::tag('i', '', ['class' => 'fa '.$content]), true);
        } else {
            $this->_icon = $content;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->setCssClass();

        if($this->_loader) {
            $this->htmlOptions['data-ui-loader'] = '';
        }

        $this->htmlOptions['id'] = $this->getId(true);

        if($this->_link) {
            $href = isset($this->htmlOptions['href']) ? $this->htmlOptions['href'] : null;
            return Html::a($this->getText(), $href, $this->htmlOptions);
        } else {
            return Html::button($this->getText(), $this->htmlOptions);
        }
    }

    protected function setCssClass()
    {
        if($this->type !== self::TYPE_NONE) {
            Html::addCssClass($this->htmlOptions, 'btn');
            Html::addCssClass($this->htmlOptions, 'btn-'.$this->type);
        }
    }

    protected function getText()
    {
        if($this->_icon) {
            return $this->_icon.' '.$this->text;
        }

        return $this->text;
    }

    public function visible($isVisible = true) {
        $this->_visible = $isVisible;
        return $this;
    }

    public function __toString()
    {
        $result = $this::widget([
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'htmlOptions' => $this->htmlOptions,
            '_icon' => $this->_icon,
            '_link' => $this->_link,
            '_loader' => $this->_loader,
            'render' => $this->_visible
        ]);

        return $result ? $result : '';
    }

}