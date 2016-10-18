<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\models\Membership;

/**
 * Created by PhpStorm.
 * User: Struppi
 * Date: 17.12.13
 * Time: 12:49
 */
class Chooser extends Widget
{

    /**
     * Displays / Run the Widgets
     */
    public function run()
    {
        if (Yii::$app->user->isGuest)
            return;

        return $this->render('spaceChooser', [
                    'currentSpace' => $this->getCurrentSpace(),
                    'canCreateSpace' => $this->canCreateSpace(),
                    'memberships' => $this->getMembershipQuery()->all()
        ]);
    }

    protected function getMembershipQuery()
    {
        $query = Membership::find();

        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $query->orderBy('name ASC');
        } else {
            $query->orderBy('last_visit DESC');
        }

        $query->joinWith('space');
        $query->where(['space_membership.user_id' => Yii::$app->user->id, 'space_membership.status' => Membership::STATUS_MEMBER]);

        return $query;
    }

    protected function canCreateSpace()
    {
        return (Yii::$app->user->permissionmanager->can(new CreatePublicSpace) || Yii::$app->user->permissionmanager->can(new CreatePrivateSpace()));
    }

    protected function getCurrentSpace()
    {
        $currentSpace = null;
        if (Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController) {
            if (Yii::$app->controller->contentContainer !== null && Yii::$app->controller->contentContainer instanceof \humhub\modules\space\models\Space) {
                return Yii::$app->controller->contentContainer;
            }
        }

        return null;
    }

}

?>