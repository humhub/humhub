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

    public bool $link = false;

    public bool $loader = true;
    /**
     * @inerhitdoc
     */
    public $encodeLabel = false;

    public static function save($label = null): static
    {
        return static::primary($label ?? Yii::t('base', 'Save'));
    }

    public static function asLink(string $label = null, string $href = '#'): static
    {
        return self::instance($label, 'link')
            ->loader(false)
            ->link($href);
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
        return self::secondary($label ?? Yii::t('base', 'Back'))
            ->link($url)
            ->icon('back')
            ->right()
            ->loader(true)
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

    public function loader($active = true): static
    {
        $this->loader = $active;
        return $this;
    }

    public function link($url = null, $pjax = true): static
    {
        $this->link = true;
        $this->options['href'] = Url::to($url);
        $this->pjax($pjax);

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
    public function confirm(?string $title = null, ?string $body = null, ?string $confirmButtonText = null, ?string $cancelButtonText = null): static
    {
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
     * @inerhitdoc
     */
    public function run(): string
    {
        $this->options = array_merge($this->options, $this->htmlOptions); // For compatibility with old bootstrap buttons

        if ($this->loader) {
            $this->options['data-ui-loader'] = '';
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

        $text =
            ($this->icon ? $this->icon . ' ' : '') .
            ($this->encodeLabel ? Html::encode($this->label) : $this->label);

        if ($this->link) {
            $href = $this->options['href'] ?? null;
            return Html::a($text, $href, $this->options);
        }

        return Html::button($text, $this->options);
    }

    public static function instance(?string $text = null, ?string $color = null): static
    {
        return new static([
            'label' => $text,
            'options' => $color ? ['class' => ['btn-' . $color]] : [],
        ]);
    }
}
