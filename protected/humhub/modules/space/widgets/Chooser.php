<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\components\Widget;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;

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

        $canCreateSpace = (Yii::$app->user->permissionmanager->can(new CreatePublicSpace) || Yii::$app->user->permissionmanager->can(new CreatePrivateSpace()));

        return $this->render('spaceChooser', array('currentSpace' => $currentSpace, 'canCreateSpace' => $canCreateSpace));
    }

}

?>