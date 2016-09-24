<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\notifications;

use Yii;
use humhub\modules\notification\components\BaseNotification;
use yii\bootstrap\Html;

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
        return \yii\helpers\Url::to(['/admin/information/about']);
    }

    /**
     * @inheritdoc
     */
    public function getLatestHumHubVersion()
    {
        return \humhub\modules\admin\libs\HumHubAPI::getLatestHumHubVersion();
    }

    /**
     * @inheritdoc
     */
    public function getAsHtml()
    {
        return Yii::t('AdminModule.notification', "There is a new HumHub Version ({version}) available.", ['version' => Html::tag('strong', $this->getLatestHumHubVersion())]);
    }

}

?>
