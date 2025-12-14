<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;
use yii\helpers\ArrayHelper;

/**
 * Provides methods for generating bootstrap variation components, such as badges or buttons,
 * with additional configurations for icons, sizes, alignment, and HTML attributes.
 *
 * @since 1.18
 */
trait BootstrapVariationsTrait
{

    public ?string $size = null;

    /**
     * @var array the HTML attributes for the widget container tag.
     * @deprecated since 1.18 use [[options]] instead
     */
    public array $htmlOptions = [];
    public bool $_visible = true;

    public function __toString(): string
    {
        return $this->_visible ? $this->run() : '';
    }

    abstract public static function instance(?string $text = null, ?string $color = null): static;

    /**
     * @deprecated since 1.18 use [[secondary]] instead
     */
    public static function defaultType($text = null)
    {
        return self::light($text);
    }

    public static function primary(?string $label = null): static
    {
        return static::instance($label, 'primary');
    }

    public static function secondary(?string $label = null): static
    {
        return static::instance($label, 'secondary');
    }

    public static function info(?string $label = null): static
    {
        return static::instance($label, 'info');
    }

    public static function accent(?string $label = null): static
    {
        return static::instance($label, 'accent');
    }

    public static function success(?string $label = null): static
    {
        return static::instance($label, 'success');
    }

    public static function warning(?string $label = null): static
    {
        return static::instance($label, 'warning');
    }

    public static function danger(?string $label = null): static
    {
        return static::instance($label, 'danger');
    }

    public static function light(?string $label = null): static
    {
        return static::instance($label, 'light');
    }

    public static function dark(?string $label = null): static
    {
        return static::instance($label, 'dark');
    }

    public static function none(?string $label = null): static
    {
        return static::instance($label);
    }

    /**
     * @deprecated since 1.18 use [[sm]] instead
     */
    public function xs(): static
    {
        return $this->sm();
    }

    public function sm(): static
    {
        $this->size = 'sm';
        return $this;
    }

    public function lg(): static
    {
        $this->size = 'lg';
        return $this;
    }



    public function asString(): string
    {
        return (string)$this;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function visible($isVisible = true): static
    {
        $this->_visible = $isVisible;
        return $this;
    }

    /**
     * @deprecated since 1.18
     * Use `static::instance($text, $color)` instead for a Bootstrap color
     * Or `cssBgColor()` for a custom color (Hexadecimal, RGB, RGBA, HSL, HSLA)
     */
    public function color($color)
    {
        return $this;
    }

    /**
     * @deprecated since 1.18
     * Use `cssTextColor()` instead
     */
    public function textColor($color)
    {
        return $this;
    }


}
