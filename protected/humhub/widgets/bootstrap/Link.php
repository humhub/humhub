<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

/**
 * Creates links using the Button::asLink() method.
 *
 * Usage examples:
 *
 * ```
 * Link::primary()->link(['/index'])->icon('info')
 * ```
 */
class Link extends Button
{
    public bool $asLink = true;

    public static function to($text, $url = '#', $pjax = true)
    {
        return self::asLink($text, $url)->pjax($pjax);
    }

    public static function withAction($text, $action, $url = null, $target = null)
    {
        return self::asLink($text)->action($action, $url, $target);
    }

    /**
     * @param $url
     * @return $this
     */
    public function post($url)
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
    public function href($url = '#', $pjax = true)
    {
        $this->link($url);
        $this->pjax($pjax);
        return $this;
    }

    public function target($target)
    {
        $this->options['target'] = $target;
        return $this;
    }

    public function blank()
    {
        return $this->target('_blank');
    }
}
