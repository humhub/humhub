<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use humhub\assets\EmojiFontAsset;
use humhub\assets\ReadableFontAsset;
use humhub\helpers\EmojiHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Colour mode and accessibility options of the current user
 *
 * Each option is a setting of the `user` module: the user's own value, else the instance default (the global value
 * of the same setting). The effective values are written to the `<html>` element by an inline script in the head,
 * see [[registerHead()]].
 *
 * @see docs/develop/concept-appearance.md
 * @since 1.20
 */
class Appearance extends Component
{
    public const COLOR_MODE = 'colorMode';
    public const CONTRAST = 'a11y.contrast';
    public const MOTION = 'a11y.motion';
    public const FONT = 'a11y.font';

    public const SYSTEM = 'system';
    public const LIGHT = 'light';
    public const DARK = 'dark';
    public const OFF = 'off';
    public const ON = 'on';

    /**
     * Setting of the `admin` module that allows the dark mode
     */
    public const SETTING_ALLOW_DARK = 'colorMode.allowDark';

    /**
     * @return array<string, string[]> the allowed values per option
     */
    public function getOptions(): array
    {
        return [
            self::COLOR_MODE => [self::SYSTEM, self::LIGHT, self::DARK],
            self::CONTRAST => [self::SYSTEM, self::OFF, self::ON],
            self::MOTION => [self::SYSTEM, self::OFF, self::ON],
            self::FONT => [self::OFF, self::ON],
        ];
    }

    /**
     * @return string the value used when neither the user nor the admin chose one
     */
    public function getBuiltInDefault(string $option): string
    {
        return match ($option) {
            self::COLOR_MODE => self::LIGHT,
            self::FONT => self::OFF,
            default => self::SYSTEM,
        };
    }

    public function isDarkModeAllowed(): bool
    {
        return (bool)Yii::$app->getModule('admin')->settings->get(self::SETTING_ALLOW_DARK, false);
    }

    /**
     * @return string the instance default of the option
     */
    public function getDefault(string $option): string
    {
        return $this->normalize($option, Yii::$app->getModule('user')->settings->get($option));
    }

    /**
     * Sets the instance default of the option
     */
    public function setDefault(string $option, ?string $value): void
    {
        $value = $this->normalize($option, $value);
        Yii::$app->getModule('user')->settings->set(
            $option,
            $value === $this->getBuiltInDefault($option) ? null : $value,
        );
    }

    /**
     * @return string the value the user chose, else the instance default
     */
    public function get(string $option, ?User $user = null): string
    {
        $user ??= $this->getCurrentUser();
        if ($user === null) {
            return $this->getDefault($option);
        }

        return $this->normalize(
            $option,
            Yii::$app->getModule('user')->settings->contentContainer($user)->getInherit($option),
        );
    }

    /**
     * Saves the user's value. A value equal to the instance default is deleted, so a later change of the default
     * reaches the user.
     */
    public function set(string $option, ?string $value, User $user): void
    {
        $value = $this->normalize($option, $value);
        Yii::$app->getModule('user')->settings->contentContainer($user)->set(
            $option,
            $value === $this->getDefault($option) ? null : $value,
        );
    }

    /**
     * @return string the colour mode in effect: `light`, `dark` or `system`
     */
    public function getColorMode(?User $user = null): string
    {
        return $this->isDarkModeAllowed() ? $this->get(self::COLOR_MODE, $user) : self::LIGHT;
    }

    /**
     * Registers the inline script that sets the attributes of the `<html>` element, the readable font and the emoji
     * font
     */
    public function registerHead(View $view): void
    {
        if (EmojiHelper::isFontEnabled()) {
            EmojiFontAsset::register($view);
        }

        $font = $this->get(self::FONT) === self::ON;
        if ($font) {
            ReadableFontAsset::register($view);
        }

        $config = Json::htmlEncode([
            'colorMode' => $this->getColorMode(),
            'contrast' => $this->get(self::CONTRAST),
            'motion' => $this->get(self::MOTION),
            'font' => $font,
        ]);

        // Runs before the first paint. "system" follows the media query: the colour mode needs the attribute
        // (Bootstrap), contrast and motion leave it out and their CSS reads the media query itself.
        $view->registerJs(<<<JS
            (function (c) {
                var root = document.documentElement;
                var dark = window.matchMedia('(prefers-color-scheme: dark)');
                var setColorMode = function () {
                    root.setAttribute('data-bs-theme', c.colorMode === 'system' ? (dark.matches ? 'dark' : 'light') : c.colorMode);
                };
                setColorMode();
                if (c.colorMode === 'system') {
                    dark.addEventListener('change', setColorMode);
                }
                if (c.contrast !== 'system') {
                    root.setAttribute('data-hh-contrast', c.contrast === 'on' ? 'more' : 'off');
                }
                if (c.motion !== 'system') {
                    root.setAttribute('data-hh-motion', c.motion === 'on' ? 'reduce' : 'off');
                }
                if (c.font) {
                    root.setAttribute('data-hh-font', 'readable');
                }
            })($config);
            JS, View::POS_HEAD, 'hh-appearance');
    }

    private function normalize(string $option, mixed $value): string
    {
        return in_array($value, $this->getOptions()[$option] ?? [], true)
            ? $value
            : $this->getBuiltInDefault($option);
    }

    private function getCurrentUser(): ?User
    {
        if (Yii::$app->user->isGuest) {
            return null;
        }

        $user = Yii::$app->user->getIdentity();
        return $user instanceof User ? $user : null;
    }
}
