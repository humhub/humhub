<?php

namespace humhub\modules\activity\widgets;


use humhub\components\Widget;
use humhub\modules\activity\services\ActivityListService;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;

class ActivityBox extends Widget
{
    public ?ContentContainerActiveRecord $contentContainer = null;

    public function run()
    {
        $activityListService = new ActivityListService(Yii::$app->user->identity, $this->contentContainer);

        return $this->render('activity-box', [
           'activities' => $activityListService->getRenderedWeb()
        ]);
    }

}
