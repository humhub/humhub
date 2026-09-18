<?php

use humhub\components\InstallationState;
use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use yii\db\Migration;

/**
 * The 1.19 move of the core theme into `protected/humhub/themes` (#8102) left the webroot
 * copy behind: an update only adds and overwrites files, so `themes/HumHub` survives - often
 * only partially, which broke the SCSS build and, before 1.19.0-beta.2, shadowed the real
 * core theme by name and looped the CSS fallback forever.
 *
 * m250405_072758 already retires every other pre-1.19 webroot theme, but skips `HumHub`, and
 * it has run on the beta installations affected by this - hence a migration of its own.
 */
class m260918_112000_disable_stale_webroot_core_theme extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            // A new installation never had a webroot core theme
            return;
        }

        $stalePath = Yii::getAlias('@themes') . DIRECTORY_SEPARATOR . Theme::CORE_THEME_NAME;
        if (!is_dir($stalePath)) {
            return;
        }

        // Has to be resolved before the rename, the stored path is the one of the theme source directory
        $storedPath = (string)Yii::$app->settings->get('theme');
        $isActive = $storedPath !== '' && realpath($storedPath) === realpath($stalePath);

        // Keep the directory around, like m250405_072758 does for the other pre-1.19 themes
        $retiredPath = $stalePath . '.bs3.old';
        if (file_exists($retiredPath) || !rename($stalePath, $retiredPath)) {
            echo "    > could not move $stalePath to $retiredPath, please remove it manually\n";
        }

        if ($isActive) {
            // Installations updated to 1.19.0-beta.1 stored the stale directory as the active
            // theme, since the core theme was shadowed by it when m250405_072758 activated it
            ThemeHelper::getThemeByName(Theme::CORE_THEME_NAME)?->activate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260918_112000_disable_stale_webroot_core_theme cannot be reverted.\n";

        return false;
    }
}
