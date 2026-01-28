<?php

namespace humhub\modules\activity\controllers;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\widgets\ActivityBox;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\ContentContainer;
use Yii;

class ActivityBoxController extends ContentContainerController
{
    public $requireContainer = false;

    public $activityLoadLimit = 10;

    public function actionLoad()
    {
        $query = static::getQuery($this->contentContainer?->contentContainerRecord)
            ->limit($this->activityLoadLimit);

        $lastActivityId = (int)Yii::$app->request->getQueryParam('lastActivityId');
        if (!empty($lastActivityId)) {
            $query->andWhere(['<', Activity::tableName() . '.id', $lastActivityId]);
        }

        $activities = array_map(fn($activity) => ActivityBox::renderActivity($activity), $query->all());

        return $this->asJson([
            'activities' => $activities,
            'isLast' => count($activities) < $this->activityLoadLimit,
        ]);
    }

    public static function getQuery(?ContentContainer $contentContainer): ActiveQueryActivity
    {
        $query = Activity::find()
            ->enableGrouping()
            ->defaultScopes(Yii::$app->user->identity);

        if ($contentContainer !== null) {
            $query->contentContainer($contentContainer, Yii::$app->user->identity);
        } else {
            $query->subscribedContentContainers(Yii::$app->user->identity);
        }
        return $query;
    }

}
