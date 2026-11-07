<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\helpers;

use Yii;

/**
 * Prepare Sass variable values for email templates with inline usage, inside quotation marks
 * Usage: `style="<?= MailStyleHelper::getFontFamily() ?>"`
 *
 * @since 1.18
 */
class MailStyleHelper
{
    public const DEFAULT_FONT_FAMILY = "-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif";
    public const DEFAULT_COLOR_PRIMARY = '#435f6f';
    public const DEFAULT_LINK_COLOR = '#435f6f';
    public const DEFAULT_COLOR_INFO = '#0582FF';
    public const DEFAULT_TEXT_COLOR_MAIN = '#555555';
    public const DEFAULT_TEXT_COLOR_SOFT = '#555555';
    public const DEFAULT_TEXT_COLOR_SOFT2 = '#aeaeae';
    public const DEFAULT_TEXT_COLOR_HIGHLIGHT = '#000000';
    public const DEFAULT_TEXT_COLOR_CONTRAST = '#ffffff';
    public const DEFAULT_BACKGROUND_COLOR_MAIN = '#ffffff';
    public const DEFAULT_BACKGROUND_COLOR_PAGE = '#ededed';
    public const DEFAULT_BACKGROUND_COLOR_SECONDARY = '#f9f9f9';

    public static function getFontFamily(): string
    {
        return static::getVariable('mail-font-family', self::DEFAULT_FONT_FAMILY);
    }

    public static function getColorPrimary(): string
    {
        return static::getVariable('primary', self::DEFAULT_COLOR_PRIMARY);
    }

    /**
     * A link: the primary colour as text, in the shade that reads on the surfaces (`$hh-primary-fg`)
     *
     * @since 1.20
     */
    public static function getLinkColor(): string
    {
        return static::getVariable('hh-primary-fg', self::DEFAULT_LINK_COLOR);
    }

    public static function getColorInfo(): string
    {
        return static::getVariable('info', self::DEFAULT_COLOR_INFO);
    }

    public static function getTextColorSoft(): string
    {
        return static::getVariable('body-color', self::DEFAULT_TEXT_COLOR_SOFT);
    }

    public static function getTextColorSoft2(): string
    {
        return static::getVariable('body-tertiary-color', self::DEFAULT_TEXT_COLOR_SOFT2);
    }

    public static function getTextColorMain(): string
    {
        return static::getVariable('body-color', self::DEFAULT_TEXT_COLOR_MAIN);
    }

    public static function getTextColorHighlight(): string
    {
        return static::getVariable('body-emphasis-color', self::DEFAULT_TEXT_COLOR_HIGHLIGHT);
    }

    public static function getTextColorContrast(): string
    {
        return ScssHelper::getColorContrast('primary') ?? self::DEFAULT_TEXT_COLOR_CONTRAST;
    }

    public static function getBackgroundColorMain(): string
    {
        return static::getVariable('body-bg', self::DEFAULT_BACKGROUND_COLOR_MAIN);
    }

    public static function getBackgroundColorPage(): string
    {
        return static::getVariable('hh-page-bg', self::DEFAULT_BACKGROUND_COLOR_PAGE);
    }

    public static function getBackgroundColorSecondary(): string
    {
        return static::getVariable('body-tertiary-bg', self::DEFAULT_BACKGROUND_COLOR_SECONDARY);
    }

    public static function getVariable(string $key, ?string $default = null): string
    {
        return static::sanitizeForInlineUsage(
            (string)Yii::$app->view->theme->variable($key, $default),
        );
    }

    /**
     * Insure the code can be inserted inline, e.g., `style="<?= MailStyleHelper::getFontFamily() ?>"`
     */
    protected static function sanitizeForInlineUsage(string $style): string
    {
        return str_replace('"', "'", trim($style));
    }
}
