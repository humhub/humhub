<?php

namespace humhub\modules\activity\widgets;

use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\activity\controllers\ActivityBoxController;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\RenderService;
use humhub\modules\content\components\ContentContainerActiveRecord;
use yii\helpers\Url;

class ActivityBox extends Widget
{
    public ?ContentContainerActiveRecord $contentContainer = null;
    public int $initLimit = 5;

    public function run()
    {
        $activities = ActivityBoxController::getQuery($this->contentContainer?->contentContainerRecord)
            ->limit($this->initLimit)->all();

        return $this->render('activity-box', [
            'activities' => $activities,
            'hasMore' => count($activities) === $this->initLimit,
            'options' => $this->getOptions(),
        ]);
    }

    public static function renderActivity(Activity $activity): string
    {
        return Html::tag('div', (new RenderService($activity))->getWeb(), [
            'class' => 'activity-entry',
            'data-activity-id' => $activity->id,
        ]);
    }

    protected function getOptions(): array
    {
        return [
            'id' => 'activity-box-content',
            'class' => 'hh-list activities',
            'data' => $this->getData(),
        ];
    }

    protected function getData(): array
    {
        return [
            'ui-widget' => 'activity.ActivityBox',
            'ui-init' => true,
            'box-url' => Url::to(['/activity/activity-box/load', 'contentContainer' => $this->contentContainer]),
        ];
    }
}
