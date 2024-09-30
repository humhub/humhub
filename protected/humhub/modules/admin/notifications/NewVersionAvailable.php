<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use humhub\helpers\Html;
use humhub\modules\admin\libs\HumHubAPI;
use humhub\modules\notification\components\BaseNotification;
use Yii;
use yii\helpers\Url;

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
    public $requireOriginator = false;

    /**
     * @inheritdoc
     */
    public $requireSource = false;

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
        return new AdminNotificationCategory();
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
