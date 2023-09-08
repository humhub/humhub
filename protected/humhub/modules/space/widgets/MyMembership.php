<?php

namespace humhub\modules\space\widgets;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\widgets\TimeAgo;
use Yii;
use yii\base\Widget;

/**
 * Sidebar snippet which displays information about the current user's Space membership.
 *
 * @author Faeze
 * @since 1.7
 */
class MyMembership extends Widget
{
    /** @var Space */
    public $space;

    /**
     * @inheritDoc
     */
    public function run()
    {
        $membership = Membership::findInstance([$this->space->id, Yii::$app->user->id], ['status' => Membership::STATUS_MEMBER]);

        return $this->render('myMembership', [
            'role' => $this->space->getUserGroup(),
            'memberSince' => $membership === null || empty($membership->created_at) ? null : TimeAgo::widget(['timestamp' => $membership->created_at])
        ]);
    }
}
