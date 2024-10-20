<?php

namespace humhub\modules\user\jobs;

use humhub\modules\queue\ActiveJob;
use humhub\modules\user\models\Invite;
use humhub\modules\user\Module;
use Yii;
use yii\db\Expression;

class CleanupInvites extends ActiveJob
{
    public function run()
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('user');

        Invite::deleteAll([
            '<', 'created_at',
            new Expression("NOW() - INTERVAL :days DAY", [':days' => $module->invitesTimeToLiveInDays]),
        ]);
    }
}
