<?php

namespace humhub\modules\space\widgets;

use Yii;
use \yii\base\Widget;
use humhub\modules\space\models\Membership;
use humhub\modules\user\models\Setting;

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

        $currentSpace = null;
        if (Yii::$app->controller instanceof \humhub\modules\content\components\ContentContainerController) {
            if (Yii::$app->controller->contentContainer !== null && Yii::$app->controller->contentContainer instanceof \humhub\modules\space\models\Space) {
                $currentSpace = Yii::$app->controller->contentContainer;
            }
        }

        $query = Membership::find();

        if (Setting::Get('spaceOrder', 'space') == 0) {
            $query->orderBy('name ASC');
        } else {
            $query->orderBy('last_visit DESC');
        }

        $query->joinWith('space');
        $query->where(['space_membership.user_id' => Yii::$app->user->id, 'space_membership.status' => Membership::STATUS_MEMBER]);

        return $this->render('spaceChooser', ['currentSpace' => $currentSpace, 'memberships' => $query->all()]);
    }

}

?>