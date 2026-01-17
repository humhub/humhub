<?php

namespace humhub\modules\activity\widgets;

use humhub\components\Widget;
use humhub\modules\activity\controllers\ActivityBoxController;
use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\helpers\Url;

class ActivityBox extends Widget
{
    public ?ContentContainerActiveRecord $contentContainer = null;

    public function run()
    {
        return $this->render('activity-box', [
            'activities' => ActivityBoxController::getQuery($this->contentContainer?->contentContainerRecord)
                ->limit(5)->all(),
            'loadUrl' => Url::to(['/activity/activity-box/load', 'contentContainer' => $this->contentContainer]),
        ]);
    }
}
