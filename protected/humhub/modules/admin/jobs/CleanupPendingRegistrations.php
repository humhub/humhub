<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\Invite;
use Yii;

/**
 * CleanupLog deletes older log records from log table
 *
 * @since 1.8
 */

class CleanupPendingRegistrations extends ActiveJob
{
    /**
     * @var int seconds before delete old pending registrations messages
     */
    public $cleanupInterval = 60 * 60 * 24 * 90;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        Invite::deleteAll(['<', 'created_at', Yii::$app->formatter->asDatetime(time() - $this->cleanupInterval, 'php:Y-m-d H:i:s')]);
    }


}
