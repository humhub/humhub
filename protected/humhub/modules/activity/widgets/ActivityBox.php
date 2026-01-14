<?php

namespace humhub\modules\activity\widgets;

use humhub\components\Widget;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;

class ActivityBox extends Widget
{
    public ?ContentContainerActiveRecord $contentContainer = null;

    public function run()
    {
        $query = Activity::find()
            ->limit(50)
            ->defaultScopes(Yii::$app->user->identity);

        if ($this->contentContainer !== null) {
            $query->contentContainer($this->contentContainer->contentContainerRecord, Yii::$app->user->identity);
        } else {
            $query->subscribedContentContainers(Yii::$app->user->identity);
        }

        return $this->render('activity-box', [
            'activities' => $query->all()
        ]);
    }

}
