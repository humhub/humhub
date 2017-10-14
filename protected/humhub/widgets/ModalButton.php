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
 * `Button::primary()->`
 *
 * @package humhub\widgets
 */
class ModalButton extends Button
{
    /**
     * @param $url
     * @return $this
     */
    public function load($url)
    {
        return $this->action('ui.modal.load', $url)->loader(false);
    }

    public function post($url)
    {
        return $this->action('ui.modal.post', $url)->loader(false);
    }

    /**
     * @param null $url
     * @param null $text
     * @return Button
     */
    public static function submitModal($url = null, $text = null)
    {
        if(!$text) {
            $text = Yii::t('base', 'Save');
        }

        return static::save($text)->submit()->action('ui.modal.submit', $url);
    }

    /**
     * @param null $text
     * @return $this
     */
    public static function cancel($text = null)
    {
        if(!$text) {
            $text = Yii::t('base', 'Cancel');
        }

        return static::defaultType($text)->options(['data-modal-close' => ''])->loader(false);
    }

    /**
     * @return $this
     */
    public function close()
    {
        return $this->options(['data-modal-close' => '']);
    }
}