<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

use humhub\helpers\Html;
use Yii;
use yii\helpers\Url;

/**
 * Provides an extension of the yii\bootstrap5\Button class with additional features.
 *
 * Usage examples:
 *
 * ```
 * Button::primary()->link(['/index'])->icon('info')
 * ```
 */
class Button extends \yii\bootstrap5\Button
{
    use BootstrapVariationsTrait;

    /**
     * If string, the loader is active and a custom loader text is displayed
     */
    public bool|string $loader = true;

    /**
     * @inerhitdoc
     */
    public $encodeLabel = false;

    public bool $asLink = false;

    /**
     * @deprecated since 1.18
     */
    public const TYPE_PRIMARY = 'primary';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_DEFAULT = self::TYPE_SECONDARY;
    /**
     * @deprecated since 1.18
     */
    public const TYPE_SECONDARY = 'secondary';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_INFO = 'info';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_WARNING = 'warning';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_DANGER = 'danger';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_SUCCESS = 'success';
    /**
     * @deprecated since 1.18
     */
    public const TYPE_NONE = 'none';

    public static function save($label = null): static
    {
        return static::primary($label ?? Yii::t('base', 'Save'));
    }

    /**
     * @deprecated since 1.18 use [[\humhub\widgets\bootstrap\Link::to()]] instead
     */
    public static function asLink(string $label = null, $href = '#'): static
    {
        $button = self::instance($label)
            ->loader(false)
            ->link($href);
        Html::removeCssClass($button->options, ['class' => 'btn']);
        Html::addCssClass($button->options, ['class' => 'link']);
        return $button;
    }

    public static function none(string $label = null): static
    {
        $button = self::instance($label)
            ->loader(false);
        Html::removeCssClass($button->options, ['class' => 'btn']);
        return $button;
    }

    /**
     * @since 1.18
     */
    public static function asBadge(string $label = null, ?string $color = null): static
    {
        return self::none($label)
            ->cssClass(['badge', 'text-bg-' . $color]);
    }

    public static function back($url, $label = null): static
    {
        return self::light($label ?? Yii::t('base', 'Back'))
            ->link($url)
            ->icon('back')
            ->right()
            ->sm();
    }

    public static function userPickerSelfSelect($selector, $label = null): static
    {
        return self::asLink($label ?? Yii::t('base', 'Select Me'))
            ->action('selectSelf', null, $selector)
            ->icon('fa-check-circle-o')
            ->right()
            ->cssClass('input-field-addon');
    }

    public function loader(bool|string $loader = true): static
    {
        $this->loader = $loader;
        return $this;
    }

    public function link($url = null, $pjax = true): static
    {
        $this->options['href'] = Url::to($url);
        $this->pjax($pjax);
        $this->asLink = true;

        return $this;
    }

    public function getHref(): ?string
    {
        return $this->options['href'] ?? null;
    }

    /**
     * If set to false the [data-pjax-prevent] flag is attached to the link.
     */
    public function pjax(bool $pjax = true): static
    {
        if (!$pjax) {
            Html::addPjaxPrevention($this->options);
        }

        return $this;
    }

    public function isPjaxEnabled(): bool
    {
        return Html::isPjaxEnabled($this->options);
    }

    public function submit(): static
    {
        $this->options['type'] = 'submit';
        return $this;
    }

    /**
     * Adds a data-action-click handler to the button.
     */
    public function action(string $handler, $url = null, ?string $target = null): static
    {
        return $this->onAction('click', $handler, $url, $target);
    }

    /**
     * Adds a data-action-* handler to the button.
     */
    public function onAction(string $event, string $handler, $url = null, ?string $target = null): static
    {
        $this->options['data-action-' . $event] = $handler;

        if ($url) {
            $this->options['data-action-' . $event . '-url'] = Url::to($url);
        }

        if ($target) {
            $this->options['data-action-' . $event . '-target'] = $target;
        }

        return $this;
    }

    /**
     * Adds a confirmation behaviour to the button.
     */
    public function confirm(
        ?string $title = null,
        ?string $body = null,
        ?string $confirmButtonText = null,
        ?string $cancelButtonText = null,
    ): static {
        if ($title) {
            $this->options['data-action-confirm-header'] = $title;
        }

        if ($body) {
            $this->options['data-action-confirm'] = $body;
        } else {
            $this->options['data-action-confirm'] = '';
        }

        if ($confirmButtonText) {
            $this->options['data-action-confirm-text'] = $confirmButtonText;
        }

        if ($cancelButtonText) {
            $this->options['data-action-cancel-text'] = $cancelButtonText;
        }

        return $this;
    }

    /**
     * Sets the button as disabled
     */
    public function disabled(bool $disabled = true): static
    {
        if ($disabled) {
            if ($this->asLink) {
                Html::addCssClass($this->options, 'disabled');
                $this->options['aria-disabled'] = 'true';
            } else {
                $this->options['disabled'] = true;
            }
        } else {
            if ($this->asLink) {
                Html::removeCssClass($this->options, 'disabled');
                unset($this->options['aria-disabled']);
            } else {
                unset($this->options['disabled']);
            }
        }

        return $this;
    }

    /**
     * @inerhitdoc
     */
    public function run(): string
    {
        $this->options = array_merge(
            $this->options,
            $this->htmlOptions,
        ); // For compatibility with old bootstrap buttons

        if ($this->loader) {
            $this->options['data-ui-loader'] = $this->loader;
        }

        // Workaround since data-method handler prevents confirm or other action handlers from being executed.
        if (isset($this->options['data-action-confirm'], $this->options['data-method'])) {
            $method = $this->options['data-method'];
            $this->options['data-method'] = null;
            $this->options['data-action-method'] = $method;
        }

        if ($this->label === null && $this->icon !== null) {
            $this->cssClass('btn-icon-only');
        }

        $text = $this->icon . ($this->encodeLabel ? Html::encode($this->label) : $this->label);

        if ($this->size) {
            Html::addCssClass($this->options, ['class' => 'btn-' . $this->size]);
        }

        return $this->asLink
            ? Html::a($text, $this->getHref(), $this->options)
            : Html::button($text, $this->options);
    }

    public static function instance(?string $text = null, ?string $color = null): static
    {
        return new static([
            'label' => $text,
            'options' => $color ? ['class' => ['btn-' . $color]] : [],
        ]);
    }

    /**
     * @since 1.18
     */
    public function outline(): static
    {
        // btn-primary → btn-outline-primary
        // btn-danger → btn-outline-danger
        // And so on for all Bootstrap 5 colors
        $pattern = '/\bbtn-(primary|secondary|success|danger|warning|info|accent|light|dark)\b/';
        $replacement = 'btn-outline-$1';

        if ($this->options['class'] !== null) {
            $this->options['class'] = preg_replace($pattern, $replacement, $this->options['class']);
        }

        return $this;
    }

    /**
     * @deprecated since 1.18
     */
    public function setType($type): static
    {
        if ($type !== static::TYPE_NONE) {
            $this->options['class'] = ['btn-' . $type];
        }
        return $this;
    }

    /**
     * @deprecated since 1.18
     */
    public function setText($text): static
    {
        $this->label = $text;
        return $this;
    }
}
