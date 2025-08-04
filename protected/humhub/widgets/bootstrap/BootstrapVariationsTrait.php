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
    /**
     * @var Icon|null the icon to be displayed before the label.
     */
    public Icon|null $icon = null;

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

    public static function primary(string $label = null): static
    {
        return static::instance($label, 'primary');
    }

    public static function secondary(string $label = null): static
    {
        return static::instance($label, 'secondary');
    }

    public static function info(string $label = null): static
    {
        return static::instance($label, 'info');
    }

    public static function success(string $label = null): static
    {
        return static::instance($label, 'success');
    }

    public static function warning(string $label = null): static
    {
        return static::instance($label, 'warning');
    }

    public static function danger(string $label = null): static
    {
        return static::instance($label, 'danger');
    }

    public static function light(string $label = null): static
    {
        return static::instance($label, 'light');
    }

    public static function dark(string $label = null): static
    {
        return static::instance($label, 'dark');
    }

    public static function none(string $label = null): static
    {
        return static::instance($label);
    }

    public function icon(string|Icon $icon, bool $right = false, $options = []): static
    {
        // Extract icon from FontAwesome 4 HTML element
        // TODO: remove later ($icon should be the name of the Icon or an instance of Icon)
        $matches = [];
        if (is_string($icon) && preg_match('/fa-([a-z0-9-]+)/', $icon, $matches)) {
            $icon = $matches[1] ?? null;
        }

        $this->icon = ($icon instanceof Icon) ? $icon : Icon::get($icon, $options);

        if ($right) {
            $this->icon->right();
        }

        return $this;
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

    public function right(bool $right = true): static
    {
        if ($right) {
            Html::removeCssClass($this->options, 'float-start');
            Html::addCssClass($this->options, 'float-end');
        } else {
            Html::removeCssClass($this->options, 'float-end');
        }

        return $this;
    }

    public function left(bool $left = true): static
    {
        if ($left) {
            Html::removeCssClass($this->options, 'float-end');
            Html::addCssClass($this->options, 'float-start');
        } else {
            Html::removeCssClass($this->options, 'float-start');
        }

        return $this;
    }

    public function id(?string $id): static
    {
        return $this->options(['id' => $id]);
    }

    /**
     * Adds an HTML title attribute
     */
    public function title(?string $title): static
    {
        return $title ? $this->options(['title' => $title]) : $this;
    }

    /**
     * Adds a title + tooltip behaviour class
     */
    public function tooltip(?string $title): static
    {
        return $title ? $this->options(['data-bs-title' => $title])->cssClass('tt') : $this;
    }

    public function cssClass(array|string $cssClass): static
    {
        Html::addCssClass($this->options, $cssClass);

        return $this;
    }

    public function style(string|array $style): static
    {
        Html::addCssStyle($this->options, $style);

        return $this;
    }

    public function options(array $options): static
    {
        if (isset($options['class'])) {
            $this->cssClass($options['class']);
            unset($options['class']);
        }

        if (isset($options['style'])) {
            $this->style($options['style']);
            unset($options['style']);
        }

        $this->options = ArrayHelper::merge($this->options, $options);

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

    /**
     * @param string $color Hexadecimal, RGB, RGBA, HSL, HSLA
     */
    public function cssBgColor(?string $color): static
    {
        if ($color) {
            $this->style('background-color:' . $color . ' !important');
        }
        return $this;
    }

    /**
     * @param string $color Hexadecimal, RGB, RGBA, HSL, HSLA
     */
    public function cssTextColor(?string $color): static
    {
        if ($color) {
            $this->style('color:' . $color . ' !important');
        }
        return $this;
    }
}
