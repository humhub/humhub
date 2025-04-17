<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\widgets\bootstrap;

/**
 * Provides an extension of the yii\bootstrap5\Alert class with additional features.
 *
 * Usage examples:
 *
 * ```
 * <?= Alert::danger('Say hello...')->icon('user')->closeButton(false) ?>
 * ```
 *
 * ```
 * <?php Alert::beginPrimary()->closeButton(false) ?>
 * Say hello...
 * <?php Alert::end() ?>
 * ```
 *
 * @since 1.18
 * @see https://getbootstrap.com/docs/5.3/components/alerts/
 */
class Alert extends \yii\bootstrap5\Alert
{
    use BootstrapVariationsTrait {
        // Disabled methods:
        sm as private;
        lg as private;
        left as private;
        right as private;
        tooltip as private;
        setLabel as private;
    }

    public static function instance(?string $text = null, ?string $color = null): static
    {
        if ($text === null) {
            throw new \InvalidArgumentException('Text must be provided');
        }

        return new static([
            'options' => [
                'class' => 'alert-' . $color,
            ],
            'body' => $text,
        ]);
    }

    public function __toString(): string
    {
        $this->run();
        return '';
    }

    /**
     * @inerhitdoc
     */
    public function run(): void
    {
        if ($this->icon) {
            $this->body = $this->icon . ' ' . $this->body;
        }

        parent::run();
    }

    /**
     * @param array|false $closeButton the options for rendering the close button tag.
     *
     * The close button is displayed in the header of the modal window. Clicking
     * on the button will hide the modal window. If this is false, no close button will be rendered.
     *
     * The following special options are supported:
     *
     * - tag: string, the tag name of the button. Defaults to 'button'.
     *
     * The rest of the options will be rendered as the HTML attributes of the button tag.
     * Please refer to the [Alert documentation](https://getbootstrap.com/docs/5.3/components/alerts/)
     * for the supported HTML attributes.
     */
    public function closeButton(array|false $closeButton = []): static
    {
        $this->closeButton = $closeButton;
        return $this;
    }

    public static function beginInstance(string $color): static
    {
        return static::begin([
            'options' => [
                'class' => 'alert-' . $color,
            ],
        ]);
    }

    public static function beginPrimary(): static
    {
        return static::beginInstance('primary');
    }

    public static function beginSecondary(): static
    {
        return static::beginInstance('secondary');
    }

    public static function beginInfo(): static
    {
        return static::beginInstance('info');
    }

    public static function beginSuccess(): static
    {
        return static::beginInstance('success');
    }

    public static function beginWarning(): static
    {
        return static::beginInstance('warning');
    }

    public static function beginDanger(): static
    {
        return static::beginInstance('danger');
    }

    public static function beginLight(): static
    {
        return static::beginInstance('light');
    }

    public static function beginDark(): static
    {
        return static::beginInstance('dark');
    }
}
