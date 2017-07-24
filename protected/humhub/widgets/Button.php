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
class Button extends BootstrapComponent
{

    public $_loader = true;
    public $_link = false;

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
    public static function back($url, $text = null)
    {
        if(!$text) {
            $text = Yii::t('base', 'Back');
        }

        return self::defaultType($text)->link($url)->icon('fa-arrow-left')->right()->loader(true);
    }

    public static function userPickerSelfSelect($selector, $text = null) {
        if(!$text) {
            $text = Yii::t('base', 'Select Me');
        }

        return self::asLink($text)->action('selectSelf', null, $selector)->icon('fa-check-circle-o')->right()->cssClass('input-field-addon');
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

    /**
     * If set to false the [data-pjax-prevent] flag is attached to the link.
     * @param bool $pjax
     * @return $this
     */
    public function pjax($pjax = true)
    {
        if(!$pjax) {
            $this->options(['data-pjax-prevent' => true]);
        }
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
     * Adds a data-action-click handler to the button.
     * @param $handler
     * @param null $url
     * @param null $target
     * @return static
     */
    public function action($handler, $url = null, $target = null)
    {
        return $this->onAction('click', $handler, $url, $target);
    }

    /**
     * Adds a data-action-* handler to the button.
     *
     * @param $event
     * @param $handler
     * @param null $url
     * @param null $target
     * @return $this
     */
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

    /**
     * Adds a confirmation behaviour to the button.
     *
     * @param null $title
     * @param null $body
     * @param null $confirmButtonText
     * @param null $cancelButtonText
     * @return $this
     */
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

    /**
     * @return string renders and returns the actual html element by means of the current settings
     */
    public function renderComponent()
    {
        if($this->_loader) {
            $this->htmlOptions['data-ui-loader'] = '';
        }

        if($this->_link) {
            $href = isset($this->htmlOptions['href']) ? $this->htmlOptions['href'] : null;
            return Html::a($this->getText(), $href, $this->htmlOptions);
        } else {
            return Html::button($this->getText(), $this->htmlOptions);
        }
    }

    public function getWidgetOptions()
    {
        $options = parent::getWidgetOptions();
        $options['_link'] = $this->_link;
        $options['_loader'] = $this->_loader;
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getComponentBaseClass()
    {
        return 'btn';
    }

    /**
     * @inheritdoc
     */
    public function getTypedClass($type)
    {
        return 'btn-'.$type;
    }
}