<?php

namespace humhub\modules\activity\controllers;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\services\RenderService;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\content\models\ContentContainer;
use Yii;

class ActivityBoxController extends ContentContainerController
{
    public $requireContainer = false;

    public $activityLoadLimit = 10;

    public function actionLoad()
    {
        $query = static::getQuery($this->contentContainer?->contentContainerRecord);

        $lastActivityId = (int)Yii::$app->request->getQueryParam('lastActivityId');
        if (!empty($lastActivityId)) {
            $query->andWhere(['>', 'id', $lastActivityId]);
        }

        $result = ['activities' => []];
        foreach ($query->limit($this->activityLoadLimit)->all() as $activity) {
            /** @var Activity $activity */
            $result['activities'][$activity->id] = (new RenderService($activity))->getWeb();
        }

        return $this->asJson($result);
    }

    public static function getQuery(?ContentContainer $contentContainer): ActiveQueryActivity
    {
        $query = Activity::find()
            ->defaultScopes(Yii::$app->user->identity);

        if ($contentContainer !== null) {
            $query->contentContainer($contentContainer, Yii::$app->user->identity);
        } else {
            $query->subscribedContentContainers(Yii::$app->user->identity);
        }

        return $query;
    }

}
