<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use Yii;
use yii\helpers\Url;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\admin\libs\HumHubAPI;

/**
 * HumHubUpdateNotification
 *
 * Notifies about new HumHub Version
 *
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
    public function getUrl()
    {
        return Url::to(['/admin/information/about']);
    }

    /**
     * @inheritdoc
     */
    public function category()
    {
        return new AdminNotificationCategory;
    }

    /**
     * @inheritdoc
     */
    public function getLatestHumHubVersion()
    {
        return HumHubAPI::getLatestHumHubVersion();
    }

    /**
     * @inheritdoc
     */
    public function html()
    {
        return Yii::t('AdminModule.notification', "There is a new HumHub Version ({version}) available.", ['version' => Html::tag('strong', $this->getLatestHumHubVersion())]);
    }

}

?>
