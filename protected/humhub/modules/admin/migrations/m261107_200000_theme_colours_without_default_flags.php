<?php

use humhub\services\ActiveThemeService;
use yii\db\Migration;

/**
 * The theme colours of Administration → Appearance → Theme are stored only where they differ from the theme's own:
 * a colour kept at its "Default" is dropped, and the `useDefaultTheme{Color}Color` flags go. The `light` and `dark`
 * colours are no settings any more: `$light` follows the surface colour, `$dark` is the theme's.
 */
class m261107_200000_theme_colours_without_default_flags extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $settings = Yii::$app->settings;
        foreach (['Primary', 'Accent', 'Secondary', 'Success', 'Danger', 'Warning', 'Info', 'Light', 'Dark', 'BodyBg', 'BodyBgDark'] as $color) {
            if ((bool)$settings->get('useDefaultTheme' . $color . 'Color', true)) {
                $settings->delete('theme' . $color . 'Color');
            }
            $settings->delete('useDefaultTheme' . $color . 'Color');
        }
        $settings->delete('themeLightColor');
        $settings->delete('themeDarkColor');

        ActiveThemeService::flush();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m261107_200000_theme_colours_without_default_flags cannot be reverted.\n";
        return false;
    }
}
