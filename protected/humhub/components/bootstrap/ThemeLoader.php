<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\bootstrap;

use humhub\components\console\Application as ConsoleApplication;
use humhub\components\InstallationState;
use humhub\components\Theme;
use humhub\helpers\ThemeHelper;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Theme as BaseTheme;
use yii\helpers\ArrayHelper;

/**
 * ThemeLoader is used during the application bootstrap process
 * to load the actual theme specified in the SettingsManager.
 *
 * @since 1.3
 */
class ThemeLoader implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // Skip dynamic theme loading during the installation
        if (Yii::getAlias('@web', false) === false) {
            return;
        }

        if ($app->installationState->hasState(InstallationState::STATE_DATABASE_CREATED)) {
            $themePath = $app->settings->get('theme');
            if (!empty($themePath) && is_dir($themePath)) {
                $theme = ThemeHelper::getThemeByPath($themePath);

                if ($theme !== null) {
                    self::mergeConfiguredPathMap($app->view->theme, $theme);
                    $app->view->theme = $theme;
                    $app->mailer->view->theme = $theme;
                }
            }
        }

        if (
            $app->view->theme instanceof Theme
            && !Yii::$app->request->isConsoleRequest
            && !(Yii::$app instanceof ConsoleApplication)
        ) {
            // Register the theme (e.g. add core js/css header)
            $app->view->theme->register();
        }
    }

    /**
     * Carries the `pathMap` from the statically configured theme (e.g.
     * `components.view.theme.pathMap` in `common.php`) over to the dynamically
     * loaded active theme, so explicit view overrides survive the runtime
     * theme switch.
     *
     * @since 1.19
     */
    private static function mergeConfiguredPathMap(?BaseTheme $configured, BaseTheme $active): void
    {
        if ($configured === null || empty($configured->pathMap)) {
            return;
        }

        $active->pathMap = ArrayHelper::merge(
            $active->pathMap ?? [],
            $configured->pathMap,
        );
    }
}
