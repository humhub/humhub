<?php

namespace humhub\core\space\widgets;

use Yii;
use \yii\base\Widget;

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
        if (Yii::$app->controller instanceof \humhub\core\content\components\ContentContainerController) {
            if (Yii::$app->controller->contentContainer !== null && Yii::$app->controller->contentContainer instanceof \humhub\core\space\models\Space) {
                $currentSpace = Yii::$app->controller->contentContainer;
            }
        }

        return $this->render('spaceChooser', array('currentSpace' => $currentSpace));
    }

}

?>