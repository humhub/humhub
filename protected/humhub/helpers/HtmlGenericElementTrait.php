<?php

namespace humhub\helpers;

use yii\helpers\ArrayHelper;

trait HtmlGenericElementTrait
{
    abstract protected function &getOptionsRef(): array;

    public function id(?string $id): static
    {
        $this->getOptionsRef()['id'] = $id;
        return $this;
    }

    public function right(bool $right = true): static
    {
        if ($right) {
            Html::removeCssClass($this->getOptionsRef(), 'float-start');
            Html::addCssClass($this->getOptionsRef(), 'float-end');
        } else {
            Html::removeCssClass($this->getOptionsRef(), 'float-end');
        }

        return $this;
    }

    public function left(bool $left = true): static
    {
        if ($left) {
            Html::removeCssClass($this->getOptionsRef(), 'float-end');
            Html::addCssClass($this->getOptionsRef(), 'float-start');
        } else {
            Html::removeCssClass($this->getOptionsRef(), 'float-start');
        }

        return $this;
    }

    /**
     * Adds an HTML title attribute
     */
    public function title(?string $title): static
    {
        $this->getOptionsRef()['title'] = $title;
        return $this;
    }

    public function cssClass(array|string $cssClass): static
    {
        Html::addCssClass($this->getOptionsRef(), $cssClass);
        return $this;
    }

    public function style(string|array $style): static
    {
        Html::addCssStyle($this->getOptionsRef(), $style);

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

        $optionsRef = $this->getOptionsRef();
        $optionsRef = ArrayHelper::merge($this->getOptionsRef(), $options);

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
