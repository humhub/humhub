<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\modules\notification\components\BaseNotification;
use Yii;

/**
 * HumHubUpdateNotification
 *
 * Notifies about new HumHub Version
 *
 * @package humhub.modules_core.admin.notifications
 * @since 0.11
 */
class NewVersionAvailable extends BaseNotification
{

    /**
     * @inheritdoc
     */
    public $moduleId = 'admin';

    /**
     * @inheritdoc
     */
    public $viewName = 'newVersionAvailable';

    public function renderText()
    {
        return Yii::t('AdminModule.views_notifications_newUpdate', "There is a new HumHub Version (%version%) available.", ['%version%' => $notification->getLatestHumHubVersion()]);
    }

    public function getUrl()
    {
        return \yii\helpers\Url::to(['/admin/about']);
    }

    public function getLatestHumHubVersion()
    {
        return \humhub\modules\admin\libs\HumHubAPI::getLatestHumHubVersion();
    }

}

?>
