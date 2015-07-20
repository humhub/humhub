<?php

namespace humhub\modules\search\widgets;

use Yii;

class SearchMenu extends \yii\base\Widget
{

    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        return $this->render('searchMenu', array());
    }

}
