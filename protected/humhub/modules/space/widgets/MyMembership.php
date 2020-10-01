<?php


namespace humhub\modules\space\widgets;


use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\widgets\TimeAgo;
use Yii;
use yii\base\Widget;

class MyMembership extends Widget
{
    /** @var Space */
    public $space;

    public function run()
    {
        $membership = Membership::find()->where([
            'space_id' => $this->space->id,
            'user_id' => Yii::$app->user->id,
            'status' => Membership::STATUS_MEMBER
        ])->one();

        return $this->render('myMembership', [
            'role' => $this->space->getUserGroup(),
            'permissions' => $this->getPermissions(),
            'memberSince' => empty($membership) ? '-' : TimeAgo::widget(['timestamp' => $membership->created_at])
        ]);
    }

    public function getPermissions()
    {
        $userPermissions = [];
        $permissions = $this->space->permissionManager->getPermissions();
        $group_id = $this->space->getUserGroup();
        foreach ($permissions as $permission) {
            if ($this->space->permissionManager->getGroupState($group_id, $permission))
                $userPermissions[] = $permission->title;
        }

        return $userPermissions;
    }
}
