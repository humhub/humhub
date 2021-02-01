<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\widgets;

use humhub\components\Widget;
use Yii;

/**
 * MaintenanceModeWarning shows a snippet in the dashboard
 * when maintenance mode is active.
 *
 * @package humhub\modules\admin\widgets
 */
class MaintenanceModeWarning extends Widget
{

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!Yii::$app->settings->get('maintenanceMode')) {
            return;
        }

        return $this->render('maintenanceModeWarning');
    }

}
