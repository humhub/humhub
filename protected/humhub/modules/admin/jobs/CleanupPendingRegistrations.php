<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\jobs;

use humhub\modules\admin\Module;
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
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin');

        Invite::deleteAll(['<', 'created_at', Yii::$app->formatter->asDatetime(time() - $module->cleanupPendingRegistrationInterval, 'php:Y-m-d H:i:s')]);
    }


}
