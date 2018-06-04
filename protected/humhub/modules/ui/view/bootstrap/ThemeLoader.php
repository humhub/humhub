<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\bootstrap;

use humhub\modules\ui\view\helpers\ThemeHelper;
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
        $themePath = $app->settings->get('theme');
        if (empty($themePath) || !is_dir($themePath)) {
            return;
        }

        $theme = ThemeHelper::getThemeByPath($themePath);

        if ($theme !== null) {
            $app->view->theme = $theme;
            $app->mailer->view->theme = $theme;
        }
    }


}
