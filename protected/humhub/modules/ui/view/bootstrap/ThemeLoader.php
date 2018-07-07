<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\bootstrap;

use humhub\libs\BaseSettingsManager;
use humhub\modules\ui\view\components\Theme;
use humhub\modules\ui\view\helpers\ThemeHelper;
use Yii;
use yii\base\BootstrapInterface;

/**
 * ThemeLoader is used during the application bootstrap process
 * to load the actual theme specifed in the SettingsManager.
 *
 * @since 1.3
 * @package humhub\modules\ui\view\bootstrap
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

        if (BaseSettingsManager::isDatabaseInstalled()) {
            $themePath = $app->settings->get('theme');
            if (!empty($themePath) && is_dir($themePath)) {
                $theme = ThemeHelper::getThemeByPath($themePath);

                if ($theme !== null) {
                    $app->view->theme = $theme;
                    $app->mailer->view->theme = $theme;
                }
            }
        }
        
        if (!Yii::$app->request->isConsoleRequest && $app->view->theme instanceof Theme) {
            $app->view->theme->register();
        }

    }

}
