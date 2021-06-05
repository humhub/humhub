<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory\commands;

use humhub\components\SettingsManager;
use humhub\modules\directory\Module;
use Yii;

/**
 * Management of activity of the deprecated module "Directory"
 *
 * @since 1.9
 */
class DirectoryController extends \yii\console\Controller
{

    /**
     * Activate the deprecated module "Directory"
     */
    public function actionActivate()
    {
        $this->getModuleSettings()->set('isActive', true);
        $this->stdout('Module "Directory" is activated.' . "\n\n");
    }

    /**
     * Deactivate the deprecated module "Directory"
     */
    public function actionDeactivate()
    {
        $this->getModuleSettings()->delete('isActive');
        $this->stdout('Module "Directory" is deactivated.' . "\n\n");
    }

    protected function getModuleSettings(): SettingsManager
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('directory');
        return $module->settings;
    }
}
