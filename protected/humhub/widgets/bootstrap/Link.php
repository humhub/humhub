<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

use humhub\widgets\modal\ModalButton;

/**
 * Creates links.
 *
 * Usage examples:
 *
 * ```
 * Link::to('Text', ['/index'])->icon('info')
 * Link::primary()->link(['/index'])->icon('info')
 * Link::modal('Modal text')->load(['/modal-url'])->icon('info')
 * ```
 */
class Link extends Button
{
    /**
     * @inheritdoc
     */
    public bool $asLink = true;

    /**
     * @inheritdoc
     */
    public static function instance(?string $text = null, ?string $color = null): static
    {
        return parent::instance($text, $color)->cssClass('link');
    }

    public static function to(?string $text = null, $url = '#', bool $pjax = true): static
    {
        return static::none($text)->link($url)->pjax($pjax);
    }

    public static function withAction(?string $text, string $action, $url = null, $target = null): static
    {
        return static::none($text)->action($action, $url, $target);
    }

    /**
     * Creates a link for opening a modal window
     *
     * @param string|null $text
     * @param string|array $url
     * @return ModalButton
     * @since 1.19
     */
    public static function modal(?string $text = null, $url = '#'): ModalButton
    {
        return ModalButton::none($text)->link($url)->cssClass('link');
    }

    /**
     * @param $url
     * @return $this
     */
    public function post($url): static
    {
        // Note data-method automatically prevents pjax
        $this->href($url);
        $this->options['data-method'] = 'POST';
        return $this;
    }

    /**
     * @param string $url
     * @param bool $pjax
     * @return $this
     */
    public function href($url = '#', $pjax = true): static
    {
        $this->link($url);
        $this->pjax($pjax);
        return $this;
    }

    public function target($target): static
    {
        $this->options['target'] = $target;
        return $this;
    }

    public function blank(): static
    {
        return $this->target('_blank');
    }
}
