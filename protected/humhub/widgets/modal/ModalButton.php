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
 * <?= ModalButton::save()->submit() ?>
 * <?= ModalButton::cancel() ?>
 * ```
 */
class ModalButton extends Button
{
    /**
     * @param $url
     * @return $this
     */
    public function load($url): static
    {
        return $this->action('ui.modal.load', $url)->loader(false);
    }

    public function post($url): static
    {
        return $this->action('ui.modal.post', $url)->loader(false);
    }

    public function show($target): static
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
     * @param null $text
     * @return $this
     */
    public static function cancel($text = null): static
    {
        if (!$text) {
            $text = Yii::t('base', 'Cancel');
        }

        return static::light($text)->close()->loader(false);
    }

    /**
     * @return $this
     */
    public function close(): static
    {
        return $this->options(['data-modal-close' => '']);
    }
}
