<?php

use humhub\components\InstallationState;
use humhub\components\Theme;
use humhub\models\Setting;
use humhub\services\ActiveThemeService;
use yii\db\Migration;

/**
 * Replaces the theme settings cache introduced in 1.3:
 *
 * - `theme` held an absolute base path and now holds the theme name
 * - `themeParents` held absolute paths and is gone
 * - `theme.var.<Theme>.<key>` held one row per SCSS variable, for every theme ever
 *   activated, and is gone
 *
 * The `theme.state` row replacing all of them is built by the first request.
 */
class m260918_101500_1_20_theme_settings_to_state extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if (!Yii::$app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            // Nothing to convert on a fresh installation
            return;
        }

        $settingsManager = Yii::$app->settings;

        $storedTheme = (string)$settingsManager->getUncached('theme');

        // Only a stored path needs converting. An absent setting means no preference,
        // which ActiveThemeService resolves to the core theme on its own - writing a name
        // here would turn that into a stored choice.
        if (str_contains($storedTheme, '/') || str_contains($storedTheme, '\\')) {
            $name = basename(rtrim(str_replace('\\', '/', $storedTheme), '/'));

            // An unresolvable name is handled by ActiveThemeService, which falls back to
            // the core theme without discarding the stored choice
            $settingsManager->set('theme', $name === '' ? Theme::CORE_THEME_NAME : $name);
        }

        $settingsManager->delete('themeParents');

        // Directly, because BaseSettingsManager::deleteAll() deletes row by row
        $this->delete(Setting::tableName(), [
            'and',
            ['module_id' => 'base'],
            ['like', 'name', 'theme.var.%', false],
        ]);

        $settingsManager->reload();

        // The web updater continues this very request with CacheHelper::flushCache().
        // ThemeLoader resolved the theme from the pre-migration path, failed, and fell back
        // to the core theme - so re-point the request at the theme just migrated.
        ActiveThemeService::flush();
        $theme = ActiveThemeService::getTheme();
        if ($theme !== null) {
            Yii::$app->view->theme = $theme;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260918_101500_1_20_theme_settings_to_state cannot be reverted.\n";

        return false;
    }
}
