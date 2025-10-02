<?php

use humhub\helpers\ThemeHelper;
use humhub\modules\user\helpers\LoginBackgroundImageHelper;
use yii\db\Migration;

class m250405_072758_1_18_switch_to_humhub_theme_and_disable_themes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $settingsManager = Yii::$app->settings;

        // Copy Login Background image
        $oldThemeBasePath = $settingsManager->get('theme');
        foreach (['login-bg.jpg', 'login-bg.png'] as $loginBgFile) {
            $oldFile = $oldThemeBasePath . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $loginBgFile;
            if (file_exists($oldFile)) {
                LoginBackgroundImageHelper::set($oldFile);
                break;
            }
        }

        // Switch to HumHub theme
        $themeAfterMigration = 'HumHub';
        if ($this->currentThemeIsVariantOf('enterprise-white')) {
            $themeAfterMigration = 'enterprise-white';
            $primaryDefault = '#12a1b3';
            $accentDefault = '#21A1B3';
        } elseif ($this->currentThemeIsVariantOf('enterprise')) {
            $themeAfterMigration = 'enterprise';
            $primaryDefault = '#2d3340';
            $accentDefault = '#21A1B3';
        } else {
            $primaryDefault = '#435f6f';
            $accentDefault = '#21A1B3';
        }

        // Copy Theme colors vars to the Settings manager
        $themeVariables = Yii::$app->view->theme->variables;

        $currentPrimary = $themeVariables->get('primary');
        $settingsManager->set('themePrimaryColor', $currentPrimary);
        $settingsManager->set(
            'useDefaultThemePrimaryColor',
            (strcasecmp($currentPrimary, $primaryDefault) == 0) ? 1 : 0,
        );

        $currentInfo = $themeVariables->get('info');
        $settingsManager->set('themeAccentColor', $currentInfo);
        $settingsManager->set(
            'useDefaultThemeAccentColor',
            (strcasecmp($currentInfo, $accentDefault) == 0) ? 1 : 0,
        );

        $currentSuccess = $themeVariables->get('success');
        $settingsManager->set('themeSuccessColor', $currentSuccess);
        $settingsManager->set(
            'useDefaultThemeSuccessColor',
            (strcasecmp($currentSuccess, '#97d271') == 0) ? 1 : 0,
        );

        $currentDanger = $themeVariables->get('danger');
        $settingsManager->set('themeDangerColor', $currentDanger);
        $settingsManager->set(
            'useDefaultThemeDangerColor',
            (strcasecmp($currentDanger, '#FC4A64') == 0) ? 1 : 0,
        );

        $currentWarning = $themeVariables->get('warning');
        $settingsManager->set('themeWarningColor', $currentWarning);
        $settingsManager->set(
            'useDefaultThemeWarningColor',
            (strcasecmp($currentWarning, '#FFC107') == 0) ? 1 : 0,
        );

        $currentLight = $themeVariables->get('default');
        $settingsManager->set('themeLightColor', $currentLight);
        $settingsManager->set(
            'useDefaultThemeLightColor',
            (strcasecmp($currentLight, '#e7e7e7') == 0) ? 1 : 0,
        );

        $hhTheme = ThemeHelper::getThemeByName($themeAfterMigration);
        if ($hhTheme === null) {
            // Fallback to Humhub theme
            $hhTheme = ThemeHelper::getThemeByName("HumHub");
        }
        $hhTheme->activate();

        // Uninstall the Theme Builder module1
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

    private function currentThemeIsVariantOf(string $theme): bool
    {
        $currentThemePath = Yii::$app->settings->get('theme');
        if ($currentThemePath && str_ends_with($currentThemePath, $theme)) {
            return true;
        }

        $parentPaths = Yii::$app->settings->getSerialized('themeParents');
        if (!is_array($parentPaths)) {
            return false;
        }

        foreach ($parentPaths as $parentPath) {
            if (str_ends_with($parentPath, $theme)) {
                return true;
            }
        }

        return false;
    }
}
