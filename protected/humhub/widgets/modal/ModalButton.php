<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\modal;

use humhub\widgets\bootstrap\Button;
use Yii;

/**
 * Usage examples:
 *
 * ```
 * <?= ModalButton::save() ?>
 * <?= ModalButton::primary()->submit() ?>
 * <?= ModalButton::cancel() ?>
 * ```
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

    public function show($target)
    {
        return $this->action('ui.modal.show', null, $target);
    }

    /**
     * @since 1.18
     */
    public function submit($url = null): static
    {
        $this->action('ui.modal.submit', $url);
        return parent::submit();
    }

    /**
     * @since 1.18
     */
    public static function save($label = null, $url = null): static
    {
        return parent::save($label)->action('ui.modal.submit', $url);
    }

    /**
     * @param null $url
     * @param null $text
     * @return Button
     * @deprecated since 1.18
     */
    public static function submitModal($url = null, $text = null)
    {
        if (!$text) {
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
        if (!$text) {
            $text = Yii::t('base', 'Cancel');
        }

        return static::secondary($text)->close()->loader(false);
    }

    /**
     * @return $this
     */
    public function close()
    {
        return $this->options(['data-modal-close' => '']);
    }
}
