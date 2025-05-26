<?php

use humhub\helpers\ThemeHelper;
use yii\db\Migration;

class m250405_072758_1_18_switch_to_humhub_theme_and_disable_themes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Copy Primary and Secondary colors to the Settings manager
        $settingsManager = Yii::$app->settings;
        $themeVariables = Yii::$app->view->theme->variables;
        $settingsManager->set('themePrimaryColor', $themeVariables->get('primary'));
        $settingsManager->set('themeSuccessColor', $themeVariables->get('success'));
        $settingsManager->set('themeDangerColor', $themeVariables->get('danger'));
        $settingsManager->set('themeWarningColor', $themeVariables->get('warning'));
        $settingsManager->set('themeInfoColor', $themeVariables->get('info'));
        $settingsManager->set('themeLightColor', $themeVariables->get('default')); // Info becomes Light

        // Switch to HumHub theme
        $hhTheme = ThemeHelper::getThemeByName('HumHub');
        $hhTheme->activate();

        // Uninstall the Theme Builder module
        $moduleManager = Yii::$app->moduleManager;
        $themeBuilderModuleId = 'theme-builder';
        if ($moduleManager->getModule($themeBuilderModuleId, false)) {
            $moduleManager->removeModule($themeBuilderModuleId);
        }

        // Loop over all /themes/* folder and rename all old theme folders to e.g. /themes/Example.bs3.old
        $themesPath = Yii::getAlias('@themes');
        foreach (ThemeHelper::getThemesByPath($themesPath) as $theme) {
            if ($theme->name === 'HumHub') {
                continue;
            }
            // Rename theme by adding .old
            $oldThemePath = $themesPath . DIRECTORY_SEPARATOR . $theme->name . '.bs3.old';
            if (!file_exists($oldThemePath)) {
                rename($themesPath . DIRECTORY_SEPARATOR . $theme->name, $oldThemePath);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250405_072758_1_18_switch_to_humhub_theme_and_disable_themes cannot be reverted.\n";

        return false;
    }
}
